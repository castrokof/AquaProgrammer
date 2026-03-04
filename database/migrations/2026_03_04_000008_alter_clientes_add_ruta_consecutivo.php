<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClientesAddRutaConsecutivo extends Migration
{
    public function up()
    {
        Schema::table('clientes', function (Blueprint $table) {
            // Ruta de lectura (equivale a Ruta/id_Ruta en ordenescu)
            $table->string('ruta', 100)->nullable()
                ->after('sector')
                ->comment('Ruta de lectura del medidor (coincide con ordenescu.Ruta)');

            // Consecutivo del cliente dentro de la ruta de lectura
            $table->unsignedInteger('consecutivo')->nullable()
                ->after('ruta')
                ->comment('Posición del cliente en la ruta de lectura (consecutivoRuta en ordenescu)');

            $table->index(['ruta', 'consecutivo']);
        });
    }

    public function down()
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropIndex(['ruta', 'consecutivo']);
            $table->dropColumn(['ruta', 'consecutivo']);
        });
    }
}
