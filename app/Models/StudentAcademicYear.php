<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAcademicYear extends Model
{
    use HasFactory;

    protected $table = 'ref_student_academic_years';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'student_id',
        'class_id',
        'academic_year',
        'status',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id', 'id');
    }
}
