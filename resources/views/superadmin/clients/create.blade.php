@extends('layouts.app')
@section('title', __('app.new_client'))
@section('header')
    <div class="flex items-center gap-2 text-sm text-gray-400">
        <a href="{{ route('admin.clients.index') }}" class="hover:text-white">{{ __('app.clients') }}</a>
        <span>/</span>
        <span class="text-white">{{ __('app.new_client') }}</span>
    </div>
@endsection
@section('content')
    <x-page-header title="{{ __('app.new_client_account') }}" />

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('admin.clients.store') }}" class="space-y-6">
            @csrf

            <!-- Company info -->
            <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
                <h3 class="text-white font-semibold mb-5">{{ __('app.company_info') }}</h3>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.company_name') }} *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                        @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.contact_first_name') }} *</label>
                            <input type="text" name="contact_first_name" value="{{ old('contact_first_name') }}" required
                                class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.contact_last_name') }} *</label>
                            <input type="text" name="contact_last_name" value="{{ old('contact_last_name') }}" required
                                class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.email') }} *</label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-500 @enderror">
                            @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.phone') }}</label>
                            <input type="text" name="phone" value="{{ old('phone') }}"
                                class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.address_line1') }}</label>
                        <input type="text" name="address_line1" value="{{ old('address_line1') }}"
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.address_line2') }}</label>
                        <input type="text" name="address_line2" value="{{ old('address_line2') }}"
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.zip_code') }}</label>
                            <input type="text" name="zip_code" value="{{ old('zip_code') }}"
                                class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.city') }}</label>
                            <input type="text" name="city" value="{{ old('city') }}"
                                class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.country_code') }}</label>
                            <input type="text" name="country_code" value="{{ old('country_code', 'FR') }}" maxlength="2"
                                class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.vat_number') }}</label>
                        <input type="text" name="vat_number" value="{{ old('vat_number') }}" placeholder="FR12345678901"
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.internal_notes') }}</label>
                        <textarea name="notes" rows="3"
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Create portal user -->
            <div class="bg-gray-900 rounded-xl border border-gray-800 p-6" x-data="{ createUser: {{ old('create_user') ? 'true' : 'false' }} }">
                <div class="flex items-center gap-3 mb-5">
                    <input type="checkbox" name="create_user" id="create_user" value="1"
                        x-model="createUser" @change="$el.value = createUser ? 1 : 0"
                        class="w-4 h-4 text-indigo-600 bg-gray-800 border-gray-600 rounded focus:ring-indigo-500">
                    <label for="create_user" class="text-white font-semibold">{{ __('app.create_portal_account') }}</label>
                </div>
                <div x-show="createUser" x-cloak class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.name_label') }}</label>
                        <input type="text" name="user_name" value="{{ old('user_name') }}"
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.email_login') }}</label>
                        <input type="email" name="user_email" value="{{ old('user_email') }}"
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.password') }}</label>
                        <input type="password" name="user_password"
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-2.5 rounded-lg transition-colors">
                    {{ __('app.create_client') }}
                </button>
                <a href="{{ route('admin.clients.index') }}"
                    class="bg-gray-800 hover:bg-gray-700 text-gray-300 font-semibold px-6 py-2.5 rounded-lg transition-colors">
                    {{ __('app.cancel') }}
                </a>
            </div>
        </form>
    </div>
@endsection