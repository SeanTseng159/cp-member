<?php
/**
 * User: Lee
 * Date: 2018/11/20
 * Time: 下午 02:57
 */

namespace App\Cache\Key;

class CartKey
{
  const ONE_OFF_KEY = 'cart.oneOff.%s';
  const MARKET_KEY = 'cart.market.%s';
  const GUEST_KEY = 'cart.guest.%s';
}
