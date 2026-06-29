<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Bundle;
use Illuminate\Database\Eloquent\Builder;
use Carbon\CarbonImmutable;

class PurgeFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fs:bundle:purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge expired uploaded files from the storage disk';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): bool
    {
        $last_month = new CarbonImmutable('last month')->timezone('UTC');
        $bundles = Bundle::where('expires_at', '<=', time())
            ->orWhere(function (Builder $query) {
                return $query->where('max_downloads', '>', 0)
                    ->whereColumn('downloads', '>=', 'max_downloads')
                ;
            })
            ->orWhere(function (Builder $query) use ($last_month) {
                return $query->where('completed', false)
                    ->where('updated_at', '<=', $last_month);
            })
            ->get()
        ;

        if ($bundles->count() > 0) {
            foreach ($bundles as $b) {
                $this->line('Bundle "'.$b->slug.'" must be deleted');

                // Deleting file models from Orbit
                foreach ($b->files()->get() as $f) {
                    try {
                        $f->delete();
                    } catch (\Exception $e) {
                        $this->error('Could not delete "'.$f->uuid.'" file');
                    }
                }

                // Deleting bundle model from Orbit
                try {
                    $b->delete();
                } catch (\Exception $e) {
                    $this->error('Could not delete bundle model.');
                }

                try {
                    Storage::disk('uploads')->deleteDirectory($b->slug);
                    $this->info('-> Bundle was properly deleted.');
                } catch (\Exception $e) {
                    $this->error('-> Unable to deleted bundle');
                }
            }
        } else {
            $this->line('Nothing to work on');
        }

        return true;
    }
}
