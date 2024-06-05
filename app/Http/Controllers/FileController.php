<?php

namespace App\Http\Controllers;

use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Http\Requests\ApiRequest;
use App\Http\Requests\EditRequest;
use App\Models\File;
use App\Models\Right;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileController extends Controller
{
  //
  public function upload(ApiRequest $request): JsonResponse
  {
    $pathToUpload = 'uploads/' . $request->user()->id . '/';

    /** Возвращаемый объект */
    $data = [];
    /** Базовый объект в случае успеха */
    $successData = [
      'success' => true,
      'code' => 200,
      'message' => 'success',
    ];

    foreach ($request->file('files') as $file) {
      /** Валидация файла */
      $validator = Validator::make(['file' => $file], [
        'file' => 'max:2048|mimes:doc,pdf,docx,zip,jpeg,jpg,png',
      ]);

      if ($validator->fails()) {
        /** Сохранение плохого ответа API */
        $data[] = [
          'success' => false,
          'message' => $validator->errors(),
          'name' => $file->getClientOriginalName(),
        ];
        continue;
      }

      /** Генерируем оригинальное имя файла */
      $fileName = $this->generateFileName($file, $pathToUpload);
      /** Генерируем file_id */
      $fileId = Str::random(10);

      try {
        $file->storeAs($pathToUpload, $fileName);

        File::create([
          'name' => pathinfo($fileName, PATHINFO_FILENAME),
          'extension' => $file->extension(),
          'path' => $pathToUpload,
          'file_id' => $fileId,
          'user_id' => $request->user()->id,
        ]);

        /** url = адрес сервера */
        $url = url("files/$fileId");

        $fileData = [
          'name' => $fileName,
          'url' => $url,
          'file_id' => $fileId,
        ];

        /** Сливаем два массива в один - итоговый */
        $data[] = array_merge($successData, $fileData);

      } catch (Exception $e) {
        $data[] = [
          'success' => false,
          'message' => $e->getMessage(),
          'name' => $file->getClientOriginalName(),
        ];
      }
    }

    return response()->json($data);
  }

  /**
   * Генирирует уникальное имя для файла в случае, если файл с таким именем уже существует
   */
  private function generateFileName(UploadedFile $file, string $pathToUpload): string
  {
    /** Получаем расширение файла */
    $extension = $file->getClientOriginalExtension();
    /** Получаем имя файла */
    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
    /** Формируем имя файла */
    $fileName = $originalName . "." . $extension;
    $number = 1;

    /** Обновляем имя, пока элемент с таким именем существует */
    while (Storage::exists($pathToUpload . $fileName)) {
      $fileName = $originalName . " ($number)." . $extension;
      $number++;
    }

    return $fileName;
  }

  //
  public function edit(EditRequest $request): JsonResponse
  {
    $newFileName = $request->name;
    $fileId = $request->id;

    $file = File::where('file_id', $fileId)->first();

    /** Нет файла в БД или на сервере */
    if (!$file || !Storage::exists($file->path)) {
      throw new NotFoundException();
    }

    /** Нет прав на редактирование */
    if ($file->user_id != $request->user()->id) {
      throw new ForbiddenException();
    }

    $basePath = $file->path;
    $oldPath = $basePath . "$file->name.$file->extension";
    $newPath = $basePath . "$newFileName.$file->extension";

    /** Переименовываем */
    Storage::move($oldPath, $newPath);
    File::where('file_id', $fileId)->update(
      ['name' => $newFileName]
    );

    $data = [
      'success' => true,
      'code' => 200,
      'message' => 'Renamed',
    ];

    return response()->json($data);
  }

  //
  public function destroy(string $file_id): JsonResponse
  {
    $file = File::where('file_id', $file_id)->first();
    if (!$file || !Storage::exists($file->path)) {
      throw new NotFoundException();
    }

    if ($file->user_id !== Auth::id()) {
      throw new ForbiddenException();
    }

    $file->delete();
    $fullPath = $file->path . "$file->name.$file->extension";
    Storage::delete($fullPath);

    $data = [
      'success' => true,
      'code' => 200,
      'message' => 'File deleted',
    ];

    return response()->json($data);
  }

  //
  public function download(string $file_id): BinaryFileResponse|string
  {
    $file = File::where('file_id', $file_id)->first();

    if (!$file) {
      throw new NotFoundException();
    }

    $coAuthor = Right
      ::where('user_id', Auth::id())
      ->where('file_id', $file->id)
      ->first();

    if ($file->user_id !== Auth::id() || !$coAuthor) {
      throw new ForbiddenException();

    }

    $path = Storage::disk("local")->path($file->path . "$file->name.$file->extension");
    return response()->download($path, basename($path));
  }

  //
  public function owned(ApiRequest $request): JsonResponse
  {
    $userId = $request->user()->id;

    // Получаем файлы, загруженные текущим пользователем
    $files = File::where('user_id', $userId)->with('rights.user')->get();

    if (!$files) {
      throw new NotFoundException();
    }

    // Формируем ответ
    /** TODO: вынести в коллекцию */
    $response = [];
    foreach ($files as $file) {
      $accesses = [];

      foreach ($file->rights as $right) {
        $accesses[] = [
          'fullname' => $right->user->name . ' ' . $right->user->lastName,
          'email' => $right->user->email,
          'type' => 'co-author',
        ];
      }

      $response[] = [
        'file_id' => $file->file_id,
        'name' => $file->name,
        'code' => 200,
        'url' => url("files/$file->file_id"),
        'accesses' => $accesses
      ];
    }

    return response()->json($response);
  }


  public function allowed(): JsonResponse
  {
    $userId = auth()->id();

    $filesWithAccess = Right::where('user_id', $userId)
      ->with('file')
      ->get()
      ->pluck('file');

    if (!$filesWithAccess) {
      throw new NotFoundException();
    }

    /** Формируем ответ, исключая файлы, загруженные самим пользователем */
    /** TODO: вынести в коллекцию */
    $response = [];
    foreach ($filesWithAccess as $file) {
      if ($file->user_id == $userId) {
        continue;
      }

      $response[] = [
        'file_id' => $file->file_id,
        'code' => 200,
        'name' => $file->name,
        'url' => url("files/$file->file_id")
      ];
    }

    return response()->json($response);
  }
}
