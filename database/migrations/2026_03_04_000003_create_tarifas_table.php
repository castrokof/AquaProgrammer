<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTarifasTable extends Migration
{
    public function up()
    {
        // ── 1. PERÍODO TARIFARIO ──────────────────────────────────────────────
        // Cada resolución de la CRA / ente regulador que cambia los precios
        Schema::create('tarifa_periodos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre', 120)->comment('ej: Resolución CRA 2024-01');
            $table->string('numero_resolucion', 80)->nullable();
            $table->date('vigente_desde');
            $table->date('vigente_hasta')->nullable()->comment('NULL = vigente actualmente');
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        // ── 2. CARGOS FIJOS ───────────────────────────────────────────────────
        // Cargo fijo mensual por servicio × estrato × tipo_uso
        Schema::create('tarifa_cargos_fijos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tarifa_periodo_id');
            $table->enum('servicio', ['ACUEDUCTO', 'ALCANTARILLADO']);
            $table->unsignedTinyInteger('estrato_id')->comment('FK a estratos.id');
            $table->decimal('cargo_fijo', 14, 2)->comment('Valor en pesos del cargo fijo mensual');
            $table->timestamps();

            $table->foreign('tarifa_periodo_id')->references('id')->on('tarifa_periodos')->onDelete('cascade');
            $table->foreign('estrato_id')->references('id')->on('estratos')->onDelete('restrict');
            $table->unique(['tarifa_periodo_id', 'servicio', 'estrato_id'], 'uq_cargo_fijo');
        });

        // ── 3. RANGOS DE CONSUMO ──────────────────────────────────────────────
        // Precio por m³ según rango: BASICO / COMPLEMENTARIO / SUNTUARIO
        Schema::create('tarifa_rangos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tarifa_periodo_id');
            $table->enum('servicio', ['ACUEDUCTO', 'ALCANTARILLADO']);
            $table->unsignedTinyInteger('estrato_id');
            $table->enum('tipo', ['BASICO', 'COMPLEMENTARIO', 'SUNTUARIO']);
            $table->unsignedInteger('rango_desde')->comment('m³ desde (inclusive)');
            $table->unsignedInteger('rango_hasta')->nullable()->comment('m³ hasta (inclusive). NULL = ilimitado (suntuario)');
            $table->decimal('precio_m3', 14, 4)->comment('Precio por m³ en pesos');
            $table->timestamps();

            $table->foreign('tarifa_periodo_id')->references('id')->on('tarifa_periodos')->onDelete('cascade');
            $table->foreign('estrato_id')->references('id')->on('estratos')->onDelete('restrict');
            $table->unique(['tarifa_periodo_id', 'servicio', 'estrato_id', 'tipo'], 'uq_rango');
            $table->index(['tarifa_periodo_id', 'servicio', 'estrato_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tarifa_rangos');
        Schema::dropIfExists('tarifa_cargos_fijos');
        Schema::dropIfExists('tarifa_periodos');
    }
}
