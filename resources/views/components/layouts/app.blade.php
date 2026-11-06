<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Stock') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="h-full antialiased font-sans text-slate-800">
    <div class="min-h-full flex flex-col">
        @auth
            <!-- Navigation Bar -->
            <nav class="bg-indigo-600 text-white shadow-md">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex items-center space-x-8">
                            <a href="{{ auth()->user()->isAdmin() ? route('admin.users') : route('stocks.index') }}" class="flex items-center space-x-2 font-bold text-xl tracking-tight">
                                <svg class="w-7 h-7 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                <span>Stock</span>
                            </a>
                            <div class="hidden sm:flex sm:space-x-4">
                                @if(!auth()->user()->isAdmin())
                                    <a href="{{ route('stocks.index') }}" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-indigo-700 transition {{ request()->routeIs('stocks.*') ? 'bg-indigo-700 text-white' : 'text-indigo-100' }}">
                                        Stok
                                    </a>
                                @endif
                                <a href="{{ route('profile.show') }}" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-indigo-700 transition {{ request()->routeIs('profile.*') ? 'bg-indigo-700 text-white' : 'text-indigo-100' }}">
                                    Profil
                                </a>
                                @if(auth()->user()->isAdmin())
                                    <a href="{{ route('admin.users') }}" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-indigo-700 transition {{ request()->routeIs('admin.*') ? 'bg-indigo-700 text-white' : 'text-indigo-100' }}">
                                        Admin
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center space-x-3">
                                @if(auth()->user()->profile_photo_path)
                                    <img src="{{ Storage::url(auth()->user()->profile_photo_path) }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full object-cover ring-2 ring-white">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-indigo-800 flex items-center justify-center text-xs font-semibold ring-2 ring-white">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                    </div>
                                @endif
                                <div class="hidden md:block text-left">
                                    <p class="text-sm font-medium leading-none">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-indigo-200 mt-0.5">
                                        {{ '@' . auth()->user()->username }} 
                                        <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold {{ auth()->user()->isAdmin() ? 'bg-purple-200 text-purple-900' : 'bg-indigo-800 text-indigo-100' }}">
                                            {{ auth()->user()->role }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 text-xs font-semibold rounded bg-indigo-700 hover:bg-indigo-800 text-white transition focus:outline-none focus:ring-2 focus:ring-white">
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>
        @endauth

        <!-- Main Content Area -->
        <main class="flex-1 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @if (session()->has('success'))
                    <div class="mb-6 rounded-md bg-green-50 p-4 border border-green-200">
                        <div class="flex">
                            <div class="shrink-0 text-green-500">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="mb-6 rounded-md bg-red-50 p-4 border border-red-200">
                        <div class="flex">
                            <div class="shrink-0 text-red-500">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{ $slot }}
            </div>
        </main>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
