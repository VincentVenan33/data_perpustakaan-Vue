<?php
namespace App\Exports;

use App\Models\Book;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BooksExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Book::select('title', 'category_id', 'published_at', 'is_available', 'details', 'file_path')->get();
    }

    public function headings(): array
    {
        return ['Judul', 'Kategori', 'Tanggal Terbit', 'Ketersediaan', 'Detail', 'Buku'];
    }
}
