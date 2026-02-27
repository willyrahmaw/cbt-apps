<!DOCTYPE html>
<html lang="id" class="h-full" id="html-theme">
<head>
    <script>
        (function() {
            const stored = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = stored === 'dark' || (!stored && prefersDark);
            if (isDark) document.documentElement.classList.add('dark');
            else document.documentElement.classList.remove('dark');
        })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CBT') - {{ \App\Models\Setting::get('site_name', 'CBT App') }}</title>
    @php $siteFavicon = \App\Models\Setting::get('site_favicon', ''); @endphp
    @if($siteFavicon)
    <link rel="icon" href="{{ asset('storage/' . $siteFavicon) }}" type="{{ str_ends_with($siteFavicon, '.ico') ? 'image/x-icon' : 'image/png' }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/contrib/auto-render.min.js"></script>
    @stack('styles')
</head>
<body class="h-full bg-slate-50 dark:bg-slate-900 transition-colors">
    <div class="flex h-full">
        {{-- Sidebar --}}
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-30 w-64 transform -translate-x-full bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 transition-transform duration-200 lg:translate-x-0 lg:static lg:inset-auto flex flex-col {{ request()->routeIs('student.exams.take') ? 'hidden lg:hidden' : '' }}">
            {{-- Logo --}}
            @php $siteLogo = \App\Models\Setting::get('site_logo', ''); $siteName = \App\Models\Setting::get('site_name', 'CBT App'); @endphp
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-6 py-5 border-b border-slate-100 dark:border-slate-700 hover:bg-slate-50/50 dark:hover:bg-slate-700/50 transition">
                @if($siteLogo)
                    <img src="{{ asset('storage/' . $siteLogo) }}" alt="Logo" class="w-10 h-10 rounded-xl object-contain border border-slate-200 dark:border-slate-600">
                @else
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow-sm">{{ strtoupper(substr($siteName, 0, 1)) }}</div>
                @endif
                <div>
                    <h1 class="font-bold text-slate-800 dark:text-slate-100 text-lg leading-tight">{{ $siteName }}</h1>
                    <p class="text-xs text-slate-400 dark:text-slate-500">{{ \App\Models\Setting::get('site_description', 'Computer Based Test') }}</p>
                </div>
            </a>

            {{-- Nav --}}
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <a href="{{ route('dashboard') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-300 transition {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>

                @if(auth()->user()->isSuperadmin())
                    <p class="px-3 pt-4 pb-1 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Admin</p>
                    <a href="{{ route('admin.users.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                        Kelola User
                    </a>
                    <a href="{{ route('admin.classes.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition {{ request()->routeIs('admin.classes.index') || request()->routeIs('admin.classes.create') || request()->routeIs('admin.classes.edit') || request()->routeIs('admin.classes.show') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Kelas
                    </a>
                    <a href="{{ route('admin.classes.promote') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition {{ request()->routeIs('admin.classes.promote') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        Kenaikan Kelas
                    </a>
                    <a href="{{ route('admin.categories.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        Kategori
                    </a>
                    <a href="{{ route('admin.results.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition {{ request()->routeIs('admin.results.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Semua Hasil
                    </a>
                    <a href="{{ route('admin.analytics.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Analytics
                    </a>
                    <a href="{{ route('admin.audit-log.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition {{ request()->routeIs('admin.audit-log.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Audit Log
                    </a>
                    <div class="space-y-0.5">
                        <p class="px-3 pt-4 pb-1 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Pengaturan
                        </p>
                        <a href="{{ route('admin.settings.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition {{ request()->routeIs('admin.settings.index') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Tahun Ajaran
                        </a>
                        <a href="{{ route('admin.settings.website') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition {{ request()->routeIs('admin.settings.website') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                            Pengaturan Website
                        </a>
                        <a href="{{ route('admin.backup.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition {{ request()->routeIs('admin.backup.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Backup & Restore
                    </a>
                    </div>
                @endif

                @if(auth()->user()->isPembuatSoal() || auth()->user()->isSuperadmin())
                    <p class="px-3 pt-4 pb-1 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Pembuat Soal</p>
                    <a href="{{ route('creator.exams.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition {{ request()->routeIs('creator.exams.*') || request()->routeIs('creator.questions.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Kelola Ujian
                    </a>
                    <a href="{{ route('creator.question-bank.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition {{ request()->routeIs('creator.question-bank.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
                        Bank Soal
                    </a>
                    <a href="{{ route('creator.results.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition {{ request()->routeIs('creator.results.*') || request()->routeIs('creator.grading.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Hasil Ujian
                    </a>
                @endif

                @if(auth()->user()->isPengguna())
                    <p class="px-3 pt-4 pb-1 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Ujian</p>
                    <a href="{{ route('student.exams.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition {{ request()->routeIs('student.exams.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        Daftar Ujian
                    </a>
                    <a href="{{ route('student.results.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition {{ request()->routeIs('student.results.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        Hasil Ujian
                    </a>
                @endif
            </nav>

            {{-- User Info --}}
            <div class="px-3 py-4 border-t border-slate-100 dark:border-slate-700">
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 transition group">
                    @if(auth()->user()->avatar)
                        <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Avatar" class="w-9 h-9 rounded-full object-cover border border-slate-200 dark:border-slate-600">
                    @else
                        <div class="w-9 h-9 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold text-sm">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500">{{ ucwords(str_replace('_', ' ', auth()->user()->role)) }}</p>
                    </div>
                    <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="mt-1 px-3" id="logout-form">
                    @csrf
                    <button type="button" onclick="confirmAction(document.getElementById('logout-form'), 'Logout?', 'Anda akan keluar dari sistem.', 'Ya, logout', 'question')"
                        class="w-full flex items-center justify-center gap-2 py-2 rounded-lg text-sm text-slate-400 dark:text-slate-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/20 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        {{-- Overlay --}}
        <div id="sidebar-overlay" class="fixed inset-0 z-20 bg-black/50 hidden lg:hidden" onclick="toggleSidebar()"></div>

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col min-h-screen overflow-x-hidden">
            {{-- Top Bar --}}
            <header class="sticky top-0 z-10 glass border-b border-slate-200 dark:border-slate-700 px-4 lg:px-8 py-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">@yield('header', 'Dashboard')</h2>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="toggleDarkMode()" class="p-2 rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition" title="Ganti tema">
                            <svg id="icon-sun" class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <svg id="icon-moon" class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        </button>
                        @yield('header-actions')
                    </div>
                </div>
            </header>

            {{-- Page Content --}}
            <main class="flex-1 p-4 lg:p-8">
                @yield('content')
            </main>
            @php $siteFooter = \App\Models\Setting::get('site_footer', ''); @endphp
            @if($siteFooter)
                <footer class="px-4 lg:px-8 py-3 border-t border-slate-200 dark:border-slate-700 text-center text-xs text-slate-400 dark:text-slate-500">
                    {{ $siteFooter }}
                </footer>
            @endif
        </div>
    </div>

    {{-- Image Preview Modal --}}
    <div id="image-preview-modal" class="fixed inset-0 z-50 hidden flex flex-col items-center justify-center bg-black/90 p-6 overflow-hidden" onclick="closeImagePreview(event)">
        <div class="absolute top-0 left-0 right-0 h-12 flex items-center justify-between px-4 bg-black/50 border-b border-white/10 z-20">
            <span id="preview-zoom-label" class="text-white/90 text-sm tabular-nums">100%</span>
            <div class="flex items-center gap-1">
                <button type="button" onclick="event.stopPropagation(); previewZoomOut();" class="w-8 h-8 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/10 rounded" title="Zoom out">−</button>
                <button type="button" onclick="event.stopPropagation(); previewZoomIn();" class="w-8 h-8 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/10 rounded" title="Zoom in">+</button>
                <button type="button" onclick="event.stopPropagation(); previewZoomReset();" class="w-8 h-8 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/10 rounded text-xs" title="Reset">1:1</button>
                <button type="button" onclick="event.stopPropagation(); closeImagePreview();" class="w-8 h-8 flex items-center justify-center text-white/80 hover:text-red-400 hover:bg-red-500/20 rounded ml-1" title="Tutup">✕</button>
            </div>
        </div>
        <div id="preview-container" class="preview-frame relative z-10 mt-12 overflow-auto flex items-center justify-center cursor-grab active:cursor-grabbing bg-neutral-900" onmousedown="previewPanStart(event)" onmousemove="previewPan(event)" onmouseup="previewPanEnd()" onmouseleave="previewPanEnd()" onclick="event.stopPropagation()">
            <div id="preview-loading" class="absolute inset-0 flex items-center justify-center text-white/40">
                <svg class="animate-spin w-8 h-8" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            </div>
            <div id="preview-wrapper" class="inline-block min-w-0 min-h-0">
                <img id="preview-img" src="" alt="Preview" class="preview-img select-none pointer-events-none block max-w-none" style="display:block;">
            </div>
        </div>
    </div>

    <script>
        @if(session('success'))
            document.addEventListener('DOMContentLoaded', () => { if (window.Toast) window.Toast.fire({ icon: 'success', title: @json(session('success')) }); });
        @endif
        @if(session('error'))
            document.addEventListener('DOMContentLoaded', () => { if (window.Swal) window.Swal.fire({ icon: 'error', title: 'Terjadi Kesalahan', text: @json(session('error')) }); });
        @endif
        @if(session('info'))
            document.addEventListener('DOMContentLoaded', () => { if (window.Swal) window.Swal.fire({ icon: 'info', title: 'Informasi', text: @json(session('info')) }); });
        @endif
        @if($errors->any())
            document.addEventListener('DOMContentLoaded', () => {
                if (window.Swal) window.Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    html: {!! json_encode('<ul class="text-left list-disc list-inside space-y-1">' . collect($errors->all())->map(fn($e) => '<li>'.e($e).'</li>')->implode('') . '</ul>') !!}
                });
            });
        @endif
    </script>
    @stack('scripts')
    @if(session('welcome'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: @json(session('welcome')),
                    showConfirmButton: false,
                    timer: 3200,
                    timerProgressBar: true,
                });
            });
        </script>
    @endif
</body>
</html>
