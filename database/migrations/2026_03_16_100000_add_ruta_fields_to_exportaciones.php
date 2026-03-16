<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRutaFieldsToExportaciones extends Migration
{
    public function up()
    {
        Schema::table('exportaciones', function (Blueprint $table) {
            $table->string('periodo', 6)->nullable()->after('usuario_id');
            $table->string('id_ruta', 20)->nullable()->after('periodo');
            $table->string('tipo', 20)->default('masiva')->after('id_ruta'); // masiva | por_ruta

            $table->index(['tipo', 'periodo']);
        });
    }

    public function down()
    {
        Schema::table('exportaciones', function (Blueprint $table) {
            $table->dropIndex(['tipo', 'periodo']);
            $table->dropColumn(['periodo', 'id_ruta', 'tipo']);
        });
    }
}
