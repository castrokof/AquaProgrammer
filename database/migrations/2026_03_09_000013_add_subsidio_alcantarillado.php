<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSubsidioAlcantarilladoFacturas extends Migration
{
    public function up()
    {
        Schema::table('facturas', function (Blueprint $table) {
            // Subsidio aplicado al consumo básico de alcantarillado
            $table->decimal('subsidio_alcantarillado', 14, 2)->default(0)
                ->after('subtotal_alcantarillado')
                ->comment('Subsidio/contribución sobre consumo básico alcantarillado');
            // Total neto alcantarillado (subtotal - subsidio)
            $table->decimal('total_facturacion_alcantarillado', 14, 2)->default(0)
                ->after('subsidio_alcantarillado')
                ->comment('Total alcantarillado tras aplicar subsidio');
        });

        Schema::table('estratos', function (Blueprint $table) {
            // Valor fijo de subsidio por m³ facturado (alternativa al porcentaje)
            $table->decimal('subsidio_fijo_acueducto', 14, 2)->default(0)
                ->after('porcentaje_subsidio')
                ->comment('Valor fijo a descontar/cargar en acueducto. 0 = usar porcentaje');
            $table->decimal('subsidio_fijo_alcantarillado', 14, 2)->default(0)
                ->after('subsidio_fijo_acueducto')
                ->comment('Valor fijo a descontar/cargar en alcantarillado. 0 = usar porcentaje');
        });
    }

    public function down()
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->dropColumn(['subsidio_alcantarillado', 'total_facturacion_alcantarillado']);
        });
        Schema::table('estratos', function (Blueprint $table) {
            $table->dropColumn(['subsidio_fijo_acueducto', 'subsidio_fijo_alcantarillado']);
        });
    }
}
