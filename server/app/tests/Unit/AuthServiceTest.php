<?php

namespace Tests\Unit;

use App\Exceptions\AuthException;
use App\Models\Tokens\AccessToken;
use App\Models\Tokens\RefreshToken;
use App\Models\User;
use App\Repositories\Tokens\AccessTokenRepository;
use App\Repositories\Tokens\RefreshTokenRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use Illuminate\Database\Eloquent\Builder;
use Lcobucci\JWT\Token\DataSet;
use Lcobucci\JWT\UnencryptedToken;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthServiceTest extends TestCase {

  public static function checkAuthorizeDataProvider(): array {
    return [
      'noHeader' => [
        'authHeader' => '',
        'mockBehavior' => function () {
        },
        'assertResult' => function ($result) {
          Assert::assertNull($result);
        },
      ],
      'wrongHeader' => [
        'authHeader' => 'asdasdasdk',
        'mockBehavior' => function () {
        },
        'assertResult' => function ($result) {
          Assert::assertNull($result);
        }
      ],
      'notValidHeader' => [
        'authHeader' => 'Bearer test_token',
        'mockBehavior' => function (
          MockInterface $userRepository,
          MockInterface $accessTokenRepository,
        ) {
          $accessTokenRepository->shouldReceive('parseToken')->andReturn(null);
        },
        'assertResult' => function ($result) {
          Assert::assertNull($result);
        }
      ],
      'tooOldToken' => [
        'authHeader' => 'Bearer test_token',
        'mockBehavior' => function (
          MockInterface $userRepository,
          MockInterface $accessTokenRepository,
        ) {
          $tokenId = '1234567890';
          $accessToken = new AccessToken(Mockery::mock(UnencryptedToken::class)
            ->shouldReceive('claims')
            ->andReturn(new DataSet(['jti' => $tokenId], ''))
            ->shouldReceive('toString')
            ->andReturn("access_token")
            ->getMock()
          );

          $accessTokenRepository->shouldReceive('parseToken')->andReturn($accessToken);
          $accessTokenRepository
            ->shouldReceive('isTokenRevoked')
            ->with($accessToken)
            ->andReturn(true);
        },
        'assertResult' => function ($result) {
          Assert::assertNull($result);
        }
      ],
      'wrongUserIdInToken' => [
        'authHeader' => 'Bearer test_token',
        'mockBehavior' => function (
          MockInterface $userRepository,
          MockInterface $accessTokenRepository,
        ) {
          $tokenId = '1234567890';
          $userId = 1;
          $accessToken = new AccessToken(Mockery::mock(UnencryptedToken::class)
            ->shouldReceive('claims')
            ->andReturn(new DataSet(['jti' => $tokenId, 'userId' => $userId], ''))
            ->shouldReceive('toString')
            ->andReturn("access_token")
            ->getMock()
          );
          $accessTokenRepository->shouldReceive('parseToken')
            ->with('test_token')
            ->andReturn($accessToken);
          $accessTokenRepository->shouldReceive('isTokenRevoked')->with($accessToken)->andReturn(false);

          $userRepository->shouldReceive('getById')->with($userId)->andReturn(null);
        },
        'assertResult' => function ($result) {
          Assert::assertNull($result);
        }
      ],
      'correctToken' => [
        'authHeader' => 'Bearer test_token',
        'mockBehavior' => function (
          MockInterface $userRepository,
          MockInterface $accessTokenRepository,
        ) {
          $tokenId = '1234567890';
          $userId = 1;
          $accessToken = new AccessToken(Mockery::mock(UnencryptedToken::class)
            ->shouldReceive('claims')
            ->andReturn(new DataSet(['jti' => $tokenId, 'userId' => $userId], ''))
            ->shouldReceive('toString')
            ->andReturn("access_token")
            ->getMock()
          );

          $accessTokenRepository->shouldReceive('parseToken')
            ->with('test_token')
            ->andReturn($accessToken);
          $accessTokenRepository->shouldReceive('isTokenRevoked')
            ->with($accessToken)
            ->andReturn(false);

          $userRepository->shouldReceive('getById')->with($userId)->andReturn(
            Mockery::mock(User::class)
          );
        },
        'assertResult' => function ($result) {
          Assert::assertNotNull($result);
          Assert::assertInstanceOf(User::class, $result);
        }
      ],
    ];
  }

  public static function loginDataProvider(): array {

    $userId = 1;
    $accessToken = 'access_token';
    $refreshToken = 'refresh_token_id';

    return [
      'wrongCredentials' => [
        'data' => [
          'email' => 'wrong_email',
          'password' => 'wrong_password'
        ],
        'mockBehavior' => function (
          MockInterface $userRepository,
        ) {
          $userRepository->shouldReceive('getByCredentials')
            ->with('wrong_email', 'wrong_password')
            ->andReturn(null);
        },
        'assertResult' => function () {},
        'exception' => true
      ],
      'correct credentials' => [
        'data' => [
          'email' => 'correct_email',
          'password' => 'correct_password'
        ],
        'mockBehavior' => function (
          MockInterface $userRepository,
          MockInterface $accessTokenRepository,
          MockInterface $refreshTokenRepository,
        ) use ($userId, $accessToken, $refreshToken, ) {
          $accessTokenId = 'access_token_id';
          $refreshTokenId = 'refresh_token_id';
          $userRepository->shouldReceive('getByCredentials')
            ->with('correct_email', 'correct_password')
            ->andReturn(User::factory()->make([
              'email' => 'correct_email',
              'password' => 'correct_password',
              'id' => $userId,
            ]));

          $accessTokenRepository->shouldReceive('newToken')
            ->with($userId)
            ->andReturn(Mockery::mock(AccessToken::class)
              ->shouldReceive('getId')
              ->andReturn($accessTokenId)
              ->shouldReceive('__toString')
              ->andReturn($accessToken)
              ->getMock()
            );
          $accessTokenRepository->shouldReceive('saveToken')->once();


          $refreshTokenRepository->shouldReceive('newToken')
            ->with($userId, $accessTokenId)
            ->andReturn(
              Mockery::mock(RefreshToken::class)
                ->shouldReceive('__toString')
                ->andReturn($refreshToken)
                ->getMock()
            );
          $refreshTokenRepository->shouldReceive('saveToken')->once();

          $accessTokenRepository->shouldReceive('saveToken')->with($accessTokenId, $accessToken);
          $refreshTokenRepository->shouldReceive('saveToken')->with($refreshTokenId, $refreshToken);
        },
        'assertResult' => function (array $result) use ($userId, $accessToken, $refreshToken) {
          Assert::assertEquals($accessToken, $result[0]);
          Assert::assertEquals($refreshToken, $result[1]);
          Assert::assertInstanceOf(User::class, $result[2]);
        },
      ]
    ];
  }

  #[DataProvider('loginDataProvider')]
  public function testLogin(array $data, callable $mockBehavior, callable $assertResult, bool $exception = false): void {
    if ($exception) {
      $this->expectException(AuthException::class);
    }

    $userRepository = Mockery::mock(UserRepository::class);
    $accessTokenRepository = Mockery::mock(AccessTokenRepository::class);
    $refreshTokenRepository = Mockery::mock(RefreshTokenRepository::class);

    $mockBehavior($userRepository, $accessTokenRepository, $refreshTokenRepository);

    $service = new AuthService(
      $accessTokenRepository,
      $refreshTokenRepository,
      $userRepository,
    );

    $result = $service->login($data['email'], $data['password']);

    $assertResult($result);
  }

  #[DataProvider('checkAuthorizeDataProvider')]
  public function testCheckAuthorization(string $authHeader, callable $mockBehavior, callable $assertResult) {

    $userRepository = Mockery::mock(UserRepository::class);
    $accessTokenRepository = Mockery::mock(AccessTokenRepository::class);
    $refreshTokenRepository = Mockery::mock(RefreshTokenRepository::class);

    $mockBehavior($userRepository, $accessTokenRepository, $refreshTokenRepository);

    $service = new AuthService(
      $accessTokenRepository,
      $refreshTokenRepository,
      $userRepository,
    );

    $result = $service->checkAuthorization($authHeader);

    $assertResult($result);
  }
}
