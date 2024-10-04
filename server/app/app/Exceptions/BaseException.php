<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

abstract class BaseException extends Exception {
  abstract protected function getErrorMessage(): string;

  public function report(): void {
    Log::error($this->getErrorMessage());
  }

  public function render(): JsonResponse {
    return response()->json([
      'message' => $this->getErrorMessage(),
    ], $this->getCode());
  }
}
