<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController {

  private const REFRESH_TOKEN = 'RefreshToken';

  public function __construct(
    private readonly AuthService $authService,
  ) {}

  public function login(LoginRequest $request) {
    [$accessToken, $refreshToken, $user] = $this->authService->login(
      $request->validated('email'),
      $request->validated('password')
    );

    return response()->json([
      'access_token' => $accessToken,
      'token_type' => 'Bearer',
      'user' => $user,
    ])->withCookie(cookie(
      name: self::REFRESH_TOKEN,
      value: $refreshToken,
      minutes: config('auth.jwt.refresh_token_ttl')/60,
      path: route(name: 'auth.refresh', absolute: false),
      secure: false
    ));
  }

  public function logout() {
    $this->authService->logout();

    return response()->noContent()->withCookie(cookie(
      name: self::REFRESH_TOKEN,
      value: '',
      minutes: -1,
    ));
  }

  public function refresh(Request $request) {
    [$accessToken, $refreshToken, $user] = $this->authService->refresh($request->cookie(self::REFRESH_TOKEN));

    return response()->json([
      'accessToken' => $accessToken,
      'token_type' => 'Bearer',
      'user' => $user,
    ])->withCookie(cookie(
      name: self::REFRESH_TOKEN,
      value: $refreshToken,
      minutes: config('auth.jwt.refresh_token_ttl')/60,
      path: route(name: 'auth.refresh', absolute: false),
      secure: false
    ));
  }
}
