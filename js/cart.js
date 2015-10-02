jQuery( document ).ready( function( $ ) {

    function tc_check_cart_update() {
        var total_quantity = 0;

        $( '.quantity' ).each( function( index ) {
            total_quantity = parseInt( total_quantity ) + parseInt( $( this ).val() );
        } );

        if ( total_quantity != $( '.owner-info-wrap' ).length ) {

            $( '.tc_cart_errors' ).html( '<ul><li><a href="cjsea" class="cjsea"></a>' + tc_ajax.update_cart_message + '</li></ul>' );

            var $target = $( '.cjsea' );

            $( 'html, body' ).stop().animate( {
                'scrollTop': ( $target.offset().top ) - 40
            }, 350, 'swing', function() {
                window.location.hash = target;
            } );
            return false;
        } else {
            return true;
        }
    }

    $( 'input.tickera_button.plus' ).on( 'click', function() {
        var quantity = $( this ).parent().find( '.quantity' ).val();
        $( this ).parent().find( '.quantity' ).val( parseInt( quantity ) + 1 );
    } );

    $( 'input.tickera_button.minus' ).on( 'click', function() {
        var quantity = $( this ).parent().find( '.quantity' ).val();
        if ( quantity >= 1 ) {
            $( this ).parent().find( '.quantity' ).val( parseInt( quantity ) - 1 );
        }
    } );

    jQuery( '#update_cart' ).on( 'click', function() {
        jQuery( '#cart_action' ).val( 'update_cart' );//when user click on the update cart button
    } );

    jQuery( '#proceed_to_checkout' ).on( 'click', function() {
        jQuery( '#cart_action' ).val( 'proceed_to_checkout' );//when user click on the proceed to checkout button
    } );

    jQuery( '#apply_coupon' ).on( 'click', function() {
        jQuery( '#cart_action' ).val( 'apply_coupon' );//when user click on the proceed to checkout button
    } );


    jQuery( '.add_to_cart' ).on( 'click', function( event ) {
        event.preventDefault();
        var button_type = $( this ).attr( 'data-button-type' );

        $( this ).fadeTo( "fast", 0.1 );

        var current_form = $( this ).parents( 'form.cart_form' );
        var ticket_id = current_form.find( ".ticket_id" ).val();
        var qty = $( this ).closest( 'tr' ).find( '.tc_quantity_selector' ).val();

        //$( this ).closest( 'tr' ).find( '.tc_quantity_selector' ).attr( 'disabled', 'disabled' );

        $.post( tc_ajax.ajaxUrl, { action: "add_to_cart", ticket_id: ticket_id, tc_qty: qty }, function( data ) {
            if ( data != 'error' ) {
                current_form.html( data );

                if ( $( '.tc_cart_contents' ).length > 0 ) {
                    $.post( tc_ajax.ajaxUrl, { action: "update_cart_widget" }, function( widget_data ) {
                        //$('#tc_cart_widget').fadeTo("fast", 0.0);
                        $( '.tc_cart_contents' ).html( widget_data );
                        //$('#tc_cart_widget').fadeTo("fast", 1);
                    } );
                }

                if ( button_type == 'buynow' ) {
                    window.location = tc_ajax.cart_url;
                }

            } else {
                current_form.html( data );//Show error message
            }
            $( this ).fadeTo( "fast", 1 );

        } );



    } );

    //empty cart
    function tc_empty_cart() {
        if ( $( "a.tc_empty_cart" ).attr( "onClick" ) != undefined ) {
            return;
        }

        $( "a.tc_empty_cart" ).click( function() {
            var answer = confirm( tc_ajax.emptyCartMsg );
            if ( answer ) {
                $( this ).html( '<img src="' + tc_ajax.imgUrl + '" />' );
                $.post( tc_ajax.ajaxUrl, { action: 'mp-update-cart', empty_cart: 1 }, function( data ) {
                    $( "div.tc_cart_widget_content" ).html( data );
                } );
            }
            return false;
        } );
    }
    //add item to cart
    function tc_cart_listeners() {
        $( "input.tc_button_addcart" ).click( function() {
            var input = $( this );
            var formElm = $( input ).parents( 'form.tc_buy_form' );
            var tempHtml = formElm.html();
            var serializedForm = formElm.serialize();
            formElm.html( '<img src="' + tc_ajax.imgUrl + '" alt="' + tc_ajax.addingMsg + '" />' );
            $.post( tc_ajax.ajaxUrl, serializedForm, function( data ) {
                var result = data.split( '||', 2 );
                if ( result[0] == 'error' ) {
                    alert( result[1] );
                    formElm.html( tempHtml );
                    tc_cart_listeners();
                } else {
                    formElm.html( '<span class="tc_adding_to_cart">' + tc_ajax.successMsg + '</span>' );
                    $( "div.tc_cart_widget_content" ).html( result[1] );
                    if ( result[0] > 0 ) {
                        formElm.fadeOut( 2000, function() {
                            formElm.html( tempHtml ).fadeIn( 'fast' );
                            tc_cart_listeners();
                        } );
                    } else {
                        formElm.fadeOut( 2000, function() {
                            formElm.html( '<span class="tc_no_stock">' + tc_ajax.outMsg + '</span>' ).fadeIn( 'fast' );
                            tc_cart_listeners();
                        } );
                    }
                    tc_empty_cart(); //re-init empty script as the widget was reloaded
                }
            } );
            return false;
        } );
    }

    //add listeners
    tc_empty_cart();
    tc_cart_listeners();

    if ( tc_ajax.show_filters == 1 ) {
        tc_ajax_products_list();
    }

    /* Cart Widget */
    $( '.tc_widget_cart_button' ).on( 'click', function() {
        window.location.href = $( this ).data( 'url' );
    } );

    jQuery( '#proceed_to_checkout' ).on( 'click', function( event ) {
        jQuery( '#cart_action' ).val( 'proceed_to_checkout' );//when user click on the proceed to checkout button
        if ( tc_check_cart_update() ) {
            //all good, do not prevent the click
        } else {
            event.preventDefault();
        }
    } );

} );



﻿/* Payment Step */
    jQuery( document ).ready( function( $ ) {
    var gateways_count = $( '.tc_gateway_form' ).length;

    if ( gateways_count > 1 ) {
        $( 'div.tc_gateway_form' ).css( 'max-height', 'auto' );
    }
    //payment method choice
    $( '.tickera-payment-gateways input.tc_choose_gateway' ).change( function() {
        var gid = $( 'input.tc_choose_gateway:checked' ).val();

        $( 'div.tc_gateway_form' ).removeClass( 'tickera-height' );
        $( 'div#' + gid ).addClass( 'tickera-height' );
    } );


    jQuery( ".tc_choose_gateway" ).each( function() {

        jQuery( this ).change( function() {
            if ( this.checked ) {
                jQuery( '.payment-option-wrap' ).removeClass( 'active-gateway' );
                jQuery( this ).closest( '.payment-option-wrap' ).addClass( 'active-gateway' );
            } else {
                jQuery( this ).closest( '.payment-option-wrap' ).toggleClass( 'active-gateway' );
            }
        } );
    } )

    jQuery( '.buyer-field-checkbox, .owner-field-checkbox' ).change( function() {

        var checkbox_values_field = jQuery( this ).parent().parent().find( '.checkbox_values' );

        checkbox_values_field.val( '' );

        jQuery( this ).parent().parent().find( 'input' ).each( function( key, value ) {
            if ( jQuery( this ).attr( 'checked' ) ) {
                checkbox_values_field.val( checkbox_values_field.val() + '' + jQuery( this ).val( ) + ', ' );
            }
        } );
        checkbox_values_field.val( checkbox_values_field.val().substring( 0, checkbox_values_field.val().length - 2 ) );

    } );




} );

/* Validation */
jQuery( document ).ready( function( $ ) {

    $( '#tickera_cart' ).validate( {
        // your other plugin options
    } );

    $( '.tc_validate_field_type_email' ).each( function() {
        $( this ).rules( 'add', {
            email: true,
        } );
    } );
    
    $( '.tc_owner_email' ).each( function() {
        $( this ).rules( 'add', {
            email: true,
        } );
    } );

    $( '#tickera_cart .required' ).each( function() {
        $( this ).rules( 'add', {
            required: true,
        } );
    } );

} );