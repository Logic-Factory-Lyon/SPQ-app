<?php
namespace Database\Seeders;

use App\Models\EmailTemplate;
use App\Models\EmailSetting;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            // ── invitation ─────────────────────────────────────────────
            [
                'key'  => 'invitation',
                'lang' => 'fr',
                'subject' => 'Vous avez été invité à rejoindre {{project_name}}',
                'body' => '<p>Bonjour,</p>
<p><strong>{{inviter_name}}</strong> vous invite à rejoindre le projet <strong>{{project_name}}</strong> sur SPQ.</p>
<p>Cliquez sur le bouton ci-dessous pour accepter l\'invitation et créer votre compte :</p>
<p><a href="{{invitation_url}}" class="btn">Accepter l\'invitation</a></p>
<p>Ce lien est valable jusqu\'au <strong>{{expires_at}}</strong>.</p>
<p>Si vous n\'attendiez pas cette invitation, vous pouvez ignorer cet e-mail.</p>',
            ],
            [
                'key'  => 'invitation',
                'lang' => 'en',
                'subject' => 'You have been invited to join {{project_name}}',
                'body' => '<p>Hello,</p>
<p><strong>{{inviter_name}}</strong> has invited you to join the project <strong>{{project_name}}</strong> on SPQ.</p>
<p>Click the button below to accept the invitation and create your account:</p>
<p><a href="{{invitation_url}}" class="btn">Accept invitation</a></p>
<p>This link is valid until <strong>{{expires_at}}</strong>.</p>
<p>If you were not expecting this invitation, you can safely ignore this email.</p>',
            ],

            // ── member_added ────────────────────────────────────────────
            [
                'key'  => 'member_added',
                'lang' => 'fr',
                'subject' => 'Vous avez été ajouté au projet {{project_name}}',
                'body' => '<p>Bonjour {{user_name}},</p>
<p><strong>{{inviter_name}}</strong> vous a ajouté au projet <strong>{{project_name}}</strong> sur SPQ.</p>
<p><a href="{{app_url}}" class="btn">Accéder à SPQ</a></p>',
            ],
            [
                'key'  => 'member_added',
                'lang' => 'en',
                'subject' => 'You have been added to the project {{project_name}}',
                'body' => '<p>Hello {{user_name}},</p>
<p><strong>{{inviter_name}}</strong> has added you to the project <strong>{{project_name}}</strong> on SPQ.</p>
<p><a href="{{app_url}}" class="btn">Go to SPQ</a></p>',
            ],
        ];

        foreach ($templates as $tpl) {
            EmailTemplate::updateOrCreate(
                ['key' => $tpl['key'], 'lang' => $tpl['lang']],
                ['subject' => $tpl['subject'], 'body' => $tpl['body']]
            );
        }

        // Default footers
        EmailSetting::updateOrCreate(['lang' => 'fr'], [
            'footer_html' => '<p>SPQ — <a href="https://spq.app">spq.app</a><br>Vous recevez cet e-mail car vous êtes membre ou invité sur la plateforme SPQ.</p>',
        ]);
        EmailSetting::updateOrCreate(['lang' => 'en'], [
            'footer_html' => '<p>SPQ — <a href="https://spq.app">spq.app</a><br>You are receiving this email because you are a member or invitee on the SPQ platform.</p>',
        ]);
    }
}
