<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('TC_Cart_Form')) {

    class TC_Cart_Form {

        var $form_title = '';
        var $valid_admin_fields_type = array('text', 'textarea', 'checkbox', 'function');
        var $ticket_type_id = '';

        function __construct($ticket_type_id = '') {
            $this->ticket_type_id = $ticket_type_id;
            $this->valid_admin_fields_type = apply_filters('tc_valid_admin_fields_type', $this->valid_admin_fields_type);
        }

        function TC_Cart_Form($ticket_type_id = '') {
            $this->__construct($ticket_type_id);
        }

        function get_buyer_info_fields() {

            $default_fields = array(
                array(
                    'field_name' => 'first_name',
                    'field_title' => __('First Name', 'tc'),
                    'field_type' => 'text',
                    'field_description' => '',
                    'post_field_type' => 'post_meta',
                    'required' => true
                ),
                array(
                    'field_name' => 'last_name',
                    'field_title' => __('Last Name', 'tc'),
                    'field_type' => 'text',
                    'field_description' => '',
                    'post_field_type' => 'post_meta',
                    'required' => true
                ),
                array(
                    'field_name' => 'email',
                    'field_title' => __('E-mail', 'tc'),
                    'field_type' => 'text',
                    'field_description' => '',
                    'post_field_type' => 'post_meta',
                    'required' => true
                ),
            );

            return apply_filters('tc_buyer_info_fields', $default_fields, isset($ticket_type_id) ? $ticket_type_id : '');
        }

        function get_owner_info_fields($ticket_type_id = '') {

            $default_fields = array(
                array(
                    'field_name' => 'ticket_type_id',
                    'field_title' => __('Ticket Type ID', 'tc'),
                    'field_type' => 'function',
                    'function' => 'tc_get_ticket_type_form_field',
                    'field_description' => '',
                    'post_field_type' => 'post_meta',
                    'required' => false
                ),
                array(
                    'field_name' => 'first_name',
                    'field_title' => __('First Name', 'tc'),
                    'field_type' => 'text',
                    'field_description' => '',
                    'post_field_type' => 'post_meta',
                    'required' => true
                ),
                array(
                    'field_name' => 'last_name',
                    'field_title' => __('Last Name', 'tc'),
                    'field_type' => 'text',
                    'field_description' => '',
                    'post_field_type' => 'post_meta',
                    'required' => true
                ),
                array(
                    'field_name' => 'owner_email',
                    'field_title' => __('E-Mail', 'tc'),
                    'field_type' => 'text',
                    'field_description' => '',
                    'post_field_type' => 'post_meta',
                    'required' => true
                ),
            );

            return apply_filters('tc_owner_info_fields', $default_fields, $ticket_type_id);
        }

        function get_columns() {
            $fields = $this->get_event_fields();
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
            $fields = $this->get_event_fields();
            $result = search_array($fields, 'field_name', $field_name);
            return $result[0]['post_field_type'];
        }

        function is_valid_event_field_type($field_type) {
            if (in_array($field_type, $this->valid_admin_fields_type)) {
                return true;
            } else {
                return false;
            }
        }

    }

}
?>
