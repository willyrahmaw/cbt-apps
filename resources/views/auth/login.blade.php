<!DOCTYPE html>
<html lang="id" class="h-full">
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
    <title>Login - {{ \App\Models\Setting::get('site_name', 'CBT App') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
        .bg-navy { background-color: #1e3a5f; }
        .bg-navy:hover { background-color: #162d47; }
    </style>
</head>
<body class="min-h-screen bg-slate-100 dark:bg-slate-900 flex items-center justify-center p-6 transition-colors">
    <div class="w-full max-w-md">
        <div class="text-center mb-6">
            <h1 class="text-xl font-semibold text-slate-800 dark:text-slate-100">{{ \App\Models\Setting::get('site_name', 'CBT App') }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Sistem Ujian Berbasis Komputer</p>
        </div>

        <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm p-6">
            <h2 class="text-base font-medium text-slate-800 dark:text-slate-100 mb-4">Masuk</h2>

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700/50 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-[#1e3a5f]/30 focus:border-[#1e3a5f] transition"
                        placeholder="nama@email.com">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Password</label>
                    <input type="password" name="password" required
                        class="w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700/50 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-[#1e3a5f]/30 focus:border-[#1e3a5f] transition"
                        placeholder="••••••••">
                </div>
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="remember" class="w-3.5 h-3.5 rounded border-slate-300 text-[#1e3a5f] focus:ring-[#1e3a5f]">
                    <span class="ml-2 text-sm text-slate-600 dark:text-slate-400">Ingat saya</span>
                </label>
                <button type="submit" class="w-full py-2.5 bg-navy text-white text-sm font-medium rounded-lg hover:opacity-90 transition">
                    Masuk
                </button>
            </form>
        </div>
    </div>
    @if($errors->any())
    <script>Swal.fire({ icon: 'error', title: 'Login Gagal', text: @json($errors->first()) });</script>
    @endif
</body>
</html>
