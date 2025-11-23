<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prompt extends Model
{
    protected $fillable = [
        'key',
        'name',
        'prompt',
        'model',
        'max_tokens',
        'temperature',
    ];

    protected $casts = [
        'max_tokens' => 'integer',
        'temperature' => 'float',
    ];

    public static function get(string $key): ?self
    {
        return static::where('key', $key)->first();
    }

    public static function getPrompt(string $key): ?string
    {
        return static::where('key', $key)->value('prompt');
    }

    public function render(array $variables = []): string
    {
        $text = $this->prompt;
        foreach ($variables as $key => $value) {
            $text = str_replace('{' . $key . '}', $value, $text);
        }
        return $text;
    }
}
