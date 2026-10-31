<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = ['sale_id','comic_id','quantity','price','subtotal'];
    protected function casts(): array { return ['price'=>'decimal:2','subtotal'=>'decimal:2']; }
    public function sale() { return $this->belongsTo(Sale::class); }
    public function comic() { return $this->belongsTo(Comic::class); }
}
