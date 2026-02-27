<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'nis',
        'email',
        'password',
        'role',
        'avatar',
        'school_class_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isSuperadmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isPembuatSoal(): bool
    {
        return $this->role === 'pembuat_soal';
    }

    public function isPengguna(): bool
    {
        return $this->role === 'pengguna';
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function exams()
    {
        return $this->hasMany(Exam::class, 'created_by');
    }

    public function examSessions()
    {
        return $this->hasMany(ExamSession::class);
    }
}
