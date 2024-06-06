<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Target>
 */
class TargetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => rand(1,5),
            'judul' => fake()->city(),
            'gambar'=> '',
            'target_uang'=>rand(50000,10000000),
            'nominal_pengisian'=>rand(10000,50000),
            'jadwal_menabung'=>fake()->randomElement(['hari','minggu','bulan']),
        ];
    }
}
