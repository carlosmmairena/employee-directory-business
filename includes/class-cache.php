<?php
/**
 * Cache helper — thin wrapper around WP Transients.
 *
 * @package LDAP_Employee_Directory
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LDAP_ED_Cache {

	/** @var string Transient key. */
	private $key;

	/** @var int TTL in seconds. */
	private $ttl;

	/**
	 * Constructor.
	 *
	 * @param string $key Transient key.
	 * @param int    $ttl TTL in seconds (default 3600).
	 */
	public function __construct( string $key = LDAP_ED_CACHE_KEY, int $ttl = 3600 ) {
		$this->key = $key;
		$this->ttl = $ttl;
	}

	/**
	 * Get cached data.
	 *
	 * @return mixed|false False when cache is empty/expired.
	 */
	public function get() {
		return get_transient( $this->key );
	}

	/**
	 * Store data in cache.
	 *
	 * @param mixed $data Data to cache.
	 */
	public function set( $data ) {
		set_transient( $this->key, $data, $this->ttl );
	}

	/**
	 * Invalidate the cache.
	 */
	public function flush() {
		delete_transient( $this->key );
	}

	/**
	 * Return whether a fresh cache entry exists.
	 *
	 * @return bool
	 */
	public function has() {
		return false !== $this->get();
	}
}
