<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Hasil Ujian - {{ $exam->title }}</title>
    <style>
        @page { margin: 25mm; }
        * { margin: 5; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #1e293b; padding: 0; }
        .header { text-align: center; margin-bottom: 24px; border-bottom: 2px solid #4f46e5; padding-bottom: 16px; }
        .header h1 { font-size: 18px; color: #4f46e5; margin-bottom: 4px; }
        .header p { font-size: 11px; color: #64748b; }
        .info-grid { display: table; width: 100%; margin-bottom: 20px; }
        .info-row { display: table-row; }
        .info-label { display: table-cell; width: 140px; padding: 3px 0; font-weight: bold; color: #475569; }
        .info-value { display: table-cell; padding: 3px 0; color: #1e293b; }
        table.results { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.results th { background: #4f46e5; color: #fff; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; padding: 8px 10px; text-align: left; }
        table.results th.center { text-align: center; }
        table.results td { padding: 7px 10px; border-bottom: 1px solid #e2e8f0; font-size: 11px; }
        table.results td.center { text-align: center; }
        table.results tr:nth-child(even) { background: #f8fafc; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; }
        .badge-pass { background: #d1fae5; color: #065f46; }
        .badge-fail { background: #fee2e2; color: #991b1b; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .summary { margin-top: 16px; padding: 12px; background: #f1f5f9; border-radius: 6px; }
        .summary-grid { display: table; width: 100%; }
        .summary-item { display: table-cell; text-align: center; width: 25%; }
        .summary-item .number { font-size: 20px; font-weight: bold; color: #4f46e5; }
        .summary-item .label { font-size: 9px; color: #64748b; text-transform: uppercase; }
        .footer { margin-top: 24px; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Hasil Ujian</h1>
        <p>CBT App - Computer Based Test</p>
    </div>

    <div class="info-grid">
        <div class="info-row">
            <span class="info-label">Nama Ujian</span>
            <span class="info-value">: {{ $exam->title }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Kategori</span>
            <span class="info-value">: {{ $exam->category->name ?? '-' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Durasi</span>
            <span class="info-value">: {{ $exam->duration }} menit</span>
        </div>
        <div class="info-row">
            <span class="info-label">KKM (Passing Score)</span>
            <span class="info-value">: {{ $exam->passing_score }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Jumlah Peserta</span>
            <span class="info-value">: {{ $sessions->count() }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal Cetak</span>
            <span class="info-value">: {{ now()->format('d F Y, H:i') }}</span>
        </div>
    </div>

    @php
        $totalSessions = $sessions->count();
        $passed = $sessions->where('needs_grading', false)->filter(fn($s) => $s->score >= $exam->passing_score)->count();
        $failed = $sessions->where('needs_grading', false)->filter(fn($s) => $s->score < $exam->passing_score)->count();
        $pending = $sessions->where('needs_grading', true)->count();
        $avgScore = $totalSessions > 0 ? round($sessions->avg('score'), 1) : 0;
    @endphp

    <div class="summary">
        <div class="summary-grid">
            <div class="summary-item">
                <div class="number">{{ $totalSessions }}</div>
                <div class="label">Total Peserta</div>
            </div>
            <div class="summary-item">
                <div class="number" style="color: #059669;">{{ $passed }}</div>
                <div class="label">Lulus</div>
            </div>
            <div class="summary-item">
                <div class="number" style="color: #dc2626;">{{ $failed }}</div>
                <div class="label">Tidak Lulus</div>
            </div>
            <div class="summary-item">
                <div class="number">{{ $avgScore }}</div>
                <div class="label">Rata-rata</div>
            </div>
        </div>
    </div>

    <br>

    <table class="results">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Nama Peserta</th>
                <th>Kelas</th>
                <th class="center">Skor</th>
                <th class="center">Benar</th>
                <th class="center">Status</th>
                <th>Waktu Selesai</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sessions as $i => $session)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                        <strong>{{ $session->user->name }}</strong><br>
                        <span style="color: #94a3b8; font-size: 9px;">{{ $session->user->email }}</span>
                    </td>
                    <td>{{ $session->user->schoolClass?->name ?? '-' }}</td>
                    <td class="center"><strong>{{ $session->score }}</strong></td>
                    <td class="center">{{ $session->correct_answers }}/{{ $session->total_questions }}</td>
                    <td class="center">
                        @if($session->needs_grading)
                            <span class="badge badge-pending">Menunggu</span>
                        @elseif($session->score >= $exam->passing_score)
                            <span class="badge badge-pass">Lulus</span>
                        @else
                            <span class="badge badge-fail">Tidak Lulus</span>
                        @endif
                    </td>
                    <td>{{ $session->finished_at->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada {{ now()->format('d F Y, H:i:s') }} &mdash; CBT App
    </div>
</body>
</html>
