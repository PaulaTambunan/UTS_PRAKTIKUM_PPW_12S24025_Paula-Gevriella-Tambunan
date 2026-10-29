<?php

namespace App\Models;

use Database\Factories\equipmentsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class equipments extends Model
{
    /** @use HasFactory<BukuFactory> */
    use HasFactory;

    protected $fillable = [
        'asset_code',
        'name',
        'specification',
        'condition',
        'status',
        'daily_rental_fee',
        'image_path',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }

    public function LoanRequest(): HasMany
    {
        return $this->hasMany(Peminjaman::class);
    }
}
