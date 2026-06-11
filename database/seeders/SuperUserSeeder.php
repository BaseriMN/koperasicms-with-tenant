<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperUserSeeder extends Seeder
{
    public function run(): void
    {
        $superUser = User::updateOrCreate(
            ['email' => 'muhamad.baseri@gmail.com'],
            [
                'name'              => 'Muhamad Baseri',
                'password'          => Hash::make('@Password12345'),
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );

        $role = Role::where('slug', 'super-user')->first();

        if ($role) {
            $superUser->roles()->syncWithoutDetaching([$role->id]);
        }
    }
}
