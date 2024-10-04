<?php

namespace Database\Seeders;

use App\Enums\Roles;
use App\Models\User;
use Illuminate\Database\Seeder;
use function Laravel\Prompts\password;

class UserSeeder extends Seeder {
  public function run(): void {
    User::create([
      'email' => 'vovka@gmail.com',
      'password' => '123456',
      'name' => 'Vovka',
      'role' => Roles::BUYER
    ]);
  }
}
