<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CategoriesExport;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('created_at', 'desc')->get();
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
            'metadata'    => 'nullable|array',
        ]);

        $category = Category::create($request->all());
        return response()->json($category, 201);
    }

    public function show($id)
    {
        $categories = Category::find($id);

        if (!$categories) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail Kategori ditemukan',
            'data' => $categories
        ]);
    }
    // public function update(Request $request, Category $category)
    // {
    //     $request->validate([
    //         'name'        => 'required|string|max:255',
    //         'description' => 'nullable|string',
    //         'is_active'   => 'boolean',
    //         'metadata'    => 'nullable|array',
    //     ]);

    //     $category->update($request->all());
    //     return response()->json($category);
    // }

    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(null, 204);
    }
    public function audits($id)
    {
        $categories = Category::findOrFail($id);
        return response()->json($categories->audits);
    }
    public function exportExcel()
    {
        $baseName = 'kategori';
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

        $excelData = Excel::raw(new CategoriesExport, \Maatwebsite\Excel\Excel::XLSX);

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

        dispatch(function () use ($filePath) {
            $dataSheets = Excel::toArray([], $filePath);
            $rows = $dataSheets[0];

            foreach ($rows as $index => $row) {
                if ($index === 0) continue; // skip header

                // Contoh data kolom 0 = nama, kolom 1 = deskripsi
                Category::create([
                    'name' => $row[0],        // sesuaikan nama kolom di DB
                    'description' => $row[1], // sesuaikan nama kolom di DB
                    'is_active' => $row[2], // sesuaikan nama kolom di DB
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Proses import dimasukkan ke antrian.'
        ]);
    }
}
