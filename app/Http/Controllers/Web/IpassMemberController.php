<?php
/**
 * User: Lee
 * Date: 2017/12/20
 * Time: 下午2:20
 */

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;


class IpassMemberController extends Controller
{
    public function __construct() {

    }

    /**
     * 登入
     * @param Illuminate\Http\Request $request
     */
    public function login(Request $request)
    {
        echo 'test';
    }
}
