<?php
/**
 * Cache helper — thin wrapper around WP Transients with a permanent stale fallback.
 *
 * The stale fallback is stored as a WP option (no TTL) so that it survives
 * transient expiry. It is served transparently when the TTL transient has
 * expired AND the LDAP server is unreachable, preventing error pages for
 * visitors while the directory is temporarily unavailable.
 *
 * flush()  — removes only the TTL transient (preserves stale).
 * purge()  — removes both the transient and the stale option (full reset).
 *            Use for manual cache clearing and on settings change.
 *
 * @package LDAP_Employee_Directory
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LDAP_ED_Cache {

	/** @var string Transient key (TTL-bound). */
	private $key;

	/** @var int TTL in seconds. */
	private $ttl;

	/** @var string WP option key for the permanent stale copy. */
	private $stale_key;

	/**
	 * Constructor.
	 *
	 * @param string $key Transient key.
	 * @param int    $ttl TTL in seconds (default 3600).
	 */
	public function __construct( string $key = LDAP_ED_CACHE_KEY, int $ttl = 3600 ) {
		$this->key       = $key;
		$this->ttl       = $ttl;
		$this->stale_key = $key . '_stale';
	}

	/**
	 * Get fresh cached data (respects TTL).
	 *
	 * @return mixed|false False when cache is empty/expired.
	 */
	public function get() {
		return get_transient( $this->key );
	}

	/**
	 * Get the last-known-good stale data (ignores TTL).
	 *
	 * @return mixed|false False when no stale data exists.
	 */
	public function get_stale() {
		return get_option( $this->stale_key, false );
	}

	/**
	 * Store data in the TTL transient and update the permanent stale copy.
	 *
	 * @param mixed $data Data to cache.
	 */
	public function set( $data ) {
		set_transient( $this->key, $data, $this->ttl );
		update_option( $this->stale_key, $data, false ); // autoload = false
	}

	/**
	 * Invalidate the TTL transient only. The stale copy is preserved.
	 */
	public function flush() {
		delete_transient( $this->key );
	}

	/**
	 * Fully remove both the TTL transient and the stale option.
	 * Use for manual cache clearing and when connection settings change.
	 */
	public function purge() {
		delete_transient( $this->key );
		delete_option( $this->stale_key );
	}

	/**
	 * Return whether a fresh (within TTL) cache entry exists.
	 *
	 * @return bool
	 */
	public function has() {
		return false !== $this->get();
	}
}
