# SPQ — SaaS Management Platform

Superadmin panel for managing clients, projects, AI agents (OpenClaw), billing, and team collaboration.

## Tech Stack

- **Backend:** Laravel 11 (PHP 8.3)
- **Frontend:** Blade templates, Tailwind CSS
- **Database:** MySQL 8
- **AI Agents:** OpenClaw CLI, daemon (Python 3.9) polling architecture
- **Payments:** Stripe (Laravel Cashier)
- **Hosting:** Shared hosting (SSH deployment)

## Architecture

### Roles

| Role | Access |
|------|--------|
| superadmin | Full admin panel: clients, projects, agents, billing, users |
| client | Client portal: view projects, quotes, invoices, pay online |
| manager | Team dashboard: conversations, member management |
| member | Employee dashboard: chat with AI agent, skills dispatch |

### Daemon Architecture

The Mac Mini daemon (`daemon/spq_daemon.py`) polls the SPQ API every 5 seconds:
- **Heartbeat** — reports machine status, checks for restart signals
- **Task processing** — initialize/resync/destroy OpenClaw agents
- **Message dispatch** — sends employee messages to OpenClaw agents, polls responses
- **Auto-update** — checks for new daemon versions via `/daemon/script`

Agent initialization flow:
1. Superadmin creates agent (status: `draft`)
2. Clicks "Initialize on OpenClaw" → creates `AgentTask` (type: initialize)
3. Daemon picks up task → creates workspace, runs `openclaw agents add`
4. Reports result → agent status becomes `ready` (or `error`)

### Agent Hierarchy

Agents can have parent-child relationships (`parent_agent_id`). The hierarchy view shows a visual tree with skills, member assignments, and status.

## Local Setup

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

## Production Access

- **SSH:** `ssh -p 18765 u685-ubgnyznjtpvj@ssh.spq.app`
- **Path:** `~/www/spq.app/public_html/`
- **Daemon:** LaunchAgent `com.spq.daemon` (auto-start, KeepAlive)