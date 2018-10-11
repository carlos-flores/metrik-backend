<?php

use Illuminate\Database\Seeder;

class UsuariosMapasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('res_users_mapas')->insert([
    'name' => 'Administrador',
    'surname' => 'Nysatec',
    'role' => 'admin',
    'email' => 'admin@nysatec.com',
    'password' => hash('sha256', 'adminadmin')

]);

    }
}
