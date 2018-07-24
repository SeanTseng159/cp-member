<?php
/**
 * User: lee
 * Date: 2018/03/04
 * Time: 上午 9:42
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MagentoProduct extends Model
{
    protected $guarded = ['id'];

    protected $appends = ['source'];

	/**
     * 加入來源
     */
	public function getSourceAttribute($value)
    {
    	return 'magento';
    }
}
