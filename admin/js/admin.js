/**
 * LDAP Staff Directory — admin JS
 * Handles: Test Connection + Clear Cache buttons.
 */
( function ( $ ) {
	'use strict';

	$( function () {
		// ── Test Connection ─────────────────────────────────────────────────
		$( '#ldap-ed-test-btn' ).on( 'click', function () {
			const $btn      = $( this );
			const $result   = $( '#ldap-ed-test-result' );
			const labelOrig = $btn.text();

			$btn.prop( 'disabled', true ).text( ldapEdAdmin.i18n.testing );
			$result.removeClass( 'is-success is-error' ).hide();

			$.post( ldapEdAdmin.ajaxUrl, {
				action: 'ldap_ed_test_connection',
				nonce:  ldapEdAdmin.nonce,
			} )
			.done( function ( res ) {
				if ( res.success ) {
					$result.addClass( 'is-success' ).text( res.data.message ).show();
				} else {
					$result.addClass( 'is-error' ).text( res.data.message ).show();
				}
			} )
			.fail( function ( xhr ) {
				$result.addClass( 'is-error' ).text( 'HTTP ' + xhr.status + ': ' + xhr.statusText ).show();
			} )
			.always( function () {
				$btn.prop( 'disabled', false ).text( labelOrig );
			} );
		} );

		// ── Clear Cache ─────────────────────────────────────────────────────
		$( '#ldap-ed-clear-cache-btn' ).on( 'click', function () {
			const $btn      = $( this );
			const $result   = $( '#ldap-ed-cache-result' );
			const labelOrig = $btn.text();

			$btn.prop( 'disabled', true ).text( ldapEdAdmin.i18n.clearing );
			$result.removeClass( 'is-success is-error' ).hide();

			$.post( ldapEdAdmin.ajaxUrl, {
				action: 'ldap_ed_clear_cache',
				nonce:  ldapEdAdmin.nonce,
			} )
			.done( function ( res ) {
				if ( res.success ) {
					$result.addClass( 'is-success' ).text( res.data.message ).show();
				} else {
					$result.addClass( 'is-error' ).text( res.data.message ).show();
				}
			} )
			.fail( function ( xhr ) {
				$result.addClass( 'is-error' ).text( 'HTTP ' + xhr.status + ': ' + xhr.statusText ).show();
			} )
			.always( function () {
				$btn.prop( 'disabled', false ).text( labelOrig );
			} );
		} );
	} );
} ( jQuery ) );
