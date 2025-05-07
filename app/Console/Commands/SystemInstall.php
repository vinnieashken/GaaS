<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SystemInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setups the application for local development';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (env('APP_ENV') == 'production') {
            $this->error('Application must be in local environment');
            exit('Application must be in local environment');
        }else {
            $this->info('Application is in local environment');
            $this->warn('migration started...');
            $this->call('migrate:fresh');
            $this->info('migration done...');
            $this->info('Setup passport...');
            $this->call('passport:client',['--password' => true,'--provider'=> 'users','--no-interaction' => true,'--name' => 'Password Grant Client']);
            $this->call('passport:client',['--personal' => true,'--no-interaction' => true,'--name' => 'Personal Access Client']);
            $this->info('passport passowrd created.');
            $this->call('config:clear');
            $this->call('cache:clear');
            $this->warn('Clearing optimized files...');
            $this->call('optimize:clear');
            $this->warn('Seeding');
            $this->call('db:seed');
            $this->info('Seeding done!');
            $this->warn("Setting up permissions...");
            $this->call('setup:permissions',['email' => 'admin@company.com']);
            $this->info('Permissions setup successful!');
            $this->warn('Housekeeping');
            $this->alert('Setup completed!');
            return 0;
        }
    }
}
