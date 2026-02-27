<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <script>
        (function() {
            const stored = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (stored === 'dark' || (!stored && prefersDark)) document.documentElement.classList.add('dark');
            else document.documentElement.classList.remove('dark');
        })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Terjadi Kesalahan</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'DM Sans', sans-serif; box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); transition: 0.3s; }
        .dark body { background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%); }
        .card { text-align: center; max-width: 28rem; }
        .illus { width: 140px; height: 140px; margin: 0 auto 1.5rem; }
        .code { font-size: 4.5rem; font-weight: 800; background: linear-gradient(135deg, #1e3a5f, #3b82f6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; letter-spacing: -0.02em; line-height: 1; }
        .dark .code { background: linear-gradient(135deg, #60a5fa, #93c5fd); -webkit-background-clip: text; background-clip: text; }
        h1 { font-size: 1.25rem; font-weight: 600; color: #1e293b; margin: 0.5rem 0 0.5rem; }
        .dark h1 { color: #f1f5f9; }
        .msg { color: #64748b; font-size: 0.9375rem; margin-bottom: 1.5rem; line-height: 1.5; }
        .dark .msg { color: #94a3b8; }
        .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.5rem; background: linear-gradient(135deg, #1e3a5f, #2c5282); color: white; text-decoration: none; border-radius: 0.5rem; font-size: 0.9375rem; font-weight: 500; box-shadow: 0 4px 14px rgba(30,58,95,0.35); transition: transform 0.2s, box-shadow 0.2s; }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(30,58,95,0.4); }
    </style>
</head>
<body>
    <div class="card">
        <div class="illus">
            <svg viewBox="0 0 140 140" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="70" cy="70" r="50" stroke="url(#g500)" stroke-width="3" fill="none" opacity="0.5"/>
                <path d="M55 55l30 30M85 55L55 85" stroke="url(#g500)" stroke-width="3" stroke-linecap="round"/>
                <circle cx="70" cy="70" r="6" fill="url(#g500)"/>
                <defs><linearGradient id="g500" x1="0" y1="0" x2="140" y2="140" gradientUnits="userSpaceOnUse"><stop stop-color="#1e3a5f"/><stop offset="1" stop-color="#3b82f6"/></linearGradient></defs>
            </svg>
        </div>
        <div class="code">500</div>
        <h1>Terjadi Kesalahan</h1>
        <p class="msg">Maaf, ada masalah di server. Silakan coba lagi nanti.</p>
        <a href="{{ url('/') }}" class="btn">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Coba Lagi
        </a>
    </div>
</body>
</html>
