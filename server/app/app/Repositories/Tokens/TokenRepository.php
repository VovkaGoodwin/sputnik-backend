<?php

namespace App\Repositories\Tokens;

use App\Models\Tokens\AbstractToken;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Lcobucci\JWT\JwtFacade;

abstract class TokenRepository {
  public function __construct(
    protected readonly JwtFacade $jwtFacade
  ) { }


  abstract public function newToken(int $userId): AbstractToken;

  abstract public function parseToken(string $token): AbstractToken | null;

  public function saveToken(AbstractToken $token): void {
    Cache::set($token->getId(), (string) $token, config('auth.jwt.refresh_token_ttl'));
  }

  public function isTokenRevoked(AbstractToken $token): bool {
    return !Cache::has($token->getId());
  }

  protected function generateId(): string {
    return Str::random(40);
  }
}
