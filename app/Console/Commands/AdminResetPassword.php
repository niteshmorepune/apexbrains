<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class AdminResetPassword extends Command
{
    /**
     * Usage:
     *   php artisan admin:reset-password
     *   php artisan admin:reset-password admin@apexbrains.in
     *   php artisan admin:reset-password admin@apexbrains.in --password="NewSecret123"
     *   php artisan admin:reset-password admin@apexbrains.in --generate
     */
    protected $signature = 'admin:reset-password
                            {email? : Email of the user whose password to reset}
                            {--password= : Set this exact password (skips the prompt)}
                            {--generate : Generate a strong random password and print it}';

    protected $description = 'Reset the login password for any user (admin/franchise/student) from the CLI';

    public function handle(): int
    {
        $email = $this->argument('email') ?: $this->ask('Email address of the account to reset');

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("No user found with email: {$email}");
            return self::FAILURE;
        }

        $role = $user->getRoleNames()->first() ?? 'no role';
        $this->info("Found: {$user->name} <{$user->email}>  (role: {$role}, active: " . ($user->is_active ? 'yes' : 'no') . ')');

        // Resolve the new password.
        if ($this->option('generate')) {
            $password = Str::password(16, symbols: false);
        } elseif ($this->option('password')) {
            $password = $this->option('password');
        } else {
            $password = $this->secret('New password (input hidden)');
            $confirm  = $this->secret('Confirm new password');

            if ($password !== $confirm) {
                $this->error('Passwords do not match. Aborted.');
                return self::FAILURE;
            }
        }

        $validator = Validator::make(
            ['password' => $password],
            ['password' => ['required', 'string', 'min:8']]
        );

        if ($validator->fails()) {
            $this->error($validator->errors()->first('password'));
            return self::FAILURE;
        }

        // The User model casts `password` as `hashed`, so assigning the plain
        // value here hashes it automatically — do NOT bcrypt() again.
        $user->password = $password;
        $user->save();

        // Immutable audit trail (timestamps disabled on AuditLog -> set created_at manually).
        AuditLog::create([
            'user_id'     => $user->id,
            'franchise_id' => $user->franchise_id,
            'action'      => 'password_reset_cli',
            'entity_type' => User::class,
            'entity_id'   => $user->id,
            'ip_address'  => '0.0.0.0',
            'user_agent'  => 'artisan:admin:reset-password',
            'created_at'  => now(),
        ]);

        $this->newLine();
        $this->info("✓ Password updated for {$user->email}");

        if ($this->option('generate')) {
            $this->newLine();
            $this->line('  New password: <comment>' . $password . '</comment>');
            $this->line('  Share it securely and ask the user to change it after logging in.');
        }

        return self::SUCCESS;
    }
}
