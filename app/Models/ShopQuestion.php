<?php

namespace App\Models;

use App\Models\Ticket\Supplier;
use Illuminate\Database\Eloquent\Model;

class ShopQuestion extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'dining_car_question';
    protected $connection = 'backend';
    protected $fillable = ['dining_car_id', 'status', 'editor'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function topicList()
    {
        return $this->hasMany(ShopQuestionDetail::class, 'question_id', 'id');
    }



}
