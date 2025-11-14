/*!
 * Javascript for the shipping method functionality.
 *
 * @package Advanced Shipping Rates for WC
 * @version 2.1.0
 */

jQuery(document).ready(function($) {
	
	var help_langs = ['en', 'en-si', 'es', 'fr', 'de', 'pt', 'it', 'ca', 'fi'];
	var help_langs_name = ['English (IU)', 'English (SI)', 'Español', 'Français', 'Deutsch', 'Português', 'Italiano', 'Català', 'Suomi'];

	/*******************************************************
	    1. Global functions
	 *******************************************************/

	// We're on Fish and ships form?
	if ($("#shipping-rules-table-fns").length != 0) {
		$('html').addClass('wc-fish-and-ships-html');
		$('body').addClass('wc-fish-and-ships');
		if ($('#wc-fns-freemium-panel.pro-version.closed').length != 0) $('body').addClass('wc-fish-and-ships-pro-closed');
		
		// Maybe some FnS notice should be shown
		$('.fns-only-notice').show();
	}

	// Field validation error tips
	$( document.body )
	.on( 'keyup change', '.wc_fns_input_decimal[type=text], .wc_fns_input_positive_decimal[type=text], .wc_fns_input_integer[type=text], .wc_fns_input_positive_integer[type=text]', function(e) {
		
		var regex, error;
		var value = $( this ).val();
		
		if ( $( this ).is( '.wc_fns_input_decimal' )) {

		    regex = new RegExp('^-?[0-9]+(\\' + woocommerce_admin.decimal_point + '?[0-9]*)?$');
			//regex = new RegExp('^-?[0-9]+(\\.?[0-9]*)?$');
			error = 'i18n_decimal_error';
			
			if( e.type=='keyup' && value=='-' ) error = false; // bypass while typing
			
		} else if ( $( this ).is( '.wc_fns_input_positive_decimal' ) ) {

			regex = new RegExp('^[0-9]+(\\' + woocommerce_admin.decimal_point + '?[0-9]*)?$');
			//regex = new RegExp('^[0-9]+(\\.?[0-9]*)?$');
			error = 'i18n_decimal_error';

		} else if ( $( this ).is( '.wc_fns_input_integer' ) ) {

			regex = new RegExp( '^-?[0-9]+$', 'gi' );
			error = 'i18n_fns_integer_error';

			if( e.type=='keyup' && value=='-' ) {
				error = false; // bypass while typing
			} else {
				woocommerce_admin[error] = wcfns_data[error];
			}

		} else {
			regex = new RegExp( '^[0-9]+$', 'gi' ); //wc_fns_input_positive_integer
			error = 'i18n_fns_integer_error';
			woocommerce_admin[error] = wcfns_data[error];
		}

		if ( error && value != '' && !regex.test(value)) {
			$( document.body ).triggerHandler( 'wc_add_error_tip', [ $( this ), error ] );
			$( this ).closest('.currency-fns-field').addClass('fns-error-inside');
		} else {
			$( document.body ).triggerHandler( 'wc_remove_error_tip', [ $( this ), error ] );
			var obj = this;
			setTimeout( function () {
				$( obj ).closest('.currency-fns-field').removeClass('fns-error-inside');
			}, 1000);
		}
	});
	
	$( document.body )
	.on( 'blur', '.wc_fns_input_decimal[type=text], .wc_fns_input_positive_decimal[type=text], .wc_fns_input_integer[type=text], .wc_fns_input_positive_integer[type=text]', function() {
		var obj = this;
		setTimeout( function () {
			$( obj ).closest('.currency-fns-field').removeClass('fns-error-inside');
		}, 1000);
	});

	// Field info tips
	$( document.body ).on( 'focus', '.wc_fns_input_tip', function() {
		
		info_type = $(this).attr('data-wc-fns-tip');
		if (typeof info_type === typeof undefined || info_type === false) return;

		var offset = $(this).position();

		if ( $(this).parent().find( '.wc_fns_info_tip' ).length === 0 ) {
			$('.wc_fns_info_tip').remove();
			
			// Prevent undefined tips
			if ( !wcfns_data[info_type] ) return;
			
			$(this).after( '<div class="wc_fns_info_tip ' + info_type + '">' + wcfns_data[info_type] + '</div>' );
			$(this).parent().find( '.wc_fns_info_tip' )
				.css( 'left', offset.left + $(this).width() - ( $(this).width() / 2 ) - ( $( '.wc_fns_info_tip' ).width() / 2 ) )
				.css( 'top', offset.top + $(this).height() )
				.fadeIn( '100' );
			
				$(this).parent().addClass('wc_fns_focus_tip');
		}
	});

	// Field info tips (exit)
	$( document.body ).on( 'blur', '.wc_fns_input_tip', function() {
		
		$(this).parent().find( '.wc_fns_info_tip' ).remove();
		$(this).parent().removeClass('wc_fns_focus_tip');
	});
	
	/* Unsaved alert */
	if ($("#shipping-rules-table-fns").length != 0) {

		var unsaved = false;
		var sending = false;
		
		/*$(window).on( 'beforeunload', function() {
			if (unsaved && !sending) return wcfns_data.i18n_unsaved;
		});*/
		
		$("#shipping-rules-table-fns, table.form-table").on('change', 'input, select, textarea', function () {
			unsaved = true;
		});
		
		$('#mainform').submit(function () {
			
			// Check if some selector is set to cart items without any group strategy
			var global_groupby  = $('#woocommerce_fish_n_ships_global_group_by').is(':checked');
			var groupby         = $('#woocommerce_fish_n_ships_global_group_by_method').val();
			var ci_non_grouping = [];
			$('select.wc-fns-selection-method').each( function(index, el) 
			{
				if ( !global_groupby ) {
					var sel_wrapper = $(el).closest('.selection_wrapper');
					groupby = $('.field-group_by select', sel_wrapper).val();
				}
				if ( $(el).val() == 'quantity' && groupby == 'none' ) {
					var rule_wrapper = $(el).closest('tr');
					var rule_number  = $('#shipping-rules-table-fns tr').index( rule_wrapper ); // starts with 0, but first one are the header
					
					// Extra fees start again in #1
					var first_rule_extra = $('#shipping-rules-table-fns tr').index( $('#shipping-rules-table-fns tr.fns-ruletype-extra').first() );
					if ( first_rule_extra != -1 && first_rule_extra <= rule_number) rule_number = ( rule_number - first_rule_extra + 1 ) + ' / EXTRA';

					ci_non_grouping.push( '#' + rule_number ); // selection method index +1 matches rule number
				}
			});
			if ( ci_non_grouping.length > 0 ) {
				var message = wcfns_data['i18n_fns_groupby_error'];
				message = message.replace( '%rules%', ci_non_grouping.join(', ') );
				if ( !confirm(message) ) return false;
			}
			// END - Check if some selector is set to cart items without any group strategy
			
			// Check an always selector method with some other selector (superfluous)
			var ci_always_superfluous = [];
			$('.selection-rules-column').each( function(index, selections_wrapper) 
			{
				$('select.wc-fns-selection-method', selections_wrapper).each( function(index2, sel) 
				{
					if ( $(sel).val() == 'always' && $('select.wc-fns-selection-method', selections_wrapper).length > 1 ) {
						var rule_wrapper = $(selections_wrapper).closest('tr');
						var rule_number  = $('#shipping-rules-table-fns tr').index( rule_wrapper ); // starts with 0, but first one are the header

						// Extra fees start again in #1
						var first_rule_extra = $('#shipping-rules-table-fns tr').index( $('#shipping-rules-table-fns tr.fns-ruletype-extra').first() );
						if ( first_rule_extra != -1 && first_rule_extra <= rule_number) rule_number = ( rule_number - first_rule_extra + 1 ) + ' / EXTRA';

						ci_always_superfluous.push( '#' + rule_number ); // selection method index +1 matches rule number
						return false; // break 1
					}
				});
			});
			if ( ci_always_superfluous.length > 0 ) {
				var message = wcfns_data['i18n_fns_always_error'];
				message = message.replace( '%rules%', ci_always_superfluous.join(', ') );
				if ( !confirm(message) ) return false;
			}
			// END - Check an always selector method with some other selector (superfluous)

			// Check math expression without rule_cost var plus previous costs (previous will be ignored)
			var ci_math_ignore = [];
			$('.fns-ruletype-normal').each( function(index, rule_wrapper) 
			{
				// Cost fields
				prev_cost = false;
				currency_restriction = $('#woocommerce_fish_n_ships_multiple_currency').is(":checked") ? '' : ' .currency-main';
				if ( $('select.wc-fns-cost-method', rule_wrapper).val() == 'composite' )
				{
					$('.cost_composite' + currency_restriction + ' input', rule_wrapper).each( function(i, input) {
						if ( $(input).val() != '0' && $(input).val() != '' ) prev_cost = true;
					});
				} else {
					$('.cost_simple' + currency_restriction + ' input', rule_wrapper).each( function(i, input) {
						if ( $(input).val() != '0' && $(input).val() != '' ) prev_cost = true;
					});
				}
				$('.special-actions-column .action_wrapper', rule_wrapper).each( function(i, action_wrapper)
				{
					action = $('.wc-fns-actions', action_wrapper).val();
					if ( action == 'min_max' || action == 'boxes' || ( action == 'math' && !prev_cost ) ) {
						prev_cost = true;
					} else if ( action == 'math' && prev_cost ) {
						formula = $('.action-math textarea', action_wrapper).val();
						if ( typeof(formula) == 'string' && formula.indexOf('rule_cost') == -1 ) {
							var rule_number  = $('#shipping-rules-table-fns tr').index( rule_wrapper ); // starts with 0, but first one are the header
							ci_math_ignore.push( '#' + rule_number ); // selection method index +1 matches rule number
							return false; // break 1
						}
					}
				});
			})
			if ( ci_math_ignore.length > 0 ) {
				var message = wcfns_data['i18n_fns_math_ignore'];
				message = message.replace( '%rules%', ci_math_ignore.join(', ') );
				if ( !confirm(message) ) return false;
			}
			// END - Check math expression without rule_cost var plus previous costs (previous will be ignored)

			sending = true;
		});
	}
	
	/*******************************************************
	    1.1. Export / Import settings
	 *******************************************************/

	// Move export buttons
	if ($('p.submit button.woocommerce-save-button').length==1) {
		$('.wc-fns-export-buttons a').insertAfter('p.submit button.woocommerce-save-button');
		$('.wc-fns-export-buttons').remove();
	}
		
	/* Export */
	$('a.wc-fns-export').click(function ()
	{	
		var data = {'version' : wcfns_data.version, 'pro' : wcfns_data.im_pro };
		var form = $(this).closest('form');
				
		data = json_stringify_fields( data, form, false );

		$('body').append('<div id="fns_dialog"><p class="fns-tabbed">'+wcfns_data.i18n_export_ins+'</p><div class="export_wrapper">...</div></div>');
		
		//Open the copy
		buttons_lang = [];
		buttons_lang.push(String(wcfns_data.i18n_close_bt), close_popup_dialog);
		
		$('#fns_dialog').dialog({
			title: wcfns_data.i18n_export_tit,
			dialogClass: 'wp-dialog',
			autoOpen: false,
			draggable: true,
			width: 'auto',
			modal: true,
			resizable: true,
			closeOnEscape: true,
			position: {
				my: "center",
				at: "center",
				of: window
			},
			open: function () {
				// close dialog by clicking the overlay behind it
				$('.ui-widget-overlay').bind('click', function() {
					close_popup_dialog();
				})
			},
			create: function () {
				// style fix for WordPress admin
				$('.ui-dialog-titlebar-close').addClass('ui-button');
			},
			buttons: [
				{
					text:   wcfns_data.i18n_close_bt,
					click:  close_popup_dialog
				}
			],
			close: function() {
				close_popup_dialog()
			}
		});

		$('body').addClass('fns-popup');
		$('.ui-dialog .ui-dialog-buttonset button').not('.ui-dialog-fns-samples button').attr('class', 'button button-primary button-large');
		$('#fns_dialog').dialog('open');
		
		$('.export_wrapper').html(data).click( function () {
			$(this).fns_selectText();
		});

		return false;
	});


	// This function has double use: export & import/save compressed
	function json_stringify_fields( data, target, delete_fields )
	{
		var ignore = ['select', 'undefined', '_wp_http_referer', '_wpnonce', 'fns-serial', 'fns-register', 'log[]', 'fnslogsperpag'];

		$('input, select, textarea', target).each( function ( index, el)
		{	
			name = $(el).attr('name');
			if ( typeof (name) != 'undefined' && $.inArray( name, ignore ) == -1 )
			{
				if (name.substr(0, 25) == 'woocommerce_fish_n_ships_') name = name.substr(25);
				val = $(el).val();
				
				//console.log( name + ': ' + val + '(' + typeof (val) + ')' );
				
				// Lovely JS... value attr should get only on checked case!
				var skip_chkbox = $(el).is(':checkbox') && !$(el).is(":checked");
				
				// ...and radio buttons? ignore not checked
				var skip_radio  = $(el).is(':radio') && !$(el).is(":checked");
				
				if( ! skip_chkbox && ! skip_radio )
				{
					// select multiple?
					if ( typeof (val) == 'object' ) {
						i=0;
						for (key in val) {
							unique_name = name.replace(/\[[0-9]*\]$/, '['+i+']' );
							data[unique_name] = val[key];
							i++;
						}
					} else {

						// Support for WC decimal separator: comma, point or whatever will be replaced by point
						if ( $(el).hasClass('wc_fns_input_positive_decimal') || $(el).hasClass('wc_fns_input_decimal') )
						{
							val = val.split( woocommerce_admin.decimal_point ).join( '.' );
						}
						data[name] = val;
					}
				}
				
				if( delete_fields ) $(el).remove();
			}
		});
		data = JSON.stringify( data );
		return data;
	}
	
	// Select text
	jQuery.fn.fns_selectText = function(){
		this.find('input').each(function() {
			if($(this).prev().length == 0 || !$(this).prev().hasClass('p_copy')) { 
				$('<p class="p_copy" style="position: absolute; z-index: -1;"></p>').insertBefore($(this));
			}
			$(this).prev().html($(this).val());
		});
		var doc = document;
		var element = this[0];
		// console.log(this, element);
		if (doc.body.createTextRange) {
			var range = document.body.createTextRange();
			range.moveToElementText(element);
			range.select();
		} else if (window.getSelection) {
			var selection = window.getSelection();        
			var range = document.createRange();
			range.selectNodeContents(element);
			selection.removeAllRanges();
			selection.addRange(range);
		}
	};

	/* Import */
	$('a.wc-fns-import').click(function () {
		
		$('body').append('<div id="fns_dialog"><p class="fns-tabbed">'+wcfns_data.i18n_import_ins+'</p><textarea id="fns_import_field"></textarea></div>');
		
		//Open the copy
		$('#fns_dialog').dialog({
			title: wcfns_data.i18n_import_tit,
			dialogClass: 'wp-dialog',
			autoOpen: false,
			draggable: true,
			width: 'auto',
			modal: true,
			resizable: true,
			closeOnEscape: true,
			position: {
			  my: "center",
			  at: "center",
			  of: window
			},
			open: function () {
			  // close dialog by clicking the overlay behind it
			  $('.ui-widget-overlay').bind('click', function(){
				//$(the_dialog).dialog('close');
				close_popup_dialog();
			  })
			},
			create: function () {
			  // style fix for WordPress admin
			  $('.ui-dialog-titlebar-close').addClass('ui-button');
			},
			buttons: [
				{
					text:   wcfns_data.i18n_cancel_bt,
					click:  close_popup_dialog
				},
				{
					text:   wcfns_data.i18n_import_bt,
					click:  import_config
				}
			],
			close: function() {
				close_popup_dialog()
			}
		});

		$('body').addClass('fns-popup');
		
		cont = $('.ui-dialog').not('.ui-dialog-fns-samples');
		$('.ui-dialog-buttonset button', cont).attr('class', 'button button-primary button-large');
		$('#fns_dialog').dialog('open');
		$('<p class="fns-tabbed"><em>'+wcfns_data.i18n_import_att+'</em></p>').insertAfter( $('.ui-dialog-buttonset', cont) );

		return false;
	});
	
	function import_config () {

		var form = $('#mainform');
		var code = $('#fns_import_field').val();
		var checks = ['global_group_by', 'multiple_currency', 'free_shipping', 'disallow_other'];
		
		// Carriages break JSON
		code = code.split( String.fromCharCode(10) ).join( '' );
		code = code.split( String.fromCharCode(13) ).join( '' );
		
		// Try to parse the import code
		try {
			code = JSON.parse(code); // this is how you parse a string into JSON 
		} catch (ex) {
			alert(wcfns_data.i18n_import_err + ' \r' + ex);
			close_popup_dialog();
			return;
		}
		
		// We will recreate the form, to send it
		$('#fns_recreate_form_wrap').remove();
		$('body').append('<div id="fns_recreate_form_wrap" style="display:none">'
					+ '<form method="post" id="fns_recreate_form" action="" enctype="multipart/form-data">'
					+ '<div class="outisde_table"></div><div class="inside_table"></div></form></div>');
		
		// First, let's put the imported fields
		for (key in code) {
			
			// Empty checkboxes not be recreated
			if ( $.inArray( key, checks ) == -1 || code[key] != '' ) {

				if (key.substr(0, 14) != 'shipping_rules') 
				{
					$('#fns_recreate_form .outisde_table').append('<input type="hidden" name="woocommerce_fish_n_ships_' + key + '" value="' + code[key] + '" />');
				}
				else
				{
					$('#fns_recreate_form .inside_table').append('<input type="hidden" name="' + key + '" value="' + code[key] + '" />');
				}
			}
		}

		// Now, we will copy the non-imported fields
		var ignore = ['select', 'undefined', 'fns-serial', 'fns-register'];
	
		// Prevent an embeded form inside the main form (WC Shipping & Tax plugin) sice 1.6.2
		$('input, select, button', form ).not('#mainform form input, #mainform form select, #mainform form button').each( function ( index, el)
		{	
			name = $(el).attr('name');

			if ( typeof (name) != 'undefined' && $.inArray( name, ignore ) == -1 && name.substr(0, 14) != 'shipping_rules') {
				
				val = $(el).val();
				
				// Lovely JS... value attr should get only on checked case!
				// if ( $(el).is(':checkbox') && !$(el).is(":checked") ) val = '';
				if ( $(el).is(':checkbox') ) return;

				// Checkboxes not be copied in any case
				shorted_name = name;
				if ( name.substr(0, 25) == 'woocommerce_fish_n_ships_' ) shorted_name = name.substr(25);
				//if ( $.inArray( shorted_name, checks ) == -1 ) {
					
					if ( $( '[name="'+name+'"]', '#fns_recreate_form' ).length == 0 ) {
						$('#fns_recreate_form .outisde_table').append('<input type="hidden" name="' + name + '" value="' + val + '" />');
					}
				//}
			}
		});

		// Compress data to avoid max_input_vars error if required
		if( wcfns_data['max_input_vars'] <= $( 'input, select, textarea, button', $('#fns_recreate_form') ).length )
		{
			console.log('Compress data to avoid max_input_vars at import, from: ' + $( 'input, select, textarea, button', $('#fns_recreate_form') ).length );
			
			target = $('#fns_recreate_form .inside_table');

			// Get the values of rule fields json-fied & delete they:
			compressed_data = json_stringify_fields( {}, target, true );
			
			$(target).append("<input type='hidden' id='fns_compressedData' name='shipping_rules[compressed]' />");
			$("#fns_compressedData").val(compressed_data);
			
			console.log('to: ' + $( 'input, select, textarea, button', $('#fns_recreate_form') ).length );
		}
		
		$('#fns_recreate_form').submit();
	}

	/*******************************************************
	    2. Global shipping rules table
	 *******************************************************/

    // Remove unused legacies
	const selectedLegacy = [];
	$('#shipping-rules-table-fns select').each(function ()
	{
		// Collect all selected legacy values
		$(this)
			.find('option:selected[data-legacy]')
			.each(function () {
				const legacyValue = $(this).data('legacy');
				if (!selectedLegacy.includes(legacyValue)) {
					selectedLegacy.push(legacyValue);
				}
			});
    });
	function remove_legacies()
	{
		$('#shipping-rules-table-fns option[data-legacy]').each(function ()
		{
			// Remove unused
			const $option = $(this);
			const legacyValue = $option.data('legacy');

			if (!selectedLegacy.includes(legacyValue)) {
				$option.remove();
			}
		});
	}
	remove_legacies();

	// Save radios
	$("#shipping-rules-table-fns input:radio").each(function (index, element) {
		$(element).attr('data-save', $(element).is(':checked') ? '1' : '0');
	});

	/*	Make the rules sortable. 
		Get it from:
		woocommerce/assets/js/admin/term-ordering.js
	*/

	function fix_cell_colors($where) {
		// Fix cell colors
		$( $where ).each( function(index, element) {
			if ( index%2 === 0 ) {
				jQuery( element ).addClass( 'alternate' );
			} else {
				jQuery( element ).removeClass( 'alternate' );
			}
		});
	}

	$("#shipping-rules-table-fns .column-handle").css('display', 'table-cell');

	$("#shipping-rules-table-fns > tbody").sortable({

		//items: item_selector,
		cursor: 'move',
		handle: '.column-handle',
		axis: 'y',
		forcePlaceholderSize: true,
		helper: 'clone',
		opacity: 0.65,
		placeholder: 'fns-rule-placeholder',
		scrollSensitivity: 40,

		// This event is triggered when the user stopped sorting and the DOM position has changed.
		update: function( event, ui ) {

			fix_cell_colors('#shipping-rules-table-fns > tbody tr');

			refresh_rules();
		}
	});
	
	/* Add new rule of type normal */
	$('#shipping-rules-table-fns a.add-rule').click( function () {
		add_new_rule('normal');
		return false;
	});

	function add_new_rule( $type ) {
		
		wrapper = $( '#shipping-rules-table-fns .fns-ruletype-container-' + $type );
		
		// add new row
		new_row = $(wcfns_data.empty_row_html).appendTo(wrapper);
		$('.selectors', new_row).append(wcfns_data.empty_selector_block_html);
		
		// Set the rule type
		$('tr:last', wrapper).addClass( 'fns-ruletype-' + $type ).removeClass('fns-ruletype-unknown');
		$('tr:last input.rule_type_selector', wrapper).val( $type );
		
		//Mark background in yellow and fadeout
		$('tr:last', wrapper).addClass('animate-bg');
		setTimeout(function() {
			$('#shipping-rules-table-fns tbody tr').removeClass('animate-bg');
		}, 50);

		//add a new selector on it
		$('.add-selector a.add_selector_bt', wrapper).last().trigger('click');

		/* renum and make sortable the new items */
		refresh_rules();
		$("#shipping-rules-table-fns .column-handle").css('display', 'table-cell');
		$("#shipping-rules-table-fns > tbody").sortable( "refresh" );
	}

	/* Add new rule of type extra*/
	$('#shipping-rules-table-fns a.add-rule-extra').click( function () {
		
		if ( wcfns_data.im_pro == '1' ) {
			
			add_new_rule('extra');
			
			// Select "always" and add a special action "ship_rate_pct" by default here
			new_rule = $( '#shipping-rules-table-fns .fns-ruletype-container-extra tr:last' );
			$('select.wc-fns-selection-method', new_rule).val('always');
			$('.add-action .button', new_rule).trigger('click');
			setTimeout( function () {
				$('select.wc-fns-actions', new_rule).val('ship_rate_pct').trigger('change');
			}, 100);
		
		} else {
			show_help( 'pro', false, wcfns_data['admin_lang'] );
		}
		return false;
	});
		
	/* Duplicate selected rules */
	$('#shipping-rules-table-fns a.duplicate-rules').click(function () {

		// Save select values (jQuery bug at clone)
		$("#shipping-rules-table-fns select").each(function(index, element) {
			$(element).attr('data-save', $(element).val());
		});

		// We will duplicate each rule inside his own container type
		$("#shipping-rules-table-fns tbody").each(function(i, wrapper) {

			cloned = $(".check-column input:checked", wrapper).closest('tr').clone().addClass('animate-bg');
			
			// Remove select 2 stuff (if there are any)
			$(cloned).find('.select2-container').remove();
			$(cloned).find('select.chosen_select, select.wc-product-search').removeClass('select2-hidden-accessible enhanced').removeAttr('aria-hidden');

			// Remove ui-slider stuff (if there are any)
			$(cloned).find('.slider.ui-slider').attr('class', 'slider').html('');

			// Remove hasDatepicker class
			$(cloned).find('.hasDatepicker').removeClass('hasDatepicker');

			// Remove dropdown
			if ($.fn.dropdownSubmenu) {
				$(cloned).find('.dropdown-submenu-tuned').dropdownSubmenu('destroy');
			}

			$(cloned).appendTo(wrapper);
		});	
		
		// Fix select values (jQuery bug at clone), with select multiple support
		$("#shipping-rules-table-fns select").each(function(index, element) {

			values = $(element).attr('data-save');
			$.each(values.split(","), function(i,e){
				$( "option[value='" + e + "']", element ).prop("selected", true);
			});
		});

		/* refresh select2 fields */
		if ($('.selection_details select.chosen_select, .selection_details select.wc-product-search').length != 0) {
			
			// We need to refresh the select2 selects:
			$( document.body ).trigger('wc-enhanced-select-init');
			init_all_product_select2();
		}
		
		$("#shipping-rules-table-fns .check-column input").prop( "checked", false );
		
		// fadeOut yellow effect by CSS
		setTimeout(function() {
			$('#shipping-rules-table-fns tbody tr').removeClass('animate-bg');
		}, 50);

		/* renum and make sortable the new items */
		unsaved = true;
		refresh_rules();
		$("#shipping-rules-table-fns .column-handle").css('display', 'table-cell');
		$("#shipping-rules-table-fns > tbody").sortable( "refresh" );
		return false;
	});

	/* Delete selected rules */
	$('#shipping-rules-table-fns a.delete-rules').click(function () {

		$("#shipping-rules-table-fns > tbody .check-column input:checked").closest('tr')
			
			// Mark red, fadeout and then remove and refresh
			.css('background', '#c91010')
			.fadeOut(function () {
			
				$("#shipping-rules-table-fns > tbody .check-column input:checked").closest('tr')
					.remove();
	
				// All rules are deleted? let's put an empty one
				if ($("#shipping-rules-table-fns > tbody.fns-ruletype-container-normal tr").length == 0) {
					$('#shipping-rules-table-fns .add-rule').last().trigger('click');
				}

				$("#shipping-rules-table-fns .check-column input").prop( "checked", false );

				/* renum and make sortable the new items */
				refresh_rules();
				$("#shipping-rules-table-fns .column-handle").css('display', 'table-cell');
				$("#shipping-rules-table-fns > tbody").sortable( "refresh" );
			});
		
		unsaved = true;

		return false;
	});
	
	// Disable or enable the Duplicate / Delete rules buttons
	function update_rule_buttons () {
		if ( $( '#shipping-rules-table-fns .check-column input:checked' ).length == 0 ) {
			$( '#shipping-rules-table-fns .duplicate-rules, #shipping-rules-table-fns .delete-rules' ).addClass('disabled');
		} else {
			$( '#shipping-rules-table-fns .duplicate-rules, #shipping-rules-table-fns .delete-rules' ).removeClass('disabled');
		}
	}
	$( '#shipping-rules-table-fns').on('change', '.check-column input', function () {
		update_rule_buttons ();
	});
	
	function refresh_rules() {
		
		check_volumetric();

		// renum the rules. Only cosmetic. Each wrapper type starts again counter
		$("#shipping-rules-table-fns > tbody").each(function(i, wrapper) {
			$('> tr', wrapper).each(function(index, element) {

				// I miss BASIC coding... XDD
				$('td.order-number', element).html('#' + (index+1) );

				$(element).removeClass('odd');
				if ( index%2 == 0 ) $(element).addClass('odd');
			});
		});

		$("#shipping-rules-table-fns > tbody > tr").each(function(rule_number, element) {
			
			// and/or/and-or-and logical operator
			/*
			if ( $('selection_wrapper', element) < 2 ) {
				$('.logical_operator_wrapper').hide();
			} else {
				$('.logical_operator_wrapper').show();
			}
			*/
			
			logical_operator = $('.logical_operator_radio:checked', element).val() || 'and';
			
			// refresh helpers (and-or-and falls well to AND here)
			$('.block_selectors', element).each( function(blockidx, block_container) {
				var and_or_label = logical_operator == 'or' ? wcfns_data.i18n_or :  wcfns_data.i18n_and;
				$(".helper", block_container).each(function(idx, helper) {
					$(helper).html(idx == 0 ? wcfns_data.i18n_where : and_or_label );
				});
			});
			
			// when there are only one selector on a rule, they can't be erased (only in the selection column!!)
			$(".selection-rules-column .delete", element).css('display', $(".selection-rules-column .delete", element).length == 1 ? 'none' : 'block');

			// the same criterion will show/hide the and/or logical operator)
			// for and/or, but always visible for and-or-and
			show = logical_operator == 'and-or-and' || $(".selection-rules-column .delete", element).length > 1;
			$(".selection-rules-column .field-logical_operator", element).css('display', show ? 'inline-block' : 'none' );
			
			// rename the fields (rule number, first occurrence)
			$('input, select, textarea', element).each(function(idx, field) {
				var fieldname = $(field).attr('name');
				// select2 create fields without name!
				if (typeof(fieldname) != 'undefined') {
					$(field).attr('name', fieldname.replace(/\[[0-9]+\]/, '['+rule_number+']'));
				}
			});
			

			// second match for selections
			$(".fns-selector-block", element).each(function(block_number, block_wrapper) // on and/or rules, there is only one selector block
			{
				$(".selection_wrapper", block_wrapper).each(function(sel_number, selector_wrapper)
				{
					// rename the fields (selection number, the second occurrence)
					$('input, select, textarea', selector_wrapper).each(function(idx_det, field_det) {
						var fieldname = $(field_det).attr('name');
						
						// select2 create fields without name!
						if (typeof(fieldname) != 'undefined') {

							// Remove the block selector index (if it is there):
							fieldname = fieldname.replace(/\[sel\]\[\d+\](?=\[)/, '[sel]');

							t=0;
							fieldname = fieldname.replace(/\[[0-9]*\]/g, function (match) {
								t++;
								if (t==2) return '['+sel_number+']'
								return match;
							});
							
							// Add the block selector index (if needed)
							if( logical_operator == 'and-or-and' )
								fieldname = fieldname.replace('[sel]', '[sel]['+block_number+']');

							$(field_det).attr( 'name', fieldname );
						}
					});
				});
			});
			
			// second match for actions
			//$(".action_details", element).each(function(index_det, element_det) {
			$(".action_wrapper", element).each(function(index_det, element_det) {

				// rename the fields (selection number, the second occurrence)
				$('input, select, textarea', element_det).each(function(idx_det, field_det) {
					var fieldname = $(field_det).attr('name');

					// select2 create fields without name!
					if (typeof(fieldname) != 'undefined') {
						
						t=0;
						$(field_det).attr('name', fieldname.replace(/\[[0-9]*\]/g, function (match) {
							t++;
							if (t==2) return '['+index_det+']'
							//if (t!=1) console.log('Fish n Ships: error on replacement key number (action)');
							return match;
						}));
					}
				});
			});
		});

		// Show/hide the bottom header if there is much info
		if ($("#shipping-rules-table-fns").outerHeight() > 350) {
			$("#shipping-rules-table-fns .fns-footer-header").show();
		} else {
			$("#shipping-rules-table-fns .fns-footer-header").hide();
		}

		// Restore radios
		$("#shipping-rules-table-fns input:radio").each(function (index, element) {
			if ( $(element).attr('data-save') == '1' ) $(element).prop( "checked", true );
		});
		
		update_rule_buttons ();

		// equalize helpers
		max_width = 0;
		$("#shipping-rules-table-fns .helper").each(function(idx, helper) {
			w = $(helper).width();
			if (max_width < w) max_width = w;
		});
		$("#shipping-rules-table-fns .selection_wrapper").css('padding-left', max_width + 10);
		
		start_sliders();

		apply_datepickers();
		
		// Remove unused legacies
		remove_legacies();
		
		// Hide the select ancestors that has all childs hidden ( normal/extra rules)
		$( "select.wc-fns-selection-method optgroup, select.wc-fns-actions optgroup" ).each( function( idx, optgroup )
		{
			$( optgroup ).toggleClass( 'no-items-normal', $( 'option.normal', optgroup ).length == 0 );
			$( optgroup ).toggleClass( 'no-items-extra', $( 'option.extra', optgroup ).length == 0 );
		});

		// Apply dropdowns submenu where there aren't
		if ($.fn.dropdownSubmenu) {
			$( ".selection_wrapper > select.wc-fns-selection-method, .shipping-costs-column select.wc-fns-cost-method, .action_wrapper select.wc-fns-actions" ).dropdownSubmenu({
				watchChangeVal:  true,
			});
		}
		
		// restart tips
		$( document.body ).trigger( 'init_tooltips' );

		updateAjaxifiedInfo();
	}
	
	// Tune [PRO] text in selectors
	$(document).on('dropdown-submenu-newfield', function( e, newfield) {
		$('.only_pro', newfield).each( function( idx, el ) {
			text = $(el).text();
			html = text.toString().replace('[PRO]', '<span class="fns-pro-icon">PRO</span>');
			$(el).html(html);
		});
	});
	$(document).on('dropdown-submenu-tuned', function( e, submenu) {
		wrapper = $(submenu).closest('.dropdown-submenu-wrapper');
		watch_content = $('.dropdown-field-watch .content', wrapper);
		text = $(watch_content).text();
		html = text.toString().replace('[PRO]', '<span class="fns-pro-icon">PRO</span>');
		$(watch_content).html(html);
	});


	/* multicurrency swithcing fields: for main table & popups */
	$(document).on('click', "#fns_dialog .nav-tab, #wrapper-shipping-rules-table-fns .nav-tab", function() {
		
		var nav   = $(this).closest('nav');
		var cont  = $(nav).next();
		
		//if ($(this)).hasClass('nav-tab-active') return false;
		
		$(".nav-tab", nav).removeClass('nav-tab-active');
		$(this).addClass('nav-tab-active');

		$(".currency-fns-field", cont).not( jQuery('table .currency-fns-field', cont) ).removeClass('currency-active');

		currency = $(this).attr('data-fns-currency');
		main = $( ".nav-tab", nav ).index(this) == 0;
		
		if (main) {
			$(cont).removeClass('locked');
		} else {
			$(cont).addClass('locked');
			$(".currency-fns-field.currency-" + currency, cont).not( jQuery('table .currency-fns-field', cont) ).addClass('currency-active');
		}
		return false;
	});
	
	var sorry_mc = $('span.woocommerce-help-tip', $('#woocommerce_fish_n_ships_multiple_currency').closest('tr')).attr('data-tip');

	function check_fields_multicurrency() {
		
		chk = $('#woocommerce_fish_n_ships_multiple_currency');
		mc = $(chk).is(':checked');
		
		if (mc && $(chk).hasClass('fns-mc-unavailable') ) {
			var decoded = $("<div/>").html(sorry_mc).text();
			alert(decoded);
			$(chk).prop( "checked", false );
			return false;
		}
		
		if (mc) {
			$('body').addClass('mc-tabs-fns');
			$('.fns-currency-secondary').show();
		} else {
			$('.nav-tab-wrapper').each( function(idx, el) {
				$('.nav-tab', el).eq(0).trigger('click');
			});
			$('body').removeClass('mc-tabs-fns');
			$('.fns-currency-secondary').hide();
		}
	}
	
	$('#woocommerce_fish_n_ships_multiple_currency').change(check_fields_multicurrency);
	
	$('.min_shipping_price_field.currency-main, .max_shipping_price_field.currency-main')
		.closest('tr').addClass('fns-currency-main');
	
	$('.min_shipping_price_field.currency-secondary, .max_shipping_price_field.currency-secondary')
		.closest('tr').addClass('fns-currency-secondary');
		
	check_fields_multicurrency();
	
	apply_datepickers();
	
	
	/*******************************************************
	    2.1. Selection rules column
	 *******************************************************/
	
	/* Add new selection condition */
	$('#shipping-rules-table-fns > tbody').on('click', '.add-selector .button', function () {
		
		var cont = $(this).closest('td');

		// Add OR block
		if( $(this).hasClass('add_selector_or_block_bt') )
		{
			$(wcfns_data.empty_selector_block_html).appendTo( $('.selectors', cont) );
			target = $('.block_selectors:last', cont);
		}
		// Add AND inside the OR block
		else if( $(this).hasClass('add_selector_and_block_bt') )
		{
			target = $('.block_selectors:first', $(this).closest('.fns-selector-block') );
		}
		// ADD selector inside unique block_selectors (AND/OR mode)
		else
		{
			target = $('.block_selectors:last', cont);
		}
		
		hi = $(wcfns_data.new_selection_method_html).appendTo( target );
		
		//Mark background in yellow and fadeout
		$(hi).addClass('animate-bg');
		setTimeout(function() {
			$(hi).removeClass('animate-bg');
		}, 50);

		unsaved = true;
		refresh_rules();

		return false;
	});
	
	/* Delete selection condition */
	$('#shipping-rules-table-fns > tbody').on('click', '.selection_wrapper .delete', function () {
		
		cont = $(this).closest('.fns-selector-block');
		if( $('.selection_wrapper', cont).length == 1 )
		{
			target = cont; // delete the entire block
		}
		else
		{
			target = $(this).closest('.selection_wrapper'); // delete only this selection wrapper
		}

		// Mark red, fadeout and then remove and refresh
		$(target)
		
			.css('background', '#c91010')
			.fadeOut(function ()
			{
				$(this).remove();
				refresh_rules();
			});

		unsaved = true;

		return false;
	});
	
	// Saving the previous selection value
	$('#shipping-rules-table-fns > tbody').on('focus', 'select.wc-fns-selection-method', function () {
		$(this).attr('data-last-value', $(this).val() );
	});
	
	/* Change the auxiliary fields at change one selection condition */
	$('#shipping-rules-table-fns > tbody').on('change', 'select.wc-fns-selection-method', function () {

		val = $(this).val();

		if (val == 'pro') {
			// Set the last valid option on select
			$(this).val( $(this).attr('data-last-value') );
			// Show pro help popup
			show_help( 'pro', false, wcfns_data['admin_lang'] );

		} else {

			cont = $(this).closest('.selection_wrapper');

			$('.selection_details', cont).html('');
			if (val != '') $('.selection_details', cont).html(wcfns_data['selection_' + val + '_html']);
			
			ajax_div = $('.selection_details .wc-fns-ajax-fields', cont);
			if (ajax_div.length != 0) {
	
				// Ajaxified option
				
				$.ajax({
					url: wcfns_data['ajax_url_main_lang'],
					data: { action: 'wc_fns_fields', type: ajax_div.attr('data-type'), method_id: ajax_div.attr('data-method-id') },
					error: function (xhr, status, error) {
						var errorMessage = xhr.status + ': ' + xhr.statusText
						alert('Error loading auxiliary fields - ' + errorMessage);
					},
					success: function (data) {
						
						// Put the HTML and remember it for next usage
						wcfns_data['selection_' + val + '_html'] = data;
						$('.selection_details', cont).html(data);

						check_global_group_by();
						if ($('.selection_details select.chosen_select', cont).length != 0) {
							/* enhance select if there is one on it */
							$( document.body ).trigger('wc-enhanced-select-init');
						}
						refresh_rules();
					},
					dataType: 'html'
				});

			} else {
				
				// Non-ajaxified or previous ajax-loaded

				check_global_group_by();
				
				if ($('.selection_details select.chosen_select, .selection_details select.wc-product-search', cont).length != 0) {
					/* enhance select if there is one on it */
					$( document.body ).trigger('wc-enhanced-select-init');
				}
				refresh_rules();
			}
	
			// Save last value
			$(this).attr('data-last-value', $(this).val() );
	
			unsaved = true;
		}
		//return false;
	});

	function init_all_product_select2()
	{
		$('select.wc-product-search').each(function() {
			const $select = $(this);
			init_product_select2( $select );
		});
	}
	
	function init_product_select2( $select )
	{
		/*if( $select.hasClass('enhanced') )
			return;*/

		var opcionsRecollides = [];

		// 1) Recollir id/text sense tocar el DOM
		$select.find('option').each(function(){
		  opcionsRecollides.push({
			id:   this.value,
			text: this.textContent
		  });
		});

		// 2) Netejar tot el select
		$select.empty();

		// 3) Reafegir cada opció com a seleccionada
		opcionsRecollides.forEach(function(item){
		  var newOpt = new Option(item.text, item.id, true, true);
		  $select.append(newOpt);
		});

		$select.trigger('change');
	}

	/* AJAX info if needed */
	let timeoutAjaxInfoId;

	function updateAjaxifiedInfo()
	{
		// We will collect all ajaxified info selectors, to update it in only one request
		const elements = [];
		$('.selection_wrapper').each(function(idx, sel_wrapper)
		{
			if( $('.wc-fns-ajax-info', sel_wrapper).length==0 )
				return; // No ajax info to update here
			
			const element = $(sel_wrapper).find('input, select, textarea').serializeArray();
			elements.push(element);
		});
				
	    const simplifiedInfo = elements.map(el => {
			var rule_number  = 0;
			var el_number    = 0;
			var method_id    = '';
			var type         = '';

			el = el.map(field => {

				// Here we seek for the selector method_id
				var regex = /shipping_rules\[(\d+)\]\[(\w+)\]\[(\d+)\]/;
				var matches = field.name.match(regex);

				if (matches) {

					const name    = matches[2];

					if( name == 'sel' )
					{
						type         = 'selector';
						rule_number  = matches[1];
						el_number    = matches[3];
						method_id    = field.value;
						return false;
					}
					
					return {
						name:   name,
						value:  field.value
					};
				}

				// Here we seek for the fields names + values
				var regex = /shipping_rules\[(\d+)\]\[(\w+)\]\[([\w-]+)\]\[([\w-]+)\]\[(\d+)\]$/;
				var matches = field.name.match(regex);
								
				if (matches) {

					const name    = matches[4];

					return {
						name:   name,
						value:  field.value
					};
				}

				return false;
			});

			return {
				rule_number :  rule_number,
				el_number   :  el_number,
				method_id   :  method_id,
				type        :  type,
				fields      :  el.filter(field => field !== false)
			};
		});
		
		// Send it via AJAX
		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: { 
				action: 'wc_fns_messages', 
				data: JSON.stringify( simplifiedInfo ) 
			},
			success: function(data) {

				var JSONreply = typeof data === "string" ? JSON.parse(data) : data;

				// Loop into replies
				JSONreply.forEach(function(item) {
					
					if( item.type == 'selector' )
					{
						const cont  = jQuery('.selection-rules-column').eq(item.rule_number);
						const el    = jQuery('.selection_wrapper', cont).eq(item.el_number);
						
						if( jQuery('.wc-fns-ajax-info', el).html() != item.message )
						{
							jQuery('.wc-fns-ajax-info', el).html('<span class="wc-fns-spinner"></span>');
							setTimeout( function() {
								jQuery('.wc-fns-ajax-info', el).html( item.message );
							}, 400);
						}
					}
				});
			},
			error: function(xhr, status, error) {
				console.log("Error in AJAX:", error);
			}
		});
	}

	// Watch the ajaxified info related fields only
	$('#shipping-rules-table-fns > tbody').on( 'input', '.wc-fns-ajax-info-field', function(event)
	{
		cont = $(event.target).closest('.selection_wrapper');
		
		if( $('.wc-fns-ajax-info', cont).length==0 )
			return; // No ajax info to update here
		
		// Cancel any previous timeout
		clearTimeout(timeoutAjaxInfoId);

		// Define a new timeout
		timeoutAjaxInfoId = setTimeout(() => {
			updateAjaxifiedInfo();
		}, 300);
	});


	// Show or hide the general volumetric weight factor field if some selector use it
	function check_volumetric() {
		var volumetric = false;
		var required   = false; // not required for math expressions
		$('#shipping-rules-table-fns select.wc-fns-selection-method').each(function(index, element) {
			if( $(element).val()=='volumetric' || $(element).val()=='volumetric-set' ) {
				volumetric = true;
				required   = true;
			}
		});
		$('#shipping-rules-table-fns select.wc-fns-actions').each(function(index, element) {
			if ($(element).val()=='math') volumetric = true;
		});
		if ( volumetric ) {
			$( '#woocommerce_fish_n_ships_volumetric_weight_factor' ).closest( 'tr' ).show();
		} else {
			$( '#woocommerce_fish_n_ships_volumetric_weight_factor' ).closest( 'tr' ).hide();
		}
		if ( required ) {
			$( '#woocommerce_fish_n_ships_volumetric_weight_factor' ).attr('required', 'required');
		} else {
			$( '#woocommerce_fish_n_ships_volumetric_weight_factor' ).removeAttr('required');
		}
	}
	check_volumetric();

	// Show or hide the global group method field
	var global_when_loaded_method = $( '#woocommerce_fish_n_ships_global_group_by' ).is( ':checked' );
	function check_global_group_by() {
		if( $( '#woocommerce_fish_n_ships_global_group_by' ).is( ':checked' ) ) {
			// Global group
			$( '#woocommerce_fish_n_ships_global_group_by_method' ).closest( 'tr' ).show();
			$( '#shipping-rules-table-fns .field-group_by' ).not('#shipping-rules-table-fns .field-cant-group_by').hide();
			if ($('#woocommerce_fish_n_ships_global_group_by_method').val() == 'none') {
				$('#shipping-rules-table-fns .field-cant-group_by').hide();
			} else {
				$('#shipping-rules-table-fns .field-cant-group_by').css('display', 'block');
			}
		} else {
			// Local selection group
			if ( $( '#woocommerce_fish_n_ships_global_group_by' ).hasClass('onlypro') ) {
				// Free version, keep checked and open the help about pro
				$( '#woocommerce_fish_n_ships_global_group_by' ).prop( 'checked', true );
				show_help( 'pro', false, wcfns_data['admin_lang'] );
			} else {
				$( '#woocommerce_fish_n_ships_global_group_by_method' ).closest( 'tr' ).hide();
				$( '#shipping-rules-table-fns .field-group_by' ).css('display','block');
				if( global_when_loaded_method )
				{
					// Set each of them to the previous global value (same operation)
					var prev_groupby = $('#woocommerce_fish_n_ships_global_group_by_method').val();
					$( '#shipping-rules-table-fns .field-group_by select').val( prev_groupby );
				}
			}
		}
	}
	$( '#woocommerce_fish_n_ships_global_group_by, #woocommerce_fish_n_ships_global_group_by_method' ).change( function(){
		check_global_group_by();
	});
	check_global_group_by();
	
	$( '#woocommerce_fish_n_ships_global_group_by').closest('tr').addClass('fns-no-bottom-padding');
	$( '#woocommerce_fish_n_ships_global_group_by_method').closest('tr').addClass('fns-no-top-padding');

	/* change the comparison options on MIN/MAX fields */	
	$('#shipping-rules-table-fns > tbody').on({
		click: function () {
			set_comparison_option(this, 'ge');
			return false;
		}
	}, 'span.comp_icon.icon_ge');

	$('#shipping-rules-table-fns > tbody').on({
		click: function () {
			set_comparison_option(this, 'greater');
			return false;
		}
	}, 'span.comp_icon.icon_greater');

	$('#shipping-rules-table-fns > tbody').on({
		click: function () {
			set_comparison_option(this, 'less');
			return false;
		}
	}, 'span.comp_icon.icon_less');

	$('#shipping-rules-table-fns > tbody').on({
		click: function () {
			set_comparison_option(this, 'le');
			return false;
		}
	}, 'span.comp_icon.icon_le');
	
	function set_comparison_option (obj, what) {
		var field_container = $(obj).closest('.field');
		$('.comp_icon', field_container).removeClass('on');
		$(obj).addClass('on');
		$('input.comparison_way', field_container).val(what);
		
		var input_field = $(field_container).attr('data-input-name');
		
		// Update attr
		attr = $('.wc_fns_input_tip', field_container).attr('data-wc-fns-tip');
		last_hypen = attr.lastIndexOf('_');
		if ( last_hypen != -1 ) {
			attr = attr.substr(0, last_hypen+1) + what;
			$('.wc_fns_input_tip', field_container).attr('data-wc-fns-tip', attr);
		}
		// $('.wc_fns_input_tip', field_container).attr('data-wc-fns-tip', 'i18n_' + input_field + '_val_info_' + what)
	}
	
	// logical operator switching control:
	// store new value, refactor the selector blocks if needed and call refresh_rules() 
	$('#shipping-rules-table-fns').on('click', '.logical_operator_radio', function()
	{
		new_value  = $(this).attr('value');
		old_value  = 'unknown';
		
		var wrap = $(this).closest('.logical_operator_wrapper');
		$('input:radio', wrap).each(function(index, element)
		{
			if( $(element).attr('data-save') == '1' ) 
				old_value = $(element).attr('value');
			
			$(element).attr('data-save', $(element).is(':checked') ? '1' : '0');
		});
		
		if( old_value == 'and-or-and' && ( new_value == 'and' || new_value == 'or' ) )
		{
			sel_wrapper = $(this).closest('td');
			if( $('.fns-selector-block', sel_wrapper ).length > 1 )
			{
				first   = $('.block_selectors:first', sel_wrapper ).first();
				others  = $('.block_selectors', sel_wrapper ).not(first);
				
				$(others).children().appendTo(first);
				$(others).remove();
				
				// Only the last pair of elements will remain:
				$('.fns-selector-block', sel_wrapper).not(':first').remove();
			}
		}
		
		if( old_value == 'or' && new_value == 'and-or-and' )
		{
			sel_wrapper = $('.selectors', $(this).closest('td') );
			if( $('.selection_wrapper', sel_wrapper ).length > 1 )
			{
				$('.selection_wrapper', sel_wrapper ).not(':first').each( function(idx, to_move)
				{
					// Add an empty selector block
					cont   = $(wcfns_data.empty_selector_block_html).appendTo( sel_wrapper );
					target = $('.block_selectors:last', cont);
					
					$(to_move).appendTo( target );
				});
			}
		}
		
		// Update class in TR wrapper
		$(this).closest('tr')
			.removeClass('fns-logic_and-or-and fns-logic_and fns-logic_or')
			.addClass('fns-logic_' + new_value);
		
		refresh_rules();
	});
	
	$('#shipping-rules-table-fns').on('mousedown', '.logical_operator_radio', function() {
		if ($(this).attr('readonly') == 'readonly') {
			show_help( 'pro', false, wcfns_data['admin_lang'] );
			return false;
		}
	});
	
	// Apply datepickers when needed: at first load and after any change
	function apply_datepickers() {
		
		if ($.datepicker) {
			
			var dateFormat = "yy-mm-dd";

			$('.selection-date-date-min').each( function (i, el) {
				
				// Skip fields with datepicker applied yet
				if ( $(el).hasClass('hasDatepicker') ) return;
				
				cont = $(el).closest('.selection_wrapper');
				
				var from = jQuery('.selection-date-date-min input[type=text]', cont).datepicker({
					minDate: 0,
					changeMonth: true,
					changeYear: true,
					//beforeShow: customRange,
					//dateFormat: "yy-mm-dd",
					dateFormat: dateFormat,
					regional: [''],
				})
				.on( "change", function() {
				  to.datepicker( "option", "minDate", getDate( this ) );
				});

				var to = jQuery('.selection-date-date-max input[type=text]', cont).datepicker({
					minDate: 0,
					changeMonth: true,
					changeYear: true,
					//beforeShow: customRange,
					//dateFormat: "yy-mm-dd",
					dateFormat: dateFormat,
					regional: [''],
				})
				.on( "change", function() {
					// console.log('change');
					from.datepicker( "option", "maxDate", getDate( this ) );
				});
				
				function getDate( element ) {
					//return element.value;
					var date;
					
					// remove time part
					try {
						date = $.datepicker.parseDate( dateFormat, element.value );
					} catch( error ) {
						date = null;
					}

					return date;
				}
			});

			$('.wc_fns_input_dayyear').each( function (i, el) {

				// Skip fields with datepicker applied yet
				if ( $(el).hasClass('hasDatepicker') ) return;

				$(el).datepicker({
					changeMonth: true,
					changeYear: false,
					dateFormat: dateFormat,
					regional: [''],
				})
				.on( "change", function() {
					
					var value = el.value;
					$(el).val( value.substr(5,5) );

					/*var date = $.datepicker.parseDate( dateFormat, el.value );
					
					// Calculate day of year
					var start = new Date(date.getFullYear(), 0, 0);
					var diff = (date - start) + ((start.getTimezoneOffset() - date.getTimezoneOffset()) * 60 * 1000);
					var oneDay = 1000 * 60 * 60 * 24;
					var day = Math.floor(diff / oneDay);
					*/
				});
			});

		}
	} // end apply_datepickers()
		
	init_all_product_select2();
	
	/*******************************************************
	    2.2. Shipping costs column
	 *******************************************************/
	
	// Show or hide the simple/composite/range cost fields
	function check_composite_cost() {
		$('#shipping-rules-table-fns select.wc-fns-cost-method').each(function(index, element) {

			// Extra rules haven't cost fields
			if( ! $(element).closest('tr').hasClass('fns-ruletype-normal') ) return;
			
			cont = $(element).closest('td');

			if ($(element).val() == 'composite') {

				$('.cost_simple', cont).hide();
				$('.cost_composite', cont).show();
				$('.fns-range-config-bt', cont).hide();

			} else if ($(element).val() == 'ranges') {

				$('.cost_simple', cont).hide();
				$('.cost_composite', cont).hide();
				$('.fns-range-config-bt', cont).show();

			} else {

				$('.cost_simple', cont).show();
				$('.cost_composite', cont).hide();
				$('.fns-range-config-bt', cont).hide();
			}
		});
	}
	
	// Move the right value to the right field on change from siple to composite price
	$('#shipping-rules-table-fns > tbody').on({
		focus: function () {
			$(this).attr('data-last-value', $(this).val() );
		},
		change: function () {

			check_composite_cost();

			// Move value on select change
			cont = $(this).closest('td');
			last_val = $(this).attr('data-last-value');

			if ($(this).val() == 'composite') {
				$('div.cost_composite input', cont).val(0);
				$('input.fns-cost-' + last_val, cont).val($('input.fns-cost', cont).val());
			} else if (last_val == 'composite') {
				$('input.fns-cost', cont).val($('input.fns-cost-' + $(this).val(), cont).val());
			}
			// Save last value
			$(this).attr('data-last-value', $(this).val() );
		}
	}, '.wc-fns-cost-method');
	
	check_composite_cost();

	$('#shipping-rules-table-fns > tbody').on('change', '.wc-fns-cost-method', function() {
		if( $(this).val() != 'ranges' ) return;
		cont = $(this).closest('td');
		$('.fns-range-config-bt', cont).trigger('click');
	});
	
	/*******************************************************
	    2.3. Special actions column
	 *******************************************************/

	/* Add new action */
	$('#shipping-rules-table-fns > tbody').on('click', '.add-action .button', function () {
		cont = $(this).closest('td');
		$('.actions', cont).append(wcfns_data.new_action_html);

		//Mark background in yellow and fadeout
		$('.action_wrapper:last', cont)
			.addClass('animate-bg');
		setTimeout(function() {
			$('.action_wrapper:last', cont).removeClass('animate-bg');
		}, 50);

		refresh_rules();
		unsaved = true;

		return false;
	});

	/* Delete action */
	$('#shipping-rules-table-fns > tbody').on('click', '.action_wrapper .delete', function () {

		//$(this).closest('.action_wrapper').remove();

		// Mark red, fadeout and then remove and refresh
		$(this).closest('.action_wrapper')
		
			.css('background', '#c91010')
			.fadeOut(function () {
				
				$(this).remove();
				refresh_rules();
			});

		unsaved = true;

		return false;
	});


	// Saving the previous action value
	$('#shipping-rules-table-fns > tbody').on('focus', '.wc-fns-actions', function () {
		$(this).attr('data-last-value', $(this).val() );
	});
	

	/* Change the auxiliary fields at change one action */
	$('#shipping-rules-table-fns > tbody').on('change', '.wc-fns-actions', function () {

		val = $(this).val();
		if (val == 'pro') {
			// Set the last valid option on select
			$(this).val( $(this).attr('data-last-value') );
			// Show pro help popup
			show_help( 'pro', false, wcfns_data['admin_lang'] );

		} else {

			cont = $(this).closest('.action_wrapper');
	
			$('.action_details', cont).html('');
			if (val != '') $('.action_details', cont).html(wcfns_data['action_' + val + '_html']);
	
			$(this).attr('data-last-value', $(this).val() );
			refresh_rules();
			unsaved = true;
		}
		return false;
	});
	
	/*******************************************************
	    2.4. Shipping boxes sliders
	 *******************************************************/

	function start_sliders() {
		
		$( '.fns_slider_wrap' ).each( function (index, el) {

			$( ".slider", el ).not('.ui-slider', el).slider({
				slide: refreshSlider,
				change: refreshSlider,
				min: 0,
				max: 100,
				step: 2,
				value: $('input', el).val()
			});
		});

		// Force update for consistency
		$( '.fns_slider_wrap' ).each( function (index, el) {
			$( ".slider", el ).slider('value', $('input', el).val() );
		});
	}
	
	var semaphore_slider = false;
	
    function refreshSlider( event, ui ) {
		
		if (semaphore_slider) return;
		semaphore_slider = true;
	
		var valor = ui.value;
		var el = $(ui.handle).closest('.fns_slider_wrap');
		var cont = $(el).closest('.fns_slider_pack');
		
		var others = $('.fns_slider_wrap', cont).not(el);
		
		var o0 = $('.slider', others[0]).slider("value");
		var o1 = $('.slider', others[1]).slider("value");

		var ratio = o0 / (o0 + o1);
		if( isNaN( ratio ) ) ratio = 0.5;

		var v0 = Math.round( (100 - valor) * ratio );
		var v1 =  100 - valor  - v0;

		$('.slider', others[0]).slider( "value", v0 );
		$('.slider', others[1]).slider( "value", v1 );

		$('input', el).val( valor );
		$('input', others[0]).val( v0 );
		$('input', others[1]).val( v1 );
		
		semaphore_slider = false;
    }

	// Suport for the special action add-boxes (since x.x.x)
	$( document ).on( 'change', '.fns-add-boxes-mode', function()
	{
		wrapper = $(this).closest('.fns_fields_popup');
		if( $(this).val() == 'auto' )
		{
			$( '.fns-add-boxes-manual', wrapper ).hide();
			$( '.fns-add-boxes-max', wrapper ).show();
			//$( '.fns-add-boxes-max-asterix', wrapper ).css('opacity', '1');
		}
		else
		{
			$( '.fns-add-boxes-manual', wrapper ).show();
			$( '.fns-add-boxes-max', wrapper ).hide();
			//$( '.fns-add-boxes-max-asterix', wrapper ).css('opacity', '0');
		}			
	});

	/*******************************************************
	    3. Logs
	 *******************************************************/
	
	$('#woocommerce_fish_n_ships_write_logs').change( function () {
		update_debug_advice();
	});
	
	function update_debug_advice() {
		if( $( '#woocommerce_fish_n_ships_write_logs' ).val() != 'off' ) {
			$('#wc_fns_debug_mode, #wc_fns_logs_list').show();
		} else {
			$('#wc_fns_debug_mode, #wc_fns_logs_list').hide();
		}
	}
	update_debug_advice();
	
	
	//Open/close logs
	jQuery(document).on('click', '#fnslogs .open_close', function() {
		cont = $(this).closest('tr');
		if (cont.hasClass('fns-open-log')) {
			// Close log
			log_cont = $(cont).next();
			if (log_cont.hasClass('log_content')) {
				$('div.fns-log-details', log_cont).slideUp(function () {
					setTimeout(function () {
						$(cont).removeClass('fns-open-log');
					}, 300);
					$(log_cont).hide();
					$('<tr class="fix_stripped"><td colspan="6"></td></tr>').insertAfter(log_cont);
				});
			}
		} else {
			if (cont.hasClass('loaded')) {
				// Reopen log
				log_cont = $(cont).next();
				$(log_cont).show();
				$('div.fns-log-details', log_cont).slideDown(function () {
					$(cont).addClass('fns-open-log');
					fix = $(log_cont).next();
					if (fix.hasClass('fix_stripped')) fix.remove();
				});
			} else {
				// prevent double click
				if (!cont.hasClass('loading')) {
					cont.addClass('loading'); 
					// Load log
					$('<tr class="loading_log"><td colspan="6"><div class="fns-log-details"><span class="wc-fns-spinner"></span></div></td></tr>').insertAfter(cont);
	
					$.ajax({
						url: ajaxurl,
						data: { action: 'wc_fns_logs', name: $(this).attr('data-fns-log') },
						error: function (xhr, status, error) {
							var errorMessage = xhr.status + ': ' + xhr.statusText
							alert('Error loading log - ' + errorMessage);
							cont.removeClass('loading');
							log_content_tr = $(cont).next();
							if (log_content_tr.hasClass('loading_log')) log_content_tr.remove();
						},
						success: function (data) {
							$(cont).addClass('fns-open-log loaded').removeClass('');
	
							log_content_tr = $(cont).next();
							$(log_content_tr).addClass('log_content').removeClass('loading_log');
	
							div_log = $('.fns-log-details', log_content_tr);
							div_log.css({height: 60, overflow: 'hidden'}).html('<div class="wrap">' + data + '</div>');
								
							setTimeout(function () {
								$(div_log).animate({height: $('.wrap', div_log).height()}, function () {
									$(div_log).css({overflow: '', height: ''});
									cont.removeClass('loading'); 
								});
							},10);

							// Make log info collapsable
							current_wrapper = null;

							$('p', div_log).each( function (index, el) {
								
								html = $(el).html();

								if ( html.substr(0, 18) == '&nbsp;&nbsp;&nbsp;' ) {
									if (current_wrapper != null) {
										$(current_wrapper).append(el);
									}
								
								} else if ( html.substr(0, 12) == '&nbsp;&nbsp;' ) {
									
									html_next = $(el).next().html();
									if ( html_next.substr(0, 18) == '&nbsp;&nbsp;&nbsp;' ) {
										
										if (current_wrapper == null) $('<p style="padding: 1.2em 0;">Rule calculations are folded. Click on each to see the details, or [<a href="#" class="fns-log-opener-all">open all</a>]:</p>').insertBefore(el);
										
										$( el ).html( '<a href="#" class="fns-log-opener"><span class="dashicons-before dashicons-arrow-right-alt2"></span>' + html.substr(12) + '</a>' );
									
										current_wrapper = $('<div class="wrapper"></div>').insertAfter(el);
									}
								}
							});
						},
						dataType: 'html'
					});
				}
			}
		}
		return false;
	});
	
	// Open / close all log rules branches

	$(document).on('click', '#fnslogs .fns-log-opener-all', function () {
		
		log_container = $(this).closest('.fns-log-details');

		// All are open now?
		all_opened = true;
		$( '.fns-log-opener', log_container ).each( function (index, el) {
			if ( !$(el).closest('p').hasClass('opened') ) {
				all_opened = false;
				// console.log('not opened');
			} else {
				// console.log('opened');
			}
		});
		
		if ( all_opened ) {
			// Close all
			$( '.fns-log-opener', log_container ).trigger('click');
			// console.log('close all');
		} else {
			// Open the closed only
			$( '.fns-log-opener', log_container ).each( function (index, el) {
				if ( !$(el).closest('p').hasClass('opened') ) $( el ).trigger('click');
			});
			// console.log('open closed all');
		}
		
		return false;
	});
	
	// Open close one rule
	$(document).on('click', '#fnslogs .fns-log-opener', function () {
		
		el = $(this).closest('p');
		
		if ( !$(el).next().hasClass('wrapper') ) return false;
		
		if ( $(el).hasClass('opened') ) {
			
			$(el).removeClass('opened');
			$(el).next().slideUp( function () {
				$(el).next().css( {height: '', display: ''} )
			});
		} else {

			$(el).addClass('opened');
			$(el).next().css( {height: 'auto', display: 'none'} ).slideDown();
		}
		return false;
	});
	

	// Paged logs with AJAX
	$(document).on('click', '#fnslogs .pagination-links a, #fns_logs_reload', function () {
		
		get_list_logs_ajaxified( $(this).attr('data-fns-logs-pag'), $(this).attr('data-instance_id'), $("select[name='fnslogsperpag']").val() );
		return false;
	});
	
	$(document).on('change', "select[name='fnslogsperpag']", function () {
		
		get_list_logs_ajaxified( 1, $(this).attr('data-instance_id'), $(this).val() );
		return false;
	});
	
	// Disable / Enable remove button
	$(document).on('change', "input[name='log[]'], input.cb-select-all", function () {
		refresh_remove_logs_button();
	});
	
	function refresh_remove_logs_button() {
		if( $("input[name='log[]']:checked").length == 0 )
		{
			$("button[name='fns-remove_logs']").attr("disabled", true);
		} 
		else 
		{
			$("button[name='fns-remove_logs']").removeAttr("disabled");
		}
	}
	refresh_remove_logs_button();

	// Delete selected logs
	$(document).on('click', "button[name='fns-remove_logs']", function () {
		
		var del_logs = [];
		$("input[name='log[]']:checked").each(function(){
			del_logs.push(this.value);
		});
		
		delete_logs_ajax( del_logs, $('#fns_logs_reload').attr('data-fns-logs-pag') );
		return false;
	});

	// Delete all logs
	$(document).on('click', "button[name='fns-remove_all_logs']", function () {
		
		delete_logs_ajax( 'all', '1' );
		return false;
	});
	
	// Talk with the server, refresh, etc.
	function delete_logs_ajax( del_logs, page ) {

		// prevent double click
		if ( $('#logs_wrapper').hasClass('loading') ) return;
		
		$('#logs_wrapper').addClass('loading').append('<div class="fns-loglist-loading"><span class="wc-fns-spinner"></span></div>');


		var data = {
			action:             'wc_fns_logs_pane', 
			'fns-remove_logs' : '1',
			log :               del_logs,

			instance_id:        $('#fns_logs_reload').attr('data-instance_id'),
			fnslogspag:         page,
			fnslogsperpag:      $("select[name='fnslogsperpag']").val(),

			// Prevent an embeded form inside the main form (WC Shipping & Tax plugin) sice 1.6.2
			_wpnonce :          $('input[name="_wpnonce"]').not('form form input').val(),
			_wp_http_referer :  $("input[name='_wp_http_referer']").not('form form input').val(),
		};

				
		$.ajax({
			url:    ajaxurl,
			data:   data,
			method: 'POST',
			error:  function (xhr, status, error) {
						var errorMessage = xhr.status + ': ' + xhr.statusText
						console.log('Error deleting logs - ' + errorMessage);
						$('#logs_wrapper').removeClass('loading').find('.fns-loglist-loading').remove();
			},
			success: function (data) {
				
				// console.log(data);
				
				if ( data == '0' ) {
					console.log('error deleting logs');
					$( '#logs_wrapper' ).find('.fns-loglist-loading').remove();
					return
				}
				$( '#logs_wrapper' )
					.html (data)
					.removeClass('loading');
					//.find('.fns-loglist-loading').remove();
				
				$('#wc_fns_logs_list').show();
				
				refresh_remove_logs_button();
			},
			dataType: 'html'
		});
		
	}
	
	
	function get_list_logs_ajaxified( $fnslogspag, $instance_id, $fnslogsperpag ) {

		// prevent double click
		if ( $('#logs_wrapper').hasClass('loading') ) return;
		
		$('#logs_wrapper').addClass('loading').append('<div class="fns-loglist-loading"><span class="wc-fns-spinner"></span></div>');
		
		$.ajax({
			url: ajaxurl,
			data: { action: 'wc_fns_logs_pane', fnslogspag: $fnslogspag, instance_id: $instance_id, fnslogsperpag: $fnslogsperpag },
			error: function (xhr, status, error) {
				var errorMessage = xhr.status + ': ' + xhr.statusText
				console.log('Error loading log list - ' + errorMessage);
				$('#logs_wrapper').removeClass('loading').find('.fns-loglist-loading').remove();
			},
			success: function (data) {
				
				if ( data == '0' ) {
					console.log('error loading log list');
					$( '#logs_wrapper' ).find('.fns-loglist-loading').remove();
					return
				}
				$( '#logs_wrapper' )
					.html (data)
					.removeClass('loading');
					//.find('.fns-loglist-loading').remove();
				
				$('#wc_fns_logs_list').show();
				
				refresh_remove_logs_button();
			},
			dataType: 'html'
		});
	}

	/*******************************************************
	    4. Dialogs and Help
	 *******************************************************/

	/* popup dialogs */
	$('#shipping-rules-table-fns > tbody').on('click', '.fns_open_fields_popup', function () {
		
		close_popup_dialog();
		close_popup_help();
		
		//Let's create a wrapper and copy the fields on it
		cont = $(this).closest('.action_details');
		$('.fns_fields_popup_wrap', cont).addClass('fns_popup_opened')
		
		$('body').append('<div id="fns_dialog"></div>');
		$('.fns_fields_popup', cont).appendTo('#fns_dialog');
		
		//Open the copy
		$('#fns_dialog').dialog({
			title: $('#fns_dialog .fns_fields_popup').attr('data-title'),
			dialogClass: 'wp-dialog',
			autoOpen: false,
			draggable: false,
			width: 'auto',
			modal: true,
			resizable: false,
			closeOnEscape: true,
			position: {
			  my: "center",
			  at: "center",
			  of: window
			},
			open: function () {
			  // close dialog by clicking the overlay behind it
			  $('.ui-widget-overlay').bind('click', function(){
				//$(the_dialog).dialog('close');
			  });
			  $('.fns-add-boxes-mode:checked').trigger('change'); // add-boxes (since x.x.x)
			},
			create: function () {
			  // style fix for WordPress admin
			  $('.ui-dialog-titlebar-close').addClass('ui-button');
			},
			buttons: [
				{
					text:   wcfns_data.i18n_close_bt,
					click:  close_popup_dialog
				}
			],
			close: function() {
				unsaved = true;
				close_popup_dialog()
			}
		});
		/* bind a button or a link to open the dialog
		$('a.open-my-dialog').click(function(e) {
			e.preventDefault();
			$('#my-dialog').dialog('open');
		});*/
		$('#fns_dialog').dialog('open');
		
		$('.ui-widget-overlay').css('opacity', 0).animate({opacity:1});
		$('body').addClass('fns-popup');
		
		$('.ui-dialog .ui-dialog-buttonset button').not('.ui-dialog-fns-samples button').attr('class', 'button button-primary button-large');
		
		// autosize textarea
		$('#fns_dialog textarea').each( function( idx, ta ) {
			h = ta.scrollHeight;
			if (h > 45 && h < 200 ) {
				jQuery(ta).height(h);
			} else if ( h >= 200 ) {
				jQuery(ta).height(200);
			} else {
				jQuery(ta).css('height', 'auto');
			}
		});
		
		return false;
	});

	function close_popup_dialog() {

		$('body.fns-popup .ui-dialog, .ui-widget-overlay').fadeOut(function () {
		
			$('body').removeClass('fns-popup');
			if ($('#fns_dialog').length==0) return;
			
			$('#fns_dialog .fns_fields_popup').appendTo('.fns_popup_opened');
			$('.fns_popup_opened').removeClass('fns_popup_opened');
			
			$('#fns_dialog')
				.dialog('close')
				.remove();
		});
	}

	/* special help popup global group_by */
	main_cont = $('#woocommerce_fish_n_ships_global_group_by, #woocommerce_fish_n_ships_global_group_by_method').closest('tr');
	$('.woocommerce-help-tip', main_cont).click(function () {
		show_help( 'group_by', false, wcfns_data['admin_lang'] );
		return false;
	});

	/* outside table rules fields help popup */
	main_cont = $('#woocommerce_fish_n_ships_title, #woocommerce_fish_n_ships_tax_status, #woocommerce_fish_n_ships_rules_charge, #woocommerce_fish_n_ships_min_shipping_price, #woocommerce_fish_n_ships_max_shipping_price, #woocommerce_fish_n_ships_write_logs').closest('tr');
	$('.woocommerce-help-tip', main_cont).click(function () {
		show_help( 'other_fields', false, wcfns_data['admin_lang'] );
		return false;
	});
	
	/* selection conditions */
	main_cont = $('#woocommerce_fish_n_ships_volumetric_weight_factor').closest('tr');
	$('.woocommerce-help-tip', main_cont).click(function () {
		show_help( 'sel_conditions', false, wcfns_data['admin_lang'] );
		return false;
	});
		
	/* help popups */
	$(document).on('click', 'a.woocommerce-fns-help-popup', function () {

		tip = $(this).attr('data-fns-tip');
		if (typeof tip !== typeof undefined && tip !== false) {
			
			// Clicked the button in the wizard? We will close it forever
			if ($(this).closest('.wc-fns-wizard-notice-4').length != 0) {

				$('.wc-fns-wizard-notice-4').slideUp(function () {
					$('div.wc-fns-wizard').remove();
				});
		
				$.ajax({
					url: ajaxurl,
					data: { action: 'wc_fns_wizard', ajax: 'wizard', param: 'off' },
					error: function (xhr, status, error) {
						var errorMessage = xhr.status + ': ' + xhr.statusText
						console.log('Fish n Ships, AJAX error - ' + errorMessage);
					},
					success: function (data) {
						if (data != '1') console.log('Fish n Ships, AJAX error - ' + data);
					},
					dataType: 'html'
				});
			}

			show_help(tip, false, wcfns_data['admin_lang'] );
			return false;
		}
	});
	
	/* links through help documents */
	$(document).on('click', 'nav.wc_fns_nav_popup a, nav.lang_switch a, a.wc_fns_nav_popup', function () {

		tip = $(this).attr('data-fns-tip');
		if (typeof(tip)=='undefined')
			return;
		
		lang = $(this).attr('data-fns-lang');
		if (typeof lang !== typeof undefined && lang !== false) {

			// Remember for the future
			wcfns_data['admin_lang'] = lang;

		} else {

			// Get the admin lang (or previously changed by the user)
			lang = wcfns_data['admin_lang'];
		}
		
		if ($('#fns_help').length!=0) {
		
			$('#fns_help')
				.dialog('close')
				.remove();
		}
		show_help(tip, true, lang);
		return false;
	});
	
	/* switch through help languages */
	$(document).on('change', 'nav.lang_switch select', function () {

		tip = $(this).closest('nav.lang_switch').attr('data-fns-tip');
		
		lang = $(this).val();
		if (typeof lang !== typeof undefined && lang !== false) {

			// Remember for the future
			wcfns_data['admin_lang'] = lang;

		} else {

			// Get the admin lang (or previously changed by the user)
			lang = wcfns_data['admin_lang'];
		}
		
		if ($('#fns_help').length!=0) {
		
			$('#fns_help')
				.dialog('close')
				.remove();
		}
		show_help(tip, true, lang);
		return false;
	});
	
	/* main help */
	function show_help($what, $concatenated, $lang) {
		
		$lang = $lang.split("_")[0]; // Let's cut the second part if there are

		if (!$concatenated) {
			
			close_popup_dialog();
			close_popup_help();
		}

		$('body').append('<div class="ui-widget-overlay ui-front fns-loading" style="z-index: 100;"><span class="wc-fns-spinner"></span></div>');

		if (!$concatenated) {
			$('.ui-widget-overlay').css('opacity', 0).animate({opacity:1});
		}

		$('body').addClass('fns-popup-opening');
		
		$.ajax({
			url: ajaxurl,
			data: { action: 'wc_fns_help', lang: $lang, what: $what },
			error: function (xhr, status, error) {
				$('body').removeClass('fns-popup-opening');
		    	var errorMessage = xhr.status + ': ' + xhr.statusText
				alert('Fns Help Error - ' + errorMessage);
				$('.ui-widget-overlay.fns-loading').remove();
			},
			success: function (data) {
				
				var parsed = $('<div/>').append(data);
				
				$('body').append('<div id="fns_help"><div class="popup_scroll_control">' + parsed.find("#content").html() + '</div></div>');
				
				// console.log(help_langs);
				// console.log($lang);

				n_lang = 0;
				for (x=0; x<help_langs.length; x++) {
					if (help_langs[x]==$lang) n_lang = x;
				}

				// Set the language selector
				var lang_selector = '<strong id="select_lang">' + help_langs_name[n_lang] + ' <span class="dashicons '
									+ 'dashicons-arrow-down-alt2"></span></strong><div id="lang_dropbox">';
				
				for (x=0; x<help_langs.length; x++) {
					if (x != n_lang) {
						lang_selector += '<a href="' + ($lang == 'en' ? '' : '../') + (x == 0 ? '' : help_langs[x] + '/') + $what + '.html" data-fns-tip="' + $what + '" data-fns-lang="'+help_langs[x]+'">' + help_langs_name[x] + '</a>';
					}
				}
				lang_selector += '</div>';
				
				$('#fns_help .popup_scroll_control .lang_switch').html(lang_selector);

				// Set the right URL to the img tags
				$('#fns_help .popup_scroll_control img').each(function(index, element) {
					url = $(element).attr('src');
					if ( typeof url !== typeof undefined && url !== false && url.indexOf('http') !== 0) $(element).attr('src', wcfns_data['help_url'] + url);
				});

				// Put the right URL to the A tags
				$('#fns_help .popup_scroll_control a').each(function(index, element) {
					url = $(element).attr('href');
					if ( typeof url !== typeof undefined && url !== false && url.indexOf('http') !== 0) $(element).attr('href', wcfns_data['help_url'] + url);
				});
				
				//Open the copy
				$('#fns_help').dialog({
					title: parsed.find("h1").html(),
					dialogClass: 'wp-dialog',
					autoOpen: false,
					draggable: true,
					width: $('#wpcontent').width() * 0.95,
					height: $(window).height() * 0.7,
					modal: true,
					resizable: true,
					closeOnEscape: true,
					position: {
					  my: "center",
					  at: "center",
					  of: window
					},
					open: function () {
					  // close dialog by clicking the overlay behind it
					  $('.ui-widget-overlay').bind('click', function(){
						//$(the_dialog).dialog('close');
						close_popup_help();
					  })
					},
					create: function () {
					  // style fix for WordPress admin
					  $('.ui-dialog-titlebar-close').addClass('ui-button');
					},
					buttons: [
						{
							text:   wcfns_data.i18n_close_bt,
							click:  close_popup_help
						}
					],
					close: function() {
						close_popup_help()
					}
				});
		
				$('#fns_help').dialog('open');
				$('body').addClass('fns-popup').removeClass('fns-popup-opening');
				$('.ui-widget-overlay.fns-loading').remove();
				$('.ui-dialog .ui-dialog-buttonset button').not('.ui-dialog-fns-samples button').attr('class', 'button button-primary button-large');
			},
			dataType: 'html'
		});
				
		return false;
	}

	function close_popup_help() {

		$('body.fns-popup .ui-dialog, .ui-widget-overlay').fadeOut(function () {

			$('body').removeClass('fns-popup');
			if ($('#fns_help').length==0) return;
			
			$('#fns_help')
				.dialog('close')
				.remove();
		});
	}

	/* language switcher */
	$(document).on('click', '#select_lang', function () {
		jQuery('#fns_help').toggleClass('show_langs');
		return false;
	});

	$(document).on('click', '#fns_help', function () {
		jQuery('#fns_help').removeClass('show_langs');
	});

	/*******************************************************
	    5. Start
	 *******************************************************/

	setTimeout(function () {
		$('#wrapper-shipping-rules-table-fns .overlay').animate({opacity: 0}, function () {
			$(this).remove();
		});
	}, 10);
	
	/*******************************************************
	    6. Freemium panel
	 *******************************************************/
	
	$('#wc-fns-freemium-panel .close_panel').click(function () {

		$.ajax({
			url: ajaxurl,
			data: { action: 'wc_fns_freemium', opened: '0' },
			error: function (xhr, status, error) {
				var errorMessage = xhr.status + ': ' + xhr.statusText
				console.log('Fish n Ships, AJAX error - ' + errorMessage);
			},
			success: function (data) {
				if (data != '1') console.log('Fish n Ships, AJAX error - ' + data);
			},
			dataType: 'html'
		});
		
		$('#wc-fns-freemium-panel').addClass('closed').removeClass('opened');

		if ($('#wc-fns-freemium-panel.pro-version').length != 0) {
			$('body').addClass('wc-fish-and-ships-pro-closed');
		}
		return false;
	});
	
	$('#wc-fns-freemium-panel .open_panel').click(function () {

		$.ajax({
			url: ajaxurl,
			data: { action: 'wc_fns_freemium', opened: '1' },
			error: function (xhr, status, error) {
				var errorMessage = xhr.status + ': ' + xhr.statusText
				console.log('Fish n Ships, AJAX error - ' + errorMessage);
			},
			success: function (data) {
				if (data != '1') console.log('Fish n Ships, AJAX error - ' + data);
			},
			dataType: 'html'
		});
		
		$('#wc-fns-freemium-panel').addClass('opened').removeClass('closed');

		if ($('#wc-fns-freemium-panel.pro-version').length != 0) {
			$('body').removeClass('wc-fish-and-ships-pro-closed');
		}
		return false;
	});
	
	// Show the serial field
	$('div.wc_fns_change_serial a').click(function () {
		$('div.wc_fns_change_serial').slideUp();
		$('div.wc_fns_hide_serial').slideDown();
		return false;
	});
	
	// Compress data to avoid max_input_vars error if required
	jQuery('button.woocommerce-save-button').bind('click', function(event)
	{
		//var elementsToSubmit = $('input, select, textarea', $(this).closest('form') );
		
		if( wcfns_data['max_input_vars'] <= $('input, select, textarea', $(this).closest('form') ).length )
		{
			target = $('#wrapper-shipping-rules-table-fns');

			console.log('Compress data to avoid max_input_vars at save, from: ' + $( 'input, select, textarea, button', $(this).closest('form') ).length );

			// Get the values of rule fields json-fied & delete they:
			compressed_data = json_stringify_fields( {}, target, true );
			
			$(target).append("<input type='hidden' id='fns_compressedData' name='shipping_rules[compressed]' />");
			$("#fns_compressedData").val(compressed_data);

			console.log('to: ' + $( 'input:not([name]), select, textarea, button', $(this).closest('form') ).length );
		}
	});

	// Remove required attr on fields when we're saving a new serial (the form is the same for everyone);
	$('#wc-fns-freemium-panel button[name="fns-register"]').click(function () {
		cont = jQuery(this).closest('form');
		jQuery('input, textarea, select', cont).removeAttr('required');
	});
	
	/*******************************************************
	    7. Free shipping options
	 *******************************************************/
	
	$( '#woocommerce_fish_n_ships_disallow_other' ).change( function(){
		if ( jQuery(this).hasClass('onlypro') ) {
			jQuery(this).prop( "checked", false );
			show_help( 'pro', false, wcfns_data['admin_lang'] );
			return false;
		}
	});
	$("#woocommerce_fish_n_ships_free_shipping").closest('tr').addClass('fns-no-bottom-padding');
	$("#woocommerce_fish_n_ships_disallow_other").closest('tr').addClass('fns_disallow_other_tr fns-no-top-padding');
	
	$('#woocommerce_fish_n_ships_free_shipping').change( function () {
		update_free_shipping();
	});
	
	function update_free_shipping() {
		if( $( '#woocommerce_fish_n_ships_free_shipping' ).is( ':checked' )  ) {
			$('.fns_disallow_other_tr').css('opacity', 1);
		} else {
			$('.fns_disallow_other_tr').css('opacity', 0.5);
		}
	}
	update_free_shipping();


	/*******************************************************
	    8. Wizard case studies & snippets
	 *******************************************************/
	
	// Move restart button
	if( $('.woocommerce-layout__activity-panel-tabs').length > 0 )
	{
		$('.woocommerce-layout__activity-panel-tabs').prepend( $('#activity-panel-tab-restart-fns') );
		
		if( $('.wc-fns-wizard').length == 0 ) 
			$('#activity-panel-tab-restart-fns').css('display','flex');
	}
	
	/* switch tabs samples */
	$(document).on('click', '.fns-samples-wizard nav a', function () {
		var cont = $(this).closest('.fns-samples-wizard');
		$( '.wc_fns_nav_popup a', cont).unwrap('strong');
		tab = $(this).wrap('<strong>').attr('data-fns-tab');
		$( '.wc_fns_tab', cont ).hide();
		$( '.wc_fns_tab_' + tab, cont ).show();
		
		if( $(this).closest('.ui-dialog').length == 1 ) {
			if( $(this).index( $( '.wc_fns_nav_popup a', cont) ) == 0 ) {
				$('.ui-dialog .fns-add-sel-snippets').hide();
			} else {
				$('.ui-dialog .fns-add-sel-snippets').show();
				setTimeout( check_snippets, 10 ); // Wait a few to open the dialog first
			}
		}
		return false;
	});
	
	/* open / close group of cases */
	$(document).on('click', '.fns-samples-wizard .fns-case-wrapper > h2', function() {
		cont = $(this).closest('.fns-case-wrapper');
		if( $(cont).hasClass('open') )
		{
			$('.sample-list', cont).stop().slideUp();
			$(cont).removeClass('open');
		}
		else
		{
			$('.sample-list', cont).stop().slideDown();
			$(cont).addClass('open');
		}
		return false;
	});
	
	/* open / close sample case */
	$(document).on('click', '.fns-samples-wizard .sample-list label', function() {
		cont = $(this).closest('li');
		if( $(cont).hasClass('open') )
		{
			$('.case', cont).stop().slideUp();
			$(cont).removeClass('open');
		}
		else
		{
			$('.case', cont).stop().slideDown();
			$(cont).addClass('open');
		}
		return false;
	});
	
	/* samples on wizard 
	if( $('.wc-fns-wizard-notice-4').length > 0 )
	{
		html = $('.fns-samples-wizard').prop('outerHTML');
		//html = '<form method="post" action="">' + html + '</div>';
		$('.wc-fns-wizard-notice-4 .fns-samples-wizard-insert').html( html );

		// Empty table rules? Remove the case warning
		if( $('select.wc-fns-selection-method').length == 1 && $('select.wc-fns-selection-method').val() == '' ) {
			$('.wc-fns-wizard-notice-4 .warning').remove();
		}
	}*/
	
	/* prepare samples & snippets popup */
	$('body').append('<div id="fns_samples"><div class="popup_scroll_control"></div></div>');
	$('#fns_samples .popup_scroll_control').append( $('form .fns-samples-wizard') );

	// Samples dialog will be created at body onload, to keep the samples AJAX loaded if the dialog is opened twice
	$('#fns_samples').dialog({
		title: 'Add Snippets or Full case examples',
		dialogClass: 'wp-dialog',
		autoOpen: false,
		draggable: true,
		width: $('#wpcontent').width() * 0.95,
		height: $(window).height() * 0.7,
		modal: true,
		resizable: true,
		closeOnEscape: true,
		position: {
		  my: "center",
		  at: "center",
		  of: window
		},
		open: function () {
		  // close dialog by clicking the overlay behind it
		  $('.ui-widget-overlay').bind('click', function(){
			//$(the_dialog).dialog('close');
			close_popup_samples();
		  })
		},
		create: function () {
		  // style fix for WordPress admin
		  $('.ui-dialog-titlebar-close').addClass('ui-button');
		},
		buttons: [
			{
				text:   wcfns_data.i18n_close_bt,
				click:  close_popup_samples
			}
		],
		close: function() {
			close_popup_help()
		}
	});
	
	$('#fns_samples').closest('.ui-dialog').addClass('ui-dialog-fns-samples');
		
	$('.ui-dialog-fns-samples .ui-dialog-buttonset').prepend( $('.ui-dialog .fns-add-sel-snippets') );
	$('.ui-dialog-fns-samples .ui-dialog-buttonset button').addClass('button button-large');

	// Open at button click
	$('a.wc-fns-add-snippet').click( function() {
		case_show_hide_warning();
		$('.ui-dialog .wc_fns_nav_popup a').eq(1).trigger('click');
		$('#fns_samples').dialog('open');
		load_samples();
		$('body').addClass('fns-popup');
		return false;
	});
	
	$('a.woocommerce-fns-case').click( function() {
		case_show_hide_warning();
		$('.ui-dialog .wc_fns_nav_popup a').eq(0).trigger('click');
		$('#fns_samples').dialog('open');
		load_samples();
		$('body').addClass('fns-popup');
		return false;
	});
	
	function load_samples()
	{
		if( $('.snippets-ajax-loading').length == 0 )
			return;
		
		$.ajax({
			url: ajaxurl,
			data: { action: 'wc_fns_samples' },
            dataType: 'json',
			error: function (xhr, status, error) {
				var errorMessage = xhr.status + ': ' + xhr.statusText
				alert('Error loading samples - ' + errorMessage);
			},
			success: function (data) {
				
				// console.log(data);
				
                $('.snippets-ajax-loading').replaceWith(data.snippets);
                $('.fullsamples-ajax-loading').replaceWith(data.fullsamples);
            },
		});
	}
	
	function case_show_hide_warning() {
		// Empty table rules? Hide the case warning
		if( is_table_rules_empty() ) {
			$('#fns_samples .warning').hide();
		} else {
			$('#fns_samples .warning').show();
		}
	}
	
	function is_table_rules_empty() {
		
		return $('select.wc-fns-selection-method').length == 1 && $('select.wc-fns-selection-method').val() == '';
	}

	function close_popup_samples() {

		$('body.fns-popup .ui-dialog, .ui-widget-overlay').fadeOut(function () {

			$('body').removeClass('fns-popup');
			if ($('#fns_samples').length==0) return;
			
			$('#fns_samples')
				.dialog('close');
				//.remove();
		});
	}
	
	$('.fns-samples-wizard').show();
	
	// Snippets checkboxes
	$(document).on('change','.wc_fns_tab_snippets input[type="checkbox"]', function() {
				
		if( ! check_snippets( true ) && $(this).is(':checked') )
		{
			$(this).prop('checked', false);
			check_snippets( false ); // Should re-activate the Add snippets button?
		}
	});

	
	function check_snippets( alert_errors )
	{
		// Default true:
		alert_errors = typeof alert_errors !== 'undefined' ? alert_errors : true;
		
		var any_group_by = true;
		var compatibles_group_by = new Array();
		var its_ok = true;
				
		// Check compatiblilty of selected snippets for free version
		$('.wc_fns_tab_snippets input[type="checkbox"]:checked').each( function( idx, el )
		{
			restriction = $(el).attr('data-restrict-group_by');
			if( typeof restriction !== 'undefined' && restriction !== false )
			{
				// Turn into array
				restriction = restriction.toString().split(',');
				if( any_group_by )
				{
					any_group_by = false;
					compatibles_group_by = restriction;
				}
				else
				{
					// Array intersection
					compatibles_group_by = compatibles_group_by.filter(function(n) {
						return restriction.indexOf(n) !== -1;
					});
				}
			}
		});
		
		// Incompatible group-by in the selected snippets?
		if( ! any_group_by && compatibles_group_by.length < 1 )
		{
			show_groupby_requeriments();
			var message = "Sorry. The selected snippets needs distinct group-by strategies.";
			message += "\n\nDistinct group-by strategies in the same shipping method is only available in Advanced Shipping Rates for WooCommerce PRO";
			if( alert_errors ) alert(message);
			// $(this).prop('checked', false);
			its_ok = false;
		}
		
		// We must change the group-by setting?
		else if( ! any_group_by && ! is_table_rules_empty() )
		{
			var current_setting = $('#woocommerce_fish_n_ships_global_group_by_method option:selected').val();
			
			if( compatibles_group_by.indexOf( current_setting ) == -1 )
			{
				show_groupby_requeriments();
				
				if( compatibles_group_by.length == 1 )
				{
					var message = "Group-by must be set to: ";
				}
				else
				{
					var message = "Group-by must be set to one of this: ";
				}

				$(compatibles_group_by).each( function( idx, req )
				{
					if( idx > 0 ) message += ' or ';
					message +=  humanize_group_by( req ) ;
				});

				message += "\n\nYour snippet selection needs a group-by strategy change in the shipping method settings.";
				message += "\n\nTo address this, please, close the samples popup and locate the 'Global group-by' option in the top area of the settings page. Please, check if your rules will continue working with this change before do it.";
				
				message += "\n\nDistinct group-by strategies in the same shipping method is only available in Advanced Shipping Rates for WooCommerce PRO";
				if( alert_errors ) alert(message);
				//$(this).prop('checked', false);
				its_ok = false;
			}
		}
		
		// Mark selected 
		$('.wc_fns_tab_snippets .case-sel').removeClass('case-sel');
		$('.wc_fns_tab_snippets input:checked').each( function( idx, el ) {
			$(el).closest('li').addClass('case-sel');
		});
		
		// Count selecteds
		$('.fns-case-wrapper').each( function( idx, el ) {
			count = $('input:checked', el).length;
			if( count == 0 )
			{
				$('h2 .counter', el).html('');
			}
			else
			{
				$('h2 .counter', el).html( ' (' + count + ')' );
			}
		});
		
		// Enable / disable the "Add selected snippets" button
		var sel   = $('.wc_fns_tab_snippets input:checked').length;

		if( ! its_ok || sel == 0) {
			$('.ui-dialog-fns-samples .fns-add-sel-snippets').addClass('disabled');
		} else {
			$('.ui-dialog-fns-samples .fns-add-sel-snippets').removeClass('disabled');
		}
		
		return its_ok;
	}
	
	// Snippets add button click
	$(document).on('click','.fns-add-sel-snippets', function() {
		
		if ( $(this).closest('.wc_fns_tab').length == 1 ) {
			// Obsolete?
			var cont  = $(this).closest('.wc_fns_tab');
			alert('1');
		} else {
			var cont  = $('.ui-dialog .wc_fns_tab_snippets');
		}
		
		if ( $('input:checked', cont).length == 0 ) return false;
		
		$('#shipping-rules-table-fns input, #shipping-rules-table-fns select').removeAttr('required');

		// Copying select values
		$('select', cont).each( function(idx, el) {
			$('#mainform').append( '<input type="hidden" name="' + $(el).attr('name') + '" value="' + $(el).val() + '" />' );
		});

		$('#mainform')
			.append( $('input:checked', cont).clone() )
			.append( '<input type="hidden" name="keep_current" value="1" />' )
			.find('.woocommerce-save-button').prop('disabled', false).trigger('click');
			
		return false;
	});
	
	// Full case add button click
	$(document).on('click','.wc_fns_tab_fullsamples button', function() {

		if( $(this).closest('li').length < 1 )
			return false;

		var key   = $(this).attr('value');		
		var cont  = $(this).closest('.case');
		
		$('#shipping-rules-table-fns input, #shipping-rules-table-fns select').removeAttr('required');
		
		// Copying select values
		$('select', cont).each( function(idx, el) {
			$('#mainform').append( '<input type="hidden" name="' + $(el).attr('name') + '" value="' + $(el).val() + '" />' );
		});

		$('#mainform')
			.append( $('select', cont).clone() )
			.append( '<input type="hidden" name="wc-fns-samples[]" value="'+key+'" />' )
			.append( '<input type="hidden" name="keep_current" value="0" />' )
			.find('.woocommerce-save-button').prop('disabled', false).trigger('click');
			
		return false;
	});
	
	// Only in case of group-by snippets incompatibility, we will show it
	function show_groupby_requeriments() {
		
		if( $('.fns-req-group-by').length > 0 ) return;
		
		$('.wc_fns_tab_snippets input[type="checkbox"]').each( function( idx, el )
		{
			restriction = $(el).attr('data-restrict-group_by');
			if( ( ! $(el).closest('li').hasClass('only_pro') ) && typeof restriction !== 'undefined' && restriction !== false )
			{
				html_info = '';
				casewrap  = $(el).closest('.case');
				
				// Turn into array
				restriction = restriction.toString().split(',');
				
				if( restriction.length == 1 )
				{
					html_info = 'Requires group-by set as: ';
				} else {
					html_info = 'Requires group-by set as one of this: ';
				}
				
				$(restriction).each( function( idx, req )
				{
					html_info += '<span>' + humanize_group_by( req ) + '</span>';
				});
				
				$(casewrap).append('<p class="fns-req-group-by">' + html_info + '</p>');
			}
		});
		
	}
	
	function humanize_group_by( req )
	{
		if( req == 'none' ) req = 'None';
		if( req == 'id_sku' ) req = 'ID/SKU';
		if( req == 'product_id' ) req = 'Per product';
		if( req == 'class' ) req = 'Shipping class';
		if( req == 'all' ) req = 'All together';
		
		return req;
	}

	/*******************************************************
	    9. Ranges Wizard
	 *******************************************************/
	
	$( '#wrapper-shipping-rules-table-fns' ).on('click', '.fns-range-config-bt', function ()
	{	
		cont    = $(this).closest('.shipping-costs-column');
		rule_n  = $('#wrapper-shipping-rules-table-fns .config-cost-method').index(this);
		
		// Prepare & open dialog
		$('body').append('<div id="fns_dialog">'+$('.fns-range-wizard-wrapper').html()+'</div>');
		
		$('#fns_dialog').dialog({
			title: 'Range settings',
			dialogClass: 'wp-dialog',
			autoOpen: false,
			draggable: true,
			width: 'auto',
			modal: true,
			resizable: true,
			closeOnEscape: true,
			position: {
				my: "center",
				at: "center",
				of: window
			},
			open: function () {
				// close dialog by clicking the overlay behind it
				$('.ui-widget-overlay').bind('click', function() {
					close_popup_dialog();
				});

				// Pass all fields values to dialog
				$('.cost_range input', cont).each( function(idx, el) {
					name  = $(el).attr('data-fns-range-field');
					val   = $(el).val();
					$('#fns_dialog [name="dialog-'+name+'"]').val(val);
				});
				
				refresh_range_wizard();

				// version 1.6.3 DIM into each range popup. Copy value from external field if this is empty
				if( 
					( ! parseFloat( $('#fns_dialog input[name="dialog-range_dim"]').val() ) > 0 ) 
					&&  parseFloat( $('#woocommerce_fish_n_ships_volumetric_weight_factor').val() ) > 0 
				){
					$('#fns_dialog input[name="dialog-range_dim"]').val( $('#woocommerce_fish_n_ships_volumetric_weight_factor').val() );
				}
			},
			create: function () {
				// style fix for WordPress admin
				$('.ui-dialog-titlebar-close').addClass('ui-button');
			},
			buttons: [
				{
					text:   wcfns_data.i18n_close_bt,
					click:  close_popup_dialog
				}
			],
			close: function() {

				// Pass all fields values to dialog
				$('#fns_dialog input, #fns_dialog select').each( function(idx, el) {

					name  = $(el).attr('name');
					if( name.substr(0,7) != 'dialog-' )
						return
					
					val = $(el).val();
					$('[data-fns-range-field="'+name.substr(7)+'"]', cont).val(val);
				});

				close_popup_dialog();
			}
		});

		$('body').addClass('fns-popup');
		$('.ui-dialog .ui-dialog-buttonset button').not('.ui-dialog-fns-samples button').attr('class', 'button button-primary button-large');
		$('#fns_dialog').dialog('open');
		
		$('#fns_dialog select[name="dialog-range_based"]').click( function() {
			refresh_range_wizard();
		});
		$('#fns_dialog .nav-tab').click( function() {
			setTimeout( refresh_range_wizard, 10 );
		});
		$('#fns_dialog .fns_range_fields')
			.change( function() {
				refresh_range_wizard();
			})
			.keyup( function() {
				refresh_range_wizard();
			});

		return false;
	});
	
	
	function refresh_range_wizard()
	{	
		range_based    = $('#fns_dialog select[name="dialog-range_based"]').val();
		range_based_n  = $('#fns_dialog select[name="dialog-range_based"]').prop('selectedIndex');
		groupbyel      = $('#fns_dialog select[name="dialog-range_group_by"]');

		// version 2.0.1 DIM into each range popup show/hide & copy value to external field if is empty
		show_dim       = range_based == 'volumetric' || range_based == 'volumetric-set';
		$('#fns_dialog .fns-dim-col').toggle(show_dim);
		if( 
			( ! parseFloat( $('#woocommerce_fish_n_ships_volumetric_weight_factor').val() ) > 0 ) 
			&&  parseFloat( $('#fns_dialog input[name="dialog-range_dim"]').val() ) > 0 
		){
			$('#woocommerce_fish_n_ships_volumetric_weight_factor').val( $('#fns_dialog input[name="dialog-range_dim"]').val() );
		}

		if( range_based == 'lwh-dimensions' || range_based == 'lgirth-dimensions' )
		{
			if( ! $(groupbyel)[0].hasAttribute('data-fns-remember') )
				$(groupbyel).attr( 'data-fns-remember', $(groupbyel).val() );

			$('#fns_dialog select[name="dialog-range_group_by"]')
				.val('none')
				.attr('disabled', true);
		}
		else
		{
			$('#fns_dialog select[name="dialog-range_group_by"]')
				.attr('disabled', false);

			if( $(groupbyel)[0].hasAttribute('data-fns-remember') )
			{
				$(groupbyel)
					.val( $(groupbyel).attr('data-fns-remember') )
					.removeAttr('data-fns-remember');
			}
		}

		// Show the right unit
		$('#fns_dialog .fns-unit-switcher').each( function( idx, el ) {
			$(el).show();
			$('span', el).hide();
			$('span', el).eq(range_based_n).show();
			
			preview_unit = $('span', el).eq(range_based_n).html();
		});
				
		// Simulation
		prefix = '';
		currency = $('.currency-switcher-fns-wrapper .units:first ').text();
		if( $('#woocommerce_fish_n_ships_multiple_currency').is(':checked') )
		{
			n = $('#fns_dialog .nav-tab').index( $('#fns_dialog .nav-tab-active') );
			currency = $('.currency-switcher-fns-wrapper .units ').eq(n).text();
			if( n > 0 ) prefix = '-' + $('#fns_dialog .nav-tab-active').attr('data-fns-currency');
		}

		base    = numerize( $('#fns_dialog input[name="dialog-range_base'+prefix+'"]').val(), 0 );
		charge  = numerize_abs( $('#fns_dialog input[name="dialog-range_charge'+prefix+'"]').val(), 0 );

		unit    = $('#fns_dialog .fns-unit-switcher span').eq(range_based_n).html();
		over    = numerize_abs( $('#fns_dialog input[name="dialog-range_over"]').val(), 0 );
		foreach = numerize_abs( $('#fns_dialog input[name="dialog-range_foreach"]').val(), 1 );

		// Same calculations here in JS simulation and PHP shipping rate calculation
		if( foreach < 0.0000001 ) foreach = 0;
		if( over    < 0.0000001 ) over = 0;
		
		// Simulation
		simulation_range  = foreach * 4;
		simulation_step   = (range_based == 'quantity') ? foreach : foreach / 2;
		simulation_start  = (over - simulation_step * 3);

		simulation_precisio = 0;
		if( simulation_step < 1 ) simulation_precisio = 1;
		if( simulation_step < 0.1 ) simulation_precisio = 2;
		if( simulation_step < 0.01 ) simulation_precisio = 3;
		if( simulation_step < 0.001 ) simulation_precisio = 4;
		if( simulation_step < 0.0001 ) simulation_precisio = 5;

		// step = foreach / 2; // Això cal que sigui rodó per al preview, p.e. "range over 4,9" fa un desastre
		// start = over - step * 3;
		if( simulation_start < 0 ) simulation_start = 0;
		
		code_simulation = '<table class="widefat striped"><tr>';
		for( n=0; n<10; n++) {
			
			value  = simulation_start + simulation_step * n;
			value  = limitDecimals( value, simulation_precisio );
			value  = value.toString().replace( '.', wcfns_data.decimal_separator);

			code_simulation += '<td>' + value + unit + '</td>';
		}
		code_simulation += '</tr><tr>';
		for( n=0; n<10; n++) {
			
			value  = simulation_start + simulation_step * n;
			value  = limitDecimals( value, simulation_precisio );
console.log('value: ' + value);
			// Value must be rounded?
			// if ( value%1 > .9 && charge > 0.49 ) value = Math.round(value);
			// if ( value%1 > .95 && charge > 0.3 ) value = Math.round(value);
			
			// Same calculations here in JS simulation and PHP shipping rate calculation
			calculation = 0;
			ranges = Math.ceil( ( value - over ) / foreach);
			if( ranges < 0 ) ranges = 0;

			calculation = base + ranges * charge;

			if( calculation < 0 ) calculation = 0;
			calculation = limitDecimals( calculation, 0 );
			calculation = calculation.toString().replace( '.', wcfns_data.decimal_separator);
			code_simulation += '<td>' + calculation + currency + '</td>';
		}
		code_simulation += '</tr></table>';
		
		$('#fns_dialog .fns-simulation').html( code_simulation );
	}
	
	function numerize(text, default_vaule) {
		text = text.toString().replace(',', '.');
		if( text == '' ) text = default_vaule;
		value = isNaN( parseFloat(text) ) ? default_vaule : parseFloat(text);
		return value;
	}

	function numerize_abs(text, default_vaule) {
		value = numerize(text, default_vaule);
		if( value < 0 ) value = 0;
		return value;
	}
		
	function limitDecimals(numero, decimals) {

		if ( numero == 0 ) 
			return 0;

		while (true) {

			var testRounded = parseFloat(numero.toFixed(decimals));

			if (Math.abs(numero - testRounded) / numero < 0.05 && decimals > 1) {
				return testRounded;
			}
			if (Math.abs(numero - testRounded) / numero < 0.01 ) {
				return testRounded;
			}
			decimals++;
		}
	}

	/* FINALLY, REFRESH RULES: */
	refresh_rules();
});
/*
jQuery(window).bind("load", function() {
	
	setTimeout( function () {
		// Prevent change serial field bubbling to prevent unsaved changes alert
		jQuery('#wc-fns-freemium-panel button').bind('click', function(event) {
			alert('2');
			window.onbeforeunload = function() {};
		});
	}, 100 );
});*/

/*************************************************
  Shipping Boxes settings
*************************************************/

jQuery(document).ready(function($) {

	$("#fns_sb_table tbody").sortable({
		//items: item_selector,
		cursor: 'move',
		//handle: '.column-handle',
		axis: 'y',
		forcePlaceholderSize: true,
		helper: 'clone',
		opacity: 0.65,
		//placeholder: 'fns-rule-placeholder',
		scrollSensitivity: 40,

		// This event is triggered when the user stopped sorting and the DOM position has changed.
		update: function( event, ui ) {

			//fix_cell_colors('#shipping-rules-table-fns > tbody tr');

			//refresh_rules();
		}
	});
	
	$('#fns_sb_table .add-box').click( function () {

		cloned = $("#fns_sb_table tbody tr:first").clone();

		// Reset info
		$(cloned).find('.numid').text('');
		$(cloned).find('.fns-sb-num input').val('0');
		$(cloned).find('input[name="fns-sb-id[]"]').val('');
		$(cloned).find('input[name="fns-sb-name[]"]').val('Unnamed');
		$(cloned).find('.fns-sb-actions').html('<em>unsaved</em>');
		
		$(cloned).appendTo("#fns_sb_table tbody");
		
		return false;
	});
	
	$( '#fns_sb_table' ).on('click', '.fns-sb-delete', function () {
		var el = $(this).closest('tr');
		$(el).fadeOut(function () {
			$(el).remove();
		});
		return false;
	});
});