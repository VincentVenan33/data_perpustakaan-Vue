<?php

namespace App\Http\Controllers\Api;

use App\Exports\LoansExport;
use App\Models\Loan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Member;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class LoanController extends Controller
{
    public function index()
    {
        $loans = Loan::with(['book', 'member'])->orderBy('borrowed_at', 'desc')->paginate(10);
        return response()->json($loans);
    }

    public function store(Request $request)
    {
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'book_id' => 'required|exists:books,id',
            'borrowed_at' => 'required|date',
        ]);

        DB::beginTransaction();

        try {
            $member = Member::findOrFail($request->member_id);
            $book = Book::with('category')->findOrFail($request->book_id);

            $loan = Loan::create([
                'member_id' => $member->id,
                'member_name_snapshot' => $member->name,
                'book_id' => $book->id,
                'book_title_snapshot' => $book->title,
                'book_category_snapshot' => $book->category->name ?? null,
                'borrowed_at' => $request->borrowed_at,
                'returned_at' => $request->returned_at,
            ]);

            DB::commit();

            return response()->json([
                'data' => $loan,
                'snapshots' => [
                    'member_name' => $loan->member_name_snapshot,
                    'book_title' => $loan->book_title_snapshot,
                    'book_category' => $loan->book_category_snapshot
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $loan = Loan::with(['member', 'book.category'])->find($id);

        if (!$loan) {
            return response()->json([
                'success' => false,
                'message' => 'Data peminjaman tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $loan->id,
                'borrowed_at' => $loan->borrowed_at,
                'returned_at' => $loan->returned_at,
                // pakai snapshot dulu, kalau null baru ambil dari relasi
                'member_name' => $loan->member_name_snapshot ?? $loan->member->name,
                'book_title' => $loan->book_title_snapshot ?? $loan->book->title,
                'book_category' => $loan->book_category_snapshot
                    ?? ($loan->book->category_name_snapshot ?? $loan->book->category->name),
            ]
        ]);
    }

    public function update(Request $request, Loan $loan)
    {
        $request->validate([
            'returned_at' => 'required|date',
        ]);

        $loan->update([
            'returned_at' => $request->returned_at,
        ]);

        return response()->json($loan);
    }

    public function destroy(Loan $loan)
    {
        $loan->delete();
        return response()->json(null, 204);
    }
    public function audits($id)
    {
        $loan = Loan::findOrFail($id);
        return response()->json($loan->audits);
    }
    public function exportExcel()
    {
        $baseName = 'peminjaman buku';
        $extension = '.xlsx';
        $folder = 'C:/Users/v3n4n/Downloads/export/';

        // Mulai dari nama dasar
        $fileName = $baseName . $extension;
        $fullPath = $folder . $fileName;

        $counter = 1;
        // Cek kalau file sudah ada, buat nama baru dengan nomor urut
        while (file_exists($fullPath)) {
            $fileName = $baseName . " ($counter)" . $extension;
            $fullPath = $folder . $fileName;
            $counter++;
        }

        $excelData = Excel::raw(new LoansExport, \Maatwebsite\Excel\Excel::XLSX);

        file_put_contents($fullPath, $excelData);

        return response()->json([
            'success' => true,
            'message' => 'File berhasil diexport dan disimpan di Downloads.',
            'file' => $fileName
        ]);
    }


    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        $filePath = $request->file('file')->store('imports');

        $duplicateEmails = [];
        $dataSheets = Excel::toArray([], $filePath);
        $rows = $dataSheets[0];

        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // skip header

            loan::create([
                'member_id' => $row[0],
                'book_id' => $row[1],
                'borrowed_at' => $row[2],
                'returned_at' => $row[3] ?? null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Import selesai.',
        ]);
    }
}
