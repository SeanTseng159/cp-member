<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AwardRecord extends Model
{
    protected $guarded = ['id'];
    protected $primaryKey = 'award_record_id';
    protected $table = 'award_records';
    protected $connection = 'backend';
    protected $fillable = ['award_id', 'user_id', 'activity_id', 'model_name', 'model_type', 'model_spec_id', 'activity_target_id', 'barcode', 'barcode_type', 'qrcode', 'supplier_id','barcode','barcode_type'];
    public $timestamps = false;



    public function award()
    {
        return $this->belongsTo(Award::class,'award_id','award_id');

    }
    public function scopeByUser($query, $memberId)
    {
        return $query->where('user_id', $memberId);
    }

}
