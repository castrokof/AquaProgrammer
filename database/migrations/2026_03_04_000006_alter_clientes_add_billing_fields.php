<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClientesAddBillingFields extends Migration
{
    public function up()
    {
        Schema::table('clientes', function (Blueprint $table) {
            // Estrato (1-9, FK a tabla estratos)
            $table->unsignedTinyInteger('estrato_id')->nullable()
                ->after('serie_medidor')
                ->comment('FK a estratos.id — estrato socioeconómico 1-6 o tipo Comercial/Industrial/Oficial');

            // Servicios activos: AG=solo acueducto, AL=solo alcantarillado, AG-AL=ambos
            $table->string('servicios', 10)->default('AG-AL')
                ->after('estrato_id')
                ->comment('AG | AL | AG-AL — servicios a los que está suscrito el cliente');

            // Tipo de uso para tarificación
            $table->enum('tipo_uso', ['RESIDENCIAL', 'COMERCIAL', 'INDUSTRIAL', 'OFICIAL'])
                ->default('RESIDENCIAL')
                ->after('servicios');

            // Medidor
            $table->boolean('tiene_medidor')->default(true)
                ->after('tipo_uso')
                ->comment('Si no tiene medidor se cobra consumo básico por promedio/mínimo');

            // Sector / zona
            $table->string('sector', 100)->nullable()
                ->after('tiene_medidor');

            // Promedio de consumo (últimos 6 meses). Se actualiza en cada facturación.
            $table->decimal('promedio_consumo', 10, 2)->default(0)
                ->after('sector')
                ->comment('Promedio m³/mes de los últimos 6 períodos. Se recalcula al facturar.');

            // Estado del servicio
            $table->enum('estado', ['ACTIVO', 'SUSPENDIDO', 'CORTADO', 'INACTIVO'])
                ->default('ACTIVO')
                ->after('promedio_consumo');

            // Fecha de corte del servicio (cuando aplica)
            $table->date('fecha_corte')->nullable()
                ->after('estado');

            $table->foreign('estrato_id', 'fk_clientes_estrato')
                ->references('id')->on('estratos')->onDelete('restrict');

            $table->index('estrato_id');
            $table->index('estado');
        });
    }

    public function down()
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropForeign('fk_clientes_estrato');
            $table->dropIndex(['estrato_id']);
            $table->dropIndex(['estado']);
            $table->dropColumn([
                'estrato_id', 'servicios', 'tipo_uso', 'tiene_medidor',
                'sector', 'promedio_consumo', 'estado', 'fecha_corte',
            ]);
        });
    }
}
