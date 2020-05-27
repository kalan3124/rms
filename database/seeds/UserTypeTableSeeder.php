<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_types')->insert([
            'user_type'=>"ADMIN",
            "main_user_type"=>1,
            "created_at"=>date('Y-m-d H:i:s'),
            "updated_at"=>date('Y-m-d H:i:s')
        ]);
    }
}
