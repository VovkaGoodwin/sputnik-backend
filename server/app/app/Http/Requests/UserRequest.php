<?php

namespace App\Http\Requests;

use App\Enums\Roles;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends BaseRequest {
  public function storeRules(): array {
    return [
      'name' => 'required',
      'email' => 'required|email|unique:users',
      'password' => ['required', Password::min(8)->letters()->numbers()],
      'role' => ['required', Rule::enum(Roles::class)],
    ];
  }
}
