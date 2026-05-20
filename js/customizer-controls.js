/**
 * Customizer Controls JavaScript
 * Phone mask input for footer phone field
 */
/* global wp, jQuery */
(function($) {
	'use strict';

	// Wait for customizer to be ready
	wp.customize.bind( 'ready', () => {
		const phoneInput = $( '#customize-control-footer_phone input[type="text"]' );

		if ( phoneInput.length ) {
			function applyPhoneMask( input ) {
				let value = input.value.replace( /\D/g, '' );

				if ( value.length > 0 ) {
					if ( value[0] === '7' ) {
						value = value.substring( 1 );
					}

					let formatted = '+7';

					if ( value.length > 0 ) {
						formatted += '(' + value.substring( 0, 3 );
					}
					if ( value.length >= 4 ) {
						formatted += ')' + value.substring( 3, 6 );
					}
					if ( value.length >= 7 ) {
						formatted += '-' + value.substring( 6, 8 );
					}
					if ( value.length >= 9 ) {
						formatted += '-' + value.substring( 8, 10 );
					}

					input.value = formatted;
				} else {
					input.value = '+7(';
				}
			}

			phoneInput.on( 'input', function () {
				applyPhoneMask( this );
			} );

			phoneInput.on( 'paste', function () {
				const self = this;
				setTimeout( () => applyPhoneMask( self ), 10 );
			} );

			phoneInput.on( 'focus', function () {
				if ( !this.value ) {
					this.value = '+7(';
				}
			} );

			if ( phoneInput.val() && phoneInput.val() !== '+7(' ) {
				applyPhoneMask( phoneInput[0] );
			}
		}
	} );

})( jQuery );
