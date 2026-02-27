<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('exams')->latest()->paginate(15);
        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $cat = Category::create($request->only('name', 'description'));
        AuditLog::log('created', 'Category', $cat->id, "Membuat kategori {$cat->name}", null, $cat->only(['name']));

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $old = $category->only(['name', 'description']);
        $category->update($request->only('name', 'description'));
        AuditLog::log('updated', 'Category', $category->id, "Mengedit kategori {$category->name}", $old, $category->only(['name', 'description']));

        return back()->with('success', 'Kategori berhasil diupdate.');
    }

    public function destroy(Category $category)
    {
        if ($category->exams()->count() > 0) {
            return back()->with('error', 'Tidak bisa menghapus kategori yang memiliki ujian.');
        }

        $name = $category->name;
        $category->delete();
        AuditLog::log('deleted', 'Category', null, "Menghapus kategori {$name}", ['name' => $name], null);

        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}
