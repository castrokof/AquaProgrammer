<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Inserta el menú del módulo de Clientes / Verificación de Fotos en la tabla `menu`
 * y asigna los ítems a los roles administrador y supervisor.
 *
 * Es idempotente: no duplica si ya existe el menú padre o los hijos.
 *
 * Ejecutar con:
 *   php artisan db:seed --class=ClientesFotosMenuSeeder
 */
class ClientesFotosMenuSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');

        // ── 1. Menú padre "Clientes" ──────────────────────────────────────────
        $padre = DB::table('menu')->where('nombre', 'Clientes')->where('menu_id', 0)->first();

        if (!$padre) {
            $maxOrden = DB::table('menu')->where('menu_id', 0)->max('orden') ?? 0;

            $padreId = DB::table('menu')->insertGetId([
                'menu_id'    => 0,
                'nombre'     => 'Clientes',
                'url'        => 'javascript:;',
                'orden'      => $maxOrden + 1,
                'icono'      => 'fas fa-users',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->command->info("  [+] Menú padre 'Clientes' creado (id={$padreId})");
        } else {
            $padreId = $padre->id;
            $this->command->warn("  [=] Menú padre 'Clientes' ya existe (id={$padreId}), se omite creación.");
        }

        // ── 2. Hijos ──────────────────────────────────────────────────────────
        $hijos = [
            [
                'nombre' => 'Listado de Clientes',
                'url'    => 'clientes',
                'icono'  => 'fas fa-list-ul',
                'orden'  => 1,
                'roles'  => ['administrador', 'supervisor'],
            ],
            [
                'nombre' => 'Verificación de Fotos',
                'url'    => 'clientes',
                'icono'  => 'fas fa-camera',
                'orden'  => 2,
                'roles'  => ['administrador', 'supervisor'],
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
                $idsPorNombre[$hijo['nombre']] = ['id' => $id, 'roles' => $hijo['roles']];
            } else {
                $this->command->warn("  [=] Submenú '{$hijo['nombre']}' ya existe (id={$existe->id}), se omite.");
                $idsPorNombre[$hijo['nombre']] = ['id' => $existe->id, 'roles' => $hijo['roles']];
            }
        }

        // ── 3. Asignar a roles ────────────────────────────────────────────────
        $roles = DB::table('rol')
            ->whereIn('nombre', ['administrador', 'supervisor'])
            ->pluck('id', 'nombre');

        if ($roles->isEmpty()) {
            $this->command->error('  [!] No se encontraron roles. Ejecute primero RolTablaSeeder.');
            return;
        }

        // Asignar menú padre a admin y supervisor
        foreach (['administrador', 'supervisor'] as $rolNombre) {
            $rolId = $roles[$rolNombre] ?? null;
            if (!$rolId) continue;

            $yaAsignado = DB::table('menu_rol')
                ->where('rol_id', $rolId)
                ->where('menu_id', $padreId)
                ->exists();

            if (!$yaAsignado) {
                DB::table('menu_rol')->insert(['rol_id' => $rolId, 'menu_id' => $padreId]);
                $this->command->info("  [+] Padre menu_id={$padreId} asignado al rol '{$rolNombre}'");
            }
        }

        // Asignar hijos según sus roles definidos
        foreach ($idsPorNombre as $nombre => $data) {
            foreach ($data['roles'] as $rolNombre) {
                $rolId = $roles[$rolNombre] ?? null;
                if (!$rolId) continue;

                $yaAsignado = DB::table('menu_rol')
                    ->where('rol_id', $rolId)
                    ->where('menu_id', $data['id'])
                    ->exists();

                if (!$yaAsignado) {
                    DB::table('menu_rol')->insert(['rol_id' => $rolId, 'menu_id' => $data['id']]);
                    $this->command->info("  [+] menu_id={$data['id']} ({$nombre}) asignado al rol '{$rolNombre}'");
                }
            }
        }

        $this->command->info('');
        $this->command->info('  ✔  Módulo de Clientes / Verificación de Fotos cargado en el menú.');
        $this->command->info('     Puede ajustar permisos por rol en: /admin/menu-rol');
    }
}
