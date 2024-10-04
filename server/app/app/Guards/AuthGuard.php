<?php

namespace App\Guards;

use App\Services\AuthService;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

class AuthGuard implements Guard {
  use GuardHelpers;

  public function __construct(
    private readonly Request $request,
    private readonly AuthService $authService,
  ) {}

  /**
   * @inheritDoc
   */
  public function user() {
    if ($this->user !== null) {
      return $this->user;
    }

    $this->user = $this->authService->checkAuthorization($this->request->header('Authorization', ''));

    return $this->user;
  }


  /**
   * @inheritDoc
   */
  public function validate(array $credentials = []) {
    // TODO: Implement validate() method.
  }
}
