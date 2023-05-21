<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckRelease extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fs:app:releases';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
		$releases = [];

		$this->newLine();
        if (! $xml = @simplexml_load_file('https://github.com/axeloz/filesharing/releases.atom')) {
			$this->error(' Unable to fetch the releases ');
		}
		else {
			foreach ($xml->entry as $e) {

				// Looking for the release link
				foreach ($e->link->attributes() as $k => $a) {
					if ($k == 'href') {
						$href = $a;
					}
				}

				// Adding the info
				array_push($releases, [
					'version'		=> $e->title,
					'updated_at'	=> (new Carbon($e->updated))->diffForHumans(),
					'link'			=> $href ?? null
				]);
			}

			// Displaying the releases
			if (count($releases) > 0) {
				$this->table([
					'Version', 'Updated', 'Link'
					], $releases
				);
			}
			else {
				$this->error(' No release found ');
			}
		}

		$this->newLine();
    }
}
