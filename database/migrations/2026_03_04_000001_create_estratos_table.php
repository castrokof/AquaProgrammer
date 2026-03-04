<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEstratosTable extends Migration
{
    public function up()
    {
        Schema::create('estratos', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->tinyInteger('numero')->unsigned()->unique()->comment('1-6 residencial; 7=Comercial, 8=Industrial, 9=Oficial');
            $table->string('nombre', 80)->comment('ej: Bajo-bajo, Bajo, Medio-bajo …');
            $table->string('codigo', 20)->unique()->comment('ej: E1, E2, … COMERCIAL, INDUSTRIAL');
            $table->decimal('porcentaje_subsidio', 5, 2)->default(0)
                ->comment('% de subsidio sobre la tarifa base (estratos 1-3). Negativo = contribución (4-6, comercial)');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Datos semilla
        DB::table('estratos')->insert([
            ['numero' => 1, 'nombre' => 'Bajo-bajo',    'codigo' => 'E1', 'porcentaje_subsidio' =>  70.00, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['numero' => 2, 'nombre' => 'Bajo',         'codigo' => 'E2', 'porcentaje_subsidio' =>  40.00, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['numero' => 3, 'nombre' => 'Medio-bajo',   'codigo' => 'E3', 'porcentaje_subsidio' =>  15.00, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['numero' => 4, 'nombre' => 'Medio',        'codigo' => 'E4', 'porcentaje_subsidio' =>   0.00, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['numero' => 5, 'nombre' => 'Medio-alto',   'codigo' => 'E5', 'porcentaje_subsidio' => -50.00, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['numero' => 6, 'nombre' => 'Alto',         'codigo' => 'E6', 'porcentaje_subsidio' => -60.00, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['numero' => 7, 'nombre' => 'Comercial',    'codigo' => 'COM', 'porcentaje_subsidio' => -60.00, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['numero' => 8, 'nombre' => 'Industrial',   'codigo' => 'IND', 'porcentaje_subsidio' => -60.00, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['numero' => 9, 'nombre' => 'Oficial',      'codigo' => 'OFI', 'porcentaje_subsidio' =>   0.00, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('estratos');
    }
}
