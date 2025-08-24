<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class Member extends Model implements AuditableContract
{
    use HasFactory, SoftDeletes, HasUuids, Auditable;

    protected $fillable = [
        'name',
        'email',
        'joined_at',
        'is_active',
        'preferences',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'is_active' => 'boolean',
        'preferences' => 'array',
    ];

    public function books()
    {
        return $this->belongsToMany(Book::class, 'loans')
                    ->withPivot(['borrowed_at', 'returned_at'])
                    ->withTimestamps();
    }
}
