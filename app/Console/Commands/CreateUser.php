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
    protected $signature = 'fs:user:create {login?}';

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
        $login = strtolower($this->argument('login'));

		login:
		// If user was not provided, asking for it
		if (empty($login)) {
			$login = strtolower($this->ask('Enter the user\'s login'));
		}

		if (! preg_match('~^[a-z0-9]{4,40}$~', $login)) {
			$this->error('Invalid login format. Must only contains letters and numbers, between 4 and 40 chars');
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

		if (! preg_match('~^.{4,100}$i~', $password)) {
			$this->error('Invalid password format. Must contains between 5 and 100 chars');
			unset($password);
			goto password;
		}

		try {
			Storage::disk('users')->put($login.'.json', json_encode([
				'username'	=> $login,
				'password'	=> Hash::make($password),
				'bundles'	=> []
			]));

			$this->info('User has been created');
		}
		catch(Exception $e) {
			$this->error('An error occurred, could not create user');
		}
    }
}
