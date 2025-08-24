<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class Book extends Model implements AuditableContract
{
    use HasFactory, SoftDeletes, HasUuids, Auditable;

    protected $fillable = [
        'title',
        'category_id',
        'published_at',
        'category_name_snapshot',
        'is_available',
        'details',
        'file_path',
    ];

    protected $casts = [
        'details' => 'array',
    ];
    public $incrementing = false;
    protected $keyType = 'string';
    public function getRouteKeyName()
    {
        return 'id';
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function members()
    {
        return $this->belongsToMany(Member::class, 'loans')
                    ->withPivot(['borrowed_at', 'returned_at'])
                    ->withTimestamps();
    }
}
