## Context

Hoy el shortcode renderiza **todos** los empleados en el DOM (hasta 500+), y `directory.js` filtra y pagina con `display:none` en el cliente. Con 300–500 registros esto genera:

- DOM pesado en la carga inicial (~500 `<article>` elements)
- Sin navegación por departamento (el caso de uso primario)
- Paginación sin contexto ("1 / 15" sin saber cuántos hay)

La caché ya existe (`ldap_ed_users` transient). El cambio mueve la lógica de filtrado y paginación a PHP sobre ese array en memoria — sin nuevas consultas LDAP.

## Goals / Non-Goals

**Goals:**
- Filtrar y paginar en PHP sobre el array en caché de WP
- Barra de chips de departamento con scroll horizontal implícito
- URLs navegables con query params (`?ldap_page`, `?ldap_search`, `?ldap_dept`)
- Texto de paginación con rango y contexto de departamento
- Fix de estilos `.ldap-phone` ausentes

**Non-Goals:**
- Nuevas consultas al servidor LDAP por página o filtro
- Búsqueda en tiempo real (live search con AJAX por keystroke)
- Múltiples shortcodes independientes en la misma página
- Infinite scroll
- Ordenación client-side por columna

## Decisions

### 1. Query params en lugar de AJAX para navegación

**Decisión:** Links HTML con `add_query_arg()` — sin peticiones AJAX para paginación ni filtrado por departamento.

**Alternativa considerada:** AJAX con `wp_ajax_nopriv_` para renderizar fragmentos de HTML. Descartada: añade un handler PHP extra, un nonce público, y duplica la lógica de renderizado. Los links son simples, bookmarkeables y funcionan sin JS.

**Parámetros de URL:**
| Param | Tipo | Default | Descripción |
|---|---|---|---|
| `ldap_page` | int ≥ 1 | `1` | Página actual |
| `ldap_search` | string | `''` | Texto de búsqueda |
| `ldap_dept` | string | `''` | Departamento activo |

Prefijo `ldap_` para evitar colisión con query vars de WordPress/WooCommerce.

### 2. Búsqueda por form submit, no por keystroke

**Decisión:** El `<input type="search">` pasa a ser un `<form method="get">` que incluye los params actuales como `<input type="hidden">`. La búsqueda se dispara al hacer Enter o clicar un botón.

**Alternativa considerada:** AJAX con debounce de 300ms. Descartada para esta iteración: añade complejidad sin ganancia visible para el usuario típico de un directorio corporativo (busca nombre específico, no browsing incremental).

### 3. Filtrado en PHP sobre el array en caché

**Flujo:**
```
shortcode render()
  ↓ load from LDAP_ED_Cache::get()
  ↓ $all_users — array completo (para contar departamentos)
  ↓ array_filter: departamento (si ldap_dept)
  ↓ array_filter: búsqueda (si ldap_search) — name+email+title+dept+phone
  ↓ $total = count($filtered)
  ↓ array_slice($filtered, ($page-1)*$per_page, $per_page) → $page_users
  ↓ extract departments from $all_users (array_unique + sort)
  ↓ render template
```

La lista de departamentos se extrae **del array completo** (no del filtrado) para que los contadores en los chips reflejen el total real de cada departamento, no los restantes tras otro filtro.

### 4. Chips: scroll horizontal implícito con CSS puro

**Decisión:** `overflow-x: auto; white-space: nowrap` en `.ldap-dept-filters`. Sin JS de scroll. Sin indicador de gradiente (el clipping natural sugiere scroll).

Los chips del lado derecho se recortarán visualmente, indicando scroll disponible — consistente con el patrón estándar de iOS/Android para listas de categorías.

### 5. Compatibilidad con page builders

Elementor y BB delegan a `do_shortcode('[ldap_directory ...]')`. El shortcode lee `$_GET` directamente, lo que funciona en peticiones reales. En la vista previa del editor (iframe) los query params no están disponibles — se mostrará la primera página sin filtros, lo cual es el comportamiento actual y aceptable.

### 6. Eliminación de lógica client-side en directory.js

`render()`, `matchesQuery()`, `initDirectory()` y el loop `allCards.forEach` desaparecen. El JS restante solo gestiona:
- Submit del form de búsqueda al presionar Enter (comportamiento nativo de `<form>` lo cubre sin JS)
- Nada más — el archivo podría quedar vacío o muy reducido

## Risks / Trade-offs

| Riesgo | Mitigación |
|---|---|
| Dos shortcodes en la misma página comparten query params | Limitación conocida, documentada. Caso de uso múltiple-shortcode no está en scope. |
| `ldap_search` / `ldap_dept` llegan como input no confiable | `sanitize_text_field()` obligatorio al leer `$_GET`. Comparación con `stripos()` sin evaluar. |
| 500 empleados × `stripos()` en 5 campos = 2500 comparaciones por request | Benchmarked en PHP: < 5ms para 500 registros. Aceptable. |
| Caché miss en el primer request: `get_users()` tarda | Comportamiento actual sin cambio. El stale fallback cubre outages de LDAP. |
| El form `method="get"` añade params de WP a la URL | Usar `add_query_arg()` con la URL base correcta y preservar solo los params propios (`ldap_*`). |
