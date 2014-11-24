jQuery( document ).ready( function( $ ) {

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

        $( this ).fadeTo( "fast", 0.1 );

        var current_form = $( this ).parents( 'form.cart_form' );
        var ticket_id = current_form.find( ".ticket_id" ).val();

        $.post( tc_ajax.ajaxUrl, { action: "add_to_cart", ticket_id: ticket_id }, function( data ) {
            if ( data != 'error' ) {
                current_form.html( data );

                if ( $( '.tc_cart_contents' ).length > 0 ) {
                    $.post( tc_ajax.ajaxUrl, { action: "update_cart_widget" }, function( widget_data ) {
                        //$('#tc_cart_widget').fadeTo("fast", 0.0);
                        $( '.tc_cart_contents' ).html( widget_data );
                        //$('#tc_cart_widget').fadeTo("fast", 1);
                    } );
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

} );



﻿/* Payment Step */
    jQuery( document ).ready( function( $ ) {
    var gateways_count = $( '.tc_gateway_form' ).length;

    if ( gateways_count > 1 ) {
        $( 'div.tc_gateway_form' ).hide();
    }
    //payment method choice
    $( 'input.tc_choose_gateway' ).change( function() {
        var gid = $( 'input.tc_choose_gateway:checked' ).val();
        $( 'div.tc_gateway_form' ).hide();
        $( 'div#' + gid ).show();
    } );
    
    
    jQuery(".tc_choose_gateway").each(function(){
     
        jQuery(this).change(function() {
            if(this.checked) {
                jQuery('.payment-option-wrap').removeClass('active-gateway');
                jQuery(this).closest('.payment-option-wrap').addClass('active-gateway');
            } else {
                jQuery(this).closest('.payment-option-wrap').toggleClass('active-gateway');
            }
        });
    })
    
    
    

} );