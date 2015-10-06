( function( $ ) {
    $( document ).ready( function( $ ) {
        initialize_shortcode_builder();
        initialize_colorbox();

        var $tc_shortcodes_form = $( '#tc-shortcode-builder' );
        tc_show_hide_shortcodes( $tc_shortcodes_form.find( '[name="shortcode-select"]' ) );

    } );

    function tc_show_hide_shortcodes( objectval ) {
        if ( $( objectval ).length ) {
            var $table = $( '#' + $( objectval ).val().replace( /_/g, '-' ) + '-shortcode' );

            if ( $table.length == 0 ) {
                $tc_shortcodes_form.find( '.shortcode-table' ).hide();
                $.colorbox.resize();
                return;
            }

            $table.show().siblings( '.shortcode-table' ).hide();
        }

    } //function tc_show_hide_shortcodes()

    var initialize_shortcode_builder = function() {
        var $tc_shortcodes_form = $( '#tc-shortcode-builder' );



        $tc_shortcodes_form.find( '[name="shortcode-select"]' ).change( function() {
            tc_show_hide_shortcodes( this );
            tc_window_height();
        } );



        $tc_shortcodes_form.submit( function( e ) {
            e.preventDefault();

            var shortcode = '[' + $tc_shortcodes_form.find( '[name="shortcode-select"]' ).val();
            var atts = '';

            $tc_shortcodes_form.find( '.shortcode-table' ).filter( ':visible' ).find( 'input, select, textarea' ).filter( '[name]' ).each( function() {
                var $this = $( this );

                if ( $.trim( $this.val() ).length == 0 || ( $this.attr( 'data-default-value' ) !== undefined && $this.attr( 'data-default-value' ) == $.trim( $this.val() ) ) ) {
                    return;
                }

                if ( $this.is( ':radio' ) || $this.is( ':checkbox' ) ) {
                    if ( $this.is( ':checked' ) ) {
                        atts += ' ' + $this.attr( 'name' ) + '="' + $this.val() + '"';
                    }
                } else {
                    atts += ' ' + $this.attr( 'name' ) + '="' + $this.val() + '"';
                }
            } );

            shortcode += atts + ']';

            window.send_to_editor( shortcode );
            $.colorbox.close();
        } );
    };

    function tc_window_height() {
        var tc_get_height = jQuery( '.tc-shortcode-wrap' ).height();
        $.colorbox.resize( {
            "height": tc_get_height + 130 + "px"
        } );
    }
    
    function tc_window_width(){
        var tc_window_width = jQuery( window ).width();            

        if(tc_window_width < 950){
            jQuery("#tc-shortcode-builder").colorbox.resize({width:"90%" });
        } else {
            jQuery("#tc-shortcode-builder").colorbox.resize({width:"39%" });
        } //if(tc_window_width < 350)
    } //function tc_set_width()
    

    var initialize_colorbox = function() {
       
        $( 'body' ).on( 'click', '.tc-shortcode-builder-button', function() {

            setTimeout( function() {
                tc_window_height();
            }, 500 );

            var $this = $( this );

            $.colorbox( {
                "width": '39%',
                "maxWidth": "80%",
                "height": "70%",
                "inline": true,
                "href": "#tc-shortcode-builder",
                "opacity": 0.8,
                "className": 'tc-shortcodes-colorbox'
            } );
            
            tc_window_width();
        } );
        
        jQuery( window ).resize(function() {
            tc_window_width();
        });
    };




}( jQuery ) );
