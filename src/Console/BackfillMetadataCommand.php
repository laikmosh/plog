<?php

namespace Laikmosh\Plog\Console;

use Illuminate\Console\Command;
use Laikmosh\Plog\Models\PlogEntry;

class BackfillMetadataCommand extends Command
{
    protected $signature = 'plog:backfill-metadata';
    protected $description = 'Backfill missing file and class metadata for existing log entries';

    public function handle()
    {
        $this->info('Starting to backfill missing metadata...');

        $entries = PlogEntry::where(function ($query) {
            $query->whereNull('file')
                  ->orWhereNull('class')
                  ->orWhere('file', '')
                  ->orWhere('class', '');
        })->get();

        $this->info("Found {$entries->count()} entries missing metadata.");

        $bar = $this->output->createProgressBar($entries->count());
        $updated = 0;

        foreach ($entries as $entry) {
            $updates = [];

            if (!$entry->file) {
                $updates['file'] = 'unknown';
            }

            if (!$entry->line) {
                $updates['line'] = null;
            }

            if (!$entry->class) {
                $updates['class'] = 'root';
            }

            if (!$entry->method) {
                $updates['method'] = 'main';
            }

            if (!empty($updates)) {
                $entry->update($updates);
                $updated++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Successfully updated {$updated} entries.");

        return 0;
    }
}