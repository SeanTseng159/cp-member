<?php

use Illuminate\Database\Seeder;

class MembersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        $data = [
//        	'email' => 'ksd@gmail.com',
//        	'password' => \Hash::make('123456'),
//        	'name' => '高大大',
//        	'nick' => '高高',
//        	'gender' => '1',
//        	'birthday' => '1999-12-11',
//        	'country' => 'tw',
//        	'countryCode' => '+86',
//        	'cellphone' => '912555666',
//        	'zipcode' => '82212',
//        	'county' => '高雄市',
//        	'district' => '新興區',
//        	'address' => '信守街104號',
//        	'openPlateform' => 'citypass',
//        	'openid' => '',
//        	'avatar' => '',
//        	'active_code' => '123456',
//        	'memo' => '',
//        	'status' => true
//        ];
//        DB::table('members')->insert($data);

        factory(App\Models\Member::class, 10000)->create();
    }
}
