<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Agrega al menú del módulo de Facturación los ítems nuevos:
 *  - Configuración Empresa (logo, NIT, prefijo, Wompi)
 *  - Portal de Pagos (enlace a la pasarela pública)
 *  - Facturación por Lote
 *  - Facturación Especial
 *
 * Es idempotente: no duplica si el ítem ya existe.
 *
 * Ejecutar con:
 *   php artisan db:seed --class=NuevosMenuSeeder
 */
class NuevosMenuSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');

        // ── 1. Buscar el padre "Facturación" ──────────────────────────────────
        $padreFacturacion = DB::table('menu')->where('nombre', 'Facturación')->first();

        if (!$padreFacturacion) {
            $this->command->error('  [!] No existe el menú "Facturación". Ejecute primero FacturacionMenuSeeder.');
            return;
        }

        $padreId = $padreFacturacion->id;
        $this->command->info("  [=] Menú padre 'Facturación' encontrado (id={$padreId})");

        // ── 2. Calcular orden base para los nuevos hijos ──────────────────────
        $maxOrdenHijo = DB::table('menu')->where('menu_id', $padreId)->max('orden') ?? 0;

        // ── 3. Hijos nuevos bajo "Facturación" ────────────────────────────────
        $nuevosHijos = [
            [
                'nombre' => 'Facturación por Lote',
                'url'    => 'facturacion/facturas/lote',
                'icono'  => 'fas fa-layer-group',
                'orden'  => $maxOrdenHijo + 1,
            ],
            [
                'nombre' => 'Facturación Especial',
                'url'    => 'facturacion/facturas-especial',
                'icono'  => 'fas fa-star-of-life',
                'orden'  => $maxOrdenHijo + 2,
            ],
            [
                'nombre' => 'Configuración Empresa',
                'url'    => 'facturacion/empresa',
                'icono'  => 'fas fa-building',
                'orden'  => $maxOrdenHijo + 3,
            ],
            [
                'nombre' => 'Portal de Pagos',
                'url'    => 'pagar',
                'icono'  => 'fas fa-globe',
                'orden'  => $maxOrdenHijo + 4,
            ],
        ];

        $idsPorNombre = [];

        foreach ($nuevosHijos as $hijo) {
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

        // ── 4. Asignar a roles ────────────────────────────────────────────────
        // administrador → todos
        // supervisor    → Facturación Lote, Especial, Portal de Pagos
        //                 (NO Configuración Empresa — solo admin)

        $roles = DB::table('rol')
            ->whereIn('nombre', ['administrador', 'supervisor'])
            ->pluck('id', 'nombre');

        if ($roles->isEmpty()) {
            $this->command->error('  [!] No se encontraron roles.');
            return;
        }

        // Qué ve cada rol
        $permisosPorRol = [
            'administrador' => array_values($idsPorNombre),                 // todos los ítems nuevos
            'supervisor'    => array_values(array_filter($idsPorNombre,    // solo los que no son config
                fn($nombre) => $nombre !== 'Configuración Empresa',
                ARRAY_FILTER_USE_KEY
            )),
        ];

        foreach ($permisosPorRol as $rolNombre => $menuIds) {
            $rolId = $roles[$rolNombre] ?? null;
            if (!$rolId) continue;

            foreach ($menuIds as $menuId) {
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
        $this->command->info('  ✔  Nuevos ítems de menú registrados correctamente.');
        $this->command->info('     Puede ajustar permisos por rol en: /admin/menu-rol');
    }
}
