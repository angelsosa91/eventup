@extends('layouts.app')

@section('title', 'Gestión de Eventos')
@section('page-title', 'Listado de Eventos')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <!-- Toolbar -->
                <div id="toolbar" class="p-3 bg-light border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" onclick="newEvent()">Nuevo
                            Evento</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-search"
                            onclick="viewEvent()">Ver Detalles</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove"
                            onclick="deleteEvent()">Eliminar</a>

                        <div class="ms-auto d-flex align-items-center gap-2">
                            <input id="searchbox" class="easyui-searchbox" style="width: 250px"
                                data-options="prompt:'Buscar evento...',searcher:doSearch">
                        </div>
                    </div>
                </div>

                <!-- DataGrid -->
                <table id="dg" class="easyui-datagrid" style="width:100%;height:600px;" data-options="
                           url: '{{ route('events.data') }}',
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
                            <th data-options="field:'name',width:300">Nombre del Evento</th>
                            <th data-options="field:'event_date',width:150,align:'center',sortable:true">Fecha</th>
                            <th data-options="field:'estimated_budget',width:150,align:'right'">Presp. Estimado</th>
                            <th data-options="field:'status',width:120,align:'center',formatter:formatStatus">Estado</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Dialog para Nuevo Evento -->
    <div id="dlg" class="easyui-dialog" style="width:500px"
        data-options="closed:true,modal:true,border:'thin',buttons:'#dlg-buttons'">
        <form id="fm" method="post" novalidate style="margin:0;padding:20px 50px">
            <h5 class="mb-4">Información del Evento</h5>

            <div class="mb-3">
                <label class="form-label">Nombre del Evento:</label>
                <input class="easyui-textbox" name="name" style="width:100%"
                    data-options="required:true,prompt:'Ej: Takuaras - Boda Gonzales'">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Fecha del Evento:</label>
                    <input class="easyui-datebox" name="event_date" style="width:100%" data-options="required:true">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Presupuesto Est. (Gs.):</label>
                    <input class="easyui-numberbox" name="estimated_budget" style="width:100%"
                        data-options="min:0,groupSeparator:'.'">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Notas:</label>
                <input class="easyui-textbox" name="notes" style="width:100%;height:80px" data-options="multiline:true">
            </div>
        </form>
    </div>
    <div id="dlg-buttons">
        <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveEvent()"
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
                    case 'draft': return '<span class="badge bg-secondary">Borrador</span>';
                    case 'confirmed': return '<span class="badge bg-success">Confirmado</span>';
                    case 'cancelled': return '<span class="badge bg-danger">Anulado</span>';
                    case 'completed': return '<span class="badge bg-info">Completado</span>';
                    default: return row.status_label || value;
                }
            }

            function doSearch(value) {
                $('#dg').datagrid('load', { search: value });
            }

            function newEvent() {
                $('#dlg').dialog('open').dialog('center').dialog('setTitle', 'Nuevo Evento');
                $('#fm').form('clear');
            }

            function saveEvent() {
                $('#fm').form('submit', {
                    url: '{{ route('events.store') }}',
                    onSubmit: function (param) {
                        param._token = '{{ csrf_token() }}';
                        return $(this).form('validate');
                    },
                    success: function (result) {
                        var result = JSON.parse(result);
                        if (result.errors) {
                            $.messager.alert('Error', result.errors.general ? result.errors.general[0] : 'Error al guardar', 'error');
                        } else {
                            $('#dlg').dialog('close');
                            $('#dg').datagrid('reload');
                            $.messager.show({ title: 'Éxito', msg: result.message });
                        }
                    }
                });
            }

            function viewEvent() {
                var row = $('#dg').datagrid('getSelected');
                if (row) {
                    window.location.href = '{{ url('events') }}/' + row.id;
                } else {
                    $.messager.alert('Aviso', 'Seleccione un evento', 'warning');
                }
            }

            function deleteEvent() {
                var row = $('#dg').datagrid('getSelected');
                if (!row) {
                    $.messager.alert('Aviso', 'Seleccione un evento', 'warning');
                    return;
                }
                if (row.status !== 'draft') {
                    $.messager.alert('Aviso', 'Solo se pueden eliminar eventos en borrador', 'warning');
                    return;
                }

                $.messager.confirm('Confirmar', '¿Desea eliminar este evento?', function (r) {
                    if (r) {
                        $.ajax({
                            url: '{{ url('events') }}/' + row.id,
                            type: 'DELETE',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function (result) {
                                if (result.success) {
                                    $('#dg').datagrid('reload');
                                    $.messager.show({ title: 'Éxito', msg: result.message });
                                }
                            }
                        });
                    }
                });
            }
        </script>
    @endpush
@endsection