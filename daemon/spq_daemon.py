#!/usr/bin/env python3
"""
SPQ Daemon - Mac Mini Agent Bridge (HTTP API mode)
Uses the SPQ Laravel HTTP API to poll for pending messages and submit results.

Usage:
    python3 spq_daemon.py --token TOKEN [options]

No SSH tunnel or direct MySQL access needed — all communication goes through HTTPS.
"""

import argparse
import json
import logging
import subprocess
import sys
import time
import os
import platform
import urllib.request
import urllib.error
import ssl
from typing import Optional

# ── Globals ─────────────────────────────────────────────────────────────────
POLL_INTERVAL      = 5    # seconds between polls
HEARTBEAT_INTERVAL = 30   # seconds between heartbeats
OPENCLAW_BINARY    = 'openclaw'
OPENCLAW_TIMEOUT   = 300  # 5 minutes max per task
SPQ_BASE_URL       = 'https://spq.app'

# ── Logging ──────────────────────────────────────────────────────────────────
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s [%(levelname)s] %(message)s',
    handlers=[
        logging.StreamHandler(sys.stdout),
        logging.FileHandler('/tmp/SPQ_daemon.log'),
    ]
)
log = logging.getLogger('SPQ_daemon')


# ── HTTP helpers ─────────────────────────────────────────────────────────────

def api_request(method: str, path: str, token: str, data: dict = None) -> dict:
    """Make an authenticated API request to SPQ."""
    url = f'{SPQ_BASE_URL}{path}'
    body = json.dumps(data).encode() if data else None

    req = urllib.request.Request(url, data=body, method=method)
    req.add_header('Authorization', f'Bearer {token}')
    req.add_header('Accept', 'application/json')
    req.add_header('Content-Type', 'application/json')
    req.add_header('User-Agent', 'SPQ-Daemon/1.0')

    try:
        with urllib.request.urlopen(req, timeout=30) as resp:
            return json.loads(resp.read().decode())
    except urllib.error.HTTPError as e:
        body = e.read().decode() if e.fp else ''
        log.error(f'HTTP {e.code} on {method} {path}: {body[:200]}')
        return {}
    except urllib.error.URLError as e:
        log.error(f'Connection error on {method} {path}: {e.reason}')
        return {}
    except Exception as e:
        log.error(f'Request error on {method} {path}: {e}')
        return {}


# ── OpenClaw ─────────────────────────────────────────────────────────────────

def get_openclaw_version() -> str:
    try:
        r = subprocess.run([OPENCLAW_BINARY, '--version'], capture_output=True, text=True, timeout=5)
        return r.stdout.strip().split('\n')[0][:30]
    except Exception:
        return 'unknown'


def get_openclaw_agents() -> list:
    try:
        r = subprocess.run(
            [OPENCLAW_BINARY, 'agents', 'list', '--json'],
            capture_output=True, text=True, timeout=10
        )
        if r.returncode == 0 and r.stdout.strip():
            try:
                data = json.loads(r.stdout)
                agents = []
                for item in (data if isinstance(data, list) else data.get('agents', [])):
                    if isinstance(item, dict):
                        agent_id   = item.get('id') or item.get('profile') or item.get('name') or str(item)
                        agent_name = item.get('identityName') or item.get('name') or agent_id
                        agents.append({'name': agent_name, 'profile': agent_id})
                    else:
                        agents.append({'name': str(item), 'profile': str(item)})
                return agents
            except json.JSONDecodeError:
                lines = [l.strip() for l in r.stdout.splitlines() if l.strip()]
                return [{'name': l, 'profile': l} for l in lines]
    except Exception as e:
        log.debug(f'Could not list openclaw agents: {e}')
    return []


def run_openclaw(profile: str, content: str) -> tuple[Optional[str], Optional[str]]:
    try:
        log.info(f'Running openclaw --profile {profile}')
        r = subprocess.run(
            [OPENCLAW_BINARY, 'agent', '--agent', profile, '--message', content],
            capture_output=True, text=True, timeout=OPENCLAW_TIMEOUT,
        )
        if r.returncode != 0:
            error = r.stderr.strip() or f'Exit code {r.returncode}'
            log.warning(f'openclaw error ({profile}): {error}')
            return None, error
        output = r.stdout.strip()
        if not output:
            return None, 'Empty response from agent.'
        log.info(f'openclaw OK ({profile}) — {len(output)} chars')
        return output, None
    except subprocess.TimeoutExpired:
        return None, f'Timeout après {OPENCLAW_TIMEOUT}s'
    except FileNotFoundError:
        return None, 'Binaire openclaw introuvable.'
    except Exception as e:
        return None, str(e)


# ── Main loop ────────────────────────────────────────────────────────────────

def main():
    global SPQ_BASE_URL, OPENCLAW_BINARY

    parser = argparse.ArgumentParser(description='SPQ Daemon — HTTP API mode')
    parser.add_argument('--token',        required=True,  help='Token de la machine (mac_machines.token)')
    parser.add_argument('--api-url',      default=SPQ_BASE_URL, help='URL de base de l\'API SPQ')
    parser.add_argument('--openclaw',     default=OPENCLAW_BINARY)
    parser.add_argument('--poll-interval',type=int, default=POLL_INTERVAL)
    args = parser.parse_args()

    SPQ_BASE_URL   = args.api_url.rstrip('/')
    OPENCLAW_BINARY = args.openclaw
    token          = args.token

    log.info(f'SPQ Daemon démarrage (HTTP) — API: {SPQ_BASE_URL}')

    last_heartbeat = 0

    while True:
        try:
            now = time.time()

            # ── 1. Heartbeat ──────────────────────────────────────────────
            if now - last_heartbeat >= HEARTBEAT_INTERVAL:
                metadata = {
                    'os_version':       platform.mac_ver()[0] or platform.system(),
                    'hostname':         platform.node(),
                    'openclaw_version': get_openclaw_version(),
                    'openclaw_agents':  get_openclaw_agents(),
                }
                resp = api_request('POST', '/api/mac/heartbeat', token, {'metadata': metadata})
                if resp.get('status') == 'ok':
                    log.debug('Heartbeat OK')
                else:
                    log.warning('Heartbeat échoué — vérifiez le token.')
                last_heartbeat = time.time()

            # ── 2. Poll pending messages ──────────────────────────────────
            resp = api_request('GET', '/api/mac/messages/pending', token)
            messages = resp.get('messages', [])

            if messages:
                log.info(f'{len(messages)} message(s) à traiter.')
                for msg in messages:
                    profile = msg.get('openclaw_profile')
                    if not profile:
                        log.warning(f"Message {msg['id']} sans profil openclaw, ignoré.")
                        api_request('POST', f'/api/mac/messages/{msg["id"]}/result', token, {
                            'error': 'Profil OpenClaw non configuré.'
                        })
                        continue

                    result, error = run_openclaw(profile, msg['content'])

                    if error:
                        api_request('POST', f'/api/mac/messages/{msg["id"]}/result', token, {
                            'error': error[:1000]
                        })
                    else:
                        api_request('POST', f'/api/mac/messages/{msg["id"]}/result', token, {
                            'result': result
                        })

            time.sleep(args.poll_interval)

        except KeyboardInterrupt:
            log.info('Daemon arrêté.')
            break
        except Exception as e:
            log.error(f'Erreur boucle principale : {e}')
            time.sleep(10)


if __name__ == '__main__':
    main()