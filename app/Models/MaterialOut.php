<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialOut extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $fillable = ['code', 'at', 'type', 'created_by_user_id', 'last_updated_by_user_id', 'note', 'desc', 'history_json'];

    public function detail_outs(){
        return $this->hasMany(MaterialOutDetails::class);
    }
}
