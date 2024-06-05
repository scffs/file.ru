<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\RightController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//

Route::post('/authorization', [UserController::class, 'login']);
Route::post('/registration', [UserController::class, 'register']);


Route::middleware('auth:api')->group(function () {
  Route::get('/logout', [UserController::class, 'logout']);

  Route::prefix('files')->group(function () {
    // Просмотр файлов пользователя
    Route::get('/disk', [FileController::class, 'owned']);
    // Просмотр файлов, к которым имеет доступ пользователь
    Route::get('/shared', [FileController::class, 'allowed']);

    Route::post('/upload', [FileController::class, 'upload']);
    Route::patch('/{id}', [FileController::class, 'edit']);
    Route::delete('/{id}', [FileController::class, 'destroy']);
    Route::get('/{id}', [FileController::class, 'download']);

    // Добавление прав доступа
    Route::post('/{file_id}/accesses', [RightController::class, 'add']);
    // Удаление прав доступа
    Route::delete('/{file_id}/accesses', [RightController::class, 'destroy']);
  });
});


