<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Orion\Http\Requests\Request;

class UserController extends Controller {
  protected $model = User::class;

}
