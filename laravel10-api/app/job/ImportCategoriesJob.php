<?php
namespace App\Jobs;

use App\Imports\CategoriesImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ImportCategoriesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $user;

    public function __construct($filePath, $user)
    {
        $this->filePath = $filePath;
        $this->user = $user;
    }

    public function handle()
    {
        Excel::import(new CategoriesImport, storage_path('app/' . $this->filePath));

        // Hapus file setelah diproses
        Storage::delete($this->filePath);

        // Bisa kirim notifikasi ke user
    }
}
