@extends('layouts.app')
@section('title', 'Audit Log')
@section('header', 'Audit Log')
@section('header-actions')
    <p class="text-sm text-slate-500">Siapa membuat/mengedit apa dan kapan</p>
@endsection

@section('content')
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
    <div class="px-6 py-4 border-b border-slate-100">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari deskripsi atau user..."
                class="flex-1 min-w-[200px] px-4 py-2 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none">
            <select name="user_id" class="px-4 py-2 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none">
                <option value="">Semua User</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>
            <select name="action" class="px-4 py-2 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none">
                <option value="">Semua Aksi</option>
                <option value="created" {{ request('action') === 'created' ? 'selected' : '' }}>Dibuat</option>
                <option value="updated" {{ request('action') === 'updated' ? 'selected' : '' }}>Diedit</option>
                <option value="deleted" {{ request('action') === 'deleted' ? 'selected' : '' }}>Dihapus</option>
            </select>
            <select name="auditable_type" class="px-4 py-2 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none">
                <option value="">Semua Tipe</option>
                @foreach($types as $t)
                    <option value="{{ $t }}" {{ request('auditable_type') == $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-sm font-medium hover:bg-slate-200">Filter</button>
            @if(request()->hasAny(['search','user_id','action','auditable_type']))
                <a href="{{ route('admin.audit-log.index') }}" class="px-4 py-2 rounded-xl text-slate-500 text-sm hover:bg-slate-100">Reset</a>
            @endif
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Waktu</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">User</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Aksi</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Deskripsi</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Tipe</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($logs as $log)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-3 text-sm text-slate-500 whitespace-nowrap">{{ $log->created_at->format('d M Y, H:i') }}</td>
                        <td class="px-6 py-3 text-sm text-slate-700">{{ $log->user?->name ?? 'Sistem' }}</td>
                        <td class="px-6 py-3">
                            @php
                                $actionBadge = match($log->action) {
                                    'created' => ['bg-emerald-100 text-emerald-700', 'Dibuat'],
                                    'updated' => ['bg-blue-100 text-blue-700', 'Diedit'],
                                    'deleted' => ['bg-red-100 text-red-700', 'Dihapus'],
                                    default => ['bg-slate-100 text-slate-700', $log->action],
                                };
                            @endphp
                            <span class="px-2.5 py-1 rounded-lg text-xs font-medium {{ $actionBadge[0] }}">{{ $actionBadge[1] }}</span>
                        </td>
                        <td class="px-6 py-3 text-sm text-slate-700">{{ $log->description ?? '-' }}</td>
                        <td class="px-6 py-3 text-sm text-slate-500">{{ $log->auditable_type }}@if($log->auditable_id) #{{ $log->auditable_id }}@endif</td>
                    </tr>
                    @if($log->old_values || $log->new_values)
                        <tr class="bg-slate-50/30">
                            <td colspan="5" class="px-6 py-2 text-xs">
                                @if($log->old_values && $log->action === 'updated')
                                    <span class="text-slate-500">Lama:</span>
                                    @foreach($log->old_values as $k => $v)
                                        <code class="mx-1 px-1.5 py-0.5 bg-slate-200 rounded text-slate-600">{{ $k }}: {{ is_array($v) ? json_encode($v) : $v }}</code>
                                    @endforeach
                                @endif
                                @if($log->new_values && in_array($log->action, ['created', 'updated']))
                                    @if($log->old_values) <span class="mx-2">→</span> @endif
                                    <span class="text-slate-500">Baru:</span>
                                    @foreach($log->new_values as $k => $v)
                                        <code class="mx-1 px-1.5 py-0.5 bg-indigo-100 rounded text-indigo-700">{{ $k }}: {{ is_array($v) ? json_encode($v) : $v }}</code>
                                    @endforeach
                                @endif
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr><td colspan="5" class="px-6 py-12 text-center text-slate-400">Belum ada log</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
