<?php

namespace App\Http\Requests;

class LoginRequest extends ApiRequest
{

  /** Валидируем входящие данные */
  public function rules(): array
  {
    return [
      'email' => 'required|email',
      'password' => 'required|string|min:3',
    ];
  }
}
