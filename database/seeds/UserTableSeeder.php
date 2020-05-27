<?php

use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name'=>'ADMIN',
            'email'=>'admin@healthcare.com',
            'password'=>'$2y$12$aYUhXB8gW0EI8RMaL.MZMOHL6OezZQl/rycpjiBK/4I1HqehZjWEO',
            'u_tp_id'=>'1',
            'base_allowances'=>'200',
            'private_mileage'=>'100',
            'contact_no'=>'0123456789',
            'user_name'=>'admin',
            'created_at'=>date("Y-m-d H:i:s"),
            'updated_at'=>date("Y-m-d H:i:s"),
            'price_list'=>'1'
        ]);
    }
}
