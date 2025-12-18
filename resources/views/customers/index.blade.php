@extends('layouts.app')

@section('title', 'Clientes - Neo ERP')
@section('page-title', 'Gestion de Clientes')

@section('content')
    <div class="card">
        <div class="card-body">
            <!-- Toolbar -->
            <div id="toolbar" style="padding:5px;">
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true"
                    onclick="newCustomer()">Nuevo Cliente</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true"
                    onclick="editCustomer()">Editar</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true"
                    onclick="deleteCustomer()">Eliminar</a>
                <span class="ms-3">
                    <input id="searchbox" class="easyui-searchbox" style="width:250px"
                        data-options="searcher:doSearch,prompt:'Buscar cliente...'">
                </span>
            </div>

            <!-- DataGrid -->
            <table id="dg-customers" class="easyui-datagrid" style="width:100%;height:700px" data-options="
                    url:'{{ route('customers.data') }}',
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
                        <th data-options="field:'id',width:50,sortable:true">ID</th>
                        <th data-options="field:'name',width:150,sortable:true">Nombre Completo</th>
                        <th data-options="field:'grade_name',width:100">Curso</th>
                        <th data-options="field:'section_name',width:60">Secc.</th>
                        <th data-options="field:'ruc',width:100,sortable:true">RUC/CI</th>
                        <th data-options="field:'email',width:150">Email</th>
                        <th data-options="field:'family_name',width:120">Familia</th>
                        <th data-options="field:'is_active',width:80,align:'center',formatter:formatStatus">Estado</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <!-- Dialog para crear/editar cliente -->
    <div id="dlg-customer" class="easyui-dialog" style="width:850px;padding:20px" closed="true" buttons="#dlg-buttons"
        modal="true">
        <form id="fm-customer" method="post">
            <input type="hidden" name="id" id="customer-id">

            <div class="tabs easyui-tabs" style="width:100%;height:auto">
                <div title="Datos Personales" style="padding:15px">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombres <span class="text-danger">*</span></label>
                            <input class="easyui-textbox" name="first_name" id="customer-first-name" style="width:100%"
                                data-options="required:true">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Apellidos <span class="text-danger">*</span></label>
                            <input class="easyui-textbox" name="last_name" id="customer-last-name" style="width:100%"
                                data-options="required:true">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Nombre Completo (Sistema) <span class="text-danger">*</span></label>
                            <input class="easyui-textbox" name="name" id="customer-name" style="width:100%"
                                data-options="required:true" placeholder="Se autocompleta con nombres y apellidos">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">RUC / CI</label>
                            <input class="easyui-textbox" name="ruc" id="customer-ruc" style="width:100%">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Fecha Nacimiento</label>
                            <input class="easyui-datebox" name="birth_date" id="customer-birth-date" style="width:100%"
                                data-options="formatter:myformatter,parser:myparser">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Celular</label>
                            <input class="easyui-textbox" name="mobile" id="customer-mobile" style="width:100%">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Email</label>
                            <input class="easyui-textbox" name="email" id="customer-email" style="width:100%"
                                data-options="validType:'email'">
                        </div>
                    </div>
                </div>

                <div title="Académica & Familia" style="padding:15px">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Curso</label>
                            <input class="easyui-combobox" name="grade_id" style="width:100%"
                                data-options="url:'{{ route('academic.grades.list') }}',method:'get',valueField:'id',textField:'name'">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sección</label>
                            <input class="easyui-combobox" name="section_id" style="width:100%"
                                data-options="url:'{{ route('academic.sections.list') }}',method:'get',valueField:'id',textField:'name'">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Turno</label>
                            <input class="easyui-combobox" name="shift_id" style="width:100%"
                                data-options="url:'{{ route('academic.shifts.list') }}',method:'get',valueField:'id',textField:'name'">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bachillerato</label>
                            <input class="easyui-combobox" name="bachillerato_id" style="width:100%"
                                data-options="url:'{{ route('academic.bachilleratos.list') }}',method:'get',valueField:'id',textField:'name'">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Familia</label>
                            <input class="easyui-combobox" name="family_id" id="customer-family-id" style="width:100%"
                                data-options="url:'/families/list',method:'get',valueField:'id',textField:'name',prompt:'Seleccione familia...'">
                        </div>
                    </div>
                </div>

                <div title="Otros Datos" style="padding:15px">
                    <div class="mb-3">
                        <label class="form-label">Dirección</label>
                        <input class="easyui-textbox" name="address" id="customer-address" style="width:100%"
                            data-options="multiline:true,height:60">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ciudad</label>
                            <input class="easyui-textbox" name="city" id="customer-city" style="width:100%">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">País</label>
                            <input class="easyui-textbox" name="country" id="customer-country" style="width:100%"
                                value="Paraguay">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Límite de Crédito (Gs.)</label>
                            <input class="easyui-numberbox" name="credit_limit" id="customer-credit-limit"
                                style="width:100%" data-options="min:0,precision:0,groupSeparator:'.'">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Días de Crédito</label>
                            <input class="easyui-numberbox" name="credit_days" id="customer-credit-days" style="width:100%"
                                data-options="min:0,precision:0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <input type="checkbox" name="is_active" id="customer-active" value="1" checked>
                        <label for="customer-active">Cuenta Activa</label>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div id="dlg-buttons">
        <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveCustomer()"
            style="width:90px">Guardar</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel"
            onclick="$('#dlg-customer').dialog('close')" style="width:90px">Cancelar</a>
    </div>
@endsection

@push('scripts')
    <script>
        var editingId = null;

        function myformatter(date){
            var y = date.getFullYear();
            var m = date.getMonth()+1;
            var d = date.getDate();
            return y+'-'+(m<10?('0'+m):m)+'-'+(d<10?('0'+d):d);
        }
        function myparser(s){
            if (!s) return new Date();
            var ss = (s.split('-'));
            var y = parseInt(ss[0],10);
            var m = parseInt(ss[1],10);
            var d = parseInt(ss[2],10);
            if (!isNaN(y) && !isNaN(m) && !isNaN(d)){
                return new Date(y,m-1,d);
            } else {
                return new Date();
            }
        }

        $(function(){
            $('#customer-first-name, #customer-last-name').on('change keyup', function(){
                var fname = $('#customer-first-name').textbox('getValue');
                var lname = $('#customer-last-name').textbox('getValue');
                $('#customer-name').textbox('setValue', (fname + ' ' + lname).trim());
            });
        });

        function formatStatus(value) {
            if (value) {
                return '<span class="badge bg-success">Activo</span>';
            } else {
                return '<span class="badge bg-danger">Inactivo</span>';
            }
        }

        function doSearch(value) {
            $('#dg-customers').datagrid('load', {
                search: value
            });
        }

        function newCustomer() {
            editingId = null;
            $('#dlg-customer').dialog('open').dialog('setTitle', 'Nuevo Alumno/Cliente');
            $('#fm-customer').form('clear');
            $('#customer-active').prop('checked', true);
            $('#customer-country').textbox('setValue', 'Paraguay');
            $('#customer-credit-limit').numberbox('setValue', 0);
            $('#customer-credit-days').numberbox('setValue', 0);
        }

        function editCustomer() {
            var row = $('#dg-customers').datagrid('getSelected');
            if (row) {
                editingId = row.id;
                $('#dlg-customer').dialog('open').dialog('setTitle', 'Editar Alumno/Cliente');
                $('#fm-customer').form('clear');

                $.get('{{ url("/customers") }}/' + row.id, function (data) {
                    $('#fm-customer').form('load', data);
                    $('#customer-active').prop('checked', data.is_active == 1);
                    if (data.birth_date) {
                        $('#customer-birth-date').datebox('setValue', data.birth_date.split('T')[0]);
                    }
                });
            } else {
                $.messager.alert('Aviso', 'Seleccione un alumno para editar', 'warning');
            }
        }

        function saveCustomer() {
            if (!$('#fm-customer').form('validate')) {
                return;
            }

            var formData = $('#fm-customer').serializeArray().reduce(function(obj, item) {
                obj[item.name] = item.value;
                return obj;
            }, {});
            
            formData.is_active = $('#customer-active').is(':checked') ? 1 : 0;

            var url = editingId ? '{{ url("/customers") }}/' + editingId : '{{ route("customers.store") }}';
            var method = editingId ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                method: method,
                data: formData,
                success: function (result) {
                    if (result.success) {
                        $('#dlg-customer').dialog('close');
                        $('#dg-customers').datagrid('reload');
                        $.messager.show({
                            title: 'Éxito',
                            msg: result.message,
                            timeout: 3000,
                            showType: 'slide'
                        });
                    } else {
                        $.messager.alert('Error', result.message, 'error');
                    }
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

        function deleteCustomer() {
            var row = $('#dg-customers').datagrid('getSelected');
            if (row) {
                $.messager.confirm('Confirmar', '¿Está seguro de eliminar el registro de "' + row.name + '"?', function (r) {
                    if (r) {
                        $.ajax({
                            url: '{{ url("/customers") }}/' + row.id,
                            method: 'DELETE',
                            success: function (result) {
                                if (result.success) {
                                    $('#dg-customers').datagrid('reload');
                                    $.messager.show({
                                        title: 'Éxito',
                                        msg: result.message,
                                        timeout: 3000,
                                        showType: 'slide'
                                    });
                                } else {
                                    $.messager.alert('Error', result.message, 'error');
                                }
                            },
                            error: function (xhr) {
                                $.messager.alert('Error', xhr.responseJSON?.message || 'Error al eliminar', 'error');
                            }
                        });
                    }
                });
            } else {
                $.messager.alert('Aviso', 'Seleccione un alumno para eliminar', 'warning');
            }
        }
    </script>
    </script>
@endpush