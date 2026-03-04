<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOtrosCobrosTable extends Migration
{
    public function up()
    {
        // ── 1. CATÁLOGO DE TIPOS DE OTROS COBROS ─────────────────────────────
        // Define los tipos de cargos adicionales que se pueden aplicar a un cliente
        // ej: Cambio de Medidor, Instalación Acometida, Reconexión, Multa, etc.
        Schema::create('otros_cobros_catalogo', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre', 120)->comment('ej: Cambio de Medidor, Instalación de Acometida');
            $table->string('codigo', 30)->unique()->comment('ej: CAMBIO_MEDIDOR, INSTALACION_ACOMETIDA');
            $table->text('descripcion')->nullable();
            $table->boolean('aplica_acueducto')->default(true);
            $table->boolean('aplica_alcantarillado')->default(false);
            $table->boolean('requiere_diametro')->default(false)
                ->comment('Si el cobro depende del diámetro de la tubería/acometida');
            $table->boolean('permite_cuotas')->default(true)
                ->comment('Si se puede financiar en cuotas o se cobra de una vez');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Datos semilla básicos
        DB::table('otros_cobros_catalogo')->insert([
            [
                'nombre' => 'Cambio de Medidor',
                'codigo' => 'CAMBIO_MEDIDOR',
                'aplica_acueducto' => true, 'aplica_alcantarillado' => false,
                'requiere_diametro' => true, 'permite_cuotas' => true,
                'activo' => true, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'nombre' => 'Instalación de Acometida',
                'codigo' => 'INSTALACION_ACOMETIDA',
                'aplica_acueducto' => true, 'aplica_alcantarillado' => true,
                'requiere_diametro' => true, 'permite_cuotas' => true,
                'activo' => true, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'nombre' => 'Reconexión',
                'codigo' => 'RECONEXION',
                'aplica_acueducto' => true, 'aplica_alcantarillado' => true,
                'requiere_diametro' => false, 'permite_cuotas' => false,
                'activo' => true, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'nombre' => 'Conexión Acueducto',
                'codigo' => 'CONEXION_ACUEDUCTO',
                'aplica_acueducto' => true, 'aplica_alcantarillado' => false,
                'requiere_diametro' => true, 'permite_cuotas' => true,
                'activo' => true, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'nombre' => 'Conexión Alcantarillado',
                'codigo' => 'CONEXION_ALCANTARILLADO',
                'aplica_acueducto' => false, 'aplica_alcantarillado' => true,
                'requiere_diametro' => true, 'permite_cuotas' => true,
                'activo' => true, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'nombre' => 'Multa / Sanción',
                'codigo' => 'MULTA',
                'aplica_acueducto' => true, 'aplica_alcantarillado' => true,
                'requiere_diametro' => false, 'permite_cuotas' => false,
                'activo' => true, 'created_at' => now(), 'updated_at' => now(),
            ],
        ]);

        // ── 2. COBROS ADICIONALES POR CLIENTE ────────────────────────────────
        // Cada cargo adicional asignado a un cliente específico con plan de cuotas
        Schema::create('cliente_otros_cobros', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('cliente_id');
            $table->unsignedBigInteger('catalogo_id');
            $table->enum('tipo_servicio', ['ACUEDUCTO', 'ALCANTARILLADO'])
                ->comment('A qué servicio se carga en la factura');

            // Detalle del trabajo / cargo
            $table->string('concepto', 255)->comment('Descripción específica del trabajo u obra');
            $table->string('diametro', 50)->nullable()->comment('ej: 1/2", 3/4", 1"');
            $table->text('observaciones')->nullable();

            // Valores y cuotas
            $table->decimal('monto_total', 14, 2)->comment('Valor total del cobro en pesos');
            $table->unsignedInteger('num_cuotas')->default(1)
                ->comment('Número de cuotas mensuales en que se divide');
            $table->decimal('cuota_mensual', 14, 2)->comment('Valor de cada cuota mensual');
            $table->unsignedInteger('cuotas_pagadas')->default(0);
            $table->decimal('saldo', 14, 2)->comment('Saldo pendiente por pagar');

            $table->date('fecha_inicio')->comment('Primer período en que se cobra la primera cuota');
            $table->enum('estado', ['ACTIVO', 'PAGADO', 'ANULADO'])->default('ACTIVO');

            $table->unsignedBigInteger('usuario_id')->nullable()->comment('Usuario que registró el cobro');
            $table->timestamps();

            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('restrict');
            $table->foreign('catalogo_id')->references('id')->on('otros_cobros_catalogo')->onDelete('restrict');
            $table->index(['cliente_id', 'estado']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cliente_otros_cobros');
        Schema::dropIfExists('otros_cobros_catalogo');
    }
}
