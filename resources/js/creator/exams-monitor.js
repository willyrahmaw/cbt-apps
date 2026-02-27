(() => {
    const cfg = window.creatorMonitorConfig || {};
    const url = cfg.dataUrl;
    if (!url) return;

    const tbody = document.getElementById('monitor-tbody');
    const statBelum = document.getElementById('stat-belum');
    const statUjian = document.getElementById('stat-ujian');
    const statSelesai = document.getElementById('stat-selesai');

    function formatTime(seconds) {
        if (!seconds || seconds <= 0) return '-';
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        return m + ':' + String(s).padStart(2, '0');
    }

    function render(students) {
        if (!tbody || !Array.isArray(students)) return;

        let cntBelum = 0, cntUjian = 0, cntSelesai = 0;
        students.forEach(s => {
            if (s.status === 'belum_mulai') cntBelum++;
            else if (s.status === 'sedang_ujian') cntUjian++;
            else cntSelesai++;
        });

        if (statBelum) statBelum.innerHTML = `<span class="font-semibold">${cntBelum}</span> Belum mulai`;
        if (statUjian) statUjian.innerHTML = `<span class="font-semibold">${cntUjian}</span> Sedang ujian`;
        if (statSelesai) statSelesai.innerHTML = `<span class="font-semibold">${cntSelesai}</span> Selesai`;

        tbody.innerHTML = students.map((s, i) => {
            let statusBadge;
            if (s.status === 'belum_mulai') statusBadge = '<span class="px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600">Belum mulai</span>';
            else if (s.status === 'sedang_ujian') statusBadge = '<span class="px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Sedang ujian</span>';
            else if (s.status === 'selesai') statusBadge = '<span class="px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Selesai</span>';
            else statusBadge = '<span class="px-2.5 py-1 rounded-full text-xs font-medium bg-rose-100 text-rose-700">Dihentikan</span>';

            let sisaWaktu = '-';
            if (s.status === 'sedang_ujian' && s.remaining_seconds > 0) {
                sisaWaktu = `<span class="font-mono font-medium text-blue-600" data-remaining="${s.remaining_seconds}">${formatTime(s.remaining_seconds)}</span>`;
            }

            const score = s.score !== null && s.score !== undefined
                ? `<span class="font-semibold ${s.score >= 60 ? 'text-emerald-600' : 'text-rose-600'}">${s.score}</span>`
                : '-';

            const name = s.name || '-';
            const email = s.email || '';
            const cls = s.class || '-';

            return (
                '<tr class="hover:bg-slate-50/50 transition">' +
                `<td class="px-6 py-3 text-sm text-slate-500">${i + 1}</td>` +
                '<td class="px-6 py-3"><div>' +
                `<p class="text-sm font-medium text-slate-700">${name}</p>` +
                `<p class="text-xs text-slate-400">${email}</p>` +
                '</div></td>' +
                `<td class="px-6 py-3"><span class="px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600">${cls}</span></td>` +
                `<td class="px-6 py-3 text-center">${statusBadge}</td>` +
                `<td class="px-6 py-3 text-center font-mono text-sm">${sisaWaktu}</td>` +
                `<td class="px-6 py-3 text-right">${score}</td>` +
                '</tr>'
            );
        }).join('');

        startCountdowns();
    }

    let countdownInterval = null;

    function startCountdowns() {
        if (!tbody) return;
        if (countdownInterval) {
            clearInterval(countdownInterval);
            countdownInterval = null;
        }

        function tick() {
            const cells = tbody.querySelectorAll('[data-remaining]');
            if (!cells.length) {
                if (countdownInterval) {
                    clearInterval(countdownInterval);
                    countdownInterval = null;
                }
                return;
            }

            let hasActive = false;
            cells.forEach(cell => {
                let val = parseInt(cell.dataset.remaining || '0', 10);
                if (val > 0) {
                    val--;
                    cell.dataset.remaining = String(val);
                    cell.textContent = formatTime(val);
                    hasActive = true;
                }
            });

            if (!hasActive && countdownInterval) {
                clearInterval(countdownInterval);
                countdownInterval = null;
            }
        }

        countdownInterval = setInterval(tick, 1000);
    }

    function load() {
        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => { render(data.students || []); })
            .catch(() => {
                if (!tbody) return;
                tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-8 text-center text-rose-500">Gagal memuat data. Coba refresh halaman.</td></tr>';
            });
    }

    load();
    setInterval(load, 5000);
})(); 
