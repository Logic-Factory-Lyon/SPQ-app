# Changelog

## v2.1 — 2026-04-16

### Added
- Agent hierarchy visualization (tree view with parent-child, skills, members)
- Agent delete with OpenClaw workspace cleanup (daemon `destroy_agent` task)
- Full i18n for all views (superadmin billing, client portal, manager, mac-machines, services, vat-rates, email-templates)
- LaunchAgent auto-start for daemon on Mac Mini
- `.env.example` updated for local development

### Fixed
- Security: `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true` in production
- Log rotation: `LOG_CHANNEL=daily` instead of single file
- Daemon `/daemon/script` parsing (use `urllib.request` instead of `api_request`)
- Stale task recovery (10 min threshold for processing tasks)
- `agent_id` nullable in `agent_tasks` for post-deletion destroy tasks
- Role labels: "Employé" → "Membre" (consistent with project member terminology)

### Changed
- Daemon v5: self-update, destroy agent, stale recovery, heartbeat restart signal
- `MacMachineController::restartDaemon()` sets flag in metadata, daemon picks up via heartbeat
- `AgentCrudController` removed `$this->middleware()` (Laravel 11 incompatible)

## v2.0 — 2026-04-16

### Added
- Agent CRUD (standalone create/edit/delete views)
- Agent initialization via daemon polling (`AgentTask` model)
- Team cloning (clone project + agents + members in one click)
- `openclaw agents add` integration for actual agent creation
- Daemon v4: auto-update, remote restart, task processing
- Skills system: dispatch skills from chat UI

### Changed
- Daemon architecture: HTTP API polling (no SSH, no direct DB access)
- Workspace path: `~/.openclaw/spqapp/{project_id}/agents/workspace-{agent_id}`

## v1.1 — 2026-04-15

### Added
- Skills model and CRUD (admin)
- i18n FR/EN foundation
- Chat UI for employee conversations
- Daemon HTTP rewrite (from SSH tunnel to API polling)

## v1.0 — 2026-04-14

### Added
- Rewrite to Laravel framework
- Authentication and role-based access
- CRUD: clients, projects, members, users
- Mac Mini daemon with message dispatch
- Telegram bot webhook integration