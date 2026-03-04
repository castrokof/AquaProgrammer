<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Inserta el menú del módulo de Facturación en la tabla `menu`
 * y asigna los ítems a los roles administrador y supervisor.
 *
 * Es idempotente: no duplica si ya existe el menú padre.
 *
 * Ejecutar con:
 *   php artisan db:seed --class=FacturacionMenuSeeder
 */
class FacturacionMenuSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');

        // ── 1. Menú padre ─────────────────────────────────────────────────────
        $padre = DB::table('menu')->where('nombre', 'Facturación')->first();

        if (!$padre) {
            // Calcular el siguiente orden (va al final del menú principal)
            $maxOrden = DB::table('menu')->where('menu_id', 0)->max('orden') ?? 0;

            $padreId = DB::table('menu')->insertGetId([
                'menu_id'    => 0,
                'nombre'     => 'Facturación',
                'url'        => 'javascript:;',
                'orden'      => $maxOrden + 1,
                'icono'      => 'fas fa-file-invoice-dollar',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->command->info("  [+] Menú padre 'Facturación' creado (id={$padreId})");
        } else {
            $padreId = $padre->id;
            $this->command->warn("  [=] Menú padre 'Facturación' ya existe (id={$padreId}), se omite creación.");
        }

        // ── 2. Hijos ──────────────────────────────────────────────────────────
        $hijos = [
            [
                'nombre' => 'Clientes',
                'url'    => 'clientes',
                'icono'  => 'fas fa-users',
                'orden'  => 1,
            ],
            [
                'nombre' => 'Períodos de Lectura',
                'url'    => 'facturacion/periodos',
                'icono'  => 'fas fa-calendar-alt',
                'orden'  => 2,
            ],
            [
                'nombre' => 'Resoluciones Tarifarias',
                'url'    => 'facturacion/tarifas',
                'icono'  => 'fas fa-tags',
                'orden'  => 3,
            ],
            [
                'nombre' => 'Facturas',
                'url'    => 'facturacion/facturas',
                'icono'  => 'fas fa-file-invoice',
                'orden'  => 4,
            ],
            [
                'nombre' => 'Generar Factura',
                'url'    => 'facturacion/facturas/generar',
                'icono'  => 'fas fa-plus-circle',
                'orden'  => 5,
            ],
            [
                'nombre' => 'Otros Cobros',
                'url'    => 'facturacion/otros-cobros',
                'icono'  => 'fas fa-receipt',
                'orden'  => 6,
            ],
            [
                'nombre' => 'Pagos',
                'url'    => 'facturacion/pagos',
                'icono'  => 'fas fa-money-bill-wave',
                'orden'  => 7,
            ],
        ];

        $idsPorNombre = [];

        foreach ($hijos as $hijo) {
            $existe = DB::table('menu')
                ->where('menu_id', $padreId)
                ->where('nombre', $hijo['nombre'])
                ->first();

            if (!$existe) {
                $id = DB::table('menu')->insertGetId([
                    'menu_id'    => $padreId,
                    'nombre'     => $hijo['nombre'],
                    'url'        => $hijo['url'],
                    'orden'      => $hijo['orden'],
                    'icono'      => $hijo['icono'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $this->command->info("  [+] Submenú '{$hijo['nombre']}' creado (id={$id})");
                $idsPorNombre[$hijo['nombre']] = $id;
            } else {
                $this->command->warn("  [=] Submenú '{$hijo['nombre']}' ya existe (id={$existe->id}), se omite.");
                $idsPorNombre[$hijo['nombre']] = $existe->id;
            }
        }

        // ── 3. Asignar a roles ────────────────────────────────────────────────
        // administrador → todos los ítems
        // supervisor    → todos los ítems (puede facturar y ver períodos/tarifas)
        // movil         → ninguno (operarios de campo no necesitan facturación)

        $roles = DB::table('rol')
            ->whereIn('nombre', ['administrador', 'supervisor'])
            ->pluck('id', 'nombre');

        if ($roles->isEmpty()) {
            $this->command->error('  [!] No se encontraron roles. Ejecute primero RolTablaSeeder.');
            return;
        }

        // Todos los IDs de menú del módulo (padre + hijos)
        $todosIds = array_merge([$padreId], array_values($idsPorNombre));

        foreach ($roles as $rolNombre => $rolId) {
            foreach ($todosIds as $menuId) {
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
        }

        $this->command->info('');
        $this->command->info('  ✔  Módulo de Facturación cargado en el menú.');
        $this->command->info('     Recuerde asignar ítems a otros roles desde: /admin/menu-rol');
    }
}
