<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = ['key', 'lang', 'subject', 'body'];

    public static function render(string $key, string $lang, array $vars = []): ?array
    {
        $template = static::where('key', $key)->where('lang', $lang)->first();
        if (! $template) {
            // Fallback to FR
            $template = static::where('key', $key)->where('lang', 'fr')->first();
        }
        if (! $template) return null;

        $replace = function (string $text) use ($vars): string {
            foreach ($vars as $k => $v) {
                $text = str_replace('{{' . $k . '}}', $v, $text);
            }
            return $text;
        };

        return [
            'subject' => $replace($template->subject),
            'body'    => $replace($template->body),
        ];
    }
}
