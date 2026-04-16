@extends('layouts.app')
@section('title', __('app.edit') . ' ' . $vatRate->name)
@section('header')
    <div class="flex items-center gap-2 text-sm text-gray-400">
        <a href="{{ route('admin.vat-rates.index') }}" class="hover:text-white">{{ __('app.vat_rates') }}</a>
        <span>/</span>
        <span class="text-white">{{ $vatRate->name }}</span>
    </div>
@endsection
@section('content')
    <x-page-header title="{{ __('app.edit') }} {{ $vatRate->name }}" />
    <div class="max-w-sm">
        <form method="POST" action="{{ route('admin.vat-rates.update', $vatRate) }}" class="space-y-5">
            @csrf
            @method('PUT')
            <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 space-y-4">
                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.label') }} *</label>
                    <input type="text" name="name" value="{{ old('name', $vatRate->name) }}" required
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                    @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.rate_decimal') }} *</label>
                    <input type="number" name="rate" value="{{ old('rate', $vatRate->rate) }}" required
                        min="0" max="1" step="0.001"
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('rate') border-red-500 @enderror">
                    @error('rate')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    <p class="text-xs text-gray-500 mt-1">{{ __('app.rate_example') }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="active" id="active" value="1" {{ old('active', $vatRate->active) ? 'checked' : '' }}
                        class="w-4 h-4 text-indigo-600 bg-gray-800 border-gray-600 rounded focus:ring-indigo-500">
                    <label for="active" class="text-sm text-gray-300">{{ __('app.vat_rate_active') }}</label>
                </div>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-2.5 rounded-lg transition-colors">
                    {{ __('app.save') }}
                </button>
                <a href="{{ route('admin.vat-rates.index') }}"
                    class="bg-gray-800 hover:bg-gray-700 text-gray-300 font-semibold px-6 py-2.5 rounded-lg transition-colors">
                    {{ __('app.cancel') }}
                </a>
                @if(!$vatRate->is_default)
                    <button type="button" class="bg-red-900/50 hover:bg-red-900 text-red-400 font-semibold px-6 py-2.5 rounded-lg transition-colors ml-auto"
                        onclick="if(confirm('{{ __("app.delete_vat_rate_confirm") }}')) document.getElementById('delete-form-vat-rate').submit()">
                        {{ __('app.delete') }}
                    </button>
                @endif
            </div>
        </form>

        @if(!$vatRate->is_default)
            <form id="delete-form-vat-rate" method="POST" action="{{ route('admin.vat-rates.destroy', $vatRate) }}" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif
    </div>
@endsection
