( function( window, undefined ) {
	'use strict';

	jQuery( function( $ ) {

		/* ======== *
		 * Tooltips *
		 * ======== */

		// Initialize Tooltips (tiptip.js).
		$( '.simcal-help-tip' ).tipTip( {
			attribute: 'data-tip',
			delay: 200,
			fadeIn: 50,
			fadeOut: 50
		} );

		// Tooltips to ease shortcode copying.
		$( '.simcal-shortcode-tip' ).tipTip( {
			activation: 'click',
			defaultPosition: 'top',
			delay: 200,
			fadeIn: 50,
			fadeOut: 50
		} );

		/* ========== *
		 * Meta Boxes *
		 * ========== */

		var // Calendar Settings Meta Box
			calendarSettings = $( '#simcal-calendar-settings' ),
			simCalBoxHandle = calendarSettings.find( '.simcal-box-handle' ),
			boxHandle = calendarSettings.find( '.hndle' );

		// Move the into the Meta Box header handle.
		$( simCalBoxHandle ).appendTo( boxHandle );
		$( function() {
			// Prevent inputs in meta box headings opening/closing contents.
			$( boxHandle ).unbind( 'click.postboxes' );
			calendarSettings.on( 'click', 'h3.hndle', function( event ) {
				// If the user clicks on some form input inside the h3 the box should not be toggled.
				if ( $( event.target ).filter( 'input, option, label, select' ).length ) {
					return;
				}
				calendarSettings.toggleClass( 'closed' );
			});

		});

		// Tabbed Panels in Settings Meta Box.
		$( document.body ).on( 'simcal-init-tabbed-panels', function() {
			$( '.simcal-tabs' ).show();
			$( '.simcal-tabs a' ).click( function() {
				var panel_wrap = $( this ).closest( 'div.simcal-panels-wrap' );
				$( 'ul.simcal-tabs li', panel_wrap ).removeClass( 'active' );
				$( this ).parent().addClass( 'active' );
				$( 'div.simcal-panel', panel_wrap ).hide();
				$( $( this ).attr( 'href' ) ).show();
				return false;
			} );
			$( 'div.simcal-panels-wrap' ).each( function() {
				$( this ).find( 'ul.simcal-tabs > li' ).eq( 0 ).find( 'a' ).click();
			} );
		} ).trigger( 'simcal-init-tabbed-panels' );

		// Swap feed type tabs and panels according to selection.
		$( '#_feed_type' ).on( 'change', function() {

			var selected = $( this ).find( 'option:selected'),
				feed     = selected.val(),
				ul       = $( '.simcal-tabs' ),
				tabs     = ul.find( '> .simcal-feed-type' ),
				tab      = ul.find( '> .simcal-feed-type-' + feed),
				a        = ul.find( '> li:first-child > a' );

			tabs.each( function() {
				$( this ).hide();
			} );
			tab.show();
			a.trigger( 'click' );

		} ).trigger( 'change' );

		// Brings back the meta box after all the manipulations above.
		calendarSettings.show();

		// Toggle default calendar settings.
		var defCalViews = $( '#_calendar_view_default-calendar' ),
			defCalSettings = $( '#default-calendar-settings' ),
			gridSettings = defCalSettings.find( '.simcal-default-calendar-grid' ),
			listSettings = defCalSettings.find( '.simcal-default-calendar-list' ),
			groupedListSettings = defCalSettings.find( '.simcal-default-calendar-list-grouped' );

		defCalViews.on( 'change', function() {

			var selView = $( this ).val();

			if ( 'grid' == selView ) {
				listSettings.hide();
				groupedListSettings.hide();
				gridSettings.show();
			} else if ( 'list' == selView ) {
				gridSettings.hide();
				groupedListSettings.hide();
				listSettings.show();
			} else if ( 'list-grouped' == selView ) {
				gridSettings.hide();
				listSettings.hide();
				groupedListSettings.show();
			}

		} ).trigger( 'change' );

		/* ============ *
		 * Input Fields *
		 * ============ */

		// Select2 enhanced select.
		$( '.simcal-field-select-enhanced' ).each( function( e, i ) {

			var field      = $( i ),
				noResults  = field.data( 'noresults' ),
				allowClear = field.data( 'allowclear' );

			field.select2( {
				allowClear: allowClear != 'undefined' ? allowClear : false,
				placeholder: {
					id: '',
					placeholder: ''
				},
				dir: simcal_admin.text_dir != 'undefined' ? simcal_admin.text_dir : 'ltr',
				tokenSeparators: [','],
				width: '100%',
				language: {
					noResults: function() {
						return noResults != 'undefined' ? noResults : '';
					}
				}
			} );
		} );

		// jQuery Date Picker.
		var fieldDatePicker = $( '.simcal-field-date-picker' );
		fieldDatePicker.each( function( e, i ) {

			var input = $( i ).find( 'input' ),
				args  = {
					autoSize: true,
					changeMonth: true,
					changeYear: true,
					dateFormat: 'yy-mm-dd',
					firstDay: 1,
					prevText: '<i class="simcal-icon-left"></i>',
					nextText: '<i class="simcal-icon-right"></i>',
					yearRange: '1900:2050',
					beforeShow: function( input, instance ) {
						$( '#ui-datepicker-div' ).addClass( 'simcal-date-picker' );
					}
				};

			$( input ).datepicker( args );
			$( input ).datepicker( 'option', $.datepicker.regional[ simcal_admin.locale ] );

		} );

		// Datetime formatter field.
		var fieldDateTime = $( '.simcal-field-datetime-format' );
		fieldDateTime.sortable( {
			items: '> div',
			stop: function() {
				formatDateTime( $( this ) );
			}
		} );
		fieldDateTime.each( function( e, i ) {

			var select = $( i ).find( '> div select' );

			select.each( function( e, i ) {
				$( i ).on( 'change', function() {
					formatDateTime( $( this ).closest( 'div.simcal-field-datetime-format' ) );
				});
			});

			formatDateTime( i );
		} );
		// Helper function for datetime formatter field.
		function formatDateTime( field ) {

			var input   = $( field ).find( 'input' ),
				select  = $( field ).find( '> div select' ),
				code    = $( field ).find( 'code' ),
				format  = '',
				preview = '';

			select.each( function( i, e ) {

				var value = $( e ).val(),
					selected = $( e ).find( '> option:selected' );

				if ( value.length ) {
					if ( selected.data( 'trim' ) ) {
						format  = format.trim() + $( e ).val();
						preview = preview.trim() + selected.data( 'preview' );
					} else {
						format  += $( e ).val() + ' ';
						preview += selected.data( 'preview' ) + ' ';
					}
				}

			});

			input.val( format );
			code.text( preview );
		}
		// If PHP datetime formatter is used, this will live preview the user input.
		$( '.simcal-field-datetime-format-php' ).each( function( e, i ) {

			var input   = $( i ).find( 'input' ),
				preview = $( i ).find( 'code' );

			$( input ).on( 'keyup', function() {

				var data = {
					action: 'simcal_date_i18n_input_preview',
					value: input.val()
				};
				$.post( simcal_admin.ajax_url, data, function( response ) {
					$( preview ).text( response.data );
				} );

			} );

		} );

		/* =============== *
		 * Input Fields UI *
		 * =============== */

		// Enforce min or max value on number inputs
		$( 'input[type="number"].simcal-field' ).each( function( e, i ) {

			var field = $( i ),
				min   = field.attr( 'min' ),
				max   = field.attr( 'max' );

			field.on( 'change', function() {

				var value = $( this ).val();

				if ( min && ( value < min ) ) {
					$( this ).val( min );
				}

				if ( max && ( value > max ) ) {
					$( this ).val( max );
				}
			} );

		} );

		// Show or hide a field when an option is selected.
		$( '.simcal-field-switch-other' ).on( 'change', function() {

			var options = $( this ).find( 'option' );

			options.each( function( e, option ) {

				var show      = $( option ).data( 'show-field' ),
					showMany  = $( option ).data( 'show-fields'),
					hide      = $( option ).data( 'hide-field' ),
					hideMany  = $( option ).data( 'hide-fields' );

				var	fieldShow = show ? $( '#' + show ) : '',
					fieldHide = hide ? $( '#' + hide ) : '';

				if ( $( option ).is( ':selected' ) ) {
					if ( fieldShow ) {
						fieldShow.show();
					}
					if ( fieldHide ) {
						fieldHide.hide();
					}
					if ( showMany ) {
						var s = hideMany.split( ',' );
						$( s ).each( function( e, field ) {
							$( '#' + field ).hide();
						} );
					}
					if ( hideMany ) {
						var h = hideMany.split( ',' );
						$( h ).each( function( e, field ) {
							$( '#' + field ).hide();
						} );
					}
				}
			} );

		} ).trigger( 'change' );

		// Show another field based on the selection of a field.
		$( '.simcal-field-show-other' ).on( 'change', function() {

			var options = $( this ).find( 'option' );

			options.each( function( e, option ) {

				var id      = $( option ).data( 'show-field' ),
					field   = id.length ? $( '#' + id ) : '',
					next    = id.length ? field.next()  : '';

				if ( field.length ) {
					if ( $( option ).is( ':selected' ) ) {
						field.show();
						if ( next.hasClass( 'select2' ) ) {
							next.show();
						}
					} else {
						field.hide();
						if ( next.hasClass( 'select2' ) ) {
							next.hide();
						}
					}
				}

			} );

		} ).trigger( 'change' );

		// Show the next field when a particular option is chosen in a field.
		$( '.simcal-field-show-next' ).on( 'change', function() {

			var value,
				trigger,
				el,
				next;

			if ( $( this ).is( ':checkbox' ) ) {

				el = $( this ).parent().next();

				if ( $( this ).is( ':checked' ) ) {
					el.show();
				} else {
					el.hide();
				}

			} else {

				value = $( this ).val();
				trigger = $( this ).data( 'show-next-if-value' );
				el = $( this ).nextUntil().not( 'i' );
				next = el.length ? el.next() : '';

				if (value == trigger) {
					el.show();
					if ( next.hasClass( 'select2' ) ) {
						next.show();
					}
				} else {
					el.hide();
					if ( next.hasClass( 'select2' ) ) {
						next.hide();
					}
				}

			}

		} ).trigger( 'change' );

		/* ==== *
		 * Misc *
		 * ==== */

		$( '#simcal-clear-cache' ).on( 'click', function( e ) {

			e.preventDefault();

			var spinner = $( this ).find( 'i' );

			$.ajax( {
				url       : simcal_admin.ajax_url,
				method    : 'POST',
				data      : {
					action: 'simcal_clear_cache',
					id    : $( this ).data( 'id' )
				},
				beforeSend: function() {
					spinner.fadeToggle();
				},
				success   : function() {
					spinner.fadeToggle();
				},
				error     : function( response ) {
					console.log( response );
				}
			} );

		} );

	} );

} )( this );
