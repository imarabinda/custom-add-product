<style>

.select_all_attributes,.select_no_attributes,.st-add-new-attribute{
	display:none!important;
}
.woocommerce_variation > h3 > select{
	margin-bo
	ttom:14px!important;
}

</style>

<?php
if (!is_user_logged_in())
{

    wp_redirect(add_query_arg('redirect_to', home_url() . $_SERVER['REQUEST_URI'], wp_login_url()));
    exit();
}

$user = wp_get_current_user();

$allowed_roles = array(
    'editor',
    'administrator',
    'author'
);
if (!array_intersect($allowed_roles, $user->roles))
{
    exit('sorry you\'re not allowed');
}

global $post;
wp_reset_query();

/**
 * Get course which currently in edit, or insert new course
 */

$product_id = (int)sanitize_text_field(array_get('product_id', $_GET));
$action = sanitize_text_field(isset($_GET['action']) ? $_GET['action'] : $old_is_gold);

if ($product_id && $action == 'edit')
{
	$post_id = $product_id;
$product_object = wc_get_product($product_id);
$post = get_post($product_id);
                
}
else if($action == 'new')
{
    $post_type = 'product';
    $post_id = wp_insert_post(array(
        'post_title' => __('New Product') ,
        'post_type' => $post_type,
		'post_status' => 'draft',
	));
	$product=new WC_Product($post_id);
	// $product->set_slug('new-product');
	// $product->save();

    // 			update_post_meta($post_id,'_product_code',generate_random_code(8,$post_id));
    wp_redirect(add_query_arg(array('product_id'=> $post_id,'action'=>'edit'), home_url() . $_SERVER['REQUEST_URI']));
    exit();

}else{
	return 'wrong attempt';
}

if (!$product_object || !$post || !$product_id)
{
    exit('Wrong Attempt');
}


// 			setup_postdata( $post );
// get_header();

?>
<!-- <link rel='stylesheet' id='imgareaselect-css'  href='<?php echo home_url() ?>/wp-includes/js/imgareaselect/imgareaselect.css?ver=0.9.8' media='all' /> -->
<link rel='stylesheet' id='woocommerce_admin_styles-css'  href='<?php echo plugins_url('/woocommerce/assets/css/admin.css?ver=4.5.2'); ?> media='all' />
<!-- <link rel='stylesheet' id='jquery-ui-style-css'  href='<?php echo home_url() ?>/wp-content/plugins/woocommerce/assets/css/jquery-ui/jquery-ui.min.css?ver=4.5.2' media='all' /> -->
<!-- <link rel='stylesheet' id='wc-components-css'  href='<?php echo home_url() ?>/wp-content/plugins/woocommerce/packages/woocommerce-admin/dist/components/style.css?ver=1.5.0' media='all' /> -->
<!-- <link rel='stylesheet' id='wc-admin-app-css'  href='<?php echo home_url() ?>/wp-content/plugins/woocommerce/packages/woocommerce-admin/dist/app/style.css?ver=1.5.0' media='all' /> -->
<!-- <link rel='stylesheet' id='wc-material-icons-css'  href='https://fonts.googleapis.com/icon?family=Material+Icons+Outlined&#038;ver=1.5.0' media='all' /> -->

<style>
.wp-editor-area{
	color:black!important;
}
</style>
<div id="post-body" class="metabox-holder columns-2">
    <div id="post-body-content" style="position: relative;">
    
<form enctype="multipart/form-data" method="post" action="easy-add/submit">

<input type="hidden" name="id" class="post_id" id="post_ID" value="<?php echo $post_id; ?>"/>
<input type="hidden" name="admin_url" class="ajaxUrl" value="<?php echo admin_url('admin-ajax.php'); ?>"/>
<input type="hidden" name="status" value="<?php echo $product_object->get_status() == 'draft'?  'publish' : 'publish'; ?>"/>
<input type="hidden" name="_visibility" value="visible"/>
<!-- 	title -->

<div id="titlediv">
    <div id="titlewrap" >
        <!--<label class="" id="title-prompt-text" for="title">Product name</label>-->
        <input type="text" name="title" size="30" placeholder="Product Name" value="<?php echo $product_object->get_name(); ?>" id="title" spellcheck="true" autocomplete="off" />
    </div>
    <div class="inside">
        <div id="edit-slug-box" class="hide-if-no-js">  <a style="float:right" target="_blank" href="<?php echo get_permalink( $product_id ); ?>">View Product</a>
</div>
    </div>
  
</div>
<!----title end---->

<?php
if(isset($_SESSION['saved'])){
    echo '<div id="message" class="updated inline"><p><strong>'.$_SESSION['saved'].'A</strong></p></div>';
    // unset($_SESSION['saved']);
}
?>

<!--description-->
<div style="margin-bottom:20px">
<?php
$editor_settings = array(
												'media_buttons' => true,
												'quicktags'     => true,
												'editor_height' => 150,
												'textarea_name' => 'description'
											);
											
											wp_editor($product_object->get_description(), 'description', $editor_settings);
?>

</div>
<!--descripttion end-->



<!--image-->
<div id="postimagediv" class="postbox ">
   <div class="postbox-header">
      <h2 style="margin-left:10px">Product image</h2>
    </div>
   <div class="inside">

<?php
if(has_post_thumbnail( $product_object->get_id() )){
	echo '<div class="form-field term-group">
     <input type="hidden" id="_thumbnail_id" name="_thumbnail_id" class="custom_media_url" value="'.$product_object->get_image_id().'">
     <div id="featured-image-wrapper">'.wp_get_attachment_image ( $product_object->get_image_id(), "thumbnail" ).'</div>
     <p>
       <input type="button" class="button button-secondary ct_tax_media_button" id="ct_tax_media_button " style="display:none" name="ct_tax_media_button" value="Set Product Image">
       <input type="button" class="button button-secondary ct_tax_media_remove" id="ct_tax_media_remove" name="ct_tax_media_remove" value="Remove Product Image">
    </p>
   </div>';
}else{
	?>
<div class="form-field term-group">
     <input type="hidden" id="_thumbnail_id" name="_thumbnail_id" class="custom_media_url" value="">
     <div id="featured-image-wrapper">
	 </div>
     <p>
       <input type="button" class="button button-secondary ct_tax_media_button" id="ct_tax_media_button" name="ct_tax_media_button" value="<?php _e( 'Set Product Image', 'hero-theme' ); ?>" />
       <input type="button" class="button button-secondary ct_tax_media_remove" id="ct_tax_media_remove" style="display:none" name="ct_tax_media_remove" value="<?php _e( 'Remove Product Image', 'hero-theme' ); ?>" />
    </p>
   </div>
<?php
}
?>

  

   </div>
</div>


<!--end image-->

<!--gallery-->
<div id="woocommerce-product-images" class="postbox">
   <div class="postbox-header">
      <h2 style="margin-left:10px">Product gallery</h2>
    </div>
   <div class="inside">
      <div id="product_images_container">
			<ul class="product_images">
				<?php
				$product_image_gallery = $product_object->get_gallery_image_ids( 'edit' );

				$attachments         = array_filter( $product_image_gallery );
				$update_meta         = false;
				$updated_gallery_ids = array();

				if ( ! empty( $attachments ) ) {
					foreach ( $attachments as $attachment_id ) {
						$attachment = wp_get_attachment_image( $attachment_id, 'thumbnail' );

						// if attachment is empty skip.
						if ( empty( $attachment ) ) {
							$update_meta = true;
							continue;
						}
						?>
						<li class="image" data-attachment_id="<?php echo esc_attr( $attachment_id ); ?>">
							<?php echo $attachment; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<ul class="actions">
								<li><a href="#" class="delete tips" data-tip="<?php esc_attr_e( 'Delete image', 'woocommerce' ); ?>"><?php esc_html_e( 'Delete', 'woocommerce' ); ?></a></li>
							</ul>
							<?php
							// Allow for extra info to be exposed or extra action to be executed for this attachment.
							do_action( 'woocommerce_admin_after_product_gallery_item', $post_id, $attachment_id );
							?>
						</li>
						<?php

						// rebuild ids to be saved.
						$updated_gallery_ids[] = $attachment_id;
					}

					// need to update product meta to set new gallery ids
					if ( $update_meta ) {
						update_post_meta( $post->ID, '_product_image_gallery', implode( ',', $updated_gallery_ids ) );
					}
				}
				?>
			</ul>

			<input type="hidden" id="product_image_gallery" name="product_image_gallery" value="<?php echo esc_attr( implode( ',', $updated_gallery_ids ) ); ?>" />

		</div>
		<p class="add_product_images hide-if-no-js">
			<a href="#" data-choose="<?php esc_attr_e( 'Add images to product gallery', 'woocommerce' ); ?>" data-update="<?php esc_attr_e( 'Add to gallery', 'woocommerce' ); ?>" data-delete="<?php esc_attr_e( 'Delete image', 'woocommerce' ); ?>" data-text="<?php esc_attr_e( 'Delete', 'woocommerce' ); ?>"><?php esc_html_e( 'Add product gallery images', 'woocommerce' ); ?></a>
		</p>



   </div>
</div>
<!--end gallery-->

<script>
jQuery( function( $ ) {
var product_gallery_frame;
	var $image_gallery_ids = jQuery( '#product_image_gallery' );
	var $product_images    = jQuery( '#product_images_container' ).find( 'ul.product_images' );

	jQuery( '.add_product_images' ).on( 'click', 'a', function( event ) {
		var $el = jQuery( this );

		event.preventDefault();

		// If the media frame already exists, reopen it.
		if ( product_gallery_frame ) {
			product_gallery_frame.open();
			return;
		}

		// Create the media frame.
		product_gallery_frame = wp.media.frames.product_gallery = wp.media({
			// Set the title of the modal.
			title: $el.data( 'choose' ),
			button: {
				text: $el.data( 'update' )
			},
			states: [
				new wp.media.controller.Library({
					title: $el.data( 'choose' ),
					filterable: 'all',
					multiple: true
				})
			]
		});

		// When an image is selected, run a callback.
		product_gallery_frame.on( 'select', function() {
			var selection = product_gallery_frame.state().get( 'selection' );
			var attachment_ids = $image_gallery_ids.val();

			selection.map( function( attachment ) {
				attachment = attachment.toJSON();

				if ( attachment.id ) {
					attachment_ids   = attachment_ids ? attachment_ids + ',' + attachment.id : attachment.id;
					var attachment_image = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;

					$product_images.append(
						'<li class="image" data-attachment_id="' + attachment.id + '"><img src="' + attachment_image +
						'" /><ul class="actions"><li><a href="#" class="delete" title="' + $el.data('delete') + '">' +
						$el.data('text') + '</a></li></ul></li>'
					);
				}
			});

			$image_gallery_ids.val( attachment_ids );
		});

		// Finally, open the modal.
		product_gallery_frame.open();
	});

	// Image ordering.
	// $product_images.sortable({
	// 	items: 'li.image',
	// 	cursor: 'move',
	// 	scrollSensitivity: 40,
	// 	forcePlaceholderSize: true,
	// 	forceHelperSize: false,
	// 	helper: 'clone',
	// 	opacity: 0.65,
	// 	placeholder: 'wc-metabox-sortable-placeholder',
	// 	start: function( event, ui ) {
	// 		ui.item.css( 'background-color', '#f6f6f6' );
	// 	},
	// 	stop: function( event, ui ) {
	// 		ui.item.removeAttr( 'style' );
	// 	},
	// 	update: function() {
	// 		var attachment_ids = '';

	// 		jQuery( '#product_images_container' ).find( 'ul li.image' ).css( 'cursor', 'default' ).each( function() {
	// 			var attachment_id = jQuery( this ).attr( 'data-attachment_id' );
	// 			attachment_ids = attachment_ids + attachment_id + ',';
	// 		});

	// 		$image_gallery_ids.val( attachment_ids );
	// 	}
	// });

	// Remove images.
	jQuery( '#product_images_container' ).on( 'click', 'a.delete', function() {
		jQuery( this ).closest( 'li.image' ).remove();

		var attachment_ids = '';

		jQuery( '#product_images_container' ).find( 'ul li.image' ).css( 'cursor', 'default' ).each( function() {
			var attachment_id = jQuery( this ).attr( 'data-attachment_id' );
			attachment_ids = attachment_ids + attachment_id + ',';
		});

		$image_gallery_ids.val( attachment_ids );

		// Remove any lingering tooltips.
		jQuery( '#tiptip_holder' ).removeAttr( 'style' );
		jQuery( '#tiptip_arrow' ).removeAttr( 'style' );

		return false;
	});
});



</script>


<!-- 	type of product -->
<div id="product_type" class="postbox">
    <div class="postbox-header">
        <h2 style="margin-left:10px">Product Type</h2>
    </div>
    
    <div class="inside">
        
        <label for="product-type">
        <select id="product-type" name="product-type">
            <optgroup label="Product Type">
                <option value="simple" <?php echo $product_object->get_type() == 'simple' ? 'selected' : ''; ?>>Simple product</option>
                <option value="variable" <?php echo $product_object->get_type() == 'variable' ? 'selected' : ''; ?>>Variable product</option>
            </optgroup>
        </select>
        </label>
    </div>
</div>
<!-- 	end type of product -->


<!--product code-->
<div id="product_code" class="postbox">
    <div class="postbox-header">
    <h2 style="margin-left:10px">Product Code: <?php echo $product_object->get_sku(); ?> (Auto Generated)</h2>
    </div>
</div>
<!-- end product code-->

<div id="general" class="postbox">

<div class="postbox-header">
<h2 style="margin-left:10px">General</h2>
</div>

<div class="inside">
<div id="general_product_data" class="panel woocommerce_options_panel">

	<div class="options_group show_if_external hidden">
		<?php
		woocommerce_wp_text_input(
			array(
				'id'          => '_product_url',
				'value'       => is_callable( array( $product_object, 'get_product_url' ) ) ? $product_object->get_product_url( 'edit' ) : '',
				'label'       => __( 'Product URL', 'woocommerce' ),
				'placeholder' => 'https://',
				'description' => __( 'Enter the external URL to the product.', 'woocommerce' ),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'          => '_button_text',
				'value'       => is_callable( array( $product_object, 'get_button_text' ) ) ? $product_object->get_button_text( 'edit' ) : '',
				'label'       => __( 'Button text', 'woocommerce' ),
				'placeholder' => _x( 'Buy product', 'placeholder', 'woocommerce' ),
				'description' => __( 'This text will be shown on the button linking to the external product.', 'woocommerce' ),
			)
		);

		?>
	</div>

	<div class="options_group pricing show_if_simple show_if_external">
		<?php
		woocommerce_wp_text_input(
			array(
				'id'        => '_regular_price',
				'value'     => $product_object->get_regular_price( 'edit' ),
				'label'     => __( 'Regular price', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')',
				'data_type' => 'price',
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'          => '_sale_price',
				'value'       => $product_object->get_sale_price( 'edit' ),
				'data_type'   => 'price',
				'label'       => __( 'Sale price', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')',
				'description' => '<a href="#" class="sale_schedule">' . __( 'Schedule', 'woocommerce' ) . '</a>',
			)
		);



		$sale_price_dates_from_timestamp = $product_object->get_date_on_sale_from( 'edit' ) ? $product_object->get_date_on_sale_from( 'edit' )->getOffsetTimestamp() : false;
		$sale_price_dates_to_timestamp   = $product_object->get_date_on_sale_to( 'edit' ) ? $product_object->get_date_on_sale_to( 'edit' )->getOffsetTimestamp() : false;

		$sale_price_dates_from = $sale_price_dates_from_timestamp ? date_i18n( 'Y-m-d', $sale_price_dates_from_timestamp ) : '';
		$sale_price_dates_to   = $sale_price_dates_to_timestamp ? date_i18n( 'Y-m-d', $sale_price_dates_to_timestamp ) : '';

		echo '<p class="form-field sale_price_dates_fields hidden">
				<label for="_sale_price_dates_from">' . esc_html__( 'Sale price dates', 'woocommerce' ) . '</label>
				<input type="text" class="short" name="_sale_price_dates_from" id="_sale_price_dates_from" value="' . esc_attr( $sale_price_dates_from ) . '" placeholder="' . esc_html( _x( 'From&hellip;', 'placeholder', 'woocommerce' ) ) . ' YYYY-MM-DD" maxlength="10" pattern="' . esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) ) . '" />
				<input type="text" class="short" name="_sale_price_dates_to" id="_sale_price_dates_to" value="' . esc_attr( $sale_price_dates_to ) . '" placeholder="' . esc_html( _x( 'To&hellip;', 'placeholder', 'woocommerce' ) ) . '  YYYY-MM-DD" maxlength="10" pattern="' . esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) ) . '" />
				<a href="#" class="description cancel_sale_schedule">' . esc_html__( 'Cancel', 'woocommerce' ) . '</a>' . wc_help_tip( __( 'The sale will start at 00:00:00 of "From" date and end at 23:59:59 of "To" date.', 'woocommerce' ) ) . '
			</p>';

		do_action( 'woocommerce_product_options_pricing' );

		?>
	</div>

	

	<?php if ( wc_tax_enabled() ) : ?>
		<div class="options_group show_if_simple show_if_external show_if_variable">
			<?php
			woocommerce_wp_select(
				array(
					'id'          => '_tax_status',
					'value'       => $product_object->get_tax_status( 'edit' ),
					'label'       => __( 'Tax status', 'woocommerce' ),
					'options'     => array(
						'taxable'  => __( 'Taxable', 'woocommerce' ),
						'shipping' => __( 'Shipping only', 'woocommerce' ),
						'none'     => _x( 'None', 'Tax status', 'woocommerce' ),
					),
					'desc_tip'    => 'false',
					'description' => __( 'Define whether or not the entire product is taxable, or just the cost of shipping it.', 'woocommerce' ),
				)
			);

			woocommerce_wp_select(
				array(
					'id'          => '_tax_class',
					'value'       => $product_object->get_tax_class( 'edit' ),
					'label'       => __( 'Tax class', 'woocommerce' ),
					'options'     => wc_get_product_tax_class_options(),
					'desc_tip'    => 'false',
					'description' => __( 'Choose a tax class for this product. Tax classes are used to apply different tax rates specific to certain types of product.', 'woocommerce' ),
				)
			);

			do_action( 'woocommerce_product_options_tax' );
			?>
		</div>
	<?php endif; ?>

</div>

</div>

</div>



<!---inventory--->
<div id="pricing" class="postbox">
        <div class="postbox-header">
            <h2 style="margin-left:10px">Inventory</h2>
        </div>
    
        <div class="inside">
                  <div id="inventory_product_data" class="panel woocommerce_options_panel">

	<div class="options_group">
		<?php
		if ( wc_product_sku_enabled() && false) {
			woocommerce_wp_text_input(
				array(
					'id'          => '_sku',
					'value'       => $product_object->get_sku( 'edit' ),
					'label'       => '<abbr title="' . esc_attr__( 'Stock Keeping Unit', 'woocommerce' ) . '">' . esc_html__( 'SKU', 'woocommerce' ) . '</abbr>',
					'desc_tip'    => false,
					'description' => __( 'SKU refers to a Stock-keeping unit, a unique identifier for each distinct product and service that can be purchased.', 'woocommerce' ),
				)
			);
		}

		do_action( 'woocommerce_product_options_sku' );

		if ( 'yes' === get_option( 'woocommerce_manage_stock' ) ) {

			woocommerce_wp_checkbox(
				array(
					'id'            => '_manage_stock',
					'value'         => $product_object->get_manage_stock( 'edit' ) ? 'yes' : 'no',
					'wrapper_class' => 'show_if_simple show_if_variable',
					'label'         => __( 'Manage stock?', 'woocommerce' ),
					'description'   => __( 'Enable stock management at product level', 'woocommerce' ),
				)
			);

			do_action( 'woocommerce_product_options_stock' );

			echo '<div class="stock_fields show_if_simple show_if_variable">';

			woocommerce_wp_text_input(
				array(
					'id'                => '_stock',
					'value'             => wc_stock_amount( $product_object->get_stock_quantity( 'edit' ) ),
					'label'             => __( 'Stock quantity', 'woocommerce' ),
					'desc_tip'          => false,
					'description'       => __( 'Stock quantity. If this is a variable product this value will be used to control stock for all variations, unless you define stock at variation level.', 'woocommerce' ),
					'type'              => 'number',
					'custom_attributes' => array(
						'step' => 'any',
					),
					'data_type'         => 'stock',
				)
			);

			echo '<input type="hidden" name="_original_stock" value="' . esc_attr( wc_stock_amount( $product_object->get_stock_quantity( 'edit' ) ) ) . '" />';

			woocommerce_wp_select(
				array(
					'id'          => '_backorders',
					'value'       => $product_object->get_backorders( 'edit' ),
					'label'       => __( 'Allow backorders?', 'woocommerce' ),
					'options'     => wc_get_product_backorder_options(),
					'desc_tip'    => false,
					'description' => __( 'If managing stock, this controls whether or not backorders are allowed. If enabled, stock quantity can go below 0.', 'woocommerce' ),
				)
			);

			woocommerce_wp_text_input(
				array(
					'id'                => '_low_stock_amount',
					'value'             => $product_object->get_low_stock_amount( 'edit' ),
					'placeholder'       => get_option( 'woocommerce_notify_low_stock_amount' ),
					'label'             => __( 'Low stock threshold', 'woocommerce' ),
					'desc_tip'          => false,
					'description'       => __( 'When product stock reaches this amount you will be notified by email', 'woocommerce' ),
					'type'              => 'number',
					'custom_attributes' => array(
						'step' => 'any',
					),
				)
			);

			do_action( 'woocommerce_product_options_stock_fields' );

			echo '</div>';
		}

		woocommerce_wp_select(
			array(
				'id'            => '_stock_status',
				'value'         => $product_object->get_stock_status( 'edit' ),
				'wrapper_class' => 'stock_status_field hide_if_variable hide_if_external hide_if_grouped',
				'label'         => __( 'Stock status', 'woocommerce' ),
				'options'       => wc_get_product_stock_status_options(),
				'desc_tip'      => false,
				'description'   => __( 'Controls whether or not the product is listed as "in stock" or "out of stock" on the frontend.', 'woocommerce' ),
			)
		);

		do_action( 'woocommerce_product_options_stock_status' );
		?>
	</div>

	<div class="options_group show_if_simple show_if_variable">
		<?php
		woocommerce_wp_checkbox(
			array(
				'id'            => '_sold_individually',
				'value'         => $product_object->get_sold_individually( 'edit' ) ? 'yes' : 'no',
				'wrapper_class' => 'show_if_simple show_if_variable',
				'label'         => __( 'Sold individually', 'woocommerce' ),
				'description'   => __( 'Enable this to only allow one of this item to be bought in a single order', 'woocommerce' ),
			)
		);

		do_action( 'woocommerce_product_options_sold_individually' );
		?>
	</div>

	<?php do_action( 'woocommerce_product_options_inventory_product_data' ); ?>
</div>
  
            </div>
</div>
<!--end of price--->


<!---link upsell--->
<div id="upsell" class="postbox hidden">
        <div class="postbox-header">
            <h2 style="margin-left:10px">Show Recent Products or Some Recommendations</h2>
        </div>
    
        <div class="inside">
        <div id="linked_product_data" class="panel woocommerce_options_panel">

	<div class="options_group show_if_grouped hidden">
		<p class="form-field">
			<label for="grouped_products"><?php esc_html_e( 'Grouped products', 'woocommerce' ); ?></label>
			<select class="wc-product-search" multiple="multiple" style="width: 50%;" id="grouped_products" name="grouped_products[]" data-sortable="true" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>" data-action="woocommerce_json_search_products" data-exclude="<?php echo intval( $post->ID ); ?>">
				<?php
				$product_ids = $product_object->is_type( 'grouped' ) ? $product_object->get_children( 'edit' ) : array();

				foreach ( $product_ids as $product_id ) {
					$product = wc_get_product( $product_id );
					if ( is_object( $product ) ) {
						echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . htmlspecialchars( wp_kses_post( $product->get_formatted_name() ) ) . '</option>';
					}
				}
				?>
			</select> 
		</p>
	</div>

	<div class="options_group">
		<p class="form-field">
			<label for="upsell_ids"><?php esc_html_e( 'Upsells', 'woocommerce' ); ?></label>
			<select class="wc-product-search " multiple="multiple" style="width: 50%;" id="upsell_ids" name="upsell_ids[]" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>" data-action="woocommerce_json_search_products_and_variations" data-exclude="<?php echo intval( $post->ID ); ?>">
				<?php
				$product_ids = $product_object->get_upsell_ids( 'edit' );

				foreach ( $product_ids as $product_id ) {
					$product = wc_get_product( $product_id );
					if ( is_object( $product ) ) {
						echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . htmlspecialchars( wp_kses_post( $product->get_formatted_name() ) ) . '</option>';
					}
				}
				?>
			</select> 
		</p>

		<p class="form-field hide_if_grouped hide_if_external">
			<label for="crosssell_ids"><?php esc_html_e( 'Cross-sells', 'woocommerce' ); ?></label>
			<select class="wc-product-search" multiple="multiple" style="width: 50%;" id="crosssell_ids" name="crosssell_ids[]" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>" data-action="woocommerce_json_search_products_and_variations" data-exclude="<?php echo intval( $post->ID ); ?>">
				<?php
				$product_ids = $product_object->get_cross_sell_ids( 'edit' );

				foreach ( $product_ids as $product_id ) {
					$product = wc_get_product( $product_id );
					if ( is_object( $product ) ) {
						echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . htmlspecialchars( wp_kses_post( $product->get_formatted_name() ) ) . '</option>';
					}
				}
				?>
			</select> 
		</p>
	</div>

	<?php do_action( 'woocommerce_product_options_related' ); ?>
</div>

        
        </div>

        </div>
<!--end--->

<!-- 	cat -->

<div id="catdiv" class="postbox">
    <div class="postbox-header">
        <h2 style="margin-left:10px">Select Categories</h2>
    </div>
    <div class="inside">
        <?php
echo product_taxanomy_dropdown('product_cat', $post_id, array(
    'classes' => 'arabinda_select2'
));
?>
    </div>
</div>
<!---end cat-->

<!-- 	brand -->

<div id="branddiv" class="postbox">
    <div class="postbox-header">
        <h2 style="margin-left:10px">Select Brands</h2>
    </div>
    <div class="inside">
        <?php
echo product_taxanomy_dropdown('brand', $post_id, array(
    'classes' => 'arabinda_select2'
));
?>
    </div>
</div>
<!---end cat-->

	
<!-- 	tags -->
<div id="tagdiv" class="postbox">
    <div class="postbox-header">
        <h2 style="margin-left:10px">Select Tags</h2>
    </div>
    <div class="inside">
        <?php
echo product_taxanomy_dropdown('product_tag', $post_id, array(
    'classes' => 'arabinda_select2'
));
?>
    </div>
</div>
<!--end tags-->



<div id="advancedata" class="postbox hidden">
<div class="postbox-header"><h2 style="margin-left:10px">Extra Data</h2>
</div>

<div class="inside">
<div id="advanced_product_data" class="panel woocommerce_options_panel">

	<div class="options_group hide_if_external hide_if_grouped">
		<?php
		woocommerce_wp_textarea_input(
			array(
				'id'          => '_purchase_note',
				'value'       => $product_object->get_purchase_note( 'edit' ),
				'label'       => __( 'Purchase note', 'woocommerce' ),
				'desc_tip'    => false,
				'description' => __( 'Enter an optional note to send the customer after purchase.', 'woocommerce' ),
			)
		);
		?>
	</div>

	<div class="options_group hidden">
		<?php
		woocommerce_wp_text_input(
			array(
				'id'                => 'menu_order',
				'value'             => $product_object->get_menu_order( 'edit' ),
				'label'             => __( 'Menu order', 'woocommerce' ),
				'desc_tip'          => false,
				'description'       => __( 'Custom ordering position.', 'woocommerce' ),
				'type'              => 'number',
				'custom_attributes' => array(
					'step' => '1',
				),
			)
		);
		?>
	</div>

	<?php if ( post_type_supports( 'product', 'comments' ) ) : ?>
		<div class="options_group reviews">
			<?php
				woocommerce_wp_checkbox(
					array(
						'id'      => 'comment_status',
						'value'   => $product_object->get_reviews_allowed( 'edit' ) ? 'open' : 'closed',
						'label'   => __( 'Enable reviews', 'woocommerce' ),
						'cbvalue' => 'open',
					)
				);
				do_action( 'woocommerce_product_options_reviews' );
				
		woocommerce_wp_checkbox(
				array(
					'id'            => '_featured',
					'value'         => $product_object->get_featured( 'edit' ) ? 'yes' : 'no',
					'wrapper_class' => '',
					'label'         => __( 'Featured product ?', 'woocommerce' ),
					'description'   => __( 'This will show as Featured product', 'woocommerce' ),
				)
			);
			?>
		</div>
	<?php endif; ?>

	<div class="options_group slug hidden">
	<?php
	woocommerce_wp_text_input(
			array(
				'id'        => 'slug_not',
				'value'     => $product_object->get_slug( 'edit' ) ? $product_object->get_slug( 'edit' ) : '',
				'label'     => __( 'Product Url', 'woocommerce' ),
				'data_type' => 'url',
			)
		);
	?>
	</div>

	<?php do_action( 'woocommerce_product_options_advanced' ); ?>
</div>


</div>
</div>

<!-- <div id="size_chart" class="postbox">
<div class="postbox-header">
<h2 style="margin-left:10px">Select Size Chart</h2>
</div>
</div> -->

<div class="inside">



<!--attribute info-->




<div id="AttributeMessage" class="error attribute-message" style="margin-bottom:20px;margin-left:0px">
    <p><strong>Important Notice</strong> – Attributes are Displayed in products and Used for variations if the product is variable product. <p style="color:red">Please Save attributes after selecting</p>
    </p>
    </div>
        

<!--end attribute info-->
                 
                                            
            <div class="product_attributes wc-metaboxes">   
	<?php

$attributes = $product_object->get_attributes('edit');
    $i = - 1;
if ($attributes)
{

    foreach ($attributes as $attribute)
    {
        $i++;
        $metabox_class = array();

        if ($attribute->is_taxonomy())
        {
            $metabox_class[] = 'taxonomy';
            $metabox_class[] = $attribute->get_name();
        }

        include __DIR__ . '/product-attribute-row.php';
    }

}

global $wc_product_attributes;

// Array of defined attribute taxonomies.
$attribute_taxonomies = wc_get_attribute_taxonomies();
$hideGen=false;
if (!empty($attribute_taxonomies))
{
    foreach ($attribute_taxonomies as $tax)
    {
        $attribute_taxonomy_name = wc_attribute_taxonomy_name($tax->attribute_name);
        if (!array_key_exists($attribute_taxonomy_name, $attributes))
        {
            $i++;
            getAttributeByName($attribute_taxonomy_name, $i);
        }
 }
}else{
    $hideGen= true;
    echo '<div class="error attribute-message" style="margin-bottom:20px;margin-left:0px">
    <p><strong>Important Notice</strong> – No Attributes Found Go and add Some Attribute.
    </p>
    </div>
    
    <script>
    jQuery("#AttributeMessage").hide();
    
    </script>';
}

?>
</div>

<?php if(!$hideGen){
    $text = $product_object->get_type() == 'variable' ? 'Generate Variations' : 'Save Attributes';
echo '<p>
	<a id="variationGenerator" class="button-primary">'.$text.'</a>
</p>	
';
}?>

        
        
        <div id="variableSection">
<div id="generate_text_tip" class="error attribute-message" style="margin-bottom:20px;margin-left:0px">
    <p><strong>Important Notice</strong> – Click Generate Variations to Generate All available variations from the selected value.
    </p>
    </div>
    
    
        
        <div class="woocommerce_variations">
                
        </div>
        
        </div>
	
	
	<p>
	<button class="button-primary"><?php echo $product_object->get_status() != 'publish' ? 'Publish' : 'Update'; ?></button>
</p>
	
</script>
<style>
.notice{
    margin-left:0px!important;
    margin-bottom:5px!important;
}
</style>

<script src='<?php echo plugins_url('/woocommerce/assets/js/jquery-blockui/jquery.blockUI.min.js?ver=2.70'); ?> id='jquery-blockui-js'></script>

<script src='<?php echo plugins_url('/woocommerce/assets/js/selectWoo/selectWoo.full.min.js?ver=1.0.6')?> id='selectWoo-js'></script>

<script src='<?php echo plugins_url('/woocommerce/assets/js/jquery-serializejson/jquery.serializejson.min.js?ver=2.8.1') ?> id='serializejson-js'></script>



<!-- Extra -->


<script>

window.addEventListener('load', function () {
var selected= '';
var product_id = jQuery('input.post_id').val(); //need to be changed
var ajaxUrl = jQuery('input.ajaxUrl').val();

 function ct_media_upload(button_class) {
         var _custom_media = true,
         _orig_send_attachment = wp.media.editor.send.attachment;
         jQuery('body').on('click', button_class, function(e) {
           var button_id = '#'+jQuery(this).attr('id');
           var send_attachment_bkp = wp.media.editor.send.attachment;
           var button = jQuery(button_id);
           _custom_media = true;
           wp.media.editor.send.attachment = function(props, attachment){
             if ( _custom_media ) {
			   jQuery('#_thumbnail_id').val(attachment.id);
			   jQuery.post(ajaxUrl,{
				   action:'arabinda_get_attachment',
				   thumbnail_id:attachment.id,
			   },function(response){
				   if(response.data){
				jQuery('#featured-image-wrapper').html(response.data.html);
				jQuery(button_class).hide();
				
	   jQuery('.ct_tax_media_remove').show();
			   }
			})
              } else {
               return _orig_send_attachment.apply( button_id, [props, attachment] );
             }
            }
         wp.media.editor.open(button);
         return false;
       });
	 }
	 
	 ct_media_upload('.ct_tax_media_button'); 
	 
     jQuery('body').on('click','.ct_tax_media_remove',function(){
       jQuery('#_thumbnail_id').val('');
	   jQuery('#featured-image-wrapper').html('');
	   jQuery('.ct_tax_media_remove').hide();
	   jQuery('.ct_tax_media_button').show();
	 });
	 
     



checkProductType();

function checkProductType(){
     logicalHideShowVariation(jQuery('select[name="product-type"]').val());
}



if(jQuery('input#_manage_stock').length > 0 ){
    
    jQuery('input#_manage_stock').on('change',function () {
                jQuery( this ).closest( '.options_group' ).find( '.stock_fields' ).hide();
			jQuery( this ).closest( '.options_group' ).find( '.stock_status_field' ).show();

        if(jQuery(this).is(':checked')){
        jQuery( this ).closest( '.options_group' ).find( '.stock_fields' ).show();
		jQuery( this ).closest( '.options_group' ).find( '.stock_status_field' ).hide();
        }
    });
    

 jQuery('input#_manage_stock').change();
}




if (jQuery().select2){
        jQuery('.arabinda_select2,.wc-product-search,.wc-enhanced-select').select2();

    }
    
    
    	

    
    jQuery('#variationGenerator').click(function(e){
        e.preventDefault();
        
        
		jQuery( '.product_attributes' ).block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		
		if(selected=='variable'){
		jQuery('#variableSection').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		}
		
		
		var original_data = jQuery( '.product_attributes' ).find( 'input, select, textarea' );
		var data = {
			post_id     : product_id,
			product_type: selected,
			data        : original_data.serialize(),
			action      : 'arabinda_save_attributes',
		};
		
		jQuery.post( ajaxUrl, data, function( response ) {
			if ( response.error ) {
				// Error.
				window.alert( response.error );
			} else if ( response.data ) {
				// Success.
				jQuery( '.product_attributes' ).html( response.data.html );
				jQuery( '.product_attributes' ).unblock();
				
				if(response.data.trigger){
				    load_variations();
				}
			}
		});
    })
    
    
    


var variations_loaded = false;
function loading_variations(){
    jQuery('#generate_text_tip').html('<p><strong>Loading Variations</strong> – Hang On</p>');
    variation_section_block();
}


function variation_section_block(){
    
jQuery('#variableSection').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		
}
function variation_section_unblock(){
    jQuery( '#variableSection' ).unblock();
	jQuery('#generate_text_tip').hide();
	jQuery('.woocommerce_variations').show();
	
}


function load_variations(){

		loading_variations();
    var data = {
			product_id     : product_id,
			action      : 'arabinda_load_variations',
		};
	
	
		jQuery.post( ajaxUrl, data, function( response ) {
		    
		    	if ( response.error ) {
				// Error.
				window.alert( response.error );
			} else if ( response.data ) {
			   variation_section_unblock();
			   if(response.data.count == 0){
		          generate_text_tip_show();
			   }else{
			   jQuery('.woocommerce_variations').html(response.data.html);
			    variations_loaded= true;
		        load_variation_script();
		   	       
			   }
		 
			}
		       
		});
}

function generate_text_tip_show(){
    jQuery('.woocommerce_variations').hide();
     jQuery('#generate_text_tip').html('<p><strong>Important Notice</strong> – Click Generate Variations to Generate All available variations from the selected value(s)</p>');
					jQuery('#generate_text_tip').show();
			   
}

function load_variation_script(){

	var prices = {
		// black:3500,
		yellow:2500,
		pink:2000,
		green:1500,
		'navy-blue':1200,
		red:1000,
		orange:500,
		purple:600,
		'lite-blue':400
	}
		jQuery('.price-changer').on('change',function(){
			show_in_price_field(this,jQuery(this).val());
		})

		jQuery('.price-changer').change();
		
		function show_in_price_field(ele,val){
			var loop = jQuery(ele).attr('data-loop');
var f = jQuery( ele ).closest( '.woocommerce_variation' ).find( '#variable_regular_price_'+loop );
if(prices[val]){

	jQuery(f).val(prices[val]);
	}else{
	jQuery(f).val('');
		
	}		
}


		var setting_variation_image_id=null;

		/**
		 * Variation image object
		 *
		 * @type {Object}
		 */
		var setting_variation_image= null;


			var	variable_image_frame= null;


	var	wp_media_post_id= wp.media.model.settings.post.id;

    jQuery('.upload_image_button').on( 'click',function(event){
		var $button = jQuery( this ),
				post_id = $button.attr( 'rel' ),
				$parent = $button.closest( '.upload_image' );

			setting_variation_image    = $parent;
			setting_variation_image_id = post_id;

			event.preventDefault();

			if ( $button.is( '.remove' ) ) {

				jQuery( '.upload_image_id', setting_variation_image ).val( '' ).change();
				setting_variation_image.find( 'img' ).eq( 0 )
					.attr( 'src','<?php echo home_url('/wp-content/uploads/woocommerce-placeholder.png');?>' );
				setting_variation_image.find( '.upload_image_button' ).removeClass( 'remove' );

			} else {

				// If the media frame already exists, reopen it.
				if ( variable_image_frame ) {
					variable_image_frame.uploader.uploader
						.param( 'post_id', setting_variation_image_id );
					variable_image_frame.open();
					return;
				} else {
					wp.media.model.settings.post.id = setting_variation_image_id;
				}

				// Create the media frame.
				variable_image_frame = wp.media.frames.variable_image = wp.media({
					// Set the title of the modal.
					title: 'Choose Variation Image',
					button: {
						text: 'Set image'
					},
					states: [
						new wp.media.controller.Library({
							title: 'Choose variation image',
							filterable: 'all'
						})
					]
				});

				// When an image is selected, run a callback.
				variable_image_frame.on( 'select', function () {

					var attachment = variable_image_frame.state()
						.get( 'selection' ).first().toJSON(),
						url = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;

					jQuery( '.upload_image_id', setting_variation_image ).val( attachment.id ).change();
					setting_variation_image.find( '.upload_image_button' ).addClass( 'remove' );
					setting_variation_image.find( 'img' ).eq( 0 ).attr( 'src', url );

					wp.media.model.settings.post.id = wp_media_post_id;
				});

				// Finally, open the modal.
				variable_image_frame.open();
			}


	} )


    jQuery('input.variable_manage_stock').on( 'change', function () {
      
        jQuery( this ).closest( '.woocommerce_variation' ).find( '.show_if_variation_manage_stock' ).hide();
			jQuery( this ).closest( '.woocommerce_variation' ).find( '.variable_stock_status' ).show();

			if ( jQuery( this ).is( ':checked' ) ) {
				jQuery( this ).closest( '.woocommerce_variation' ).find( '.show_if_variation_manage_stock' ).show();
				jQuery( this ).closest( '.woocommerce_variation' ).find( '.variable_stock_status' ).hide();
			}

			// Parent level.
			if ( jQuery( 'input#_manage_stock:checked' ).length ) {
				jQuery( this ).closest( '.woocommerce_variation' ).find( '.variable_stock_status' ).hide();
			}

    })
    
    
    jQuery('input.variable_manage_stock').change();

    jQuery('.remove_variation').on( 'click', function(){
    
            var variation = jQuery( this ).attr( 'rel' ),
					variation_ids = [],
					data          = {
						action: 'arabinda_remove_variations'
					};

				if ( 0 < variation ) {
					variation_ids.push( variation );

					data.variation_ids = variation_ids;
					
			        var element = jQuery(this);
					variation_section_block();
					jQuery.post( ajaxUrl, data, function(response) {
					    var p = jQuery(element).parent();
					    var nd = jQuery(p).parent().remove();
					    variation_section_unblock();
					if(jQuery('.woocommerce_variation').length == 0 ){
					    generate_text_tip_show();
					    
					}
					    
					});

				}
});

jQuery('#remove_all').on( 'click', function(){
		
	var data          = {
						action: 'arabinda_remove_variations',
					product_id:product_id,
					all:true
					};

					variation_section_block();
					jQuery.post( ajaxUrl, data, function(response) {
					    variation_section_unblock();
					    generate_text_tip_show();					    
					});

				
});

jQuery('#remove_empty').on( 'click', function(){
		
	var data          = {
						action: 'arabinda_remove_variations',
					product_id:product_id,
					all:false
					};

					variation_section_block();
					jQuery.post( ajaxUrl, data, function(response) {
						variation_section_unblock();
						console.log(response);
					    // generate_text_tip_show();					    
					});

				
});

    
    jQuery('#save_changes,.save_variation').click(function(e){
        e.preventDefault();
        variation_section_block();
        var wrapper     = jQuery( '.woocommerce_variations'),
				need_update = jQuery( '.variation-needs-update', wrapper ),
				data        = get_variations_fields(need_update);
                data.action          = 'arabinda_save_variations';
				data.product_id      = product_id;
				data['product-type'] = selected;

jQuery.ajax({
					url: ajaxUrl,
					data: data,
					type: 'POST',
					success: function( response ) {
						// Allow change page, delete and add new variations
						
					    variation_section_unblock();
					}
				});


    })
}

	function get_variations_fields( fields ) {
	    
			var data = jQuery( ':input', fields ).serializeJSON();
			return data;
		};



jQuery('select[name="product-type"]').on('change',function(){
     logicalHideShowVariation(jQuery(this).val());
})


function logicalHideShowVariation(val){
    var id ='#variableSection';
    selected = val;
    if(val == 'variable'){
        jQuery(id).show();
        jQuery('#variationGenerator').text('Generate Variations');
        if(!variations_loaded){
            load_variations();
        }
        
        jQuery('#general').hide();
    }else{
        jQuery(id).hide();
        jQuery('#general').show();
        jQuery('#variationGenerator').text('Save Attributes');
        
    }
}
});


</script>
</div>
</div>
<?php
// get_footer();
?>
