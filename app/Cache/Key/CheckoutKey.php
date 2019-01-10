<?php
/**
 * User: Lee
 * Date: 2018/11/20
 * Time: 下午 02:57
 */

namespace App\Cache\Key;

class CheckoutKey
{
  const PAYMENT_METHOD_KEY = 'checkout.paymentMethod';
  const CREDIT_CARD_KEY = 'checkout.creditCard.%s';
}
