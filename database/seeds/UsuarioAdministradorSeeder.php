<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsuarioAdministradorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('usuario')->insert([

            'usuario'=>'jcastro', 
            'nombre'=>'Jhonnathan Castro', 
            'tipodeusuario'=>'administrador', 
            'email'=>'castrokof@gmail.com',
            'empresa'=>'acuaprogrammer',  
            'password'=>bcrypt('123456'),
            'remenber_token'=>bcrypt('123456'),
            'estado'=>'activo', 
            

        ]);
        
        DB::table('usuario')->insert([

            'usuario'=>'pruebas', 
            'nombre'=>'Pruebas uno', 
            'tipodeusuario'=>'supervisor', 
            'email'=>'pruebas@gmail.com',
            'empresa'=>'acuaprogrammer',  
            'password'=>bcrypt('123456'),
            'remenber_token'=>bcrypt('123456'),
            'estado'=>'activo',
            

        ]);
        DB::table('usuario_rol')->insert([

            'rol_id'=>1, 
            'usuario_id'=>1, 
             
                      

        ]);
        DB::table('usuario_rol')->insert([

            'rol_id'=>2, 
            'usuario_id'=>2, 
             
                      

        ]);
    }
}
