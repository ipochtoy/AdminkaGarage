<?php

namespace App\Console\Commands;

use App\Jobs\ProcessPhotoBatchJob;
use App\Models\PhotoBatch;
use Illuminate\Console\Command;

class ProcessPhotoBatchCommand extends Command
{
    protected $signature = 'process:photo-batch {batch_id} {provider=gemini}';

    protected $description = 'Process photo batch in background (for async execution)';

    public function handle()
    {
        $batchId = $this->argument('batch_id');
        $provider = $this->argument('provider');

        $batch = PhotoBatch::find($batchId);

        if (!$batch) {
            $this->error("Batch {$batchId} not found");
            return 1;
        }

        $this->info("Processing batch {$batchId} with {$provider}...");

        (new ProcessPhotoBatchJob($batch, $provider))->handle();

        $this->info("Batch {$batchId} processed successfully!");

        return 0;
    }
}
