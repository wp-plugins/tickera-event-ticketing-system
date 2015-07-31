jQuery( document ).ready( function( $ ) {


    /* Toggle Controls */
    //tickera_page_tc_ticket_types
    var tc_event_id = 0;
    var tc_ticket_id = 0;
    var tc_event_status = 'publish';
    var tc_ticket_status = 'publish';

    var tc_toggle = {
        init: function() {
            $( 'body' ).addClass( 'tctgl' );
            this.attachHandlers( '.tctgl .tc-control' );
        },
        tc_controls: {
            $tc_toggle_init: function( selector )
            {
                $( selector ).click( function()
                {
                    tc_event_id = $( this ).attr( 'event_id' );
                    tc_ticket_id = $( this ).attr( 'ticket_id' );

                    if ( $( this ).hasClass( 'tc-on' ) ) {
                        $( this ).removeClass( 'tc-on' );
                        tc_event_status = 'private';
                        tc_ticket_status = 'private';
                    } else {
                        $( this ).addClass( 'tc-on' );
                        tc_event_status = 'publish';
                        tc_ticket_status = 'publish';
                    }

                    var attr = $( this ).attr( 'event_id' );
                    if ( typeof attr !== typeof undefined && attr !== false ) {//Event toggle
                        $.post(
                            tc_vars.ajaxUrl, {
                                action: 'change_event_status',
                                event_status: tc_event_status,
                                event_id: tc_event_id,
                            }
                        );
                    } else {
                        $.post(
                            tc_vars.ajaxUrl, {
                                action: 'change_ticket_status',
                                ticket_status: tc_ticket_status,
                                ticket_id: tc_ticket_id,
                            }
                        );
                    }


                } );

            }
        },
        attachHandlers: function( selector ) {
            this.tc_controls.$tc_toggle_init( selector );
        }
    };

    tc_toggle.init();


    $( "input.tc_active_gateways" ).change( function() {
        //alert($(this).val());
        var currently_selected_gateway_name = $( this ).val();
        var checked = $( this ).attr( 'checked' );
        if ( checked == 'checked' ) {
            $( '#' + currently_selected_gateway_name ).show( 200 );
        } else {
            $( '#' + currently_selected_gateway_name ).hide( 200 );
        }
    } );


    if ( tc_vars.animated_transitions ) {
        $( ".tc_wrap" ).fadeTo( 250, 1 );
        $( ".tc_wrap #message" ).delay( 2000 ).slideUp( 250 );
    } else {
        $( ".tc_wrap" ).fadeTo( 0, 1 );
    }


    $( '.tc_delete_link' ).click( function( event )
    {
        tc_delete( event );
    } );

    function tc_delete_confirmed() {
        return confirm( tc_vars.delete_confirmation_message );
    }

    function tc_delete( event ) {
        if ( tc_delete_confirmed() ) {
            return true;
        } else {
            event.preventDefault()
            return false;
        }
    }


    $( '.file_url_button' ).click( function()
    {
        var target_url_field = $( this ).prevAll( ".file_url:first" );
        wp.media.editor.send.attachment = function( props, attachment )
        {
            $( target_url_field ).val( attachment.url );
        };
        wp.media.editor.open( this );
        return false;
    } );


    /* Ticket Tempaltes */

    var ticket_classes = new Array();
    var parent_id = 0;

    $( '.tc-color-picker' ).wpColorPicker();
    $( "ul.sortables" ).sortable( {
        connectWith: 'ul',
        forcePlaceholderSize: true,
        //placeholder: "ui-state-highlight",
        receive: function( template, ui ) {
            update_li();
            $( ".rows ul li" ).last().addClass( "last_child" );
        },
        stop: function( template, ui ) {
            update_li();
        }
    } )/*.disableSelection()*/;

    //$( ".sortables" ).disableSelection();

    function update_li() {

        var children_num = 0;
        var current_child_num = 0;

        $( ".rows ul" ).each( function() {

            ticket_classes.length = 0; //empty the array

            children_num = $( this ).children( 'li' ).length;
            $( this ).children( 'li' ).removeClass();
            $( this ).children( 'li' ).addClass( "ui-state-default" );
            $( this ).children( 'li' ).addClass( "cols cols_" + children_num );
            $( this ).children( 'li' ).last().addClass( "last_child" );
            $( this ).find( 'li' ).each( function( index, element ) {
                if ( $.inArray( $( this ).attr( 'data-class' ), ticket_classes ) == -1 ) {
                    ticket_classes.push( $( this ).attr( 'data-class' ) );
                }
            } );
            $( this ).find( '.rows_classes' ).val( ticket_classes.join() );
        } );
        tc_fix_template_elements_sizes();

        $( ".rows ul li" ).last().addClass( "last_child" );
        $( ".tc_wrap select" ).css( 'width', '25em' );
        $( ".tc_wrap select" ).css( 'display', 'block' );
        $( ".tc_wrap select" ).chosen( { disable_search_threshold: 5 } );
        $( ".tc_wrap select" ).css( 'display', 'none' );
        $( ".tc_wrap .chosen-container" ).css( 'width', '100%' );
        $( ".tc_wrap .chosen-container" ).css( 'max-width', '25em' );
        $( ".tc_wrap .chosen-container" ).css( 'min-width', '1em' );
    }

    function tc_fix_template_elements_sizes() {
        $( ".rows ul" ).each( function() {
            var maxHeight = -1;

            $( this ).find( 'li' ).each( function() {
                $( this ).removeAttr( "style" );
                maxHeight = maxHeight > $( this ).height() ? maxHeight : $( this ).height();
            } );

            $( this ).find( 'li' ).each( function() {
                $( this ).height( maxHeight );
            } );
        } );
    }


    if ( $( '#ticket_elements' ).length ) {
        update_li();
        tc_fix_template_elements_sizes();
    }

    function fix_chosen() {
        $( ".tc_wrap select" ).css( 'width', '25em' );
        $( ".tc_wrap select" ).css( 'display', 'block' );
        $( ".tc_wrap select" ).chosen( { disable_search_threshold: 5 } );
        $( ".tc_wrap select" ).css( 'display', 'none' );
        $( ".tc_wrap .chosen-container" ).css( 'width', '100%' );
        $( ".tc_wrap .chosen-container" ).css( 'max-width', '25em' );
        $( ".tc_wrap .chosen-container" ).css( 'min-width', '1em' );
    }

    $( '.order_status_change' ).on( 'change', function() {
        var new_status = $( this ).val();
        var order_id = $( '#order_id' ).val();

        $.post( tc_vars.ajaxUrl, { action: "change_order_status", order_id: order_id, new_status: new_status }, function( data ) {
            if ( data != 'error' ) {
                $( '.tc_wrap .message_placeholder' ).html( '' );
                $( '.tc_wrap .message_placeholder' ).append( '<div id="message" class="updated fade"><p>' + tc_vars.order_status_changed_message + '</p></div>' );
                $( ".tc_wrap .message_placeholder" ).show( 250 );
                $( ".tc_wrap .message_placeholder" ).delay( 2000 ).slideUp( 250 );
            } else {
                //current_form.html(data);//Show error message
            }
            $( this ).fadeTo( "fast", 1 );
        } );
    } );



    /* PAYMENT GATEWAY IMAGE SWITCH */

    jQuery( ".tc_active_gateways" ).each( function() {

        if ( this.checked ) {
            jQuery( this ).closest( '.image-check-wrap' ).toggleClass( 'active-gateway' );
        }

        jQuery( this ).change( function() {
            fix_chosen();

            if ( this.checked ) {
                jQuery( this ).closest( '.image-check-wrap' ).toggleClass( 'active-gateway' );
            } else {
                jQuery( this ).closest( '.image-check-wrap' ).toggleClass( 'active-gateway' );
            }
        } );
    } )

    if ( jQuery( '#tickets_limit_type' ).val() == 'event_level' ) {
        jQuery( '#event_ticket_limit' ).parent().parent().show();
    } else {
        jQuery( '#event_ticket_limit' ).parent().parent().hide();
    }

    jQuery( '#tickets_limit_type' ).on( 'change', function() {
        if ( jQuery( '#tickets_limit_type' ).val() == 'event_level' ) {
            jQuery( '#event_ticket_limit' ).parent().parent().show();
        } else {
            jQuery( '#event_ticket_limit' ).parent().parent().hide();
        }
    } );


    $( ".tc_wrap select" ).chosen( { disable_search_threshold: 5 } );
} );

