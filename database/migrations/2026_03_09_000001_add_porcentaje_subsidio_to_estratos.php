<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPorcentajeSubsidioToEstratos extends Migration
{
    public function up()
    {
        // 1. Agregar la columna a estratos si no existe
        if (!Schema::hasColumn('estratos', 'porcentaje_subsidio')) {
            Schema::table('estratos', function (Blueprint $table) {
                $table->decimal('porcentaje_subsidio', 5, 2)->default(0)
                    ->after('codigo')
                    ->comment('% subsidio sobre consumo básico acueducto. Positivo=subsidio (E1-E3), negativo=contribución (E5-E6/COM/IND)');
            });
        }

        // 2. Asignar los porcentajes correctos por número de estrato
        $valores = [
            1 =>  70.00,  // E1 Bajo-bajo
            2 =>  40.00,  // E2 Bajo
            3 =>  15.00,  // E3 Medio-bajo
            4 =>   0.00,  // E4 Medio
            5 => -50.00,  // E5 Medio-alto
            6 => -60.00,  // E6 Alto
            7 => -60.00,  // Comercial
            8 => -60.00,  // Industrial
            9 =>   0.00,  // Oficial
        ];

        foreach ($valores as $numero => $pct) {
            DB::table('estratos')
                ->where('numero', $numero)
                ->update(['porcentaje_subsidio' => $pct]);
        }

        // 3. Recalcular subsidio_emergencia en facturas existentes
        //    que tienen subsidio_emergencia = 0 pero su estrato tiene porcentaje != 0.
        //    Se calcula desde consumo_basico_acueducto_valor (ya guardado en la factura).
        //    NO se modifica total_a_pagar para no alterar montos históricos.
        $estratos = DB::table('estratos')
            ->where('porcentaje_subsidio', '!=', 0)
            ->get(['numero', 'porcentaje_subsidio']);

        foreach ($estratos as $estrato) {
            $pct = (float) $estrato->porcentaje_subsidio;

            // Obtener facturas de este estrato con subsidio en cero
            $facturas = DB::table('facturas')
                ->where('estrato_snapshot', $estrato->numero)
                ->where('subsidio_emergencia', 0)
                ->where('consumo_basico_acueducto_valor', '>', 0)
                ->select('id', 'consumo_basico_acueducto_valor', 'total_facturacion_acueducto', 'subtotal_conexion_otros_acueducto', 'total_a_pagar')
                ->get();

            foreach ($facturas as $f) {
                $subsidio = round((float)$f->consumo_basico_acueducto_valor * $pct / 100, 2);

                // Actualizar solo los campos de display + recalcular totales derivados
                $nuevoTotalAcueducto  = round((float)$f->total_facturacion_acueducto - $subsidio, 2);
                $deltaSubtotal        = round((float)$f->subtotal_conexion_otros_acueducto - $subsidio, 2);
                $nuevoTotal           = round((float)$f->total_a_pagar - $subsidio, 2);

                DB::table('facturas')->where('id', $f->id)->update([
                    'subsidio_emergencia'                => $subsidio,
                    'total_facturacion_acueducto'        => $nuevoTotalAcueducto,
                    'subtotal_conexion_otros_acueducto'  => $deltaSubtotal,
                    'total_a_pagar'                      => $nuevoTotal,
                ]);
            }
        }
    }

    public function down()
    {
        if (Schema::hasColumn('estratos', 'porcentaje_subsidio')) {
            Schema::table('estratos', function (Blueprint $table) {
                $table->dropColumn('porcentaje_subsidio');
            });
        }
    }
}
