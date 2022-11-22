<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialInDetail extends Model
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $fillable = ['material_in_id', 'material_id', 'qty', 'price'];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function materialIn()
    {
        return $this->belongsTo(MaterialIn::class);
    }

    public function getQtyRemainAttribute()
    {
        return $this->stock->qty;
    }

    public function stock()
    {
        return $this->hasOne(MaterialInDetailsStockView::class);
    }

    public static function search($q)
    {
        return self::with(['material', 'materialIn', 'stock'])
            ->whereRelation('material', 'name', 'LIKE', "%${q}%")
            ->orWhereRelation('materialIn', 'at', 'LIKE', "%${q}%");
    }
}
