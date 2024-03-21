<?php

namespace App\Http\Requests;

//use App\Models\File;

class EditRequest extends ApiRequest
{
  //
  /** Валидируем входящие данные */
  public function rules(): array
  {
    return [
      'name' => 'required|string',
    ];
  }
}
