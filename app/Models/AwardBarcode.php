<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AwardBarcode extends Model
{
    protected $guarded = ['award_barcode_id'];
    protected $table = 'award_barcodes';
    protected $connection = 'backend';
    public $timestamps = false;
}
