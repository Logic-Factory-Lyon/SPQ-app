<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@lang('invitation.expired_title') — SPQ</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-950 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-sm text-center">
        <h1 class="text-2xl font-bold text-white mb-2">SPQ</h1>
        <div class="bg-gray-900 rounded-2xl border border-gray-800 p-8 mt-6">
            <div class="w-14 h-14 rounded-full bg-red-900/40 flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 class="text-lg font-semibold text-white mb-2">@lang('invitation.expired_title')</h2>
            <p class="text-gray-400 text-sm">@lang('invitation.expired_message')</p>
            <a href="{{ route('login') }}" class="inline-block mt-6 text-indigo-400 hover:text-indigo-300 text-sm">
                &larr; Retour à la connexion
            </a>
        </div>
    </div>
</body>
</html>
