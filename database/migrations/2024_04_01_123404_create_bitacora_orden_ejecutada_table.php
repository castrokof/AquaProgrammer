<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBitacoraOrdenEjecutadaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bitacora_orden_ejecutada', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->BigInteger('bitacoraordenejecutada_id');
            $table->foreign('bitacoraordenejecutada_id', 'fk_bitacoraordenejecutada_orden_ejecutada')->references('id')->on('orden_ejecutada')->onDelete('restrict')->onUpdate('restrict');
            $table->string('suscriptor',50);
            $table->string('usuario',50);
            $table->string('tipo_usuario',50)->nullable();
            $table->dateTime('fecha_de_ejecucion');
            $table->string('new_medidor',50)->nullable();
            $table->integer('Lect_Actual')->nullable();
            $table->integer('Cons_Act')->nullable();
            $table->string('Comentario',128)->nullable();
            $table->string('Critica',128)->nullable();
            $table->double('Desviacion',11,2)->nullable();
            $table->string('coordenada',255)->nullable();
            $table->string('latitud',255)->nullable();
            $table->string('longitud',255)->nullable();
            $table->string('estado',50);
            $table->integer('estado_id');   
            $table->string('foto1',255);
            $table->string('foto2',255)->nullable();
            $table->string('futuro1',255)->nullable();
            $table->integer('futuro2')->nullable();
            $table->string('futuro3',255)->nullable();
            $table->integer('futuro4')->nullable();
            $table->string('futuro5',255)->nullable();
            $table->string('futuro6',255)->nullable();
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
        Schema::dropIfExists('bitacora_orden_ejecutada');
    }
}
