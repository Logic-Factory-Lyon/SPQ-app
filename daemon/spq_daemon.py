#!/usr/bin/env python3
"""
SPQ Daemon - Mac Mini Agent Bridge (HTTP API mode)
Uses the SPQ Laravel HTTP API to poll for pending messages and submit results.
Also handles agent initialization/resync tasks and centralized skill syncing.

Usage:
    python3 spq_daemon.py --token TOKEN [options]
    python3 spq_daemon.py --resync-skills  (one-shot skill sync from API)

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
import shutil
import urllib.request
import urllib.error
from typing import Optional

# ── Globals ─────────────────────────────────────────────────────────────────
POLL_INTERVAL      = 5    # seconds between polls
HEARTBEAT_INTERVAL = 30   # seconds between heartbeats
OPENCLAW_BINARY    = 'openclaw'
OPENCLAW_TIMEOUT   = 300  # 5 minutes max per task
SPQ_BASE_URL       = 'https://spq.app'
DAEMON_VERSION     = 6     # increment when breaking changes are deployed
SELF_UPDATE_INTERVAL = 300  # check for updates every 5 minutes

# ── Central paths ───────────────────────────────────────────────────────────
CENTRAL_SKILLS_DIR = os.path.expanduser('~/.openclaw/spqapp/skills')
CENTRAL_TOOLS_DIR  = os.path.expanduser('~/.openclaw/spqapp/tools')
BRIDGE_TOOLS_JSON  = os.path.join(CENTRAL_TOOLS_DIR, 'spq_bridge_tools.json')

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
        log.info(f'Running openclaw --agent {profile}')
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
        return None, f'Timeout after {OPENCLAW_TIMEOUT}s'
    except FileNotFoundError:
        return None, 'openclaw binary not found.'
    except Exception as e:
        return None, str(e)


# ── Self-update ──────────────────────────────────────────────────────────────

def check_self_update(token: str) -> bool:
    """Download latest daemon script from SPQ and restart if version is newer."""
    log.info('Checking for daemon update...')
    url = f'{SPQ_BASE_URL}/daemon/script'
    req = urllib.request.Request(url)
    req.add_header('User-Agent', 'SPQ-Daemon/1.0')
    try:
        with urllib.request.urlopen(req, timeout=30) as r:
            new_script = r.read().decode('utf-8')
    except Exception as e:
        log.error(f'Failed to download daemon update: {e}')
        return False

    import re
    match = re.search(r'^DAEMON_VERSION\s*=\s*(\d+)', new_script, re.MULTILINE)
    remote_version = int(match.group(1)) if match else 1
    log.info(f'Remote daemon version: {remote_version}, local: {DAEMON_VERSION}')

    if remote_version <= DAEMON_VERSION:
        log.info('Daemon is up to date.')
        return False

    script_path = os.path.abspath(__file__)
    backup_path = script_path + '.bak'

    log.info(f'Updating daemon: v{DAEMON_VERSION} → v{remote_version}')
    shutil.copy2(script_path, backup_path)

    with open(script_path, 'w', encoding='utf-8') as f:
        f.write(new_script)
    os.chmod(script_path, os.stat(script_path).st_mode | 0o755)

    log.info(f'Daemon updated to v{remote_version}. Restarting...')
    os.execv(sys.executable, [sys.executable] + sys.argv)
    return True


# ── Centralized Skills Sync ─────────────────────────────────────────────────

def sync_skills_from_api(token: str) -> int:
    """
    One-shot sync: pull all active skills from SPQ API and write to central directory.
    Returns the number of skills synced.
    """
    resp = api_request('GET', '/api/mac/skills/sync', token)
    skills = resp.get('skills', [])

    if not skills:
        log.warning('No skills received from API')
        return 0

    os.makedirs(CENTRAL_SKILLS_DIR, exist_ok=True)

    # Write individual .skill.json files
    for skill in skills:
        slug = skill.get('slug', 'unknown')
        filepath = os.path.join(CENTRAL_SKILLS_DIR, f'{slug}.skill.json')
        with open(filepath, 'w', encoding='utf-8') as f:
            json.dump(skill, f, indent=2)
        log.info(f'Synced skill: {slug}.skill.json')

    # Write master skills.json
    skills_json_path = os.path.join(CENTRAL_SKILLS_DIR, 'skills.json')
    with open(skills_json_path, 'w', encoding='utf-8') as f:
        json.dump(skills, f, indent=2)
    log.info(f'Written skills.json ({len(skills)} skills)')

    # Write tools config if provided
    tools_config = resp.get('tools_config', {})
    if tools_config:
        os.makedirs(CENTRAL_TOOLS_DIR, exist_ok=True)
        with open(BRIDGE_TOOLS_JSON, 'w', encoding='utf-8') as f:
            json.dump(tools_config, f, indent=2)
        log.info(f'Written spq_bridge_tools.json')

    return len(skills)


def sync_skills_to_agent_workspace(project_id: int, agent_id: int, agent_skills: list) -> int:
    """
    Copy skill files from central directory to a specific agent's workspace.
    Only copies skills that are attached to this agent (via agent_skill pivot).
    agent_skills is a list of {slug, name, ...} dicts from the task payload.
    """
    pid = project_id if project_id else 0
    workspace = os.path.expanduser(f'~/.openclaw/spqapp/{pid}/agents/workspace-{agent_id}')
    agent_skills_dir = os.path.join(workspace, 'spqapp', 'skills')
    agent_tools_dir = os.path.join(workspace, 'spqapp', 'tools')

    os.makedirs(agent_skills_dir, exist_ok=True)
    os.makedirs(agent_tools_dir, exist_ok=True)

    # Copy only attached skills
    synced = []
    for skill in agent_skills:
        slug = skill.get('slug', '')
        central_file = os.path.join(CENTRAL_SKILLS_DIR, f'{slug}.skill.json')
        if os.path.exists(central_file):
            dest = os.path.join(agent_skills_dir, f'{slug}.skill.json')
            shutil.copy2(central_file, dest)
            synced.append(slug)
        else:
            log.warning(f'Central skill file not found for {slug}, creating from payload')
            dest = os.path.join(agent_skills_dir, f'{slug}.skill.json')
            with open(dest, 'w', encoding='utf-8') as f:
                json.dump(skill, f, indent=2)
            synced.append(slug)

    # Write filtered skills.json for this agent
    agent_skills_json = os.path.join(agent_skills_dir, 'skills.json')
    with open(agent_skills_json, 'w', encoding='utf-8') as f:
        json.dump(agent_skills, f, indent=2)
    log.info(f'Written agent skills.json ({len(synced)} skills) for workspace-{agent_id}')

    # Copy bridge tools config
    if os.path.exists(BRIDGE_TOOLS_JSON):
        dest = os.path.join(agent_tools_dir, 'spq_bridge_tools.json')
        shutil.copy2(BRIDGE_TOOLS_JSON, dest)
        log.info(f'Copied spq_bridge_tools.json to workspace')

    # Clean up old-style skills/ directory if it exists
    old_skills_dir = os.path.join(workspace, 'skills')
    if os.path.isdir(old_skills_dir):
        shutil.rmtree(old_skills_dir)
        log.info(f'Removed old-style skills/ directory')

    return len(synced)


# ── Agent Initialization ────────────────────────────────────────────────────

def expand_path(path: str) -> str:
    """Expand ~ and environment variables in a path."""
    return os.path.expanduser(os.path.expandvars(path))


def initialize_agent(task: dict) -> tuple[Optional[str], Optional[str]]:
    """
    Initialize an agent workspace on this machine and register it in OpenClaw.
    task['payload'] contains: profile, name, system_prompt, project_id, agent_id, skills
    Skills are now synced from the central directory.
    """
    payload = task.get('payload', {})
    profile = payload.get('profile', '')
    name = payload.get('name', 'unknown')
    system_prompt = payload.get('system_prompt', '')
    project_id = payload.get('project_id', 0)
    agent_id = payload.get('agent_id', 0)
    skills = payload.get('skills', [])

    if not profile:
        return None, 'No profile specified for agent initialization.'

    pid = project_id if project_id else 0
    workspace_path = f'~/.openclaw/spqapp/{pid}/agents/workspace-{agent_id}'
    workspace = expand_path(workspace_path)

    try:
        log.info(f'Initializing agent "{name}" (profile={profile}) at {workspace}')

        # 1. Create workspace directory
        os.makedirs(workspace, exist_ok=True)

        # 2. Write SOUL.md (system prompt)
        soul_path = os.path.join(workspace, 'SOUL.md')
        with open(soul_path, 'w', encoding='utf-8') as f:
            f.write(f'# {name}\n\n')
            f.write(system_prompt or f'You are {name}, an AI agent managed by SPQ.')
            f.write('\n')
        log.info(f'Written SOUL.md ({os.path.getsize(soul_path)} bytes)')

        # 3. Write MEMORY.md
        memory_path = os.path.join(workspace, 'MEMORY.md')
        if not os.path.exists(memory_path):
            with open(memory_path, 'w', encoding='utf-8') as f:
                f.write(f'# Memory — {name}\n\n')
                f.write('This file is the agent\'s persistent memory.\n')
            log.info('Created MEMORY.md')

        # 4. Sync skills from central directory to agent workspace
        skills_count = 0
        if skills:
            skills_count = sync_skills_to_agent_workspace(project_id, agent_id, skills)
            log.info(f'Synced {skills_count} skills to agent workspace')

        # 5. Register agent in OpenClaw via CLI
        agent_registered = False
        try:
            existing = get_openclaw_agents()
            if any(a.get('profile') == profile for a in existing):
                log.info(f'Agent "{profile}" already exists in OpenClaw — skipping add')
                agent_registered = True
            else:
                log.info(f'Creating agent in OpenClaw: openclaw agents add {profile}')
                r = subprocess.run(
                    [OPENCLAW_BINARY, 'agents', 'add', profile,
                     '--workspace', workspace,
                     '--non-interactive',
                     '--json'],
                    capture_output=True, text=True, timeout=30
                )
                if r.returncode == 0:
                    agent_registered = True
                    log.info(f'Agent "{profile}" created in OpenClaw')
                else:
                    error_msg = r.stderr.strip() or f'Exit code {r.returncode}'
                    log.warning(f'Failed to create agent in OpenClaw: {error_msg}')
        except Exception as e:
            log.warning(f'Error creating agent in OpenClaw: {e}')

        result_parts = [
            f'Workspace: {workspace}',
            f'SOUL.md: {os.path.getsize(soul_path)} bytes',
            f'Skills: {skills_count} synced (central → workspace)',
            f'OpenClaw agent: {"registered" if agent_registered else "failed (check logs)"}',
        ]

        log.info(f'Agent "{name}" initialization complete')
        return '\n'.join(result_parts), None

    except PermissionError as e:
        return None, f'Permission denied: {e}'
    except OSError as e:
        return None, f'OS error: {e}'
    except Exception as e:
        return None, f'Unexpected error: {e}'


def destroy_agent(task: dict) -> tuple[Optional[str], Optional[str]]:
    """
    Destroy an agent: delete from OpenClaw, remove workspace files.
    """
    payload = task.get('payload', {})
    profile = payload.get('profile', '')
    project_id = payload.get('project_id', 0)
    agent_id = payload.get('agent_id', 0)
    name = payload.get('name', 'unknown')

    if not profile:
        return None, 'No profile specified for agent destruction.'

    pid = project_id if project_id else 0
    workspace_path = f'~/.openclaw/spqapp/{pid}/agents/workspace-{agent_id}'
    workspace = expand_path(workspace_path)

    try:
        log.info(f'Destroying agent "{name}" (profile={profile})')

        oc_deleted = False
        try:
            r = subprocess.run(
                [OPENCLAW_BINARY, 'agents', 'delete', profile, '--force', '--json'],
                capture_output=True, text=True, timeout=15
            )
            if r.returncode == 0:
                oc_deleted = True
                log.info(f'Agent "{profile}" deleted from OpenClaw')
            else:
                error_msg = r.stderr.strip() or f'Exit code {r.returncode}'
                log.warning(f'Failed to delete agent from OpenClaw: {error_msg}')
        except Exception as e:
            log.warning(f'Error deleting agent from OpenClaw: {e}')

        ws_removed = False
        if os.path.isdir(workspace):
            shutil.rmtree(workspace)
            ws_removed = True
            log.info(f'Removed workspace: {workspace}')
        else:
            log.info(f'Workspace not found (already removed): {workspace}')

        result_parts = [
            f'OpenClaw agent: {"deleted" if oc_deleted else "not found or failed"}',
            f'Workspace: {"removed" if ws_removed else "not found"}',
        ]

        log.info(f'Agent "{name}" destruction complete')
        return '\n'.join(result_parts), None

    except Exception as e:
        return None, f'Error destroying agent: {e}'


def resync_agent(task: dict) -> tuple[Optional[str], Optional[str]]:
    """
    Resync an agent: update skill files in workspace from central directory.
    """
    payload = task.get('payload', {})
    profile = payload.get('profile', '')
    project_id = payload.get('project_id', 0)
    agent_id = payload.get('agent_id', 0)
    name = payload.get('name', 'unknown')
    skills = payload.get('skills', [])

    pid = project_id if project_id else 0
    workspace = expand_path(f'~/.openclaw/spqapp/{pid}/agents/workspace-{agent_id}')

    if not os.path.isdir(workspace):
        return None, f'Workspace not found: {workspace}'

    try:
        count = sync_skills_to_agent_workspace(project_id, agent_id, skills)
        return f'Resynced {count} skills for agent "{name}"', None
    except Exception as e:
        return None, f'Resync error: {e}'


def process_tasks(token: str):
    """Poll and process agent initialization/resync tasks."""
    resp = api_request('GET', '/api/mac/tasks/pending', token)
    tasks = resp.get('tasks', [])

    if not tasks:
        return

    log.info(f'{len(tasks)} task(s) to process.')
    for task in tasks:
        task_type = task.get('type', '')
        task_id = task.get('id')
        log.info(f'Processing task {task_id} (type={task_type})')

        if task_type == 'initialize':
            result, error = initialize_agent(task)
        elif task_type == 'resync':
            result, error = resync_agent(task)
        elif task_type == 'destroy':
            result, error = destroy_agent(task)
        else:
            result, error = None, f'Unknown task type: {task_type}'

        if error:
            log.error(f'Task {task_id} failed: {error}')
            api_request('POST', f'/api/mac/tasks/{task_id}/result', token, {
                'error': error[:2000]
            })
        else:
            log.info(f'Task {task_id} succeeded')
            api_request('POST', f'/api/mac/tasks/{task_id}/result', token, {
                'result': result or 'OK'
            })


# ── Main loop ────────────────────────────────────────────────────────────────

def main():
    global SPQ_BASE_URL, OPENCLAW_BINARY

    parser = argparse.ArgumentParser(description='SPQ Daemon — HTTP API mode')
    parser.add_argument('--token',        required=True,  help='Machine token (mac_machines.token)')
    parser.add_argument('--api-url',      default=SPQ_BASE_URL, help='SPQ API base URL')
    parser.add_argument('--openclaw',     default=OPENCLAW_BINARY)
    parser.add_argument('--poll-interval', type=int, default=POLL_INTERVAL)
    parser.add_argument('--resync-skills', action='store_true', help='One-shot: sync skills from API to central dir')
    args = parser.parse_args()

    SPQ_BASE_URL   = args.api_url.rstrip('/')
    OPENCLAW_BINARY = args.openclaw
    token          = args.token

    # One-shot skills resync mode
    if args.resync_skills:
        log.info('One-shot skill resync from SPQ API...')
        count = sync_skills_from_api(token)
        log.info(f'Skill resync complete: {count} skills synced to {CENTRAL_SKILLS_DIR}')
        return

    log.info(f'SPQ Daemon starting (HTTP) — API: {SPQ_BASE_URL}')

    # Sync skills on startup
    log.info('Syncing skills from API on startup...')
    sync_skills_from_api(token)

    last_heartbeat = 0
    last_self_update = 0
    last_skills_sync = time.time()

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
                    if resp.get('daemon_restart'):
                        log.info('Daemon restart requested by server. Updating and restarting...')
                        check_self_update(token)
                else:
                    log.warning('Heartbeat failed — check token.')
                last_heartbeat = time.time()

            # ── 2. Self-update check ─────────────────────────────────────
            if now - last_self_update >= SELF_UPDATE_INTERVAL:
                check_self_update(token)
                last_self_update = time.time()

            # ── 3. Periodic skills sync (every 5 min) ───────────────────
            if now - last_skills_sync >= 300:
                sync_skills_from_api(token)
                last_skills_sync = time.time()

            # ── 4. Poll pending agent tasks ──────────────────────────────
            process_tasks(token)

            # ── 5. Poll pending messages ──────────────────────────────────
            resp = api_request('GET', '/api/mac/messages/pending', token)
            messages = resp.get('messages', [])

            if messages:
                log.info(f'{len(messages)} message(s) to process.')
                for msg in messages:
                    profile = msg.get('openclaw_profile')
                    if not profile:
                        log.warning(f"Message {msg['id']} has no openclaw profile, skipping.")
                        api_request('POST', f'/api/mac/messages/{msg["id"]}/result', token, {
                            'error': 'OpenClaw profile not configured.'
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
            log.info('Daemon stopped.')
            break
        except Exception as e:
            log.error(f'Main loop error: {e}')
            time.sleep(10)


if __name__ == '__main__':
    main()