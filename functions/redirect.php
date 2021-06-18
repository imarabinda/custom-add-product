<?php
if (!$_POST['id'])
{
    exit('how did you came here? authentication error, no direct _POSTs.');
}



$post_id = $_POST['id'];



$product_type = empty( $_POST['product-type'] ) ? WC_Product_Factory::get_product_type( $post_id ) : sanitize_title( wp_unslash( $_POST['product-type'] ) );
		$classname    = WC_Product_Factory::get_product_classname( $post_id, $product_type ? $product_type : 'simple' );
		$product      = new $classname( $post_id );
		$attributes   = prepare_attributes();
		$stock        = null;

		// Handle stock changes.
		if ( isset( $_POST['_stock'] ) ) {
			if ( isset( $_POST['_original_stock'] ) && wc_stock_amount( $product->get_stock_quantity( 'edit' ) ) !== wc_stock_amount( wp_unslash( $_POST['_original_stock'] ) ) ) {
				/* translators: 1: product ID 2: quantity in stock */
				WC_Admin_Meta_Boxes::add_error( sprintf( __( 'The stock has not been updated because the value has changed since editing. Product %1$d has %2$d units in stock.', 'woocommerce' ), $product->get_id(), $product->get_stock_quantity( 'edit' ) ) );
			} else {
				$stock = wc_stock_amount( wp_unslash( $_POST['_stock'] ) );
			}
		}

		// Handle dates.
		$date_on_sale_from = '';
		$date_on_sale_to   = '';

		// Force date from to beginning of day.
		if ( isset( $_POST['_sale_price_dates_from'] ) ) {
			$date_on_sale_from = wc_clean( wp_unslash( $_POST['_sale_price_dates_from'] ) );

			if ( ! empty( $date_on_sale_from ) ) {
				$date_on_sale_from = date( 'Y-m-d 00:00:00', strtotime( $date_on_sale_from ) );
			}
		}

		// Force date to to the end of the day.
		if ( isset( $_POST['_sale_price_dates_to'] ) ) {
			$date_on_sale_to = wc_clean( wp_unslash( $_POST['_sale_price_dates_to'] ) );

			if ( ! empty( $date_on_sale_to ) ) {
				$date_on_sale_to = date( 'Y-m-d 23:59:59', strtotime( $date_on_sale_to ) );
			}
		}

		if(isset($_POST['_kp_size_guide'])){
			update_post_meta($post_id,'_kp_size_guide',$_POST['_kp_size_guide']);
		}

		$brands = $_POST['brand'] ? $_POST['brand'] : array();
		wp_set_object_terms( $post_id,$brands ,'brand' );
		// $product->set_gallery_image_ids( $attachment_ids );
		
		$errors = $product->set_props(
			array(
				'name'=>isset($_POST['title']) ? wc_clean($_POST['title']):'New Product',
				'description'=>isset($_POST['description']) ? $_POST['description'] : null,
				'short_description'=>isset($_POST['description']) ? $_POST['description'] : null ,
				'status'=>isset($_POST['status']) ? $_POST['status'] : $product->get_status(),
				'date_modified'      => date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) ),
				'category_ids'=> array_unique( array_map( 'intval', isset($_POST['product_cat'] ) ? $_POST['product_cat']  : array())),
				'tag_ids'=> array_unique( array_map( 'intval', isset($_POST['product_tag'] ) ? $_POST['product_tag']  : array() )),
				'slug'=>isset($_POST['slug']) ? $_POST['slug'] : $product->get_slug(),
				'gallery_image_ids'=>isset( $_POST['product_image_gallery'] ) ? array_filter( explode( ',', wc_clean( $_POST['product_image_gallery'] ) ) ) : array(),
				'image_id'=>isset($_POST['_thumbnail_id']) ? $_POST['_thumbnail_id'] :'',
				'sku'                => isset( $_POST['_sku'] ) ? wc_clean( wp_unslash( $_POST['_sku'] ) ) : null,
				'purchase_note'      => isset( $_POST['_purchase_note'] ) ? wp_kses_post( wp_unslash( $_POST['_purchase_note'] ) ) : '',
				'downloadable'       => isset( $_POST['_downloadable'] ),
				'virtual'            => isset( $_POST['_virtual'] ),
				'featured'           => isset( $_POST['_featured'] ),
				'catalog_visibility' => isset( $_POST['_visibility'] ) ? wc_clean( wp_unslash( $_POST['_visibility'] ) ) : 'visible',
				'tax_status'         => isset( $_POST['_tax_status'] ) ? wc_clean( wp_unslash( $_POST['_tax_status'] ) ) : null,
				'tax_class'          => isset( $_POST['_tax_class'] ) ? wc_clean( wp_unslash( $_POST['_tax_class'] ) ) : null,
				'weight'             => isset( $_POST['_weight'] ) ? wc_clean( wp_unslash( $_POST['_weight'] ) ) : null,
				'length'             => isset( $_POST['_length'] ) ? wc_clean( wp_unslash( $_POST['_length'] ) ) : null,
				'width'              => isset( $_POST['_width'] ) ? wc_clean( wp_unslash( $_POST['_width'] ) ) : null,
				'height'             => isset( $_POST['_height'] ) ? wc_clean( wp_unslash( $_POST['_height'] ) ) : null,
				'shipping_class_id'  => isset( $_POST['product_shipping_class'] ) ? absint( wp_unslash( $_POST['product_shipping_class'] ) ) : null,
				'sold_individually'  => ! empty( $_POST['_sold_individually'] ),
				'upsell_ids'         => isset( $_POST['upsell_ids'] ) ? array_map( 'intval', (array) wp_unslash( $_POST['upsell_ids'] ) ) : array(),
				'cross_sell_ids'     => isset( $_POST['crosssell_ids'] ) ? array_map( 'intval', (array) wp_unslash( $_POST['crosssell_ids'] ) ) : array(),
				'regular_price'      => isset( $_POST['_regular_price'] ) ? wc_clean( wp_unslash( $_POST['_regular_price'] ) ) : null,
				'sale_price'         => isset( $_POST['_sale_price'] ) ? wc_clean( wp_unslash( $_POST['_sale_price'] ) ) : null,
				'date_on_sale_from'  => $date_on_sale_from,
				'date_on_sale_to'    => $date_on_sale_to,
				'manage_stock'       => ! empty( $_POST['_manage_stock'] ),
				'backorders'         => isset( $_POST['_backorders'] ) ? wc_clean( wp_unslash( $_POST['_backorders'] ) ) : null,
				'stock_status'       => isset( $_POST['_stock_status'] ) ? wc_clean( wp_unslash( $_POST['_stock_status'] ) ) : null,
				'stock_quantity'     => $stock,
				'low_stock_amount'   => isset( $_POST['_low_stock_amount'] ) && '' !== $_POST['_low_stock_amount'] ? wc_stock_amount( wp_unslash( $_POST['_low_stock_amount'] ) ) : '',
				'download_limit'     => isset( $_POST['_download_limit'] ) && '' !== $_POST['_download_limit'] ? absint( wp_unslash( $_POST['_download_limit'] ) ) : '',
				'download_expiry'    => isset( $_POST['_download_expiry'] ) && '' !== $_POST['_download_expiry'] ? absint( wp_unslash( $_POST['_download_expiry'] ) ) : '',
				// Those are sanitized inside prepare_downloads.
				'downloads'          => prepare_downloads(
					isset( $_POST['_wc_file_names'] ) ? wp_unslash( $_POST['_wc_file_names'] ) : array(), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					isset( $_POST['_wc_file_urls'] ) ? wp_unslash( $_POST['_wc_file_urls'] ) : array(), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					isset( $_POST['_wc_file_hashes'] ) ? wp_unslash( $_POST['_wc_file_hashes'] ) : array() // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				),
				'product_url'        => isset( $_POST['_product_url'] ) ? esc_url_raw( wp_unslash( $_POST['_product_url'] ) ) : '',
				'button_text'        => isset( $_POST['_button_text'] ) ? wc_clean( wp_unslash( $_POST['_button_text'] ) ) : '',
				'children'           => 'grouped' === $product_type ? self::prepare_children() : null,
				'reviews_allowed'    => ! empty( $_POST['comment_status'] ) && 'open' === $_POST['comment_status'],
				'attributes'         => $attributes,
				'default_attributes' => prepare_set_attributes( $attributes, 'default_attribute_' ),
			)
		);

		if ( is_wp_error( $errors ) ) {
			WC_Admin_Meta_Boxes::add_error( $errors->get_error_message() );
		}

		/**
		 * Set props before save.
		 *
		 * @since 3.0.0
		 */
		do_action( 'woocommerce_admin_process_product_object', $product );
		

		$product->save();

		if ( $product->is_type( 'variable' ) ) {
			$original_post_title = isset( $_POST['original_post_title'] ) ? wc_clean( wp_unslash( $_POST['original_post_title'] ) ) : '';
			$post_title          = isset( $_POST['post_title'] ) ? wc_clean( wp_unslash( $_POST['post_title'] ) ) : '';

			$product->get_data_store()->sync_variation_names( $product, $original_post_title, $post_title );
		}
		$text = $product->get_status() == 'publish' ? 'Updated': 'Published';
		
		$_SESSION['saved'] = 'Product '.$text.' succesfully. :)';
		
		if(isset($_SESSION['saved'])){
		    
		wp_redirect(add_query_arg(array('page'=>'easy-add','action'=>'edit','product_id'=>$post_id), admin_url('admin.php')));
		
		}
    






function prepare_attributes( $data = false ) {
		$attributes = array();

		if ( ! $data ) {
			$data = stripslashes_deep( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}

		if ( isset( $data['attribute_names'], $data['attribute_values'] ) ) {
			$attribute_names         = $data['attribute_names'];
			$attribute_values        = $data['attribute_values'];
			$attribute_visibility    = isset( $data['attribute_visibility'] ) ? $data['attribute_visibility'] : array();
			$attribute_variation     = isset( $data['attribute_variation'] ) ? $data['attribute_variation'] : array();
			$attribute_position      = $data['attribute_position'];
			$attribute_names_max_key = max( array_keys( $attribute_names ) );

			for ( $i = 0; $i <= $attribute_names_max_key; $i++ ) {
				if ( empty( $attribute_names[ $i ] ) || ! isset( $attribute_values[ $i ] ) ) {
					continue;
				}
				$attribute_id   = 0;
				$attribute_name = wc_clean( esc_html( $attribute_names[ $i ] ) );

				if ( 'pa_' === substr( $attribute_name, 0, 3 ) ) {
					$attribute_id = wc_attribute_taxonomy_id_by_name( $attribute_name );
				}

				$options = isset( $attribute_values[ $i ] ) ? $attribute_values[ $i ] : '';

				if ( is_array( $options ) ) {
					// Term ids sent as array.
					$options = wp_parse_id_list( $options );
				} else {
					// Terms or text sent in textarea.
					$options = 0 < $attribute_id ? wc_sanitize_textarea( esc_html( wc_sanitize_term_text_based( $options ) ) ) : wc_sanitize_textarea( esc_html( $options ) );
					$options = wc_get_text_attributes( $options );
				}

				if ( empty( $options ) ) {
					continue;
				}

				$attribute = new WC_Product_Attribute();
				$attribute->set_id( $attribute_id );
				$attribute->set_name( $attribute_name );
				$attribute->set_options( $options );
				$attribute->set_position( $attribute_position[ $i ] );
				$attribute->set_visible( isset( $attribute_visibility[ $i ] ) );
				$attribute->set_variation( isset( $attribute_variation[ $i ] ) );
				$attributes[] = apply_filters( 'woocommerce_admin_meta_boxes_prepare_attribute', $attribute, $data, $i );
			}
		}
		return $attributes;
    }
    

    function prepare_set_attributes( $all_attributes, $key_prefix = 'attribute_', $index = null ) {
		$attributes = array();

		if ( $all_attributes ) {
			foreach ( $all_attributes as $attribute ) {
				if ( $attribute->get_variation() ) {
					$attribute_key = sanitize_title( $attribute->get_name() );

					if ( ! is_null( $index ) ) {
						$value = isset( $_POST[ $key_prefix . $attribute_key ][ $index ] ) ? wp_unslash( $_POST[ $key_prefix . $attribute_key ][ $index ] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					} else {
						$value = isset( $_POST[ $key_prefix . $attribute_key ] ) ? wp_unslash( $_POST[ $key_prefix . $attribute_key ] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					}

					if ( $attribute->is_taxonomy() ) {
						// Don't use wc_clean as it destroys sanitized characters.
						$value = sanitize_title( $value );
					} else {
						$value = html_entity_decode( wc_clean( $value ), ENT_QUOTES, get_bloginfo( 'charset' ) ); // WPCS: sanitization ok.
					}

					$attributes[ $attribute_key ] = $value;
				}
			}
		}

		return $attributes;
    }
    

    function prepare_downloads( $file_names, $file_urls, $file_hashes ) {
		$downloads = array();

		if ( ! empty( $file_urls ) ) {
			$file_url_size = count( $file_urls );

			for ( $i = 0; $i < $file_url_size; $i ++ ) {
				if ( ! empty( $file_urls[ $i ] ) ) {
					$downloads[] = array(
						'name'        => wc_clean( $file_names[ $i ] ),
						'file'        => wp_unslash( trim( $file_urls[ $i ] ) ),
						'download_id' => wc_clean( $file_hashes[ $i ] ),
					);
				}
			}
		}
		return $downloads;
	}


        
    function request_data() {
		return $_REQUEST;
    }
    

    /**
	 * Apply product type constraints to stock status.
	 *
	 * @param WC_Product  $product The product whose stock status will be adjusted.
	 * @param string|null $stock_status The stock status to use for adjustment, or null if no new stock status has been supplied in the request.
	 * @return WC_Product The supplied product, or the synced product if it was a variable product.
	 */
	function maybe_update_stock_status( $product, $stock_status ) {
		if ( $product->is_type( 'external' ) ) {
			// External products are always in stock.
			$product->set_stock_status( 'instock' );
		} elseif ( isset( $stock_status ) ) {
			if ( $product->is_type( 'variable' ) && ! $product->get_manage_stock() ) {
				// Stock status is determined by children.
				foreach ( $product->get_children() as $child_id ) {
					$child = wc_get_product( $child_id );
					if ( ! $product->get_manage_stock() ) {
						$child->set_stock_status( $stock_status );
						$child->save();
					}
				}
				$product = WC_Product_Variable::sync( $product, false );
			} else {
				$product->set_stock_status( $stock_status );
			}
		}

		return $product;
	}

	/**
	 * Set the new regular or sale price if requested.
	 *
	 * @param WC_Product $product The product to set the new price for.
	 * @param string     $price_type 'regular' or 'sale'.
	 * @return bool true if a new price has been set, false otherwise.
	 */
	function set_new_price( $product, $price_type ) {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended

		$request_data = $this->request_data();

		if ( empty( $request_data[ "change_{$price_type}_price" ] ) || ! isset( $request_data[ "_{$price_type}_price" ] ) ) {
			return false;
		}

		$old_price     = $product->{"get_{$price_type}_price"}();
		$price_changed = false;

		$change_price  = absint( $request_data[ "change_{$price_type}_price" ] );
		$raw_price     = wc_clean( wp_unslash( $request_data[ "_{$price_type}_price" ] ) );
		$is_percentage = (bool) strstr( $raw_price, '%' );
		$price         = wc_format_decimal( $raw_price );

		switch ( $change_price ) {
			case 1:
				$new_price = $price;
				break;
			case 2:
				if ( $is_percentage ) {
					$percent   = $price / 100;
					$new_price = $old_price + ( $old_price * $percent );
				} else {
					$new_price = $old_price + $price;
				}
				break;
			case 3:
				if ( $is_percentage ) {
					$percent   = $price / 100;
					$new_price = max( 0, $old_price - ( $old_price * $percent ) );
				} else {
					$new_price = max( 0, $old_price - $price );
				}
				break;
			case 4:
				if ( 'sale' !== $price_type ) {
					break;
				}
				$regular_price = $product->get_regular_price();
				if ( $is_percentage ) {
					$percent   = $price / 100;
					$new_price = max( 0, $regular_price - ( round( $regular_price * $percent, wc_get_price_decimals() ) ) );
				} else {
					$new_price = max( 0, $regular_price - $price );
				}
				break;

			default:
				break;
		}

		if ( isset( $new_price ) && $new_price !== $old_price ) {
			$price_changed = true;
			$new_price     = round( $new_price, wc_get_price_decimals() );
			$product->{"set_{$price_type}_price"}( $new_price );
		}

		return $price_changed;

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
	}


    function save_product_shipping_data($product,$data){
        if ( isset( $data['virtual'] ) && true === $data['virtual'] ) {
			$product->set_weight( '' );
			$product->set_height( '' );
			$product->set_length( '' );
			$product->set_width( '' );
		} else {
			if ( isset( $data['weight'] ) ) {
				$product->set_weight( $data['weight'] );
			}

			// Height.
			if ( isset( $data['dimensions']['height'] ) ) {
				$product->set_height( $data['dimensions']['height'] );
			}

			// Width.
			if ( isset( $data['dimensions']['width'] ) ) {
				$product->set_width( $data['dimensions']['width'] );
			}

			// Length.
			if ( isset( $data['dimensions']['length'] ) ) {
				$product->set_length( $data['dimensions']['length'] );
			}
		}

		// Shipping class.
		if ( isset( $data['shipping_class'] ) ) {
			$data_store        = $product->get_data_store();
			$shipping_class_id = $data_store->get_shipping_class_id_by_slug( wc_clean( $data['shipping_class'] ) );
			$product->set_shipping_class_id( $shipping_class_id );
		}

        return $product;
        }


    function save_taxonomy_terms( $product, $terms, $taxonomy = 'cat' ) {
		$term_ids = wp_list_pluck( $terms, 'id' );

		if ( 'cat' === $taxonomy ) {
			$product->set_category_ids( $term_ids );
		} elseif ( 'tag' === $taxonomy ) {
			$product->set_tag_ids( $term_ids );
		}

		return $product;
	}