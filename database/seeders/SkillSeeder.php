<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        $skills = [
            [
                'name' => 'Tester un site web',
                'slug' => 'test-website',
                'description' => 'Effectue un audit de sécurité ou de performance sur un site web.',
                'icon' => 'o-globe-alt',
                'category' => 'Audit',
                'handler_type' => 'native_tool',
                'prompt_template' => "Tu es un expert QA. Utilise les tools SPQ pour exécuter les tests. Si incertitude ou erreur bloquante, appelle spq_ask_human.",
                'parameter_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'start_url'    => ['type' => 'string', 'format' => 'uri', 'description' => 'URL de départ'],
                        'login'        => ['type' => 'string', 'description' => 'Login (optionnel)'],
                        'password'     => ['type' => 'string', 'description' => 'Mot de passe (optionnel)'],
                        'instructions' => ['type' => 'string', 'description' => 'Instructions détaillées. L\'agent peut s\'arrêter et demander confirmation humaine.'],
                    ],
                    'required' => ['start_url', 'instructions'],
                ],
                'output_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'summary'        => ['type' => 'string'],
                        'issues_found'   => ['type' => 'array'],
                        'recommendations' => ['type' => 'array'],
                        'artifacts'      => ['type' => 'array'],
                    ],
                ],
                'allowed_tools' => ['spq_run_website_test', 'spq_create_document', 'spq_update_project_memory', 'spq_ask_human'],
                'action_handlers' => ['run_website_test', 'create_document', 'update_project_memory'],
                'version' => 2,
            ],
            [
                'name' => 'Rédiger une documentation',
                'slug' => 'write-doc',
                'description' => 'Génère une documentation technique à partir du code existant.',
                'icon' => 'o-document-text',
                'category' => 'Documentation',
                'handler_type' => 'composite',
                'prompt_template' => "Rédige une documentation {{doc_type}} pour le projet. Contexte : {{context}}. Analyse le code existant et produis une documentation claire, structurée, avec des exemples d'utilisation. Format : Markdown. Utilise spq_create_document pour sauvegarder le résultat.",
                'parameter_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'doc_type' => ['type' => 'string', 'description' => 'Type de documentation (ex: API, architecture, utilisateur)', 'enum' => ['API', 'architecture', 'utilisateur', 'technique', 'procédure']],
                        'context'  => ['type' => 'string', 'description' => 'Contexte ou périmètre de la documentation'],
                    ],
                    'required' => ['doc_type', 'context'],
                ],
                'allowed_tools' => ['Glob', 'Grep', 'Read', 'spq_create_document'],
                'action_handlers' => ['create_document'],
                'version' => 2,
            ],
            [
                'name' => 'Analyser du code',
                'slug' => 'analyze-code',
                'description' => 'Effectue une revue de code et identifie les problèmes.',
                'icon' => 'o-code-bracket',
                'category' => 'Développement',
                'handler_type' => 'prompt',
                'prompt_template' => "Analyse le code du projet en te concentrant sur : {{focus}}. Identifie les bugs potentiels, les problèmes de sécurité, les violations de conventions, et les opportunités d'optimisation. Fournis un rapport avec sévérité et suggestions de corrections.",
                'parameter_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'focus' => ['type' => 'string', 'description' => 'Aspect à analyser (ex: sécurité, performance, conventions)'],
                    ],
                    'required' => ['focus'],
                ],
                'allowed_tools' => ['Glob', 'Grep', 'Read'],
                'action_handlers' => null,
                'version' => 2,
            ],
            [
                'name' => 'Rechercher des informations',
                'slug' => 'search-info',
                'description' => 'Effectue une recherche approfondie sur un sujet donné.',
                'icon' => 'o-magnifying-glass',
                'category' => 'Recherche',
                'handler_type' => 'composite',
                'prompt_template' => "Recherche des informations approfondies sur le sujet : {{subject}}. Contexte : {{context}}. Synthétise les résultats de manière structurée avec les sources. Utilise spq_search_info et spq_create_document si pertinent.",
                'parameter_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'subject'  => ['type' => 'string', 'description' => 'Sujet de la recherche'],
                        'context'  => ['type' => 'string', 'description' => 'Contexte additionnel (optionnel)'],
                    ],
                    'required' => ['subject'],
                ],
                'allowed_tools' => ['WebSearch', 'WebFetch', 'spq_search_info', 'spq_create_document'],
                'action_handlers' => ['search_info', 'create_document'],
                'version' => 2,
            ],
        ];

        foreach ($skills as $skill) {
            Skill::updateOrCreate(
                ['slug' => $skill['slug']],
                $skill
            );
        }
    }
}