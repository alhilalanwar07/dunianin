<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;
    use HasUlids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'level',
        'tipe_engine',
        'payload',
        'difficulty',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    public function playerLogs(): HasMany
    {
        return $this->hasMany(PlayerQuestionLog::class);
    }
}
