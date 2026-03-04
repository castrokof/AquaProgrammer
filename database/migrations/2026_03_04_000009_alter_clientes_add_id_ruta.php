<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClientesAddIdRuta extends Migration
{
    public function up()
    {
        Schema::table('clientes', function (Blueprint $table) {
            // id_Ruta: identificador numérico del grupo/ruta (coincide con ordenescu.id_Ruta)
            $table->unsignedInteger('id_ruta')->nullable()
                ->after('consecutivo')
                ->comment('ID numérico de la ruta de lectura (coincide con ordenescu.id_Ruta)');

            $table->index('id_ruta');
        });
    }

    public function down()
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropIndex(['id_ruta']);
            $table->dropColumn('id_ruta');
        });
    }
}
