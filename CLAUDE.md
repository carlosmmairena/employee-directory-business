# LDAP Employee Directory — Claude Code Guide

## Project Overview

WordPress plugin (GPL v2) that connects a site to an LDAP/LDAPS server and renders a public employee directory. Supports a native shortcode, an Elementor widget, and a Beaver Builder module.

- **Plugin slug:** `ldap-employee-directory`
- **Text domain:** `ldap-employee-directory`
- **Constant/class prefix:** `LDAP_ED_`
- **Option key:** `ldap_ed_settings` (constant `LDAP_ED_OPTION_KEY`)
- **Cache transient key:** `ldap_ed_users` (constant `LDAP_ED_CACHE_KEY`)
- **Requires:** PHP 7.4+, WordPress 5.8+, PHP `ldap` extension

## File Structure

```
ldap-employee-directory.php          # Main file: constants, autoloader, bootstrap hooks
includes/
  class-ldap-connector.php           # LDAP_ED_Connector  — connect/bind/search/test
  class-cache.php                    # LDAP_ED_Cache      — WP Transients wrapper
  class-admin.php                    # LDAP_ED_Admin      — Settings API, admin page
  class-ajax.php                     # LDAP_ED_Ajax       — test connection + clear cache
  class-shortcode.php                # LDAP_ED_Shortcode  — [ldap_directory] shortcode
elementor/
  class-elementor-widget.php         # LDAP_ED_Elementor_Widget
beaver-builder/
  class-bb-module.php                # LDAP_ED_BB_Module
  frontend.php                       # BB module front-end template
admin/
  views/settings-page.php            # Admin settings page HTML
  css/admin.css
  js/admin.js                        # AJAX: test connection, clear cache
public/
  views/directory.php                # Front-end directory card grid
  css/directory.css                  # Styles using CSS custom properties
  js/directory.js                    # Client-side search + pagination (vanilla JS)
uninstall.php                        # Cleanup on uninstall
readme.txt                           # WordPress.org readme
```

## Architecture

- **Bootstrap:** `plugins_loaded` → `ldap_ed_init()` instantiates Admin, Ajax, Shortcode. Page builder integrations are lazy-registered only when the builder is active.
- **Autoloader:** `ldap_ed_autoload()` maps class names to files via a manual `$map` array — no PSR-4.
- **Data flow:** Shortcode/widget → `LDAP_ED_Cache::get()` → on miss → `LDAP_ED_Connector::get_users()` → store result via `LDAP_ED_Cache::set()`.
- **Caching:** WP Transients. TTL default 60 min. Cache is flushed automatically when settings are saved and manually via the admin AJAX action.
- **Page builders:** Both Elementor widget and BB module delegate rendering to `do_shortcode('[ldap_directory ...]')`.
- **Styling:** CSS custom properties (`--ldap-primary-color`, `--ldap-card-bg`, `--ldap-columns`, `--ldap-gap`, `--ldap-card-radius`, `--ldap-text-color`) enable theme integration without overriding selectors.

## Coding Standards

Follow **WordPress Coding Standards (WPCS)**:

- Escape all output: `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_*`.
- Sanitize all input: `sanitize_text_field()`, `absint()`, `esc_url_raw()`, `wp_strip_all_tags()`.
- Use `WP_Error` for error returns from `LDAP_ED_Connector` methods.
- Nonces on every AJAX action (`check_ajax_referer`) and capability checks (`current_user_can('manage_options')`).
- Prefix all functions, classes, constants, option names with `ldap_ed_` / `LDAP_ED_`.
- Silence LDAP PHP functions (`@ldap_*`) — they trigger warnings on failure; use `ldap_error()` to capture the message.
- Add `/* translators: ... */` comments before every `sprintf`+`__()` call.

## Key Settings (stored in `ldap_ed_settings` option)

| Key | Type | Default | Description |
|---|---|---|---|
| `server` | string | `ldaps://` | LDAP server URL |
| `port` | int | `636` | LDAP port |
| `bind_dn` | string | `''` | Service account DN |
| `bind_pass` | string | `''` | Bind password (never echoed back) |
| `base_ou` | string | `''` | Base OU for search |
| `verify_ssl` | `'0'`/`'1'` | `'1'` | SSL cert verification |
| `ca_cert` | string | `''` | Path to CA .pem file |
| `fields` | array | `['name','email','title','department']` | Fields to display |
| `per_page` | int | `20` | Items per page |
| `enable_search` | `'0'`/`'1'` | `'1'` | Show search bar |
| `custom_css` | string | `''` | Custom CSS injected inline |
| `cache_ttl` | int | `60` | Cache TTL in minutes |

## Shortcode

```
[ldap_directory]
[ldap_directory fields="name,title" per_page="10" search="false"]
```

Attributes override the admin panel defaults. Allowed fields: `name`, `email`, `title`, `department`.

## AJAX Actions (admin-only, nonce: `ldap_ed_admin_nonce`)

- `ldap_ed_test_connection` — calls `LDAP_ED_Connector::test_connection()`
- `ldap_ed_clear_cache` — calls `LDAP_ED_Cache::flush()`

## Adding a New Feature Checklist

1. Add the class to `includes/` and register it in `ldap_ed_autoload()`.
2. Instantiate it inside `ldap_ed_init()` if needed.
3. Add new settings fields via `LDAP_ED_Admin::register_settings()` + a `render_field_*` method + sanitization in `sanitize_settings()`.
4. Escape output, sanitize input, add nonce/capability checks on any new AJAX handler.
5. Update `readme.txt` changelog and bump `LDAP_ED_VERSION` / plugin header version consistently.
