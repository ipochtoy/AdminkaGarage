<?php

namespace App\Console\Commands;

use App\Models\PhotoBuffer;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RecalculateExifCommand extends Command
{
    protected $signature = 'buffer:recalculate-exif';
    protected $description = 'Recalculate EXIF taken_at for all photos in buffer';

    public function handle()
    {
        if (!function_exists('exif_read_data')) {
            $this->error('EXIF extension is not installed');
            return 1;
        }

        $photos = PhotoBuffer::whereNull('taken_at')->get();
        $this->info("Found {$photos->count()} photos without taken_at");

        $updated = 0;
        $bar = $this->output->createProgressBar($photos->count());

        foreach ($photos as $photo) {
            $path = Storage::disk('public')->path($photo->image);

            if (!file_exists($path)) {
                $bar->advance();
                continue;
            }

            $exif = @exif_read_data($path);

            if ($exif && isset($exif['DateTimeOriginal'])) {
                try {
                    $takenAt = Carbon::createFromFormat('Y:m:d H:i:s', $exif['DateTimeOriginal']);
                    $photo->update(['taken_at' => $takenAt]);
                    $updated++;
                } catch (\Exception $e) {
                    // Skip invalid dates
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Updated {$updated} photos with EXIF data");

        return 0;
    }
}
