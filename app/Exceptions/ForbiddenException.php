<?php

namespace App\Exceptions;

use Illuminate\Http\Exceptions\HttpResponseException;

class ForbiddenException extends HttpResponseException
{
  // ForbiddenException
  public function __construct()
  {
    parent::__construct(response()->json(['message' => 'Forbidden for  you'])->setStatusCode(403));
  }
}
