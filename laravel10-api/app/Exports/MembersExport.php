<?php
namespace App\Exports;

use App\Models\Member;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MembersExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Member::select('name', 'email', 'joined_at', 'preferences','is_active')->get();
    }

    public function headings(): array
    {
        return ['Nama', 'email', 'Tanggal bergabung', 'Sosial Media', 'Status'];
    }
}
