<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $connection = 'mysql';
    protected $fillable = ['code', 'name', 'tags', 'unit'];
    protected $appends = ['tags', 'qty'];

    public function setTagsAttribute(Array $tags)
    {
        $this->tags_json = json_encode($tags);
    }

    public function getTagsAttribute()
    {
        return json_decode($this->tags_json);
    }

    public function monthlyMovements()
    {
        return $this->hasMany(MaterialMonthlyMovement::class)->orderByDesc('year')->orderByDesc('month');
    }

    public function inDetails()
    {
        return $this->hasMany(MaterialInDetail::class);
    }

    public function getQtyAttribute()
    {
        $qty = 0;
        $this->load('monthlyMovements');

        foreach ($this->monthlyMovements as $monthlyMovement) {
            $qty += $monthlyMovement->in - $monthlyMovement->out;
        }

        return $qty;
    }
}
