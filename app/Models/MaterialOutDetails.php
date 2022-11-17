<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialOutDetails extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $table = 'material_out_details';
    protected $fillable = ['material_out_id', 'mat_in_detail_id', 'qty'];

    public function insert_details(){
        $this->belongsTo(Material_in_detail::class, 'material_in_detail_id', 'id');
    }
}
