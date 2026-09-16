<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\LogsActivity;

class Setting extends Model
{
    use LogsActivity;
    
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
    ];

    public function getTypedValueAttribute(): mixed
    {
        return match ($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $this->value,
            'float' => (float) $this->value,
            'json', 'array' => json_decode($this->value, true),
            default => $this->value,
        };
    }
}
