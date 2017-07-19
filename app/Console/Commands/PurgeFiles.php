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
	protected $signature = 'storage:purge-expired';

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
			$bundles = Storage::disk('uploads')->files('bundles');
			if (count($bundles) > 0) {
				foreach ($bundles as $b) {
					$this->comment('Opening bundle file: '.$b);
					$bundle = Storage::disk('uploads')->get($b);
					if ($metadata = json_decode($bundle, true)) {
						if (!empty($metadata['expires_at'])) {
							if ($metadata['expires_at'] >= time()) {
								$this->info('-> bundle is still valid (expiration date: '.date('Y-m-d', $metadata['expires_at']).')');
							}
							else {
								$this->line('-> bundle has expired, must be removed');

								// Deleting all files of the bundle
								if (count($metadata['files']) > 0) {
									foreach ($metadata['files'] as $f) {
										try {
											Storage::disk('uploads')->delete($f['fullpath']);
											$this->info('--> successfully deleted file '.$f['fullpath']);
										}
										// In case of an error, displaying a message and resuming operation
										catch (Exception $e) {
											$this->error('--> could not delete file '.$f['fullpath']);
											continue;
										}
									}
								}

								// Now deleting bundle itself
								try {
									Storage::disk('uploads')->delete($b);
									$this->info('--> successfully deleted bundle file '.$b);
								}
								catch (Exception $e) {
									$this->error('--> could not delete bundle '.$b);
								}
							}
						}
						else {
							$this->line('-> bundle has no expiring date');
						}
					}
					else {
						$this->error('Unable to decode JSON metadata');
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
