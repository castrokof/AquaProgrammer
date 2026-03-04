<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFacturasTable extends Migration
{
    public function up()
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->bigIncrements('id');

            // ── ENCABEZADO ──────────────────────────────────────────────────────
            $table->string('numero_factura', 30)->unique()->comment('ej: 48630');
            $table->string('suscriptor', 50)->comment('Código suscriptor – igual que ordenescu.Suscriptor');
            $table->unsignedInteger('cliente_id');
            $table->unsignedBigInteger('periodo_lectura_id')
                ->comment('FK al período de lectura que generó esta factura');
            $table->unsignedBigInteger('tarifa_periodo_id')
                ->comment('Resolución tarifaria usada para el cálculo');
            $table->char('periodo', 6)->comment('YYYYMM — copia rápida sin JOIN');
            $table->string('mes_cuenta', 30)->comment('ej: ABRIL 2024');
            $table->date('fecha_del')->comment('Inicio del período facturado');
            $table->date('fecha_hasta')->comment('Fin del período facturado');
            $table->date('fecha_expedicion');
            $table->date('fecha_vencimiento');
            $table->date('fecha_corte')->comment('Fecha de corte del servicio por mora');

            // Datos del predio al momento de facturar (snapshot)
            $table->string('serie_medidor', 100)->nullable();
            $table->string('sector', 100)->nullable();
            $table->unsignedTinyInteger('estrato_snapshot')->nullable()->comment('Número de estrato al momento de facturar');
            $table->string('clase_uso', 30)->nullable()->comment('RESIDENCIAL / COMERCIAL / …');
            $table->boolean('tiene_medidor_snapshot')->default(true);
            $table->string('servicios_snapshot', 10)->default('AG-AL')->comment('AG | AL | AG-AL');

            // ── LECTURA Y PROMEDIO ──────────────────────────────────────────────
            $table->unsignedInteger('lectura_anterior')->nullable();
            $table->unsignedInteger('lectura_actual')->nullable();
            $table->unsignedInteger('consumo_m3')->default(0);
            $table->unsignedInteger('dias_facturados')->default(30);
            // Snapshot de los 6 meses del promedio (como aparecen en la factura)
            $table->unsignedInteger('prom_m1')->nullable();
            $table->unsignedInteger('prom_m2')->nullable();
            $table->unsignedInteger('prom_m3')->nullable();
            $table->unsignedInteger('prom_m4')->nullable();
            $table->unsignedInteger('prom_m5')->nullable();
            $table->unsignedInteger('prom_m6')->nullable();
            $table->decimal('promedio_consumo_snapshot', 10, 2)->default(0)
                ->comment('Promedio calculado al momento de facturar');

            // ── ACUEDUCTO ───────────────────────────────────────────────────────
            $table->decimal('cargo_fijo_acueducto', 14, 2)->default(0);
            $table->unsignedInteger('consumo_basico_acueducto_m3')->default(0);
            $table->decimal('consumo_basico_acueducto_valor', 14, 2)->default(0);
            $table->unsignedInteger('consumo_complementario_acueducto_m3')->default(0);
            $table->decimal('consumo_complementario_acueducto_valor', 14, 2)->default(0);
            $table->unsignedInteger('consumo_suntuario_acueducto_m3')->default(0);
            $table->decimal('consumo_suntuario_acueducto_valor', 14, 2)->default(0);
            $table->decimal('subtotal_facturacion_acueducto', 14, 2)->default(0);
            $table->decimal('subsidio_emergencia', 14, 2)->default(0)
                ->comment('Subsidio de emergencia (puede ser 0 o positivo)');
            $table->decimal('total_facturacion_acueducto', 14, 2)->default(0);

            // Otros cobros acueducto (financiación de trabajos)
            $table->decimal('otros_cobros_acueducto', 14, 2)->default(0)
                ->comment('Monto total del cargo adicional acueducto en este período');
            $table->decimal('cuota_otros_cobros_acueducto', 14, 2)->default(0)
                ->comment('Cuota mensual del plan de financiación acueducto');
            $table->decimal('saldo_otros_cobros_acueducto', 14, 2)->default(0)
                ->comment('Saldo pendiente de otros cobros acueducto tras esta cuota');
            $table->decimal('subtotal_conexion_otros_acueducto', 14, 2)->default(0);

            // ── ALCANTARILLADO ──────────────────────────────────────────────────
            $table->decimal('cargo_fijo_alcantarillado', 14, 2)->default(0);
            $table->unsignedInteger('consumo_basico_alcantarillado_m3')->default(0);
            $table->decimal('consumo_basico_alcantarillado_valor', 14, 2)->default(0);
            $table->unsignedInteger('consumo_complementario_alcantarillado_m3')->default(0);
            $table->decimal('consumo_complementario_alcantarillado_valor', 14, 2)->default(0);
            $table->unsignedInteger('consumo_suntuario_alcantarillado_m3')->default(0);
            $table->decimal('consumo_suntuario_alcantarillado_valor', 14, 2)->default(0);
            $table->decimal('subtotal_alcantarillado', 14, 2)->default(0);

            // Otros cobros alcantarillado
            $table->decimal('otros_cobros_alcantarillado', 14, 2)->default(0);
            $table->decimal('cuota_otros_cobros_alcantarillado', 14, 2)->default(0);
            $table->decimal('saldo_otros_cobros_alcantarillado', 14, 2)->default(0);
            $table->decimal('subtotal_conexion_otros_alcantarillado', 14, 2)->default(0);

            // ── CONEXIÓN (financiación de cargo por nueva conexión) ─────────────
            $table->decimal('conexion_acueducto', 14, 2)->default(0);
            $table->decimal('cuota_conexion_acueducto', 14, 2)->default(0);
            $table->decimal('pagos_conexion_acueducto', 14, 2)->default(0);
            $table->decimal('saldo_conexion_acueducto', 14, 2)->default(0);

            $table->decimal('conexion_alcantarillado', 14, 2)->default(0);
            $table->decimal('cuota_conexion_alcantarillado', 14, 2)->default(0);
            $table->decimal('pagos_conexion_alcantarillado', 14, 2)->default(0);
            $table->decimal('saldo_conexion_alcantarillado', 14, 2)->default(0);

            // ── SALDOS Y TOTALES ────────────────────────────────────────────────
            $table->decimal('saldo_anterior', 14, 2)->default(0)
                ->comment('Saldo pendiente de facturas anteriores al momento de emitir');
            $table->unsignedInteger('facturas_en_mora')->default(0);
            $table->decimal('total_a_pagar', 14, 2)->default(0)
                ->comment('Gran total = todos los conceptos + saldo anterior');

            // ── CONTROL ─────────────────────────────────────────────────────────
            $table->enum('estado', ['PENDIENTE', 'PAGADA', 'VENCIDA', 'ANULADA'])->default('PENDIENTE');
            $table->boolean('es_automatica')->default(false)
                ->comment('True = generada automáticamente por lectura normal. False = generada manualmente.');
            $table->unsignedBigInteger('orden_revision_id')->nullable()
                ->comment('FK a ordenes_revision si la factura proviene de una revisión de crítica');
            $table->unsignedBigInteger('usuario_id')->nullable()
                ->comment('Usuario que generó la factura (null si fue automática)');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('restrict');
            $table->foreign('periodo_lectura_id')->references('id')->on('periodos_lectura')->onDelete('restrict');
            $table->foreign('tarifa_periodo_id')->references('id')->on('tarifa_periodos')->onDelete('restrict');

            // Índices
            $table->index(['suscriptor', 'periodo']);
            $table->index(['cliente_id', 'periodo']);
            $table->index('estado');
            $table->index('periodo_lectura_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('facturas');
    }
}
