<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeldTransaction extends Model
{
    protected $fillable = ['user_id', 'reference', 'draft'];

    protected function casts(): array
    {
        return ['draft' => 'array'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
