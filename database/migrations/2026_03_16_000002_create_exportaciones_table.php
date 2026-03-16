<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExportacionesTable extends Migration
{
    public function up()
    {
        Schema::create('exportaciones', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->json('ids');                           // IDs de facturas a exportar
            $table->enum('estado', ['PENDIENTE', 'PROCESANDO', 'LISTO', 'ERROR'])
                  ->default('PENDIENTE');
            $table->unsignedInteger('total')->default(0);
            $table->unsignedInteger('procesados')->default(0);
            $table->unsignedTinyInteger('progreso')->default(0); // 0-100
            $table->string('archivo')->nullable();         // ruta del ZIP generado
            $table->text('mensaje_error')->nullable();
            $table->timestamps();

            $table->foreign('usuario_id')->references('id')->on('usuario')->onDelete('set null');
            $table->index('estado');
        });
    }

    public function down()
    {
        Schema::dropIfExists('exportaciones');
    }
}
