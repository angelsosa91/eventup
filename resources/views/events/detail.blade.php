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
                            <th data-options="field:'notes',width:250">Notas</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <!-- PESTAÑA: MESAS -->
            <div title="Mesas" style="padding:10px;">
                <div id="tb-tables" class="p-2 border-bottom mb-2">
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" onclick="addTable()">Agregar
                        Mesa</a>
                </div>
                <table id="dg-tables" class="easyui-datagrid" style="width:100%;height:580px" data-options="
                           url: '{{ route('events.tables.grid.data', $event->id) }}',
                           method: 'get',
                           singleSelect:true,
                           fitColumns:true,
                           rownumbers:true,
                           toolbar:'#tb-tables'
                       ">
                    <thead>
                        <tr>
                            <th data-options="field:'id',hidden:true">ID</th>
                            <th data-options="field:'name',width:200">Nombre de Mesa</th>
                            <th data-options="field:'capacity',width:100,align:'center'">Capacidad</th>
                            <th data-options="field:'occupied',width:100,align:'center'">Ocupados</th>
                        </tr>
                    </thead>
                </table>
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

    @push('scripts')
        <script>
            var current_item_id = null;

            function formatMoney(amount) {
                return new Intl.NumberFormat('es-PY').format(amount);
            }

            function newItemManual() {
                current_item_id = null;
                $('#dlg-item-manual').dialog('open').dialog('setTitle', 'Nuevo Item');
                $('#fm-item-manual').form('clear');
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

            function addTable() {
                $.messager.prompt('Nueva Mesa', 'Nombre de la mesa:', function (name) {
                    if (name) {
                        $.messager.prompt('Capacidad', 'Capacidad para ' + name + ':', function (cap) {
                            if (cap) {
                                $.post('{{ route('events.tables.store', $event->id) }}', {
                                    _token: '{{ csrf_token() }}',
                                    name: name,
                                    capacity: cap
                                }, function (result) {
                                    if (result.success) {
                                        $('#dg-tables').datagrid('reload');
                                    }
                                });
                            }
                        });
                    }
                });
            }
        </script>
    @endpush
@endsection