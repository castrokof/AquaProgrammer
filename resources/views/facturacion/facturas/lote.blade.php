@extends("theme.$theme.layout")

@section('titulo', 'Facturación por Lote')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<style>
.modern-card { border-radius:20px; box-shadow:0 10px 40px rgba(0,0,0,.1); border:none; overflow:hidden; margin-bottom:20px; background:white; }
.modern-card .card-header { background:linear-gradient(135deg,#2e50e4 0%,#2b0c49 100%); border:none; padding:22px 28px; display:flex; justify-content:space-between; align-items:center; }
.modern-card .card-header h3 { color:white; font-weight:700; font-size:1.3rem; margin:0; }
.form-control-gen { border-radius:10px; border:2px solid #e2e8f0; padding:9px 13px; transition:all .3s; font-size:.88rem; }
.form-control-gen:focus { border-color:#667eea; box-shadow:0 0 0 3px rgba(102,126,234,.12); outline:none; }
label.lbl { font-weight:600; color:#4a5568; font-size:.8rem; text-transform:uppercase; letter-spacing:.4px; }

/* Tabs tipo */
.tipo-tabs { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px; }
.tipo-tab { padding:6px 18px; border-radius:20px; font-size:.8rem; font-weight:700; cursor:pointer; border:2px solid transparent; transition:all .2s; user-select:none; }
.tipo-tab.sin_medidor    { background:#fef3c7; color:#92400e; border-color:#f6e05e; }
.tipo-tab.alto           { background:#fee2e2; color:#991b1b; border-color:#fc8181; }
.tipo-tab.bajo           { background:#e0e7ff; color:#3730a3; border-color:#818cf8; }
.tipo-tab.causado        { background:#fef9c3; color:#713f12; border-color:#fde047; }
.tipo-tab.normal         { background:#d1fae5; color:#065f46; border-color:#6ee7b7; }
.tipo-tab.consumo_cero   { background:#f1f5f9; color:#334155; border-color:#94a3b8; }
.tipo-tab.promedio_medidor { background:#fdf4ff; color:#7e22ce; border-color:#c084fc; }
.tipo-tab.todos          { background:#e2e8f0; color:#2d3748; border-color:#cbd5e0; }
.tipo-tab.active         { box-shadow:0 4px 12px rgba(0,0,0,.15); transform:translateY(-1px); }
.tipo-tab .cnt           { background:rgba(0,0,0,.15); border-radius:10px; padding:0 7px; margin-left:5px; font-size:.75rem; }

/* Tabla */
#tablaLote { font-size:.82rem; }
#tablaLote thead th { background:#f3f4f6; padding:7px 10px; font-size:.75rem; font-weight:700; text-transform:uppercase; color:#374151; white-space:nowrap; }
#tablaLote tbody td { padding:5px 8px; vertical-align:middle; }
#tablaLote tbody tr:hover { background:#f7fafc; }
.inp-consumo, .inp-lect { width:78px; border:2px solid #e2e8f0; border-radius:8px; padding:4px 8px; font-size:.82rem; text-align:right; }
.inp-consumo:focus, .inp-lect:focus { border-color:#667eea; outline:none; }
.inp-obs-fila { width:160px; border:2px solid #e2e8f0; border-radius:8px; padding:3px 7px; font-size:.78rem; color:#374151; }
.inp-obs-fila:focus { border-color:#f59e0b; outline:none; background:#fffbeb; }
.inp-obs-fila.filled { border-color:#f59e0b; background:#fffbeb; }

/* Badge tipo */
.badge-tipo { display:inline-block; padding:2px 9px; border-radius:10px; font-size:.72rem; font-weight:700; white-space:nowrap; }
.badge-tipo.sin_medidor    { background:#fef3c7; color:#92400e; }
.badge-tipo.alto           { background:#fee2e2; color:#991b1b; }
.badge-tipo.bajo           { background:#e0e7ff; color:#3730a3; }
.badge-tipo.causado        { background:#fef9c3; color:#713f12; }
.badge-tipo.normal         { background:#d1fae5; color:#065f46; }
.badge-tipo.consumo_cero   { background:#fff7ed; color:#9a3412; }
.badge-tipo.promedio_medidor { background:#fff7ed; color:#9a3412; }

/* Barra de acciones flotante */
#barraAcciones { position:fixed; bottom:0; left:0; right:0; background:white; border-top:3px solid #667eea; padding:14px 30px; display:none; z-index:999; box-shadow:0 -4px 20px rgba(0,0,0,.12); }

.spinner-wrap { text-align:center; padding:40px; color:#a0aec0; }

/* DataTables overrides */
.dataTables_wrapper .dataTables_filter { display:none; } /* usamos nuestro buscador */
.dataTables_wrapper .dataTables_length select { border-radius:8px; border:2px solid #e2e8f0; padding:4px 8px; }
.dataTables_wrapper .dataTables_info { font-size:.8rem; color:#718096; }
.dataTables_wrapper .dataTables_paginate .paginate_button { border-radius:8px !important; font-size:.8rem; }
.dataTables_wrapper .dataTables_paginate .paginate_button.current { background:#667eea !important; color:white !important; border-color:#667eea !important; }

/* Foto btn */
.btn-foto { background:none; border:none; padding:2px 6px; color:#667eea; cursor:pointer; font-size:.9rem; }
.btn-foto:hover { color:#4338ca; }

/* Obs filter */
#filtroObs { min-width:200px; }
</style>
@endsection

@section('contenido')
<div class="container-fluid">

    <div class="modern-card">
        <div class="card-header">
            <h3><i class="fa fa-layer-group"></i> Facturación por Lote</h3>
            <div style="display:flex;gap:10px;">
                <a href="{{ route('facturas.generar') }}" class="btn btn-light btn-sm" style="border-radius:10px;font-weight:700;">
                    <i class="fa fa-user"></i> Individual
                </a>
                <a href="{{ route('facturas.index') }}" class="btn btn-light btn-sm" style="border-radius:10px;font-weight:700;">
                    <i class="fa fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    {{-- ── PASO 1: Período ────────────────────────────────────────────────── --}}
    <div style="background:white;border-radius:16px;padding:20px 24px;box-shadow:0 4px 15px rgba(0,0,0,.06);margin-bottom:18px;">
        <div class="row align-items-end">
            <div class="col-md-5">
                <label class="lbl"><i class="fa fa-calendar-alt"></i> Período de Facturación</label>
                <select class="form-control form-control-gen mt-1" id="selPeriodo">
                    <option value="">— Seleccione período —</option>
                    @foreach($periodos as $p)
                    <option value="{{ $p->id }}">{{ $p->nombre }} — {{ $p->estado }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary mt-3" id="btnCargar" style="border-radius:10px;font-weight:700;" disabled>
                    <i class="fa fa-search"></i> Cargar Suscriptores
                </button>
            </div>
            <div class="col-md-4 text-right">
                <div id="resumenCounts" style="display:none;font-size:.85rem;"></div>
            </div>
        </div>
    </div>

    {{-- ── Contenido cargado dinámicamente ───────────────────────────────── --}}
    <div id="contenidoLote" style="display:none;">

        {{-- Tabs por tipo --}}
        <div class="tipo-tabs" id="tipoTabs">
            <span class="tipo-tab todos active" data-tipo="todos" onclick="filtrarTipo('todos',this)">
                Todos <span class="cnt" id="cnt-todos">0</span>
            </span>
            <span class="tipo-tab sin_medidor" data-tipo="sin_medidor" onclick="filtrarTipo('sin_medidor',this)" style="display:none;">
                Sin Medidor <span class="cnt" id="cnt-sin_medidor">0</span>
            </span>
            <span class="tipo-tab alto" data-tipo="alto" onclick="filtrarTipo('alto',this)" style="display:none;">
                Altos <span class="cnt" id="cnt-alto">0</span>
            </span>
            <span class="tipo-tab bajo" data-tipo="bajo" onclick="filtrarTipo('bajo',this)" style="display:none;">
                Bajos <span class="cnt" id="cnt-bajo">0</span>
            </span>
            <span class="tipo-tab causado" data-tipo="causado" onclick="filtrarTipo('causado',this)" style="display:none;">
                Causados <span class="cnt" id="cnt-causado">0</span>
            </span>
            <span class="tipo-tab consumo_cero" data-tipo="consumo_cero" onclick="filtrarTipo('consumo_cero',this)" style="display:none;">
                <i class="fa fa-home"></i> Desocupados <span class="cnt" id="cnt-consumo_cero">0</span>
            </span>
            <span class="tipo-tab promedio_medidor" data-tipo="promedio_medidor" onclick="filtrarTipo('promedio_medidor',this)" style="display:none;">
                <i class="fa fa-tachometer-alt"></i> Medidor Parado <span class="cnt" id="cnt-promedio_medidor">0</span>
            </span>
            <span class="tipo-tab normal" data-tipo="normal" onclick="filtrarTipo('normal',this)" style="display:none;">
                Normales <span class="cnt" id="cnt-normal">0</span>
            </span>
        </div>

        <div style="background:white;border-radius:14px;padding:16px 20px;box-shadow:0 4px 15px rgba(0,0,0,.06);">

            {{-- Toolbar: buscador + filtro observación + botones selección --}}
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:10px;">
                <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                    <input type="text" id="buscarTabla" class="form-control form-control-gen" style="width:240px;"
                           placeholder="Buscar suscriptor, nombre, sector...">
                    <select id="filtroObs" class="form-control form-control-gen">
                        <option value="">— Todas las observaciones —</option>
                    </select>
                </div>
                <div style="display:flex;gap:8px;">
                    <button class="btn btn-sm btn-outline-secondary" onclick="selTodos(true)" style="border-radius:8px;font-size:.8rem;">
                        <i class="fa fa-check-square"></i> Seleccionar visibles
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="selTodos(false)" style="border-radius:8px;font-size:.8rem;">
                        <i class="fa fa-square"></i> Deseleccionar todos
                    </button>
                </div>
            </div>

            <div style="overflow-x:auto;">
                <table id="tablaLote" class="table table-sm table-bordered" style="width:100%;">
                    <thead>
                        <tr>
                            <th style="width:36px;"><input type="checkbox" id="chkAll"></th>
                            <th>Suscriptor</th>
                            <th>Nombre</th>
                            <th>Sector</th>
                            <th>Estrato</th>
                            <th>Promedio m³</th>
                            <th>Tipo</th>
                            <th>Obs. Campo</th>
                            <th>Lect. Ant.</th>
                            <th>Lect. Act.</th>
                            <th>Consumo m³</th>
                            <th>Obs. Analista</th>
                            <th style="width:40px;">Foto</th>
                            <th>_tipo</th>
                            <th>_obs</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyLote">
                        <tr><td colspan="15" class="spinner-wrap"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Cargando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ── Spinner de carga ────────────────────────────────────────────────── --}}
    <div id="spinnerCarga" style="display:none;text-align:center;padding:50px;background:white;border-radius:16px;box-shadow:0 4px 15px rgba(0,0,0,.06);">
        <i class="fa fa-spinner fa-spin fa-3x" style="color:#667eea;"></i>
        <p style="margin-top:14px;color:#718096;font-weight:600;">Cargando suscriptores...</p>
    </div>

    {{-- ── Barra de acciones flotante ──────────────────────────────────────── --}}
    <div id="barraAcciones">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;flex:1;">
                <div>
                    <span id="textoSeleccionados" style="font-weight:700;color:#2d3748;font-size:.95rem;"></span>
                    <span style="color:#718096;font-size:.82rem;margin-left:6px;">suscriptores seleccionados</span>
                </div>
                <div style="flex:1;min-width:240px;max-width:480px;">
                    <input type="text" id="obsLote" class="form-control form-control-gen"
                           placeholder="Observación / Motivo del ajuste (opcional)"
                           style="font-size:.83rem;border-radius:10px;">
                </div>
            </div>
            <div style="display:flex;gap:10px;align-items:center;">
                <span id="spinnerGen" style="display:none;"><i class="fa fa-spinner fa-spin" style="color:#667eea;font-size:1.2rem;"></i></span>
                <button class="btn btn-success" id="btnGenerarLote" style="border-radius:12px;font-weight:700;padding:10px 28px;">
                    <i class="fa fa-file-invoice-dollar"></i> Generar Facturas Seleccionadas
                </button>
            </div>
        </div>
    </div>

</div>

{{-- ── Modal Foto de Lectura ────────────────────────────────────────────── --}}
<div class="modal fade" id="modalFoto" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#2e50e4,#2b0c49);color:white;border:none;">
                <h5 class="modal-title"><i class="fa fa-camera"></i> Foto de Lectura</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:white;opacity:1;">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center" style="padding:20px;">
                <p id="fotoSuscriptor" style="font-weight:700;color:#2d3748;margin-bottom:14px;"></p>
                <div id="fotosContainer" style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script>
var CSRF          = $("meta[name='csrf-token']").attr("content");
var todosClientes = [];
var tipoActivo    = 'todos';
var obsActiva     = '';
var seleccionados = new Set(); // IDs de checkboxes seleccionados
var dt            = null;

// (filtros tipo y obs se aplican vía dt.column().search — ver filtrarTipo y #filtroObs)

// ── Habilitar botón al seleccionar período ────────────────────────────────
$('#selPeriodo').on('change', function () {
    $('#btnCargar').prop('disabled', !$(this).val());
});

// ── Cargar suscriptores ───────────────────────────────────────────────────
$('#btnCargar').on('click', function () {
    var periodoId = $('#selPeriodo').val();
    if (!periodoId) return;

    $('#contenidoLote').hide();
    $('#spinnerCarga').show();
    $('#barraAcciones').hide();
    seleccionados.clear();

    $.ajax({
        url: '{{ route("facturas.clientes-sin-factura") }}',
        method: 'POST',
        data: { periodo_lectura_id: periodoId, _token: CSRF },
        success: function (r) {
            $('#spinnerCarga').hide();
            if (!r.ok || r.clientes.length === 0) {
                Swal.fire('Sin pendientes', 'Todos los suscriptores activos ya tienen factura para este período.', 'info');
                return;
            }
            todosClientes = r.clientes;
            construirTabla();
            actualizarContadores();
            poblarFiltroObs();
            filtrarTipo('todos', document.querySelector('.tipo-tab.todos'));
            $('#contenidoLote').show();
            $('#resumenCounts').show();
        },
        error: function () {
            $('#spinnerCarga').hide();
            Swal.fire('Error', 'No se pudo cargar la lista de suscriptores.', 'error');
        }
    });
});

// ── Construir tabla con DataTables ────────────────────────────────────────
function construirTabla() {
    // Destruir instancia anterior si existe
    if (dt) { dt.destroy(); }
    seleccionados.clear();

    var labelMap = {
        sin_medidor:'Sin Medidor', alto:'Alto', bajo:'Bajo', causado:'Causado',
        normal:'Normal', consumo_cero:'Desocupado', promedio_medidor:'Medidor Parado'
    };

    var html = '';
    todosClientes.forEach(function (c) {
        var badge = '<span class="badge-tipo ' + c.tipo + '">' + (labelMap[c.tipo] || c.tipo) + '</span>';
        var sinMedidor = !c.tiene_medidor;

        // Fondo del consumo según critica del campo (lo que reportó el operario)
        var criticaUp = (c.critica || '').toUpperCase();
        var bgConsumo = '#f0fdf4'; // verde por defecto (normal)
        if (sinMedidor) {
            bgConsumo = '#fffbeb'; // amarillo — sin medidor
        } else if (criticaUp.indexOf('IGUAL') !== -1) {
            bgConsumo = '#fff7ed'; // naranja suave — lecturas iguales (desocupado / medidor parado)
        } else if (criticaUp.indexOf('ALTO') !== -1 || criticaUp.indexOf('ELEVADO') !== -1) {
            bgConsumo = '#fee2e2'; // rojo suave — consumo alto
        } else if (criticaUp.indexOf('BAJO') !== -1) {
            bgConsumo = '#eff6ff'; // azul suave — consumo bajo
        } else if (c.tipo === 'causado') {
            bgConsumo = '#fef9c3'; // amarillo pálido — causado sin clasificar
        }

        var inpConsumo = '<input class="inp-consumo" type="number" min="0" value="' + c.consumo_sugerido
            + '" data-id="' + c.id + '" name="consumo" style="background:' + bgConsumo + ';"'
            + ' title="Editable — valor sugerido: ' + c.consumo_sugerido + '">';

        // Lecturas: editables para todos los que tienen medidor
        var inpLectAnt = sinMedidor ? '<span style="color:#cbd5e0;">—</span>' :
            '<input class="inp-lect" type="number" min="0" value="' + (c.lect_anterior !== null ? c.lect_anterior : '')
            + '" data-id="' + c.id + '" name="lect_ant" oninput="calcularConsumo(this)" style="background:#f0fdf4;">';
        var inpLectAct = sinMedidor ? '<span style="color:#cbd5e0;">—</span>' :
            '<input class="inp-lect" type="number" min="0" value="' + (c.lect_actual !== null ? c.lect_actual : '')
            + '" data-id="' + c.id + '" name="lect_act" oninput="calcularConsumo(this)" style="background:#f0fdf4;">';

        // Observación de campo (del sistema de lectura) — sortable por texto
        var obsTexto = c.observacion_des || '';
        var obsLabel = obsTexto
            ? ('<small style="color:#475569;" title="Cód. ' + c.observacion_id + '">' + obsTexto + '</small>')
            : '<small style="color:#cbd5e0;">—</small>';

        // Foto
        var fotoBtn = '';
        if (c.foto1 || c.foto2) {
            fotoBtn = '<button class="btn-foto" onclick="verFotos(' + c.id + ')" title="Ver foto de lectura">'
                    + '<i class="fa fa-camera"></i></button>';
        }

        // Observación del analista (por fila, para trazabilidad)
        var inpObsFila = '<input class="inp-obs-fila" type="text" maxlength="300"'
            + ' data-id="' + c.id + '" name="obs_fila"'
            + ' placeholder="Motivo del ajuste..."'
            + ' oninput="this.classList.toggle(\'filled\', this.value.length>0)">';

        html += '<tr data-id="' + c.id + '" data-tipo="' + c.tipo
            + '" data-obs="' + (c.observacion_id || '') + '"'
            + ' data-q="' + (c.suscriptor + ' ' + c.nombre + ' ' + c.sector + ' ' + c.observacion_des).toLowerCase() + '">';
        html += '<td style="text-align:center;"><input type="checkbox" class="chk-fila" data-id="' + c.id + '"></td>';
        html += '<td style="font-weight:700;color:#2d3748;">' + c.suscriptor + '</td>';
        html += '<td>' + c.nombre + '</td>';
        html += '<td style="color:#718096;">' + (c.sector || '—') + '</td>';
        html += '<td>' + c.estrato + '</td>';
        html += '<td style="text-align:right;font-weight:600;">' + (c.promedio_consumo || 0) + '</td>';
        html += '<td>' + badge + '</td>';
        html += '<td data-order="' + obsTexto + '">' + obsLabel + '</td>';
        html += '<td>' + inpLectAnt + '</td>';
        html += '<td>' + inpLectAct + '</td>';
        html += '<td>' + inpConsumo + '</td>';
        html += '<td>' + inpObsFila + '</td>';
        html += '<td style="text-align:center;">' + fotoBtn + '</td>';
        html += '<td>' + c.tipo + '</td>';
        html += '<td>' + (c.observacion_id || '') + '</td>';
        html += '</tr>';
    });

    $('#tbodyLote').html(html);

    dt = $('#tablaLote').DataTable({
        paging:   true,
        pageLength: 50,
        ordering: true,
        searching: true,    // necesario para dt.column().search() — UI oculto vía CSS
        order:    [[1, 'asc']],
        lengthMenu: [[25, 50, 100, 200, -1], ['25', '50', '100', '200', 'Mostrar Todo']],
        language: {
            lengthMenu:    'Mostrar _MENU_ registros',
            info:          'Mostrando _START_ a _END_ de _TOTAL_ suscriptores',
            infoEmpty:     'Sin resultados',
            infoFiltered:  '(filtrado de _MAX_ total)',
            paginate:      { first:'«', last:'»', next:'›', previous:'‹' },
            zeroRecords:   'No hay suscriptores para los filtros seleccionados.'
        },
        columnDefs: [
            { orderable: false, targets: [0, 8, 9, 10, 11, 12] },
            { visible: false, targets: [13, 14] }
        ],
        drawCallback: function() {
            actualizarBarraAcciones();
        }
    });

    actualizarBarraAcciones();
}

// ── Poblar select de observaciones ───────────────────────────────────────
function poblarFiltroObs() {
    var obsMap = {};
    todosClientes.forEach(function(c) {
        if (c.observacion_id && c.observacion_des) {
            obsMap[c.observacion_id] = c.observacion_des;
        }
    });
    var $sel = $('#filtroObs');
    $sel.find('option:not(:first)').remove();
    Object.keys(obsMap).sort(function(a,b){ return a-b; }).forEach(function(id) {
        $sel.append('<option value="' + id + '">' + id + ' – ' + obsMap[id] + '</option>');
    });
}

// ── Auto-calcular consumo al ingresar lecturas ────────────────────────────
function calcularConsumo(el) {
    var $el = $(el);
    var id  = $el.data('id');
    var ant = parseInt($('input[data-id="'+id+'"][name="lect_ant"]').val()) || 0;
    var act = parseInt($('input[data-id="'+id+'"][name="lect_act"]').val()) || 0;
    if (act >= ant && act > 0) {
        $('input[data-id="'+id+'"][name="consumo"]').val(act - ant);
    }
}

// ── Filtrar por tipo — columna oculta 13 ─────────────────────────────────
function filtrarTipo(tipo, el) {
    tipoActivo = tipo;
    document.querySelectorAll('.tipo-tab').forEach(function(t){ t.classList.remove('active'); });
    if (el) el.classList.add('active');
    if (!dt) return;
    // Búsqueda exacta con regex: ^ y $ para evitar coincidencias parciales
    dt.column(13).search(tipo === 'todos' ? '' : '^' + tipo + '$', true, false).draw();
}

// ── Filtro por observación — columna oculta 14 ───────────────────────────
$('#filtroObs').on('change', function () {
    obsActiva = $(this).val();
    if (!dt) return;
    dt.column(14).search(obsActiva ? '^' + obsActiva + '$' : '', true, false).draw();
});

// ── Buscador de texto — búsqueda global de DataTables ────────────────────
$('#buscarTabla').on('input', function () {
    if (!dt) return;
    // dt.search() hace búsqueda en todas las columnas visibles (suscriptor, nombre, sector…)
    // Las columnas ocultas 13/14 no interfieren porque searching:false no aplica a ellas
    dt.search($(this).val()).draw();
});

// ── Checkboxes con paginación ─────────────────────────────────────────────
$('#chkAll').on('change', function () {
    var checked = $(this).is(':checked');
    // Solo afecta las filas VISIBLES en la página actual
    $('#tablaLote tbody tr:visible .chk-fila').prop('checked', checked).each(function() {
        var id = $(this).data('id');
        checked ? seleccionados.add(id) : seleccionados.delete(id);
    });
    actualizarBarraAcciones();
});

$(document).on('change', '.chk-fila', function () {
    var id = $(this).data('id');
    $(this).is(':checked') ? seleccionados.add(id) : seleccionados.delete(id);
    actualizarBarraAcciones();
});

function selTodos(estado) {
    if (!dt) return;
    // Aplicar a TODAS las filas filtradas (no solo la página actual)
    dt.rows({ search: 'applied' }).nodes().each(function(row) {
        var $chk = $(row).find('.chk-fila');
        $chk.prop('checked', estado);
        var id = $chk.data('id');
        estado ? seleccionados.add(id) : seleccionados.delete(id);
    });
    actualizarBarraAcciones();
}

function actualizarBarraAcciones() {
    var n = seleccionados.size;
    if (n > 0) {
        $('#textoSeleccionados').text(n);
        $('#barraAcciones').show();
    } else {
        $('#barraAcciones').hide();
    }
}

// ── Actualizar contadores de tabs ─────────────────────────────────────────
function actualizarContadores() {
    var tiposConocidos = ['sin_medidor','alto','bajo','causado','consumo_cero','promedio_medidor','normal'];
    var counts = { todos: todosClientes.length };
    tiposConocidos.forEach(function(t){ counts[t] = 0; });
    todosClientes.forEach(function(c){ if (counts[c.tipo] !== undefined) counts[c.tipo]++; });

    var resumen = [];
    tiposConocidos.forEach(function(tipo) {
        $('#cnt-'+tipo).text(counts[tipo]);
        var tab = $('[data-tipo="'+tipo+'"]');
        if (counts[tipo] > 0) { tab.show(); resumen.push('<b>'+counts[tipo]+'</b> '+tipo.replace(/_/g,' ')); }
        else { tab.hide(); }
    });
    $('#cnt-todos').text(counts.todos);
    $('#resumenCounts').html('<span style="color:#718096;font-size:.82rem;">Total pendientes: <b>'+counts.todos+'</b> | '+resumen.join(' · ')+'</span>');
}

// ── Ver fotos de lectura ──────────────────────────────────────────────────
function verFotos(clienteId) {
    var c = todosClientes.find(function(x){ return x.id == clienteId; });
    if (!c) return;

    $('#fotoSuscriptor').text('Suscriptor: ' + c.suscriptor + ' — ' + c.nombre);

    var BASE_URL = '{{ rtrim(url("/"), "/") }}';
    var html = '';
    [c.foto1, c.foto2].forEach(function(foto, i) {
        if (!foto) return;
        var src = foto.startsWith('http') ? foto : BASE_URL + '/' + foto.replace(/^\//, '');
        html += '<div style="flex:1;min-width:200px;max-width:420px;">'
              + '<p style="font-size:.75rem;color:#718096;margin-bottom:6px;">Foto ' + (i+1) + '</p>'
              + '<a href="' + src + '" target="_blank">'
              + '<img src="' + src + '" style="max-width:100%;border-radius:10px;box-shadow:0 4px 15px rgba(0,0,0,.15);" '
              + 'onerror="this.parentElement.innerHTML=\'<span style=\\\"color:#e11d48\\\">No se pudo cargar la foto</span>\'">'
              + '</a></div>';
    });

    if (!html) html = '<p style="color:#718096;">No hay fotos disponibles.</p>';
    $('#fotosContainer').html(html);
    $('#modalFoto').modal('show');
}

// ── Generar facturas del lote ─────────────────────────────────────────────
$('#btnGenerarLote').on('click', function () {
    if (seleccionados.size === 0) {
        Swal.fire('Sin selección', 'Seleccione al menos un suscriptor.', 'warning');
        return;
    }

    var rows = [];
    seleccionados.forEach(function(id) {
        var consumoVal = parseInt($('input[data-id="'+id+'"][name="consumo"]').val());
        if (isNaN(consumoVal)) consumoVal = 0;
        var obsFila = $('input[data-id="'+id+'"][name="obs_fila"]').val().trim() || null;
        rows.push({
            cliente_id:       id,
            consumo_m3:       consumoVal,
            lectura_anterior: $('input[data-id="'+id+'"][name="lect_ant"]').length
                                ? ($('input[data-id="'+id+'"][name="lect_ant"]').val() || null)
                                : null,
            lectura_actual:   $('input[data-id="'+id+'"][name="lect_act"]').length
                                ? ($('input[data-id="'+id+'"][name="lect_act"]').val() || null)
                                : null,
            observacion:      obsFila,
        });
    });

    // Validar consumo negativo (0 es válido)
    var invalidos = rows.filter(function(r){ return r.consumo_m3 < 0; });
    if (invalidos.length > 0) {
        Swal.fire('Consumo inválido', invalidos.length + ' suscriptor(es) con consumo negativo.', 'warning');
        return;
    }

    Swal.fire({
        title: '¿Generar ' + rows.length + ' facturas?',
        html:  'Se generarán las facturas para los suscriptores seleccionados.<br>Esta acción no se puede deshacer.',
        icon: 'question', showCancelButton: true,
        confirmButtonText: 'Generar', cancelButtonText: 'Cancelar',
        confirmButtonColor: '#2e50e4'
    }).then(function(res) {
        if (!res.value) return;
        $('#spinnerGen').show();
        $('#btnGenerarLote').prop('disabled', true);

        $.ajax({
            url: '{{ route("facturas.store-lote") }}',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                _token: CSRF,
                periodo_lectura_id: $('#selPeriodo').val(),
                observaciones: $('#obsLote').val().trim() || null,
                rows: rows
            }),
            success: function(r) {
                $('#spinnerGen').hide();
                $('#btnGenerarLote').prop('disabled', false);
                if (r.ok) {
                    Swal.fire({
                        title: '¡Listo!', icon: 'success',
                        html: r.mensaje + '<br><small>Se recargará la lista automáticamente.</small>'
                    }).then(function() { $('#btnCargar').click(); });
                } else {
                    Swal.fire('Error', r.mensaje, 'error');
                }
            },
            error: function(xhr) {
                $('#spinnerGen').hide();
                $('#btnGenerarLote').prop('disabled', false);
                Swal.fire('Error', xhr.responseJSON?.mensaje || 'Error al generar facturas.', 'error');
            }
        });
    });
});
</script>
@endsection
