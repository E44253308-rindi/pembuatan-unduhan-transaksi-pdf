<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comic extends Model
{
    protected $fillable = ['category_id','title','author','publisher','price','stock','synopsis','cover','barcode'];
    protected function casts(): array { return ['price' => 'decimal:2']; }
    public function category() { return $this->belongsTo(Category::class); }
    public function saleItems() { return $this->hasMany(SaleItem::class); }
}
