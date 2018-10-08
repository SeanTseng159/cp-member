<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LinepayStore extends Model
{
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at'];
    
    /**
     * 取得範圍內的商店
     * @param Bulider $query
     * @param array $longitude  [min, max]
     * @param array $latitude   [min, max]
     * @return Bulider
     */
    public function scopeWithinLocation($query, $longitude, $latitude)
    {
        return $query->whereBetween('longitude', $longitude)
                     ->whereBetween('latitude', $latitude);
    }
}
