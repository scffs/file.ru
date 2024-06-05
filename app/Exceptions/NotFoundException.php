<?php

namespace App\Exceptions;

use Illuminate\Http\Exceptions\HttpResponseException;

class NotFoundException extends HttpResponseException
{
  // NotFoundException
  public function __construct()
  {
    parent::__construct(response()->json(['message' => 'Not Found'])->setStatusCode(404));
  }
}
