@extends('layouts.app')
@section('title', __('email_templates.title'))
@section('content')
    <x-page-header title="{{ __('email_templates.title') }}" />

    <div class="space-y-6">
        <!-- Email Templates -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-800">
                <h3 class="font-semibold text-white">@lang('email_templates.title')</h3>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-800">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase">@lang('email_templates.key')</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase">@lang('email_templates.lang')</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase">@lang('email_templates.subject')</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @foreach($templates as $key => $group)
                        @foreach($group as $template)
                        <tr>
                            <td class="px-5 py-3 font-mono text-gray-300 text-xs">{{ $template->key }}</td>
                            <td class="px-5 py-3">
                                <x-badge color="{{ $template->lang === 'fr' ? 'indigo' : 'gray' }}">{{ strtoupper($template->lang) }}</x-badge>
                            </td>
                            <td class="px-5 py-3 text-gray-300">{{ $template->subject }}</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('admin.email-templates.edit', [$template->key, $template->lang]) }}"
                                   class="text-indigo-400 hover:text-indigo-300 text-xs font-medium">@lang('email_templates.edit')</a>
                            </td>
                        </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Footer Settings -->
        <div class="bg-gray-900 rounded-xl border border-gray-800">
            <div class="px-5 py-4 border-b border-gray-800">
                <h3 class="font-semibold text-white">@lang('email_templates.footer')</h3>
            </div>
            <div class="divide-y divide-gray-800">
                @foreach(['fr', 'en'] as $lang)
                <div class="flex items-center justify-between px-5 py-4">
                    <div>
                        <p class="text-white font-medium text-sm">{{ strtoupper($lang) }}</p>
                        @if(isset($settings[$lang]) && $settings[$lang]->footer_html)
                            <p class="text-gray-500 text-xs mt-0.5 truncate max-w-md">{{ strip_tags($settings[$lang]->footer_html) }}</p>
                        @else
                            <p class="text-gray-600 text-xs mt-0.5 italic">Aucun pied de page configuré</p>
                        @endif
                    </div>
                    <a href="{{ route('admin.email-templates.footer.edit', $lang) }}"
                       class="text-indigo-400 hover:text-indigo-300 text-xs font-medium">@lang('email_templates.edit_footer')</a>
                </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
