@extends('layouts.app')

@section('title', 'Detalle de Presupuesto')
@section('page-title', 'Presupuesto Familiar: ' . ($eventBudget->customer->name))

@section('content')
    <div class="container-fluid">
        <div class="d-flex align-items-center mb-3 gap-3">
            <a href="{{ route('event-budgets.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
            <h4 class="mb-0">Familia: {{ $eventBudget->family_name }}</h4>
            <span
                class="badge bg-{{ $eventBudget->status === 'accepted' ? 'success' : ($eventBudget->status === 'rejected' ? 'danger' : 'secondary') }}">
                {{ $eventBudget->status_label }}
            </span>
            <div class="ms-auto">
                <a href="{{ route('event-budgets.pdf', $eventBudget->id) }}" target="_blank" class="btn btn-primary btn-sm">
                    <i class="bi bi-printer"></i> Imprimir Presupuesto
                </a>
            </div>
        </div>

        <div class="easyui-tabs" style="width:100%;height:700px;">
            <!-- PESTAÑA: GENERAL -->
            <div title="General" style="padding:20px;">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Evento:</strong> {{ $eventBudget->event->name }}</p>
                        <p><strong>Alumno:</strong> {{ $eventBudget->customer->name }}</p>
                        <p><strong>Fecha:</strong> {{ $eventBudget->budget_date->format('d/m/Y') }}</p>
                        <p><strong>Total Presupuesto:</strong> <span
                                class="fw-bold fs-5 text-primary">{{ number_format($eventBudget->total_amount, 0, ',', '.') }}</span>
                            Gs.</p>
                    </div>
                    <div class="col-md-6">
                        <form id="fm-header" method="post">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="form-label">Estado:</label>
                                <input class="easyui-combobox" name="status" value="{{ $eventBudget->status }}"
                                    style="width:100%" data-options="
                                                                                panelHeight:'auto',
                                                                                data: [
                                                                                    {value:'draft',text:'Borrador'},
                                                                                    {value:'sent',text:'Enviado'},
                                                                                    {value:'accepted',text:'Aceptado'},
                                                                                    {value:'rejected',text:'Rechazado'}
                                                                                ]
                                                                            ">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notas:</label>
                                <input class="easyui-textbox" name="notes" value="{{ $eventBudget->notes }}"
                                    style="width:100%;height:80px" data-options="multiline:true">
                            </div>
                            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-save"
                                onclick="saveHeader()">Actualizar Cabecera</a>
                        </form>
                    </div>
                </div>
            </div>

            <!-- PESTAÑA: ITEMS -->
            <div title="Items" style="padding:10px;">
                <div id="tb-items" class="p-2 border-bottom mb-2">
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" onclick="newItem()">Agregar
                        Item</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit"
                        onclick="editItem()">Editar</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove"
                        onclick="removeItem()">Eliminar</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-reload"
                        onclick="importItems()">Cargar desde Evento</a>

                    <span class="ms-4 fw-bold text-success">
                        Subtotal: <span
                            id="budget-total-display">{{ number_format($eventBudget->total_amount, 0, ',', '.') }}</span>
                        Gs.
                    </span>
                </div>

                <table id="dg-items" class="easyui-datagrid" style="width:100%;height:580px" data-options="
                                                    url:'{{ route('event-budgets.items.data', $eventBudget->id) }}',
                                                    method:'get',
                                                    singleSelect:true,
                                                    fitColumns:true,
                                                    rownumbers:true,
                                                    toolbar:'#tb-items'
                                                ">
                    <thead>
                        <tr>
                            <th data-options="field:'id',hidden:true">ID</th>
                            <th data-options="field:'description',width:300">Descripción/Concepto</th>
                            <th data-options="field:'quantity',width:100,align:'center'">Cantidad</th>
                            <th data-options="field:'unit_price',width:150,align:'right'">Precio Unit.</th>
                            <th
                                data-options="field:'total',width:150,align:'right',styler:function(){return 'font-weight:bold;'}">
                                Total</th>
                            <th data-options="field:'count_guests',width:100,align:'center',formatter:countGuestsFormatter">
                                Cuenta Inv.</th>
                            <th data-options="field:'notes',width:200">Notas</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <!-- PESTAÑA: INVITADOS -->
            <div title="Invitados" style="padding:10px;">
                <div id="tb-guests" class="p-2 border-bottom mb-2">
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" onclick="newGuest()">Nuevo
                        Invitado</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit"
                        onclick="editGuest()">Editar</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove"
                        onclick="removeGuest()">Eliminar</a>
                    <span class="ms-3 text-muted">Total Invitados: {{ $eventBudget->guests->count() }}</span>
                </div>
                <table id="dg-guests" class="easyui-datagrid" style="width:100%;height:580px" data-options="
                                                    url:'{{ route('event-budgets.guests.data', $eventBudget->id) }}',
                                                    method:'get',
                                                    singleSelect:true,
                                                    fitColumns:true,
                                                    rownumbers:true,
                                                    toolbar:'#tb-guests'
                                                ">
                    <thead>
                        <tr>
                            <th data-options="field:'id',hidden:true">ID</th>
                            <th data-options="field:'name',width:300">Nombre del Invitado</th>
                            <th data-options="field:'cedula',width:150">Cédula</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Diálogos -->
    <!-- Nuevo/Editar Item -->
    <div id="dlg-item" class="easyui-dialog" style="width:450px"
        data-options="closed:true,modal:true,title:'Info Item',buttons:'#item-buttons'">
        <form id="fm-item" method="post" novalidate style="padding:20px">
            <div class="mb-3">
                <label class="form-label">Descripción:</label>
                <input class="easyui-textbox" name="description" id="item-desc" style="width:100%"
                    data-options="required:true">
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Cantidad:</label>
                    <input class="easyui-numberbox" name="quantity" id="item-qty" style="width:100%"
                        data-options="required:true,min:0,precision:0">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Monto Unit.:</label>
                    <input class="easyui-numberbox" name="unit_price" id="item-price" style="width:100%"
                        data-options="required:true,min:0,groupSeparator:'.'">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Notas:</label>
                <input class="easyui-textbox" name="notes" style="width:100%;height:60px" data-options="multiline:true">
            </div>
            <div class="mb-3">
                <input class="easyui-checkbox" name="count_guests" id="item-count-guests" value="1"
                    label="Cuenta Invitados:">
            </div>
        </form>
    </div>
    <div id="item-buttons">
        <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveItem()"
            style="width:90px">Guardar</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel"
            onclick="javascript:$('#dlg-item').dialog('close')" style="width:90px">Cancelar</a>
    </div>

    <!-- Nuevo Invitado -->
    <div id="dlg-guest" class="easyui-dialog" style="width:400px"
        data-options="closed:true,modal:true,title:'Nuevo Invitado',buttons:'#guest-buttons'">
        <form id="fm-guest" method="post" novalidate style="padding:20px">
            <div class="mb-3">
                <label class="form-label">Nombre:</label>
                <input class="easyui-textbox" name="name" style="width:100%" data-options="required:true">
            </div>
            <div class="mb-3">
                <label class="form-label">Cédula:</label>
                <input class="easyui-textbox" name="cedula" style="width:100%">
            </div>
        </form>
    </div>
    <div id="guest-buttons">
        <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveGuest()"
            style="width:90px">Guardar</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel"
            onclick="javascript:$('#dlg-guest').dialog('close')" style="width:90px">Cancelar</a>
    </div>

    @push('scripts')
        <script>
            function formatMoney(amount) {
                return new Intl.NumberFormat('es-PY').format(amount);
            }

            function countGuestsFormatter(value, row) {
                if (value == 1 || value == true) {
                    return '<span class="badge bg-info text-dark"><i class="bi bi-person-check-fill"></i> SÍ</span>';
                }
                return '<span class="text-muted"><i class="bi bi-person-x"></i> NO</span>';
            }

            function updateBudgetTotal(total) {
                var formatted = formatMoney(total);
                $('#budget-total-display').text(formatted);
                // También actualizar el total en la pestaña General
                $('.fw-bold.fs-5.text-primary').first().text(formatted);
            }

            function saveHeader() {
                $('#fm-header').form('submit', {
                    url: '{{ route('event-budgets.update', $eventBudget->id) }}',
                    success: function (result) {
                        var res = JSON.parse(result);
                        if (res.success) $.messager.show({ title: 'Éxito', msg: 'Cabecera actualizada' });
                    }
                });
            }

            // --- Items ---
            function newItem() {
                current_item_id = null;
                $('#dlg-item').dialog('open').dialog('setTitle', 'Nuevo Item de Presupuesto');
                $('#fm-item').form('clear');
                $('#item-count-guests').checkbox('uncheck');
            }

            function editItem() {
                var row = $('#dg-items').datagrid('getSelected');
                if (!row) return;
                current_item_id = row.id;
                $('#dlg-item').dialog('open').dialog('setTitle', 'Editar Item');
                $('#fm-item').form('load', {
                    description: row.description,
                    quantity: row.raw_quantity,
                    unit_price: row.raw_unit_price,
                    notes: row.notes
                });

                if (row.count_guests == 1 || row.count_guests == true) {
                    $('#item-count-guests').checkbox('check');
                } else {
                    $('#item-count-guests').checkbox('uncheck');
                }
            }

            function saveItem() {
                var url = current_item_id
                    ? '{{ url('event-budgets/items') }}/' + current_item_id
                    : '{{ route('event-budgets.items.store', $eventBudget->id) }}';

                $('#fm-item').form('submit', {
                    url: url,
                    onSubmit: function (param) {
                        param._token = '{{ csrf_token() }}';
                        if (current_item_id) param._method = 'PUT';
                        return $(this).form('validate');
                    },
                    success: function (result) {
                        var res = JSON.parse(result);
                        if (res.success) {
                            $('#dlg-item').dialog('close');
                            $('#dg-items').datagrid('reload');
                            updateBudgetTotal(res.total_budget);
                        }
                    }
                });
            }

            function importItems() {
                $.messager.confirm('Confirmar', '¿Deseas cargar los items configurados en el evento general?', function (r) {
                    if (r) {
                        $.post('{{ route('event-budgets.import', $eventBudget->id) }}', {
                            _token: '{{ csrf_token() }}'
                        }, function (res) {
                            if (res.success) {
                                $.messager.show({ title: 'Éxito', msg: res.message });
                                $('#dg-items').datagrid('reload');
                                updateBudgetTotal(res.total_budget);
                            } else {
                                $.messager.alert('Error', res.message, 'error');
                            }
                        });
                    }
                });
            }

            function removeItem() {
                var row = $('#dg-items').datagrid('getSelected');
                if (!row) return;
                $.messager.confirm('Confirmar', '¿Eliminar este item?', function (r) {
                    if (r) {
                        $.post('{{ url('event-budgets/items') }}/' + row.id, {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        }, function (res) {
                            $('#dg-items').datagrid('reload');
                            updateBudgetTotal(res.total_budget);
                        });
                    }
                });
            }

            // --- Invitados ---
            var current_guest_id = null;

            function newGuest() {
                current_guest_id = null;
                $('#dlg-guest').dialog('open').dialog('setTitle', 'Nuevo Invitado');
                $('#fm-guest').form('clear');
            }

            function editGuest() {
                var row = $('#dg-guests').datagrid('getSelected');
                if (!row) {
                    $.messager.alert('Aviso', 'Seleccione un invitado', 'warning');
                    return;
                }
                current_guest_id = row.id;
                $('#dlg-guest').dialog('open').dialog('setTitle', 'Editar Invitado');
                $('#fm-guest').form('clear');
                $('#fm-guest').form('load', row);
            }

            function saveGuest() {
                var url = current_guest_id
                    ? '{{ url('event-budgets/guests') }}/' + current_guest_id
                    : '{{ route('event-budgets.guests.store', $eventBudget->id) }}';

                $('#fm-guest').form('submit', {
                    url: url,
                    onSubmit: function (param) {
                        param._token = '{{ csrf_token() }}';
                        if (current_guest_id) param._method = 'PUT';
                        return $(this).form('validate');
                    },
                    success: function (result) {
                        var res = JSON.parse(result);
                        if (res.success || result.includes('success')) {
                            $('#dlg-guest').dialog('close');
                            $('#dg-guests').datagrid('reload');
                        } else {
                            // Try fallback parsing if controller returns strict json
                            $('#dlg-guest').dialog('close');
                            $('#dg-guests').datagrid('reload');
                        }
                    }
                });
            }

            function removeGuest() {
                var row = $('#dg-guests').datagrid('getSelected');
                if (row) {
                    $.post('{{ url('event-budgets/guests') }}/' + row.id, {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    }, function () {
                        $('#dg-guests').datagrid('reload');
                    });
                }
            }
        </script>
    @endpush
@endsection