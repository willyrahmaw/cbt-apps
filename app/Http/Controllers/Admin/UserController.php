<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\UsersImport;
use App\Models\AuditLog;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('schoolClass');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('class')) {
            $query->where('school_class_id', $request->class);
        }

        $users = $query->latest()->paginate(15);
        $classes = SchoolClass::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'classes'));
    }

    public function create()
    {
        $classes = SchoolClass::orderBy('name')->get();
        return view('admin.users.create', compact('classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nis' => 'nullable|string|max:50|unique:users,nis',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:superadmin,pembuat_soal,pengguna',
            'school_class_id' => 'nullable|exists:school_classes,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'nis' => $request->nis,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->role,
            'school_class_id' => $request->role === 'pengguna' ? $request->school_class_id : null,
        ]);

        AuditLog::log('created', 'User', $user->id, "Membuat user {$user->name} ({$user->email})", null, $user->only(['name', 'nis', 'email', 'role']));

        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $classes = SchoolClass::orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'classes'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'nis' => 'nullable|string|max:50|unique:users,nis,' . $user->id,
            'role' => 'required|in:superadmin,pembuat_soal,pengguna',
            'school_class_id' => 'nullable|exists:school_classes,id',
        ]);

        $old = $user->only(['name', 'nis', 'email', 'role', 'school_class_id']);
        $user->update([
            'name' => $request->name,
            'nis' => $request->nis,
            'email' => $request->email,
            'role' => $request->role,
            'school_class_id' => $request->role === 'pengguna' ? $request->school_class_id : null,
        ]);
        AuditLog::log('updated', 'User', $user->id, "Mengedit user {$user->name}", $old, $user->only(['name', 'nis', 'email', 'role', 'school_class_id']));

        return redirect()->route('admin.users.edit', $user)->with('success', 'Data user berhasil diupdate.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user->update(['password' => $request->password]);

        return redirect()->route('admin.users.edit', $user)->with('success', 'Password user berhasil direset.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        $name = $user->name;
        $email = $user->email;
        $user->delete();
        AuditLog::log('deleted', 'User', null, "Menghapus user {$name} ({$email})", ['name' => $name, 'email' => $email], null);

        return back()->with('success', 'User berhasil dihapus.');
    }

    public function template()
    {
        return Excel::download(new \App\Exports\UserTemplateExport, 'template-import-user.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:2048',
        ]);

        $import = new UsersImport();
        Excel::import($import, $request->file('file'));

        $message = "Berhasil mengimpor {$import->getImportedCount()} user.";
        if (!empty($import->getSkipped())) {
            $message .= ' Baris yang dilewati: ' . implode('; ', array_slice($import->getSkipped(), 0, 5));
            if (count($import->getSkipped()) > 5) {
                $message .= ' (dan ' . (count($import->getSkipped()) - 5) . ' lainnya)';
            }
            $message .= '.';
        }

        return redirect()->route('admin.users.index')->with('success', $message);
    }
}
