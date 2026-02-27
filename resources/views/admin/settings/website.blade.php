@extends('layouts.app')
@section('title', 'Pengaturan Website')
@section('header', 'Pengaturan Website')

@section('content')
<div class="max-w-2xl space-y-6">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <h3 class="font-semibold text-slate-800 mb-4">Informasi Website</h3>
        <form method="POST" action="{{ route('admin.settings.website.update') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf @method('PUT')
            <input type="hidden" name="remove_logo" value="0">
            <input type="hidden" name="remove_favicon" value="0">
            <div class="flex items-center gap-6">
                <div class="relative group shrink-0">
                    <div id="logo-placeholder" class="w-16 h-16 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-bold text-2xl {{ $siteLogo ? 'hidden' : '' }}">{{ strtoupper(substr($siteName, 0, 1)) }}</div>
                    <img id="logo-preview" src="{{ $siteLogo ? asset('storage/' . $siteLogo) : '' }}" alt="Logo" class="w-16 h-16 rounded-xl object-contain border-2 border-slate-200 {{ $siteLogo ? '' : 'hidden' }}">
                    <label class="absolute inset-0 rounded-xl bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 cursor-pointer transition">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/></svg>
                        <input type="file" name="site_logo" accept="image/*" class="hidden" onchange="previewLogo(this)">
                    </label>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-700">Logo Website</p>
                    <p class="text-xs text-slate-400 mt-0.5">JPG, PNG, WebP, atau SVG. Maks 1MB.</p>
                    @if($siteLogo)
                        <span class="flex gap-2 mt-1">
                            <button type="button" onclick="openImagePreview(document.getElementById('logo-preview').src)" class="text-xs text-indigo-600 hover:text-indigo-700">Lihat</button>
                            <button type="button" onclick="document.querySelector('input[name=remove_logo]').value='1'; document.getElementById('logo-preview').classList.add('hidden'); document.getElementById('logo-placeholder').classList.remove('hidden');" class="text-xs text-red-500 hover:text-red-700">Hapus logo</button>
                        </span>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-6">
                <div class="relative group shrink-0">
                    <div id="favicon-placeholder" class="w-10 h-10 rounded-lg bg-slate-200 flex items-center justify-center text-slate-500 text-lg {{ $siteFavicon ? 'hidden' : '' }}">◉</div>
                    <img id="favicon-preview" src="{{ $siteFavicon ? asset('storage/' . $siteFavicon) : '' }}" alt="Favicon" class="w-10 h-10 rounded-lg object-contain border-2 border-slate-200 {{ $siteFavicon ? '' : 'hidden' }}">
                    <label class="absolute inset-0 rounded-lg bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 cursor-pointer transition">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/></svg>
                        <input type="file" name="site_favicon" accept=".ico,.png,image/x-icon,image/png" class="hidden" onchange="previewFavicon(this)">
                    </label>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-700">Icon Web (Favicon)</p>
                    <p class="text-xs text-slate-400 mt-0.5">ICO atau PNG 32×32. Tampil di tab browser.</p>
                    @if($siteFavicon)
                        <span class="flex gap-2 mt-1">
                            <button type="button" onclick="openImagePreview(document.getElementById('favicon-preview').src)" class="text-xs text-indigo-600 hover:text-indigo-700">Lihat</button>
                            <button type="button" onclick="document.querySelector('input[name=remove_favicon]').value='1'; document.getElementById('favicon-preview').classList.add('hidden'); document.getElementById('favicon-placeholder').classList.remove('hidden');" class="text-xs text-red-500 hover:text-red-700">Hapus</button>
                        </span>
                    @endif
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Website</label>
                <input type="text" name="site_name" value="{{ old('site_name', $siteName) }}" required
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm"
                    placeholder="CBT App">
                @error('site_name')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi <span class="text-slate-400 font-normal">(opsional)</span></label>
                <input type="text" name="site_description" value="{{ old('site_description', $siteDescription) }}"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm"
                    placeholder="Computer Based Test">
                @error('site_description')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Zona Waktu</label>
                <select name="site_timezone" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm">
                    <option value="Asia/Jakarta" {{ old('site_timezone', $siteTimezone ?? 'Asia/Jakarta') === 'Asia/Jakarta' ? 'selected' : '' }}>WIB - Sumatra, Jawa, Kalimantan Barat/Tengah</option>
                    <option value="Asia/Makassar" {{ old('site_timezone', $siteTimezone ?? 'Asia/Jakarta') === 'Asia/Makassar' ? 'selected' : '' }}>WITA - Bali, Kalimantan Timur/Selatan/Utara, Sulawesi, Nusa Tenggara</option>
                    <option value="Asia/Jayapura" {{ old('site_timezone', $siteTimezone ?? 'Asia/Jakarta') === 'Asia/Jayapura' ? 'selected' : '' }}>WIT - Maluku, Papua</option>
                </select>
                <p class="text-xs text-slate-500 mt-0.5">Digunakan untuk waktu ujian, waktu selesai, dll.</p>
                @error('site_timezone')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Footer <span class="text-slate-400 font-normal">(opsional, tampil di bawah halaman)</span></label>
                <textarea name="site_footer" rows="3"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm resize-none"
                    placeholder="© 2025 Sekolah. All rights reserved.">{{ old('site_footer', $siteFooter) }}</textarea>
                @error('site_footer')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="pt-2">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">Simpan</button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
@vite(['resources/js/admin/settings-website.js'])
@endpush
@endsection
