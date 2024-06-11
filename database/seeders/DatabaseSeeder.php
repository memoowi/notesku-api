<?php

namespace Database\Seeders;

use App\Models\Background;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Misbach',
            'email' => 'misbach@gmail.com',
            'password' => Hash::make('12345678'),
        ]);
        Background::create([
            'color_name' => 'red',
            'color_code' => '#ff0000',
        ]);
        Background::create([
            'color_name' => 'green',
            'color_code' => '#00ff00',
        ]);
        Background::create([
            'color_name' => 'blue',
            'color_code' => '#0000ff',
        ]);
        Background::create([
            'color_name' => 'yellow',
            'color_code' => '#ffff00',
        ]);
        Background::create([
            'color_name' => 'grey',
            'color_code' => '#808080',
        ]);
        
    }
}
