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
                'prompt_template' => "Effectue un test de sécurité et de performance sur le site {{url}}. Concentre-toi sur : {{focus}}. Analyse les vulnérabilités courantes (OWASP Top 10), les temps de réponse, et la conformité aux bonnes pratiques. Fournis un rapport structuré avec sévérité et recommandations.",
                'allowed_tools' => ['WebFetch', 'WebSearch', 'Bash'],
            ],
            [
                'name' => 'Rédiger une documentation',
                'slug' => 'write-doc',
                'description' => 'Génère une documentation technique à partir du code existant.',
                'icon' => 'o-document-text',
                'category' => 'Documentation',
                'prompt_template' => "Rédige une documentation {{doc_type}} pour le projet. Contexte : {{context}}. Analyse le code existant et produis une documentation claire, structurée, avec des exemples d'utilisation. Format : Markdown.",
                'allowed_tools' => ['Glob', 'Grep', 'Read'],
            ],
            [
                'name' => 'Analyser du code',
                'slug' => 'analyze-code',
                'description' => 'Effectue une revue de code et identifie les problèmes.',
                'icon' => 'o-code-bracket',
                'category' => 'Développement',
                'prompt_template' => "Analyse le code du projet en te concentrant sur : {{focus}}. Identifie les bugs potentiels, les problèmes de sécurité, les violations de conventions, et les opportunités d'optimisation. Fournis un rapport avec sévérité et suggestions de corrections.",
                'allowed_tools' => ['Glob', 'Grep', 'Read'],
            ],
            [
                'name' => 'Rechercher des informations',
                'slug' => 'search-info',
                'description' => 'Effectue une recherche approfondie sur un sujet donné.',
                'icon' => 'o-magnifying-glass',
                'category' => 'Recherche',
                'prompt_template' => "Recherche des informations approfondies sur le sujet : {{subject}}. Contexte : {{context}}. Synthétise les résultats de manière structurée avec les sources.",
                'allowed_tools' => ['WebSearch', 'WebFetch'],
            ],
        ];

        foreach ($skills as $skill) {
            Skill::firstOrCreate(
                ['slug' => $skill['slug']],
                $skill
            );
        }
    }
}