<?php

namespace App\Console\Commands;

use App\Models\User;
use Exception;
use Illuminate\Console\Command;

class DeleteUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fs:user:delete {login}';

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
        $user = User::where('username', $this->argument('login'))->first();
		if (empty($user)) {
			$this->error('No such user "'.$this->argument('login').'"');
		}
		else {
			try {
				$user->delete();
				$this->info('User "'.$this->argument('login').'" has been deleted');
			}
			catch (Exception $e) {
				$this->error('Could not delete user "'.$this->argument('login').'"');
			}
		}

    }
}
