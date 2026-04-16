@extends('layouts.app')
@section('title', __('app.edit') . ' ' . $client->name)
@section('header')
    <div class="flex items-center gap-2 text-sm text-gray-400">
        <a href="{{ route('admin.clients.index') }}" class="hover:text-white">{{ __('app.clients') }}</a>
        <span>/</span>
        <a href="{{ route('admin.clients.show', $client) }}" class="hover:text-white">{{ $client->name }}</a>
        <span>/</span>
        <span class="text-white">{{ __('app.edit') }}</span>
    </div>
@endsection
@section('content')
    <x-page-header title="{{ __('app.edit') }} {{ $client->name }}" />
    <div class="max-w-2xl">
        <form method="POST" action="{{ route('admin.clients.update', $client) }}" class="space-y-6">
            @csrf
            @method('PUT')
            <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
                <h3 class="text-white font-semibold mb-5">{{ __('app.company_info') }}</h3>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.company_name') }} *</label>
                        <input type="text" name="name" value="{{ old('name', $client->name) }}" required
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                        @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.first_name') }} *</label>
                            <input type="text" name="contact_first_name" value="{{ old('contact_first_name', $client->contact_first_name) }}" required
                                class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.last_name') }} *</label>
                            <input type="text" name="contact_last_name" value="{{ old('contact_last_name', $client->contact_last_name) }}" required
                                class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.email') }} *</label>
                            <input type="email" name="email" value="{{ old('email', $client->email) }}" required
                                class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-500 @enderror">
                            @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.phone') }}</label>
                            <input type="text" name="phone" value="{{ old('phone', $client->phone) }}"
                                class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.address_line1') }}</label>
                        <input type="text" name="address_line1" value="{{ old('address_line1', $client->address_line1) }}"
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.address_line2') }}</label>
                        <input type="text" name="address_line2" value="{{ old('address_line2', $client->address_line2) }}"
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.zip_code') }}</label>
                            <input type="text" name="zip_code" value="{{ old('zip_code', $client->zip_code) }}"
                                class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.city') }}</label>
                            <input type="text" name="city" value="{{ old('city', $client->city) }}"
                                class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.country') }}</label>
                            <input type="text" name="country_code" value="{{ old('country_code', $client->country_code) }}" maxlength="2"
                                class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.vat_number_short') }}</label>
                        <input type="text" name="vat_number" value="{{ old('vat_number', $client->vat_number) }}"
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.internal_notes') }}</label>
                        <textarea name="notes" rows="3"
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes', $client->notes) }}</textarea>
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="active" id="active" value="1" {{ old('active', $client->active) ? 'checked' : '' }}
                            class="w-4 h-4 text-indigo-600 bg-gray-800 border-gray-600 rounded focus:ring-indigo-500">
                        <label for="active" class="text-sm text-gray-300">{{ __('app.account_active') }}</label>
                    </div>
                </div>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-2.5 rounded-lg transition-colors">
                    {{ __('app.save') }}
                </button>
                <a href="{{ route('admin.clients.show', $client) }}"
                    class="bg-gray-800 hover:bg-gray-700 text-gray-300 font-semibold px-6 py-2.5 rounded-lg transition-colors">
                    {{ __('app.cancel') }}
                </a>
            </div>
        </form>
    </div>
@endsection