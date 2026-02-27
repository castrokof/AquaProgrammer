<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientesTable extends Migration
{
    public function up()
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('suscriptor', 50)->unique()->comment('Código suscriptor – coincide con Entrada.Suscriptor y Ordenesmtl.Suscriptor');
            $table->string('nuip', 30)->nullable()->comment('NUIP / cédula / documento de identidad');
            $table->string('tipo_documento', 10)->nullable()->comment('CC, TI, CE, PA, etc.');
            $table->string('nombre', 150)->nullable();
            $table->string('apellido', 150)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->string('serie_medidor', 100)->nullable()->comment('Serie / número de serie del medidor instalado en el predio');
            $table->timestamps();

            $table->index('nuip');
        });

        Schema::create('cliente_fotos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cliente_id');
            $table->string('ruta_foto', 400);
            // tipo: 'documento' = foto del carnet/cédula, 'medidor' = foto del medidor, 'predio' = foto del predio/fachada
            $table->string('tipo', 20)->default('medidor')->comment('documento | medidor | predio');
            // orden_ejecutada que originó esta foto (nullable – puede cargarse desde el panel web)
            $table->unsignedInteger('orden_ejecutada_id')->nullable();
            $table->timestamps();

            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
        });

        // Historial de series del medidor por período (trazabilidad)
        Schema::create('cliente_series', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cliente_id');
            $table->string('serie', 100)->comment('Número de serie del medidor');
            $table->string('periodo', 6)->comment('Período YYYYMM en que se registró la serie');
            $table->date('fecha_registro');
            $table->unsignedInteger('orden_ejecutada_id')->nullable()->comment('Orden de lectura que capturó esta serie');
            $table->timestamps();

            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->index(['cliente_id', 'periodo']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cliente_series');
        Schema::dropIfExists('cliente_fotos');
        Schema::dropIfExists('clientes');
    }
}
