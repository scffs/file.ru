<?php

namespace App\Http\Requests;

use App\Exceptions\ApiException;
use Illuminate\Support\Facades\Auth;

class LoginRequest extends ApiRequest
{
  public function authorize(): bool
  {
    /** креды от аккаунта */
    $creds = request(['email', 'password']);

    /** чек на валидность */
    if (!Auth::attempt($creds)) {
      throw new ApiException(401, 'Authorization failed');
    }

    return true;
  }
  //
  /** Валидируем входящие данные */
  public function rules(): array
  {
    return [
      'email' => 'required|email',
      'password' => 'required|string|min:3',
    ];
  }
}
