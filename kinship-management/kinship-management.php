<?php
/*
Plugin Name: Kinship Management
Plugin URI: https://www.biplabmukherjee.com/
Description: A simple plugin for Kinship management.
Version: 1.0.0
Author: Biplab Mukherjee
Author URI: https://www.biplabmukherjee.com/
License: GPL2
*/

defined( 'ABSPATH' ) or die( '¡Sin trampas!' );

require plugin_dir_path( __FILE__ ) . 'includes/metabox-p1.php';

function kinship_custom_admin_styles() {
    wp_enqueue_style('custom-styles', plugins_url('/css/styles.css', __FILE__ ));
	}
add_action('admin_enqueue_scripts', 'kinship_custom_admin_styles');

global $kinship_db_version;
$kinship_db_version = '1.1.0'; 


function kinship_install()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'userstable'; 


    $sql = "CREATE TABLE " . $table_name . " (
      id int(11) NOT NULL AUTO_INCREMENT,
      name VARCHAR (50) NOT NULL,
      age VARCHAR (100) NOT NULL,
      realtion VARCHAR (100)  NULL,
      relation_details VARCHAR (100)  NULL,
      PRIMARY KEY  (id)
    );";


    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    add_option('kinship_db_version', $kinship_db_version);
    $installed_ver = get_option('kinship_db_version');
	
    if ($installed_ver != $kinship_db_version) {
        $sql = "CREATE TABLE " . $table_name . " (
          id int(11) NOT NULL AUTO_INCREMENT,
          name VARCHAR (50) NOT NULL,
          age VARCHAR (100) NOT NULL,
          realtion VARCHAR (100)  NULL,
          relation_details VARCHAR (100)  NULL,
          PRIMARY KEY  (id)
        );";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        update_option('kinship_db_version', $kinship_db_version);
    }
}

register_activation_hook(__FILE__, 'kinship_install');


function kinship_install_data()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'userstable'; 

}

register_activation_hook(__FILE__, 'kinship_install_data');


function kinship_update_db_check()
{
    global $kinship_db_version;
    if (get_site_option('kinship_db_version') != $kinship_db_version) {
        kinship_install();
    }
}

add_action('plugins_loaded', 'kinship_update_db_check');



if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}


class Custom_Table_Example_List_Table extends WP_List_Table
 { 
    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'contact',
            'plural'   => 'contacts',
        ));
    }


    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }


    function column_phone($item)
    {
        return '<em>' . $item['phone'] . '</em>';
    }


    function column_name($item)
    {

        $actions = array(
            'view' => sprintf('<a href="?page=kinship_form&id=%s">%s</a>', $item['id'], __('View', 'kinship')),
            'edit' => sprintf('<a href="?page=contacts_form&id=%s">%s</a>', $item['id'], __('Edit', 'kinship')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Delete', 'kinship')),
        );

        return sprintf('%s %s',
            $item['name'],
            $this->row_actions($actions)
        );
    }


    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }

    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', 
            'name'      => __('Name', 'kinship'),
            'age'  => __('Age', 'kinship'),
            'relation'     => __('Relation', 'kinship'),
            'relation_details'     => __('Relation With', 'kinship'),
        );
        return $columns;
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'name'      => array('name', true),
            'age'  => array('age', true),
            'relation'     => array('relation', true),
            'relation_details'     => array('relation_details', true),
            
        );
        return $sortable_columns;
    }

    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    function process_bulk_action()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'userstable'; 

        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
            }
        }
    }

    function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'userstable'; 

        $per_page = 10; 

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
       
        $this->process_bulk_action();

        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");


        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'name';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';


        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);


        $this->set_pagination_args(array(
            'total_items' => $total_items, 
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page) 
        ));
    }
}

function kinship_admin_menu()
{
    add_menu_page(__('Contacts', 'kinship'), __('Contacts', 'kinship'), 'activate_plugins', 'contacts', 'kinship_contacts_page_handler');
    add_submenu_page('contacts', __('Contacts', 'kinship'), __('Contacts', 'kinship'), 'activate_plugins', 'contacts', 'kinship_contacts_page_handler');
   
    add_submenu_page('contacts', __('Add new', 'kinship'), __('Add new', 'kinship'), 'activate_plugins', 'contacts_form', 'kinship_contacts_form_page_handler');
    add_submenu_page('contacts', __('Add new', 'kinship'), __('Add new', 'kinship'), 'activate_plugins', 'kinship_form', 'kinship_contacts_kinship_form_page_handler');
    remove_menu_page('kinship_form');
}

add_action('admin_menu', 'kinship_admin_menu');


function kinship_validate_contact($item)
{
    $messages = array();

    if (empty($item['name'])) $messages[] = __('Name is required', 'kinship');
    if (empty($item['age'])) $messages[] = __('age is required', 'kinship');
    
    

    if (empty($messages)) return true;
    return implode('<br />', $messages);
}


