@extends('layouts.app')

@section('title', 'Familias')
@section('page-title', 'Administración de Familias')

@section('content')
    <div class="card">
        <div class="card-body">
            <table id="dg" title="Familias" class="easyui-datagrid" style="width:100%;height:450px"
                url="{{ route('families.data') }}" toolbar="#toolbar" pagination="true" rownumbers="true" fitColumns="true"
                singleSelect="true">
                <thead>
                    <tr>
                        <th field="id" width="50" sortable="true">ID</th>
                        <th field="name" width="200" sortable="true">Nombre Familia</th>
                        <th field="budget_type" width="100" formatter="formatBudget">Tipo Presupuesto</th>
                        <th field="billing_name" width="200">Razón Social Fact.</th>
                        <th field="billing_ruc" width="100">RUC Fact.</th>
                        <th field="students_count" width="80" align="center">Alumnos</th>
                    </tr>
                </thead>
            </table>
            <div id="toolbar">
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true"
                    onclick="newItem()">Nueva Familia</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true"
                    onclick="editItem()">Editar</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true"
                    onclick="destroyItem()">Eliminar</a>
            </div>

            <!-- Dialog -->
            <div id="dlg" class="easyui-dialog" style="width:500px" closed="true" buttons="#dlg-buttons" modal="true">
                <form id="fm" method="post" novalidate style="margin:0;padding:20px">
                    <div style="margin-bottom:10px">
                        <input name="name" class="easyui-textbox" required="true" label="Nombre Familia:"
                            style="width:100%">
                    </div>
                    <div style="margin-bottom:10px">
                        <select class="easyui-combobox" name="budget_type" label="Presupuesto:" style="width:100%"
                            required="true">
                            <option value="family">Familiar (Unificado)</option>
                            <option value="individual">Individual (Por Alumno)</option>
                        </select>
                    </div>
                    <hr>
                    <div style="margin-bottom:10px">
                        <h6>Datos de Facturación</h6>
                    </div>
                    <div style="margin-bottom:10px">
                        <input name="billing_name" class="easyui-textbox" label="Razón Social:" style="width:100%">
                    </div>
                    <div style="margin-bottom:10px">
                        <input name="billing_ruc" class="easyui-textbox" label="RUC:" style="width:100%">
                    </div>
                    <div style="margin-bottom:10px">
                        <input name="billing_email" class="easyui-textbox" validType="email" label="Email Envío:"
                            style="width:100%">
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
        function formatBudget(value) {
            return value == 'family' ? 'Familiar' : 'Individual';
        }
        function newItem() {
            $('#dlg').dialog('open').dialog('center').dialog('setTitle', 'Nueva Familia');
            $('#fm').form('clear');
            $('#fm').form('load', { budget_type: 'family' });
            url = "{{ route('families.store') }}";
        }
        function editItem() {
            var row = $('#dg').datagrid('getSelected');
            if (row) {
                $('#dlg').dialog('open').dialog('center').dialog('setTitle', 'Editar Familia');
                $('#fm').form('load', row);
                url = "{{ url('/families') }}/" + row.id;
            }
        }
        function saveItem() {
            var row = $('#dg').datagrid('getSelected');
            var method = (url.indexOf(row ? row.id : '') > -1) ? 'PUT' : 'POST';

            // Form submit doesn't support PUT easily, use AJAX
            if (!$('#fm').form('validate')) return;

            var data = {};
            $('#fm').serializeArray().forEach(function (item) {
                data[item.name] = item.value;
            });

            $.ajax({
                url: url,
                method: editingId ? 'PUT' : 'POST', // Wait, I didn't set editingId here
                data: data,
                success: function (result) {
                    if (result.success) {
                        $('#dlg').dialog('close');
                        $('#dg').datagrid('reload');
                    } else {
                        $.messager.show({ title: 'Error', msg: result.message });
                    }
                }
            });
        }

        // Actually, I'll use the same pattern as Customer for consistency
        var editingId = null;
        function editItem() {
            var row = $('#dg').datagrid('getSelected');
            if (row) {
                editingId = row.id;
                $('#dlg').dialog('open').dialog('center').dialog('setTitle', 'Editar Familia');
                $('#fm').form('load', row);
                url = "{{ url('/families') }}/" + row.id;
            }
        }
        function newItem() {
            editingId = null;
            $('#dlg').dialog('open').dialog('center').dialog('setTitle', 'Nueva Familia');
            $('#fm').form('clear');
            $('#fm').form('load', { budget_type: 'family' });
            url = "{{ route('families.store') }}";
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
                },
                error: function (xhr) {
                    $.messager.alert('Error', xhr.responseJSON?.message || 'Error al guardar');
                }
            });
        }

        function destroyItem() {
            var row = $('#dg').datagrid('getSelected');
            if (row) {
                $.messager.confirm('Confirmar', '¿Está seguro de eliminar esta familia?', function (r) {
                    if (r) {
                        $.ajax({
                            url: "{{ url('/families') }}/" + row.id,
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