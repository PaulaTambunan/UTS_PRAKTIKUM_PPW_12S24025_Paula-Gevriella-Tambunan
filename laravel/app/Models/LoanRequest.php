<?php

namespace App\Models;

use Database\Factories\LoanRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanRequest extends Model
{
    /** @use HasFactory<LOanRequestFactory> */
    use HasFactory;

    protected $table = 'loan_request';

    protected $fillable = [
        'borrower_name',
        'borrower_nim',
        'borrower_email',
        'loan_date',
        'expected_return_date',
        'purpose',
        'status',
        'total_estimated_fee',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pinjam' => 'date',
            'tanggal_jatuh_tempo' => 'date',
            'tanggal_kembali' => 'date',
        ];
    }

    public function equipments(): BelongsTo
    {
        return $this->belongsTo(equipments::class);
    }
}
