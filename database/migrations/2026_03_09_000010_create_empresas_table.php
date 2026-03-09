<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmpresasTable extends Migration
{
    public function up()
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre', 150)->default('');
            $table->string('nit', 30)->nullable();
            $table->string('direccion', 200)->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('logo_path', 300)->nullable()->comment('Ruta relativa en storage/public');
            $table->string('prefijo_factura', 20)->nullable()->comment('Prefijo del consecutivo de factura, ej: ASPD');
            $table->string('texto_documento_equivalente', 200)
                ->default('Documento Equivalente Servicios Públicos Domiciliarios');
            $table->string('texto_pie', 300)->nullable()->comment('Texto de pie de página en PDF');
            $table->string('nombre_banco', 100)->nullable();
            $table->string('numero_cuenta', 50)->nullable();
            $table->timestamps();
        });

        // Insertar registro por defecto
        DB::table('empresas')->insert([
            'nombre'                     => 'EMPRESA DE SERVICIOS PÚBLICOS',
            'nit'                        => '',
            'texto_documento_equivalente'=> 'Documento Equivalente Servicios Públicos Domiciliarios',
            'prefijo_factura'            => '',
            'created_at'                 => now(),
            'updated_at'                 => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('empresas');
    }
}
