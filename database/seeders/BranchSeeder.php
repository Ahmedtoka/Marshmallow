<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        Branch::updateOrCreate(['slug' => 'hadayek-al-ahram'], [
            'name' => 'Hadayek Al Ahram',
            'short_name' => 'Hadayek',
            'phone' => '01012666625',
            'whatsapp' => '01012666625',
            'email' => 'admin@marshmallownursery.com',
            'address' => 'Villa 3, Street 5A, Gate 1 (Khofo), Hadayek Al Ahram, Giza 12556',
            'address_note' => 'Khofo Gate',
            'map_url' => 'https://maps.app.goo.gl/LDdGxQaq8H63eSJ59',
            'map_embed_url' => 'https://www.google.com/maps?q='.rawurlencode('Street 5A, Khofo Gate, Hadayek Al Ahram, Giza').'&output=embed',
            'working_hours' => 'Sun – Thu, 7:00 am – 4:00 pm',
            'sort_order' => 1,
        ]);

        Branch::updateOrCreate(['slug' => 'sheikh-zayed'], [
            'name' => 'Sheikh Zayed',
            'short_name' => 'Zayed',
            'phone' => '01010813332',
            'whatsapp' => '01010813332',
            'email' => 'admin@marshmallownursery.com',
            'address' => 'Building 1, District 10, El Mostasmer El Sagheer, Sheikh Zayed, Giza',
            'address_note' => 'Next to the District 10 mosque',
            'map_url' => 'https://maps.app.goo.gl/msV7sEuAVJ9jie8w8',
            'map_embed_url' => 'https://www.google.com/maps?q='.rawurlencode('District 10, El Mostasmer El Sagheer, Sheikh Zayed, Giza').'&output=embed',
            'working_hours' => 'Sun – Thu, 7:00 am – 4:00 pm',
            'sort_order' => 2,
        ]);
    }
}
