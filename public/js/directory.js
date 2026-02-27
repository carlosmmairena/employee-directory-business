/**
 * Employee Directory Business — public JS
 * Real-time search + client-side pagination.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', init );

	function init() {
		document.querySelectorAll( '.ldap-directory-wrap' ).forEach( initDirectory );
	}

	function initDirectory( wrap ) {
		const grid       = wrap.querySelector( '.ldap-directory-grid' );
		const searchInput = wrap.querySelector( '.ldap-search' );
		const prevBtn    = wrap.querySelector( '.ldap-prev' );
		const nextBtn    = wrap.querySelector( '.ldap-next' );
		const pageInfo   = wrap.querySelector( '.ldap-page-info' );
		const noResults  = wrap.querySelector( '.ldap-no-results--search' );

		if ( ! grid ) return;

		const perPage    = parseInt( wrap.dataset.perPage, 10 ) || 20;
		let allCards     = Array.from( grid.querySelectorAll( '.ldap-employee-card' ) );
		let visibleCards = allCards.slice();
		let currentPage  = 1;

		// ── Search ────────────────────────────────────────────────────────────
		if ( searchInput ) {
			searchInput.addEventListener( 'input', function () {
				const query = this.value.trim().toLowerCase();
				currentPage = 1;

				visibleCards = query
					? allCards.filter( card => matchesQuery( card, query ) )
					: allCards.slice();

				render();
			} );
		}

		// ── Pagination buttons ────────────────────────────────────────────────
		if ( prevBtn ) {
			prevBtn.addEventListener( 'click', function () {
				if ( currentPage > 1 ) { currentPage--; render(); }
			} );
		}
		if ( nextBtn ) {
			nextBtn.addEventListener( 'click', function () {
				const totalPages = Math.ceil( visibleCards.length / perPage );
				if ( currentPage < totalPages ) { currentPage++; render(); }
			} );
		}

		// Initial render
		render();

		// ── Helpers ───────────────────────────────────────────────────────────

		function matchesQuery( card, query ) {
			return (
				( card.dataset.name       || '' ).includes( query ) ||
				( card.dataset.email      || '' ).includes( query ) ||
				( card.dataset.title      || '' ).includes( query ) ||
				( card.dataset.department || '' ).includes( query ) ||
				( card.dataset.phone      || '' ).includes( query )
			);
		}

		function render() {
			const totalPages = Math.max( 1, Math.ceil( visibleCards.length / perPage ) );
			const start      = ( currentPage - 1 ) * perPage;
			const end        = start + perPage;
			const pageCards  = visibleCards.slice( start, end );

			// Hide all, show only current page of visible cards.
			allCards.forEach( c => ( c.style.display = 'none' ) );
			pageCards.forEach( c => ( c.style.display = '' ) );

			// No-results messages.
			const emptyMsg = grid.querySelector( '.ldap-no-results:not(.ldap-no-results--search)' );
			if ( emptyMsg ) {
				emptyMsg.style.display = allCards.length === 0 ? '' : 'none';
			}
			if ( noResults ) {
				noResults.style.display = ( allCards.length > 0 && visibleCards.length === 0 ) ? '' : 'none';
			}

			// Pagination controls.
			if ( prevBtn ) prevBtn.disabled = currentPage <= 1;
			if ( nextBtn ) nextBtn.disabled = currentPage >= totalPages;
			if ( pageInfo ) {
				pageInfo.textContent = visibleCards.length > 0
					? currentPage + ' / ' + totalPages
					: '';
			}

			// Hide pagination when everything fits on one page.
			const paginationEl = wrap.querySelector( '.ldap-pagination' );
			if ( paginationEl ) {
				paginationEl.style.display = visibleCards.length > perPage ? '' : 'none';
			}
		}
	}
} () );
