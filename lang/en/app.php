<?php

return [

    // ── Brand & general ────────────────────────────────────────────────────
    'brand'          => 'SPQ',
    'tagline'        => 'AI Agent Management Platform',
    'all_rights'     => 'All rights reserved',

    // ── Auth ──────────────────────────────────────────────────────────────
    'login'          => 'Sign in',
    'logout'         => 'Sign out',
    'email'          => 'Email address',
    'password'       => 'Password',
    'forgot_password'=> 'Forgot password?',
    'remember_me'    => 'Remember me',
    'sign_in'        => 'Sign in',

    // ── Sidebar nav ───────────────────────────────────────────────────────
    'dashboard'      => 'Dashboard',

    // Employee
    'my_agent'       => 'My agent',

    // Client
    'my_projects'    => 'My projects',
    'projects_teams' => 'Projects & teams',
    'billing'        => 'Billing',
    'quotes'         => 'Quotes',
    'invoices'       => 'Invoices',

    // Manager
    'team'           => 'Team',
    'team_conversations'=> 'Team conversations',

    // Superadmin
    'clients'        => 'Clients',
    'client_accounts'=> 'Client accounts',
    'projects'       => 'Projects',
    'users'          => 'Users',
    'payments'       => 'Payments',
    'communication'  => 'Communication',
    'email_templates'=> 'Email templates',
    'settings'       => 'Settings',
    'skills'         => 'Skills',
    'service_catalog'=> 'Service catalog',
    'vat_rates'      => 'VAT rates',

    // ── Dashboard (employee) ──────────────────────────────────────────────
    'hello'                   => 'Hello, :name',
    'project_label'           => 'Project: :name',
    'no_project'              => 'You are not assigned to any project.',
    'no_agent'                => 'No agent assigned — contact your manager.',
    'available'               => 'available',
    'offline'                 => 'offline',
    'contact_manager'         => 'Contact your manager to be assigned to a project.',
    'start_conversation'      => 'Start a conversation',
    'recent_conversations'    => 'Recent conversations',
    'see_all'                 => 'See all →',

    // ── Conversations ────────────────────────────────────────────────────
    'conversations'           => 'Conversations',
    'new_conversation'       => 'New conversation',
    'new_conversation_btn'   => '+ New conversation',
    'delete'                  => 'Delete',
    'delete_conversation'     => 'Delete this conversation?',
    'no_conversations'        => 'No conversations',
    'start_new_conversation'  => 'Start a new conversation with your agent.',
    'title_optional'          => 'Title (optional)',
    'title_placeholder'       => 'e.g. Q1 sales analysis...',
    'create'                  => 'Create',
    'cancel'                   => 'Cancel',
    'conversation_of'          => 'Conversation of :date',

    // ── Chat ──────────────────────────────────────────────────────────────
    'online'            => 'online',
    'chat_placeholder'  => 'Write your message... (Enter to send, Shift+Enter for new line)',
    'enter_to_send'     => 'Enter to send · Shift+Enter for new line',
    'skill'             => 'Skill',
    'hide'              => 'Hide',
    'show'              => 'Show',
    'launch'            => 'Launch',
    'no_agent_configured'=> 'OpenClaw profile not configured.',

    // ── Telegram ──────────────────────────────────────────────────────────
    'telegram_description'     => 'Your agent is available on Telegram. Click below to open the chat directly in the app.',
    'open_telegram'            => 'Open Telegram chat',
    'telegram_note'            => 'Conversations happen directly in Telegram.',
    'agent_online'            => 'online',
    'agent_offline'           => 'offline',

    // ── Common actions ─────────────────────────────────────────────────────
    'save'           => 'Save',
    'edit'           => 'Edit',
    'add'            => 'Add',
    'back'           => 'Back',
    'confirm'        => 'Confirm',
    'search'         => 'Search...',

    // ── Agent management ───────────────────────────────────────────────────
    'agents'                    => 'OpenClaw Agents',
    'add_agent'                 => '+ New agent',
    'agent_name'                => 'Agent name',
    'agent_name_placeholder'    => 'e.g. oracle-erp-specialist',
    'agent_profile'             => 'Profile (slug)',
    'agent_profile_placeholder'=> 'e.g. oracle-erp-v1',
    'agent_profile_help'        => 'Unique identifier used by OpenClaw to identify this agent.',
    'description'               => 'Description',
    'machine'                   => 'Machine',
    'no_machine'                => 'No machine',
    'create_new_machine'        => 'Create new machine',
    'parent_agent'              => 'Parent agent',
    'no_parent'                 => 'None (root)',
    'system_prompt'             => 'System prompt',
    'system_prompt_help'         => 'Base instructions injected into the agent context at startup.',
    'workspace_path'             => 'Workspace path',
    'telegram_settings'          => 'Telegram settings',
    'telegram_bot_username'      => 'Telegram bot username',
    'telegram_bot_token'         => 'Telegram bot token',
    'agent_created'              => 'Agent created successfully.',
    'agent_updated'              => 'Agent updated.',
    'agent_deleted'              => 'Agent deleted.',
    'agent_needs_machine'        => 'Agent must be assigned to a machine to initialize.',
    'agent_already_initializing' => 'This agent is already initializing.',
    'initialize_openclaw'        => 'Initialize on OpenClaw',
    'resync'                     => 'Resync',
    'agent_initializing'         => 'Initializing…',
    'agent_resyncing'            => 'Resyncing…',
    'status'                     => 'Status',
    'last_sync'                  => 'Last sync',
    'last_task_error'            => 'Error on last task',
    'last_task_success'          => 'Last task succeeded',

    // ── Team cloning ────────────────────────────────────────────────────────
    'clone_team'                 => 'Clone team',
    'new_project'                => 'New project',
    'project_name'               => 'Project name',
    'target_client'              => 'Target client',
    'agents_to_clone'            => 'Agents to clone',
    'clone_new_instance'         => 'Clone (new instance)',
    'reuse_shared'               => 'Reuse (shared)',
    'members_to_clone'           => 'Members to clone',
    'initialize_agents'          => 'Initialize agents on OpenClaw',
    'initialize_agents_help'      => 'Create workspaces and config files for each cloned agent.',
    'memory_cloning'             => 'Memory cloning',
    'memory_core'                => 'Core memory (business knowledge)',
    'memory_company'             => 'Company memory (methodology)',
    'memory_project'             => 'Project memory (decisions & deliverables)',
    'always_cloned'              => 'always cloned',
    'memory_cloning_note'         => 'Selective project memory cloning will be available in a future version.',
    'team_cloned'                => 'Team cloned with {count} agent(s).',
];