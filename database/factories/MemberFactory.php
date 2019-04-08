<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\Models\Member::class, function (Faker $faker) {

    return [
        'email' => $faker->unique()->safeEmail,
        'password' => \Hash::make(123456),
        'name' => $faker->name,
        'nick' => $faker->name,
        'gender' => $faker->numberBetween(0,2),
        'birthday' => $faker->date(),
        'countryCode' => '886',
        'cellphone' => '9' . $faker->numberBetween(10000000, 99999999),
        'openPlateform' => 'citypass',
        'openid' => 'testDiningCarMember',
        'isValidPhone' => 1,
        'validPhoneCode' => '',
        'isValidEmail' => 1,
        'validEmailCode' => '',
        'status' => true,
        'isRegistered' => true,
    ];
});
