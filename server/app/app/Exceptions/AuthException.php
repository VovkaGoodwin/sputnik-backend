<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class AuthException extends BaseException {
  public function __construct(
    protected string $developMessage,
    protected $message = "Authentication failed",
    $code = Response::HTTP_BAD_REQUEST
  ) {
    parent::__construct($message, $code);
  }

  public function getDevelopMessage(): string {
    return $this->developMessage;
  }

  public function getErrorMessage(): string {
    return app()->isProduction() ? $this->getMessage() : $this->getDevelopMessage();
  }
}
