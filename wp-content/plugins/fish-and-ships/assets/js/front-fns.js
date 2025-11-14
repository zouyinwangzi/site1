/**
 * Front-end JS for cart. Compatible with WC blocks
 *
 * @package Advanced Shipping Rates for WC Pro
 * @since 1.1.12
 * @version 2.0.1
 */

(function() {
	
	// Outdated Error Messages Prevention in WC block messaging
	var outdatedErrorMessagesP = [];
	
	var latestPhpTime = 0; // Cache issues prevention

	// Here we will detext the initial printing of shipping methods
	// Needed for subtitles

	document.addEventListener('DOMContentLoaded', function() {
		var targetNode = document.body;
		var config = { childList: true, subtree: true };

		var callback = function(mutationsList, observer) {
			for (var mutation of mutationsList) {
				if (mutation.type === 'childList') {
					for (var node of mutation.addedNodes) {
						if (node.nodeType === 1) { // Check if the node it's an element
							//var div = node.querySelector('div.wp-block-woocommerce-cart-order-summary-shipping-block');
							//maybe_console_log(div);
							
							fullHtml=node.outerHTML;
							if (	fullHtml.includes('wp-block-woocommerce-cart-order-summary-shipping-block')
								 || fullHtml.includes('wc-block-checkout__shipping-option') ) {
								maybe_console_log('New blocks for cart or checkout detected.');
								
								// First load
								if( typeof wcfns_data !== 'undefined' && wcfns_data.descriptions )
								{
									add_subtitles_for_blocks(wcfns_data.descriptions);
								}

								observer.disconnect(); // First load only
								
								return; // Prevent multiple registrations

								/*if (div.hasChildNodes()) {
									maybe_console_log('Div found with content:', div);
									observer.disconnect();
									return; // Prevent multiple registrations
								}*/
							}
							
							/*if (div) {
								// Check for node childs
								if (div.hasChildNodes()) {
									maybe_console_log('Div found with content:', div);
									observer.disconnect();
									return; // Prevent multiple registrations
								}
							}*/
						}
					}
				}
			}
		};

		var observer = new MutationObserver(callback);
		observer.observe(targetNode, config);

		maybe_console_log('Observer configured for body.');
	});





	/*setTimeout( function() {
		//console.dir(wcSettings, { depth: null });
	}, 10000);

	//alert('x');

	maybe_console_log('window.fetch:');
	maybe_console_log(window.fetch);
	*/


	// Listen for block shipping methods update

	// Save original fetch
	const originalFetch = window.fetch;

	// Replace fetch with own
	window.fetch = async function() {
		// Show fetch args
		maybe_console_log('Fetch request:', arguments);

		// Perform original fetch
		const response = await originalFetch.apply(this, arguments);

		// Clone reply to analize without consume it
		const clonedResponse = response.clone();

		// Process the cloned reply as JSON
		clonedResponse.json().then(data => {
			
			maybe_console_log('window.fetch, data:');
			maybe_console_log(data);

			var new_data = false;
			if (data.responses && data.responses[0] && data.responses[0].body && data.responses[0].body.wcfns_data) {
				// Multiple info comes into array
				new_data = data.responses[0].body.wcfns_data;
			} else if (data.wcfns_data) {
				// Single info, no array form
				new_data = data.wcfns_data;
			} else {
				maybe_console_log('No wcfns data.');
			}
			
			if( new_data )
			{
				if( new_data.php_time && new_data.php_time > latestPhpTime )
				{
					latestPhpTime = new_data.php_time;
					
					if( new_data.descriptions )
						add_subtitles_for_blocks(new_data.descriptions);

					if( new_data.messages )
						new_update_messages( new_data.messages );
				}
				else
				{
					maybe_console_log('Old fetch data, maybe cached?');
				}
			}
		}).catch(error => {
			maybe_console_log('Error processing response as JSON:', error);
		});
		
		//maybe_console_log('Fetch data:');
		//maybe_console_log(data.wcfns_data);
		
		//add_subtitles_for_blocks(); // After updates
		//new_update_messages();
		
		// Response the original reply
		return response;
	};



	function add_subtitles_for_blocks( data ) {

		maybe_console_log('add_subtitles_for_blocks:');

		if (typeof jQuery === 'undefined')
			return;
		
		jQuery('.fns-description.blocks').remove();

		jQuery('.wc-block-components-shipping-rates-control input').each( function( idx, el ) {

			for ( const [method_id, subtitle] of Object.entries(data) ) {

				//maybe_console_log("Index: " + method_id + ", Valor: " + subtitle);
				
				if( jQuery(el).attr('value') == 'fish_n_ships:' + method_id )
				{
					wrapper = jQuery(el).closest('label');
					jQuery('.wc-block-components-radio-control__label-group', wrapper).after('<div class="fns-description blocks">' + subtitle + '</div>');
				}
			}
		});
	}

	// Refresh the messages
	function new_update_messages( messages ) {
		
		maybe_console_log('new_update_messages()');
		
		if (typeof jQuery === 'undefined')
			return;
		
		// Remember the error messages
		outdatedErrorMessagesP = [...outdatedErrorMessagesP, ...messages
			.filter(msg => msg.type === 'error')
			.map( msg => normalitzeText(msg.message) ) ];

		// Sticky only in cart and checkout? skip if we aren't here
		if( typeof wcfns_data !== 'undefined' && wcfns_data.is_checkout == 0 && wcfns_data.is_cart == 0 )
		{
			Object.keys(messages).forEach((key, idx) => {
				
				if( messages[key].show == 'scrtchk' )
					delete messages[key];
			});
		}

		// WooCommerce blocks show the errors in cart & checkout at load time
		// Let's try to identify it and add notice_control then
		jQuery('.wc-block-components-notice-banner.is-error').each( function( idx, el )
		{
			if( jQuery('.fns_notice_control', el).length > 0 )
				return; // Previously identified
			
			// Try to get the error text message
			if( jQuery('.wc-block-components-notice-banner__content', el).length != 1 )
				return;
			text_error = normalitzeText( jQuery('.wc-block-components-notice-banner__content', el).text() );
			
			// Seek it in the current messages
			message = messages.find( msg => msg.type=='error' && normalitzeText(msg.message) == text_error );
			
			if( message ) {
				// Identify it
				jQuery(el).append( get_html_control( message ) );
			}
			else
			{
				// Old error, not currently active. Let's hide it:
				if( outdatedErrorMessagesP.includes(text_error) )
					jQuery(el).hide();
			}
		});
		
		// Remove old messages
		jQuery('div.fns_notice_control').each( function( idx, el )
		{	
			id = jQuery(el).attr('data-id');
			
			maybe_console_log( 'There is a message with the id: ' + id );
			
			message = messages.find(msg => msg.id === id);

			if( ! message )
			{
				maybe_console_log('delete the message');
				hide_message( el, false );
			}
			else
			{
				maybe_console_log('keep the message');
				ensure_message_shown( el ); // maybe hidden
			}
		});

		// Add messages that they aren't
		must_do_scroll2messages = false;
		Object.keys(messages).forEach((key, idx) => {
						
			message = messages[key];
			maybe_console_log( 'looking for messages with the id: ' + message.id );
			
			found = jQuery('div.fns_notice_control[data-id="'+message.id+'"]');

			/* WooCommerce blocks show the errors in cart & checkout at load time
			if( message.type == 'error' && jQuery('.wc-block-components-notice-banner.is-error').length > 0 )
			{
				if( found.length > 0 ) jQuery(found).remove();
				return;
			}*/
			
			if( found.length == 0 ) 
			{
				if( message.deadline && wcfns_data.php_time > message.deadline ) {
					maybe_console_log('outdated message, we won\'t add it');
				} 
				else
				{
					// Add message
					jQuery( get_notices_wrapper(document) ).prepend( get_html_message(message) );
					maybe_console_log('adding the message');
					must_do_scroll2messages = true;
				}
			}
			
			// Update message content & prevent duplications
			jQuery(found).each( function(idx, el)
			{
				if( idx == 0 )
				{
					if( generateCompactHash(message) != jQuery(el).attr('data-hashed' ) )
					{
						maybe_console_log('the message had been changed');
						wrapper_el = get_message_wrapper(el);
						jQuery(wrapper_el).replaceWith( get_html_message(message) );
						ensure_message_shown( el ); // maybe hidden
						must_do_scroll2messages = true;
					}
					else
					{
						ensure_message_shown( el ); // maybe hidden
						maybe_console_log('the message remains unchanged');
					}
				}
				else
				{
					maybe_console_log('deleting duplicated message');
					hide_message( el, true );
				}
			});
		});
		
		// Some message added / content changed? Let's scroll to:
		if( must_do_scroll2messages )
			scroll_to_messages();		
	}

	function get_html_message( message )
	{	
		type = message.type;
		
		// Try to get the new block style message template
		if( typeof wcfns_data !== 'undefined' && wcfns_data['tmpl_'+type] && wcfns_data['tmpl_'+type] != '' )
		{
			// Get message template and replace the text:
			html = wcfns_data['tmpl_'+type];
			html = html.replace('#text#', '<div class="fns-text">' + message.message + '</div>');
			
			// Add control before close the last tag:
			let index = html.lastIndexOf('</div>');
			if (index === -1) index = html.lastIndexOf('</');
			html = html.substring(0, index) + get_html_control(message) + html.substring(index);

			// Add classes in the first wrapper:
			html = html.replace(
				/class\s*=\s*(['"])\s*(.*?)\s*\1/,  // Cerca `class="..."` o `class='...'` amb espais
				(match, quote, classes) => `class=${quote}${classes.trim()} ${message.cssextra}${quote}`
			);
			
			console.log(html);
			
			return html;
		}
		
		// Keep the old system as fallback:
		if( type == 'success' ) type = 'message';
		if( type == 'notice' )  type = 'info';
		
		html = '<div class="woocommerce-' + type + ' ' + message.cssextra + '" role="alert"><div class="fns-text">' + message.message + '</div>'
				+ get_html_control( message ) + '</div>';
		
		return html;
	}

	function get_html_control( message )
	{	
		return '<div class="fns_notice_control" data-id="'+message.id+'" data-hashed="'+generateCompactHash(message)+'" style="display: none;"></div>';
	}
	
	// Used for comparison
	function generateCompactHash(obj) {
		// Turn object or array into JSON object
		const jsonString = JSON.stringify(obj);

		// Generate a compact hash with a bit to bit sum
		let hash = 0;
		for (let i = 0; i < jsonString.length; i++) {
			const char = jsonString.charCodeAt(i);
			hash = (hash << 5) - hash + char; // Bit to bit operations
			hash = hash & hash; // Keep into 32 bits
		}

		// Short it by make it hex
		return Math.abs(hash).toString(16);
	}

	function hide_message( control_el, fast )
	{
		wrapper = get_message_wrapper( control_el );
		
		if( wrapper && fast )
			jQuery(wrapper).addClass('fns-hidden' ).hide();

		if( wrapper && !fast )
			jQuery(wrapper).addClass('fns-hidden' ).fadeOut();
	}

	function ensure_message_shown( control_el )
	{
		wrapper = get_message_wrapper( control_el );
		
		if( wrapper && jQuery(wrapper).hasClass('fns-hidden' ) )
		{
			jQuery(wrapper).removeClass('fns-hidden' ).fadeIn();
		}
	}

	function get_message_wrapper( control_el ) {

		const possible_wrappers = [".wc-block-components-notice-banner", ".wc-block-store-notice", ".woocommerce-info", ".woocommerce-error", ".woocommerce-message"];
		
		// Old shortcode-way sometimes add the errors as elements in a unique list
		if( jQuery(control_el).closest('ul.woocommerce-error').length > 0 )
		{
			maybe_console_log('old-style list errors detected');
			possible_wrappers.unshift('li');
		}
		
		result = false;
		
		possible_wrappers.forEach( lookinfor => {
			
			match = jQuery(control_el).closest( lookinfor );
			if( match.length > 0 )
			{
				maybe_console_log('wrapper found: ');
				maybe_console_log(match);
				result = match;
				return true; // exit forEach
			}
		});
		
		return result;
	}

	function get_notices_wrapper( obj ) {
		
		$target = jQuery(obj).find( '.wc-block-components-notices' ).first(); 
		if ($target.length != 0) return $target;
		
		$target = jQuery(obj).find( '.woocommerce-notices-wrapper' ).first(); 
		if ($target.length != 0) return $target;

		$target = jQuery(obj).find( '.cart-empty' ).closest( '.woocommerce' ).first();  
		if ($target.length != 0) return $target;
		
		$target = jQuery(obj).find( '.woocommerce-cart-form' ).first();  
		if ($target.length != 0) return $target;

		$target = jQuery(obj).find( 'main' ).first(); 
		if ($target.length != 0) return $target;
		
		maybe_console_log('Can\'t find notices wrapper');
		return false;
	}

	function scroll_to_messages() {
		
		scrollElement = get_notices_wrapper ( document );
		
		new_top         = scrollElement.first().offset().top - 150;
		current_top     = jQuery(window).scrollTop();
		viewportHeight  = jQuery(window).height();
		
		if( new_top < current_top || new_top > current_top + viewportHeight / 2 )
		{
			jQuery( 'html, body' ).animate( {
				scrollTop: new_top
			}, 1000 );
		}
	}

	function refresh_wc_printed_messages()
	{
		jQuery( '.fns_notice_control' ).hide();

		jQuery( '.fns_notice_control' ).each( function( idx, el )
		{
			wrapper   = get_message_wrapper( el );
			cssextra  = jQuery(el).attr('data-cssextra');
			jQuery(wrapper).addClass(cssextra);
		});
	}

	function refresh_after_classic_update()
	{
		maybe_console_log('refresh_after_classic_update()');

		// .wc_fns_cart_control
		if( jQuery('.wc_fns_cart_control').length > 0 )
		{
			code = jQuery('.wc_fns_cart_control').last().text();

			try {
				maybe_console_log( JSON.parse( atob(code) ) );
				var new_data = JSON.parse( atob(code) );
				maybe_console_log(new_data);
			} catch (error) {
				console.error("[Advanced Shipping Rates for WooCommerce] Error parsing JSON:", error);
				return;
			}

			if( new_data.php_time && new_data.php_time > latestPhpTime )
			{
				latestPhpTime = new_data.php_time;

				if( new_data.descriptions )
					add_subtitles_for_blocks(new_data.descriptions);

				if( new_data.messages )
					new_update_messages( new_data.messages );
			}
			else
			{
				maybe_console_log('Old cart control data, maybe cached?');
			}
		}
	}

	function normalitzeText(text) {
		return text
			.trim()  // Trim spaces at start/end
			.replace(/\s+/g, ' ')  // Replace multiple spaces and breaklines
			.normalize("NFC");  // Normalize unicode chars (tildes, etc)
	}


	jQuery(document).ready(function($) {
			
		refresh_wc_printed_messages();
				
		if( typeof wcfns_data !== 'undefined' && wcfns_data.php_time )
		{
			latestPhpTime = wcfns_data.php_time;
			
			if( wcfns_data.messages )
				new_update_messages( wcfns_data.messages );
		}
		
		/* WC Events */
		jQuery(document).on('wc_fragments_loaded event', function () {
			//alert('wc_fragments_loaded');
			maybe_console_log('wc_fragments_loaded');
			refresh_after_classic_update();
			//new_update_messages();
		});
		jQuery(document).on('updated_checkout', function () {
			maybe_console_log('updated_checkout event');
			refresh_wc_printed_messages();
			refresh_after_classic_update();
			// new_update_messages();
		});
		jQuery(document).on('wc_fragments_refreshed', function () {
			maybe_console_log('wc_fragments_refreshed event');
			refresh_after_classic_update();
		});
		jQuery(document).on('updated_wc_div', function () {
			maybe_console_log('updated_wc_div event');
			refresh_after_classic_update();
		});

		jQuery(document).on('update_checkout', function () {
			maybe_console_log('update_checkout event (ignored)');
		});
		jQuery(document).on('wc_cart_emptied', function () {
			maybe_console_log('wc_cart_emptied event (ignored)');
		});
		jQuery(document).on('updated_wc_div', function () {
			maybe_console_log('updated_wc_div');
		});
		jQuery(document).on('updated_cart_totals', function () {
			maybe_console_log('updated_cart_totals event (ignored)');
		});
		jQuery(document).on('updated_shipping_method', function () {
			maybe_console_log('updated_shipping_method event (ignored)');
		});
		jQuery(document).on('applied_coupon', function () {
			maybe_console_log('applied_coupon event (ignored)');
		});
		jQuery(document).on('removed_coupon', function () {
			maybe_console_log('removed_coupon event (ignored)');
		});
		jQuery(document).on('wc_fragment_refresh', function () {
			maybe_console_log('wc_fragment_refresh event (ignored)');
		});
	});
	
	var first_message = true;
	
	function maybe_console_log( $message )
	{
		if( typeof wcfns_data !== 'undefined' && wcfns_data.verbose == 1 )
		{
			if( first_message )
			{
				console.log( '==============================================================================');
				console.log( ' This is the Advanced Shipping Rates for WooCommerce log for messages.');
				console.log( ' Useful to check/debug our new support for WC blocks cart & checkout.');
				console.log( ' This log will only be printed if you\'re logged in as admin or shop manager.');
				console.log( '==============================================================================');
				first_message = false;
			}
			console.log( $message );
		}
	}

})();