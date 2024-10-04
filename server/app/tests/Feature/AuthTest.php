<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AuthTest extends TestCase {

  use RefreshDatabase;

  protected function setUp(): void {
    parent::setUp();
    $this->seed([UserSeeder::class]);
  }

  public function testLogin() {
    $response = $this->post(
      route('auth.login'),
      ['email' => 'vovka@gmail.com', 'password' => '123456']
    );

    $response->assertStatus(200);
  }

  public function testLogout() {
    $response = $this->post(
      route('auth.login'),
      ['email' => 'vovka@gmail.com', 'password' => '123456']
    );

    $response->assertStatus(200);

    $response = $this->post(
      route('auth.logout'),
      headers: [
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . $response->json('access_token')
      ]
    );

    $response->assertNoContent();
    $response->assertCookie("RefreshToken", "", false);
  }
}
