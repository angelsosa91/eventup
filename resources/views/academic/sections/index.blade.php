@extends('layouts.app')

@section('title', 'Secciones')
@section('page-title', 'Administración de Secciones')

@section('content')
    <div class="card">
        <div class="card-body">
            <table id="dg" title="Secciones" class="easyui-datagrid" style="width:100%;height:450px"
                url="{{ route('academic.sections.data') }}" toolbar="#toolbar" pagination="true" rownumbers="true"
                fitColumns="true" singleSelect="true">
                <thead>
                    <tr>
                        <th field="id" width="50" sortable="true">ID</th>
                        <th field="name" width="200" sortable="true">Nombre de la Sección</th>
                        <th field="students_count" width="100">Alumnos</th>
                        <th field="is_active" width="100" formatter="formatActive">Estado</th>
                    </tr>
                </thead>
            </table>
            <div id="toolbar">
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true"
                    onclick="newItem()">Nueva Sección</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true"
                    onclick="editItem()">Editar</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true"
                    onclick="destroyItem()">Eliminar</a>
                <div style="float:right; padding-right:10px">
                    <input id="search" class="easyui-textbox" data-options="prompt:'Buscar...'" style="width:200px">
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-search" onclick="doSearch()"></a>
                </div>
            </div>

            <!-- Dialog -->
            <div id="dlg" class="easyui-dialog" style="width:400px" closed="true" buttons="#dlg-buttons" modal="true">
                <form id="fm" method="post" novalidate style="margin:0;padding:20px 50px">
                    <div style="margin-bottom:10px">
                        <input name="name" class="easyui-textbox" required="true" label="Nombre:" style="width:100%">
                    </div>
                    <div style="margin-bottom:10px">
                        <select class="easyui-combobox" name="is_active" label="Estado:" style="width:100%">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </form>
            </div>
            <div id="dlg-buttons">
                <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveItem()"
                    style="width:90px">Guardar</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel"
                    onclick="javascript:$('#dlg').dialog('close')" style="width:90px">Cancelar</a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="text/javascript">
        var url;
        function newItem() {
            $('#dlg').dialog('open').dialog('center').dialog('setTitle', 'Nueva Sección');
            $('#fm').form('clear');
            $('#fm').form('load', { is_active: 1 });
            url = "{{ route('academic.sections.store') }}";
        }
        function editItem() {
            var row = $('#dg').datagrid('getSelected');
            if (row) {
                $('#dlg').dialog('open').dialog('center').dialog('setTitle', 'Editar Sección');
                $('#fm').form('load', row);
                url = "{{ url('academic/sections') }}/" + row.id;
                // Laravel needs PUT for update
                $('<input>').attr({ type: 'hidden', name: '_method', value: 'PUT' }).appendTo('#fm');
            }
        }
        function saveItem() {
            $('#fm').form('submit', {
                url: url,
                onSubmit: function () {
                    return $(this).form('validate');
                },
                success: function (result) {
                    var result = JSON.parse(result);
                    if (result.errors) {
                        $.messager.show({
                            title: 'Error',
                            msg: 'Error al guardar los datos'
                        });
                    } else {
                        $('#dlg').dialog('close');
                        $('#dg').datagrid('reload');
                    }
                }
            });
        }
        function destroyItem() {
            var row = $('#dg').datagrid('getSelected');
            if (row) {
                $.messager.confirm('Confirmar', '¿Está seguro de eliminar esta sección?', function (r) {
                    if (r) {
                        $.post("{{ url('academic/sections') }}/" + row.id, { _method: 'DELETE' }, function (result) {
                            if (result.success) {
                                $('#dg').datagrid('reload');
                            } else {
                                $.messager.show({
                                    title: 'Error',
                                    msg: result.message
                                });
                            }
                        }, 'json');
                    }
                });
            }
        }
        function doSearch() {
            $('#dg').datagrid('load', {
                search: $('#search').val()
            });
        }
        function formatActive(val, row) {
            if (val == 1) {
                return '<span class="badge bg-success">Activo</span>';
            } else {
                return '<span class="badge bg-danger">Inactivo</span>';
            }
        }
    </script>
@endpush