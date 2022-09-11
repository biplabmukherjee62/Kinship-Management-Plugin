<?php
function kinship_contacts_page_handler()
{
    global $wpdb;

    $table = new Custom_Table_Example_List_Table();
    $table->prepare_items();

    $message = '';
    if ('delete' === $table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d', 'kinship'), count($_REQUEST['id'])) . '</p></div>';
    }
    ?>
<div class="wrap">

    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Contacts', 'kinship')?> <a class="add-new-h2"
                                 href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=contacts_form');?>"><?php _e('Add new', 'kinship')?></a>
    </h2>
    <?php echo $message; ?>

    <form id="contacts-table" method="POST">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <?php $table->display() ?>
    </form>

</div>
<?php
}


function kinship_contacts_form_page_handler()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'userstable'; 

    $message = '';
    $notice = '';


    $default = array(
        'id' => 0,
        'name'      => '',
        'age'  => '',
        'relation'     => null,
        'relation_details'     => null,
    );


    if ( isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
        
        $item = shortcode_atts($default, $_REQUEST);     

        $item_valid = kinship_validate_contact($item);
        if ($item_valid === true) {
            if ($item['id'] == 0) {
                $result = $wpdb->insert($table_name, $item);
                $item['id'] = $wpdb->insert_id;
                if ($result) {
                    $message = __('Item was successfully saved', 'kinship');
                } else {
                    $notice = __('There was an error while saving item', 'kinship');
                }
            } else {
                $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
                if ($result) {
                    $message = __('Item was successfully updated', 'kinship');
                } else {
                    $notice = __('There was an error while updating item', 'kinship');
                }
            }
        } else {
            
            $notice = $item_valid;
        }
    }
    else {
        
        $item = $default;
        if (isset($_REQUEST['id'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found', 'kinship');
            }
        }
    }

    
    add_meta_box('contacts_form_meta_box', __('Contact data', 'kinship'), 'kinship_contacts_form_meta_box_handler', 'contact', 'normal', 'default');

    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Contact', 'kinship')?> <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=contacts');?>"><?php _e('back to list', 'kinship')?></a>
    </h2>

    <?php if (!empty($notice)): ?>
    <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif;?>
    <?php if (!empty($message)): ?>
    <div id="message" class="updated"><p><?php echo $message ?></p></div>
    <?php endif;?>

    <form id="form" method="POST">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
        
        <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>

        <div class="metabox-holder" id="poststuff">
            <div id="post-body">
                <div id="post-body-content">
                    
                    <?php do_meta_boxes('contact', 'normal', $item); ?>
                    <input type="submit" value="<?php _e('Save', 'kinship')?>" id="submit" class="button-primary" name="submit">
                </div>
            </div>
        </div>
    </form>
</div>
<?php
}
function kinship_contacts_form_meta_box_handler($item)
{
    ?>
<tbody >
		
	<div class="formdatabc">		
		
    <form >
		<div class="form2bc">
        <p>			
		    <label for="name"><?php _e('Name:', 'kinship')?></label>
		<br>	
            <input id="name" name="name" type="text" value="<?php echo esc_attr($item['name'])?>" required>
		</p>
		<p>	
            <label for="age"><?php _e('Age:', 'kinship')?></label>
		<br>
		    <input id="age" name="age" type="number" value="<?php echo esc_attr($item['age'])?>" required>
        </p>
		</div>	
		
		</form>
		</div>
</tbody>
<?php
}
function kinship_contacts_kinship_form_page_handler()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'userstable'; 

    $message = '';
    $notice = '';


    $default = array(
        'id' => 0,
        'name'      => '',
        'age'  => '',
        'relation' => '',
        'relation_details' =>'' ,
    );


    if ( isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
        
        $item = shortcode_atts($default, $_REQUEST);     

        $item_valid = kinship_validate_contact($item);
        if ($item_valid === true) {

                $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
                if ($result) {
                    $message = __('Item was successfully updated', 'kinship');
                } else {
                    $notice = __('There was an error while updating item', 'kinship');
                }
            
        } else {
            
            $notice = $item_valid;
        }
    }
    else {
        
        $item = $default;
        if (isset($_REQUEST['id'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found', 'kinship');
            }
        }
    }

    
    add_meta_box('contacts_form_meta_box', __('Contact data', 'kinship'), 'kinship_contacts_kinship_form_meta_box_handler', 'contact', 'normal', 'default');

    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Contact', 'kinship')?> <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=contacts');?>"><?php _e('back to list', 'kinship')?></a>
    </h2>

    <?php if (!empty($notice)): ?>
    <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif;?>
    <?php if (!empty($message)): ?>
    <div id="message" class="updated"><p><?php echo $message ?></p></div>
    <?php endif;?>

    <form id="form" method="POST">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
        
        <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>
        <input type="hidden" name="name" value="<?php echo esc_attr($item['name'])?>"/>
        <input type="hidden" name="age" value="<?php echo esc_attr($item['age'])?>"/>
        <div class="metabox-holder" id="poststuff">
            <div id="post-body">
                <div id="post-body-content">
                    
                    <?php do_meta_boxes('contact', 'normal', $item); ?>
                    <input type="submit" value="<?php _e('Save', 'kinship')?>" id="submit" class="button-primary" name="submit">
                </div>
            </div>
        </div>
    </form>
</div>
<?php
}
function kinship_contacts_kinship_form_meta_box_handler($item)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'userstable'; 
         
  ?>     
    <h3>Person Details</h3>
    <div>
    <label>Name</lable>: <?php echo esc_attr($item['name'])?>
    </br>
    <label>Age</label>: <?php echo esc_attr($item['age'])?>
    </div>
<tbody >
		
	<div class="formdatabc">		
	<h4>Update Kinship Relation</h4>	
    <form >
		<div class="form2bc">
        <p>			
		    <label for="name"><?php _e('Relation list:', 'kinship')?></label>
		<br>	
            <!--<input id="relation" name="relation" type="text" value="" required>-->
            <select name="relation" id="relation" required>
              <option value="">Select option</option>  
              <option value="mother">Mother</option>
              <option value="sister">Sister</option>
              <option value="father">Father</option>
              <option value="brother">Brother</option>
            </select>
		</p>
		<p>	
            <label for="relation_with"><?php _e('Relation with:', 'kinship')?></label>
		<br>
		    <!--<input id="relation_details" name="relation_details" type="text" value="" required>-->
		    <select name="relation_details" id="relation_details" required>
              <option value="">Select option</option>
              <?php 
              $result = $wpdb->get_results ( "SELECT * FROM $table_name" );
                foreach ( $result as $print ) { ?>
              <option value="<?php echo $print->name;?>"><?php echo $print->name;?></option>
              <?php } ?>
            </select>
        </p>
		</div>	
		
		</form>
		</div>
</tbody>
<?php
}
