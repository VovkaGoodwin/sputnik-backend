<?php

namespace App\Repositories\Tokens;

use App\Models\Tokens\AbstractToken;
use App\Models\Tokens\AccessToken;
use Carbon\FactoryImmutable;
use DateTimeImmutable;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;

class AccessTokenRepository extends TokenRepository {

  public function newToken(int $userId): AccessToken {
    $token = $this->jwtFacade->issue(
      new Sha256(),
      InMemory::plainText(config('auth.jwt.token_salt')),
      fn(Builder $builder, DateTimeImmutable $issuedAt) => $builder
        ->issuedBy(config('app.url'))
        ->permittedFor(config('app.url'))
        ->identifiedBy($this->generateId())
        ->expiresAt($issuedAt->modify('+' . config('auth.jwt.access_token_ttl', 3600) . ' seconds'))
        ->withClaim('userId', $userId)
    );

    return new AccessToken($token);
  }

  public function parseToken(string $token): AccessToken | null {
    try {
      $parsedToken = $this->jwtFacade->parse(
        $token,
        new SignedWith(new Sha256(), InMemory::plainText(config('auth.jwt.token_salt'))),
        new StrictValidAt(new FactoryImmutable())
      );

      return new AccessToken($parsedToken);
    } catch (RequiredConstraintsViolated) {
      return null;
    }
  }
}
