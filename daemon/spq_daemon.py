#!/usr/bin/env python3
"""
SPQ Daemon - Mac Mini Agent Bridge (SSH/MySQL direct mode)
Bypasses SiteGround HTTP entirely by connecting to MySQL via SSH tunnel.

Usage:
    python3 spq_daemon.py --token TOKEN [options]

Install dependency:
    pip3 install pymysql
"""

import argparse
import json
import logging
import platform
import subprocess
import sys
import time
import os
from typing import Optional

import pymysql
import pymysql.cursors

# ── Globals ─────────────────────────────────────────────────────────────────
_tunnel_proc: Optional[subprocess.Popen] = None
_db_conn:     Optional[pymysql.connections.Connection] = None

POLL_INTERVAL      = 5    # seconds between polls
HEARTBEAT_INTERVAL = 30   # seconds between heartbeats
OPENCLAW_BINARY    = 'openclaw'
OPENCLAW_TIMEOUT   = 300  # 5 minutes max per task

# Filled by argparse
SSH_HOST = ''; SSH_PORT = 18765; SSH_USER = ''; SSH_KEY = ''
DB_HOST  = '127.0.0.1'; DB_PORT = 3307
DB_NAME  = ''; DB_USER = ''; DB_PASS = ''

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


# ── SSH Tunnel ───────────────────────────────────────────────────────────────

def ensure_tunnel() -> bool:
    """Start or restart the SSH tunnel if it is not running."""
    global _tunnel_proc, _db_conn

    if _tunnel_proc and _tunnel_proc.poll() is None:
        return True  # Tunnel still alive

    log.info(f"Opening SSH tunnel → {SSH_USER}@{SSH_HOST}:{SSH_PORT} (MySQL on 127.0.0.1:{DB_PORT})")

    cmd = [
        'ssh', '-N', '-q',
        '-L', f'{DB_PORT}:127.0.0.1:3306',
        '-p', str(SSH_PORT),
        '-o', 'StrictHostKeyChecking=no',
        '-o', 'ServerAliveInterval=30',
        '-o', 'ServerAliveCountMax=3',
        '-o', 'ExitOnForwardFailure=yes',
        '-o', 'ConnectTimeout=15',
    ]
    if SSH_KEY:
        cmd += ['-i', os.path.expanduser(SSH_KEY)]
    cmd.append(f'{SSH_USER}@{SSH_HOST}')

    try:
        _tunnel_proc = subprocess.Popen(cmd, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
        time.sleep(3)
        if _tunnel_proc.poll() is not None:
            log.error('SSH tunnel exited immediately. Vérifiez votre clé SSH et les paramètres de connexion.')
            return False
        log.info(f'Tunnel SSH actif (PID {_tunnel_proc.pid})')
        _db_conn = None  # Force reconnect to MySQL after tunnel restart
        return True
    except FileNotFoundError:
        log.error("Commande 'ssh' introuvable.")
        return False
    except Exception as e:
        log.error(f'Tunnel error: {e}')
        return False


# ── MySQL ────────────────────────────────────────────────────────────────────

def get_db() -> Optional[pymysql.connections.Connection]:
    """Return a live MySQL connection, reconnecting if needed."""
    global _db_conn

    try:
        if _db_conn:
            _db_conn.ping(reconnect=True)
            return _db_conn
    except Exception:
        _db_conn = None

    try:
        _db_conn = pymysql.connect(
            host=DB_HOST,
            port=DB_PORT,
            user=DB_USER,
            password=DB_PASS,
            database=DB_NAME,
            charset='utf8mb4',
            cursorclass=pymysql.cursors.DictCursor,
            autocommit=False,
            connect_timeout=10,
        )
        log.info('MySQL connecté.')
        return _db_conn
    except Exception as e:
        log.error(f'MySQL connection error: {e}')
        return None


def get_machine_id(token: str) -> Optional[int]:
    db = get_db()
    if not db: return None
    try:
        with db.cursor() as cur:
            cur.execute('SELECT id FROM mac_machines WHERE token = %s', (token,))
            row = cur.fetchone()
        db.commit()
        return row['id'] if row else None
    except Exception as e:
        db.rollback()
        log.error(f'get_machine_id: {e}')
        return None


# ── Heartbeat ────────────────────────────────────────────────────────────────

def send_heartbeat(machine_id: int) -> None:
    db = get_db()
    if not db: return
    metadata = json.dumps({
        'os_version':       platform.mac_ver()[0] or platform.system(),
        'hostname':         platform.node(),
        'openclaw_version': get_openclaw_version(),
        'openclaw_agents':  get_openclaw_agents(),
    })
    try:
        with db.cursor() as cur:
            cur.execute(
                "UPDATE mac_machines SET status='online', last_seen_at=NOW(), metadata=%s WHERE id=%s",
                (metadata, machine_id)
            )
        db.commit()
        log.debug('Heartbeat OK')
    except Exception as e:
        db.rollback()
        log.error(f'Heartbeat error: {e}')


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
            [OPENCLAW_BINARY, 'agent', '--profile', profile, '--message', content],
            capture_output=True, text=True, timeout=OPENCLAW_TIMEOUT,
        )
        if r.returncode != 0:
            error = r.stderr.strip() or f'Exit code {r.returncode}'
            log.warning(f'openclaw error ({profile}): {error}')
            return None, error
        output = r.stdout.strip()
        if not output:
            return None, "Réponse vide de l'agent."
        log.info(f'openclaw OK ({profile}) — {len(output)} chars')
        return output, None
    except subprocess.TimeoutExpired:
        return None, f'Timeout après {OPENCLAW_TIMEOUT}s'
    except FileNotFoundError:
        return None, 'Binaire openclaw introuvable.'
    except Exception as e:
        return None, str(e)


# ── Message polling & dispatch ───────────────────────────────────────────────

def get_pending_messages(machine_id: int) -> list:
    """Fetch pending messages and mark them as 'processing' atomically."""
    db = get_db()
    if not db: return []
    try:
        with db.cursor() as cur:
            cur.execute("""
                SELECT m.id, m.conversation_id, m.content, a.profile AS openclaw_profile
                FROM messages m
                JOIN conversations   c  ON m.conversation_id   = c.id
                JOIN project_members pm ON c.project_member_id = pm.id
                JOIN agents          a  ON pm.agent_id         = a.id
                WHERE m.direction = 'out'
                  AND m.status    = 'pending'
                  AND a.mac_machine_id = %s
                LIMIT 10
                FOR UPDATE
            """, (machine_id,))
            rows = cur.fetchall()

            if rows:
                ids = [r['id'] for r in rows]
                fmt = ','.join(['%s'] * len(ids))
                cur.execute(f"UPDATE messages SET status='processing' WHERE id IN ({fmt})", ids)

        db.commit()
        return rows
    except Exception as e:
        db.rollback()
        log.error(f'get_pending_messages: {e}')
        return []


def submit_result(msg: dict, result: Optional[str], error: Optional[str]) -> None:
    """Write OpenClaw result back to MySQL."""
    db = get_db()
    if not db: return
    try:
        with db.cursor() as cur:
            if error:
                cur.execute(
                    "UPDATE messages SET status='error', error_message=%s, processed_at=NOW() WHERE id=%s",
                    (error[:1000], msg['id'])
                )
            else:
                cur.execute(
                    "UPDATE messages SET status='done', processed_at=NOW() WHERE id=%s",
                    (msg['id'],)
                )
                cur.execute(
                    """INSERT INTO messages (conversation_id, direction, content, status)
                       VALUES (%s, 'out', %s, 'response')""",
                    (msg['conversation_id'], result)
                )
        db.commit()
    except Exception as e:
        db.rollback()
        log.error(f'submit_result: {e}')


def process_message(msg: dict) -> None:
    profile = msg.get('openclaw_profile')
    if not profile:
        log.warning(f"Message {msg['id']} sans profil openclaw, ignoré.")
        submit_result(msg, None, 'Profil OpenClaw non configuré.')
        return

    result, error = run_openclaw(profile, msg['content'])
    submit_result(msg, result, error)


# ── Main loop ────────────────────────────────────────────────────────────────

def main():
    global SSH_HOST, SSH_PORT, SSH_USER, SSH_KEY
    global DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS
    global OPENCLAW_BINARY

    parser = argparse.ArgumentParser(description='SPQ Daemon — SSH/MySQL mode')
    parser.add_argument('--token',        required=True,  help='Token de la machine (mac_machines.token)')
    parser.add_argument('--ssh-host',     default='ssh.spq.app')
    parser.add_argument('--ssh-port',     type=int, default=18765)
    parser.add_argument('--ssh-user',     default='u685-ubgnyznjtpvj')
    parser.add_argument('--ssh-key',      default='',     help='Chemin clé privée SSH (défaut : clé SSH par défaut)')
    parser.add_argument('--db-host',      default='127.0.0.1')
    parser.add_argument('--db-port',      type=int, default=3307)
    parser.add_argument('--db-name',      required=True)
    parser.add_argument('--db-user',      required=True)
    parser.add_argument('--db-pass',      required=True)
    parser.add_argument('--openclaw',     default=OPENCLAW_BINARY)
    parser.add_argument('--poll-interval',type=int, default=POLL_INTERVAL)
    args = parser.parse_args()

    SSH_HOST = args.ssh_host
    SSH_PORT = args.ssh_port
    SSH_USER = args.ssh_user
    SSH_KEY  = args.ssh_key
    DB_HOST  = args.db_host
    DB_PORT  = args.db_port
    DB_NAME  = args.db_name
    DB_USER  = args.db_user
    DB_PASS  = args.db_pass
    OPENCLAW_BINARY = args.openclaw

    log.info(f'SPQ Daemon démarrage — tunnel SSH → {SSH_USER}@{SSH_HOST}:{SSH_PORT}')

    machine_id    = None
    last_heartbeat = 0

    while True:
        try:
            # ── 1. Ensure SSH tunnel ──────────────────────────────────────
            if not ensure_tunnel():
                log.error('Tunnel SSH indisponible — nouvelle tentative dans 30s')
                time.sleep(30)
                continue

            # ── 2. Identify this machine ──────────────────────────────────
            if machine_id is None:
                machine_id = get_machine_id(args.token)
                if machine_id is None:
                    log.error('Token introuvable en base. Vérifiez --token.')
                    time.sleep(60)
                    continue
                log.info(f'Machine ID : {machine_id}')

            # ── 3. Heartbeat ──────────────────────────────────────────────
            now = time.time()
            if now - last_heartbeat >= HEARTBEAT_INTERVAL:
                send_heartbeat(machine_id)
                last_heartbeat = time.time()

            # ── 4. Poll pending messages ──────────────────────────────────
            messages = get_pending_messages(machine_id)
            if messages:
                log.info(f'{len(messages)} message(s) à traiter.')
                for msg in messages:
                    process_message(msg)

            time.sleep(args.poll_interval)

        except KeyboardInterrupt:
            log.info('Daemon arrêté.')
            break
        except Exception as e:
            log.error(f'Erreur boucle principale : {e}')
            time.sleep(10)

    # ── Cleanup ───────────────────────────────────────────────────────────
    if _tunnel_proc and _tunnel_proc.poll() is None:
        _tunnel_proc.terminate()
    if _db_conn:
        try: _db_conn.close()
        except Exception: pass


if __name__ == '__main__':
    main()
