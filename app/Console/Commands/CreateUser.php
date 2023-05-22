<?php

namespace App\Console\Commands;

use App\Models\User;
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

		$existing = User::find($login);
		if (! empty($existing) && $existing->count() > 0) {
			$this->error('User "'.$login.'" already exists');
			unset($login);
			goto login;
		}

		password:
		// Asking for user's password
		$password = $this->secret('Enter the user\'s password');

		if (! preg_match('~^[^\s]{5,100}$~', $password)) {
			$this->error('Invalid password format. Must contains between 5 and 100 chars without space');
			unset($password);
			goto password;
		}

		try {
			User::create([
				'username'	=> $login,
				'password'	=> Hash::make($password)
			]);

			$this->info('User has been created');
		}
		catch(Exception $e) {
			$this->error('An error occurred, could not create user');
		}
    }
}
