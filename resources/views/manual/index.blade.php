@extends("theme.$theme.layout")

@section('titulo', 'Manual de Usuario')

@section('styles')
<style>
/* ── Manual de Usuario ─────────────────────────────────────────────────────── */
.manual-wrap {
    display: flex;
    gap: 0;
    min-height: calc(100vh - 120px);
    font-family: 'Source Sans Pro', sans-serif;
}

/* Sidebar de navegación */
.manual-nav {
    width: 270px;
    flex-shrink: 0;
    background: #1e2a3a;
    color: #c8d6e5;
    padding: 24px 0;
    position: sticky;
    top: 0;
    height: calc(100vh - 56px);
    overflow-y: auto;
    border-radius: 14px 0 0 14px;
}
.manual-nav h2 {
    font-size: .78rem;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    color: #5a7fa8;
    padding: 0 20px 8px;
    margin: 0 0 4px;
    border-bottom: 1px solid #263347;
}
.manual-nav a {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 9px 20px;
    color: #a8bdd0;
    font-size: .85rem;
    text-decoration: none;
    border-left: 3px solid transparent;
    transition: all .15s;
}
.manual-nav a:hover,
.manual-nav a.active {
    background: #263347;
    color: #fff;
    border-left-color: #4299e1;
}
.manual-nav a i { width: 16px; text-align: center; font-size: .8rem; }

/* Cuerpo del manual */
.manual-body {
    flex: 1;
    background: #f7f9fc;
    padding: 32px 40px 60px;
    overflow-y: auto;
}
.manual-section {
    display: none;
}
.manual-section.active {
    display: block;
}

/* Tarjeta de sección */
.m-card {
    background: white;
    border-radius: 14px;
    padding: 28px 32px;
    margin-bottom: 24px;
    box-shadow: 0 1px 6px rgba(0,0,0,.06);
}
.m-card h1 {
    font-size: 1.45rem;
    font-weight: 800;
    color: #1a202c;
    margin: 0 0 6px;
}
.m-card .subtitle {
    color: #718096;
    font-size: .88rem;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 2px solid #edf2f7;
}
.m-card h2 {
    font-size: 1.05rem;
    font-weight: 700;
    color: #2d3748;
    margin: 24px 0 10px;
}
.m-card h3 {
    font-size: .9rem;
    font-weight: 700;
    color: #4a5568;
    margin: 18px 0 8px;
}
.m-card p {
    font-size: .88rem;
    color: #4a5568;
    line-height: 1.65;
    margin-bottom: 10px;
}
.m-card ul, .m-card ol {
    font-size: .88rem;
    color: #4a5568;
    line-height: 1.7;
    padding-left: 22px;
    margin-bottom: 14px;
}
.m-tip {
    background: #ebf8ff;
    border-left: 4px solid #4299e1;
    border-radius: 0 10px 10px 0;
    padding: 12px 16px;
    font-size: .83rem;
    color: #2b6cb0;
    margin: 14px 0;
}
.m-warn {
    background: #fffbeb;
    border-left: 4px solid #ecc94b;
    border-radius: 0 10px 10px 0;
    padding: 12px 16px;
    font-size: .83rem;
    color: #744210;
    margin: 14px 0;
}
.m-ok {
    background: #f0fff4;
    border-left: 4px solid #48bb78;
    border-radius: 0 10px 10px 0;
    padding: 12px 16px;
    font-size: .83rem;
    color: #276749;
    margin: 14px 0;
}
.badge-estado {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 20px;
    font-size: .75rem;
    font-weight: 700;
}
.badge-plan  { background:#ebf8ff; color:#2b6cb0; }
.badge-act   { background:#f0fff4; color:#276749; }
.badge-lc    { background:#fffbeb; color:#744210; }
.badge-fact  { background:#faf5ff; color:#553c9a; }
.badge-cerr  { background:#f7fafc; color:#718096; }

.step-row {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    margin-bottom: 12px;
}
.step-num {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #4299e1;
    color: white;
    font-weight: 800;
    font-size: .8rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 1px;
}
.step-text { font-size: .87rem; color: #4a5568; line-height: 1.55; }

/* Mapa del sitio */
.sitemap-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 14px;
    margin-top: 14px;
}
.sitemap-card {
    background: #f7f9fc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 14px 16px;
    cursor: pointer;
    transition: all .15s;
}
.sitemap-card:hover {
    border-color: #4299e1;
    box-shadow: 0 2px 8px rgba(66,153,225,.15);
}
.sitemap-card i {
    font-size: 1.2rem;
    margin-bottom: 8px;
    display: block;
}
.sitemap-card strong {
    display: block;
    font-size: .83rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 4px;
}
.sitemap-card span {
    font-size: .75rem;
    color: #718096;
}

/* Tabla comparativa estados */
.states-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
.states-table th {
    background: #f7f9fc;
    padding: 8px 12px;
    text-align: left;
    font-size: .78rem;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: #718096;
    border-bottom: 2px solid #e2e8f0;
}
.states-table td {
    padding: 10px 12px;
    font-size: .84rem;
    color: #4a5568;
    border-bottom: 1px solid #f0f0f0;
    vertical-align: top;
}
</style>
@endsection

@section('contenido')
<div class="manual-wrap">

    {{-- ══════════════════════════════════════════════════════════════════
         BARRA LATERAL DE NAVEGACIÓN
         ══════════════════════════════════════════════════════════════════ --}}
    <nav class="manual-nav" id="manualNav">
        <h2>Manual de Usuario</h2>

        <a href="#" data-sec="mapa"         class="nav-link-manual active">
            <i class="fa fa-sitemap"></i> Mapa del sistema
        </a>
        <a href="#" data-sec="clientes"     class="nav-link-manual">
            <i class="fa fa-users"></i> Clientes
        </a>
        <a href="#" data-sec="periodos"     class="nav-link-manual">
            <i class="fa fa-calendar-alt"></i> Períodos de Lectura
        </a>
        <a href="#" data-sec="lecturas"     class="nav-link-manual">
            <i class="fa fa-file-import"></i> Carga de Lecturas
        </a>
        <a href="#" data-sec="asignacion"   class="nav-link-manual">
            <i class="fa fa-tasks"></i> Asignación de Órdenes
        </a>
        <a href="#" data-sec="facturacion"  class="nav-link-manual">
            <i class="fa fa-file-invoice-dollar"></i> Facturación
        </a>
        <a href="#" data-sec="pagos"        class="nav-link-manual">
            <i class="fa fa-money-bill-wave"></i> Pagos y Cobros
        </a>
        <a href="#" data-sec="tarifas"      class="nav-link-manual">
            <i class="fa fa-percentage"></i> Tarifas y Subsidios
        </a>
        <a href="#" data-sec="reportes"     class="nav-link-manual">
            <i class="fa fa-chart-bar"></i> Reportes y Exportación
        </a>
        <a href="#" data-sec="admin"        class="nav-link-manual">
            <i class="fa fa-cogs"></i> Administración
        </a>
    </nav>

    {{-- ══════════════════════════════════════════════════════════════════
         CUERPO
         ══════════════════════════════════════════════════════════════════ --}}
    <div class="manual-body" id="manualBody">

        {{-- ─── MAPA DEL SISTEMA ─── --}}
        <div class="manual-section active" id="sec-mapa">
            <div class="m-card">
                <h1><i class="fa fa-sitemap" style="color:#4299e1;margin-right:8px;"></i>Mapa del Sistema</h1>
                <p class="subtitle">AquaProgrammer es una plataforma integral de facturación y lectura para empresas de acueducto y alcantarillado. Permite gestionar el ciclo completo: desde el registro del cliente hasta el cobro de la factura.</p>

                <div class="sitemap-grid">
                    <div class="sitemap-card" onclick="mostrarSeccion('clientes')">
                        <i class="fa fa-users" style="color:#4299e1;"></i>
                        <strong>Clientes</strong>
                        <span>Registro, perfil, medidor y fotos</span>
                    </div>
                    <div class="sitemap-card" onclick="mostrarSeccion('periodos')">
                        <i class="fa fa-calendar-alt" style="color:#48bb78;"></i>
                        <strong>Períodos de Lectura</strong>
                        <span>Ciclos de facturación y estados</span>
                    </div>
                    <div class="sitemap-card" onclick="mostrarSeccion('lecturas')">
                        <i class="fa fa-file-import" style="color:#ed8936;"></i>
                        <strong>Carga de Lecturas</strong>
                        <span>Excel y sincronización API</span>
                    </div>
                    <div class="sitemap-card" onclick="mostrarSeccion('asignacion')">
                        <i class="fa fa-tasks" style="color:#9f7aea;"></i>
                        <strong>Asignación de Órdenes</strong>
                        <span>Asignación y seguimiento</span>
                    </div>
                    <div class="sitemap-card" onclick="mostrarSeccion('facturacion')">
                        <i class="fa fa-file-invoice-dollar" style="color:#f56565;"></i>
                        <strong>Facturación</strong>
                        <span>Masiva, especial y lote</span>
                    </div>
                    <div class="sitemap-card" onclick="mostrarSeccion('pagos')">
                        <i class="fa fa-money-bill-wave" style="color:#48bb78;"></i>
                        <strong>Pagos</strong>
                        <span>Registro, pasarela y cobros</span>
                    </div>
                    <div class="sitemap-card" onclick="mostrarSeccion('tarifas')">
                        <i class="fa fa-percentage" style="color:#4299e1;"></i>
                        <strong>Tarifas y Subsidios</strong>
                        <span>Cargos fijos, rangos y estratos</span>
                    </div>
                    <div class="sitemap-card" onclick="mostrarSeccion('reportes')">
                        <i class="fa fa-chart-bar" style="color:#ed8936;"></i>
                        <strong>Reportes</strong>
                        <span>PDF, Excel y KPIs</span>
                    </div>
                    <div class="sitemap-card" onclick="mostrarSeccion('admin')">
                        <i class="fa fa-cogs" style="color:#718096;"></i>
                        <strong>Administración</strong>
                        <span>Usuarios, roles y permisos</span>
                    </div>
                </div>

                <h2>Flujo principal de un período de facturación</h2>
                <div class="step-row"><div class="step-num">1</div><div class="step-text"><strong>Crear Período:</strong> Define fechas, ciclo y tarifa. Estado inicial: <span class="badge-estado badge-plan">PLANIFICADO</span></div></div>
                <div class="step-row"><div class="step-num">2</div><div class="step-text"><strong>Generar Órdenes:</strong> Se crean las órdenes de lectura para todos los clientes con medidor. Estado: <span class="badge-estado badge-act">ACTIVO</span></div></div>
                <div class="step-row"><div class="step-num">3</div><div class="step-text"><strong>Tomar Lecturas:</strong> Los operadores de campo registran las lecturas en las órdenes asignadas.</div></div>
                <div class="step-row"><div class="step-num">4</div><div class="step-text"><strong>Cerrar Lectura:</strong> Se avanza el estado a <span class="badge-estado badge-lc">LECTURA_CERRADA</span>. Ya no se permiten cambios en lecturas.</div></div>
                <div class="step-row"><div class="step-num">5</div><div class="step-text"><strong>Generar Facturas:</strong> Facturación masiva o individual. Estado: <span class="badge-estado badge-fact">FACTURADO</span></div></div>
                <div class="step-row"><div class="step-num">6</div><div class="step-text"><strong>Cobro y cierre:</strong> Los clientes pagan. Al conciliar, el período se cierra: <span class="badge-estado badge-cerr">CERRADO</span></div></div>
            </div>
        </div>

        {{-- ─── CLIENTES ─── --}}
        <div class="manual-section" id="sec-clientes">
            <div class="m-card">
                <h1><i class="fa fa-users" style="color:#4299e1;margin-right:8px;"></i>Gestión de Clientes</h1>
                <p class="subtitle">Módulo para registrar, consultar y actualizar la información de los suscriptores del servicio.</p>

                <h2>Listado de Clientes</h2>
                <p>Acceso: menú lateral → <strong>Clientes</strong>. La tabla muestra todos los suscriptores con filtro de búsqueda en tiempo real por nombre, suscriptor, NUIP o serie de medidor.</p>
                <p><strong>Acciones disponibles en la tabla:</strong></p>
                <ul>
                    <li><i class="fa fa-eye" style="color:#3182ce;"></i> <strong>Ver perfil</strong> — Abre el panel lateral con resumen del cliente.</li>
                    <li><i class="fa fa-tachometer-alt" style="color:#d69e2e;"></i> <strong>Editar medidor</strong> — Abre un mini-modal para cambiar la serie del medidor directamente desde la tabla, sin abrir el perfil completo. El cambio queda registrado en el historial de series del cliente.</li>
                </ul>

                <h2>Crear o actualizar cliente</h2>
                <p>Haz clic en <strong>Nuevo Cliente</strong> o busca uno existente por su número de suscriptor. Los campos obligatorios son:</p>
                <ul>
                    <li><strong>Suscriptor</strong> — Código único del cliente en el sistema.</li>
                    <li><strong>Estado</strong> — ACTIVO, SUSPENDIDO, CORTADO o INACTIVO.</li>
                    <li><strong>Estrato</strong> — Determina el subsidio o contribución aplicado en las facturas.</li>
                </ul>
                <div class="m-tip"><i class="fa fa-info-circle"></i> Si el suscriptor ya tiene órdenes de lectura en el sistema, sus datos (nombre, dirección, serie) se autocompletan desde la última orden ejecutada.</div>

                <h2>Medidor (Serie)</h2>
                <p>La serie del medidor se puede actualizar de dos maneras:</p>
                <ol>
                    <li>Desde el perfil completo del cliente (formulario).</li>
                    <li>Desde el botón <i class="fa fa-tachometer-alt" style="color:#d69e2e;"></i> de la tabla — edición rápida sin recargar la página.</li>
                </ol>
                <p>Cada cambio de serie queda registrado automáticamente en el <strong>historial de series</strong> vinculado al período de facturación vigente.</p>

                <h2>Fotos del cliente</h2>
                <p>Cada cliente puede tener fotos de dos tipos:</p>
                <ul>
                    <li><strong>M — Medidor:</strong> Foto del medidor instalado.</li>
                    <li><strong>P — Predio:</strong> Foto del predio o conexión.</li>
                </ul>
                <p>Para agregar una foto: abre el perfil → sección Fotos → selecciona el tipo y el archivo. Para eliminar: icono de papelera junto a la foto.</p>
                <div class="m-warn"><i class="fa fa-exclamation-triangle"></i> Solo se permiten imágenes (JPG, PNG, GIF). El tamaño máximo es el configurado en el servidor (generalmente 10 MB).</div>
            </div>
        </div>

        {{-- ─── PERÍODOS DE LECTURA ─── --}}
        <div class="manual-section" id="sec-periodos">
            <div class="m-card">
                <h1><i class="fa fa-calendar-alt" style="color:#48bb78;margin-right:8px;"></i>Períodos de Lectura</h1>
                <p class="subtitle">Cada período representa un ciclo de facturación mensual. El flujo de estados es unidireccional y no puede revertirse.</p>

                <h2>Crear un Período</h2>
                <p>Menú: <strong>Facturación → Períodos</strong>. Haz clic en <strong>Nuevo Período</strong> e ingresa:</p>
                <ul>
                    <li><strong>Código</strong> — 6 dígitos: año + mes (ej: <code>202503</code> para marzo de 2025). Debe ser único.</li>
                    <li><strong>Nombre</strong> — Descripción libre (ej: "Marzo 2025 Ciclo 1").</li>
                    <li><strong>Ciclo</strong> — Número del ciclo de facturación.</li>
                    <li><strong>Tarifa del período</strong> — Tarifa activa que se aplicará para calcular las facturas.</li>
                    <li><strong>Fechas</strong> — Inicio y fin de lectura, fecha de expedición, vencimiento y corte.</li>
                </ul>

                <h2>Estados del Período</h2>
                <table class="states-table">
                    <tr><th>Estado</th><th>Significado</th><th>Acciones permitidas</th></tr>
                    <tr>
                        <td><span class="badge-estado badge-plan">PLANIFICADO</span></td>
                        <td>Período creado, aún sin órdenes generadas.</td>
                        <td>Editar, Generar Órdenes, Avanzar estado.</td>
                    </tr>
                    <tr>
                        <td><span class="badge-estado badge-act">ACTIVO</span></td>
                        <td>Órdenes generadas. Se están tomando lecturas.</td>
                        <td>Ver órdenes, Avanzar a Lectura Cerrada.</td>
                    </tr>
                    <tr>
                        <td><span class="badge-estado badge-lc">LECTURA_CERRADA</span></td>
                        <td>Lecturas finalizadas. Listo para facturar.</td>
                        <td>Generar facturas masivas o individuales.</td>
                    </tr>
                    <tr>
                        <td><span class="badge-estado badge-fact">FACTURADO</span></td>
                        <td>Al menos una factura generada en el período.</td>
                        <td>Exportar, registrar pagos, anular facturas.</td>
                    </tr>
                    <tr>
                        <td><span class="badge-estado badge-cerr">CERRADO</span></td>
                        <td>Período completamente conciliado.</td>
                        <td>Solo consulta y exportación.</td>
                    </tr>
                </table>

                <h2>Generar Órdenes de Lectura</h2>
                <p>En la vista de detalle del período, haz clic en <strong>Generar Órdenes</strong>. El sistema:</p>
                <ol>
                    <li>Crea una orden por cada cliente <strong>ACTIVO con medidor</strong> (<code>tiene_medidor = true</code>).</li>
                    <li>Para clientes <strong>sin medidor</strong>, genera automáticamente la factura usando su <em>promedio de consumo</em>.</li>
                    <li>Ordena las órdenes por sector (CU) y consecutivo de ruta.</li>
                </ol>
                <div class="m-warn"><i class="fa fa-exclamation-triangle"></i> Este proceso no puede ejecutarse dos veces en el mismo período. Si hay un error, debe corregirse antes de volver a intentar.</div>
            </div>
        </div>

        {{-- ─── CARGA DE LECTURAS ─── --}}
        <div class="manual-section" id="sec-lecturas">
            <div class="m-card">
                <h1><i class="fa fa-file-import" style="color:#ed8936;margin-right:8px;"></i>Carga de Lecturas</h1>
                <p class="subtitle">Permite cargar lecturas de medidor mediante Excel o sincronizar desde el sistema de facturas externo.</p>

                <h2>Importar desde Excel</h2>
                <p>Menú: <strong>Lecturas → Importar</strong>. Pasos:</p>
                <div class="step-row"><div class="step-num">1</div><div class="step-text">Descarga la <strong>plantilla Excel</strong> (botón <em>Descargar Plantilla</em>) — contiene las columnas requeridas con el formato correcto.</div></div>
                <div class="step-row"><div class="step-num">2</div><div class="step-text">Llena la plantilla con las lecturas: suscriptor, lectura anterior, lectura actual, fecha.</div></div>
                <div class="step-row"><div class="step-num">3</div><div class="step-text">Sube el archivo usando el formulario de importación. El sistema valida y reporta errores por fila.</div></div>
                <div class="m-tip"><i class="fa fa-info-circle"></i> Las lecturas importadas se vinculan al período activo automáticamente.</div>

                <h2>Sincronizar desde Facturas Anteriores</h2>
                <p>El botón <strong>Sincronizar desde facturas</strong> extrae las lecturas finales del período anterior y las usa como lecturas iniciales del período actual. Útil para arrancar el ciclo sin digitar datos manualmente.</p>

                <h2>Sincronizar desde API externa</h2>
                <p>Si el sistema externo (app móvil de lecturas) expone una API, el botón <strong>Sincronizar API</strong> descarga las lecturas ejecutadas en campo y las carga al período activo.</p>
            </div>
        </div>

        {{-- ─── ASIGNACIÓN DE ÓRDENES ─── --}}
        <div class="manual-section" id="sec-asignacion">
            <div class="m-card">
                <h1><i class="fa fa-tasks" style="color:#9f7aea;margin-right:8px;"></i>Asignación de Órdenes</h1>
                <p class="subtitle">Distribuye las órdenes de lectura entre los operadores de campo y realiza seguimiento del avance.</p>

                <h2>Asignar órdenes</h2>
                <p>Menú: <strong>Asignación</strong>. La tabla muestra todas las órdenes del período activo agrupadas por ciclo y usuario. Para asignar:</p>
                <ol>
                    <li>Selecciona las órdenes de una división.</li>
                    <li>Elige el usuario (lecturista) en el desplegable.</li>
                    <li>Haz clic en <strong>Asignar</strong>.</li>
                </ol>
                <p>Para desasignar, usa el botón <strong>Desasignar</strong> junto a la orden correspondiente.</p>

                <h2>Seguimiento</h2>
                <p>La vista de <strong>Seguimiento</strong> muestra en tiempo real:</p>
                <ul>
                    <li>Órdenes asignadas, pendientes y ejecutadas por usuario.</li>
                    <li>Críticas detectadas: alto consumo, bajo consumo, consumo negativo, lecturas iguales.</li>
                    <li>Hora de inicio y hora final de ejecución.</li>
                </ul>

                <h2>Fotos de la orden ejecutada</h2>
                <p>Cada orden puede tener fotos tomadas en campo. Se visualizan en el detalle de la orden. También se pueden subir desde la vista de administración usando el botón <strong>Fotos</strong>.</p>

                <h2>Detalle y exportación</h2>
                <p>El botón <strong>Detalle</strong> abre el resumen completo de la orden. Usa <strong>Exportar ciclo</strong> para descargar el reporte de un ciclo completo en Excel.</p>
            </div>
        </div>

        {{-- ─── FACTURACIÓN ─── --}}
        <div class="manual-section" id="sec-facturacion">
            <div class="m-card">
                <h1><i class="fa fa-file-invoice-dollar" style="color:#f56565;margin-right:8px;"></i>Facturación</h1>
                <p class="subtitle">El módulo de facturación permite generar, visualizar, exportar y anular facturas de agua y alcantarillado.</p>

                <h2>Facturación Masiva</h2>
                <p>Menú: <strong>Facturación → Masiva</strong>. Genera facturas para todos los clientes con lectura normal en el período activo.</p>
                <div class="step-row"><div class="step-num">1</div><div class="step-text">Selecciona el período (debe estar en estado <span class="badge-estado badge-lc">LECTURA_CERRADA</span> o superior).</div></div>
                <div class="step-row"><div class="step-num">2</div><div class="step-text">Haz clic en <strong>Procesar</strong>. Se muestra un resumen con total de clientes, total a facturar y clientes sin factura.</div></div>
                <div class="step-row"><div class="step-num">3</div><div class="step-text">Confirma para generar. El sistema calcula consumos, aplica tarifas y subsidios por estrato.</div></div>
                <div class="m-ok"><i class="fa fa-check-circle"></i> Los clientes sin medidor ya fueron facturados automáticamente al generar las órdenes usando su promedio de consumo.</div>

                <h2>Facturación Especial</h2>
                <p>Para clientes con lecturas no normales (alto consumo, bajo consumo, negativo, etc.). Menú: <strong>Facturación → Especial</strong>.</p>
                <ul>
                    <li>Lista los clientes con crítica pendiente de resolver.</li>
                    <li>Permite revisar y ajustar la lectura antes de facturar.</li>
                    <li>Se pueden facturar individualmente o en grupo (<em>Facturar seleccionadas</em>).</li>
                </ul>

                <h2>Factura Individual</h2>
                <p>Desde <strong>Facturación → Facturas</strong>, usa el botón <strong>Nueva Factura</strong>. Ingresa el suscriptor, el período y el consumo. El sistema calcula cargos y subsidios automáticamente. Puedes previsualizar antes de guardar.</p>

                <h2>Facturas en Lote</h2>
                <p>Para generar varias facturas seleccionadas a la vez, usa la opción <strong>Lote</strong> en el listado de facturas.</p>

                <h2>Estados de una Factura</h2>
                <ul>
                    <li><strong>PENDIENTE</strong> — Emitida, no pagada, no vencida.</li>
                    <li><strong>VENCIDA</strong> — Pasó la fecha de vencimiento sin pago.</li>
                    <li><strong>PAGADA</strong> — Pago registrado por el total.</li>
                    <li><strong>ANULADA</strong> — Factura anulada manualmente (requiere justificación).</li>
                </ul>

                <h2>Anular una Factura</h2>
                <p>En el detalle de la factura, botón <strong>Anular</strong>. Se requiere observación. Solo facturas PENDIENTE o VENCIDA pueden anularse.</p>
                <div class="m-warn"><i class="fa fa-exclamation-triangle"></i> Una factura anulada no puede reactivarse. Si fue un error, genera una nueva factura para ese suscriptor y período.</div>
            </div>
        </div>

        {{-- ─── PAGOS Y COBROS ─── --}}
        <div class="manual-section" id="sec-pagos">
            <div class="m-card">
                <h1><i class="fa fa-money-bill-wave" style="color:#48bb78;margin-right:8px;"></i>Pagos y Otros Cobros</h1>
                <p class="subtitle">Registra pagos manuales de facturas y gestiona cobros adicionales por reconexión, inspección u otros conceptos.</p>

                <h2>Registrar un Pago</h2>
                <p>Desde el detalle de una factura → botón <strong>Registrar Pago</strong>. Campos requeridos:</p>
                <ul>
                    <li>Monto pagado.</li>
                    <li>Fecha de pago.</li>
                    <li>Referencia o recibo (opcional).</li>
                </ul>
                <p>Si el pago cubre el total, la factura pasa a estado <strong>PAGADA</strong> automáticamente.</p>

                <h2>Pasarela de Pago en Línea (Wompi)</h2>
                <p>Los clientes pueden pagar en línea desde <code>/pagar</code> sin necesidad de ingresar al sistema:</p>
                <ol>
                    <li>El cliente ingresa su número de suscriptor.</li>
                    <li>El sistema muestra las facturas pendientes.</li>
                    <li>El cliente selecciona la factura y paga con tarjeta de crédito/débito o PSE.</li>
                    <li>Wompi confirma el pago mediante webhook y la factura se actualiza automáticamente.</li>
                </ol>
                <div class="m-tip"><i class="fa fa-info-circle"></i> La URL pública de pago es: <strong>/pagar</strong> — no requiere autenticación.</div>

                <h2>Otros Cobros</h2>
                <p>Menú: <strong>Facturación → Otros Cobros</strong>. Permite crear cargos adicionales vinculados a un cliente (reconexión, multa, inspección, etc.).</p>
                <ul>
                    <li>El cobro se incluye automáticamente en la próxima factura del cliente.</li>
                    <li>Se puede anular si aún no fue facturado.</li>
                    <li>Cada cobro tiene monto total, cuota mensual y saldo pendiente.</li>
                </ul>
            </div>
        </div>

        {{-- ─── TARIFAS Y SUBSIDIOS ─── --}}
        <div class="manual-section" id="sec-tarifas">
            <div class="m-card">
                <h1><i class="fa fa-percentage" style="color:#4299e1;margin-right:8px;"></i>Tarifas y Subsidios</h1>
                <p class="subtitle">Configura los valores que el sistema usa para calcular el costo de cada factura.</p>

                <h2>Tarifas</h2>
                <p>Menú: <strong>Facturación → Tarifas</strong>. Cada tarifa de período define:</p>
                <ul>
                    <li><strong>Cargos Fijos</strong> por tipo de uso (residencial, comercial, industrial, oficial) para acueducto y alcantarillado.</li>
                    <li><strong>Rangos de consumo</strong> (básico, complementario, suntuario) con precio por m³ para cada combinación de tipo de uso y estrato.</li>
                </ul>
                <p>Para activar una tarifa, usa el botón <strong>Activar</strong>. Solo puede haber una tarifa activa a la vez.</p>

                <h2>Estratos y Subsidios</h2>
                <p>Menú: <strong>Facturación → Estratos / Subsidios</strong>. Configura el subsidio o contribución por estrato:</p>
                <table class="states-table">
                    <tr><th>Estratos</th><th>Tipo</th><th>Comportamiento</th></tr>
                    <tr><td>1, 2, 3</td><td>Subsidio</td><td>Descuento sobre el consumo básico facturado.</td></tr>
                    <tr><td>4</td><td>Neutro</td><td>Sin subsidio ni contribución.</td></tr>
                    <tr><td>5, 6, COM, IND</td><td>Contribución</td><td>Sobretasa sobre el consumo básico.</td></tr>
                </table>
                <p>Puedes configurar el subsidio de dos formas:</p>
                <ul>
                    <li><strong>Porcentaje (%)</strong> — Se calcula como un % del consumo básico facturado.</li>
                    <li><strong>Valor Fijo ($)</strong> — Monto fijo independiente del consumo. Tiene prioridad sobre el porcentaje.</li>
                </ul>
                <div class="m-ok"><i class="fa fa-check-circle"></i> <strong>Condición de consumo:</strong> El subsidio y la contribución <strong>solo se aplican si el cliente tuvo consumo real (m³ &gt; 0)</strong>. Si en un período el cliente solo tiene cargo fijo (consumo = 0), no se aplica ningún ajuste de subsidio.</div>
                <div class="m-tip"><i class="fa fa-info-circle"></i> Los cambios en las tablas de estratos/subsidios aplican únicamente a <strong>facturas generadas después del cambio</strong>. Las facturas ya emitidas no se recalculan.</div>
            </div>
        </div>

        {{-- ─── REPORTES Y EXPORTACIÓN ─── --}}
        <div class="manual-section" id="sec-reportes">
            <div class="m-card">
                <h1><i class="fa fa-chart-bar" style="color:#ed8936;margin-right:8px;"></i>Reportes y Exportación</h1>
                <p class="subtitle">El sistema ofrece múltiples formatos de exportación para integración con sistemas externos y archivo físico.</p>

                <h2>Factura en PDF</h2>
                <ul>
                    <li><strong>Individual</strong> — Botón <em>PDF</em> en el detalle de cualquier factura.</li>
                    <li><strong>Masivo por período</strong> — <em>Descarga masiva</em> en Facturación Masiva. Genera un ZIP con todos los PDFs del período.</li>
                    <li><strong>Seleccionadas</strong> — Marca las facturas que necesitas y usa <em>Exportar seleccionadas</em>.</li>
                </ul>

                <h2>Exportación Excel</h2>
                <ul>
                    <li>Reporte de lecturas y órdenes por ciclo: <strong>Asignación → Exportar Excel</strong>.</li>
                    <li>Reporte de facturas: <strong>Facturación → Exportar masivo</strong>.</li>
                    <li>Plantilla de carga de lecturas: <strong>Lecturas → Plantilla</strong>.</li>
                </ul>

                <h2>KPIs del Tablero</h2>
                <p>La pantalla de inicio (<strong>Tablero</strong>) muestra estadísticas en tiempo real por ciclo y período: órdenes ejecutadas, críticas, lecturistas activos, horario de inicio y fin.</p>

                <h2>Revisiones</h2>
                <p>Menú: <strong>Revisiones</strong>. Permite gestionar órdenes de revisión técnica (macromedidores, revisiones físicas). Incluye tablero de posicionamiento y críticas independientes del ciclo de facturación.</p>
            </div>
        </div>

        {{-- ─── ADMINISTRACIÓN ─── --}}
        <div class="manual-section" id="sec-admin">
            <div class="m-card">
                <h1><i class="fa fa-cogs" style="color:#718096;margin-right:8px;"></i>Administración del Sistema</h1>
                <p class="subtitle">Gestión de accesos, permisos y configuración general de la plataforma. Requiere rol de Superadmin.</p>

                <h2>Usuarios</h2>
                <p>Menú: <strong>Usuarios</strong>. Permite crear, editar y desactivar cuentas de usuario. Cada usuario tiene:</p>
                <ul>
                    <li>Nombre de usuario y contraseña.</li>
                    <li>Rol asignado (define los permisos).</li>
                    <li>Estado activo/inactivo.</li>
                </ul>

                <h2>Roles y Permisos</h2>
                <p>Menú: <strong>Admin → Roles</strong> y <strong>Admin → Permisos</strong>. El sistema usa un modelo de roles con menús y permisos asignados:</p>
                <ul>
                    <li><strong>Rol</strong> — Agrupa un conjunto de permisos.</li>
                    <li><strong>Permiso</strong> — Acción específica (ej: <em>crear factura</em>, <em>anular factura</em>).</li>
                    <li><strong>Menú-Rol</strong> — Define qué ítems del menú lateral ve cada rol.</li>
                    <li><strong>Permiso-Rol</strong> — Asigna permisos a roles.</li>
                </ul>

                <h2>Empresa</h2>
                <p>Menú: <strong>Facturación → Empresa</strong>. Configura los datos que aparecen en el encabezado de las facturas: nombre, NIT, dirección, teléfono, logo.</p>

                <h2>Macromedidores</h2>
                <p>Menú: <strong>Macromedidores</strong>. Registro y seguimiento de los macromedidores de la red. Permite asociar lecturas de macromedidor a sectores para control de pérdidas.</p>

                <h2>Marcas de Medidor</h2>
                <p>Menú: <strong>Marcas</strong>. Catálogo de marcas y modelos de medidor disponibles en el parque de medidores de la empresa.</p>

                <div class="m-warn"><i class="fa fa-exclamation-triangle"></i> Los cambios en roles y permisos aplican de forma inmediata. Verifica siempre que los usuarios afectados tengan los accesos correctos después de cualquier modificación.</div>
            </div>
        </div>

    </div>{{-- /manual-body --}}
</div>{{-- /manual-wrap --}}
@endsection

@section('scripts')
<script>
function mostrarSeccion(id) {
    // Ocultar todas
    document.querySelectorAll('.manual-section').forEach(function (el) {
        el.classList.remove('active');
    });
    document.querySelectorAll('.nav-link-manual').forEach(function (el) {
        el.classList.remove('active');
    });

    // Mostrar la seleccionada
    var sec = document.getElementById('sec-' + id);
    if (sec) sec.classList.add('active');

    var link = document.querySelector('[data-sec="' + id + '"]');
    if (link) link.classList.add('active');

    // Scroll al tope
    document.getElementById('manualBody').scrollTo({ top: 0, behavior: 'smooth' });
}

document.querySelectorAll('.nav-link-manual').forEach(function (el) {
    el.addEventListener('click', function (e) {
        e.preventDefault();
        mostrarSeccion(this.dataset.sec);
    });
});
</script>
@endsection
