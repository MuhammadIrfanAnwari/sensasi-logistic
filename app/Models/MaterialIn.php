<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaterialIn extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $connection = "mysql";
    protected $fillable = ['code', 'at', 'type', 'created_by_user_id', 'last_updated_by_user_id', 'note'];
    protected $dates = [
        'at'
    ];

    public function details()
    {
        return $this->hasMany(MaterialInDetail::class);
    }

    public function outDetails()
    {
        return $this->hasManyThrough(MaterialOutDetail::class, MaterialInDetail::class)->has('materialOut');
    }
}
