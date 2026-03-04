<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePeriodosLecturaTable extends Migration
{
    public function up()
    {
        Schema::create('periodos_lectura', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('codigo', 6)->unique()->comment('YYYYMM ej: 202404');
            $table->string('nombre', 80)->comment('ej: ABRIL 2024');
            $table->integer('ciclo')->comment('Número de ciclo de lectura');

            // Estado del período
            // PLANIFICADO → ACTIVO → LECTURA_CERRADA → FACTURADO → CERRADO
            $table->enum('estado', [
                'PLANIFICADO',
                'ACTIVO',
                'LECTURA_CERRADA',
                'FACTURADO',
                'CERRADO',
            ])->default('PLANIFICADO');

            // Tarifa vigente para este período (qué precios aplican)
            $table->unsignedBigInteger('tarifa_periodo_id')->nullable()
                ->comment('Resolución tarifaria vigente para este período de facturación');

            // Ventana de lectura en campo
            $table->date('fecha_inicio_lectura')->comment('Fecha desde que los lecturistas pueden registrar');
            $table->date('fecha_fin_lectura')->comment('Fecha límite de lectura en campo');

            // Fechas de la factura
            $table->date('fecha_expedicion')->comment('Fecha de expedición de las facturas del período');
            $table->date('fecha_vencimiento')->comment('Fecha límite de pago sin recargo');
            $table->date('fecha_corte')->comment('Fecha en que se corta el servicio por mora');

            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('estado');
            $table->index('codigo');
        });
    }

    public function down()
    {
        Schema::dropIfExists('periodos_lectura');
    }
}
