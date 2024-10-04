<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRepository {
  public function __construct(
    private readonly User $model
  ) {}

  public function getByCredentials(string $email, string $password): User | null {
    $user = $this->model->whereEmail($email)->first();

    if ($user === null) {
      return null;
    }

    if (!Hash::check($password, $user->password)) {
      return null;
    }

    return $user;
  }

  public function getById(int $userId): User | null {
    return $this->model->find($userId);
  }
}
