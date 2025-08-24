<?php
namespace App\Exports;

use App\Models\Loan;
use App\Models\Member;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LoansExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Loan::select('member_id', 'book_id', 'borrowed_at', 'returned_at')->get();
    }

    public function headings(): array
    {
        return ['Nama', 'Judul', 'Tanggal Peminjaman', 'Tanggal Pengembalian'];
    }
}
