@extends('layouts.app')

@section('title', 'Padres / Tutores')
@section('page-title', 'Administración de Padres y Tutores')

@section('content')
    <div class="card">
        <div class="card-body">
            <table id="dg" title="Padres / Tutores" class="easyui-datagrid" style="width:100%;height:450px"
                url="{{ route('parents.data') }}" toolbar="#toolbar" pagination="true" rownumbers="true" fitColumns="true"
                singleSelect="true">
                <thead>
                    <tr>
                        <th field="id" width="50" sortable="true">ID</th>
                        <th field="first_name" width="150" sortable="true">Nombre</th>
                        <th field="last_name" width="150" sortable="true">Apellido</th>
                        <th field="document_number" width="100">Documento</th>
                        <th field="email" width="200">Email</th>
                        <th field="phone" width="100">Teléfono</th>
                        <th field="students_count" width="80" align="center">Hijos/Alumnos</th>
                    </tr>
                </thead>
            </table>
            <div id="toolbar">
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true"
                    onclick="newItem()">Nuevo Padre/Tutor</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true"
                    onclick="editItem()">Editar</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true"
                    onclick="destroyItem()">Eliminar</a>
            </div>

            <!-- Dialog -->
            <div id="dlg" class="easyui-dialog" style="width:600px" closed="true" buttons="#dlg-buttons" modal="true">
                <form id="fm" method="post" novalidate style="margin:0;padding:20px">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombres <span class="text-danger">*</span></label>
                            <input name="first_name" class="easyui-textbox" required="true" style="width:100%">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Apellidos <span class="text-danger">*</span></label>
                            <input name="last_name" class="easyui-textbox" required="true" style="width:100%">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Documento (CI/Pass):</label>
                            <input name="document_number" class="easyui-textbox" style="width:100%">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Celular:</label>
                            <input name="phone" class="easyui-textbox" style="width:100%">
                        </div>
                    </div>
                    <div style="margin-bottom:10px">
                        <label class="form-label">Email:</label>
                        <input name="email" class="easyui-textbox" validType="email" style="width:100%">
                    </div>
                    <div style="margin-bottom:10px">
                        <label class="form-label">Dirección:</label>
                        <input name="address" class="easyui-textbox" data-options="multiline:true,height:60"
                            style="width:100%">
                    </div>
                    <div style="margin-bottom:10px">
                        <label class="form-label">Profesión / Ocupación:</label>
                        <input name="occupation" class="easyui-textbox" style="width:100%">
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
        var editingId = null;
        var url;

        function newItem() {
            editingId = null;
            $('#dlg').dialog('open').dialog('center').dialog('setTitle', 'Nuevo Padre/Tutor');
            $('#fm').form('clear');
            url = "{{ route('parents.store') }}";
        }

        function editItem() {
            var row = $('#dg').datagrid('getSelected');
            if (row) {
                editingId = row.id;
                $('#dlg').dialog('open').dialog('center').dialog('setTitle', 'Editar Padre/Tutor');
                $('#fm').form('clear');
                $.get("{{ url('/parents') }}/" + row.id, function (data) {
                    $('#fm').form('load', data);
                });
                url = "{{ url('/parents') }}/" + row.id;
            }
        }

        function saveItem() {
            if (!$('#fm').form('validate')) return;
            var data = {};
            $('#fm').serializeArray().forEach(function (item) {
                data[item.name] = item.value;
            });
            var method = editingId ? 'PUT' : 'POST';
            $.ajax({
                url: url,
                method: method,
                data: data,
                success: function (result) {
                    $('#dlg').dialog('close');
                    $('#dg').datagrid('reload');
                    $.messager.show({ title: 'Éxito', msg: result.message });
                },
                error: function (xhr) {
                    $.messager.alert('Error', xhr.responseJSON?.message || 'Error al guardar');
                }
            });
        }

        function destroyItem() {
            var row = $('#dg').datagrid('getSelected');
            if (row) {
                $.messager.confirm('Confirmar', '¿Está seguro de eliminar este registro?', function (r) {
                    if (r) {
                        $.ajax({
                            url: "{{ url('/parents') }}/" + row.id,
                            method: 'DELETE',
                            success: function (result) {
                                $('#dg').datagrid('reload');
                            },
                            error: function (xhr) {
                                $.messager.alert('Error', xhr.responseJSON?.message || 'Error al eliminar');
                            }
                        });
                    }
                });
            }
        }
    </script>
@endpush