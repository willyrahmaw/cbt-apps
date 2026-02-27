@extends('layouts.app')
@section('title', 'Kelola Kategori')
@section('header', 'Kelola Kategori')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Form --}}
    <div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h3 class="font-semibold text-slate-800 mb-4" id="form-title">Tambah Kategori</h3>
            <form method="POST" action="{{ route('admin.categories.store') }}" id="category-form" class="space-y-4">
                @csrf
                <div id="method-field"></div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Kategori</label>
                    <input type="text" name="name" id="cat-name" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm"
                        placeholder="Contoh: Matematika">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi</label>
                    <input type="text" name="description" id="cat-desc"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm"
                        placeholder="Deskripsi singkat">
                </div>
                <button type="submit" class="w-full py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                    Simpan
                </button>
            </form>
        </div>
    </div>

    {{-- List --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Kategori</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Ujian</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($categories as $cat)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-3">
                                    <div>
                                        <p class="text-sm font-medium text-slate-700">{{ $cat->name }}</p>
                                        <p class="text-xs text-slate-400">{{ $cat->description ?: '-' }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <span class="px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-600 text-xs font-medium">{{ $cat->exams_count }}</span>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button onclick="editCategory({{ $cat->id }}, '{{ addslashes($cat->name) }}', '{{ addslashes($cat->description) }}')"
                                            class="p-2 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}">
                                            @csrf @method('DELETE')
                                            <button type="button" onclick="confirmDelete(this.closest('form'), 'Kategori ini akan dihapus permanen.')" class="p-2 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-6 py-12 text-center text-sm text-slate-400">Belum ada kategori</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($categories->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">{{ $categories->links() }}</div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
@vite(['resources/js/admin/categories-index.js'])
@endpush
@endsection
