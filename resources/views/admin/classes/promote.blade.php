@extends('layouts.app')
@section('title', 'Kenaikan Kelas')
@section('header', 'Kenaikan Kelas')
@section('header-actions')
    <a href="{{ route('admin.classes.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-medium hover:bg-slate-200 transition">Kembali</a>
@endsection

@section('content')
<div class="max-w-3xl space-y-6">
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
        <h3 class="font-semibold text-amber-800 mb-2">Tahun Ajaran Saat Ini</h3>
        <p class="text-2xl font-bold text-amber-700">{{ $academicYear }}</p>
        <p class="text-sm text-amber-600 mt-1">Akan diubah ke <strong>{{ $nextYear }}</strong> setelah proses kenaikan kelas</p>
        <a href="{{ route('admin.settings.index') }}" class="inline-block mt-3 text-sm text-amber-700 hover:text-amber-800 font-medium">Ubah tahun ajaran manual →</a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <h3 class="font-semibold text-slate-800 mb-4">Kelas yang Akan Diproses</h3>
        @if($classes->isEmpty())
            <p class="text-slate-500 py-6">Tidak ada kelas dengan tahun ajaran {{ $academicYear }}. Pastikan kelas sudah menggunakan tahun ajaran yang benar.</p>
        @else
            <ul class="space-y-2 mb-6">
                @foreach($classes as $class)
                    <li class="flex items-center justify-between py-2 border-b border-slate-50 last:border-0">
                        <span class="font-medium text-slate-700">{{ $class->name }}</span>
                        <span class="text-sm text-slate-500">{{ $class->students_count }} siswa</span>
                    </li>
                @endforeach
            </ul>

            <div class="border-t border-slate-100 pt-5">
                <p class="text-sm text-slate-600 mb-4">Proses ini akan: memindahkan siswa X→XI, XI→XII; siswa XII akan lulus (dikeluarkan dari kelas); tahun ajaran diubah ke {{ $nextYear }}.</p>
                <form method="POST" action="{{ route('admin.classes.promote.store') }}" id="promote-form">
                    @csrf
                    <input type="hidden" name="confirm" value="1">
                    <button type="button" onclick="confirmAction(document.getElementById('promote-form'), 'Proses Kenaikan Kelas?', 'Tahun ajaran akan diubah ke {{ $nextYear }}. Siswa X→XI, XI→XII, XII→lulus.', 'Ya, proses', 'warning')" class="px-6 py-3 rounded-xl bg-emerald-600 text-white font-semibold hover:bg-emerald-700 transition">
                        Proses Kenaikan Kelas
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
