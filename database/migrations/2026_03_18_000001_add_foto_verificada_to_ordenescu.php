<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFotoVerificadaToOrdenescu extends Migration
{
    public function up()
    {
        Schema::table('ordenescu', function (Blueprint $table) {
            // 0 = pendiente, 1 = verificada OK, -1 = foto faltante
            $table->tinyInteger('foto_verificada')->default(0)->after('foto2');
            $table->timestamp('foto_verificada_at')->nullable()->after('foto_verificada');
        });
    }

    public function down()
    {
        Schema::table('ordenescu', function (Blueprint $table) {
            $table->dropColumn(['foto_verificada', 'foto_verificada_at']);
        });
    }
}
