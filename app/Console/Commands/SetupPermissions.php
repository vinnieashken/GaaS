<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class SetupPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:permissions {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronizes permissions to the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? null;
        $this->updatePermissions();
        if($email) {
            $user = User::where('email', $email)->first();
            if($user) {
                $user->givePermissionTo(Permission::all());
            }
        }
        return 0;
    }

    private function updatePermissions(): void
    {
        foreach (config('settings.permissions') as $scopes => $permissions) {
            foreach ($permissions as $groups => $values) {
                foreach ($values as $value) {
                    Permission::updateOrCreate(['name' => $value], [
                        'name' => $value,
                        'scope' => $scopes,
                        'display_name' => Str::replace('_', ' ', Str::title($value)),
                        'group' => $groups,
                    ]);
                }
            }
        };
    }
}
