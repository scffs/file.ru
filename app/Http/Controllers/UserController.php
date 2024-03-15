<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
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
    $creds = request(['email', 'password']);

    if (!Auth::attempt($creds)) {
      throw new ApiException(401, 'Authorization failed');
    }

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
    $token = User::create($request->all())->generateToken();
    $data = $this->getAuthResponse($token);

    return response()->json($data);
  }

  /** Выход из аккаунта */
  public function logout(ApiRequest $request): JsonResponse
  {
    $request->user()->forceFill(['remember_token' => ''])->save();

    return response()->json()->setStatusCode(204);
  }
}
