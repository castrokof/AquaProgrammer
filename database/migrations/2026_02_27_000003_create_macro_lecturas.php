<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Reemplaza el modelo de lectura única en macromedidores por un historial
 * de lecturas diarias. Cada lectura queda como un registro independiente.
 */
class CreateMacroLecturas extends Migration
{
    public function up()
    {
        Schema::create('macro_lecturas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('macromedidor_id');
            $table->unsignedBigInteger('usuario_id')->nullable();

            // Snapshot de la lectura anterior al momento de tomar la nueva
            $table->integer('lectura_anterior')->default(0);
            $table->integer('lectura_actual');
            // Consumo calculado (lectura_actual - lectura_anterior)
            $table->integer('consumo')->default(0);

            $table->string('observacion', 500)->nullable();
            $table->double('gps_latitud')->nullable();
            $table->double('gps_longitud')->nullable();
            $table->dateTime('fecha_lectura');
            $table->boolean('sincronizado')->default(true);
            $table->timestamps();

            $table->foreign('macromedidor_id')->references('id')->on('macromedidores')->onDelete('cascade');
            $table->index(['macromedidor_id', 'fecha_lectura']);
        });

        Schema::create('macro_lectura_fotos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('macro_lectura_id');
            $table->string('ruta_foto', 400);
            $table->timestamps();

            $table->foreign('macro_lectura_id')->references('id')->on('macro_lecturas')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('macro_lectura_fotos');
        Schema::dropIfExists('macro_lecturas');
    }
}
