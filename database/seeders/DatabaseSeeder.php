<?php

namespace Database\Seeders;

use App\Models\Pay;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Target;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'id'=> '9c6c9f38-38c6-4059-b39f-5af6d001a0e5',
            'username' => 'UdinJamsot',
            'name' => 'Saepudin',
            'password' => 'test1234'
        ]);

        Target::factory()->create([
            'id' => '2dd7a49e-5f66-4c40-9d99-409336cd1427',
            'user_id' => '9c6c9f38-38c6-4059-b39f-5af6d001a0e5',
            'judul' => "Mbappe",
            'gambar'=> '',
            'target_uang'=>1000000,
            'nominal_pengisian'=>50000,
            'jadwal_menabung'=>'minggu'
        ]);

        // Pay::create([
        //     'target_id' => 1,
        //     'uang_masuk' =>50000
        // ]);

        // Pay::create([
        //     'target_id' => 1,
        //     'uang_masuk' =>60000
        // ]);

        // User::factory(5)->create();
        // Target::factory(60)->create();
    }
}
