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
            $table->timestamps();

            $table->index('nuip');
        });

        Schema::create('cliente_fotos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cliente_id');
            $table->string('ruta_foto', 400);
            // tipo: 'documento' = foto del carnet/cédula, 'rostro' = foto del cliente, 'medidor' = foto del medidor
            $table->string('tipo', 20)->default('documento')->comment('documento | rostro | medidor');
            // orden_ejecutada que originó esta foto (nullable – puede cargarse desde el panel web)
            $table->unsignedInteger('orden_ejecutada_id')->nullable();
            $table->timestamps();

            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cliente_fotos');
        Schema::dropIfExists('clientes');
    }
}
