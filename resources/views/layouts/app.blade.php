<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full bg-gray-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SPQ') — SPQ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <style>
        [x-cloak] { display: none !important; }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #9ca3af;
            transition: color 0.15s, background-color 0.15s;
        }
        .sidebar-link:hover {
            color: #fff;
            background-color: #1f2937;
        }
        .sidebar-link.active {
            color: #fff;
            background-color: #4f46e5;
        }
        .sidebar-link.active:hover {
            background-color: #6366f1;
        }
        .sidebar-section {
            font-size: 0.75rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0 0.75rem;
            margin-bottom: 0.5rem;
            margin-top: 1.5rem;
        }
    </style>
    @stack('styles')
</head>
<body class="h-full" x-data="{ sidebarOpen: false }">
    <div class="flex h-full">

        <!-- Sidebar overlay (mobile) -->
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
             class="fixed inset-0 z-20 bg-black/60 lg:hidden"></div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-30 w-64 bg-gray-900 border-r border-gray-800
                      transform transition-transform duration-200 ease-in-out
                      lg:translate-x-0 lg:static lg:inset-0 flex flex-col">

            <!-- Logo -->
            <div class="flex items-center gap-3 px-4 h-16 border-b border-gray-800 shrink-0">
                <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="text-white font-bold text-lg tracking-tight">SPQ</span>
                <span class="ml-auto text-xs text-gray-600 font-mono">
                    {{ strtoupper(auth()->user()->role) }}
                </span>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-3 py-4">
                @auth
                    @if(auth()->user()->isSuperadmin())
                        @include('layouts.partials.nav-superadmin')
                    @elseif(auth()->user()->isClient())
                        @include('layouts.partials.nav-client')
                    @elseif(auth()->user()->isManager())
                        @include('layouts.partials.nav-manager')
                    @elseif(auth()->user()->isEmployee())
                        @include('layouts.partials.nav-employee')
                    @endif
                @endauth
            </nav>

            <!-- User info -->
            <div class="shrink-0 border-t border-gray-800 px-3 py-4 space-y-3">
                <!-- Locale switcher -->
                <div class="flex items-center gap-1 px-2">
                    @php $currentLocale = auth()->user()->locale ?? 'fr'; @endphp
                    <form method="POST" action="{{ route('locale.switch') }}">
                        @csrf
                        <input type="hidden" name="locale" value="fr">
                        <button type="submit"
                            class="text-xs px-2 py-1 rounded {{ $currentLocale === 'fr' ? 'bg-indigo-600 text-white' : 'text-gray-500 hover:text-gray-300' }} transition-colors">
                            FR
                        </button>
                    </form>
                    <form method="POST" action="{{ route('locale.switch') }}">
                        @csrf
                        <input type="hidden" name="locale" value="en">
                        <button type="submit"
                            class="text-xs px-2 py-1 rounded {{ $currentLocale === 'en' ? 'bg-indigo-600 text-white' : 'text-gray-500 hover:text-gray-300' }} transition-colors">
                            EN
                        </button>
                    </form>
                </div>
                <div class="flex items-center gap-3 px-2">
                    <div class="w-8 h-8 rounded-full bg-indigo-700 flex items-center justify-center text-white text-sm font-bold shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="{{ __('app.logout') }}"
                            class="text-gray-500 hover:text-gray-300 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top bar -->
            <header class="h-16 bg-gray-900 border-b border-gray-800 flex items-center px-4 lg:px-6 shrink-0">
                <!-- Mobile menu button -->
                <button @click="sidebarOpen = true" class="lg:hidden text-gray-400 hover:text-white mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <!-- Breadcrumb / page title -->
                <div class="flex-1">
                    @yield('header')
                </div>

                <!-- Right actions -->
                <div class="flex items-center gap-3">
                    @yield('header-actions')
                </div>
            </header>

            <!-- Flash messages -->
            <div class="px-4 lg:px-6 pt-4 space-y-3">
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                         class="flex items-center gap-3 p-4 bg-green-900/50 border border-green-700 rounded-xl text-green-300 text-sm">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="flex items-center gap-3 p-4 bg-red-900/50 border border-red-700 rounded-xl text-red-300 text-sm">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        {{ session('error') }}
                    </div>
                @endif
            </div>

            <!-- Page content -->
            <main class="flex-1 @yield('main_class', 'overflow-y-auto p-4 lg:p-6')">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
