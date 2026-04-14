<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TemplateMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly array $rendered
    ) {}

    public function build(): static
    {
        return $this
            ->subject($this->rendered['subject'])
            ->view('emails.template', [
                'body'   => $this->rendered['body'],
                'footer' => $this->rendered['footer'] ?? '',
            ]);
    }
}
