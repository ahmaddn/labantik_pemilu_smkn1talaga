<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $table = 'ref_students';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'student_id',
        'student_number',
        'national_student_number',
        'full_name',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function academicYears(): HasMany
    {
        return $this->hasMany(StudentAcademicYear::class, 'student_id', 'id');
    }

    /**
     * Get student's active academic year status.
     */
    public function activeAcademicYear(?string $academicYear = null)
    {
        $query = $this->academicYears()->where('status', 'Active');
        if ($academicYear) {
            $query->where('academic_year', $academicYear);
        }

        return $query->first();
    }
}
