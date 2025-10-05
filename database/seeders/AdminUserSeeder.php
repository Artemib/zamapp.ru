<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use MoonShine\Laravel\Models\MoonshineUser;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        MoonshineUser::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Администратор',
                'password' => Hash::make('password'),
                'moonshine_user_role_id' => 1,
            ]
        );

        $this->command->info('Администратор создан:');
        $this->command->info('Email: admin@example.com');
        $this->command->info('Пароль: password');
    }
}
