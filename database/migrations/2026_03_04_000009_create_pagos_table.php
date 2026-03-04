<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePagosTable extends Migration
{
    public function up()
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('factura_id');

            // Fecha y medio de pago
            $table->date('fecha_pago');
            $table->string('numero_recibo', 60)->nullable()
                ->comment('Número de recibo de caja / comprobante');
            $table->enum('medio_pago', [
                'EFECTIVO', 'TRANSFERENCIA', 'CONSIGNACION', 'DATAFONO', 'OTRO'
            ])->default('EFECTIVO');

            // Desglose del pago (igual que columnas del Excel)
            $table->decimal('pagos_acueducto', 14, 2)->default(0);
            $table->decimal('pagos_alcantarillado', 14, 2)->default(0);
            $table->decimal('pago_otros_cobros_acueducto', 14, 2)->default(0);
            $table->decimal('pago_otros_cobros_alcantarillado', 14, 2)->default(0);
            $table->decimal('pago_conexion_acueducto', 14, 2)->default(0);
            $table->decimal('pago_conexion_alcantarillado', 14, 2)->default(0);
            $table->decimal('total_pago_realizado', 14, 2)->default(0)
                ->comment('Suma de todos los conceptos pagados en este recibo');

            $table->unsignedBigInteger('usuario_id')->nullable()
                ->comment('Usuario del sistema que registró el pago');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('factura_id')->references('id')->on('facturas')->onDelete('restrict');
            $table->index(['factura_id']);
            $table->index('fecha_pago');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pagos');
    }
}
