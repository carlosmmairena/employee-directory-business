## 1. Paginación y filtrado server-side en class-shortcode.php

- [x] 1.1 Agregar método privado `get_query_params()` que lea y sanitice `$_GET['ldap_page']` (absint, mínimo 1), `$_GET['ldap_search']` (sanitize_text_field) y `$_GET['ldap_dept']` (sanitize_text_field)
- [x] 1.2 Agregar método privado `filter_users( array $users, string $search, string $dept ): array` que aplique filtro de departamento (strcasecmp) y búsqueda (stripos en name/email/title/department/phone)
- [x] 1.3 Agregar método privado `extract_departments( array $all_users ): array` que devuelva array de departamentos únicos no vacíos ordenados alfabéticamente con sus conteos
- [x] 1.4 Agregar método privado `paginate_users( array $users, int $page, int $per_page ): array` que devuelva solo el slice correspondiente y los metadatos: `total`, `total_pages`, `current_page`, `offset`
- [x] 1.5 Refactorizar `render()` para llamar a estos métodos y pasar al template las nuevas variables: `$departments`, `$current_dept`, `$current_page`, `$total_pages`, `$total_count`, `$all_count`, `$search_query`
- [x] 1.6 Agregar método privado `build_nav_url( int $page ): string` que use `add_query_arg()` preservando `ldap_search` y `ldap_dept` en la URL actual (`get_pagenum_link` o `remove_query_arg` + `add_query_arg`)

## 2. Template public/views/directory.php

- [x] 2.1 Reemplazar el bloque de buscador existente con un `<form method="get">` que incluya `<input type="hidden">` para `ldap_dept` (si activo) y el input de búsqueda; el submit ocurre por Enter o botón
- [x] 2.2 Agregar bloque de barra de departamentos `.ldap-dept-filters` (condicional: solo si `$enable_search && count($departments) >= 2`); iterar `$departments` para generar chips con clase `is-active` cuando corresponda e incluir enlace × en el chip activo
- [x] 2.3 Eliminar los atributos `data-*` del `<article>` que solo usaba el JS client-side (`data-name`, `data-email`, `data-title`, `data-department`, `data-phone`) — ya no son necesarios para búsqueda
- [x] 2.4 Actualizar `<div class="ldap-directory-wrap">` para eliminar `data-per-page` y `data-total` (ya no los usa el JS)
- [x] 2.5 Reemplazar el `<nav class="ldap-pagination">` con links `<a>` construidos desde `$prev_url` y `$next_url`; renderizar botón Anterior con `aria-disabled="true"` en página 1 y Siguiente en última página
- [x] 2.6 Actualizar el texto de `.ldap-page-info` para mostrar "Mostrando X–Y de Z" o "Mostrando X–Y de Z en [Dept]" usando `esc_html()` y `absint()` en los valores numéricos

## 3. CSS public/css/directory.css

- [x] 3.1 Agregar reglas para `.ldap-dept-filters`: `overflow-x: auto; white-space: nowrap; display: flex; gap: 8px; padding-bottom: 4px` (scroll horizontal implícito)
- [x] 3.2 Agregar reglas para `.ldap-dept-chip`: estilos base del chip (padding, border-radius, border, font-size, cursor, text-decoration: none, color)
- [x] 3.3 Agregar reglas para `.ldap-dept-chip.is-active`: fondo primario (`var(--ldap-primary-color)`), texto blanco, sin borde
- [x] 3.4 Agregar reglas para `.ldap-dept-chip .ldap-dept-chip-clear`: el × dentro del chip activo (opacity, font-weight, margin-left)
- [x] 3.5 Agregar reglas para `.ldap-phone` faltantes: mismo tratamiento que `.ldap-email` (color primario, font-size .88em, text-decoration none, word-break break-all, transition opacity en hover)
- [x] 3.6 Agregar reglas para `.ldap-btn[aria-disabled="true"]`: opacity 0.38, pointer-events none (reemplaza el `disabled` de `<button>` que ya no aplica a `<a>`)
- [x] 3.7 Agregar reglas para el formulario de búsqueda `.ldap-search-form` si se requieren ajustes de layout adicionales

## 4. JS public/js/directory.js

- [x] 4.1 Eliminar las funciones `initDirectory()`, `render()`, `matchesQuery()` y el event listener de paginación
- [x] 4.2 Verificar que el formulario de búsqueda funciona por submit nativo (Enter); si se necesita algún comportamiento adicional (ej. limpiar campo vacío antes de submit para no añadir `?ldap_search=` a la URL), agregar solo ese handler mínimo

## 5. Internacionalización

- [x] 5.1 Agregar strings `__()` para "Mostrando %1$s–%2$s de %3$s" y "Mostrando %1$s–%2$s de %3$s en %4$s" con comentarios `/* translators: */` correctos (línea inmediatamente anterior al `__()`)
- [x] 5.2 Agregar string para "Todos (%s)" del chip All y verificar que los strings de paginación (Anterior/Siguiente) ya existen o añadirlos

## 6. Bump de versión y changelog

- [x] 6.1 Incrementar `LDAP_ED_VERSION` en `ldap-staff-directory.php` (header + constante)
- [x] 6.2 Actualizar `Stable tag` en `readme.txt`
- [x] 6.3 Añadir entrada en `== Changelog ==` de `readme.txt` describiendo filtro por departamento, paginación server-side y fix de `.ldap-phone`
