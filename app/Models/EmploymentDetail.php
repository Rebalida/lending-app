<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmploymentDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'added_by',
        'employment_type',
        'employer_business_name',
        'abn',
        'employment_role',
        'position',
        'employment_start_date',
        'length_of_employment_months',
        'base_income',
        'after_tax_income',
        'additional_income',
        'income_frequency',
        'employer_phone',
        'employer_address',
        'comment',
        'is_current',
        'employment_end_date',
    ];

    protected $casts = [
        'employment_start_date'       => 'date',
        'length_of_employment_months' => 'integer',
        'base_income'                 => 'decimal:2',
        'after_tax_income'            => 'decimal:2',
        'additional_income'           => 'decimal:2',
        'is_current'                  => 'boolean',
        'employment_end_date'         => 'date',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function history(): HasMany
    {
        return $this->hasMany(EmploymentDetailHistory::class)
                    ->orderBy('changed_at', 'desc');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(AssessorEmploymentDocument::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isAssessorAdded(): bool
    {
        return $this->added_by !== null;
    }

    public function getEmploymentTypeLabelAttribute(): string
    {
        return ucwords(str_replace('_', ' ', $this->employment_type));
    }

    public function getAnnualIncome(): float
    {
        $total = (float) $this->base_income + (float) $this->additional_income;

        return match($this->income_frequency) {
            'weekly'      => $total * 52,
            'fortnightly' => $total * 26,
            'monthly'     => $total * 12,
            'annual'      => $total,
            default       => 0,
        };
    }

    public function getMonthlyIncome(): float
    {
        return $this->getAnnualIncome() / 12;
    }

    public function calculateEmploymentLength(): void
    {
        if ($this->employment_start_date) {
            $this->length_of_employment_months = $this->employment_start_date->diffInMonths(now());
            $this->save();
        }
    }

    // ── Auto-history on update ────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::updating(function (EmploymentDetail $employment) {
            $trackFields = [
                'employment_type', 'employer_business_name', 'abn',
                'employment_role', 'position', 'employment_start_date',
                'length_of_employment_months', 'base_income', 'additional_income',
                'income_frequency', 'employer_phone', 'employer_address', 'comment',
            ];

            foreach ($trackFields as $field) {
                if ($employment->isDirty($field)) {
                    EmploymentDetailHistory::create([
                        'employment_detail_id' => $employment->id,
                        'changed_by'           => auth()->id(),
                        'field'                => $field,
                        'old_value'            => $employment->getOriginal($field),
                        'new_value'            => $employment->getAttribute($field),
                    ]);
                }
            }
        });
    }
}