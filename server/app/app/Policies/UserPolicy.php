<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy {
  use HandlesAuthorization;

  public function create(User | null $user): bool
  {
    return true;
  }
}
