@extends("theme.$theme.layout")

@section('titulo', 'Facturación por Lote')

@section('styles')
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
.tipo-tab.sin_medidor  { background:#fef3c7; color:#92400e; border-color:#f6e05e; }
.tipo-tab.alto         { background:#fee2e2; color:#991b1b; border-color:#fc8181; }
.tipo-tab.bajo         { background:#e0e7ff; color:#3730a3; border-color:#818cf8; }
.tipo-tab.causado      { background:#fef9c3; color:#713f12; border-color:#fde047; }
.tipo-tab.normal       { background:#d1fae5; color:#065f46; border-color:#6ee7b7; }
.tipo-tab.todos        { background:#e2e8f0; color:#2d3748; border-color:#cbd5e0; }
.tipo-tab.active       { box-shadow:0 4px 12px rgba(0,0,0,.15); transform:translateY(-1px); }
.tipo-tab .cnt         { background:rgba(0,0,0,.15); border-radius:10px; padding:0 7px; margin-left:5px; font-size:.75rem; }

/* Tabla lote */
#tablaLote { font-size:.82rem; }
#tablaLote thead th { background:#f3f4f6; padding:7px 10px; font-size:.75rem; font-weight:700; text-transform:uppercase; color:#374151; white-space:nowrap; }
#tablaLote tbody td { padding:5px 8px; vertical-align:middle; }
#tablaLote tbody tr:hover { background:#f7fafc; }
.inp-consumo, .inp-lect { width:80px; border:2px solid #e2e8f0; border-radius:8px; padding:4px 8px; font-size:.82rem; text-align:right; }
.inp-consumo:focus, .inp-lect:focus { border-color:#667eea; outline:none; }

/* Barra de acciones flotante */
#barraAcciones { position:fixed; bottom:0; left:0; right:0; background:white; border-top:3px solid #667eea; padding:14px 30px; display:none; z-index:999; box-shadow:0 -4px 20px rgba(0,0,0,.12); }

/* Badge tipo */
.badge-tipo { display:inline-block; padding:2px 9px; border-radius:10px; font-size:.72rem; font-weight:700; }
.badge-tipo.sin_medidor { background:#fef3c7; color:#92400e; }
.badge-tipo.alto        { background:#fee2e2; color:#991b1b; }
.badge-tipo.bajo        { background:#e0e7ff; color:#3730a3; }
.badge-tipo.causado     { background:#fef9c3; color:#713f12; }
.badge-tipo.normal      { background:#d1fae5; color:#065f46; }

.spinner-wrap { text-align:center; padding:40px; color:#a0aec0; }
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
            <span class="tipo-tab normal" data-tipo="normal" onclick="filtrarTipo('normal',this)" style="display:none;">
                Normales <span class="cnt" id="cnt-normal">0</span>
            </span>
        </div>

        {{-- Buscador en tabla --}}
        <div style="background:white;border-radius:14px;padding:16px 20px;box-shadow:0 4px 15px rgba(0,0,0,.06);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:10px;">
                <div>
                    <input type="text" id="buscarTabla" class="form-control form-control-gen" style="width:260px;"
                           placeholder="Buscar suscriptor, nombre, sector...">
                </div>
                <div style="display:flex;gap:8px;">
                    <button class="btn btn-sm btn-outline-secondary" onclick="selTodos(true)" style="border-radius:8px;font-size:.8rem;">
                        Seleccionar visibles
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="selTodos(false)" style="border-radius:8px;font-size:.8rem;">
                        Deseleccionar todos
                    </button>
                </div>
            </div>

            <div style="overflow-x:auto;">
                <table id="tablaLote" style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th style="width:36px;"><input type="checkbox" id="chkAll"></th>
                            <th>Suscriptor</th>
                            <th>Nombre</th>
                            <th>Sector</th>
                            <th>Estrato</th>
                            <th>Servicios</th>
                            <th>Promedio m³</th>
                            <th>Tipo</th>
                            <th>Lect. Ant.</th>
                            <th>Lect. Act.</th>
                            <th>Consumo m³</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyLote">
                        <tr><td colspan="11" class="spinner-wrap"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Cargando...</td></tr>
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
@endsection

@section('scripts')
<script>
var CSRF        = $("meta[name='csrf-token']").attr("content");
var todosClientes = [];      // array completo
var tipoActivo  = 'todos';

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
            construirTabla(todosClientes);
            actualizarContadores();
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

// ── Construir filas de la tabla ───────────────────────────────────────────
function construirTabla(clientes) {
    var html = '';
    if (clientes.length === 0) {
        html = '<tr><td colspan="11" style="text-align:center;padding:30px;color:#a0aec0;">No hay suscriptores en esta categoría.</td></tr>';
        $('#tbodyLote').html(html);
        return;
    }
    clientes.forEach(function (c) {
        var badgeMap = { sin_medidor:'sin_medidor', alto:'alto', bajo:'bajo', causado:'causado', normal:'normal' };
        var labelMap = { sin_medidor:'Sin Medidor', alto:'Alto', bajo:'Bajo', causado:'Causado', normal:'Normal' };
        var badge = '<span class="badge-tipo ' + (badgeMap[c.tipo]||'normal') + '">' + (labelMap[c.tipo]||c.tipo) + '</span>';

        var sinMedidor = !c.tiene_medidor;
        var lectAnterior = c.lect_anterior !== null ? c.lect_anterior : '';
        var lectActual   = c.lect_actual   !== null ? c.lect_actual   : '';
        var consumo      = c.consumo_sugerido > 0 ? c.consumo_sugerido : '';

        // Sin medidor: consumo EDITABLE (promedio como sugerencia); con medidor: desde lectura
        var defaultConsumo = sinMedidor ? Math.round(c.promedio_consumo || 0) : (consumo !== '' ? consumo : 0);
        var inpConsumo = '<input class="inp-consumo" type="number" min="0" value="' + defaultConsumo + '" data-id="' + c.id + '" name="consumo"'
            + (sinMedidor ? ' title="Sin medidor: puede modificar el consumo a facturar" style="background:#fffbeb;"' : '') + '>';

        var inpLectAnt = sinMedidor ? '—' :
            '<input class="inp-lect" type="number" min="0" value="' + lectAnterior + '" data-id="' + c.id + '" name="lect_ant" oninput="calcularConsumo(this)">';
        var inpLectAct = sinMedidor ? '—' :
            '<input class="inp-lect" type="number" min="0" value="' + lectActual + '" data-id="' + c.id + '" name="lect_act" oninput="calcularConsumo(this)">';

        html += '<tr data-id="' + c.id + '" data-tipo="' + c.tipo + '" data-q="' + (c.suscriptor+' '+c.nombre+' '+c.sector).toLowerCase() + '">';
        html += '<td><input type="checkbox" class="chk-fila" data-id="' + c.id + '"></td>';
        html += '<td style="font-weight:700;color:#2d3748;">' + c.suscriptor + '</td>';
        html += '<td>' + c.nombre + '</td>';
        html += '<td style="color:#718096;">' + (c.sector||'—') + '</td>';
        html += '<td>' + c.estrato + '</td>';
        html += '<td>' + (c.servicios||'—') + '</td>';
        html += '<td style="text-align:right;font-weight:600;">' + (c.promedio_consumo||0).toFixed(1) + '</td>';
        html += '<td>' + badge + '</td>';
        html += '<td>' + inpLectAnt + '</td>';
        html += '<td>' + inpLectAct + '</td>';
        html += '<td>' + inpConsumo + '</td>';
        html += '</tr>';
    });
    $('#tbodyLote').html(html);
    actualizarBarraAcciones();
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

// ── Filtrar por tipo ──────────────────────────────────────────────────────
function filtrarTipo(tipo, el) {
    tipoActivo = tipo;
    document.querySelectorAll('.tipo-tab').forEach(function(t){ t.classList.remove('active'); });
    el.classList.add('active');

    $('#tbodyLote tr').each(function () {
        var $tr = $(this);
        if (!$tr.data('tipo')) { $tr.show(); return; }
        if (tipo === 'todos') {
            $tr.show();
        } else {
            $tr.data('tipo') === tipo ? $tr.show() : $tr.hide();
        }
    });
    aplicarBusqueda();
}

// ── Buscador inline ───────────────────────────────────────────────────────
$('#buscarTabla').on('input', aplicarBusqueda);
function aplicarBusqueda() {
    var q = $('#buscarTabla').val().toLowerCase();
    $('#tbodyLote tr').each(function () {
        var $tr = $(this);
        if (!$tr.data('tipo')) return;
        if (!$tr.is(':visible') && tipoActivo !== 'todos') return; // respeta filtro tipo
        if (!q) { if (tipoActivo === 'todos' || $tr.data('tipo') === tipoActivo) $tr.show(); return; }
        var match = ($tr.data('q') || '').indexOf(q) !== -1;
        if (tipoActivo !== 'todos' && $tr.data('tipo') !== tipoActivo) return;
        match ? $tr.show() : $tr.hide();
    });
}

// ── Checkboxes ────────────────────────────────────────────────────────────
$('#chkAll').on('change', function () {
    var checked = $(this).is(':checked');
    $('#tbodyLote tr:visible .chk-fila').prop('checked', checked);
    actualizarBarraAcciones();
});

$(document).on('change', '.chk-fila', function () {
    actualizarBarraAcciones();
});

function selTodos(estado) {
    $('#tbodyLote tr:visible .chk-fila').prop('checked', estado);
    actualizarBarraAcciones();
}

function actualizarBarraAcciones() {
    var n = $('.chk-fila:checked').length;
    if (n > 0) {
        $('#textoSeleccionados').text(n);
        $('#barraAcciones').show();
    } else {
        $('#barraAcciones').hide();
    }
}

// ── Actualizar contadores de tabs ─────────────────────────────────────────
function actualizarContadores() {
    var counts = { todos: todosClientes.length, sin_medidor:0, alto:0, bajo:0, causado:0, normal:0 };
    todosClientes.forEach(function(c){ if (counts[c.tipo] !== undefined) counts[c.tipo]++; });

    var resumen = [];
    ['sin_medidor','alto','bajo','causado','normal'].forEach(function(tipo) {
        $('#cnt-'+tipo).text(counts[tipo]);
        var tab = $('[data-tipo="'+tipo+'"]');
        if (counts[tipo] > 0) { tab.show(); resumen.push('<b>'+counts[tipo]+'</b> '+tipo.replace('_',' ')); }
        else { tab.hide(); }
    });
    $('#cnt-todos').text(counts.todos);
    $('#resumenCounts').html('<span style="color:#718096;font-size:.82rem;">Total pendientes: <b>'+counts.todos+'</b> | '+resumen.join(' · ')+'</span>');
}

// ── Generar facturas del lote ─────────────────────────────────────────────
$('#btnGenerarLote').on('click', function () {
    var seleccionados = [];
    $('.chk-fila:checked').each(function () {
        var id = $(this).data('id');
        seleccionados.push({
            cliente_id:      id,
            consumo_m3:      parseInt($('input[data-id="'+id+'"][name="consumo"]').val()) || 0,
            lectura_anterior: $('input[data-id="'+id+'"][name="lect_ant"]').length
                                ? ($('input[data-id="'+id+'"][name="lect_ant"]').val() || null)
                                : null,
            lectura_actual:  $('input[data-id="'+id+'"][name="lect_act"]').length
                                ? ($('input[data-id="'+id+'"][name="lect_act"]').val() || null)
                                : null,
        });
    });

    if (seleccionados.length === 0) { Swal.fire('Sin selección', 'Seleccione al menos un suscriptor.', 'warning'); return; }

    // Validar que ninguno tenga consumo negativo (0 es válido: predio desocupado → solo básico)
    var sinConsumo = seleccionados.filter(function(r){ return r.consumo_m3 === null || r.consumo_m3 === undefined || r.consumo_m3 < 0; });
    if (sinConsumo.length > 0) {
        Swal.fire('Consumo inválido', 'Hay ' + sinConsumo.length + ' suscriptor(es) con consumo negativo.', 'warning');
        return;
    }

    Swal.fire({
        title: '¿Generar ' + seleccionados.length + ' facturas?',
        html: 'Se generarán las facturas para los suscriptores seleccionados.<br>Esta acción no se puede deshacer.',
        icon: 'question', showCancelButton: true,
        confirmButtonText: 'Generar', cancelButtonText: 'Cancelar',
        confirmButtonColor: '#2e50e4'
    }).then(function(res){
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
                rows: seleccionados
            }),
            success: function(r){
                $('#spinnerGen').hide();
                $('#btnGenerarLote').prop('disabled', false);
                if (r.ok) {
                    Swal.fire({
                        title: '¡Listo!', icon: 'success',
                        html: r.mensaje + '<br><small>Se recargará la lista automáticamente.</small>'
                    }).then(function(){
                        $('#btnCargar').click(); // recargar
                    });
                } else {
                    Swal.fire('Error', r.mensaje, 'error');
                }
            },
            error: function(xhr){
                $('#spinnerGen').hide();
                $('#btnGenerarLote').prop('disabled', false);
                Swal.fire('Error', xhr.responseJSON?.mensaje || 'Error al generar facturas.', 'error');
            }
        });
    });
});
</script>
@endsection
