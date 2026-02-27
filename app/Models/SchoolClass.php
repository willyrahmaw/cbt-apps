<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    use HasFactory;

    protected $table = 'school_classes';

    protected $fillable = [
        'name',
        'grade_level',
        'academic_year',
        'description',
    ];

    public function students()
    {
        return $this->hasMany(User::class, 'school_class_id');
    }

    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'class_exam');
    }
}
