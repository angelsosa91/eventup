@extends('layouts.app')

@section('title', 'Aportes de Alumnos')
@section('page-title', 'Mantenimiento de Aportes')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <!-- Toolbar -->
                <div id="toolbar" class="p-3 bg-light border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add"
                            onclick="newContribution()">Nuevo Aporte</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok"
                            onclick="confirmContribution()">Confirmar</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-print"
                            onclick="printReceipt()">Imprimir Recibo</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel"
                            onclick="cancelContribution()">Anular</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove"
                            onclick="deleteContribution()">Eliminar</a>

                        <div class="ms-auto d-flex align-items-center gap-2">
                            <input id="searchbox" class="easyui-searchbox" style="width: 250px"
                                data-options="prompt:'Buscar por número o alumno...',searcher:doSearch">
                        </div>
                    </div>
                </div>

                <!-- DataGrid -->
                <table id="dg" class="easyui-datagrid" style="width:100%;height:600px;" data-options="
                           url: '{{ route('contributions.data') }}',
                           method: 'get',
                           toolbar: '#toolbar',
                           pagination: true,
                           rownumbers: true,
                           singleSelect: true,
                           fitColumns: true,
                           pageSize: 20,
                           pageList: [10, 20, 50, 100],
                           sortName: 'id',
                           sortOrder: 'desc',
                           remoteSort: true
                       ">
                    <thead>
                        <tr>
                            <th data-options="field:'contribution_number',width:120,sortable:true">Número</th>
                            <th data-options="field:'contribution_date',width:100,sortable:true">Fecha</th>
                            <th data-options="field:'customer_name',width:250">Alumno</th>
                            <th
                                data-options="field:'amount',width:120,align:'right',styler:function(){return 'font-weight:bold;'}">
                                Monto (Gs.)</th>
                            <th data-options="field:'payment_method',width:120">Medio de Pago</th>
                            <th data-options="field:'status',width:100,align:'center',formatter:formatStatus">Estado</th>
                            <th data-options="field:'user_name',width:120">Usuario</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Dialog para Nuevo Aporte -->
    <div id="dlg" class="easyui-dialog" style="width:500px"
        data-options="closed:true,modal:true,border:'thin',buttons:'#dlg-buttons'">
        <form id="fm" method="post" novalidate style="margin:0;padding:20px 50px">
            <h5 class="mb-4">Registrar Aporte</h5>

            <div class="mb-3">
                <label class="form-label">Alumno:</label>
                <input class="easyui-combobox" name="customer_id" id="customer_id" style="width:100%" data-options="
                           url:'{{ route('customers.list') }}',
                           method:'get',
                           valueField:'id',
                           textField:'name',
                           required:true,
                           mode:'remote',
                           prompt:'Buscar alumno...'
                       ">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Fecha:</label>
                    <input class="easyui-datebox" name="contribution_date" id="contribution_date" style="width:100%"
                        data-options="required:true">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Monto (Gs.):</label>
                    <input class="easyui-numberbox" name="amount" id="amount" style="width:100%"
                        data-options="required:true,min:0,groupSeparator:'.'">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Medio de Pago:</label>
                    <select class="easyui-combobox" name="payment_method" id="payment_method" style="width:100%"
                        data-options="required:true,editable:false">
                        <option value="Efectivo">Efectivo</option>
                        <option value="Transferencia">Transferencia</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Referencia:</label>
                    <input class="easyui-textbox" name="reference" id="reference" style="width:100%"
                        data-options="prompt:'Núm. Transferencia / Cheque'">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Notas:</label>
                <input class="easyui-textbox" name="notes" style="width:100%;height:60px" data-options="multiline:true">
            </div>
        </form>
    </div>
    <div id="dlg-buttons">
        <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveContribution()"
            style="width:90px">Guardar</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel"
            onclick="javascript:$('#dlg').dialog('close')" style="width:90px">Cancelar</a>
    </div>

    @push('styles')
        <style>
            .badge {
                padding: 5px 10px;
                border-radius: 4px;
                font-weight: 500;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            function formatStatus(value, row) {
                switch (value) {
                    case 'draft':
                        return '<span class="badge bg-secondary">Borrador</span>';
                    case 'confirmed':
                        return '<span class="badge bg-success">Confirmado</span>';
                    case 'cancelled':
                        return '<span class="badge bg-danger">Anulado</span>';
                    default:
                        return row.status_label || value;
                }
            }

            function doSearch(value) {
                $('#dg').datagrid('load', {
                    search: value
                });
            }

            function newContribution() {
                $('#dlg').dialog('open').dialog('center').dialog('setTitle', 'Nuevo Aporte');
                $('#fm').form('clear');
                $('#contribution_date').datebox('setValue', '{{ date('d-m-Y') }}');
                $('#payment_method').combobox('setValue', 'Efectivo');
            }

            function saveContribution() {
                $('#fm').form('submit', {
                    url: '{{ route('contributions.store') }}',
                    onSubmit: function (param) {
                        param._token = '{{ csrf_token() }}';
                        return $(this).form('validate');
                    },
                    success: function (result) {
                        var result = JSON.parse(result);
                        if (result.errors) {
                            var message = '';
                            for (var key in result.errors) {
                                message += result.errors[key].join('<br>') + '<br>';
                            }
                            $.messager.alert('Error', message, 'error');
                        } else {
                            $('#dlg').dialog('close');
                            $('#dg').datagrid('reload');
                            $.messager.show({
                                title: 'Éxito',
                                msg: result.message
                            });
                        }
                    }
                });
            }

            function confirmContribution() {
                var row = $('#dg').datagrid('getSelected');
                if (!row) {
                    $.messager.alert('Aviso', 'Seleccione un aporte', 'warning');
                    return;
                }
                if (row.status !== 'draft') {
                    $.messager.alert('Aviso', 'Solo se pueden confirmar aportes en borrador', 'warning');
                    return;
                }

                $.messager.confirm('Confirmar', '¿Desea confirmar este aporte? Se registrará en Caja/Banco y Contabilidad.', function (r) {
                    if (r) {
                        $.post('{{ url('contributions') }}/' + row.id + '/confirm', {
                            _token: '{{ csrf_token() }}'
                        }, function (result) {
                            if (result.success) {
                                $('#dg').datagrid('reload');
                                $.messager.show({
                                    title: 'Éxito',
                                    msg: result.message
                                });
                            } else {
                                $.messager.alert('Error', result.message || 'Error desconocido', 'error');
                            }
                        }, 'json').fail(function (xhr) {
                            var message = xhr.responseJSON.errors?.general?.[0] || 'Error en el servidor';
                            $.messager.alert('Error', message, 'error');
                        });
                    }
                });
            }

            function cancelContribution() {
                var row = $('#dg').datagrid('getSelected');
                if (!row) {
                    $.messager.alert('Aviso', 'Seleccione un aporte', 'warning');
                    return;
                }
                if (row.status === 'cancelled') {
                    $.messager.alert('Aviso', 'El aporte ya está anulado', 'warning');
                    return;
                }

                $.messager.confirm('Confirmar', '¿Desea anular este aporte? Se revertirán los movimientos en Caja/Banco y Contabilidad.', function (r) {
                    if (r) {
                        $.post('{{ url('contributions') }}/' + row.id + '/cancel', {
                            _token: '{{ csrf_token() }}'
                        }, function (result) {
                            if (result.success) {
                                $('#dg').datagrid('reload');
                                $.messager.show({
                                    title: 'Éxito',
                                    msg: result.message
                                });
                            }
                        }, 'json');
                    }
                });
            }

            function deleteContribution() {
                var row = $('#dg').datagrid('getSelected');
                if (!row) {
                    $.messager.alert('Aviso', 'Seleccione un aporte', 'warning');
                    return;
                }
                if (row.status !== 'draft') {
                    $.messager.alert('Aviso', 'Solo se pueden eliminar aportes en borrador', 'warning');
                    return;
                }

                $.messager.confirm('Confirmar', '¿Desea eliminar permanentemente este aporte?', function (r) {
                    if (r) {
                        $.ajax({
                            url: '{{ url('contributions') }}/' + row.id,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function (result) {
                                if (result.success) {
                                    $('#dg').datagrid('reload');
                                    $.messager.show({
                                        title: 'Éxito',
                                        msg: result.message
                                    });
                                }
                            }
                        });
                    }
                });
            }

            function printReceipt() {
                var row = $('#dg').datagrid('getSelected');
                if (!row) {
                    $.messager.alert('Aviso', 'Seleccione un aporte', 'warning');
                    return;
                }
                if (row.status !== 'confirmed') {
                    $.messager.alert('Aviso', 'Solo se pueden imprimir recibos de aportes confirmados', 'warning');
                    return;
                }

                window.open('{{ url('contributions') }}/' + row.id + '/receipt', '_blank');
            }
        </script>
    @endpush
@endsection