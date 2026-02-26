=== LDAP Employee Directory ===
Contributors:      carlosmmairena
Tags:              ldap, ldaps, directory, employees, staff, elementor, beaver-builder
Requires at least: 5.8
Tested up to:      6.7
Requires PHP:      7.4
Stable tag:        1.0.2
License:           GPLv2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Connects to an LDAPS server and displays an employee directory from a specific OU. Supports Elementor, Beaver Builder and a native shortcode.

== Description ==

**LDAP Employee Directory** lets you connect your WordPress site to an LDAP / LDAPS server and publish a public employee directory with zero manual data entry.

= Features =

* Connects to any LDAP or LDAPS server (including Active Directory, OpenLDAP, Samba)
* Configurable Base OU and Bind DN
* Extracts: full name, email, job title, department
* Shortcode `[ldap_directory]` usable in any post, page or widget
* Native Elementor widget with full style controls
* Native Beaver Builder module with General / Style / Advanced tabs
* Real-time client-side search (no page reload)
* Client-side pagination with configurable items per page
* Transient-based cache with configurable TTL and one-click invalidation
* Admin panel under **Settings → LDAP Directory**
* SSL certificate verification toggle (supports self-signed certs)
* Optional CA certificate file path
* CSS custom properties for easy theme integration
* Custom CSS textarea in admin panel and in page builder controls
* Multisite compatible (per-site settings)

= Requirements =

* PHP `ldap` extension enabled on the server
* WordPress 5.8 or higher
* PHP 7.4 or higher

== Installation ==

1. Upload the `employee-directory-business` folder to `/wp-content/plugins/`.
2. Activate the plugin through **Plugins → Installed Plugins**.
3. Go to **Settings → LDAP Directory** and fill in your LDAP connection details.
4. Click **Test Connection** to verify the settings.
5. Insert `[ldap_directory]` in any post or page, or use the Elementor / Beaver Builder widget.

== Frequently Asked Questions ==

= The plugin says "PHP LDAP extension not found". What should I do? =

Contact your hosting provider and ask them to enable the `php-ldap` (or `php7.x-ldap`) extension. On Linux servers you can typically install it with:
`sudo apt-get install php-ldap` or `sudo yum install php-ldap`.

= My server uses a self-signed SSL certificate. How do I connect? =

Go to **Settings → LDAP Directory → LDAP Connection** and uncheck **Verify SSL Certificate**. Save, then click **Test Connection**.

= How do I connect to Active Directory? =

Use `ldaps://your-dc.domain.com` as the server, port `636`, and a service account DN such as `CN=svc-wordpress,OU=ServiceAccounts,DC=domain,DC=com`.

= Can I show only certain fields? =

Yes. In the admin panel, check or uncheck the fields you want. You can also override per shortcode:
`[ldap_directory fields="name,title"]`

= How do I change the card layout or colors? =

Use the **Custom CSS** textarea in the admin panel, or override the CSS variables:
`--ldap-primary-color`, `--ldap-card-bg`, `--ldap-columns`, etc.

= How long is data cached? =

By default 60 minutes. Change the TTL under **Settings → LDAP Directory → Cache** or flush immediately with the **Clear Cache** button.

== Screenshots ==

1. Admin settings page — connection and display options
2. Employee directory rendered with the default style
3. Elementor widget controls
4. Beaver Builder module tabs

== Changelog ==

= 1.0.2 =
* Feat: Added `telephoneNumber` field — read from LDAP, displayed on cards as a clickable `tel:` link, included in client-side search, and available in admin panel, Elementor and Beaver Builder controls.
* Feat: New "Exclude Disabled Accounts" setting (connection section) — filters out disabled Active Directory accounts using the `userAccountControl` bit flag. Leave unchecked for OpenLDAP/other servers.
* Feat: Resilient cache — when the LDAP server is unreachable after cache expiry, the last successfully fetched data (stale copy) is served silently to visitors. Only a manual "Clear Cache" action removes the stale copy entirely.

= 1.0.1 =
* Fix: LDAP server URL no longer lost on save — replaced `esc_url_raw()` (which strips `ldap://`/`ldaps://` schemes) with a dedicated sanitizer that validates the scheme and shows an admin error on invalid input.
* Fix: Added runtime admin notice when the PHP LDAP extension is missing, covering cases where the extension is disabled after activation or the plugin is activated via WP-CLI/DB without going through the activation hook.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
Initial release — no upgrade steps required.
