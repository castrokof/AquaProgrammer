<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RevisionAddActaNuevaLectura extends Migration
{
    public function up()
    {
        Schema::table('ordenes_revision', function (Blueprint $table) {
            // Ruta al PDF del acta enviado desde la app móvil
            $table->string('acta_pdf', 400)->nullable()->after('firma_cliente')
                  ->comment('Ruta al archivo PDF del acta de visita');

            // Nueva lectura tomada en campo durante la revisión
            $table->integer('nueva_lectura')->nullable()->after('acta_pdf')
                  ->comment('Lectura del medidor tomada por el revisor durante la visita');
        });
    }

    public function down()
    {
        Schema::table('ordenes_revision', function (Blueprint $table) {
            $table->dropColumn(['acta_pdf', 'nueva_lectura']);
        });
    }
}
