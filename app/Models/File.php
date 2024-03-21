<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class File extends Model
{
  use HasFactory;

  protected $fillable = [
    'name',
    'extension',
    'path',
    'file_id',
    'user_id',
  ];

  //
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  //
  public function file(): BelongsTo
  {
    return $this->belongsTo(File::class, 'file_id');
  }

  //
  public function rights(): HasMany
  {
    return $this->hasMany(Right::class);
  }
}
