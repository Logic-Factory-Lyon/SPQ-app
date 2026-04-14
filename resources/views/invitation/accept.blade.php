<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@lang('invitation.accept') — SPQ</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-950 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-white">SPQ</h1>
        </div>

        <div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden">
            <div class="bg-indigo-600 px-6 py-5">
                <p class="text-indigo-200 text-sm">@lang('invitation.invited_to')</p>
                <h2 class="text-xl font-bold text-white mt-1">{{ $invitation->project->name }}</h2>
                <p class="text-indigo-200 text-sm mt-1">
                    @lang('invitation.your_role') :
                    <span class="font-semibold text-white">@lang('invitation.role_' . $invitation->role)</span>
                </p>
            </div>

            @if(session('error'))
                <div class="mx-6 mt-4 bg-red-900/40 border border-red-700 text-red-300 text-sm px-4 py-3 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            @if($existingUser)
                <!-- Login form -->
                <div class="px-6 py-6">
                    <h3 class="font-semibold text-white mb-4">@lang('invitation.login_existing')</h3>
                    <form method="POST" action="{{ route('invitation.login', $invitation->token) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1">Email</label>
                            <input type="email" name="email" value="{{ $invitation->email }}" readonly
                                   class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-400 cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1">@lang('invitation.password')</label>
                            <input type="password" name="password" required autofocus
                                   class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('password') border-red-500 @enderror">
                            @error('password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2.5 rounded-lg transition-colors">
                            @lang('invitation.submit_login')
                        </button>
                    </form>
                </div>
            @else
                <!-- Registration form -->
                <div class="px-6 py-6">
                    <h3 class="font-semibold text-white mb-4">@lang('invitation.create_account')</h3>
                    <form method="POST" action="{{ route('invitation.register', $invitation->token) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1">Email</label>
                            <input type="email" value="{{ $invitation->email }}" readonly
                                   class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-400 cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1">@lang('invitation.name')</label>
                            <input type="text" name="name" value="{{ old('name') }}" required autofocus
                                   class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                            @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1">@lang('invitation.password')</label>
                            <input type="password" name="password" required
                                   class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('password') border-red-500 @enderror">
                            @error('password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1">@lang('invitation.password_confirm')</label>
                            <input type="password" name="password_confirmation" required
                                   class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2.5 rounded-lg transition-colors">
                            @lang('invitation.submit_register')
                        </button>
                    </form>
                </div>
            @endif

            <div class="border-t border-gray-800 px-6 py-4 text-center">
                <p class="text-xs text-gray-500">
                    @lang('members.invitation_expires') {{ $invitation->expires_at->format('d/m/Y') }}
                </p>
            </div>
        </div>
    </div>
</body>
</html>
