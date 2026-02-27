@extends('layouts.app')
@section('title', 'Edit User')
@section('header', 'Edit User')

@section('content')
<div class="max-w-4xl" data-open-password-tab="{{ $errors->has('password') ? '1' : '0' }}">
    <div class="flex flex-col sm:flex-row gap-6">
        {{-- Left: Vertical Tabs --}}
        <div class="sm:w-56 shrink-0">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-2 sm:sticky sm:top-24">
                {{-- User info --}}
                <div class="flex items-center gap-3 px-3 py-3 mb-2 border-b border-slate-100">
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="w-10 h-10 rounded-full object-cover border border-slate-200">
                    @else
                        <div class="w-10 h-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold text-sm">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-700 truncate">{{ $user->name }}</p>
                        <p class="text-xs text-slate-400">{{ ucwords(str_replace('_', ' ', $user->role)) }}</p>
                    </div>
                </div>

                <button onclick="switchTab('data')" id="tab-data"
                    class="vtab active w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition text-left">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Informasi User
                </button>
                <button onclick="switchTab('password')" id="tab-password"
                    class="vtab w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition text-left">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Reset Password
                </button>

                <div class="mt-2 pt-2 border-t border-slate-100">
                    <a href="{{ route('admin.users.index') }}"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Kembali
                    </a>
                </div>
            </div>
        </div>

        {{-- Right: Tab Content --}}
        <div class="flex-1 min-w-0">
            {{-- Panel: Data User --}}
            <div id="panel-data" class="tab-panel">
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                    <h3 class="font-semibold text-slate-800 mb-5">Informasi User</h3>
                    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-5">
                        @csrf @method('PUT')
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm">
                            @error('name')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm">
                                @error('email')
                                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">NIS <span class="text-slate-400 font-normal">(opsional)</span></label>
                                <input type="text" name="nis" value="{{ old('nis', $user->nis) }}"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm"
                                    placeholder="Hanya untuk siswa">
                                @error('nis')
                                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Role</label>
                                <select name="role" id="role-select" required onchange="toggleClassField()"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm">
                                    <option value="pengguna" {{ old('role', $user->role) === 'pengguna' ? 'selected' : '' }}>Pengguna</option>
                                    <option value="pembuat_soal" {{ old('role', $user->role) === 'pembuat_soal' ? 'selected' : '' }}>Pembuat Soal</option>
                                    <option value="superadmin" {{ old('role', $user->role) === 'superadmin' ? 'selected' : '' }}>Superadmin</option>
                                </select>
                            </div>
                            <div id="class-field">
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Kelas</label>
                                <select name="school_class_id"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm">
                                    <option value="">Tanpa Kelas</option>
                                    @foreach($classes as $cls)
                                        <option value="{{ $cls->id }}" {{ old('school_class_id', $user->school_class_id) == $cls->id ? 'selected' : '' }}>{{ $cls->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="pt-2">
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">Simpan Data</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Panel: Reset Password --}}
            <div id="panel-password" class="tab-panel hidden">
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                    <h3 class="font-semibold text-slate-800 mb-1">Reset Password</h3>
                    <p class="text-xs text-slate-400 mb-5">Atur ulang password untuk <strong class="text-slate-600">{{ $user->name }}</strong></p>
                    <form method="POST" action="{{ route('admin.users.resetPassword', $user) }}" class="space-y-5">
                        @csrf @method('PUT')
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Password Baru</label>
                            <input type="password" name="password" required
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm">
                            @error('password')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" required
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm">
                        </div>
                        <div class="pt-2">
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-slate-800 text-white text-sm font-medium hover:bg-slate-900 transition">Reset Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@vite(['resources/js/admin/users.js'])
@endpush
@endsection
