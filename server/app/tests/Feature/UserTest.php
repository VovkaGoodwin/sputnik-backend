<?php

namespace Tests\Feature;

use App\Enums\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserTest extends TestCase {
  use RefreshDatabase;

  public static function createUserDataProvider(): array {
    return [
      'successfullyCreated' => [
        'data' => [
          'name' => 'vovka',
          'email' => 'vovka@gmail.com',
          'password' => 'password123456',
          'role' => Roles::BUYER->name,
        ],
        'assertion' => function (TestResponse $response) {
          $response->assertCreated();
        }
      ],
      'wrongPasswordWoNumbers' => [
        'data' => [
          'name' => 'vovka',
          'email' => 'vovka@gmail.com',
          'password' => 'password',
          'role' => Roles::BUYER->name,
        ],
        'assertion' => function (TestResponse $response) {
          $response->assertUnprocessable();
          $response->assertJsonValidationErrors('password');
        }
      ],
      'wrongPasswordWoLetters' => [
        'data' => [
          'name' => 'vovka',
          'email' => 'vovka@gmail.com',
          'password' => '12345678',
          'role' => Roles::BUYER->name,
        ],
        'assertion' => function (TestResponse $response) {
          $response->assertUnprocessable();
          $response->assertJsonValidationErrors('password');
        }
      ],
      'wrongPasswordLength' => [
        'data' => [
          'name' => 'vovka',
          'email' => 'vovka@gmail.com',
          'password' => '11ww',
          'role' => Roles::BUYER->name,
        ],
        'assertion' => function (TestResponse $response) {
          $response->assertUnprocessable();
          $response->assertJsonValidationErrors('password');
        }
      ],
      'wrongRole' => [
        'data' => [
          'name' => 'vovka',
          'email' => 'vovka@gmail.com',
          'password' => '123456qwerty',
          'role' => 'admin',
        ],
        'assertion' => function (TestResponse $response) {
          $response->assertUnprocessable();
          $response->assertJsonValidationErrors('role');
        }
      ]
    ];
  }

  #[DataProvider('createUserDataProvider')]
  public function testCreateUser($data, $assertion) {
    $response = $this->post(route('api.users.store'), $data);
    $assertion($response);
  }
}
