<?php

use App\Enums\AccessGrants;
use App\Enums\Roles;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('users', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('email')->unique();
      $table->string('password');
      $table->enum('role', Arr::map(Roles::cases(), fn(Roles $role) => $role->name));
      $table->enum('access', Arr::map(AccessGrants::cases(), fn(AccessGrants $grant) => $grant->name))
        ->default(AccessGrants::GUEST->name);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    if (!app()->isProduction()) {
      Schema::dropIfExists('users');
    }
  }
};
