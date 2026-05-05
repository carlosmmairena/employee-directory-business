## Why

El directorio sirve organizaciones con 300–500+ empleados y 15+ departamentos, pero hoy carga todos los registros en el DOM del cliente y filtra/pagina con JS. Esto genera páginas pesadas, no escala, y no permite navegar por departamento — el caso de uso primario.

## What Changes

- **Nueva barra de filtros por departamento**: chips generados dinámicamente desde los datos LDAP, con scroll horizontal implícito. Un clic en un chip filtra la cuadrícula por ese departamento. El chip activo muestra una × para limpiar el filtro.
- **Paginación server-side**: el shortcode acepta `page` y `department` como parámetros de query string; WordPress sirve solo la página solicitada desde caché, no todos los registros.
- **Búsqueda server-side**: el input de búsqueda dispara una petición AJAX (o recarga con query param) procesada por PHP sobre el índice en caché — sin filtrar 300+ nodos del DOM en el cliente.
- **Texto de paginación mejorado**: cambia de "1 / 15" a "Mostrando 1–20 de 312" (y "1–20 de 47 en Engineering" cuando hay filtro activo).
- **Fix: estilos `.ldap-phone` faltantes**: el link de teléfono no tiene reglas CSS propias; se añaden con el mismo tratamiento visual que `.ldap-email`.
- **Eliminación del JS de filtrado/paginación client-side**: `directory.js` pierde la lógica de `render()`, `matchesQuery()` y el loop `allCards.forEach(display:none)`.

## Capabilities

### New Capabilities

- `department-filter`: Barra de chips de departamento sobre la cuadrícula. Extrae departamentos únicos del caché, los renderiza como chips, gestiona estado activo/inactivo con scroll horizontal implícito (CSS `overflow-x: auto; white-space: nowrap`).
- `server-side-pagination`: Paginación y búsqueda procesadas en PHP sobre el array en caché de WP. El shortcode recibe `page`, `per_page`, `search` y `department` como parámetros, aplica slice + filtro, devuelve solo los registros necesarios.

### Modified Capabilities

*(ninguna — no hay specs existentes)*

## Impact

- **`includes/class-shortcode.php`**: lógica principal de paginación/filtrado migra de JS a PHP. El shortcode pasa a leer query params (`$_GET`) para `page`, `search`, `department`.
- **`public/views/directory.php`**: nuevo bloque de barra de departamentos; variables adicionales `$departments`, `$current_department`, `$current_page`, `$total_pages`, `$total_count`.
- **`public/js/directory.js`**: se elimina búsqueda y paginación client-side. Queda solo el submit del formulario de búsqueda y navegación de página (links o botones que actualizan la URL).
- **`public/css/directory.css`**: estilos nuevos para `.ldap-dept-filters`, `.ldap-dept-chip`, `.ldap-dept-chip.is-active`; fix de `.ldap-phone`.
- **Sin cambios en LDAP**: todo opera sobre el caché existente (`ldap_ed_users` transient). No se añaden nuevas consultas al servidor LDAP.
- **Sin nuevas dependencias**: vanilla JS, sin build tools.
