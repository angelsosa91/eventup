@extends('layouts.app')

@section('title', 'Detalle del Evento')
@section('page-title', 'Evento: ' . $event->name)

@section('content')
    <div class="container-fluid">
        <div class="d-flex align-items-center mb-3 gap-3">
            <a href="{{ route('events.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
            <h4 class="mb-0">{{ $event->name }}</h4>
            <span
                class="badge bg-{{ $event->status === 'confirmed' ? 'success' : ($event->status === 'cancelled' ? 'danger' : 'secondary') }}">
                {{ $event->status_label }}
            </span>
        </div>

        <div class="easyui-tabs" style="width:100%;height:700px;">
            <!-- PESTAÑA: GENERAL -->
            <div title="General" style="padding:20px;">
                <div class="row">
                    <div class="col-md-4">
                        <p><strong>Fecha del Evento:</strong> {{ $event->event_date->format('d/m/Y') }}</p>
                        <p><strong>Presupuesto Estimado:</strong> {{ number_format($event->estimated_budget, 0, ',', '.') }}
                            Gs.</p>
                    </div>
                    <div class="col-md-8">
                        <p><strong>Notas:</strong></p>
                        <div class="p-3 bg-light border rounded">
                            {{ $event->notes ?? 'Sin notas.' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- PESTAÑA: ITEMS -->
            <div title="Items" style="padding:10px;">
                <div id="tb-items" class="p-2 border-bottom mb-2">
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add"
                        onclick="newItemManual()">Nuevo Item</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add"
                        onclick="openCatalogDialog()">Agregar del Catálogo</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit"
                        onclick="editItem()">Editar</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove"
                        onclick="removeItem()">Eliminar</a>

                    <span class="ms-4 fw-bold text-primary">
                        Total Items: <span
                            id="budget-total">{{ number_format($event->items->sum('total'), 0, ',', '.') }}</span> Gs.
                    </span>
                </div>

                <table id="dg-items" class="easyui-datagrid" style="width:100%;height:580px" data-options="
                                                                           url: '{{ route('events.items.data', $event->id) }}',
                                                                           method: 'get',
                                                                           singleSelect:true,
                                                                           fitColumns:true,
                                                                           rownumbers:true,
                                                                           toolbar:'#tb-items'
                                                                       ">
                    <thead>
                        <tr>
                            <th data-options="field:'id',hidden:true">ID</th>
                            <th data-options="field:'description',width:400">Nombre del Item</th>
                            <th
                                data-options="field:'amount',width:150,align:'right',styler:function(){return 'font-weight:bold;'}">
                                Monto (Gs.)</th>
                            <th data-options="field:'count_guests',width:120,align:'center',formatter:countGuestsFormatter">
                                Cuenta Inv.</th>
                            <th data-options="field:'notes',width:250">Notas</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <!-- PESTAÑA: MESAS -->
            <div title="Mesas" style="padding:10px;">
                <div class="row h-100">
                    <!-- Columna Izquierda: Familias Sin Asignar -->
                    <div class="col-md-4 h-100">
                        <div class="card h-100">
                            <div class="card-header bg-warning text-dark">
                                <strong>Familias / Presupuestos por Asignar</strong>
                            </div>
                            <div class="card-body p-0">
                                <table id="dg-unassigned" class="easyui-datagrid" style="width:100%;height:530px"
                                    data-options="
                                                                                    url: '{{ route('events.budget.unassigned', $event->id) }}',
                                                                                    method: 'get',
                                                                                    singleSelect:true,
                                                                                    fitColumns:true,
                                                                                    rownumbers:true,
                                                                                    toolbar: '#tb-unassigned'
                                                                                ">
                                    <thead>
                                        <tr>
                                            <th data-options="field:'family_name',width:180">Familia</th>
                                            <th data-options="field:'guests_count',width:80,align:'center'">Inv.</th>
                                        </tr>
                                    </thead>
                                </table>
                                <div id="tb-unassigned" class="p-1">
                                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok"
                                        onclick="openAssignDialog()">Asignar a Mesa</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna Derecha: Mesas -->
                    <div class="col-md-8 h-100">
                        <div class="card h-100">
                            <div class="card-header bg-light">
                                <strong>Distribución de Mesas</strong>
                            </div>
                            <div class="card-body p-0">
                                <div id="tb-tables" class="p-2 border-bottom mb-2">
                                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add"
                                        onclick="addTable()">Agregar Mesa</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit"
                                        onclick="editTable()">Editar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove"
                                        onclick="removeTable()">Eliminar</a>
                                    <a href="{{ route('events.tables.report', $event->id) }}" class="easyui-linkbutton"
                                        iconCls="icon-print" target="_blank">Imprimir Distribución</a>
                                </div>
                                <table id="dg-tables" class="easyui-datagrid" style="width:100%;height:530px" data-options="
                                                                                           url: '{{ route('events.tables.grid.data', $event->id) }}',
                                                                                           method: 'get',
                                                                                           singleSelect:true,
                                                                                           fitColumns:true,
                                                                                           rownumbers:true,
                                                                                           toolbar:'#tb-tables',
                                                                                           view: detailview,
                                                                                           detailFormatter: detailFormatter
                                                                                       ">
                                    <thead>
                                        <tr>
                                            <th data-options="field:'id',hidden:true">ID</th>
                                            <th data-options="field:'name',width:150">Nombre de Mesa</th>
                                            <th
                                                data-options="field:'assigned_families',width:200,formatter:familiesFormatter">
                                                Familia(s)</th>
                                            <th data-options="field:'capacity',width:80,align:'center'">Cap.</th>
                                            <th
                                                data-options="field:'color',width:80,align:'center',formatter:colorFormatter">
                                                Color</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Diálogo: Nuevo/Editar Item Manual -->
    <div id="dlg-item-manual" class="easyui-dialog" style="width:450px"
        data-options="closed:true,modal:true,title:'Información del Item',buttons:'#item-manual-buttons'">
        <form id="fm-item-manual" method="post" novalidate style="padding:20px">
            <div class="mb-3">
                <label class="form-label">Nombre del Item:</label>
                <input class="easyui-textbox" name="description" id="manual-description" style="width:100%"
                    data-options="required:true,prompt:'Ej: Alquiler de Luces'">
            </div>
            <div class="mb-3">
                <label class="form-label">Monto (Gs.):</label>
                <input class="easyui-numberbox" name="estimated_unit_price" id="manual-price" style="width:100%"
                    data-options="required:true,min:0,groupSeparator:'.'">
            </div>
            <div class="mb-3">
                <label class="form-label">Notas:</label>
                <input class="easyui-textbox" name="notes" style="width:100%;height:60px" data-options="multiline:true">
            </div>
            <div class="mb-3">
                <input class="easyui-checkbox" name="count_guests" id="manual-count-guests" value="1" label="Cta Invitado">
            </div>
        </form>
    </div>
    <div id="item-manual-buttons">
        <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveItemManual()"
            style="width:90px">Guardar</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel"
            onclick="javascript:$('#dlg-item-manual').dialog('close')" style="width:90px">Cancelar</a>
    </div>

    <!-- Diálogo: Catálogo (Opcional) -->
    <div id="dlg-catalog" class="easyui-dialog" style="width:700px;height:500px"
        data-options="closed:true,modal:true,title:'Seleccionar del Catálogo',buttons:'#catalog-buttons'">
        <table id="dg-catalog" class="easyui-datagrid" style="width:100%;height:380px"
            data-options="url:'{{ route('services.data') }}',method:'get',pagination:true,pageSize:10">
            <thead>
                <tr>
                    <th data-options="field:'ck',checkbox:true"></th>
                    <th data-options="field:'name',width:300">Servicio/Producto</th>
                    <th data-options="field:'sale_price',width:150,align:'right'">Precio Ref.</th>
                </tr>
            </thead>
        </table>
    </div>
    <div id="catalog-buttons">
        <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="addSelectedItems()"
            style="width:120px">Agregar</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel"
            onclick="javascript:$('#dlg-catalog').dialog('close')" style="width:90px">Cerrar</a>
    </div>

    <!-- Diálogo: Asignar Mesa -->
    <div id="dlg-assign" class="easyui-dialog" style="width:400px"
        data-options="closed:true,modal:true,title:'Asignar Familia a Mesa',buttons:'#assign-buttons'">
        <form id="fm-assign" method="post" novalidate style="padding:20px">
            <div class="mb-3">
                <p>Asignando: <strong id="assign-family-name"></strong></p>
                <input type="hidden" name="budget_id" id="assign-budget-id">
            </div>
            <div class="mb-3">
                <label class="form-label">Seleccione Mesa:</label>
                <input class="easyui-combobox" name="table_id" style="width:100%" data-options="
                                                                    url:'{{ route('events.tables.data', $event->id) }}',
                                                                    method:'get',
                                                                    valueField:'id',
                                                                    textField:'text',
                                                                    required:true,
                                                                    prompt:'Elija una mesa...'
                                                                ">
            </div>
        </form>
    </div>
    <div id="assign-buttons">
        <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveAssignment()"
            style="width:90px">Asignar</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel"
            onclick="javascript:$('#dlg-assign').dialog('close')" style="width:90px">Cancelar</a>
    </div>

    <!-- Diálogo: Nueva/Editar Mesa -->
    <div id="dlg-table" class="easyui-dialog" style="width:400px"
        data-options="closed:true,modal:true,title:'Mesa',buttons:'#table-buttons'">
        <form id="fm-table" method="post" novalidate style="padding:20px">
            <div class="mb-3">
                <label class="form-label">Nombre de la Mesa:</label>
                <input class="easyui-textbox" name="name" style="width:100%" data-options="required:true">
            </div>
        </form>
    </div>
    <div id="table-buttons">
        <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveTable()"
            style="width:90px">Guardar</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel"
            onclick="javascript:$('#dlg-table').dialog('close')" style="width:90px">Cancelar</a>
    </div>

    @push('scripts')
        <script>
            // ... (previous variables) ...

            var current_table_id = null;

            function addTable() {
                current_table_id = null;
                $('#dlg-table').dialog('open').dialog('setTitle', 'Nueva Mesa');
                $('#fm-table').form('clear');
            }

            function editTable() {
                var row = $('#dg-tables').datagrid('getSelected');
                if (!row) {
                    $.messager.alert('Aviso', 'Seleccione una mesa', 'warning');
                    return;
                }
                current_table_id = row.id;
                $('#dlg-table').dialog('open').dialog('setTitle', 'Editar Mesa');
                $('#fm-table').form('clear');
                $('#fm-table').form('load', row);
            }

            function saveTable() {
                var url = current_table_id
                    ? '{{ url('events/tables') }}/' + current_table_id
                    : '{{ route('events.tables.store', $event->id) }}';

                $('#fm-table').form('submit', {
                    url: url,
                    onSubmit: function (param) {
                        param._token = '{{ csrf_token() }}';
                        if (current_table_id) param._method = 'PUT';
                        return $(this).form('validate');
                    },
                    success: function (result) {
                        var res = JSON.parse(result);
                        if (res.success) {
                            $('#dlg-table').dialog('close');
                            $('#dg-tables').datagrid('reload');
                        } else {
                            $.messager.alert('Error', 'Error al guardar mesa', 'error');
                        }
                    }
                });
            }

            function removeTable() {
                var row = $('#dg-tables').datagrid('getSelected');
                if (!row) {
                    $.messager.alert('Aviso', 'Seleccione una mesa', 'warning');
                    return;
                }
                $.messager.confirm('Confirmar', '¿Eliminar esta mesa?', function (r) {
                    if (r) {
                        $.ajax({
                            url: '{{ url('events/tables') }}/' + row.id,
                            type: 'DELETE',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function (res) {
                                if (res.success) {
                                    $('#dg-tables').datagrid('reload');
                                    $('#dg-unassigned').datagrid('reload');
                                }
                            }
                        });
                    }
                });
            }
            var current_item_id = null;

            function formatMoney(amount) {
                return new Intl.NumberFormat('es-PY').format(amount);
            }

            function newItemManual() {
                current_item_id = null;
                $('#dlg-item-manual').dialog('open').dialog('setTitle', 'Nuevo Item');
                $('#fm-item-manual').form('clear');
                $('#manual-count-guests').checkbox('uncheck');
            }

            function editItem() {
                var row = $('#dg-items').datagrid('getSelected');
                if (!row) {
                    $.messager.alert('Aviso', 'Seleccione un item', 'warning');
                    return;
                }
                current_item_id = row.id;
                $('#dlg-item-manual').dialog('open').dialog('setTitle', 'Editar Item');
                $('#fm-item-manual').form('clear');

                $('#manual-description').textbox('setValue', row.description);
                $('#manual-price').numberbox('setValue', row.raw_amount);
                $('#fm-item-manual').form('load', { notes: row.notes });

                if (row.count_guests) {
                    $('#manual-count-guests').checkbox('check');
                } else {
                    $('#manual-count-guests').checkbox('uncheck');
                }
            }

            function saveItemManual() {
                var url = current_item_id
                    ? '{{ url('events/budget-items') }}/' + current_item_id
                    : '{{ route('events.budget.item.store', $event->id) }}';

                $('#fm-item-manual').form('submit', {
                    url: url,
                    onSubmit: function (param) {
                        param._token = '{{ csrf_token() }}';
                        if (current_item_id) param._method = 'PUT';
                        // Asegurar que el checkbox envíe su valor
                        param.count_guests = $('#manual-count-guests').checkbox('options').checked ? 1 : 0;
                        return $(this).form('validate');
                    },
                    success: function (result) {
                        var res = JSON.parse(result);
                        if (res.success) {
                            $('#dlg-item-manual').dialog('close');
                            $('#dg-items').datagrid('reload');
                            $('#budget-total').text(res.total_items);
                        } else {
                            $.messager.alert('Error', 'Error al guardar el item', 'error');
                        }
                    }
                });
            }

            function removeItem() {
                var row = $('#dg-items').datagrid('getSelected');
                if (!row) return;
                $.messager.confirm('Confirmar', '¿Eliminar este item del evento?', function (r) {
                    if (r) {
                        $.ajax({
                            url: '{{ url('events/budget-items') }}/' + row.id,
                            type: 'DELETE',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function (res) {
                                $('#dg-items').datagrid('reload');
                                $('#budget-total').text(res.total_items);
                            }
                        });
                    }
                });
            }

            function openCatalogDialog() {
                $('#dlg-catalog').dialog('open').dialog('center');
            }

            function addSelectedItems() {
                var rows = $('#dg-catalog').datagrid('getSelections');
                if (rows.length === 0) return;
                var ids = rows.map(function (r) { return r.id; });
                $.post('{{ route('events.budget.add-from-catalog', $event->id) }}', {
                    _token: '{{ csrf_token() }}',
                    product_ids: ids
                }, function (result) {
                    if (result.success) {
                        $('#dlg-catalog').dialog('close');
                        $('#dg-items').datagrid('reload');
                        $('#budget-total').text(result.total_items);
                    }
                }, 'json');
            }


            // --- Lógica de Asignación ---
            function openAssignDialog() {
                var row = $('#dg-unassigned').datagrid('getSelected');
                if (!row) {
                    $.messager.alert('Aviso', 'Seleccione una familia de la lista izquierda', 'warning');
                    return;
                }
                $('#dlg-assign').dialog('open');
                $('#fm-assign').form('clear');
                $('#assign-family-name').text(row.family_name);
                $('#assign-budget-id').val(row.id);
            }

            function saveAssignment() {
                $('#fm-assign').form('submit', {
                    url: '{{ route('events.tables.assign-budget', $event->id) }}',
                    onSubmit: function (param) {
                        param._token = '{{ csrf_token() }}';
                        param.budget_id = $('#assign-budget-id').val();
                        return $(this).form('validate');
                    },
                    success: function (result) {
                        var res = JSON.parse(result);
                        if (res.success) {
                            $('#dlg-assign').dialog('close');
                            $('#dg-unassigned').datagrid('reload');
                            $('#dg-tables').datagrid('reload');
                        } else {
                            $.messager.alert('Error', 'No se pudo asignar', 'error');
                        }
                    }
                });
            }

            // --- Formatters para DataGrid Mesas ---

            function colorFormatter(val, row) {
                return '<div style="width:20px;height:20px;background-color:' + val + ';border:1px solid #ccc;margin:0 auto;"></div>';
            }

            function countGuestsFormatter(val, row) {
                if (val) {
                    return '<span class="badge bg-info text-dark"><i class="bi bi-person-check"></i> Sí</span>';
                }
                return '<span class="text-muted">No</span>';
            }

            function familiesFormatter(val, row) {
                if (row.assigned_budgets && row.assigned_budgets.length > 0) {
                    return row.assigned_budgets.map(function (b) { return b.family_name; }).join(', ');
                }
                return '<span class="text-muted">-</span>';
            }

            // --- Detail View para Mesas (Mostrar Familias adentro) ---
            var detailview = $.extend({}, $.fn.datagrid.defaults.view, {
                renderRow: function (target, fields, frozen, rowIndex, rowData) {
                    return $.fn.datagrid.defaults.view.renderRow.call(this, target, fields, frozen, rowIndex, rowData);
                },
                onExpandRow: function (index, row) {
                    var ddv = $(this).datagrid('getRowDetail', index).find('div.ddv');
                    if (row.assigned_budgets && row.assigned_budgets.length > 0) {
                        var html = '<ul class="list-group list-group-flush">';
                        row.assigned_budgets.forEach(function (b) {
                            html += '<li class="list-group-item" style="background-color:' + row.color + '20">';
                            html += '<div class="d-flex justify-content-between align-items-center">';
                            html += '<strong>' + b.family_name + '</strong>';
                            html += '<span class="badge bg-primary rounded-pill">' + b.guests_count + '</span>';
                            html += '</div>';

                            if (b.guests && b.guests.length > 0) {
                                html += '<ul class="mt-2 text-muted small" style="list-style-type:circle; padding-left:20px;">';
                                b.guests.forEach(function (g) {
                                    html += '<li>' + g.name + (g.cedula ? ' (' + g.cedula + ')' : '') + '</li>';
                                });
                                html += '</ul>';
                            }

                            html += '</li>';
                        });
                        html += '</ul>';
                        ddv.html(html);
                        $('#dg-tables').datagrid('fixDetailRowHeight', index);
                    } else {
                        ddv.html('<div class="p-2 text-muted">Sin familias asignadas</div>');
                        $('#dg-tables').datagrid('fixDetailRowHeight', index);
                    }
                }
            });

            function detailFormatter(index, row) {
                return '<div class="ddv" style="padding:5px 0"></div>';
            }
        </script>
    @endpush
@endsection