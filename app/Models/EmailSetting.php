<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailSetting extends Model
{
    protected $fillable = ['lang', 'footer_html'];

    public static function footerFor(string $lang): string
    {
        return static::where('lang', $lang)->value('footer_html')
            ?? static::where('lang', 'fr')->value('footer_html')
            ?? '';
    }
}
