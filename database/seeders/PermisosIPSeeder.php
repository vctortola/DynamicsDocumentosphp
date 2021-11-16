<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermisosIPSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('permisosip')->insert(['ip' => '181.174.76.212', "descripcion" => "IP Universidad Galileo"]);
        DB::table('permisosip')->insert(['ip' => '186.151.141.183', "descripcion" => "IP casa Edy"]);
    }
}
