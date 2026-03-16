<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOrdenescuAddPeriodoLectura extends Migration
{
    public function up()
    {
        // Deshabilitar modo estricto para tolerar fechas '0000-00-00' heredadas (MySQL only)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("SET SESSION sql_mode = ''");
        }

        Schema::table('ordenescu', function (Blueprint $table) {
            // FK al período de lectura formal (gestión de ciclos)
            $table->unsignedBigInteger('periodo_lectura_id')->nullable()
                ->after('id')
                ->comment('FK a periodos_lectura. NULL para órdenes antiguas migradas.');

            $table->foreign('periodo_lectura_id', 'fk_ordenescu_periodo_lectura')
                ->references('id')->on('periodos_lectura')->onDelete('restrict');

            $table->index('periodo_lectura_id');
        });
    }

    public function down()
    {
        Schema::table('ordenescu', function (Blueprint $table) {
            $table->dropForeign('fk_ordenescu_periodo_lectura');
            $table->dropIndex(['periodo_lectura_id']);
            $table->dropColumn('periodo_lectura_id');
        });
    }
}
