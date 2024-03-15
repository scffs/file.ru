<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Right extends Model
{
  use HasFactory;

  protected $fillable = [
    'file_id',
    'user_id'
  ];

  public function file(): BelongsTo
  {
    return $this->belongsTo(File::class);
  }

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }
}
