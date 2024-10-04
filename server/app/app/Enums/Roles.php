<?php

namespace App\Enums;

enum Roles {
  case BUYER;
  case SUPPLIER;

  public static function tryFrom($value): Roles | null {
    return match (strtolower($value)) {
      strtolower(Roles::BUYER->name) => Roles::BUYER,
      strtolower(Roles::SUPPLIER->name) => Roles::SUPPLIER,
      default => null,
    };
  }
}
