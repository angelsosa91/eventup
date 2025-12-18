@extends('layouts.app')

@section('title', 'Configuración de la Empresa')
@section('page-title', 'Configuración de la Empresa')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Datos de la Empresa</h5>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('settings.company.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <!-- Información Básica -->
                <div class="col-md-8">
                    <h6 class="mb-3 text-primary">Información Básica</h6>

                    <div class="mb-3">
                        <label class="form-label">Nombre de la Empresa <span class="text-danger">*</span></label>
                        <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror"
                               value="{{ old('company_name', $settings->company_name) }}" required>
                        @error('company_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">RUC</label>
                            <input type="text" name="ruc" class="form-control @error('ruc') is-invalid @enderror"
                                   value="{{ old('ruc', $settings->ruc) }}">
                            @error('ruc')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone', $settings->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $settings->email) }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sitio Web</label>
                            <input type="url" name="website" class="form-control @error('website') is-invalid @enderror"
                                   value="{{ old('website', $settings->website) }}" placeholder="https://ejemplo.com">
                            @error('website')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Dirección</label>
                        <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2">{{ old('address', $settings->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Slogan</label>
                        <textarea name="slogan" class="form-control @error('slogan') is-invalid @enderror" rows="2" placeholder="Frase o lema de la empresa">{{ old('slogan', $settings->slogan) }}</textarea>
                        @error('slogan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Logo -->
                <div class="col-md-4">
                    <h6 class="mb-3 text-primary">Logo de la Empresa</h6>

                    @if($settings->logo_path)
                        <div class="text-center mb-3">
                            <img src="{{ asset('storage/' . $settings->logo_path) }}"
                                 alt="Logo" class="img-fluid border rounded" style="max-height: 200px;">
                            <form action="{{ route('settings.company.delete-logo') }}" method="POST" class="mt-2">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('¿Está seguro de eliminar el logo?')">
                                    <i class="bi bi-trash"></i> Eliminar Logo
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="text-center mb-3">
                            <div class="border rounded p-4 bg-light">
                                <i class="bi bi-image" style="font-size: 3rem; color: #ccc;"></i>
                                <p class="text-muted mb-0">Sin logo</p>
                            </div>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Cargar Logo</label>
                        <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/jpeg,image/png,image/jpg">
                        <small class="text-muted">JPG, JPEG o PNG. Máximo 2MB.</small>
                        @error('logo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <!-- Configuración Regional y de Formato -->
            <div class="row">
                <div class="col-md-12">
                    <h6 class="mb-3 text-primary">Configuración Regional</h6>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Moneda <span class="text-danger">*</span></label>
                    <select name="currency" class="form-select @error('currency') is-invalid @enderror" required>
                        <option value="PYG" {{ old('currency', $settings->currency) == 'PYG' ? 'selected' : '' }}>Guaraníes (PYG)</option>
                        <option value="USD" {{ old('currency', $settings->currency) == 'USD' ? 'selected' : '' }}>Dólares (USD)</option>
                        <option value="EUR" {{ old('currency', $settings->currency) == 'EUR' ? 'selected' : '' }}>Euros (EUR)</option>
                        <option value="ARS" {{ old('currency', $settings->currency) == 'ARS' ? 'selected' : '' }}>Pesos Argentinos (ARS)</option>
                        <option value="BRL" {{ old('currency', $settings->currency) == 'BRL' ? 'selected' : '' }}>Reales (BRL)</option>
                    </select>
                    @error('currency')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Símbolo de Moneda <span class="text-danger">*</span></label>
                    <input type="text" name="currency_symbol" class="form-control @error('currency_symbol') is-invalid @enderror"
                           value="{{ old('currency_symbol', $settings->currency_symbol) }}" required>
                    @error('currency_symbol')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Decimales <span class="text-danger">*</span></label>
                    <select name="decimal_places" class="form-select @error('decimal_places') is-invalid @enderror" required>
                        <option value="0" {{ old('decimal_places', $settings->decimal_places) == 0 ? 'selected' : '' }}>0 decimales</option>
                        <option value="2" {{ old('decimal_places', $settings->decimal_places) == 2 ? 'selected' : '' }}>2 decimales</option>
                        <option value="3" {{ old('decimal_places', $settings->decimal_places) == 3 ? 'selected' : '' }}>3 decimales</option>
                        <option value="4" {{ old('decimal_places', $settings->decimal_places) == 4 ? 'selected' : '' }}>4 decimales</option>
                    </select>
                    @error('decimal_places')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Formato de Fecha <span class="text-danger">*</span></label>
                    <select name="date_format" class="form-select @error('date_format') is-invalid @enderror" required>
                        <option value="d/m/Y" {{ old('date_format', $settings->date_format) == 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY (31/12/2025)</option>
                        <option value="m/d/Y" {{ old('date_format', $settings->date_format) == 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY (12/31/2025)</option>
                        <option value="Y-m-d" {{ old('date_format', $settings->date_format) == 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD (2025-12-31)</option>
                    </select>
                    @error('date_format')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Zona Horaria <span class="text-danger">*</span></label>
                    <select name="timezone" class="form-select @error('timezone') is-invalid @enderror" required>
                        <option value="America/Asuncion" {{ old('timezone', $settings->timezone) == 'America/Asuncion' ? 'selected' : '' }}>América/Asunción (Paraguay)</option>
                        <option value="America/Buenos_Aires" {{ old('timezone', $settings->timezone) == 'America/Buenos_Aires' ? 'selected' : '' }}>América/Buenos Aires (Argentina)</option>
                        <option value="America/Sao_Paulo" {{ old('timezone', $settings->timezone) == 'America/Sao_Paulo' ? 'selected' : '' }}>América/São Paulo (Brasil)</option>
                        <option value="America/Santiago" {{ old('timezone', $settings->timezone) == 'America/Santiago' ? 'selected' : '' }}>América/Santiago (Chile)</option>
                    </select>
                    @error('timezone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr class="my-4">

            <!-- Configuración de Negocio -->
            <div class="row">
                <div class="col-md-12">
                    <h6 class="mb-3 text-primary">Configuración de Negocio</h6>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Umbral de Stock Bajo <span class="text-danger">*</span></label>
                    <input type="number" name="low_stock_threshold" class="form-control @error('low_stock_threshold') is-invalid @enderror"
                           value="{{ old('low_stock_threshold', $settings->low_stock_threshold) }}" min="0" required>
                    <small class="text-muted">Cantidad mínima para alertar de stock bajo</small>
                    @error('low_stock_threshold')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <div class="form-check mt-4">
                        <input type="hidden" name="invoice_requires_tax_id" value="0">
                        <input type="checkbox" name="invoice_requires_tax_id" value="1"
                               class="form-check-input @error('invoice_requires_tax_id') is-invalid @enderror"
                               id="invoice_requires_tax_id"
                               {{ old('invoice_requires_tax_id', $settings->invoice_requires_tax_id) ? 'checked' : '' }}>
                        <label class="form-check-label" for="invoice_requires_tax_id">
                            Requerir RUC del cliente en facturas
                        </label>
                        @error('invoice_requires_tax_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <div class="text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Guardar Configuración
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
