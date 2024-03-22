<?php

namespace App\Http\Requests;

class RegisterRequest extends ApiRequest
{
  public function rules(): array
  {
    //
    return [
      'email' => 'required|email|unique:users,email',
      'password' => 'required|string|min:3|mixedCase|numbers',
      'first_name' => 'required|string|min:2',
      'last_name' => 'required|string',
    ];
  }
}
