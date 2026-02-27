@extends('layouts.app')
@section('title', 'Kelola User')
@section('header', 'Kelola User')
@section('header-actions')
    <div class="flex flex-wrap items-center gap-2 justify-end">
        <a href="{{ route('admin.users.template') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-medium hover:bg-slate-200 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Template
        </a>
        <form method="POST" action="{{ route('admin.users.import') }}" enctype="multipart/form-data" class="flex items-center gap-2">
            @csrf
            <label class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-500 text-white text-sm font-medium hover:bg-emerald-600 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Impor
                <input type="file" name="file" accept=".xlsx,.xls" class="hidden" onchange="if(this.files.length) this.form.submit()">
            </label>
        </form>
        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah User
        </a>
    </div>
@endsection

@section('content')
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
    {{-- Filter --}}
    <div class="px-6 py-4 border-b border-slate-100">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..."
                class="flex-1 min-w-[200px] px-4 py-2 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none">
            <select name="role" class="px-4 py-2 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none">
                <option value="">Semua Role</option>
                <option value="superadmin" {{ request('role') === 'superadmin' ? 'selected' : '' }}>Superadmin</option>
                <option value="pembuat_soal" {{ request('role') === 'pembuat_soal' ? 'selected' : '' }}>Pembuat Soal</option>
                <option value="pengguna" {{ request('role') === 'pengguna' ? 'selected' : '' }}>Pengguna</option>
            </select>
            <select name="class" class="px-4 py-2 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none">
                <option value="">Semua Kelas</option>
                @foreach($classes as $cls)
                    <option value="{{ $cls->id }}" {{ request('class') == $cls->id ? 'selected' : '' }}>{{ $cls->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-medium hover:bg-slate-200 transition">Filter</button>
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full min-w-[900px]">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">NIS</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Role</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Kelas</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Terdaftar</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($users as $user)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-3 text-sm text-slate-500">{{ $users->firstItem() + $loop->index }}</td>
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-3">
                                @if($user->avatar)
                                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar {{ $user->name }}"
                                         class="w-8 h-8 rounded-full object-cover border border-slate-200">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold text-xs">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                                <span class="text-sm font-medium text-slate-700">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-3 text-sm text-slate-500">{{ $user->email }}</td>
                        <td class="px-6 py-3 text-sm text-slate-500 font-mono">{{ $user->nis ?? '-' }}</td>
                        <td class="px-6 py-3 text-center">
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium whitespace-nowrap
                                {{ $user->role === 'superadmin' ? 'bg-red-100 text-red-700' : ($user->role === 'pembuat_soal' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700') }}">
                                {{ ucwords(str_replace('_', ' ', $user->role)) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-center text-sm">
                            @if($user->schoolClass)
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-600 whitespace-nowrap">
                                    {{ $user->schoolClass->name }}
                                </span>
                            @else
                                <span class="text-slate-300">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-center text-sm text-slate-400">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="px-6 py-3 text-center">
                            <div class="flex flex-wrap items-center justify-center gap-1">
                                <a href="{{ route('admin.users.edit', $user) }}" class="p-2 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                        @csrf @method('DELETE')
                                        <button type="button" onclick="confirmDelete(this.closest('form'), 'User ini akan dihapus permanen.')" class="p-2 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-12 text-center text-sm text-slate-400">Tidak ada data user</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
