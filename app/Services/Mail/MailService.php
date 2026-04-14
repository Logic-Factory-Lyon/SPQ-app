<?php
namespace App\Services\Mail;

use App\Mail\TemplateMail;
use App\Models\EmailSetting;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class MailService
{
    public function send(string $key, string|User $recipient, array $vars = []): bool
    {
        $email = $recipient instanceof User ? $recipient->email : $recipient;
        $lang  = $recipient instanceof User ? ($recipient->locale ?? 'fr') : 'fr';

        $rendered = EmailTemplate::render($key, $lang, $vars);
        if (! $rendered) {
            \Log::warning("Email template '{$key}' not found for lang '{$lang}'");
            return false;
        }

        $footer = EmailSetting::footerFor($lang);
        $rendered['footer'] = $footer;

        Mail::to($email)->send(new TemplateMail($rendered));
        return true;
    }
}
