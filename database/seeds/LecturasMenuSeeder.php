<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Agrega al menú de Facturación el ítem "Cargar Lecturas Anteriores".
 *
 * Es idempotente: no duplica si ya existe.
 *
 * Ejecutar con:
 *   php artisan db:seed --class=LecturasMenuSeeder
 */
class LecturasMenuSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');

        // ── 1. Buscar el padre "Facturación" ──────────────────────────────────
        $padre = DB::table('menu')->where('nombre', 'Facturación')->first();

        if (!$padre) {
            $this->command->error('  [!] No existe el menú "Facturación". Ejecute primero FacturacionMenuSeeder.');
            return;
        }

        $padreId = $padre->id;
        $this->command->info("  [=] Menú padre 'Facturación' encontrado (id={$padreId})");

        // ── 2. Insertar ítem "Cargar Lecturas Anteriores" ─────────────────────
        $existe = DB::table('menu')
            ->where('menu_id', $padreId)
            ->where('nombre', 'Cargar Lecturas Anteriores')
            ->first();

        if ($existe) {
            $this->command->warn("  [=] Submenú 'Cargar Lecturas Anteriores' ya existe (id={$existe->id}), se omite.");
            $menuId = $existe->id;
        } else {
            $maxOrden = DB::table('menu')->where('menu_id', $padreId)->max('orden') ?? 0;

            $menuId = DB::table('menu')->insertGetId([
                'menu_id'    => $padreId,
                'nombre'     => 'Cargar Lecturas Anteriores',
                'url'        => 'lecturas/importar',
                'orden'      => $maxOrden + 1,
                'icono'      => 'fas fa-file-upload',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->command->info("  [+] Submenú 'Cargar Lecturas Anteriores' creado (id={$menuId})");
        }

        // ── 3. Asignar a roles administrador y supervisor ─────────────────────
        $roles = DB::table('rol')
            ->whereIn('nombre', ['administrador', 'supervisor'])
            ->pluck('id', 'nombre');

        if ($roles->isEmpty()) {
            $this->command->error('  [!] No se encontraron roles. Ejecute primero RolTablaSeeder.');
            return;
        }

        foreach ($roles as $rolNombre => $rolId) {
            $yaAsignado = DB::table('menu_rol')
                ->where('rol_id', $rolId)
                ->where('menu_id', $menuId)
                ->exists();

            if (!$yaAsignado) {
                DB::table('menu_rol')->insert([
                    'rol_id'  => $rolId,
                    'menu_id' => $menuId,
                ]);
                $this->command->info("  [+] menu_id={$menuId} asignado al rol '{$rolNombre}'");
            }
        }

        $this->command->info('');
        $this->command->info('  ✔  Ítem "Cargar Lecturas Anteriores" registrado en el menú.');
        $this->command->info('     URL: lecturas/importar');
    }
}
