<?php

return [

    // ── Brand & general ────────────────────────────────────────────────────
    'brand'          => 'SPQ',
    'tagline'        => 'Plateforme de gestion d\'agents IA',
    'all_rights'     => 'Tous droits réservés',

    // ── Auth ──────────────────────────────────────────────────────────────
    'login'          => 'Connexion',
    'logout'         => 'Déconnexion',
    'email'          => 'Adresse email',
    'password'       => 'Mot de passe',
    'forgot_password'=> 'Mot de passe oublié ?',
    'remember_me'    => 'Se souvenir de moi',
    'sign_in'        => 'Se connecter',

    // ── Sidebar nav ───────────────────────────────────────────────────────
    'dashboard'      => 'Tableau de bord',

    // Employee
    'my_agent'       => 'Mon agent',

    // Client
    'my_projects'    => 'Mes projets',
    'projects_teams' => 'Projets & équipes',
    'billing'        => 'Facturation',
    'quotes'         => 'Devis',
    'invoices'       => 'Factures',

    // Manager
    'team'           => 'Équipe',
    'team_conversations'=> 'Conversations équipe',

    // Superadmin
    'clients'        => 'Clients',
    'client_accounts'=> 'Comptes clients',
    'projects'       => 'Projets',
    'users'          => 'Utilisateurs',
    'payments'       => 'Paiements',
    'communication'  => 'Communication',
    'email_templates'=> 'Modèles d\'e-mails',
    'settings'       => 'Paramètres',
    'skills'         => 'Skills',
    'service_catalog'=> 'Catalogue services',
    'vat_rates'      => 'Taux de TVA',

    // ── Dashboard (employee) ──────────────────────────────────────────────
    'hello'                   => 'Bonjour, :name',
    'project_label'           => 'Projet : :name',
    'no_project'              => 'Vous n\'êtes associé à aucun projet.',
    'no_agent'                => 'Aucun agent assigné — contactez votre manager.',
    'available'               => 'disponible',
    'offline'                 => 'hors ligne',
    'contact_manager'         => 'Contactez votre manager pour être associé à un projet.',
    'start_conversation'      => 'Démarrer une conversation',
    'recent_conversations'    => 'Conversations récentes',
    'see_all'                 => 'Voir tout →',

    // ── Conversations ────────────────────────────────────────────────────
    'conversations'           => 'Conversations',
    'new_conversation'       => 'Nouvelle conversation',
    'new_conversation_btn'   => '+ Nouvelle conversation',
    'delete'                  => 'Supprimer',
    'delete_conversation'     => 'Supprimer cette conversation ?',
    'no_conversations'        => 'Aucune conversation',
    'start_new_conversation'  => 'Démarrez une nouvelle conversation avec votre agent.',
    'title_optional'          => 'Titre (optionnel)',
    'title_placeholder'       => 'Ex: Analyse des ventes Q1...',
    'create'                  => 'Créer',
    'cancel'                   => 'Annuler',
    'conversation_of'          => 'Conversation du :date',

    // ── Chat ──────────────────────────────────────────────────────────────
    'online'            => 'en ligne',
    'chat_placeholder'  => 'Écrivez votre message... (Entrée pour envoyer, Maj+Entrée pour aller à la ligne)',
    'enter_to_send'     => 'Entrée pour envoyer · Maj+Entrée pour nouvelle ligne',
    'skill'             => 'Skill',
    'hide'              => 'Masquer',
    'show'              => 'Afficher',
    'launch'            => 'Lancer',
    'no_agent_configured'=> 'Profil OpenClaw non configuré.',

    // ── Telegram ──────────────────────────────────────────────────────────
    'telegram_description'     => 'Votre agent est disponible sur Telegram. Cliquez ci-dessous pour ouvrir le chat directement dans l\'application.',
    'open_telegram'            => 'Ouvrir le chat Telegram',
    'telegram_note'           => 'Les conversations se passent directement dans Telegram.',
    'agent_online'            => 'en ligne',
    'agent_offline'           => 'hors ligne',

    // ── Common actions ─────────────────────────────────────────────────────
    'save'           => 'Enregistrer',
    'edit'           => 'Modifier',
    'add'            => 'Ajouter',
    'back'           => 'Retour',
    'confirm'        => 'Confirmer',
    'search'         => 'Rechercher...',

    // ── Agent management ───────────────────────────────────────────────────
    'agents'                    => 'Agents OpenClaw',
    'add_agent'                 => '+ Nouvel agent',
    'agent_name'                => 'Nom de l\'agent',
    'agent_name_placeholder'    => 'Ex: oracle-erp-specialist',
    'agent_profile'             => 'Profil (slug)',
    'agent_profile_placeholder'=> 'Ex: oracle-erp-v1',
    'agent_profile_help'        => 'Identifiant unique utilisé par OpenClaw pour identifier cet agent.',
    'description'               => 'Description',
    'machine'                   => 'Machine',
    'no_machine'                => 'Aucune machine',
    'create_new_machine'        => 'Créer une nouvelle machine',
    'parent_agent'              => 'Agent parent',
    'no_parent'                 => 'Aucun (racine)',
    'system_prompt'             => 'Prompt système',
    'system_prompt_help'         => 'Instructions de base injectées dans le contexte de l\'agent au démarrage.',
    'workspace_path'             => 'Chemin du workspace',
    'telegram_settings'          => 'Paramètres Telegram',
    'telegram_bot_username'      => 'Nom d\'utilisateur du bot Telegram',
    'telegram_bot_token'         => 'Token du bot Telegram',
    'agent_created'              => 'Agent créé avec succès.',
    'agent_updated'              => 'Agent mis à jour.',
    'agent_deleted'              => 'Agent supprimé.',
    'agent_needs_machine'        => 'L\'agent doit être associé à une machine pour être initialisé.',
    'agent_already_initializing' => 'Cet agent est déjà en cours d\'initialisation.',
    'initialize_openclaw'        => 'Initialiser sur OpenClaw',
    'resync'                     => 'Resync',
    'agent_initializing'         => 'Initialisation en cours…',
    'agent_resyncing'            => 'Resynchronisation en cours…',
    'status'                     => 'Statut',
    'last_sync'                  => 'Dernier sync',
    'last_task_error'            => 'Erreur lors de la dernière tâche',
    'last_task_success'          => 'Dernière tâche réussie',

    // ── Team cloning ────────────────────────────────────────────────────────
    'clone_team'                 => 'Cloner l\'équipe',
    'new_project'                => 'Nouveau projet',
    'project_name'               => 'Nom du projet',
    'target_client'              => 'Client cible',
    'agents_to_clone'            => 'Agents à cloner',
    'clone_new_instance'         => 'Cloner (nouvelle instance)',
    'reuse_shared'               => 'Réutiliser (partagé)',
    'members_to_clone'           => 'Membres à cloner',
    'initialize_agents'          => 'Initialiser les agents sur OpenClaw',
    'initialize_agents_help'      => 'Créer les workspaces et fichiers de configuration pour chaque agent cloné.',
    'memory_cloning'             => 'Clonage mémoire',
    'memory_core'                => 'Mémoire Core (connaissances métier)',
    'memory_company'             => 'Mémoire Société (méthodologie)',
    'memory_project'             => 'Mémoire Projet (décisions & livrables)',
    'always_cloned'              => 'toujours clonée',
    'memory_cloning_note'         => 'Le clonage sélectif de la mémoire projet sera disponible dans une prochaine version.',
    'team_cloned'                => 'Équipe clonée avec {count} agent(s).',
];