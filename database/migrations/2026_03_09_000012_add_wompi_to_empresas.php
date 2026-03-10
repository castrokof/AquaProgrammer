<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWompiToEmpresas extends Migration
{
    public function up()
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('wompi_public_key', 120)->nullable()->after('numero_cuenta')
                ->comment('Llave pública Wompi (pub_test_xxx o pub_prod_xxx)');
            $table->string('wompi_private_key', 120)->nullable()->after('wompi_public_key')
                ->comment('Llave privada Wompi para validar webhooks (prv_test_xxx)');
            $table->string('wompi_integrity_key', 120)->nullable()->after('wompi_private_key')
                ->comment('Llave de integridad Wompi para firmar transacciones');
            $table->boolean('wompi_test_mode')->default(true)->after('wompi_integrity_key')
                ->comment('true = entorno sandbox, false = producción');
            $table->string('wompi_redirect_url', 300)->nullable()->after('wompi_test_mode')
                ->comment('URL de redirección después del pago (debe ser pública)');
        });
    }

    public function down()
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn([
                'wompi_public_key','wompi_private_key','wompi_integrity_key',
                'wompi_test_mode','wompi_redirect_url',
            ]);
        });
    }
}
