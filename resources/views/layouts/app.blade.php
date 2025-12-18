<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Neo ERP')</title>

    <!-- Favicons -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#3498db">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- jEasyUI CSS (local) -->
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/easyui/themes/default/easyui.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/easyui/themes/icon.css') }}">
    
    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 70px;
            --primary-color: #2c3e50;
            --accent-color: #3498db;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #1a252f 0%, #2c3e50 100%);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            overflow-x: hidden;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(0,0,0,0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.2);
            border-radius: 3px;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar.collapsed .brand-text,
        .sidebar.collapsed .menu-text {
            opacity: 0;
            visibility: hidden;
        }

        .sidebar.collapsed .submenu {
            display: none !important;
        }

        .sidebar.collapsed .nav-menu .has-submenu > a i.chevron {
            display: none;
        }

        .sidebar .brand {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            position: relative;
        }

        .sidebar .brand h4 {
            color: #fff;
            margin: 0;
            font-size: 1.3rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .sidebar .brand h4 i {
            color: var(--accent-color);
        }

        .sidebar .brand-text {
            transition: all 0.3s;
        }

        .toggle-sidebar {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            background: var(--accent-color);
            border: none;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .toggle-sidebar:hover {
            background: #2980b9;
            transform: translateY(-50%) scale(1.1);
        }

        .nav-menu {
            padding: 10px 0;
            margin: 0;
            list-style: none;
        }

        .menu-section-title {
            color: rgba(255,255,255,0.5);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 15px 20px 8px 20px;
            margin-top: 10px;
            transition: all 0.3s;
        }

        .sidebar.collapsed .menu-section-title {
            opacity: 0;
            height: 0;
            padding: 0;
            margin: 0;
            overflow: hidden;
        }

        .nav-menu li a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: all 0.3s;
            position: relative;
            white-space: nowrap;
        }

        .nav-menu li a::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 3px;
            background: var(--accent-color);
            transform: scaleY(0);
            transition: transform 0.3s;
        }

        .nav-menu li a:hover,
        .nav-menu li a.active {
            background: rgba(52, 152, 219, 0.15);
            color: #fff;
        }

        .nav-menu li a:hover::before,
        .nav-menu li a.active::before {
            transform: scaleY(1);
        }

        .nav-menu li a i:first-child {
            margin-right: 12px;
            font-size: 1.2rem;
            min-width: 24px;
            text-align: center;
            color: var(--accent-color);
            transition: all 0.3s;
        }

        .nav-menu li a:hover i:first-child,
        .nav-menu li a.active i:first-child {
            transform: scale(1.1);
            color: #5dade2;
        }

        .menu-text {
            flex: 1;
            transition: all 0.3s;
        }

        .submenu {
            list-style: none;
            padding-left: 0;
            margin: 0;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: rgba(0,0,0,0.1);
        }

        .submenu.show {
            max-height: 500px;
        }

        .submenu li a {
            padding-left: 56px;
            font-size: 0.9rem;
        }

        .submenu li a i:first-child {
            font-size: 1rem;
        }

        .has-submenu > a {
            position: relative;
        }

        .has-submenu > a i.chevron {
            margin-left: auto;
            margin-right: 0;
            font-size: 0.9rem;
            transition: transform 0.3s;
            color: rgba(255,255,255,0.5);
        }

        .has-submenu.open > a i.chevron {
            transform: rotate(90deg);
        }

        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            background: #f8f9fa;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }

        .top-navbar {
            background: #fff;
            padding: 15px 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .content-wrapper {
            padding: 20px;
        }

        .card {
            border: none;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }

        /* jEasyUI customizations */
        .datagrid-header td,
        .datagrid-body td {
            font-size: 13px;
        }

        .panel-title {
            font-weight: 600;
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.mobile-open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="brand">
            <h4>
                <i class="bi bi-box-seam"></i>
                <span class="brand-text">Neo ERP</span>
            </h4>
            <button class="toggle-sidebar" onclick="toggleSidebar()">
                <i class="bi bi-list" id="toggle-icon"></i>
            </button>
        </div>
        <ul class="nav-menu">
            <!-- PRINCIPAL -->
            <li>
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>

            <!-- OPERACIONES -->
            <div class="menu-section-title">Operaciones</div>

            <!-- Ventas -->
            <li class="has-submenu {{ request()->routeIs('sales.*') || request()->routeIs('credit-notes.*') || request()->routeIs('remissions.*') ? 'open' : '' }}">
                <a href="javascript:void(0)" class="{{ request()->routeIs('sales.*') || request()->routeIs('credit-notes.*') || request()->routeIs('remissions.*') ? 'active' : '' }}">
                    <i class="bi bi-cart-check"></i>
                    <span class="menu-text">Ventas</span>
                    <i class="bi bi-chevron-right chevron"></i>
                </a>
                <ul class="submenu {{ request()->routeIs('sales.*') || request()->routeIs('credit-notes.*') || request()->routeIs('remissions.*') ? 'show' : '' }}">
                    <li>
                        <a href="{{ route('sales.create') }}" class="{{ request()->routeIs('sales.create') ? 'active' : '' }}">
                            <i class="bi bi-plus-circle"></i>
                            <span class="menu-text">Nueva Venta</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('sales.index') }}" class="{{ request()->routeIs('sales.index') ? 'active' : '' }}">
                            <i class="bi bi-list-ul"></i>
                            <span class="menu-text">Listado</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('credit-notes.index') }}" class="{{ request()->routeIs('credit-notes.*') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-minus"></i>
                            <span class="menu-text">Notas de Crédito</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('remissions.index') }}" class="{{ request()->routeIs('remissions.*') ? 'active' : '' }}">
                            <i class="bi bi-truck"></i>
                            <span class="menu-text">Remisiones</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Compras -->
            <li class="has-submenu {{ request()->routeIs('purchases.*') ? 'open' : '' }}">
                <a href="javascript:void(0)" class="{{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                    <i class="bi bi-bag"></i>
                    <span class="menu-text">Compras</span>
                    <i class="bi bi-chevron-right chevron"></i>
                </a>
                <ul class="submenu {{ request()->routeIs('purchases.*') ? 'show' : '' }}">
                    <li>
                        <a href="{{ route('purchases.create') }}" class="{{ request()->routeIs('purchases.create') ? 'active' : '' }}">
                            <i class="bi bi-plus-circle"></i>
                            <span class="menu-text">Nueva Compra</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('purchases.index') }}" class="{{ request()->routeIs('purchases.index') ? 'active' : '' }}">
                            <i class="bi bi-list-ul"></i>
                            <span class="menu-text">Listado</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Gastos -->
            <li class="has-submenu {{ request()->routeIs('expenses.*', 'expense-categories.*') ? 'open' : '' }}">
                <a href="javascript:void(0)" class="{{ request()->routeIs('expenses.*', 'expense-categories.*') ? 'active' : '' }}">
                    <i class="bi bi-cash-stack"></i>
                    <span class="menu-text">Gastos</span>
                    <i class="bi bi-chevron-right chevron"></i>
                </a>
                <ul class="submenu {{ request()->routeIs('expenses.*', 'expense-categories.*') ? 'show' : '' }}">
                    <li>
                        <a href="{{ route('expenses.create') }}" class="{{ request()->routeIs('expenses.create') ? 'active' : '' }}">
                            <i class="bi bi-plus-circle"></i>
                            <span class="menu-text">Nuevo Gasto</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('expenses.index') }}" class="{{ request()->routeIs('expenses.index') ? 'active' : '' }}">
                            <i class="bi bi-list-ul"></i>
                            <span class="menu-text">Listado</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('expense-categories.index') }}" class="{{ request()->routeIs('expense-categories.*') ? 'active' : '' }}">
                            <i class="bi bi-tags"></i>
                            <span class="menu-text">Categorías</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- INVENTARIO & PRODUCTOS -->
            <div class="menu-section-title">Inventario</div>

            <li class="has-submenu {{ request()->routeIs('products.*', 'categories.*', 'inventory-adjustments.*') ? 'open' : '' }}">
                <a href="javascript:void(0)" class="{{ request()->routeIs('products.*', 'categories.*', 'inventory-adjustments.*') ? 'active' : '' }}">
                    <i class="bi bi-box"></i>
                    <span class="menu-text">Productos</span>
                    <i class="bi bi-chevron-right chevron"></i>
                </a>
                <ul class="submenu {{ request()->routeIs('products.*', 'categories.*', 'inventory-adjustments.*') ? 'show' : '' }}">
                    <li>
                        <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}">
                            <i class="bi bi-box-seam"></i>
                            <span class="menu-text">Productos</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'active' : '' }}">
                            <i class="bi bi-tags"></i>
                            <span class="menu-text">Categorías</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('inventory-adjustments.index') }}" class="{{ request()->routeIs('inventory-adjustments.*') ? 'active' : '' }}">
                            <i class="bi bi-sliders"></i>
                            <span class="menu-text">Ajustes</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- CONTACTOS -->
            <div class="menu-section-title">Contactos</div>

            <li>
                <a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span class="menu-text">Clientes</span>
                </a>
            </li>
            <li>
                <a href="{{ route('suppliers.index') }}" class="{{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                    <i class="bi bi-truck"></i>
                    <span class="menu-text">Proveedores</span>
                </a>
            </li>

            <!-- FINANZAS Section -->
            <div class="menu-section-title">Finanzas</div>

            <li>
                <a href="{{ route('account-receivables.index') }}" class="{{ request()->routeIs('account-receivables.*') ? 'active' : '' }}">
                    <i class="bi bi-wallet2"></i>
                    <span class="menu-text">Cuentas por Cobrar</span>
                </a>
            </li>

            <li>
                <a href="{{ route('account-payables.index') }}" class="{{ request()->routeIs('account-payables.*') ? 'active' : '' }}">
                    <i class="bi bi-credit-card"></i>
                    <span class="menu-text">Cuentas por Pagar</span>
                </a>
            </li>

            <li>
                <a href="{{ route('cash-registers.current') }}" class="{{ request()->routeIs('cash-registers.*') ? 'active' : '' }}">
                    <i class="bi bi-cash-coin"></i>
                    <span class="menu-text">Caja</span>
                </a>
            </li>

            <li class="has-submenu {{ request()->routeIs('banks.*', 'bank-accounts.*', 'bank-transactions.*', 'checks.*', 'bank-reconciliations.*') ? 'open' : '' }}">
                <a href="javascript:void(0)" class="{{ request()->routeIs('bank-accounts.*', 'bank-transactions.*', 'checks.*', 'bank-reconciliations.*') ? 'active' : '' }}">
                    <i class="bi bi-bank"></i>
                    <span class="menu-text">Bancos</span>
                    <i class="bi bi-chevron-right chevron"></i>
                </a>
                <ul class="submenu {{ request()->routeIs('banks.*', 'bank-accounts.*', 'bank-transactions.*', 'checks.*', 'bank-reconciliations.*') ? 'show' : '' }}">
                    <li>
                        <a href="{{ route('banks.index') }}" class="{{ request()->routeIs('banks.*') ? 'active' : '' }}">
                            <i class="bi bi-bank"></i> Bancos
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('bank-accounts.index') }}" class="{{ request()->routeIs('bank-accounts.*') ? 'active' : '' }}">
                            <i class="bi bi-credit-card-2-front"></i> Cuentas Bancarias
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('bank-transactions.index') }}" class="{{ request()->routeIs('bank-transactions.*') ? 'active' : '' }}">
                            <i class="bi bi-arrow-left-right"></i> Movimientos
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('checks.index') }}" class="{{ request()->routeIs('checks.*') ? 'active' : '' }}">
                            <i class="bi bi-receipt"></i> Cheques
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('bank-reconciliations.index') }}" class="{{ request()->routeIs('bank-reconciliations.*') ? 'active' : '' }}">
                            <i class="bi bi-check2-square"></i> Conciliación Bancaria
                        </a>
                    </li>
                </ul>
            </li>

            <!-- CONTABILIDAD Section -->
            <div class="menu-section-title">Contabilidad</div>

            <li class="has-submenu {{ request()->routeIs('account-chart.*', 'journal-entries.*', 'general-ledger.*', 'trial-balance.*', 'accounting.*', 'accounting-settings.*') ? 'open' : '' }}">
                <a href="javascript:void(0)" class="{{ request()->routeIs('account-chart.*', 'journal-entries.*', 'general-ledger.*', 'trial-balance.*', 'accounting.*', 'accounting-settings.*') ? 'active' : '' }}">
                    <i class="bi bi-journal-text"></i>
                    <span class="menu-text">Contabilidad</span>
                    <i class="bi bi-chevron-right chevron"></i>
                </a>
                <ul class="submenu {{ request()->routeIs('account-chart.*', 'journal-entries.*', 'general-ledger.*', 'trial-balance.*', 'accounting.*', 'accounting-settings.*') ? 'show' : '' }}">
                    <li>
                        <a href="{{ route('account-chart.index') }}" class="{{ request()->routeIs('account-chart.*') ? 'active' : '' }}">
                            <i class="bi bi-diagram-3"></i> Plan de Cuentas
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('journal-entries.index') }}" class="{{ request()->routeIs('journal-entries.*') ? 'active' : '' }}">
                            <i class="bi bi-journal-bookmark"></i> Asientos Contables
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('general-ledger.index') }}" class="{{ request()->routeIs('general-ledger.*') ? 'active' : '' }}">
                            <i class="bi bi-book"></i> Libro Mayor
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('trial-balance.index') }}" class="{{ request()->routeIs('trial-balance.*') ? 'active' : '' }}">
                            <i class="bi bi-calculator"></i> Balance de Comprobación
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('accounting.balance-sheet') }}" class="{{ request()->routeIs('accounting.balance-sheet') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-bar-graph"></i> Balance General
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('accounting.income-statement') }}" class="{{ request()->routeIs('accounting.income-statement') ? 'active' : '' }}">
                            <i class="bi bi-graph-up"></i> Estado de Resultados
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('accounting-settings.index') }}" class="{{ request()->routeIs('accounting-settings.*') ? 'active' : '' }}">
                            <i class="bi bi-gear"></i> Configuración Contable
                        </a>
                    </li>
                </ul>
            </li>

            <!-- REPORTES Section -->
            <div class="menu-section-title">Reportes</div>

            <li class="has-submenu {{ request()->routeIs('reports.*') ? 'open' : '' }}">
                <a href="javascript:void(0)" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i class="bi bi-file-bar-graph"></i>
                    <span class="menu-text">Reportes</span>
                    <i class="bi bi-chevron-right chevron"></i>
                </a>
                <ul class="submenu {{ request()->routeIs('reports.*') ? 'show' : '' }}">
                    <li>
                        <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.index') ? 'active' : '' }}">
                            <i class="bi bi-grid"></i> Centro de Reportes
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('reports.sales') }}" class="{{ request()->routeIs('reports.sales') ? 'active' : '' }}">
                            <i class="bi bi-cart-check"></i> Ventas
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('reports.purchases') }}" class="{{ request()->routeIs('reports.purchases') ? 'active' : '' }}">
                            <i class="bi bi-bag"></i> Compras
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('reports.expenses') }}" class="{{ request()->routeIs('reports.expenses') ? 'active' : '' }}">
                            <i class="bi bi-cash-stack"></i> Gastos
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('reports.inventory') }}" class="{{ request()->routeIs('reports.inventory') ? 'active' : '' }}">
                            <i class="bi bi-box-seam"></i> Inventario
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('reports.cash-flow') }}" class="{{ request()->routeIs('reports.cash-flow') ? 'active' : '' }}">
                            <i class="bi bi-arrow-left-right"></i> Flujo de Caja
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('reports.aging-report') }}" class="{{ request()->routeIs('reports.aging-report') ? 'active' : '' }}">
                            <i class="bi bi-clock-history"></i> Antigüedad de Saldos
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('reports.top-products') }}" class="{{ request()->routeIs('reports.top-products') ? 'active' : '' }}">
                            <i class="bi bi-trophy"></i> Productos Más Vendidos
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('reports.profitability') }}" class="{{ request()->routeIs('reports.profitability') ? 'active' : '' }}">
                            <i class="bi bi-graph-up-arrow"></i> Rentabilidad
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('reports.inventory-movements') }}" class="{{ request()->routeIs('reports.inventory-movements') ? 'active' : '' }}">
                            <i class="bi bi-arrow-down-up"></i> Movimientos de Inventario
                        </a>
                    </li>
                </ul>
            </li>

            <!-- ADMINISTRACIÓN Section -->
            <div class="menu-section-title">Administración</div>

            <li>
                <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="bi bi-person-badge"></i>
                    <span class="menu-text">Usuarios</span>
                </a>
            </li>

            <li>
                <a href="{{ route('roles.index') }}" class="{{ request()->routeIs('roles.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-lock"></i>
                    <span class="menu-text">Roles</span>
                </a>
            </li>

            <!-- CONFIGURACIÓN Section -->
            <div class="menu-section-title">Configuración</div>

            <li class="has-submenu {{ request()->routeIs('settings.*') ? 'open' : '' }}">
                <a href="javascript:void(0)" class="{{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i>
                    <span class="menu-text">Configuración</span>
                    <i class="bi bi-chevron-right chevron"></i>
                </a>
                <ul class="submenu {{ request()->routeIs('settings.*') ? 'show' : '' }}">
                    <li>
                        <a href="{{ route('settings.company') }}" class="{{ request()->routeIs('settings.company*') ? 'active' : '' }}">
                            <i class="bi bi-building"></i> Datos de la Empresa
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('settings.documents') }}" class="{{ request()->routeIs('settings.documents*') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-text"></i> Numeración de Documentos
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('settings.taxes') }}" class="{{ request()->routeIs('settings.taxes*') ? 'active' : '' }}">
                            <i class="bi bi-percent"></i> Impuestos (IVA)
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div>
                <h5 class="mb-0">@yield('page-title', 'Dashboard')</h5>
            </div>
            <div class="d-flex align-items-center gap-2">
                <!-- Botón de Ayuda -->
                <a href="{{ route('help.index') }}" class="btn btn-outline-primary btn-sm" title="Centro de Ayuda">
                    <i class="bi bi-question-circle"></i> Ayuda
                </a>

                <span class="me-2">{{ auth()->user()->name }}</span>
                <div class="dropdown">
                    <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profile.show') }}"><i class="bi bi-person me-2"></i>Mi Perfil</a></li>
                        <li><a class="dropdown-item" href="{{ route('profile.login-history') }}"><i class="bi bi-clock-history me-2"></i>Historial de Sesiones</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesi&oacute;n
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Content -->
        <div class="content-wrapper">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @yield('content')
        </div>
    </div>
    
    <!-- jQuery (required by jEasyUI) - local -->
    <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jEasyUI JS (local) -->
    <script type="text/javascript" src="{{ asset('vendor/easyui/jquery.easyui.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/easyui/locale/easyui-lang-es.js') }}"></script>
    
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Toggle submenu
        $(document).ready(function() {
            $('.has-submenu > a').click(function(e) {
                e.preventDefault();
                var $parent = $(this).parent();
                $parent.toggleClass('open');
                $parent.find('.submenu').toggleClass('show');
            });
        });

        // Toggle sidebar collapse/expand
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.querySelector('.main-content');
            const toggleIcon = document.getElementById('toggle-icon');

            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');

            // Change icon
            if (sidebar.classList.contains('collapsed')) {
                toggleIcon.classList.remove('bi-list');
                toggleIcon.classList.add('bi-chevron-right');
            } else {
                toggleIcon.classList.remove('bi-chevron-right');
                toggleIcon.classList.add('bi-list');
            }

            // Save state to localStorage
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        }

        // Restore sidebar state on page load
        $(document).ready(function() {
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (isCollapsed) {
                const sidebar = document.getElementById('sidebar');
                const mainContent = document.querySelector('.main-content');
                const toggleIcon = document.getElementById('toggle-icon');
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
                toggleIcon.classList.remove('bi-list');
                toggleIcon.classList.add('bi-chevron-right');
            }
        });
    </script>

    @stack('scripts')

    <!-- Widget de Ayuda Flotante -->
    <x-help-widget :module="request()->segment(1)" />
</body>
</html>
