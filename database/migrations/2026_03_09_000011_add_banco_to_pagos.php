<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBancoPagos extends Migration
{
    public function up()
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->string('banco', 100)->nullable()->after('medio_pago')
                ->comment('Banco destino/origen cuando es TRANSFERENCIA o CONSIGNACION');
            $table->string('referencia_pasarela', 100)->nullable()->after('banco')
                ->comment('ID de transacción externo (Wompi, etc.)');
            $table->string('estado_pasarela', 30)->nullable()->after('referencia_pasarela')
                ->comment('Estado reportado por la pasarela: APPROVED, DECLINED, etc.');
        });
    }

    public function down()
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn(['banco', 'referencia_pasarela', 'estado_pasarela']);
        });
    }
}
