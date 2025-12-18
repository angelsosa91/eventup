<?php

namespace Database\Seeders;

use App\Models\HelpCategory;
use App\Models\HelpArticle;
use Illuminate\Database\Seeder;

class HelpContentSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Crear categorías
        $categories = [
            [
                'name' => 'Primeros Pasos',
                'slug' => 'primeros-pasos',
                'icon' => 'bi-rocket',
                'description' => 'Guías para comenzar a usar NEO ERP',
                'order' => 1,
            ],
            [
                'name' => 'Ventas',
                'slug' => 'ventas',
                'icon' => 'bi-cart-check',
                'description' => 'Todo sobre el módulo de ventas',
                'order' => 2,
            ],
            [
                'name' => 'Compras',
                'slug' => 'compras',
                'icon' => 'bi-bag',
                'description' => 'Gestión de compras y proveedores',
                'order' => 3,
            ],
            [
                'name' => 'Inventario',
                'slug' => 'inventario',
                'icon' => 'bi-box-seam',
                'description' => 'Administración de productos y stock',
                'order' => 4,
            ],
            [
                'name' => 'Contabilidad',
                'slug' => 'contabilidad',
                'icon' => 'bi-calculator',
                'description' => 'Módulo contable y reportes financieros',
                'order' => 5,
            ],
            [
                'name' => 'Cuentas por Cobrar/Pagar',
                'slug' => 'cuentas',
                'icon' => 'bi-cash-stack',
                'description' => 'Gestión de cobros y pagos',
                'order' => 6,
            ],
            [
                'name' => 'Reportes',
                'slug' => 'reportes',
                'icon' => 'bi-graph-up',
                'description' => 'Generación y análisis de reportes',
                'order' => 7,
            ],
            [
                'name' => 'Configuración',
                'slug' => 'configuracion',
                'icon' => 'bi-gear',
                'description' => 'Configuración del sistema',
                'order' => 8,
            ],
        ];

        foreach ($categories as $categoryData) {
            $category = HelpCategory::create($categoryData);

            // Crear artículos según la categoría
            $this->createArticlesForCategory($category);
        }
    }

    private function createArticlesForCategory($category)
    {
        switch ($category->slug) {
            case 'primeros-pasos':
                $this->createGettingStartedArticles($category);
                break;
            case 'ventas':
                $this->createSalesArticles($category);
                break;
            case 'compras':
                $this->createPurchaseArticles($category);
                break;
            case 'inventario':
                $this->createInventoryArticles($category);
                break;
            case 'contabilidad':
                $this->createAccountingArticles($category);
                break;
            case 'cuentas':
                $this->createAccountsArticles($category);
                break;
            case 'reportes':
                $this->createReportsArticles($category);
                break;
            case 'configuracion':
                $this->createConfigArticles($category);
                break;
        }
    }

    private function createGettingStartedArticles($category)
    {
        HelpArticle::create([
            'help_category_id' => $category->id,
            'title' => '¿Cómo empezar a usar NEO ERP?',
            'slug' => 'como-empezar',
            'summary' => 'Guía rápida para dar tus primeros pasos en el sistema',
            'content' => "Bienvenido a NEO ERP. Esta guía te ayudará a comenzar:

1. CONFIGURACIÓN INICIAL
   - Completa los datos de tu empresa en Configuración > Empresa
   - Configura tu logo y datos de contacto
   - Verifica la configuración de impuestos (IVA)

2. USUARIOS Y PERMISOS
   - Crea usuarios para tu equipo en Usuarios
   - Asigna roles según las responsabilidades
   - Configura permisos personalizados si es necesario

3. PRODUCTOS Y SERVICIOS
   - Registra tus productos en Inventario > Productos
   - Crea categorías para organizar mejor
   - Define precios de compra y venta
   - Establece stock mínimo y máximo

4. CLIENTES Y PROVEEDORES
   - Agrega tus clientes en Clientes
   - Registra proveedores en Proveedores
   - Completa datos fiscales (RUC/CI)

5. PLAN DE CUENTAS
   - Revisa el plan de cuentas en Contabilidad > Plan de Cuentas
   - Personaliza según tu empresa
   - Mapea cuentas para automatización

6. PRIMERA VENTA
   - Ve a Ventas > Nueva Venta
   - Selecciona cliente
   - Agrega productos
   - Confirma la venta

¡Listo! Ya estás usando NEO ERP.",
            'module' => null,
            'is_featured' => true,
            'order' => 1,
            'tags' => json_encode(['inicio', 'configuración', 'guía']),
        ]);

        HelpArticle::create([
            'help_category_id' => $category->id,
            'title' => 'Navegación y estructura del menú',
            'slug' => 'navegacion-menu',
            'summary' => 'Aprende a navegar por el sistema y encontrar las funciones principales',
            'content' => "El menú de NEO ERP está organizado en módulos:

OPERACIONES
- Dashboard: Vista general de tu negocio
- Ventas: Facturación y notas de crédito
- Compras: Órdenes de compra
- Inventario: Productos y ajustes

FINANZAS
- Cuentas por Cobrar: Seguimiento de cobros
- Cuentas por Pagar: Pagos a proveedores
- Caja: Apertura y cierre diario
- Bancos: Movimientos bancarios

CONTABILIDAD
- Plan de Cuentas: Catálogo contable
- Asientos Contables: Movimientos manuales
- Reportes: Balances e informes

CONFIGURACIÓN
- Empresa: Datos fiscales
- Usuarios: Gestión de accesos
- Productos: Catálogo maestro
- Clientes/Proveedores: Contactos

TIPS DE NAVEGACIÓN:
- Usa la barra de búsqueda superior
- Los módulos se expanden al hacer click
- El menú se colapsa para más espacio
- Breadcrumbs muestran tu ubicación",
            'module' => null,
            'order' => 2,
            'tags' => json_encode(['navegación', 'menú', 'interfaz']),
        ]);
    }

    private function createSalesArticles($category)
    {
        HelpArticle::create([
            'help_category_id' => $category->id,
            'title' => 'Cómo crear una venta o factura',
            'slug' => 'crear-venta',
            'summary' => 'Paso a paso para registrar una venta en el sistema',
            'content' => "CREAR UNA VENTA:

1. IR AL MÓDULO
   - Click en Ventas > Nueva Venta
   - O desde Dashboard > Botón Nueva Venta

2. DATOS DE LA VENTA
   - Fecha: Por defecto hoy (puedes cambiar)
   - Cliente: Busca y selecciona del combo
   - Tipo de Pago: Contado o Crédito
   - Si es crédito: Define días y fecha de vencimiento

3. AGREGAR PRODUCTOS
   - Busca el producto en el combo
   - Ingresa la cantidad
   - Click en Agregar
   - Repite para más productos
   - Puedes eliminar items con el botón rojo

4. VERIFICAR TOTALES
   - El sistema calcula automáticamente:
     * Subtotales por tasa de IVA (0%, 5%, 10%)
     * IVA correspondiente
     * Total a cobrar

5. NOTAS ADICIONALES
   - Campo de observaciones (opcional)
   - Útil para condiciones especiales

6. GUARDAR
   - Click en Guardar
   - El sistema:
     * Genera número de factura
     * Descuenta del stock
     * Crea asiento contable
     * Genera cuentas por cobrar (si es crédito)

7. DESPUÉS DE GUARDAR
   - Puedes imprimir PDF
   - Ver detalle de la venta
   - Confirmar o anular

CONSEJOS:
- Verifica stock antes de vender
- Completa datos del cliente
- Revisa los totales
- Confirma para no poder editar",
            'module' => 'sales',
            'is_featured' => true,
            'order' => 1,
            'tags' => json_encode(['ventas', 'facturación', 'tutorial']),
        ]);

        HelpArticle::create([
            'help_category_id' => $category->id,
            'title' => 'Notas de Crédito: Devoluciones y anulaciones',
            'slug' => 'notas-credito',
            'summary' => 'Cómo procesar devoluciones con notas de crédito',
            'content' => "CREAR NOTA DE CRÉDITO:

¿QUÉ ES?
Una nota de crédito anula total o parcialmente una venta.

¿CUÁNDO USAR?
- Devolución de mercadería
- Descuento posterior a la venta
- Error en facturación
- Cancelación de venta

PROCESO:

1. DESDE LA VENTA
   - Ve a Ventas > Listado
   - Busca la venta original
   - Click en Ver Detalle
   - Botón Nota de Crédito

2. DATOS DE LA NC
   - Fecha de la nota
   - Motivo: Selecciona del combo
   - Tipo: Total o Parcial

3. ITEMS
   - Total: Se cargan todos los items
   - Parcial: Selecciona qué devolver
   - Ajusta cantidades si es parcial

4. CONFIRMAR
   - Revisa totales
   - Click en Guardar
   - Luego Confirmar

EFECTOS:
✓ Devuelve stock al inventario
✓ Crea asiento contable de reversión
✓ Reduce cuentas por cobrar
✓ Genera PDF de la NC

IMPORTANTE:
- Solo se pueden crear de ventas confirmadas
- Una vez confirmada, no se puede editar
- Afecta a la contabilidad
- El cliente recupera saldo/dinero",
            'module' => 'sales',
            'order' => 2,
            'tags' => json_encode(['notas de crédito', 'devoluciones']),
        ]);

        HelpArticle::create([
            'help_category_id' => $category->id,
            'title' => 'Remisiones: Entregas sin facturación',
            'slug' => 'remisiones',
            'summary' => 'Usa remisiones para entregas, demostraciones y consignaciones',
            'content' => "REMISIONES (NOTAS DE REMISIÓN):

¿QUÉ ES?
Documento para enviar mercadería SIN facturar aún.

USOS:
- Entrega a domicilio (se factura después)
- Traslado entre sucursales
- Consignación (el cliente paga después)
- Demostración de productos

CREAR REMISIÓN:

1. ACCESO
   - Ventas > Remisiones > Nueva

2. DATOS
   - Cliente
   - Motivo: Entrega/Traslado/Consignación/Demo
   - Dirección de entrega
   - Productos y cantidades

3. CONFIRMAR
   - Reserva stock (no lo descuenta aún)
   - Estado: Confirmada

4. MARCAR ENTREGADA
   - Cuando se entrega física
   - Estado: Entregada

5. CONVERTIR A FACTURA
   - Cuando se cobra
   - Click en Convertir a Factura
   - Elige forma de pago
   - Se crea la venta automáticamente
   - Descuenta stock y genera factura

ESTADOS:
- Borrador: Sin confirmar
- Confirmada: Stock reservado
- Entregada: Mercadería despachada
- Facturada: Ya se convirtió en venta
- Anulada: Cancelada

VENTAJAS:
✓ Control de entregas
✓ Stock reservado pero disponible
✓ Documentación de traslados
✓ Conversión fácil a factura",
            'module' => 'sales',
            'order' => 3,
            'tags' => json_encode(['remisiones', 'entregas']),
        ]);
    }

    private function createPurchaseArticles($category)
    {
        HelpArticle::create([
            'help_category_id' => $category->id,
            'title' => 'Registrar compras a proveedores',
            'slug' => 'registrar-compras',
            'summary' => 'Cómo registrar las compras de mercadería',
            'content' => "REGISTRAR UNA COMPRA:

1. NUEVA COMPRA
   - Compras > Nueva Compra

2. DATOS BÁSICOS
   - Proveedor: Selecciona del combo
   - Fecha de compra
   - Número de factura del proveedor
   - Tipo de pago: Contado o Crédito

3. PRODUCTOS
   - Busca el producto
   - Cantidad comprada
   - Precio de compra unitario
   - El sistema calcula IVA

4. CONFIRMAR
   - Revisa totales
   - Guardar
   - Confirmar

EFECTOS:
✓ Aumenta stock
✓ Crea asiento contable
✓ Genera cuenta por pagar (si es crédito)
✓ Actualiza precio de compra del producto

CONSEJOS:
- Verifica el precio de compra
- Registra el número de factura
- Confirma para actualizar stock",
            'module' => 'purchases',
            'order' => 1,
            'tags' => json_encode(['compras', 'proveedores']),
        ]);
    }

    private function createInventoryArticles($category)
    {
        HelpArticle::create([
            'help_category_id' => $category->id,
            'title' => 'Gestión de productos y stock',
            'slug' => 'gestion-productos',
            'summary' => 'Administra tu inventario de manera eficiente',
            'content' => "GESTIÓN DE INVENTARIO:

CREAR PRODUCTO:
1. Inventario > Productos > Nuevo
2. Datos básicos:
   - Nombre
   - Código/SKU
   - Categoría
   - Precio de venta y compra
   - Tasa de IVA (0%, 5%, 10%)
3. Stock:
   - Stock actual
   - Stock mínimo (para alertas)
   - Stock máximo
4. Guardar

AJUSTES DE INVENTARIO:
Cuando necesitas corregir stock:
1. Inventario > Ajustes > Nuevo
2. Motivo: Entrada/Salida/Corrección
3. Agrega productos con cantidades
4. Confirmar
5. Efecto: Actualiza stock

ALERTAS DE STOCK:
- Dashboard muestra productos con stock bajo
- Aparecen en rojo cuando están por debajo del mínimo

CONSEJOS:
✓ Define stock mínimo realista
✓ Haz inventarios periódicos
✓ Usa ajustes para correcciones
✓ Categoriza bien los productos",
            'module' => 'products',
            'order' => 1,
            'tags' => json_encode(['inventario', 'productos', 'stock']),
        ]);
    }

    private function createAccountingArticles($category)
    {
        HelpArticle::create([
            'help_category_id' => $category->id,
            'title' => 'Plan de cuentas contable',
            'slug' => 'plan-cuentas',
            'summary' => 'Entiende y personaliza tu plan de cuentas',
            'content' => "PLAN DE CUENTAS:

¿QUÉ ES?
El catálogo de todas las cuentas contables de tu empresa.

ESTRUCTURA:
- 4 niveles jerárquicos
- Tipos: Activo, Pasivo, Patrimonio, Ingreso, Gasto
- Cada cuenta tiene código y nombre

NAVEGACIÓN:
1. Contabilidad > Plan de Cuentas
2. Vista de árbol expandible
3. Click en + para ver subcuentas
4. Usa filtros por tipo o búsqueda

AGREGAR CUENTA:
1. Click en Nueva Cuenta
2. Selecciona cuenta padre
3. Código (se sugiere automáticamente)
4. Nombre
5. Tipo
6. Marcar si acepta movimientos
7. Guardar

MAPEO AUTOMÁTICO:
En Configuración > Contabilidad:
- Define cuentas para ventas
- Define cuentas para compras
- Define cuentas para IVA
- Así el sistema crea asientos automáticos

IMPORTANTE:
⚠️ No elimines cuentas con movimientos
⚠️ Respeta la jerarquía
✓ Usa códigos numéricos consistentes
✓ Nombres claros y descriptivos",
            'module' => 'accounting',
            'order' => 1,
            'tags' => json_encode(['contabilidad', 'plan de cuentas']),
        ]);

        HelpArticle::create([
            'help_category_id' => $category->id,
            'title' => 'Asientos contables manuales',
            'slug' => 'asientos-manuales',
            'summary' => 'Cómo crear asientos contables manualmente',
            'content' => "CREAR ASIENTO MANUAL:

¿CUÁNDO USAR?
- Ajustes contables
- Operaciones no automatizadas
- Correcciones
- Asientos de apertura/cierre

PROCESO:

1. NUEVO ASIENTO
   - Contabilidad > Asientos > Nuevo

2. DATOS
   - Fecha del asiento
   - Tipo: Manual
   - Descripción clara

3. LÍNEAS DEL ASIENTO
   - Click en Agregar Línea
   - Selecciona cuenta
   - Monto en Debe o Haber
   - Descripción de la línea
   - Repite para cada línea

4. VALIDACIÓN
   - El sistema verifica que:
     * Total Debe = Total Haber
     * Las cuentas acepten movimientos
   - Si no cuadra, mostrará error

5. GUARDAR
   - Estado: Borrador
   - Puedes editar

6. CONFIRMAR
   - Click en Confirmar
   - Ya no se puede editar
   - Afecta balances

REGLAS CONTABLES:
- Todo asiento debe cuadrar
- Débitos = Créditos
- Usa cuentas de detalle
- Descripción clara

EJEMPLO: Pago de servicios
Debe: Gastos de Servicios    500.000
Haber: Caja                   500.000

TIPS:
✓ Revisa bien antes de confirmar
✓ Usa descripciones claras
✓ Verifica que cuadre
✓ Consulta con contador si dudas",
            'module' => 'accounting',
            'order' => 2,
            'tags' => json_encode(['contabilidad', 'asientos']),
        ]);
    }

    private function createAccountsArticles($category)
    {
        HelpArticle::create([
            'help_category_id' => $category->id,
            'title' => 'Cuentas por Cobrar: Gestión de cobros',
            'slug' => 'cuentas-por-cobrar',
            'summary' => 'Administra los cobros a tus clientes',
            'content' => "CUENTAS POR COBRAR:

¿QUÉ SON?
Las ventas a crédito que tus clientes te deben.

CREACIÓN AUTOMÁTICA:
- Al hacer una venta a crédito
- El sistema crea la cuenta por cobrar
- Con fecha de vencimiento

REGISTRAR PAGO:
1. Cuentas por Cobrar > Listado
2. Busca la cuenta del cliente
3. Click en Ver Detalle
4. Botón Registrar Pago

5. DATOS DEL PAGO:
   - Fecha de cobro
   - Monto (puede ser parcial)
   - Método: Efectivo/Transferencia/Cheque
   - Referencia (opcional)

6. CONFIRMAR
   - Se genera recibo de pago
   - Actualiza saldo pendiente
   - Crea asiento contable

ESTADOS:
- Pendiente: No ha cobrado nada
- Parcial: Cobró parte
- Pagada: Cobró todo
- Vencida: Pasó la fecha límite

REPORTES:
- Antigüedad de saldos
- Clientes con deuda
- Vencimientos próximos

CONSEJOS:
✓ Registra pagos inmediatamente
✓ Usa referencias (nro. transf.)
✓ Revisa vencimientos semanalmente
✓ Contacta clientes con mora",
            'module' => 'account-receivables',
            'order' => 1,
            'tags' => json_encode(['cuentas por cobrar', 'cobros']),
        ]);

        HelpArticle::create([
            'help_category_id' => $category->id,
            'title' => 'Cuentas por Pagar: Gestión de pagos',
            'slug' => 'cuentas-por-pagar',
            'summary' => 'Controla tus pagos a proveedores',
            'content' => "CUENTAS POR PAGAR:

¿QUÉ SON?
Las compras a crédito que debes a proveedores.

CREACIÓN:
- Al registrar una compra a crédito
- Automáticamente se crea la CP

REGISTRAR PAGO:
1. Cuentas por Pagar > Listado
2. Selecciona la cuenta
3. Click en Registrar Pago
4. Monto y método de pago
5. Confirmar

EFECTOS:
✓ Reduce saldo pendiente
✓ Genera comprobante de pago
✓ Asiento contable automático
✓ Descuenta de banco/caja

PROGRAMAR PAGOS:
- Usa el calendario de vencimientos
- Prioriza según fechas
- Planifica flujo de caja

CONSEJOS:
✓ Paga a tiempo para mantener crédito
✓ Aprovecha descuentos por pronto pago
✓ Negocia plazos con proveedores
✓ Controla vencimientos",
            'module' => 'account-payables',
            'order' => 2,
            'tags' => json_encode(['cuentas por pagar', 'pagos']),
        ]);
    }

    private function createReportsArticles($category)
    {
        HelpArticle::create([
            'help_category_id' => $category->id,
            'title' => 'Reportes financieros principales',
            'slug' => 'reportes-financieros',
            'summary' => 'Balance General, Estado de Resultados y más',
            'content' => "REPORTES FINANCIEROS:

BALANCE GENERAL:
- Contabilidad > Balance General
- Muestra: Activos, Pasivos, Patrimonio
- Filtra por fecha
- Exporta a Excel o PDF

ESTADO DE RESULTADOS:
- Contabilidad > Estado de Resultados
- Muestra: Ingresos, Gastos, Utilidad
- Define período (mes, trimestre, año)
- Compara períodos

LIBRO MAYOR:
- Contabilidad > Libro Mayor
- Movimientos de una cuenta específica
- Con saldos acumulados
- Exportable

BALANCE DE SUMAS Y SALDOS:
- Todas las cuentas con movimiento
- Débitos, Créditos, Saldos
- Para auditorías

REPORTES DE VENTAS:
- Reportes > Ventas
- Por período, cliente, producto
- Gráficos y tablas
- Análisis de tendencias

REPORTES DE COMPRAS:
- Reportes > Compras
- Por proveedor
- Por categoría de producto

FLUJO DE CAJA:
- Reportes > Flujo de Caja
- Entradas vs Salidas
- Proyecciones

TIPS:
✓ Genera reportes mensualmente
✓ Compara con períodos anteriores
✓ Exporta para análisis externos
✓ Revisa inconsistencias",
            'module' => 'reports',
            'order' => 1,
            'tags' => json_encode(['reportes', 'informes', 'balance']),
        ]);
    }

    private function createConfigArticles($category)
    {
        HelpArticle::create([
            'help_category_id' => $category->id,
            'title' => 'Configuración de la empresa',
            'slug' => 'configuracion-empresa',
            'summary' => 'Personaliza los datos de tu empresa',
            'content' => "CONFIGURAR TU EMPRESA:

1. ACCESO
   - Configuración > Empresa

2. DATOS FISCALES
   - Nombre o Razón Social
   - RUC (obligatorio)
   - Timbrado (si aplica)
   - Actividad económica

3. CONTACTO
   - Dirección completa
   - Teléfono
   - Email
   - Sitio web

4. LOGO
   - Sube tu logo (PNG o JPG)
   - Aparecerá en:
     * Facturas
     * Reportes
     * Encabezado del sistema

5. NUMERACIÓN DE DOCUMENTOS
   - Configuración > Documentos
   - Define prefijos:
     * Ventas: V-
     * Compras: C-
     * Remisiones: R-
   - Número inicial

6. IMPUESTOS
   - Configuración > Impuestos
   - Verifica tasas de IVA
   - Paraguay: 0%, 5%, 10%

7. USUARIOS
   - Configuración > Usuarios
   - Crea cuentas para tu equipo
   - Asigna roles y permisos

IMPORTANTE:
⚠️ Verifica que el RUC sea correcto
⚠️ El timbrado es obligatorio en Paraguay
✓ Mantén actualizados los datos de contacto
✓ Respalda antes de cambios importantes",
            'module' => null,
            'order' => 1,
            'tags' => json_encode(['configuración', 'empresa']),
        ]);

        HelpArticle::create([
            'help_category_id' => $category->id,
            'title' => 'Gestión de usuarios y permisos',
            'slug' => 'usuarios-permisos',
            'summary' => 'Controla quién accede a qué en el sistema',
            'content' => "USUARIOS Y PERMISOS:

CREAR USUARIO:
1. Configuración > Usuarios > Nuevo
2. Datos personales:
   - Nombre completo
   - Email (usado para login)
   - Contraseña inicial
3. Rol: Asigna un rol existente
4. Estado: Activo/Inactivo
5. Guardar

ROLES PREDEFINIDOS:
- Administrador: Acceso total
- Vendedor: Solo ventas y clientes
- Contador: Módulo contable
- Cajero: Ventas y caja

CREAR ROL PERSONALIZADO:
1. Configuración > Roles > Nuevo
2. Nombre del rol
3. Selecciona permisos:
   - Ver, Crear, Editar, Eliminar
   - Por cada módulo
4. Guardar
5. Asigna a usuarios

PERMISOS DISPONIBLES:
✓ Ventas
✓ Compras
✓ Inventario
✓ Clientes/Proveedores
✓ Contabilidad
✓ Reportes
✓ Configuración
✓ Usuarios

BUENAS PRÁCTICAS:
✓ Principio de mínimo privilegio
✓ Roles por función, no por persona
✓ Audita accesos regularmente
✓ Desactiva usuarios que ya no trabajan
✓ Contraseñas seguras

SEGURIDAD:
- Cambia contraseñas periódicamente
- No compartas usuarios
- Revisa logs de acceso
- Cada persona debe tener su usuario",
            'module' => null,
            'order' => 2,
            'tags' => json_encode(['usuarios', 'permisos', 'seguridad']),
        ]);
    }
}
