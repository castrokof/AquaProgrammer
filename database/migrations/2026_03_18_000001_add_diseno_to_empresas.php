<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDisenoToEmpresas extends Migration
{
    public function up()
    {
        Schema::table('empresas', function (Blueprint $table) {
            // ── Colores ─────────────────────────────────────────────────────────
            $table->string('factura_color_primario', 10)->default('#2e50e4')
                ->comment('Color principal del encabezado y totales en PDF');
            $table->string('factura_color_acento', 10)->default('#2e50e4')
                ->comment('Color de acento para valores destacados');

            // ── Subtítulo empresa ────────────────────────────────────────────────
            $table->string('factura_subtitulo', 150)->default('Servicio Público Domiciliario')
                ->comment('Subtítulo bajo el nombre de la empresa en el PDF');

            // ── Visibilidad de secciones ─────────────────────────────────────────
            $table->boolean('factura_mostrar_logo')->default(true)
                ->comment('Mostrar logo en el PDF');
            $table->boolean('factura_mostrar_lectura')->default(true)
                ->comment('Mostrar sección de lectura del medidor');
            $table->boolean('factura_mostrar_serie_medidor')->default(true)
                ->comment('Mostrar serie del medidor en datos del suscriptor');
            $table->boolean('factura_mostrar_sector')->default(true)
                ->comment('Mostrar sector del suscriptor');
            $table->boolean('factura_mostrar_tipo_uso')->default(true)
                ->comment('Mostrar clase de servicio / tipo de uso');
            $table->boolean('factura_mostrar_estrato')->default(true)
                ->comment('Mostrar estrato del suscriptor');
            $table->boolean('factura_mostrar_tarifa')->default(true)
                ->comment('Mostrar nombre de la tarifa vigente');
            $table->boolean('factura_mostrar_saldo_anterior')->default(true)
                ->comment('Mostrar aviso de saldo anterior en mora');
            $table->boolean('factura_mostrar_creditos')->default(true)
                ->comment('Mostrar tabla de créditos y financiación');
            $table->boolean('factura_mostrar_barras_consumo')->default(true)
                ->comment('Mostrar gráfica de barras de últimos consumos');
            $table->boolean('factura_mostrar_observaciones')->default(true)
                ->comment('Mostrar campo de observaciones en el PDF');
            $table->boolean('factura_mostrar_codigo_barras')->default(true)
                ->comment('Mostrar código numérico / código de barras al pie');
        });
    }

    public function down()
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn([
                'factura_color_primario',
                'factura_color_acento',
                'factura_subtitulo',
                'factura_mostrar_logo',
                'factura_mostrar_lectura',
                'factura_mostrar_serie_medidor',
                'factura_mostrar_sector',
                'factura_mostrar_tipo_uso',
                'factura_mostrar_estrato',
                'factura_mostrar_tarifa',
                'factura_mostrar_saldo_anterior',
                'factura_mostrar_creditos',
                'factura_mostrar_barras_consumo',
                'factura_mostrar_observaciones',
                'factura_mostrar_codigo_barras',
            ]);
        });
    }
}
