<?php
/**
 * User: Lee
 * Date: 2017/10/27
 * Time: 下午2:20
 */

namespace Ksd\OAuth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OauthClientMember extends Model
{
	use SoftDeletes;

    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];
}
