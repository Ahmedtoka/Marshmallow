<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /** Change these passwords from Dashboard → Team right after the first login. */
    public function run(): void
    {
        $hadayek = Branch::where('slug', 'hadayek-al-ahram')->value('id');
        $zayed = Branch::where('slug', 'sheikh-zayed')->value('id');

        $users = [
            ['Marshmallow Admin', 'admin@marshmallownursery.com', 'admin', null],
            ['Sales Manager', 'manager@marshmallownursery.com', 'sales_manager', null],
            ['Hadayek Sales', 'sales.hadayek@marshmallownursery.com', 'sales', $hadayek],
            ['Zayed Sales', 'sales.zayed@marshmallownursery.com', 'sales', $zayed],
        ];

        foreach ($users as [$name, $email, $role, $branch]) {
            User::firstOrCreate(['email' => $email], [
                'name' => $name,
                'role' => $role,
                'branch_id' => $branch,
                'password' => 'Marshmallow@2026',
                'is_active' => true,
            ]);
        }
    }
}
