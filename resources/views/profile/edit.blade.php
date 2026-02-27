@extends('layouts.app')
@section('title', 'Profil Saya')
@section('header', 'Profil Saya')

@section('content')
<div class="max-w-4xl mx-auto">
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

                <button onclick="switchTab('profil')" id="tab-profil"
                    class="vtab active w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition text-left">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Informasi Profil
                </button>
                <button onclick="switchTab('password')" id="tab-password"
                    class="vtab w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition text-left">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Ubah Password
                </button>
            </div>
        </div>

        {{-- Right: Tab Content --}}
        <div class="flex-1 min-w-0">
            {{-- Panel: Profil --}}
            <div id="panel-profil" class="tab-panel">
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                    <h3 class="font-semibold text-slate-800 mb-5">Informasi Profil</h3>
                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-5">
                        @csrf @method('PUT')
                        <input type="hidden" name="remove_avatar" id="remove-avatar-flag" value="0">

                        {{-- Avatar --}}
                        <div class="flex items-center gap-5">
                            <div class="relative group">
                                @if($user->avatar)
                                    <img id="avatar-preview" src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar"
                                        class="w-20 h-20 rounded-full object-cover border-2 border-slate-200">
                                @else
                                    <div id="avatar-placeholder" class="w-20 h-20 rounded-full bg-indigo-500 flex items-center justify-center text-white text-2xl font-bold border-2 border-slate-200">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <img id="avatar-preview" src="" alt="Avatar" class="w-20 h-20 rounded-full object-cover border-2 border-slate-200 hidden">
                                @endif
                                <label class="absolute inset-0 rounded-full bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 cursor-pointer transition">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="previewAvatar(this)">
                                </label>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-700">Foto Profil</p>
                                <p class="text-xs text-slate-400 mt-0.5">JPG, PNG, atau WebP. Maks 2MB.</p>
                                @if($user->avatar)
                                    <button type="button" onclick="removeAvatar()" class="text-xs text-red-500 hover:text-red-700 mt-1">Hapus foto</button>
                                @endif
                            </div>
                        </div>

                        {{-- Email --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm">
                            @error('email')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Info --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-3 rounded-xl bg-slate-50">
                                <p class="text-xs text-slate-400">Nama</p>
                                <p class="text-sm font-medium text-slate-700 mt-0.5">{{ $user->name }}</p>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50">
                                <p class="text-xs text-slate-400">Role</p>
                                <p class="text-sm font-medium text-slate-700 mt-0.5">{{ ucwords(str_replace('_', ' ', $user->role)) }}</p>
                            </div>
                            @if($user->schoolClass)
                                <div class="p-3 rounded-xl bg-slate-50">
                                    <p class="text-xs text-slate-400">Kelas</p>
                                    <p class="text-sm font-medium text-slate-700 mt-0.5">{{ $user->schoolClass->name }}</p>
                                </div>
                            @endif
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Panel: Ubah Password --}}
            <div id="panel-password" class="tab-panel hidden">
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                    <h3 class="font-semibold text-slate-800 mb-5">Ubah Password</h3>
                    <form method="POST" action="{{ route('profile.password') }}" class="space-y-5">
                        @csrf @method('PUT')
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Password Lama</label>
                            <input type="password" name="current_password" required
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm">
                            @error('current_password')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Password Baru</label>
                            <input type="password" name="password" required
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm">
                            @error('password')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirmation" required
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm">
                        </div>
                        <div class="pt-2">
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-slate-800 text-white text-sm font-medium hover:bg-slate-900 transition">
                                Ubah Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.vtab { color: #64748b; }
.vtab:hover:not(.active) { background: #f8fafc; color: #334155; }
.vtab.active { background: #eef2ff; color: #4f46e5; }
</style>
@endpush

@push('scripts')
<script>
function switchTab(tab) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
    document.querySelectorAll('.vtab').forEach(b => b.classList.remove('active'));
    document.getElementById('panel-' + tab).classList.remove('hidden');
    document.getElementById('tab-' + tab).classList.add('active');
}

function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatar-preview');
            const placeholder = document.getElementById('avatar-placeholder');
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            if (placeholder) placeholder.classList.add('hidden');
            document.getElementById('remove-avatar-flag').value = '0';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeAvatar() {
    document.getElementById('remove-avatar-flag').value = '1';
    const preview = document.getElementById('avatar-preview');
    const placeholder = document.getElementById('avatar-placeholder');
    if (preview) preview.classList.add('hidden');
    if (placeholder) placeholder.classList.remove('hidden');
    const fileInput = document.querySelector('input[name="avatar"]');
    if (fileInput) fileInput.value = '';
}

@if($errors->has('current_password') || $errors->has('password'))
    switchTab('password');
@endif
</script>
@endpush
@endsection
