<?php
namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\EmailSetting;
use App\Models\EmailTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailTemplateController extends Controller
{
    public function index(): View
    {
        $templates = EmailTemplate::orderBy('key')->orderBy('lang')->get()->groupBy('key');
        $settings  = EmailSetting::all()->keyBy('lang');
        return view('superadmin.email-templates.index', compact('templates', 'settings'));
    }

    public function edit(string $key, string $lang): View
    {
        $template = EmailTemplate::where('key', $key)->where('lang', $lang)->firstOrFail();
        return view('superadmin.email-templates.edit', compact('template'));
    }

    public function update(Request $request, string $key, string $lang): RedirectResponse
    {
        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'body'    => 'required|string',
        ]);

        EmailTemplate::where('key', $key)->where('lang', $lang)->update($data);

        return redirect()->route('admin.email-templates.index')
            ->with('success', __('email_templates.updated'));
    }

    public function editFooter(string $lang): View
    {
        $setting = EmailSetting::firstOrNew(['lang' => $lang]);
        return view('superadmin.email-templates.footer', compact('setting', 'lang'));
    }

    public function updateFooter(Request $request, string $lang): RedirectResponse
    {
        $data = $request->validate([
            'footer_html' => 'nullable|string',
        ]);

        EmailSetting::updateOrCreate(['lang' => $lang], ['footer_html' => $data['footer_html'] ?? '']);

        return redirect()->route('admin.email-templates.index')
            ->with('success', __('email_templates.footer_updated'));
    }
}
