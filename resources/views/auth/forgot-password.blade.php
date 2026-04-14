<!DOCTYPE html>
<html lang="fr" class="h-full bg-gray-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié — SPQ</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full flex items-center justify-center">
    <div class="w-full max-w-md px-6">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-600 rounded-2xl mb-4">
                <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-white">SPQ</h1>
        </div>

        <div class="bg-gray-900 rounded-2xl shadow-2xl p-8 border border-gray-800">
            <h2 class="text-xl font-semibold text-white mb-2">Réinitialisation du mot de passe</h2>
            <p class="text-gray-400 text-sm mb-6">Entrez votre email pour recevoir un lien de réinitialisation.</p>

            @if (session('status'))
                <div class="mb-4 p-3 bg-green-900/50 border border-green-700 rounded-lg text-green-300 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Adresse email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                               @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2.5 px-4 rounded-lg transition-colors">
                    Envoyer le lien
                </button>
            </form>

            <div class="mt-4 text-center">
                <a href="{{ route('login') }}" class="text-sm text-indigo-400 hover:text-indigo-300">&larr; Retour à la connexion</a>
            </div>
        </div>
    </div>
</body>
</html>
