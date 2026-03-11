<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CheckUsers extends Command
{
    protected $signature = 'check:users';
    protected $description = 'Check users in database';

    public function handle()
    {
        $this->info('Users in database:');
        $this->info('ID | Username | Email');
        $this->info('-------------------');

        $users = User::all();
        foreach ($users as $user) {
            $this->info($user->id . ' | ' . $user->username . ' | ' . $user->email);
        }

        $this->info('Total users: ' . $users->count());
        
        return 0;
    }
}