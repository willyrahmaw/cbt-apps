@extends('layouts.app')
@section('title', 'Detail Kelas')
@section('header', $class->name)
@section('header-actions')
    <a href="{{ route('admin.classes.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-medium hover:bg-slate-200 transition">Kembali</a>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Info Kelas + Tambah Siswa --}}
    <div class="lg:col-span-1 space-y-4">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <h3 class="font-semibold text-slate-800 mb-3">Info Kelas</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">Nama</span>
                    <span class="font-medium text-slate-700">{{ $class->name }}</span>
                </div>
                @if($class->grade_level)
                    <div class="flex justify-between">
                        <span class="text-slate-500">Tingkat</span>
                        <span class="font-medium text-slate-700">{{ $class->grade_level }}</span>
                    </div>
                @endif
                @if($class->academic_year)
                    <div class="flex justify-between">
                        <span class="text-slate-500">Tahun Ajaran</span>
                        <span class="font-medium text-slate-700">{{ $class->academic_year }}</span>
                    </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-slate-500">Jumlah Siswa</span>
                    <span class="font-medium text-slate-700">{{ $class->students->count() }}</span>
                </div>
            </div>
        </div>

        @if($unassignedStudents->isNotEmpty())
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <h3 class="font-semibold text-slate-800 mb-3">Tambah Siswa</h3>
                <form method="POST" action="{{ route('admin.classes.addStudent', $class) }}" class="space-y-3">
                    @csrf
                    <div class="max-h-48 overflow-y-auto space-y-1.5 border border-slate-100 rounded-xl p-3">
                        @foreach($unassignedStudents as $student)
                            <label class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-slate-50 cursor-pointer transition">
                                <input type="checkbox" name="user_ids[]" value="{{ $student->id }}" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-slate-700 truncate">{{ $student->name }}</p>
                                    <p class="text-xs text-slate-400 truncate">{{ $student->email }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                        Tambahkan ke Kelas
                    </button>
                </form>
            </div>
        @endif
    </div>

    {{-- Daftar Siswa --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="font-semibold text-slate-800">Daftar Siswa ({{ $class->students->count() }})</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">#</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($class->students as $index => $student)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-3 text-sm text-slate-500">{{ $index + 1 }}</td>
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xs font-semibold">
                                            {{ strtoupper(substr($student->name, 0, 1)) }}
                                        </div>
                                        <span class="text-sm font-medium text-slate-700">{{ $student->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-sm text-slate-500">{{ $student->email }}</td>
                                <td class="px-6 py-3 text-center">
                                    <form method="POST" action="{{ route('admin.classes.removeStudent', [$class, $student]) }}">
                                        @csrf @method('DELETE')
                                        <button type="button" onclick="confirmDelete(this.closest('form'), 'Siswa akan dikeluarkan dari kelas ini.')" class="px-3 py-1.5 rounded-lg text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 transition">
                                            Keluarkan
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-12 text-center text-sm text-slate-400">Belum ada siswa di kelas ini</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
