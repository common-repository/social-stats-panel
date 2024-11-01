<?php

class ListsStats extends WP_List_Table {

    var $data;

    function __construct($data) {
        $this->data = $data;
    }

    function get_columns() {
        $columns = array(
            'Post_id' => 'Post_id',
            'Link' => 'Link',
            'Stats' => 'Stats',
            'Twitter' => 'Twitter',
            'Facebook' => 'Facebook',
            'Google+' => 'Google+',
            'Date' => 'Date'
        );
        return $columns;
    }

    function prepare_items() {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        usort($this->data, array(&$this, 'usort_reorder'));

        $per_page = 50;
        $current_page = $this->get_pagenum();
        $total_items = count($this->data);

        // only ncessary because we have sample data
        $this->found_data = array_slice($this->data, ( ( $current_page - 1 ) * $per_page), $per_page);

        $this->set_pagination_args(array(
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page' => $per_page                     //WE have to determine how many items to show on a page
        ));
        $this->items = $this->found_data;
    }

    function usort_reorder($a, $b) {
        $orderby = (!empty($_GET['orderby']) ) ? $_GET['orderby'] : 'Post_id';
        $order = (!empty($_GET['order']) ) ? $_GET['order'] : 'desc';
        //$result = strcmp($a[$orderby], $b[$orderby]);
        $result = intval($a[$orderby]) - intval($b[$orderby]);
        return ( $order === 'asc' ) ? $result : -$result;
    }

    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'Post_id':
            case 'Link':
            case 'Stats':
            case 'Twitter':
            case "Facebook":
            case "Google+":
            case 'Date':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'Post_id' => array('Post_id', false),
            'Twitter' => array('Twitter', false),
            'Facebook' => array('Facebook', false),
            'Google+' => array('Google+', false),
        );
        return $sortable_columns;
    }

}

