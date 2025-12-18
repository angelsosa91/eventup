@extends('layouts.app')

@section('title', 'Cursos')
@section('page-title', 'Administración de Cursos')

@section('content')
    <div class="card">
        <div class="card-body">
            <table id="dg" title="Cursos" class="easyui-datagrid" style="width:100%;height:700px" data-options="
                            url:'{{ route('academic.grades.data') }}',
                            method:'get',
                            toolbar:'#toolbar',
                            pagination:true,
                            pageSize:20,
                            pageList:[10,20,50,100],
                            rownumbers:true,
                            singleSelect:true,
                            fitColumns:true,
                            sortName:'id',
                            sortOrder:'desc',
                            remoteSort:true
                        ">
                <thead>
                    <tr>
                        <th field="id" width="50" sortable="true">ID</th>
                        <th field="name" width="200" sortable="true">Nombre del Curso</th>
                        <th field="students_count" width="100">Alumnos</th>
                        <th field="is_active" width="100" formatter="formatActive">Estado</th>
                    </tr>
                </thead>
            </table>
            <div id="toolbar">
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true"
                    onclick="newItem()">Nuevo Curso</a>
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
                    @csrf
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
        var editingId = null;

        function newItem() {
            editingId = null;
            $('#dlg').dialog('open').dialog('center').dialog('setTitle', 'Nuevo Curso');
            $('#fm').form('clear');
            $('#fm').form('load', { is_active: 1 });
            url = "{{ route('academic.grades.store') }}";
        }

        function editItem() {
            var row = $('#dg').datagrid('getSelected');
            if (row) {
                editingId = row.id;
                $('#dlg').dialog('open').dialog('center').dialog('setTitle', 'Editar Curso');
                $('#fm').form('load', row);
                url = "{{ url('academic/grades') }}/" + row.id;
            }
        }

        function saveItem() {
            if (!$('#fm').form('validate')) return;

            var data = {};
            $('#fm').serializeArray().forEach(function (item) {
                data[item.name] = item.value;
            });

            $.ajax({
                url: url,
                method: editingId ? 'PUT' : 'POST',
                data: data,
                success: function (result) {
                    $('#dlg').dialog('close');
                    $('#dg').datagrid('reload');
                    $.messager.show({
                        title: 'Éxito',
                        msg: result.message || 'Datos guardados correctamente'
                    });
                },
                error: function (xhr) {
                    var errors = xhr.responseJSON?.errors;
                    if (errors) {
                        var msg = Object.values(errors).flat().join('<br>');
                        $.messager.alert('Error de validación', msg, 'error');
                    } else {
                        $.messager.alert('Error', xhr.responseJSON?.message || 'Error al guardar', 'error');
                    }
                }
            });
        }

        function destroyItem() {
            var row = $('#dg').datagrid('getSelected');
            if (row) {
                $.messager.confirm('Confirmar', '¿Está seguro de eliminar este curso?', function (r) {
                    if (r) {
                        $.ajax({
                            url: "{{ url('academic/grades') }}/" + row.id,
                            method: 'DELETE',
                            success: function (result) {
                                if (result.success) {
                                    $('#dg').datagrid('reload');
                                } else {
                                    $.messager.show({
                                        title: 'Error',
                                        msg: result.message
                                    });
                                }
                            },
                            error: function (xhr) {
                                $.messager.alert('Error', xhr.responseJSON?.message || 'Error al eliminar');
                            }
                        });
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