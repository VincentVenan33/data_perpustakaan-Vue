<?php

namespace App\Http\Controllers\Api;

use App\Exports\BooksExport;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::with('category');

        // // Searching
        // if ($request->has('search')) {
        //     $query->where('title', 'like', '%' . $request->search . '%');
        // }

        // // Filtering by category
        // if ($request->has('category_id')) {
        //     $query->where('category_id', $request->category_id);
        // }

        // // Sorting
        // if ($request->has('sort')) {
        //     $query->orderBy($request->sort, $request->get('direction', 'asc'));
        // }

        $books = $query->orderBy('created_at', 'desc')->get();
        return response()->json($books);
    }


    public function store(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'category_id'  => 'required|exists:categories,id',
            'published_at' => 'nullable|string',
            'is_available' => 'string|required',
            'details'      => 'nullable|array',
            'file'         => 'required|mimes:pdf|max:500|required|file|min:100',
        ], [
            'file.max' => 'Ukuran file maksimal 500KB',
            'file.min' => 'Ukuran file minimal 100KB',
        ]);

        // Ambil nama kategori dari master
        $category = Category::findOrFail($request->category_id);

        // Upload file
        $path = $request->file('file')->store('books', 'public');

        $book = Book::create([
            'title'        => $request->title,
            'category_id'  => $request->category_id,
            'category_name_snapshot' => $category ? $category->name : null, // 🔹 snapshot disimpan
            'published_at' => $request->published_at,
            'is_available' => $request->is_available ?? true,
            'details'      => $request->details,
            'file_path'    => $path,
        ]);

        return response()->json([
            'data' => $book,
            'snapshots' => [
                'category' => $book->category_name_snapshot
            ]
        ]);
    }

    public function show($id)
    {
        $book = Book::with('category')->find($id);

        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Book tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail Buku ditemukan',
            'data' => [
                'id' => $book->id,
                'title' => $book->title,
                'category_id' => $book->category_id,
                'category_name_snapshot' => $book->category_name_snapshot,
                'published_at' => $book->published_at,
                'is_available' => $book->is_available,
                'details' => $book->details,
                'file_path' => $book->file_path,
                'category' => $book->category // kalau kategori masih ada
            ]
        ]);
    }

    public function update(Request $request, Book $book)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'category_id'  => 'required|exists:categories,id',
            'published_at' => 'nullable|string',
            'is_available' => 'string|required',
            'details'      => 'nullable|array',
            'file'         => 'nullable|mimes:pdf|max:500|file|min:100',
        ]);

        // Ambil nama kategori dari master
        $category = Category::find($request->category_id);

        if ($request->hasFile('file')) {
            if ($book->file_path && Storage::disk('public')->exists($book->file_path)) {
                Storage::disk('public')->delete($book->file_path);
            }
            $book->file_path = $request->file('file')->store('books', 'public');
        }

        $book->update(array_merge(
            $request->except('file'),
            ['category_name_snapshot' => $category ? $category->name : null] // 🔹 snapshot disimpan
        ));

        return response()->json($book);
    }

    public function destroy(Book $book)
    {
        // Hapus file lama jika ada
        if ($book->file_path && Storage::disk('public')->exists($book->file_path)) {
            Storage::disk('public')->delete($book->file_path);
        }

        // Soft delete
        $book->delete();

        return response()->json(null, 204);
    }
    public function audits($id)
    {
        $book = Book::findOrFail($id);
        return response()->json($book->audits);
    }
    public function exportExcel()
    {
        $baseName = 'buku';
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

        $excelData = Excel::raw(new BooksExport, \Maatwebsite\Excel\Excel::XLSX);

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

        $dataSheets = Excel::toArray([], $filePath);
        $rows = $dataSheets[0];

        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // skip header

            Book::create([
                'title' => $row[0],
                'category_id' => $row[1],
                'published_at' => $row[2] ?? null,
                'is_available' => $row[3] ?? null,
                'details' => $row[4] ?? null,
                'file_path' => $row[5] ?? null
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Import selesai.',
        ]);
    }
}
