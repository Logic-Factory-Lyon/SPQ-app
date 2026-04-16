@extends('layouts.app')
@section('title', __('email_templates.edit_footer') . ' — ' . strtoupper($lang))
@section('header')
    <div class="flex items-center gap-2 text-sm text-gray-400">
        <a href="{{ route('admin.email-templates.index') }}" class="hover:text-white">@lang('email_templates.title')</a>
        <span>/</span>
        <span class="text-white">@lang('email_templates.footer')</span>
        <x-badge color="{{ $lang === 'fr' ? 'indigo' : 'gray' }}">{{ strtoupper($lang) }}</x-badge>
    </div>
@endsection
@section('content')
    <form method="POST" action="{{ route('admin.email-templates.footer.update', $lang) }}" class="max-w-2xl space-y-5">
        @csrf @method('PUT')

        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
            <label class="block text-xs font-semibold text-gray-400 uppercase mb-2">@lang('email_templates.footer_html')</label>
            <textarea name="footer_html" rows="8"
                      class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('footer_html', $setting->footer_html ?? '') }}</textarea>
            <p class="text-gray-600 text-xs mt-1">{{ __('app.html_allowed_footer') }}</p>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.email-templates.index') }}"
               class="bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                {{ __('app.cancel') }}
            </a>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold px-5 py-2 rounded-lg transition-colors">
                {{ __('app.save') }}
            </button>
        </div>
    </form>
@endsection
