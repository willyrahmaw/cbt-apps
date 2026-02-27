<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\SchoolClassesImport;
use App\Models\AuditLog;
use App\Models\SchoolClass;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ClassController extends Controller
{
    public function index()
    {
        $classes = SchoolClass::withCount('students')->latest()->paginate(15);
        return view('admin.classes.index', compact('classes'));
    }

    public function create()
    {
        $academicYear = Setting::getAcademicYear();
        return view('admin.classes.create', compact('academicYear'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'grade_level' => 'nullable|string|max:50',
            'academic_year' => 'nullable|string|max:20',
            'description' => 'nullable|string',
        ]);

        $data = $request->only('name', 'grade_level', 'academic_year', 'description');
        if (empty($data['academic_year'])) {
            $data['academic_year'] = Setting::getAcademicYear();
        }
        $cls = SchoolClass::create($data);
        AuditLog::log('created', 'SchoolClass', $cls->id, "Membuat kelas {$cls->name}", null, $cls->only(['name', 'grade_level']));

        return redirect()->route('admin.classes.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function show(SchoolClass $class)
    {
        $class->load(['students' => fn($q) => $q->orderBy('name')]);
        $unassignedStudents = User::where('role', 'pengguna')
            ->whereNull('school_class_id')
            ->orderBy('name')
            ->get();

        return view('admin.classes.show', compact('class', 'unassignedStudents'));
    }

    public function edit(SchoolClass $class)
    {
        $academicYear = Setting::getAcademicYear();
        return view('admin.classes.edit', compact('class', 'academicYear'));
    }

    public function update(Request $request, SchoolClass $class)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'grade_level' => 'nullable|string|max:50',
            'academic_year' => 'nullable|string|max:20',
            'description' => 'nullable|string',
        ]);

        $old = $class->only(['name', 'grade_level', 'academic_year']);
        $class->update($request->only('name', 'grade_level', 'academic_year', 'description'));
        AuditLog::log('updated', 'SchoolClass', $class->id, "Mengedit kelas {$class->name}", $old, $class->only(['name', 'grade_level', 'academic_year']));

        return redirect()->route('admin.classes.index')->with('success', 'Kelas berhasil diupdate.');
    }

    public function destroy(SchoolClass $class)
    {
        $name = $class->name;
        $class->delete();
        AuditLog::log('deleted', 'SchoolClass', null, "Menghapus kelas {$name}", ['name' => $name], null);

        return back()->with('success', 'Kelas berhasil dihapus.');
    }

    public function addStudent(Request $request, SchoolClass $class)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        User::whereIn('id', $request->user_ids)->update(['school_class_id' => $class->id]);

        return back()->with('success', count($request->user_ids) . ' siswa berhasil ditambahkan ke kelas.');
    }

    public function removeStudent(SchoolClass $class, User $user)
    {
        if ($user->school_class_id === $class->id) {
            $user->update(['school_class_id' => null]);
        }

        return back()->with('success', 'Siswa berhasil dikeluarkan dari kelas.');
    }

    public function template()
    {
        return Excel::download(new \App\Exports\SchoolClassTemplateExport, 'template-import-kelas.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:2048',
        ]);

        $import = new SchoolClassesImport();
        Excel::import($import, $request->file('file'));

        $message = "Berhasil mengimpor {$import->getImportedCount()} kelas.";
        if (!empty($import->getSkipped())) {
            $message .= ' Baris yang dilewati: ' . implode('; ', array_slice($import->getSkipped(), 0, 5));
            if (count($import->getSkipped()) > 5) {
                $message .= ' (dan ' . (count($import->getSkipped()) - 5) . ' lainnya)';
            }
            $message .= '.';
        }

        return redirect()->route('admin.classes.index')->with('success', $message);
    }

    public function promote()
    {
        $academicYear = Setting::getAcademicYear();
        $nextYear = Setting::nextAcademicYear($academicYear);
        $classes = SchoolClass::withCount('students')
            ->where('academic_year', $academicYear)
            ->orderBy('name')
            ->get();

        return view('admin.classes.promote', compact('academicYear', 'nextYear', 'classes'));
    }

    public function promoteStore(Request $request)
    {
        $request->validate([
            'confirm' => 'required|accepted',
        ]);

        $academicYear = Setting::getAcademicYear();
        $nextYear = Setting::nextAcademicYear($academicYear);
        $promoted = 0;
        $graduated = 0;

        $classes = SchoolClass::where('academic_year', $academicYear)->get();

        foreach ($classes as $class) {
            $targetName = $this->getPromotedClassName($class->name);
            $targetGradeLevel = $this->getPromotedGradeLevel($class->grade_level);

            if ($targetName === null) {
                $students = $class->students;
                foreach ($students as $student) {
                    $student->update(['school_class_id' => null]);
                    $graduated++;
                }
                continue;
            }

            $targetClass = SchoolClass::firstOrCreate(
                ['name' => $targetName, 'academic_year' => $nextYear],
                [
                    'grade_level' => $targetGradeLevel ?? $class->grade_level,
                    'description' => $class->description ? str_replace($academicYear, $nextYear, $class->description) : null,
                ]
            );

            $count = $class->students()->update(['school_class_id' => $targetClass->id]);
            $promoted += $count;
        }

        Setting::setAcademicYear($nextYear);

        $message = "Kenaikan kelas berhasil. Tahun ajaran diubah ke {$nextYear}. ";
        if ($promoted > 0) {
            $message .= "{$promoted} siswa naik kelas. ";
        }
        if ($graduated > 0) {
            $message .= "{$graduated} siswa lulus (kelas XII).";
        }

        return redirect()->route('admin.classes.promote')->with('success', trim($message));
    }

    private function getPromotedClassName(string $name): ?string
    {
        $name = trim($name);
        if (preg_match('/^XII\b/i', $name) || preg_match('/^12\b/i', $name)) {
            return null;
        }
        if (preg_match('/^XI\b/i', $name)) {
            return preg_replace('/^XI\b/i', 'XII', $name);
        }
        if (preg_match('/^X\b/i', $name) && !preg_match('/^XI\b/i', $name)) {
            return preg_replace('/^X\b/i', 'XI', $name);
        }
        if (preg_match('/^11\b/i', $name)) {
            return preg_replace('/^11\b/i', '12', $name);
        }
        if (preg_match('/^10\b/i', $name)) {
            return preg_replace('/^10\b/i', '11', $name);
        }
        return null;
    }

    private function getPromotedGradeLevel(?string $gradeLevel): ?string
    {
        if (!$gradeLevel) {
            return null;
        }
        return match (trim($gradeLevel)) {
            'Kelas 10', '10' => 'Kelas 11',
            'Kelas 11', '11' => 'Kelas 12',
            'Kelas 12', '12' => null,
            default => $gradeLevel,
        };
    }
}
