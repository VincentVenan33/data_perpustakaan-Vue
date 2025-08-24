<?php

namespace App\Http\Controllers\Api;

use App\Exports\MembersExport;
use App\Models\Member;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class MemberController extends Controller
{
    public function index()
    {
        $members = Member::orderBy('created_at', 'desc')->get();
        return response()->json($members);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:members,email',
            'joined_at'   => 'nullable|date',
            'is_active'   => 'boolean',
            'preferences' => 'nullable|json',
        ]);

        $member = Member::create($request->all());
        return response()->json($member, 201);
    }

    public function show($id)
    {
        $member = Member::find($id);

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Member tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail Member ditemukan',
            'data' => $member
        ]);
    }

    public function update(Request $request, Member $member)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:members,email,' . $member->id,
            'joined_at'   => 'nullable|date',
            'is_active'   => 'boolean',
            'preferences' => 'nullable|json',
        ]);

        $member->update($request->all());
        return response()->json($member);
    }

    public function destroy(Member $member)
    {
        $member->delete();
        return response()->json(null, 204);
    }
    public function audits($id)
    {
        $member = Member::findOrFail($id);
        return response()->json($member->audits);
    }
    public function exportExcel()
    {
        $baseName = 'anggota';
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

        $excelData = Excel::raw(new MembersExport, \Maatwebsite\Excel\Excel::XLSX);

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

            $email = $row[1];

            if (Member::where('email', $email)->exists()) {
                $duplicateEmails[] = $email;
                continue;
            }

            $preferences = $row[4] ?? '{}';

            if (!$this->isValidJson($preferences)) {
                $preferences = '{}';
            }

            Member::create([
                'name' => $row[0],
                'email' => $email,
                'joined_at' => $row[2] ?? null,
                'is_active' => filter_var($row[3], FILTER_VALIDATE_BOOLEAN),
                'preferences' => $preferences,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Import selesai.',
            'duplicates' => $duplicateEmails,
        ]);
    }

    // Fungsi helper cek JSON valid
    private function isValidJson($string)
    {
        json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }
}