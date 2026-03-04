<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClienteHistoricoConsumosTable extends Migration
{
    public function up()
    {
        // Historial de consumo mensual por cliente.
        // Se inserta una fila por cada factura generada.
        // Las últimas 6 filas ordenadas por periodo desc = promedio de 6 meses.
        Schema::create('cliente_historico_consumos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('cliente_id');
            $table->string('suscriptor', 50)->comment('Desnormalizado para búsquedas rápidas');
            $table->char('periodo', 6)->comment('YYYYMM');
            $table->unsignedInteger('consumo_m3')->comment('Consumo real facturado en m³');
            $table->unsignedInteger('lectura_anterior')->nullable();
            $table->unsignedInteger('lectura_actual')->nullable();
            $table->unsignedInteger('dias_facturados')->default(30);
            $table->timestamps();

            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
            // Un cliente, un registro por período
            $table->unique(['cliente_id', 'periodo'], 'uq_consumo_periodo');
            $table->index(['cliente_id', 'periodo']);
            $table->index('suscriptor');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cliente_historico_consumos');
    }
}
