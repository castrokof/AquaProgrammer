<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPorcentajeSubsidioToEstratos extends Migration
{
    public function up()
    {
        // Agregar la columna si no existe
        if (!Schema::hasColumn('estratos', 'porcentaje_subsidio')) {
            Schema::table('estratos', function (Blueprint $table) {
                $table->decimal('porcentaje_subsidio', 5, 2)->default(0)
                    ->after('codigo')
                    ->comment('% subsidio sobre consumo básico acueducto. Positivo=subsidio (E1-E3), negativo=contribución (E5-E6/COM/IND)');
            });
        }

        // Actualizar los valores por número de estrato
        // (tanto si la columna era nueva como si tenía todos en 0)
        $valores = [
            1 =>  70.00,  // E1 Bajo-bajo
            2 =>  40.00,  // E2 Bajo
            3 =>  15.00,  // E3 Medio-bajo
            4 =>   0.00,  // E4 Medio
            5 => -50.00,  // E5 Medio-alto
            6 => -60.00,  // E6 Alto
            7 => -60.00,  // Comercial
            8 => -60.00,  // Industrial
            9 =>   0.00,  // Oficial
        ];

        foreach ($valores as $numero => $pct) {
            DB::table('estratos')
                ->where('numero', $numero)
                ->update(['porcentaje_subsidio' => $pct]);
        }
    }

    public function down()
    {
        if (Schema::hasColumn('estratos', 'porcentaje_subsidio')) {
            Schema::table('estratos', function (Blueprint $table) {
                $table->dropColumn('porcentaje_subsidio');
            });
        }
    }
}
