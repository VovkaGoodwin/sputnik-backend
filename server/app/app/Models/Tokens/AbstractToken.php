<?php

namespace App\Models\Tokens;

use Lcobucci\JWT\Token\RegisteredClaims;
use Lcobucci\JWT\UnencryptedToken;

abstract class AbstractToken {
  public function __construct(
    protected readonly UnencryptedToken $token,
  ) { }

  public function __toString(): string {
    return $this->token->toString();
  }

  public function getUserId(): int {
    return $this->token->claims()->get('userId', 0);
  }

  public function getId(): string {
    return $this->token->claims()->get(RegisteredClaims::ID);
  }


}
