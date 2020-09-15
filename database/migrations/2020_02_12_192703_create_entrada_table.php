<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEntradaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entrada', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('Ciclo');
            $table->string('Suscriptor',50);
            $table->string('Nombre',128)->nullable();
            $table->string('Apell',128)->nullable();
            $table->string('Direccion',200);
            $table->string('Ref_Medidor',50)->nullable();
            $table->integer('LA')->nullable();
            $table->integer('Promedio');
            $table->string('recorrido',128)->nullable();
            $table->string('uso',128)->nullable();  
            $table->integer('estrato')->nullable();
            $table->integer('Año');
            $table->integer('Mes');
            $table->integer('id_Ruta');
            $table->integer('Periodo');
            $table->integer('consecutivoRuta');
            $table->integer('consecutivo_int');
            $table->string('Ruta',128)->nullable();
            $table->integer('Tope')->nullable();
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
        Schema::dropIfExists('entrada');
    }
}
