<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\Foreach_;

class RolTablaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rols=[

            'administrador',
            'movil',
            'supervisor'

        ];

        foreach ($rols as $key => $value) {
            DB::table('rol')->insert([
                
                'nombre' => $value,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s')
              
            ]);
        }

        

    }
}
