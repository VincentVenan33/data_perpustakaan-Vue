<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class Loan extends Model implements AuditableContract
{
    use HasFactory, HasUuids, Auditable;

    protected $fillable = [
        'member_id',
        'member_name_snapshot',
        'book_id',
        'book_title_snapshot',
        'borrowed_at',
        'returned_at'
    ];

    protected $auditInclude = [
        'member_id',
        'book_id',
        'borrowed_at',
        'returned_at',
        'member_name_snapshot',
        'book_title_snapshot'
    ];

    protected $casts = [
        'borrowed_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}