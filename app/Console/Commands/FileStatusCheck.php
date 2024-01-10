<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\File; // Assuming your model is named "File"

class FileStatusCheck extends Command
{
    protected $signature = 'files:check-status';

    protected $description = 'Checks the status of files and releases them if they have been reserved for more than 6 hours.';

    public function handle()
    {
        $files = File::where('status','=', 'taken')->get();

        foreach ($files as $file) {
            $now = now();
            $difference = $now->diffInMinutes($file->updated_at);

//            if ($difference > 1) {
            if ($difference > 24) {
                $file->status = 'free';
                $file->save();
               // Log::info("Released file: {$file->id}"); // Log released file IDs
            }
        }

        //Log::info('File status check completed.');
    }
}
