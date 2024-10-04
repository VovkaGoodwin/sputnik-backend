<?php

namespace App\Services;

use App\Exceptions\AuthException;
use App\Models\User;
use App\Repositories\Tokens\AccessTokenRepository;
use App\Repositories\Tokens\RefreshTokenRepository;
use App\Repositories\UserRepository;

class AuthService {

  public function __construct(
    private readonly AccessTokenRepository $accessTokenRepository,
    private readonly RefreshTokenRepository $refreshTokenRepository,
    private readonly UserRepository $userRepository,
  ) {}

  public function login(string $email, string $password): array {
    $user = $this->userRepository->getByCredentials($email, $password);
    throw_if($user === null, AuthException::class, __('auth.exceptions.user_not_found'));

    $accessToken = $this->accessTokenRepository->newToken($user->id);
    $refreshToken = $this->refreshTokenRepository->newToken($user->id, $accessToken->getId());

    $this->accessTokenRepository->saveToken($accessToken);
    $this->refreshTokenRepository->saveToken($refreshToken);

    return [
      (string) $accessToken,
      (string) $refreshToken,
      $user,
    ];
  }

  public function logout() {
    // TODO: Implement logout() method.
  }

  public function checkAuthorization(string $header): User|null {
    if (strlen($header) === 0) {
      return null;
    }

    [$token] = sscanf($header, 'Bearer %s');
    if ($token === null) {
      return null;
    }

    $decodedToken = $this->accessTokenRepository->parseToken($token);
    if ($decodedToken === null) {
      return null;
    }

    if ($this->accessTokenRepository->isTokenRevoked($decodedToken)) {
      return null;
    }

    return $this->userRepository->getById($decodedToken->getUserId());
  }

  public function refresh(string $refreshToken): array | null {
    $parsedToken = $this->refreshTokenRepository->parseToken($refreshToken);
    if ($parsedToken === null) {
      return null;
    }

    $user = User::whereId($parsedToken->getUserId())->first();
    if ($user === null) {
      return null;
    }

    $accessToken = $this->accessTokenRepository->newToken($user->id);
    $refreshToken = $this->refreshTokenRepository->newToken($accessToken->getId(), $user->id);

    $this->accessTokenRepository->saveToken($accessToken);
    $this->refreshTokenRepository->saveToken($refreshToken);

    return [
      (string) $accessToken,
      (string) $refreshToken,
      $user,
    ];
  }
}
