var epdp_admin_vars;
jQuery(document).ready(function ($) {

	// Setup Chosen menus
	$('.epdp_select_chosen').chosen({
		inherit_select_classes: true,
	});

    if ( epdp_admin_vars.hide_save_settings )	{
		$('#submit').hide();
	}

	// Color Picker
	var epdp_color_picker = $('.epdp-color-picker');

	if ( epdp_color_picker.length ) {
		epdp_color_picker.wpColorPicker();
	}

	// Highlight shortcode when clicked
	$( document.body ).on( 'click', '.epdp_copy_shortcode', function() {
		this.focus(); this.select();
	});

	/**
	 * Settings
	 */
	var EPDP_Settings = {
		init : function() {
			this.api();	
		},
		api : function() {
            // Highlight phrase when clicked
            $( document.body ).on( 'click', '#epdp-phrase', function() {
                this.focus(); this.select();
            });

            // Re-enter remote secret
            $( document.body ).on( 'click', '#epd-remote-phrase-button', function(e) {
                e.preventDefault();

                var postData = {
                    action : 'epdp_reenter_remote_phrase'
                };

                $.ajax({
					type: 'POST',
					dataType: 'json',
					data: postData,
					url: ajaxurl,
					beforeSend: function()	{
                        $('#epd-remote-phrase-button').html( epdp_admin_vars.please_wait );
						$('#epd-remote-phrase-button').prop( 'disabled', true );
                        $('#epd-remote-phrase-div').remove();
					},
					success: function (response) {
						if ( true === response.success && '' !== response.data.input )	{
							$('#epd-remote-phrase-button').replaceWith( response.data.input );
						}
					}
				}).fail(function (data) {
					if ( window.console && window.console.log ) {
						console.log( data );
					}
				});
            });

			// Toggle disabled attribute for reveal secret button
			$( document.body ).on( 'change', '.epdp_enable_rest', function() {
                var checked = $(this).is(':checked');
                if ( checked ) {
                    $('#epdp-reveal-secret-button').prop( 'disabled', false );
                    $('#epdp-regenerate-secret').prop( 'disabled', false );
                } else	{
					$('#epdp-reveal-secret-button').prop( 'disabled', true );
                    $('#epdp-regenerate-secret').prop( 'disabled', true );
				}
			});

			$( document.body ).on( 'click', '#epdp-reveal-secret-button', function(e) {
				e.preventDefault();

				var button = '<button id="epdp-reveal-secret-button" type="button" class="button epd-button">' + epdp_admin_vars.reveal_phrase + '</button>';
                var desc   = '<p class="description">' + epdp_admin_vars.phrase_desc + '</p>';

                var postData = {
                    action : 'epdp_reveal_phrase'
                };

                $.ajax({
					type: 'POST',
					dataType: 'json',
					data: postData,
					url: ajaxurl,
					beforeSend: function()	{
                        $('#epdp-reveal-secret-button').html( epdp_admin_vars.please_wait );
						$('#epdp-reveal-secret-button').prop( 'disabled', true );
                        $('#epdp-regenerate-secret').prop( 'disabled', true );
					},
					success: function (response) {
						if ( true === response.success && '' !== response.data.phrase )	{
                            var phrase_input = '<input type="text" id="epdp-phrase" class="regular-text" readonly="readonly" value="' + response.data.phrase + '" />';
							$('#epdp-api-key-display').html( phrase_input + desc );

                            setTimeout(
                                function()  {
                                    $('#epdp-api-key-display').html( button + desc );
                                    $('#epdp-regenerate-secret').prop( 'disabled', false );
                                },
                                10000
                            );
						}
					}
				}).fail(function (data) {
					if ( window.console && window.console.log ) {
						console.log( data );
					}
				});
			});
            $( document.body ).on( 'click', '#epdp-regenerate-secret', function(e) {
                e.preventDefault();

                var postData = {
                    action: 'epdp_regenerate_phrase'
                };

                $.ajax({
					type: 'POST',
					dataType: 'json',
					data: postData,
					url: ajaxurl,
                    beforeSend: function()	{
                        $('#epdp-regenerate-secret').html( epdp_admin_vars.please_wait );
                        $('#epdp-reveal-secret-button').prop( 'disabled', true );
                        $('#epdp-regenerate-secret').prop( 'disabled', true );
					},
					success: function (response) {
						if ( true === response.success )	{
							$('#epdp-regenerate-secret').html( epdp_admin_vars.done );

                            setTimeout(
                                function()  {
                                    $('#epdp-regenerate-secret').html( epdp_admin_vars.regenerate_key );
                                    $('#epdp-reveal-secret-button').prop( 'disabled', false );
                                    $('#epdp-regenerate-secret').prop( 'disabled', false );
                                },
                                3000
                            );
						}
					}
				}).fail(function (data) {
					if ( window.console && window.console.log ) {
						console.log( data );
					}
				});
            });
		}
	};
    EPDP_Settings.init();

    /**
	 * Demo Configuration Metabox
	 */
	var EPD_Demo_Configuration = {
		init : function() {
			this.add();
            this.options();
			this.clone();
			this.theme();
            this.remove();
		},
        clone_repeatable : function(row) {
            // Retrieve the highest current key
            var key = 1,
				highest = 1;

            row.parent().find( '.epdp_repeatable_row' ).each(function() {
                var current = $(this).data( 'key' );
                if( parseInt( current ) > highest ) {
                    highest = current;
                }
            });
            key = highest += 1;

            clone = row.clone();

            clone.removeClass( 'epdp_add_blank' );

            clone.attr( 'data-key', key );
            clone.find( 'input, select, textarea' ).val( '' ).each(function() {
                var name = $( this ).attr( 'name' );
                var id   = $( this ).attr( 'id' );

                if( name ) {

                    name = name.replace( /\[(\d+)\]/, '[' + parseInt( key ) + ']');
                    $( this ).attr( 'name', name );

                }

                $( this ).attr( 'data-key', key );

                if( typeof id !== 'undefined' ) {

                    id = id.replace( /(\d+)/, parseInt( key ) );
                    $( this ).attr( 'id', id );

                }

            });

            /** manually update any select box values */
            clone.find( 'select' ).each(function() {
                $( this ).val( row.find( 'select[name="' + $( this ).attr( 'name' ) + '"]' ).val() );
            });

            /** manually uncheck any checkboxes */
            clone.find( 'input[type="checkbox"]' ).each(function() {

                // Make sure checkboxes are unchecked when cloned
                var checked = $(this).is(':checked');
                if ( checked ) {
                    $(this).prop('checked', false);
                }

                // reset the value attribute to 1 in order to properly save the new checked state
                $(this).val(1);
            });

            // Remove Chosen elements
            clone.find( '.search-choice' ).remove();
            clone.find( '.chosen-container' ).remove();

            return clone;
        },
        add : function() {
			$( document.body ).on( 'click', '.submit .epdp_add_repeatable', function(e) {
				e.preventDefault();
				var button = $( this ),
				row = button.parent().parent().prev( '.epdp_repeatable_row' ),
				clone = EPD_Demo_Configuration.clone_repeatable(row);

				clone.insertAfter( row ).find('input, textarea, select').filter(':visible').eq(0).focus();

				// Setup chosen fields again if they exist
				clone.find('.epdp_select_chosen').chosen({
					inherit_select_classes: true,
				});
				clone.find( '.epdp_select_chosen' ).css( 'width', '100%' );
                clone.find( '.epdp_select_chosen' ).val( '0' );
                clone.find( '.epdp_select_chosen' ).trigger('chosen:updated');
			});
		},
        options : function()    {
			$( document.body ).on( 'change', '#epdp-add-custom-welcome', function()	{
				$('#epdp-welcome-editor').slideToggle(500);
			});

            $( document.body ).on( 'change', '#epdp-registration-action', function() {
                if ( 'redirect' === $('#epdp-registration-action').val() ) {
                    $('#epdp-redirect-page').show();
                    $('#epdp-redirect-page').css( 'display', 'inline-block' );
                } else  {
                    $('#epdp-redirect-page').hide();
                }
			});
        },
		clone : function()	{
			$( document.body ).on( 'change', '#epdp-clone-site', function() {
                if ( '0' !== $('#epdp-clone-site').val() ) {
                    $('#epdp-clone-plugins').show();
                    $('#epdp-clone-plugins').css( 'display', 'inline-block' );
					$('#epdp-clone-themes').show();
                    $('#clone-themes-action-chosen').css( 'display', 'inline-block' );
                } else  {
                    $('#epdp-clone-plugins').hide();
					$('#epdp-clone-themes').hide();
                }
			});
		},
		theme : function()	{
			$( document.body ).on( 'change', '#hide-appearance-menu', function() {
				$('#epdp-allowed-themes').toggle( 'fast' );
			});
		},
        remove : function() {
			$( document.body ).on( 'click', '.epdp-remove-row, .epdp_remove_repeatable', function(e) {
				e.preventDefault();

				var row   = $(this).parents( '.epdp_repeatable_row' ),
					count = row.parent().find( '.epdp_repeatable_row' ).length,
                    type  = $(this).data('type'),
					repeatable = 'div.epdp_repeatable_' + type + 's',
					focusElement,
					focusable,
					firstFocusable;

                // Set focus on next element if removing the first row. Otherwise set focus on previous element.
                if ( $(this).is( '.ui-sortable .epdp_repeatable_row:first-child .epdp-remove-row, .ui-sortable .epdp_repeatable_row:first-child .epdp_remove_repeatable' ) ) {
                    focusElement  = row.next( '.epdp_repeatable_row' );
                } else {
                    focusElement  = row.prev( '.epdp_repeatable_row' );
                }

                focusable  = focusElement.find( 'select, input, textarea, button' ).filter( ':visible' );
                firstFocusable = focusable.eq(0);


				if ( count > 1 ) {
					$( 'input, select', row ).val( '' );
					row.fadeOut( 'fast' ).remove();
					firstFocusable.focus();
				} else {
					switch( type ) {
						case 'file':
							$( 'input, select', row ).val( '' );
							break;
						default:
							$( 'input, select', row ).val( '' );
					}
				}

				/* re-index after deleting */
				$(repeatable).each( function( rowIndex ) {
					$(this).find( 'input, select' ).each(function() {
						var name = $( this ).attr( 'name' );
						name = name.replace( /\[(\d+)\]/, '[' + rowIndex+ ']');
						$( this ).attr( 'name', name ).attr( 'id', name );
					});
				});
			});
		}
    };
    EPD_Demo_Configuration.init();
});