<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdenesmtlTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ordenescu', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ordenescu_id');
            $table->foreign('ordenescu_id', 'fk_ordenescu_entrada')->references('id')->on('entrada')->onDelete('restrict')->onUpdate('restrict');
            $table->string('Suscriptor',50);
            $table->integer('Periodo');
            $table->integer('Año');
            $table->integer('Mes');
            $table->integer('Ciclo');
            $table->string('Nombre',128)->nullable();
            $table->string('Apell',128)->nullable();
            $table->string('Direccion', 200);
            $table->string('Telefono', 200);
            $table->string('Ref_Medidor',50)->nullable();
            $table->integer('idDivision');
            $table->integer('id_Ruta');
            $table->string('Ruta',128)->nullable();
            $table->integer('Consecutivo');
            $table->integer('Lect_Actual')->nullable();
            $table->integer('LA');
            $table->integer('Cons_Act')->nullable();
            $table->integer('Promedio');
            $table->string('Critica',128);
            $table->string('Usuario',50)->nullable();
            $table->string('nombreu',100)->nullable();
            $table->dateTime('fecha_de_ejecucion')->nullable();
            $table->string('recorrido',128)->nullable();
            $table->string('foto1',255)->nullable();
            $table->string('foto2',255)->nullable();
            $table->string('Coordenada',255)->nullable();
            $table->string('Latitud',255)->nullable();
            $table->string('Longitud',255)->nullable();
            $table->integer('Causa_id')->nullable();
            $table->integer('Observacion_id')->nullable();
            $table->string('Causa_des',128)->nullable();
            $table->string('Observacion_des',128)->nullable();
            $table->string('Estado_des',50);
            $table->integer('Estado');
            $table->integer('Tope')->nullable();
            $table->integer('consecutivoRuta');
            $table->string('new_medidor',50)->nullable();
            $table->string('id_lectura');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ordenescu');
    }
}
