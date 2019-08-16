<?php
/**
 * Plugin Name: Color Categories
 * Plugin URI: http://www.glezer.co.il
 * Description: Color bars for each category
 * Version: 1.0
 * Author: Yaacov Glezer
 * Author URI: http://www.glezer.co.il
 * Text Domain: color-cat
 * Domain Path: /
 * License: GPL2+
 *
 * @package color-cat
 */
 
// add wp color picker script and style

add_action( 'admin_enqueue_scripts', 'gy_add_color_picker' );
function gy_add_color_picker( $hook ) {
 
    if( is_admin() ) { 
    	// Add the color picker css file       
    	wp_enqueue_style( 'wp-color-picker' ); 
         
        // Include our custom jQuery file with WordPress Color Picker dependency
        wp_enqueue_script( 'custom-script-handle', plugins_url( 'color-picker.js', __FILE__ ), array( 'wp-color-picker' ), false, true ); 
    }
}


//add extra fields to category edit form hook
add_action ( 'edit_category_form_fields', 'extra_category_fields');

//add extra fields to category edit form callback function
function extra_category_fields( $tag ) {    //check for existing featured ID
    $t_id = $tag->term_id;
    $cat_meta = get_option( "category_$t_id");
?>
<tr class="form-field">
<th scope="row" valign="top"><label for="cat_Image_url"><?php _e('Category Image Url'); ?></label></th>
<td>
<input type="text" name="Cat_meta[img]" id="Cat_meta[img]" size="3" style="width:60%;" value="<?php echo $cat_meta['img'] ? $cat_meta['img'] : ''; ?>"><br />
            <span class="description"><?php _e('Image for category: use full url with '); ?></span>
        </td>
</tr>
<tr class="form-field">
<th scope="row" valign="top"><label for="bgcolor"><?php _e('background color'); ?></label></th>
	<td>
		<input type="text" class="color-field" name="Cat_meta[bgcolor]" id="Cat_meta[bgcolor]" value="<?php echo $cat_meta['bgcolor'] ? $cat_meta['bgcolor'] : ''; ?>"><br />
	</td>
</tr>
<tr class="form-field">
<th scope="row" valign="top"><label for="textcolor"><?php _e('text color'); ?></label></th>
	<td>
		<input type="text" class="color-field" name="Cat_meta[textcolor]" id="Cat_meta[textcolor]" value="<?php echo $cat_meta['textcolor'] ? $cat_meta['textcolor'] : ''; ?>" />
	</td>
</tr>
<?php
}

// save extra category extra fields hook
add_action ( 'edited_category', 'save_extra_category_fileds');
add_action ( 'create_category', 'save_extra_category_fileds');
// save extra category extra fields callback function
function save_extra_category_fileds( $term_id ) {
    if ( isset( $_POST['Cat_meta'] ) ) {
        $t_id = $term_id;
        $cat_meta = get_option( "category_$t_id");
        $cat_keys = array_keys($_POST['Cat_meta']);
            foreach ($cat_keys as $key){
            if (isset($_POST['Cat_meta'][$key])){
                $cat_meta[$key] = $_POST['Cat_meta'][$key];
            }
        }
        //save the option array
        update_option( "category_$t_id", $cat_meta );
    }
}


function gy_get_category_color($term_id,$field) {
	$term_meta =  get_option('category_'.$term_id);
	$cat_color = $term_meta[$field];
	return $cat_color;
}


function gycc_add_category_columns($columns)
{
 // add 'My Column'
	$columns['cat_color'] = 'צבע רקע';
	$columns['cat_text_color'] = 'צבע גופן';

 return $columns;
}
add_filter('manage_edit-category_columns','gycc_add_category_columns');

function gycc_manage_category_custom_fields($deprecated,$column_name,$term_id) {
	$cat_color = gy_get_category_color($term_id,'bgcolor');
	$cat_text_color = gy_get_category_color($term_id,'textcolor');
	if ($column_name == 'cat_color') {
		echo '<div style="width:20%;background-color:',$cat_color,'; color:',$cat_text_color,';">&nbsp;</div>';
	}
		if ($column_name == 'cat_text_color') {
		echo '<div style="width:20%;background-color:',$cat_text_color,'; color:',$cat_color,';">&nbsp;</div>';
	}
}
add_filter ('manage_category_custom_column', 'gycc_manage_category_custom_fields', 10,3);



function gycc_add_styles() {
?>
<style id="color-categories">
.grid-container .grid__item {
	border-bottom-style:solid;
	border-bottom-width:8px;
}
<?php
	$my_post_id = get_the_id();
	$categories = get_categories();
//	var_dump($categories);
	foreach ($categories as $category) {
		$my_cat_id =  $category->cat_ID;
		$cat_slug = 'category_'.$my_cat_id;
		$cat_color = gy_get_category_color($my_cat_id,'bgcolor');//get_field('cat_color',$cat_slug);
		$cat_text_color = gy_get_category_color($my_cat_id,'textcolor');//get_field('cat_color',$cat_slug);
		$cat_name = 'category-'.$my_cat_id;
		$cat_class = sanitize_html_class( $category->slug, $category->term_id );
		if ( is_numeric( $cat_class ) || ! trim( $cat_class, '-' ) ) {
			$cat_class = $category->term_id;
		}
//		echo '.category-'.$cat_class.'{ color:green;}';
?>
		<?php echo '.grid-container .category-'.$cat_class.' .grid__item'; ?> {
			border-bottom-color:<?php echo $cat_color; ?>;
		}
		li.cat-item-<?php echo $my_cat_id?> a{
			background-color:<?php echo $cat_color;?>75;
			color:<?php echo $cat_text_color;?>;
		}
<?php

	}
?> </style> <?php
}
add_action('wp_head','gycc_add_styles',5);


add_action('__before_regular_heading_title','gycc_add_color3');
function gycc_add_color3() {
	$my_post_id = get_the_id();
	$categories = get_the_category();
	if ( ! empty( $categories ) ) {
		$my_cat_id =  $categories[0]->cat_ID;
		$my_cat_name =  $categories[0]->slug;
	}
	$cat_slug = 'category_'.$my_cat_id;
	$cat_color = gy_get_category_color($my_cat_id,'bgcolor');//get_field('cat_color',$cat_slug);
?>
<div class="gy3-test" style="direction:ltr;background-color:<?php
echo $cat_color;
echo '">';
//echo sprintf('post:%1s, cat id:%2s, cat name:%3s, color:%4s <br>',$my_post_id,$my_cat_id,$my_cat_name,$cat_color);
echo "&nbsp;";
?></div><br>
<?php

}

?>
