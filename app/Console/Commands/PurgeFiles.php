<?php

namespace app\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

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
	public function handle()
	{
		//
		try {
			$bundles = Storage::disk('uploads')->directories('.');
			if (count($bundles) > 0) {
				foreach ($bundles as $b) {
                    $this->line(' ');
					$this->line('Found bundle: '.$b);

                    if (Storage::disk('uploads')->exists($b.'/bundle.json')) {
                        $this->line('-> found bundle.json file in folder');

                        $content = Storage::disk('uploads')->get($b.'/bundle.json');
                        if (! $metadata = json_decode($content, true)) {
                            $this->error('-> unable to decode JSON');
                            continue;
                        }

                        if (! empty($metadata['expires_at'])) {
                            if ($metadata['expires_at'] >= time()) {
                                $this->info('-> bundle is still valid (expiration date: '.date('Y-m-d H:i:s', $metadata['expires_at']).')');
                            }
                            else {
                                $this->comment('-> bundle has expired, must be removed');

                                if (Storage::disk('uploads')->deleteDirectory($b)) {
                                    $this->info('-> bundle was properly deleted');
                                }
                                else {
                                    $this->error('-> bundle could not be deleted');
                                    continue;
                                }
                            }
                        }
                        else {
                            $this->comment('-> bundle has no expiring date, skipping');
                        }
                    }
				}
			}
			else {
				$this->line('No bundle was found');
			}
		}
		catch (Exception $e) {
			$this->error($e->getMessage());
		}

	}
}
