<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ListUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fs:user:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listing of existing users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::get();
		$this->table([
			'username',
			'connected_at',
			'created_at',
			'updated_at'
		], $users);
    }
}
