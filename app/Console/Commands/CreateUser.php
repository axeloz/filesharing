<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fs:create-user {login?}';

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
        $login = $this->argument('login');

		login:
		// If user was not provided, asking for it
		if (empty($login)) {
			$login = $this->ask('Enter the user\'s login');
		}

		if (! preg_match('~^[a-z0-9]{1,40}$~', $login)) {
			$this->error('Invalid login format. Must only contains letters and numbers, between 1 and 40 chars');
			unset($login);
			goto login;
		}

		// Checking login unicity
		if (Storage::disk('users')->exists($login.'.json')) {
			$this->error('User "'.$login.'" already exists');
			unset($login);
			goto login;
		}

		password:
		// Asking for user's password
		$password = $this->secret('Enter the user\'s password');

		if (strlen($password) < 5) {
			$this->error('Invalid password format. Must only contains 5 chars minimum');
			unset($password);
			goto password;
		}

		try {
			Storage::disk('users')->put($login.'.json', json_encode([
				'login'		=> $login,
				'password'	=> Hash::make($password)
			]));

			$this->info('User has been created');
		}
		catch(Exception $e) {
			$this->error('An error occurred, could not create user');
		}
    }
}
