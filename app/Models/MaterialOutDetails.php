<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialOutDetails extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $fillable = ['material_out_id', 'mat_in_detail_id', 'qty'];

    public function insert_details(){
        return $this->belongsTo(Material_in_details::class, 'mat_in_detail_id', 'id');
    }
}
