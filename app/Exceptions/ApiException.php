<?php

namespace App\Exceptions;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\MessageBag;

class ApiException extends HttpResponseException
{
  //
  public function __construct(int $code, string $message, MessageBag|array $errors = [])
  {
    $data = [
      'success' => false,
      'code' => $code
    ];

    if (!empty($message)) {
      $data['message'] = $message;
    }

    if (count($errors)) {
      $data['message'] = $errors;
    }

    parent::__construct(response()->json($data)->setStatusCode($code));
  }
}
