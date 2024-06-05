<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Exceptions\ForbiddenException;
use App\Http\Requests\ApiRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
  /** Авторизация */
  public function login(LoginRequest $request): JsonResponse
  {
    /** креды от аккаунта */
    $creds = request(['email', 'password']);

    /** чек на валидность */
    if (!Auth::attempt($creds)) {
      throw new ApiException(401, 'Authorization failed');
    }

    /** генерация токена + формирования ответа */
    $token = $request->user()->generateToken();
    $data = $this->getAuthResponse($token);

    return response()->json($data);
  }

  /** Формирует ответ согласно требованиям */
  protected function getAuthResponse(string $token, int $code = 200): array
  {
    return [
      'code' => $code,
      'token' => $token,
      'success' => true,
      'message' => 'Success'
    ];
  }

  /** Регистрация */
  public function register(RegisterRequest $request): JsonResponse
  {
    throw new ForbiddenException();

    /** созадние юзера + генерация токена + формирования ответа */
    $token = User::create($request->all())->generateToken();
    $data = $this->getAuthResponse($token);

    return response()->json($data);
  }

  /** Выход из аккаунта */
  public function logout(ApiRequest $request): JsonResponse
  {
    /** удаление токена из бд */
    $request->user()->forceFill(['remember_token' => ''])->save();

    return response()->json()->setStatusCode(204);
  }
}
