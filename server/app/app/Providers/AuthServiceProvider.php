<?php

namespace App\Providers;


use App\Guards\AuthGuard;
use Carbon\FactoryImmutable;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Lcobucci\JWT\JwtFacade;

class AuthServiceProvider extends ServiceProvider {
  public function boot(): void {
    $this->app->bind(JwtFacade::class, function () {
      return new JwtFacade(clock: new FactoryImmutable());
    });

    Auth::extend('token', fn(Container $app) => $app->make(AuthGuard::class));
  }
}
