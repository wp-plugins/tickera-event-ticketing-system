<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('TC_Ticket_Templates')) {

    class TC_Ticket_Templates {

        var $form_title = '';
        var $valid_admin_fields_type = array('text', 'textarea', 'checkbox', 'function');

        function __construct() {
            $this->valid_admin_fields_type = apply_filters('tc_valid_admin_fields_type', $this->valid_admin_fields_type);
        }

        function generate_preview($ticket_instance_id = false, $force_download = false, $template_id = false, $ticket_type_id = false) {
            global $tc, $pdf;
            error_reporting(0);

            require_once($tc->plugin_dir . 'includes/tcpdf/examples/tcpdf_include.php');

            //include_once( $tc->plugin_dir . 'includes/tcpdf/config/lang/eng.php' );
            //require_once( $tc->plugin_dir . 'includes/tcpdf/tcpdf.php' );
            $output_buffering = ini_get('output_buffering');
            if (isset($output_buffering) && $output_buffering > 0) {
                ob_end_clean();
                ob_start();
            }
            //use $template_id only if you preview the ticket

            /* if (ini_get('output_buffering') == 0) {
              echo 'Output buffering is turned off on this server and that\'s the reason why PDF tickets cannot be generated.<br />';
              echo 'You can turn on the output buffering by adding this line to your .htaccess file: <br /><br />
              <strong>php_flag output_buffering on</strong><br /><br />';
              echo 'In case that above line does not work, try adding this one:<br /><br />
              <strong>php_value output_buffering 1</strong><br /><br />';
              exit;
              } */

            //use $template_id only if you preview the ticket

            if ($ticket_instance_id) {
                $ticket_instance = new TC_Ticket($ticket_instance_id);
            }
            //require_once($tc->plugin_dir . 'includes/tcpdf/examples/tcpdf_include.php');

            if ($template_id) {
                $post_id = $template_id;
            } else {
                $post_id = get_post_meta($ticket_instance->details->ticket_type_id, 'ticket_template', true);
            }

            if ($post_id) {//post id = template id
                $metas = tc_get_post_meta_all($post_id);
            }

            $margin_left = $metas['document_ticket_left_margin'];
            $margin_top = $metas['document_ticket_top_margin'];
            $margin_right = $metas['document_ticket_right_margin'];
            // create new PDF document

            $pdf = new TCPDF($metas['document_ticket_orientation'], PDF_UNIT, apply_filters('tc_additional_ticket_document_size_output', $metas['document_ticket_size']), true, apply_filters('tc_ticket_document_encoding', get_bloginfo('charset')), false);
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetFont($metas['document_font'], '', 14);
            // set margins
            $pdf->SetMargins($margin_left, $margin_top, $margin_right);
            // set auto page breaks
            $pdf->SetAutoPageBreak(false, PDF_MARGIN_BOTTOM);
            // set font
            //$pdf->SetFont($metas->document_font_post_meta, '', 20);
            $pdf->AddPage();
            //error_reporting(0); //Don't show errors in the PDF 

            if (isset($metas['document_ticket_background_image']) && $metas['document_ticket_background_image'] !== '') {
                $pdf->Image($metas['document_ticket_background_image'], 0, 0, '', '', '', '', '', false, 300, '', false, false, 0);
            }

            $col_1 = 'width: 100%;';
            $col_1_width = '100%';
            $col_2 = 'width: 49.2%; margin-right: 1%;';
            $col_2_width = '49.2%';
            $col_3 = 'width: 32.5%; margin-right: 1%;';
            $col_3_width = '32.5%';
            $col_4 = 'width: 24%; margin-right: 1%;';
            $col_5 = 'width: 19%; margin-right: 1%;';
            $col_6 = 'width: 15.66%; margin-right: 1%;';
            $col_7 = 'width: 13.25%; margin-right: 1%;';
            $col_8 = 'width: 11.43%; margin-right: 1%;';
            $col_9 = 'width: 10%; margin-right: 1%;';
            $col_10 = 'width: 8.94%; margin-right: 1%;';

            $rows = '<table>';

            for ($i = 1; $i <= apply_filters('tc_ticket_template_row_number', 10); $i++) {

                $rows .= '<tr>';
                $rows_elements = get_post_meta($post_id, 'rows_' . $i, true);

                if (isset($rows_elements) && $rows_elements !== '') {

                    $element_class_names = explode(',', $rows_elements);
                    $rows_count = count($element_class_names);

                    foreach ($element_class_names as $element_class_name) {

                        if (class_exists($element_class_name)) {

                            if (isset($post_id)) {
                                $rows .= '<td ' . (isset($metas[$element_class_name . '_cell_alignment']) ? 'align="' . $metas[$element_class_name . '_cell_alignment'] . '"' : 'align="left"') . ' style="' . ${"col_" . $rows_count} . (isset($metas[$element_class_name . '_cell_alignment']) ? 'text-align:' . $metas[$element_class_name . '_cell_alignment'] . ';' : '') . (isset($metas[$element_class_name . '_font_size']) ? 'font-size:' . $metas[$element_class_name . '_font_size'] . ';' : '') . (isset($metas[$element_class_name . '_font_color']) ? 'color:' . $metas[$element_class_name . '_font_color'] . ';' : '') . '">';

                                for ($s = 1; $s <= ($metas[$element_class_name . '_top_padding']); $s++) {
                                    $rows .= '<br />';
                                }

                                $element = new $element_class_name($post_id);
                                $rows .= $element->ticket_content($ticket_instance_id, $ticket_type_id);

                                for ($s = 1; $s <= ($metas[$element_class_name . '_bottom_padding']); $s++) {
                                    $rows .= '<br />';
                                }

                                $rows .= '</td>';
                            }
                        }
                    }
                }
                $rows .= '</tr>';
            }
            $rows .= '</table>';

            $page1 = preg_replace("/\s\s+/", '', $rows); //Strip excess whitespace 
            if (isset($output_buffering) && $output_buffering > 0) {
                ob_get_clean();
            }
            $pdf->writeHTML($page1, true, 0, true, 0); //Write page 1 
            //$pdf->lastPage();
            $pdf->Output((isset($ticket_instance->details->ticket_code) ? $ticket_instance->details->ticket_code : __('preview', 'tc')) . '.pdf', ($force_download ? 'D' : 'I'));
            exit;
        }

        function TC_Cart_Form() {
            $this->__construct();
        }

        function add_new_template() {
            global $wpdb;

            if (isset($_POST['template_title'])) {

                $post = array(
                    'post_content' => '',
                    'post_status' => 'publish',
                    'post_title' => $_POST['template_title'],
                    'post_type' => 'tc_templates',
                );

                $post = apply_filters('tc_template_post', $post);

                if (isset($_POST['template_id'])) {
                    $post['ID'] = $_POST['template_id']; //If ID is set, wp_insert_post will do the UPDATE instead of insert
                }

                $post_id = wp_insert_post($post);

                //Update post meta
                if ($post_id != 0) {
                    foreach ($_POST as $key => $value) {
                        if (preg_match("/_post_meta/i", $key)) {//every field name with sufix "_post_meta" will be saved as post meta automatically
                            update_post_meta($post_id, str_replace('_post_meta', '', $key), $value);
                            do_action('tc_template_post_metas');
                        }
                    }
                }

                return $post_id;
            }
        }

        function get_template_col_fields() {

            $default_fields = array(
                array(
                    'field_name' => 'post_title',
                    'field_title' => __('Template Name', 'tc'),
                    'field_type' => 'text',
                    'field_description' => '',
                    'post_field_type' => 'post_title',
                    'table_visibility' => true,
                ),
                array(
                    'field_name' => 'post_date',
                    'field_title' => __('Date', 'tc'),
                    'field_type' => 'text',
                    'field_description' => '',
                    'post_field_type' => 'post_date',
                    'table_visibility' => true,
                ),
            );

            return apply_filters('tc_template_col_fields', $default_fields);
        }

        function get_columns() {
            $fields = $this->get_template_col_fields();
            $results = search_array($fields, 'table_visibility', true);

            $columns = array();

            $columns['ID'] = __('ID', 'tc');

            foreach ($results as $result) {
                $columns[$result['field_name']] = $result['field_title'];
            }

            $columns['edit'] = __('Edit', 'tc');
            $columns['delete'] = __('Delete', 'tc');

            return $columns;
        }

        function check_field_property($field_name, $property) {
            $fields = $this->get_template_col_fields();
            $result = search_array($fields, 'field_name', $field_name);
            return $result[0]['post_field_type

                 '];
        }

        function is_valid_template_col_field_type($field_type) {
            if (in_array($field_type, $this->valid_admin_fields_type)) {
                return true;
            } else {
                return false;
            }
        }

    }

}
?>