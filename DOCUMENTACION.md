# FULMUV — Documentación Técnica del Sistema

> Plataforma marketplace automotriz para Ecuador. Conecta compradores, vendedores y proveedores de servicios del sector automotriz.

---

## Tabla de Contenidos

1. [Descripción General](#1-descripción-general)
2. [Stack Tecnológico](#2-stack-tecnológico)
3. [Estructura de Carpetas](#3-estructura-de-carpetas)
4. [Base de Datos](#4-base-de-datos)
5. [API REST](#5-api-rest)
6. [Módulos del Frontend](#6-módulos-del-frontend)
7. [JavaScript por Módulo](#7-javascript-por-módulo)
8. [Sistema de Includes Globales](#8-sistema-de-includes-globales)
9. [Pasarelas de Pago](#9-pasarelas-de-pago)
10. [Sistema de Correos](#10-sistema-de-correos)
11. [Sistema de Entregas](#11-sistema-de-entregas)
12. [Paneles Administrativos](#12-paneles-administrativos)

---

## 1. Descripción General

FULMUV es un marketplace automotriz que permite:

- Publicar y buscar **productos** (repuestos, accesorios)
- Publicar y buscar **servicios** automotrices y de emergencia
- Publicar y buscar **vehículos** en venta
- Gestionar **eventos** del sector automotriz
- Publicar y aplicar a **empleos** del sector
- Gestión de **órdenes de compra**, pagos y entregas
- Sistema de **membresías** para empresas proveedoras
- Panel para **empresas/vendedores** y panel **administrativo**

---

## 2. Stack Tecnológico

| Capa | Tecnología |
|---|---|
| Backend | PHP 7+ |
| Framework API | Slim Framework (PHP) |
| Base de datos | MySQL / MariaDB |
| Frontend | HTML5, CSS3, JavaScript (jQuery) |
| Template UI | Nest Frontend v6.1 |
| Iconografía | Font Awesome 6.5.2 |
| Tipografía | Google Fonts (Big Shoulders, Figtree, Inter, Roboto Slab) |
| Filtros de precio | noUiSlider + wNumb |
| Notificaciones | Toastr.js |
| Alertas | SweetAlert2 |
| Editor de texto | Summernote |
| Correos | PHPMailer |
| Pasarela de pago | Nuvei (ex-Paymentez) |
| Logística | Grupo Entrega / Servientrega |
| Slider/Carrusel | Slick.js |
| Zona horaria | America/Guayaquil |

---

## 3. Estructura de Carpetas

```
fulmuv/
├── admin/                      # Panel administrativo
│   ├── files/                  # Archivos subidos desde admin
│   ├── includes/               # Partials del panel admin
│   ├── js/                     # JS del panel admin
│   └── summernote/             # Editor de texto enriquecido
│
├── api/                        # API REST Backend
│   ├── Slim/                   # Framework Slim PHP
│   ├── include/
│   │   ├── Config.php          # Credenciales BD y constantes
│   │   ├── DbConnect.php       # Conexión MySQLi
│   │   ├── DbHandler.php       # Todas las operaciones de BD (>300 funciones)
│   │   └── PHPMailer/          # Envío de correos
│   ├── v1/
│   │   └── index.php           # Definición de todos los endpoints REST (~300 rutas)
│   └── vendor/                 # Dependencias Composer
│
├── empresa/                    # Panel de empresas (versión principal)
├── empresa1/                   # Versión alternativa empresa
├── empresa4/                   # Versión alternativa empresa
├── empresa5/                   # Versión alternativa empresa
│
├── includes/                   # Partials globales del frontend
│   ├── header.php              # Cabecera: CSS, meta tags, buscador, carrito
│   ├── footer.php              # Pie: JS global, carrito mini, buscador
│   └── mobile_bottom_nav.php   # Navegación inferior móvil
│
├── js/                         # JavaScript del frontend público
├── img/                        # Imágenes estáticas
├── cv/                         # CVs subidos por postulantes
├── documentos/                 # Documentos de empresas
├── pagos_ordenes/              # Comprobantes de pago
└── themelading/nest-frontend/  # Assets del template (CSS/JS compilados)
```

---

## 4. Base de Datos

### Conexión

| Parámetro | Valor |
|---|---|
| Host | 18.191.120.236 |
| Base de datos | fulmuv |
| Usuario | induwagen |
| Driver | MySQLi (PHP) |

### Tablas del Sistema

#### Usuarios y Autenticación

| Tabla | Descripción |
|---|---|
| `usuarios` | Usuarios del sistema (admin, empresas) |
| `clientes` | Clientes compradores |
| `rol` | Roles de usuario |
| `permisos` | Permisos por rol |
| `cookies_consent` | Registro de aceptación de cookies |
| `terminos_producto_accept` | Registro de aceptación de T&C por producto |

#### Empresas y Membresías

| Tabla | Descripción |
|---|---|
| `empresas` | Empresas/vendedores registrados |
| `sucursales` | Sucursales de cada empresa |
| `membresias` | Planes de membresía disponibles |
| `membresias_empresas` | Membresías activas por empresa |
| `cobros_programados_membresias` | Cobros recurrentes de membresías |
| `verificacion_empresa` | Estado de verificación de empresas |
| `tipo_vendedor` | Tipos de vendedor (concesionario, particular, etc.) |
| `tipos_eventos` | Tipos de establecimientos |
| `establecimientos` | Tipos de establecimiento por empresa |

#### Catálogo de Productos

| Tabla | Descripción |
|---|---|
| `productos` | Productos y servicios publicados |
| `categorias` | Categorías de productos |
| `categorias_principales` | Categorías principales / padres |
| `sub_categorias` | Subcategorías de productos |
| `atributos` | Atributos configurables por categoría |
| `marcas_productos` | Marcas para productos |
| `nombres_productos` | Catálogo de nombres de productos |
| `nombres_servicios` | Catálogo de nombres de servicios |
| `catalogo_productos` | Catálogos de productos por sucursal |
| `catalogos` | Catálogos de empresa |
| `archivos_productos` | Imágenes y archivos de productos |
| `producto_interacciones` | Registro de interacciones con productos |

#### Vehículos

| Tabla | Descripción |
|---|---|
| `vehiculos` | Vehículos publicados en venta |
| `marcas` | Marcas de vehículos |
| `modelos_autos` | Modelos de vehículos |
| `tipos_auto` | Tipos de vehículo (sedán, SUV, etc.) |
| `tipo_traccion` | Tipo de tracción |
| `transmision` | Tipos de transmisión |
| `funcionamiento_motor` | Tipos de motor (gasolina, eléctrico, etc.) |
| `colores` | Colores disponibles |
| `tapiceria` | Tipos de tapicería |
| `climatizacion` | Sistemas de climatización |
| `archivos_vehiculos` | Imágenes y archivos de vehículos |

#### Órdenes y Ventas

| Tabla | Descripción |
|---|---|
| `ordenes` | Órdenes de compra de clientes |
| `ordenes_empresas` | Desglose de órdenes por empresa |
| `ordenes_iso` | Órdenes internas/ISO |
| `ordenes_notas` | Notas y comentarios de órdenes |
| `ordenes_servientrega` | Datos de guías Servientrega |
| `estado_venta` | Estados posibles de una venta |
| `facturas` | Facturas generadas |
| `secuencias_facturacion` | Secuencias numéricas de facturación |

#### Pagos

| Tabla | Descripción |
|---|---|
| `pagos_ordenes` | Pagos registrados por orden |
| `pagos_transaccion` | Transacciones de pasarela de pago |
| `pagos_recurrentes` | Pagos recurrentes registrados |

#### Logística

| Tabla | Descripción |
|---|---|
| `rutas` | Rutas de entrega disponibles |
| `ruta_grupo` | Grupos de rutas |
| `trayecto` | Trayectos dentro de una ruta |
| `direccion` | Direcciones registradas |
| `datos_domicilio` | Datos de domicilio para entrega |
| `datos_facturacion` | Datos de facturación del cliente |
| `contenedor` | Contenedores de entrega |

#### Eventos y Empleos

| Tabla | Descripción |
|---|---|
| `evento` / `eventos` | Eventos del sector automotriz |
| `galerias_eventos` | Galería de imágenes por evento |
| `subtipo_eventos` | Subtipos de eventos |
| `empleos` | Ofertas de empleo publicadas |
| `archivos_empleos` | Archivos adjuntos a empleos |
| `postulante_trabajo_empresa` | Postulaciones a empleos |

#### Marketing y Comunicaciones

| Tabla | Descripción |
|---|---|
| `banner` | Banners publicitarios del sitio |
| `publicidad` | Anuncios/publicidad interna |
| `correo_control` | Control de envíos de correo |
| `correo_plantilla` | Plantillas de correo |
| `correos_default` | Correos predeterminados del sistema |
| `contacto_fulmuv` | Formulario de contacto |

#### Personas

| Tabla | Descripción |
|---|---|
| `agentes` | Agentes comerciales |
| `areas` | Áreas organizacionales |

### Códigos de Respuesta de la API

| Código | Significado |
|---|---|
| -2 | REQUIRED_PAYMENT |
| -1 | RECORD_CREATION_FAILED |
| 0 | RECORD_CREATED_SUCCESSFULLY |
| 2 | RECORD_ALREADY_EXISTED |
| 3 | RECORD_DOES_NOT_EXIST |
| 4 | OPERATION_COMPLETED |
| 5 | ACCESS_DENIED |
| 6 | RECORD_DOES_NOT_APPLY |
| 7 | OPERATION_FULL |
| 8 | RECORD_UPDATED_SUCCESSFULLY |
| 9 | RECORD_UPDATED_FAILED |
| 10 | USER_ALREADY_EXISTED |
| 11 | USER_CEDULA_ALREADY_EXISTED |

---

## 5. API REST

La API está construida con **Slim Framework** en `api/v1/index.php`. Toda la lógica de base de datos está en `api/include/DbHandler.php` (+10.000 líneas, +300 funciones públicas).

Base URL: `/api/v1/fulmuv/`

### Endpoints GET

#### Categorías
| Endpoint | Descripción |
|---|---|
| `GET /categorias/All` | Todas las categorías |
| `GET /categorias/{id}` | Categoría por ID |
| `GET /categoriasPrincipales/All` | Categorías principales |
| `GET /categoriasByPrincipales/{id}` | Categorías por principal |
| `GET /sub_categorias/` | Todas las subcategorías |
| `GET /sub_categorias/{id}` | Subcategoría por ID |

#### Productos
| Endpoint | Descripción |
|---|---|
| `GET /productosAll/all` | Todos los productos activos |
| `GET /productos/{id}` | Producto por ID |
| `GET /productos/all/{id_empresa}/{tipo}` | Productos por empresa y tipo |
| `GET /productos/borrador/{id}` | Borradores de empresa |
| `GET /getProductosVendidosHoy/` | Productos destacados del día |
| `GET /ofertas_imperdibles/` | Ofertas con descuento |

#### Servicios
| Endpoint | Descripción |
|---|---|
| `GET /serviciosProductos/All` | Todos los servicios |
| `GET /getServiciosEmergenciaAll/All` | Servicios de emergencia |
| `GET /getServiciosVendidosHoy/` | Servicios destacados del día |
| `GET /servicios/{id}` | Servicio por ID |

#### Vehículos
| Endpoint | Descripción |
|---|---|
| `GET /vehiculos/All` | Todos los vehículos |
| `GET /vehiculos/{id}` | Vehículo por ID |
| `GET /vehiculos/all/{id_empresa}` | Vehículos por empresa |
| `GET /vehiculosLlegados/All` | Vehículos recién llegados |

#### Empresas y Membresías
| Endpoint | Descripción |
|---|---|
| `GET /empresas/{id}` | Empresa por ID |
| `GET /empresas/{id}/sucursales` | Sucursales de empresa |
| `GET /membresias/` | Planes de membresía |
| `GET /empresa/membresia_actual/{id}` | Membresía activa de empresa |
| `GET /validarMembresiaProductos/{id}/{tipo}` | Validar límite de publicaciones |

#### Vehículos — Especificaciones
| Endpoint | Descripción |
|---|---|
| `GET /marcas/` | Marcas de vehículos |
| `GET /modelosAutos/` | Modelos de autos |
| `GET /tiposAuto/` | Tipos de auto |
| `GET /tipo_tracccion/` | Tipos de tracción |
| `GET /getTransmision/` | Transmisiones |
| `GET /getFuncionamientoMotor/` | Tipos de motor |
| `GET /getColores/` | Colores |
| `GET /getTapiceria/` | Tapicerías |
| `GET /getClimatizacion/` | Climatización |

#### Eventos y Empleos
| Endpoint | Descripción |
|---|---|
| `GET /eventos/all` | Todos los eventos |
| `GET /eventos/{id}` | Evento por ID |
| `GET /empleosAll/all` | Todos los empleos |
| `GET /empleos/{id}` | Empleo por ID |

### Endpoints POST

#### Autenticación
| Endpoint | Descripción |
|---|---|
| `POST /admin/login` | Login administrador |
| `POST /cliente/login` | Login cliente |
| `POST /cliente/resetPassword` | Resetear contraseña cliente |

#### Búsqueda
| Endpoint | Descripción |
|---|---|
| `POST /productos/busqueda` | Búsqueda global (productos, servicios, vehículos, eventos, empleos) |
| `POST /ProductosSearch/All` | Búsqueda avanzada de productos |
| `POST /categorias/productos` | Productos relacionados por categoría |
| `POST /categorias/vehiculosRelacionados` | Vehículos relacionados |

#### Productos (CRUD)
| Endpoint | Descripción |
|---|---|
| `POST /productos/create` | Crear producto |
| `POST /productos/update` | Actualizar producto |
| `POST /productos/delete` | Eliminar producto |
| `POST /productos/idCategoria` | Productos por categoría(s) |
| `POST /productos/idEmpresa` | Productos por empresa |
| `POST /productos/excel` | Carga masiva por Excel |

#### Vehículos (CRUD)
| Endpoint | Descripción |
|---|---|
| `POST /vehiculos/create` | Crear vehículo |
| `POST /vehiculos/update_full` | Actualizar vehículo completo |
| `POST /vehiculos/update_basic` | Actualizar datos básicos |
| `POST /vehiculos/delete` | Eliminar vehículo |

#### Órdenes
| Endpoint | Descripción |
|---|---|
| `POST /generarOrden` | Crear nueva orden |
| `POST /ordenes/updateEstado` | Actualizar estado de orden |
| `POST /ordenes/confirmarVenta` | Confirmar venta |
| `POST /ordenes_iso/create` | Crear orden ISO |
| `POST /getPDFGUIAA4` | Generar PDF de guía |

#### Empresas
| Endpoint | Descripción |
|---|---|
| `POST /empresas/create` | Registrar empresa |
| `POST /empresas/update` | Actualizar empresa |
| `POST /empresas/verificar` | Verificar empresa |
| `POST /empresas/membresiasUpdate` | Actualizar membresía |
| `POST /empresa/darsebaja` | Dar de baja empresa |

#### Pagos
| Endpoint | Descripción |
|---|---|
| `POST /nuvei/openOrder` | Abrir orden en Nuvei |
| `POST /nuvei/getPaymentStatus` | Consultar estado de pago |
| `POST /venta/recurrente/` | Cobro recurrente |
| `POST /debitToken/` | Débito con token guardado |
| `POST /empresa/wallet/add` | Agregar tarjeta al wallet |
| `POST /refund/pago/` | Reembolsar pago |

#### Logística
| Endpoint | Descripción |
|---|---|
| `POST /crearGuiaServientrega/` | Crear guía Servientrega |
| `POST /getRutaByProvinciaCantonSector` | Obtener ruta por ubicación |
| `POST /getTarifas` | Obtener tarifas de envío |
| `POST /getCantonesDestinoByProvinciaLike` | Cantones por provincia |
| `POST /getSectoresByProvinciaCanton` | Sectores por cantón |

---

## 6. Módulos del Frontend

Cada módulo consta de un archivo PHP (vista) + un archivo JS (lógica).

### Páginas Públicas

| Archivo PHP | Archivo JS | Descripción |
|---|---|---|
| `index.php` | *(inline)* | Página principal con carruseles de productos, servicios y vehículos |
| `productos_categoria.php` | `js/productos_categoria.js` | Listado de productos por categoría con filtros completos |
| `productos_vendor.php` | `js/productos_vendor.js` | Listado de productos/vehículos de un vendor específico |
| `productos_vendidos_hoy.php` | `js/productos_vendidos_hoy.js` | Productos destacados del día |
| `ofertas_imperdibles.php` | `js/ofertas_imperdibles.js` | Productos en oferta con descuento |
| `servicios.php` | `js/servicios.js` | Listado de servicios automotrices |
| `servicios_emergencia.php` | `js/servicios_emergencia.js` | Servicios de emergencia vial |
| `vehiculos.php` | `js/vehiculos.js` | Listado de vehículos en venta |
| `busqueda_productos.php` | `js/busqueda_productos.js` | Resultados de búsqueda global |
| `detalle_productos.php` | `js/detalle_productos.js` | Detalle de producto con relacionados |
| `detalle_vehiculo.php` | `js/detalle_vehiculo.js` | Detalle de vehículo con relacionados |
| `detalle_eventos.php` | *(inline)* | Detalle de evento |
| `eventos.php` | *(inline)* | Listado de eventos |
| `empleos.php` | *(inline)* | Listado de empleos |
| `vendor.php` | `js/vendor.js` | Perfil público de un vendor |

### Páginas de Compra

| Archivo PHP | Archivo JS | Descripción |
|---|---|---|
| `shop-cart.php` | `js/shop-cart.js` | Carrito de compras |
| `shop-checkout.php` | `js/shop-checkout.js` | Proceso de pago (Nuvei) |
| `lista_pedidos.php` | `js/lista_pedidos.js` | Historial de pedidos del cliente |
| `seguimiento_pedido.php` | `js/seguimiento_pedido.js` | Seguimiento de orden |

### Páginas de Cuenta

| Archivo PHP | Archivo JS | Descripción |
|---|---|---|
| `login.php` | `js/login.js` | Login de clientes |
| `mi_cuenta.php` | `js/mi_cuenta.js` | Perfil del cliente |
| `cambiar_contrasena.php` | `js/cambiar_contrasena.js` | Cambio de contraseña |
| `recuperar_contrasena.php` | `js/recuperar_contrasena.js` | Recuperación de contraseña |

### Páginas de Publicación

| Archivo PHP | Descripción |
|---|---|
| `anuncia.php` | Landing para anunciarse como empresa |
| `anuncia_fulmuv.php` | Formulario de registro de empresa |
| `cargar_excel.php` | Carga masiva de productos por Excel |
| `cargar_imagen_pago.php` | Subida de comprobante de pago |
| `cargar_pdf_cv.php` | Subida de CV para empleos |

---

## 7. JavaScript por Módulo

### Patrón Común en Listados

Todos los archivos JS de listado siguen el mismo patrón arquitectónico:

```javascript
// 1. Estado global
let productosData = [];        // dataset maestro (nunca se modifica)
let sortOption = "todos";      // "mayor" | "menor" | "todos"
let searchText = "";
let subcategoriasSeleccionadas = [];
let precioMin = 0, precioMax = Infinity;
let provinciaSel = {}, cantonSel = {};

// 2. Carga inicial (una sola llamada API)
$.get("api/v1/fulmuv/...", function(data) {
    productosData = shuffleArray(data);   // orden aleatorio
    inicializarSlider(maxPrecio);
    buildMarcasYModelos(productosData);
    renderEmpresas(productosData, 1);
});

// 3. Filtrado cliente (sin llamadas adicionales a la API)
function renderEmpresas(data, page) {
    let filtrados = data.filter(p => matchSearch && matchCat && matchPrecio && matchUbicacion ...);
    if (sortOption === "mayor") filtrados.sort((a,b) => b.precio - a.precio);
    // paginar y renderizar
}

// 4. Precio con formato superíndice
function formatPrecioSuperscript(valor) { ... }
```

### Funciones Utilitarias Clave

| Función | Descripción |
|---|---|
| `formatPrecioSuperscript(valor)` | Formatea precio como `US$` + entero en negrita + centavos superíndice |
| `shuffleArray(arr)` | Fisher-Yates shuffle — orden aleatorio del listado en cada carga |
| `buildMarcasYModelos(data)` | Construye filtros de marcas y modelos desde el dataset |
| `buildCatsAndSubcatsFromProductos(data)` | Construye índice de categorías/subcategorías del dataset |
| `applyAllFilters()` / `renderEmpresas()` | Aplica todos los filtros activos y renderiza |
| `inicializarSlider(max)` | Inicializa el slider de rango de precio (noUiSlider) |
| `normalizarTexto(s)` | Normaliza texto sin tildes, minúsculas, para comparación |
| `capitalizarPrimeraLetra(str)` | Capitaliza primera letra respetando el formato del título |

### Características por Archivo JS

| Archivo | Dataset | Shuffle | noUiSlider | Filtros especiales |
|---|---|---|---|---|
| `productos_categoria.js` | `productosDataAll` | ✓ | ✓ | Filtro por membresía activa |
| `productos_vendor.js` | `productosData` | ✓ | ✓ | Switch producto/vehículo/servicio |
| `servicios.js` | `productosData` | ✓ | ✓ | Filtro por tipo servicio |
| `servicios_emergencia.js` | `productosMaster` | ✓ | ✓ | Filtro emergencia específico |
| `vehiculos.js` | `productosData` | ✓ | ✓ | Km, año, color, tapicería, transmisión |
| `productos_vendidos_hoy.js` | `productosData` | ✓ | ✓ | Categoría recarga dataset |
| `ofertas_imperdibles.js` | `productosData` | ✓ | ✓ | Solo productos con descuento |
| `detalle_productos.js` | N/A | Relacionados | ✗ | Mínimo 6 relacionados (rellena aleatoriamente) |
| `detalle_vehiculo.js` | N/A | Relacionados | ✗ | Mínimo 6 relacionados (rellena aleatoriamente) |

---

## 8. Sistema de Includes Globales

### `includes/header.php`

Incluido en todas las páginas públicas. Contiene:

- **Meta tags** SEO y Open Graph
- **CSS**: Nest Frontend, Font Awesome, Toastr, Slider-Range
- **Sesión PHP**: control de autenticación
- **Buscador global** (`#input-busqueda`): conecta con `POST /productos/busqueda`
- **Carrito mini** (slide-panel): se actualiza desde localStorage
- **Menú de navegación**: categorías, servicios, vehículos, eventos, empleos
- **Modal de login**: acceso cliente / proveedor

### `includes/footer.php`

Incluido en todas las páginas públicas. Contiene:

- **Scripts JS**: jQuery, Bootstrap, Slick, SweetAlert2, Toastr, Select2
- **Lógica del buscador global**: renderiza resultados de productos, servicios, vehículos, eventos y empleos con `formatPrecioSuperscript` y títulos en negrita
- **Carrito**: lógica completa de localStorage, actualización de totales, mini-cart
- **Modal de Términos y Condiciones**: aceptación obligatoria antes de ver detalle de producto
- **`formatPrecioSuperscript(valor)`**: función global de formato de precios
- **`capitalizarPrimeraLetra(str)`**: función global de capitalización
- **Notificaciones Toastr**: configuración global

### `includes/mobile_bottom_nav.php`

Barra de navegación fija en la parte inferior para móviles con accesos rápidos.

---

## 9. Pasarelas de Pago

### Nuvei (ex-Paymentez)

| Parámetro | Valor |
|---|---|
| Ambiente activo | STG (pruebas) |
| Application Code | TESTNUVEISTG-EC-SERVER |
| URL | https://ccapi-stg.paymentez.com/v2/ |

**Flujo de pago:**
1. Frontend llama `POST /nuvei/openOrder` → obtiene token de sesión
2. Nuvei renderiza formulario de tarjeta en el frontend
3. Nuvei notifica resultado → `POST /nuvei/getPaymentStatus` verifica
4. Se crea/actualiza la orden en BD

**Funciones en DbHandler.php:**
- `nuveiOpenOrder()` — inicia sesión de pago
- `nuveiGetPaymentStatus()` — consulta resultado
- `nuveiIsConfigured()` — valida configuración
- `debitToken()` — débito con tarjeta tokenizada
- `addWalletTarjetaPaymentez()` — guardar tarjeta
- `webstoreCreateRecurrente()` — pago recurrente
- `reembolsar()` — reembolso de pago

---

## 10. Sistema de Correos

Usa **PHPMailer** (`api/include/PHPMailer/`).

Correos automáticos implementados:

| Evento | Función |
|---|---|
| Registro de empresa | `notificaEmpresaVerificacionEnProceso()` |
| Verificación aprobada | `correoVerificacionAprobada()` |
| Verificación rechazada | `correoVerificacionRechazada()` |
| Nueva compra | `notificaCompra()`, `enviarGraciasCompra()` |
| Nuevo cliente | `notificaNuevoCliente()` |
| Reset de contraseña | `resetPassword()`, `notificaActualizacionPasswordUsuario()` |
| Nuevo evento publicado | `notificaEvento()` |
| Nuevo anuncio FULMUV | `enviarCorreoNuevoAnuncioFulmuv()` |
| Inicio de sesión | `enviarCorreoInicioSesion` |
| Reembolso de membresía | `correoReembolsoMembresia()` |

Las plantillas de correo se gestionan desde el panel admin (tabla `correo_plantilla` y `correo_control`).

---

## 11. Sistema de Entregas

### Grupo Entrega

Integración propia para gestión de rutas y trayectos dentro de Ecuador.

| Endpoint | Descripción |
|---|---|
| `GET /grupo_entrega/getProvinciasAll` | Provincias disponibles |
| `POST /grupo_entrega/getCantones/ByIdProvincia` | Cantones por provincia |
| `POST /grupo_entrega/getParroquia/ByIdCanton` | Parroquias por cantón |
| `POST /getRutaByProvinciaCantonSector` | Ruta y tarifa para origen→destino |
| `POST /getTarifas` | Tarifas de envío |
| `POST /ordenes/trayecto` | Crear trayecto en orden |

### Servientrega

Integración para generación de guías de envío.

| Función DB | Descripción |
|---|---|
| `crearGuiaServientrega()` | Genera guía Servientrega |
| `crearGuiaGrupoEntrega()` | Genera guía de grupo entrega |
| `getPDFGUIAA4()` | Genera PDF de guía de envío |
| `apiTrackingGrupoEntregas()` | Consulta tracking de guía |

---

## 12. Paneles Administrativos

### Panel Admin (`/admin/`)

Acceso exclusivo para administradores de FULMUV. Gestiona:

- Usuarios y roles
- Empresas y verificaciones
- Membresías y cobros
- Productos, servicios, vehículos de todas las empresas
- Correos y plantillas
- Banners y publicidad
- Eventos
- Reportes e interacciones
- Órdenes globales
- Categorías, marcas, modelos

### Panel de Empresas (`/empresa/`)

Acceso para proveedores registrados. Gestiona:

- Perfil de empresa y sucursales
- Publicación de productos, servicios y vehículos (con validación de membresía)
- Carga masiva por Excel
- Órdenes recibidas y ventas
- Membresía y pagos
- Galería de imágenes
- Empleos publicados
- Eventos propios

### Sistema de Membresías

Las empresas requieren una membresía activa para publicar. La validación se hace en el API (`validarMembresiaProductos`) y en el JS del frontend (`filterByMembresiaActiva`).

Flujo:
1. Empresa se registra → estado pendiente de verificación
2. Admin aprueba verificación → empresa puede contratar membresía
3. Con membresía activa → puede publicar hasta el límite del plan
4. Cobros recurrentes automáticos (`debitarMembresiasDiarias`, `procesarCobrosProgramadosPendientes`)

---

*Generado el 2026-04-28 — Proyecto FULMUV v2.0*
