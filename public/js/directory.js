/**
 * LDAP Staff Directory — public JS
 * Filtering and pagination are now server-side. This file strips empty
 * ldap_search from the form submission to keep URLs clean.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		document.querySelectorAll( '.ldap-search-form' ).forEach( function ( form ) {
			form.addEventListener( 'submit', function () {
				var input = form.querySelector( '[name="ldap_search"]' );
				if ( input && '' === input.value.trim() ) {
					input.disabled = true;
				}
			} );
		} );
	} );
} () );
