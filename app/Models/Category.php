<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public function isMathOrPhysics(): bool
    {
        $name = strtolower($this->name ?? '');
        return str_contains($name, 'matematika') || str_contains($name, 'fisika');
    }
}
