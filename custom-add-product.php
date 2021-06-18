<?php
/*
Plugin Name: Custom Product Add
Plugin URI: https://www.mrlazyfox.com/
Description: Simple Way to Add variable and Simple products :). Hope it will make your work easily thanks :)
Author: Arabinda
Version: 1.7.0
Author URI: http://mrlazyfox.com
Requires at least: 4.5
Tested up to: 5.4
*/

if (!defined('ABSPATH'))
{
    die;
}

if (!defined('WPINC'))
{
    die;
}



	
//custom column product code
// add_filter( 'manage_edit-product_columns', 'arabinda_product_code_column', 20 );
// function arabinda_product_code_column( $columns_array ) {
// 	// I want to display Brand column just after the product name column
// // 	return array_slice( $columns_array, 0, 3, true )
// // 	+ array( 'product_code' => 'Code' )
// // 	+ array_slice( $columns_array, 3, NULL, true );
// //  return $columns_array;
// }


//css
// add_action('admin_head', 'arabinda_my_custom_fonts');
// function arabinda_my_custom_fonts() {
//   echo '<style>
//     table.wp-list-table .column-product_code {
//     width: 22%!important;
//     text-align:center;
// }
//   </style>';
// }
// add_action('manage_product_posts_custom_column','arabinda_product_code_value');
// function arabinda_product_code_value($column){
//     if($column == 'product_code'){
//         $code= get_post_meta(get_the_ID(),'_product_code',true);
//         echo $code ? $code : '	–';
//     }
// }

function easy_add_menu_page() {
    add_menu_page(
        __( 'Easy Product Add' ),
        'Easy Product Add',
        'manage_options',
        'easy-add',
        'easy_add_call',
        'dashicons-products',
        6
    );
}
add_action( 'admin_menu', 'easy_add_menu_page' );

function easy_add_call(){
    $old_is_gold= 'new';
     $load = include (__DIR__ . '/templates/template-add-product.php');
        // if ($load)
        // {
        //     exit(); // just exit if template was found and loaded
            
        // }
}

//cat
if (!function_exists('product_taxanomy_dropdown'))
{
    function product_taxanomy_dropdown($taxanomy, $post_ID = 0, $args = array())
    {
        $default = array(
            'classes' => '',
            'name' =>  $taxanomy,
            'multiple' => true,
        );

        $args = array_merge($default, $args);

        $multiple_select = '';

        if (array_get('multiple', $args))
        {
            if (isset($args['name']))
            {
                $args['name'] = $args['name'] . '[]';
            }
            $multiple_select = "multiple='multiple'";
        }

        extract($args);

        $classes = (array)$classes;
        $classes = implode(' ', $classes);

        $categories = get_product_taxanomy($taxanomy);

        $text  = $taxanomy == 'product_tag' ? 'tags':'categories';
        $text = 'Select '.$text;
        $output = '';
        $output .= "<select name='{$name}' style='width:50%' data-sortable='true' {$multiple_select} class='{$classes}' data-placeholder='" . __($text, 'tutor') . "'>";
        $output .= "<option value=''>" . __($text, 'tutor') . "</option>";
        $output .= _generate_taxanomy_dropdown_option($taxanomy, $post_ID, $categories, $args);
        $output .= "</select>";

        return $output;
    }
}

if (!function_exists('_generate_taxanomy_dropdown_option'))
{
    function _generate_taxanomy_dropdown_option($taxanomy, $post_ID = 0, $categories, $args = array() , $depth = 0)
    {
        $output = '';

        if (count($categories))
        {
            foreach ($categories as $category_id => $category)
            {
                if (!$category->parent)
                {
                    $depth = 0;
                }

                $childrens = array_get('children', $category);
                $has_in_term = has_term($category->term_id, $taxanomy, $post_ID);

                $depth_seperator = '';
                if ($depth)
                {
                    for ($depth_i = 0;$depth_i < $depth;$depth_i++)
                    {
                        $depth_seperator .= '-';
                    }
                }

                $output .= "<option value='{$category->term_id}' " . selected($has_in_term, true, false) . " >   {$depth_seperator} {$category->name}</option> ";

                if (count($childrens))
                {
                    $depth++;
                    $output .= _generate_taxanomy_dropdown_option($taxanomy, $post_ID, $childrens, $args, $depth);
                }
            }
        }
        return $output;
    }
}
function get_product_taxanomy($taxanomy, $parent = 0)
{
    $args = array(
        'taxonomy' => $taxanomy,
        'hide_empty' => false,
        'parent' => $parent,
    );

    $terms = get_terms($args);

    $children = array();
    foreach ($terms as $term)
    {
        $term->children = get_product_taxanomy($taxanomy, $term->term_id);
        $children[$term->term_id] = $term;
    }

    return $children;
}

//attr
//get all attr
function getAllAttributes()
{
    global $wc_product_attributes;

    // Array of defined attribute taxonomies.
    $attribute_taxonomies = wc_get_attribute_taxonomies();

    if (!empty($attribute_taxonomies))
    {
        $i = 0;
        foreach ($attribute_taxonomies as $tax)
        {
            $attribute_taxonomy_name = wc_attribute_taxonomy_name($tax->attribute_name);
            getAttributeByName($attribute_taxonomy_name, $i);
            $i++;
        }
    }
}
// attr by name
function getAttributeByName($attribute_taxonomy_name, $i = 0)
{

    $i = absint($i);
    $metabox_class = array();

    $attribute = new WC_Product_Attribute();

    $attribute->set_id(wc_attribute_taxonomy_id_by_name(sanitize_text_field(wp_unslash($attribute_taxonomy_name))));
    $attribute->set_name(sanitize_text_field(wp_unslash($attribute_taxonomy_name)));
    $attribute->set_visible(apply_filters('woocommerce_attribute_default_visibility', 1));
    $attribute->set_variation(apply_filters('woocommerce_attribute_default_is_variation', 1));

    if ($attribute->is_taxonomy())
    {
        $metabox_class[] = 'taxonomy';
        $metabox_class[] = $attribute->get_name();
    }

    include __DIR__ . '/templates/product-attribute-row.php';

}


function media_scripts(){
    wp_enqueue_media( );
}
add_action('admin_enqueue_scripts','media_scripts');
//save attributes
function save_attributes()
{

    if (!isset($_POST['data'], $_POST['post_id']))
    {
        wp_die(-1);
    }
    $response = array();

    try
    {
        parse_str(wp_unslash($_POST['data']) , $data); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $attributes = WC_Meta_Box_Product_Data::prepare_attributes($data);
        $product_id = absint(wp_unslash($_POST['post_id']));
        $product_type = !empty($_POST['product_type']) ? wc_clean(wp_unslash($_POST['product_type'])) : 'simple';
        $classname = WC_Product_Factory::get_product_classname($product_id, $product_type);
        $product = new $classname($product_id);

        $product->set_attributes($attributes);
        $product->save();

        ob_start();
        $attributes = $product->get_attributes('edit');
        $i = - 1;
        if (!empty($data['attribute_names']))
        {
            foreach ($data['attribute_names'] as $attribute_name)
            {
                $attribute = isset($attributes[sanitize_title($attribute_name) ]) ? $attributes[sanitize_title($attribute_name) ] : false;
                $i++;
                if (!$attribute)
                {
                    getAttributeByName($attribute_name, $i);
                    continue;
                }
                $metabox_class = array();

                if ($attribute->is_taxonomy())
                {
                    $metabox_class[] = 'taxonomy';
                    $metabox_class[] = $attribute->get_name();
                }

                include __DIR__ . '/templates/product-attribute-row.php';

            }
            
        }

        // global $wc_product_attributes;

        // // Array of defined attribute taxonomies.
        // $attribute_taxonomies = wc_get_attribute_taxonomies();

        // if (!empty($attribute_taxonomies))
        // {
        //     foreach ($attribute_taxonomies as $tax)
        //     {
        //         $attribute_taxonomy_name = wc_attribute_taxonomy_name($tax->attribute_name);
        //         if (!array_key_exists($attribute_taxonomy_name, $attributes))
        //         {
        //             $i++;
        //             getAttributeByName($attribute_taxonomy_name, $i);
        //         }
                
        //     }
        // }

        $response['trigger'] = $_POST['product_type'] == 'variable' ? true : false;

        $response['html'] = ob_get_clean();

        if ($_POST['product_type'] == 'variable')
        {
            $response['variations_count'] = link_all_variations($_POST['post_id']);
        }
    }
    catch(Exception $e)
    {
        wp_send_json_error(array(
            'error' => $e->getMessage()
        ));
    }

    // wp_send_json_success must be outside the try block not to break phpunit tests.
    wp_send_json_success($response);
}

add_action('wp_ajax_nopriv_arabinda_save_attributes', 'save_attributes');
add_action('wp_ajax_arabinda_save_attributes', 'save_attributes');



function get_attachment(){
    if(!isset($_POST['thumbnail_id'])){
        wp_die('-1');
    }
    $response['html'] =  wp_get_attachment_image( $_POST['thumbnail_id'], 'thumbnail' );
    wp_send_json_success($response);

}

add_action('wp_ajax_nopriv_arabinda_get_attachment', 'get_attachment');
add_action('wp_ajax_arabinda_get_attachment', 'get_attachment');

//styles


//generate random code
function generate_random_code($length_of_string, $id)
{

    $id_length = strlen($id);
    if ($id_length > $length_of_string)
    {
        $length_of_string = $id_length - $length_of_string;
    }
    else
    {
        $length_of_string = $length_of_string - $id_length;
    }

    $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ' . $id;

    return substr(str_shuffle($str_result) . str_shuffle($id) , 0, $length_of_string);
}

//link all variations
function link_all_variations($post_id)
{

    wc_set_time_limit(0);

    $product = wc_get_product($post_id);
    $data_store = $product->get_data_store();

    //         $variations     = wc_get_products(
    // 			array(
    // 				'status'  => array( 'private', 'publish' ),
    // 				'type'    => 'variation',
    // 				'parent'  => $post_id,
    // 				'orderby' => array(
    // 					'menu_order' => 'ASC',
    // 					'ID'         => 'DESC',
    // 				),
    // 				'return'  => 'objects',
    // 			)
    // 		);
    //         if($variations){
    //             foreach($variations as $variation){
    //                 $variation = wc_get_product( $variation->get_id() );
    // 				$variation->delete( true );
    //             }
    //         }
    if (!is_callable(array(
        $data_store,
        'create_all_product_variations'
    )))
    {
        wp_die();
    }

    $a = esc_html($data_store->create_all_product_variations($product, 50));

    $data_store->sort_all_product_variations($product->get_id());
    return $a;

}



//remove variation
function remove_variations()
{

    if (isset($_POST['variation_ids']))
    {
        $variation_ids = array_map('absint', (array)wp_unslash($_POST['variation_ids']));

        foreach ($variation_ids as $variation_id)
        {
            if ('product_variation' === get_post_type($variation_id))
            {
                $variation = wc_get_product($variation_id);
                $variation->delete(true);
            }
        }
    }

    if(isset($_POST['product_id'])){
         $variations     = wc_get_products(
    			array(
    				'status'  => array( 'private', 'publish' ),
    				'type'    => 'variation',
    				'parent'  => $_POST['product_id'],
    				'orderby' => array(
    					'menu_order' => 'ASC',
    					'ID'         => 'DESC',
    				),
    				'return'  => 'objects',
    			)
            );
            
            if($variations){
                        $ids=array();
                foreach($variations as $variation){
                    // $variation = wc_get_product( $variation->get_id() );
                    if($_POST['all']){
                        $variation->delete( true );
                    }else{
                        if(!$variation->get_regular_price()){
                            $ids[]=$variation->get_id();    
                            $variation->delete( true );
                            }
                    }
                }

                if($_POST['all']){
                    echo true;
                }else{
                    echo json_encode($ids);
                }
            }
            echo false;
            wp_die();
    }
}

add_action('wp_ajax_nopriv_arabinda_remove_variations', 'remove_variations');
add_action('wp_ajax_arabinda_remove_variations', 'remove_variations');

/**
 * Save variations via AJAX.
 */
function save_variations()
{
    ob_start();

    // Check permissions again and make sure we have what we need.
    if (empty($_POST) || empty($_POST['product_id']))
    {
        wp_die(-1);
    }

    $product_id = absint($_POST['product_id']);
    WC_Admin_Meta_Boxes::$meta_box_errors = array();
    WC_Meta_Box_Product_Data::save_variations($product_id, get_post($product_id));

    do_action('woocommerce_ajax_save_product_variations', $product_id);

    $errors = WC_Admin_Meta_Boxes::$meta_box_errors;

    if ($errors)
    {
        echo '<div class="error notice is-dismissible">';

        foreach ($errors as $error)
        {
            echo '<p>' . wp_kses_post($error) . '</p>';
        }

        echo '<button type="button" class="notice-dismiss"><span class="screen-reader-text">' . esc_html__('Dismiss this notice.', 'woocommerce') . '</span></button>';
        echo '</div>';

        delete_option('woocommerce_meta_box_errors');
    }

    wp_die();
}
add_action('wp_ajax_nopriv_arabinda_save_variations', 'save_variations');
add_action('wp_ajax_arabinda_save_variations', 'save_variations');

//load all variations
function load_variations()
{

    if (empty($_POST['product_id']))
    {
        wp_die(-1);
    }

    $response = array();

    try
    {

        ob_start();

        global $post;

        $loop = 0;
        $product_id = absint($_POST['product_id']);
        $post = get_post($product_id); // phpcs:ignore
        $product_object = wc_get_product($product_id);
        $per_page = !empty($_POST['per_page']) ? absint($_POST['per_page']) : 100;
        $page = !empty($_POST['page']) ? absint($_POST['page']) : 1;
        $variations = wc_get_products(array(
            'status' => array(
                'private',
                'publish'
            ) ,
            'type' => 'variation',
            'parent' => $product_id,
            'limit'   => 100,
            // 'page'    => $page,
            'orderby' => array(
                'menu_order' => 'ASC',
                'ID' => 'DESC',
            ) ,
            'return' => 'objects',
        ));
        $response['count'] = 0;
        if ($variations)
        {

            echo '<a class="button-primary" style="background-color:red" id="remove_all">Remove All Variations</a>
            <!---<a class="button-primary" style="background-color:orange" id="remove_empty">Remove Empty Price Variations</a>---->';
echo '<div id="message" class="inline notice woocommerce-message woocommerce-notice-info-variation">
			<p>
			Don\'t forget to save variations. Otherwise value(s) will not be updated. :)	--- <a class="save_variation"> you can also click here.</a>		</p>
		</div>';
            wc_render_invalid_variation_notice($product_object);

            foreach ($variations as $variation_object)
            {
                $variation_id = $variation_object->get_id();
                $variation = get_post($variation_id);
                $variation_data = array_merge(get_post_custom($variation_id) , wc_get_product_variation_attributes($variation_id)); // kept for BW compatibility.
                include __DIR__ . '/templates/product-variations.php';
                $loop++;
            }
            echo '<a class="button-primary"id="save_changes">Save Variations</a>';

            $response['count'] = count($variations);

        }else{
            echo '<div id="NoVariationMessage" class="error variation-message" style="margin-bottom:20px;margin-left:0px">
    <p><strong>Important Notice</strong> – Add Some attribute value before generating variations.</p>
    <p></p>
    </div>';
        }

        $response['html'] = ob_get_clean();

    }
    catch(Exception $e)
    {
        wp_send_json_error(array(
            'error' => $e->getMessage()
        ));
    }

    // wp_send_json_success must be outside the try block not to break phpunit tests.
    wp_send_json_success($response);

}

add_action('wp_ajax_nopriv_arabinda_load_variations', 'load_variations');
add_action('wp_ajax_arabinda_load_variations', 'load_variations');




//rest
function array_get($key = null, $array = array() , $default = false)
{
    return avalue_dot($key, $array, $default);
}

function avalue_dot($key = null, $array = array() , $default = false)
{
    $array = (array)$array;
    if (!$key || !count($array))
    {
        return $default;
    }
    $option_key_array = explode('.', $key);

    $value = $array;

    foreach ($option_key_array as $dotKey)
    {
        if (isset($value[$dotKey]))
        {
            $value = $value[$dotKey];
        }
        else
        {
            return $default;
        }
    }
    return $value;
}

add_action('init', function ()
{
    $url_path = trim(parse_url(add_query_arg(array()) , PHP_URL_PATH) , '/');

    if ($url_path === 'easy-add')
    {
        // load the file if exists
        $load = include (__DIR__ . '/templates/template-add-product.php');
        if ($load)
        {
            exit(); // just exit if template was found and loaded
            
        }
    }
    if ($url_path === 'easy-add/submit')
        {
        // load the file if exists
        $load = include (__DIR__ . '/functions/redirect.php');
        if ($load)
        {
            exit();
        }

    }
    if ($url_path === 'wp-admin/easy-add/submit')
    {
        // load the file if exists
        $load = include (__DIR__ . '/functions/redirect.php');
        if ($load)
        {
            exit();
        }

    }

});

