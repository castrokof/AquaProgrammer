<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddConsumoMinimoSubsidioToEstratos extends Migration
{
    public function up()
    {
        Schema::table('estratos', function (Blueprint $table) {
            // Consumo mínimo en m³ requerido para que aplique el subsidio o la contribución.
            // Default 4 m³. Si el consumo del período es <= este valor, no se aplica subsidio.
            $table->decimal('consumo_minimo_subsidio', 8, 2)
                  ->default(4)
                  ->after('subsidio_fijo_alcantarillado')
                  ->comment('m³ mínimos de consumo para aplicar subsidio/sobretasa. 0 = siempre aplica.');
        });

        // Aplicar el valor por defecto (4 m³) a todos los estratos existentes
        DB::table('estratos')->update(['consumo_minimo_subsidio' => 4]);
    }

    public function down()
    {
        Schema::table('estratos', function (Blueprint $table) {
            $table->dropColumn('consumo_minimo_subsidio');
        });
    }
}
