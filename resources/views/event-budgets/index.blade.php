@extends('layouts.app')

@section('title', 'Presupuesto Familiar')
@section('page-title', 'Presupuesto Familiar')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <!-- Toolbar -->
                <div id="toolbar" class="p-3 bg-light border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add"
                            onclick="newBudget()">Nuevo Presupuesto</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-search"
                            onclick="viewBudget()">Ver Detalles</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove"
                            onclick="deleteBudget()">Eliminar</a>

                        <div class="ms-auto d-flex align-items-center gap-2">
                            <label>Evento:</label>
                            <select id="filter-event" class="easyui-combobox" style="width:200px"
                                data-options="panelHeight:'auto'">
                                <option value="">Todos los eventos</option>
                                @foreach($events as $event)
                                    <option value="{{ $event->id }}">{{ $event->name }}</option>
                                @endforeach
                            </select>
                            <input id="searchbox" class="easyui-textbox" style="width: 250px"
                                data-options="prompt:'Alumno o Familia...'">
                            <a href="javascript:void(0)" class="easyui-linkbutton" plain="true" iconCls="icon-search"
                                onClick="doSearch()">Buscar</a>
                        </div>
                    </div>
                </div>

                <!-- DataGrid -->
                <table id="dg" class="easyui-datagrid" style="width:100%;height:600px;" data-options="
                                                                                                                   url: '{{ route('event-budgets.data') }}',
                                                                                                                   method: 'get',
                                                                                                                   toolbar: '#toolbar',
                                                                                                                   pagination: true,
                                                                                                                   rownumbers: true,
                                                                                                                   singleSelect: true,
                                                                                                                   fitColumns: true,
                                                                                                                   pageSize: 20,
                                                                                                                   sortName: 'id',
                                                                                                                   sortOrder: 'desc',
                                                                                                                   remoteSort: true
                                                                                                               ">
                    <thead>
                        <tr>
                            <th data-options="field:'event_name',width:200">Evento</th>
                            <th data-options="field:'customer_name',width:250">Alumno</th>
                            <th data-options="field:'family_name',width:200">Familia</th>
                            <th data-options="field:'budget_date',width:120,align:'center'">Fecha</th>
                            <th
                                data-options="field:'total_amount',width:150,align:'right',styler:function(){return 'font-weight:bold;'}">
                                Total (Gs.)</th>
                            <th data-options="field:'status',width:100,align:'center',formatter:formatStatus">Estado</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Dialog para Nuevo Presupuesto -->
    <div id="dlg" class="easyui-dialog" style="width:500px"
        data-options="closed:true,modal:true,border:'thin',buttons:'#dlg-buttons'">
        <form id="fm" method="post" novalidate style="margin:0;padding:20px 50px">
            <h5 class="mb-4">Crear Presupuesto Familiar</h5>

            <div class="mb-3">
                <label class="form-label">Evento:</label>
                <input class="easyui-combobox" name="event_id" style="width:100%" data-options="
                                                                                                            url:'{{ route('events.data') }}',
                                                                                                            method:'get',
                                                                                                            valueField:'id',
                                                                                                            textField:'name',
                                                                                                            required:true,
                                                                                                            prompt:'Seleccione un evento',
                                                                                                            loadFilter: function(data){ return data.rows; }
                                                                                                        ">
            </div>

            <div class="mb-3">
                <label class="form-label">Alumno/Alumno:</label>
                <input class="easyui-combobox" name="customer_id" style="width:100%" data-options="
                                                                                                            url:'{{ route('customers.list') }}',
                                                                                                            method:'get',
                                                                                                            valueField:'id',
                                                                                                            textField:'name',
                                                                                                            required:true,
                                                                                                            prompt:'Busque el alumno...',
                                                                                                            mode:'remote'
                                                                                                        ">
            </div>

            <div class="mb-3">
                <label class="form-label">Fecha de Presupuesto:</label>
                <input class="easyui-datebox" name="budget_date" value="{{ date('d/m/Y') }}" style="width:100%"
                    data-options="required:true">
            </div>

            <div class="mb-3">
                <label class="form-label">Notas:</label>
                <input class="easyui-textbox" name="notes" style="width:100%;height:80px" data-options="multiline:true">
            </div>
        </form>
    </div>
    <div id="dlg-buttons">
        <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveBudget()"
            style="width:90px">Guardar</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel"
            onclick="javascript:$('#dlg').dialog('close')" style="width:90px">Cancelar</a>
    </div>
@endsection

@push('scripts')
    <script>
        function formatStatus(value, row) {
            switch (value) {
                case 'draft': return '<span class="badge bg-secondary">Borrador</span>';
                case 'sent': return '<span class="badge bg-primary">Enviado</span>';
                case 'accepted': return '<span class="badge bg-success">Aceptado</span>';
                case 'rejected': return '<span class="badge bg-danger">Rechazado</span>';
                default: return row.status_label || value;
            }
        }

        function doSearch() {
            var event_id = $('#filter-event').length > 0 ? $('#filter-event').combobox('getValue') : '';
            var search = $('#searchbox').length > 0 ? $('#searchbox').textbox('getValue') : '';

            $('#dg').datagrid('load', {
                event_id: event_id,
                search: search
            });
        }

        function newBudget() {
            $('#dlg').dialog('open').dialog('setTitle', 'Nuevo Presupuesto');
            $('#fm').form('clear');
            $('#fm').form('load', { budget_date: '{{ date('d/m/Y') }}' });
        }

        function saveBudget() {
            $('#fm').form('submit', {
                url: '{{ route('event-budgets.store') }}',
                onSubmit: function (param) {
                    param._token = '{{ csrf_token() }}';
                    return $(this).form('validate');
                },
                success: function (result) {
                    var res = JSON.parse(result);
                    if (res.errors) {
                        $.messager.alert('Error', res.errors.general[0], 'error');
                    } else {
                        $('#dlg').dialog('close');
                        $('#dg').datagrid('reload');
                        $.messager.show({ title: 'Éxito', msg: res.message });
                    }
                }
            });
        }

        function viewBudget() {
            var row = $('#dg').datagrid('getSelected');
            if (row) {
                window.location.href = '{{ url('event-budgets') }}/' + row.id;
            } else {
                $.messager.alert('Aviso', 'Seleccione un presupuesto', 'warning');
            }
        }

        function deleteBudget() {
            var row = $('#dg').datagrid('getSelected');
            if (row) {
                $.messager.confirm('Confirmar', '¿Está seguro de eliminar este presupuesto?', function (r) {
                    if (r) {
                        $.post('{{ url('event-budgets') }}/' + row.id, {
                            _method: 'DELETE',
                            _token: '{{ csrf_token() }}'
                        }, function (result) {
                            if (result.success) {
                                $('#dg').datagrid('reload');
                                $.messager.show({ title: 'Éxito', msg: result.message });
                            } else {
                                $.messager.alert('Error', result.message, 'error');
                            }
                        }, 'json');
                    }
                });
            } else {
                $.messager.alert('Aviso', 'Seleccione un presupuesto para eliminar', 'warning');
            }
        }
    </script>
@endpush