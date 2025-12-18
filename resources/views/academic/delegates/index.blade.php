@extends('layouts.app')

@section('title', 'Delegados')
@section('page-title', 'Administración de Delegados')

@section('content')
    <div class="card">
        <div class="card-body">
            <table id="dg" title="Delegados" class="easyui-datagrid" style="width:100%;height:700px" data-options="
                                                url:'{{ route('academic.delegates.data') }}',
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
                        <th field="name" width="200" sortable="true">Nombre</th>
                        <th field="email" width="200">Email</th>
                        <th field="phone" width="100">Teléfono</th>
                        <th field="position" width="150">Cargo/Posición</th>
                    </tr>
                </thead>
            </table>
            <div id="toolbar">
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true"
                    onclick="newItem()">Nuevo Delegado</a>
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
                        <input name="email" class="easyui-textbox" validType="email" label="Email:" style="width:100%">
                    </div>
                    <div style="margin-bottom:10px">
                        <input name="phone" class="easyui-textbox" label="Teléfono:" style="width:100%">
                    </div>
                    <div style="margin-bottom:10px">
                        <input name="position" class="easyui-textbox" label="Cargo:" style="width:100%">
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
            $('#dlg').dialog('open').dialog('center').dialog('setTitle', 'Nuevo Delegado');
            $('#fm').form('clear');
            url = "{{ route('academic.delegates.store') }}";
        }

        function editItem() {
            var row = $('#dg').datagrid('getSelected');
            if (row) {
                editingId = row.id;
                $('#dlg').dialog('open').dialog('center').dialog('setTitle', 'Editar Delegado');
                $('#fm').form('load', row);
                url = "{{ url('academic/delegates') }}/" + row.id;
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
                $.messager.confirm('Confirmar', '¿Está seguro de eliminar este delegado?', function (r) {
                    if (r) {
                        $.ajax({
                            url: "{{ url('academic/delegates') }}/" + row.id,
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
    </script>
@endpush