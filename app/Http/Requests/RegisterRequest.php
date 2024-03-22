<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rules\Password;

class RegisterRequest extends ApiRequest
{
  public function rules(): array
  {
    //
    return [
      'email' => 'required|email|unique:users,email',
      'password' => ['required', Password::min(3)->numbers()->mixedCase()],
      'first_name' => 'required|string|min:2',
      'last_name' => 'required|string',
    ];
  }
}
