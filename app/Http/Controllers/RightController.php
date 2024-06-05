<?php

namespace App\Http\Controllers;

use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Http\Requests\ApiRequest;
use App\Http\Requests\RightAddRequest;
use App\Models\File;
use App\Models\Right;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class RightController extends Controller
{
  //
  public function add(RightAddRequest $request): JsonResponse
  {
    $response = $this->getRightsResponse($request->file_id);

    return response()->json($response);
  }

  //

  /**  Мелкая утилитка для формирования ответа с правами */
  private function getRightsResponse(string $file_id): JsonResponse|array
  {
    $file = File::where('file_id', $file_id)->first();

    if (!$file) {
      throw new NotFoundException();
    }

    /** По сути сначала надо проверить права, а уже потом вызывать функцию, ну да ладно */
    if ($file->user_id !== Auth::id()) {
      throw new ForbiddenException();
    }

    $rights = Right::where('file_id', $file->id)->with('user')->get();
    $response = [];

    $author = $file->user;
    $response[] = [
      'fullname' => "$author->first_name $author->last_name",
      'email' => $author->email,
      'type' => 'author',
      'code' => 200,
    ];

    /** TODO: вынести в коллекции */
    foreach ($rights as $access) {
      $user = $access->user;
      $response[] = [
        'fullname' => "$user->first_name $user->last_name",
        'email' => $user->email,
        'type' => 'co-author',
        'code' => 200,
      ];
    }

    return $response;
  }

  //

  public function destroy(ApiRequest $request, string $file_id): JsonResponse
  {
    $file = File::findOrFail($file_id);

    /** По хорошему это вынести в request или политику для прав */
    if ($request->user()->id !== $file->user_id) {
      throw new ForbiddenException();
    }

    $user = User::where('email', $request->email)->first();

    if (!$user) {
      throw new NotFoundException();
    }

    $right = Right::where('file_id', $file->id)->where('user_id', $user->id)->first();

    /** По хорошему это вынести в request или политику для прав */
    if (!$right) {
      throw new ForbiddenException();
    }

    $right->delete();

    $response = $this->getRightsResponse($file->id);

    return response()->json($response);
  }
}
