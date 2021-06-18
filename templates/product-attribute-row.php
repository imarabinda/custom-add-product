<?php
if (!defined('ABSPATH'))
{
    exit;
}
?>
<div id="<?php echo esc_attr($attribute->get_name()); ?>div" class="postbox">
    
    
    <div class="postbox-header">
        <h2 style="margin-left:10px">	
        <?php if ($attribute->is_taxonomy()): ?>
							<strong>Select <?php echo wc_attribute_label($attribute->get_name()); ?></strong>
							<input type="hidden" name="attribute_names[<?php echo esc_attr($i); ?>]" value="<?php echo esc_attr($attribute->get_name()); ?>" />
						<?php
else: ?>
							<input type="text" class="attribute_name" name="attribute_names[<?php echo esc_attr($i); ?>]" value="<?php echo esc_attr($attribute->get_name()); ?>" />
						<?php
endif; ?>
</h2>
    </div>
    
    
    
    <div class="inside">
        
        <div data-taxonomy="<?php echo esc_attr($attribute->get_taxonomy()); ?>" class="woocommerce_attribute wc-metabox <?php echo esc_attr(implode(' ', $metabox_class)); ?>" rel="<?php echo esc_attr($attribute->get_position()); ?>">
            
	    <div class="woocommerce_attribute_data wc-metabox-content">
		<table cellpadding="0" cellspacing="0">
			<tbody>
				<tr>
					<td class="attribute_position">
						<input type="hidden" name="attribute_position[<?php echo esc_attr($i); ?>]" class="attribute_position" value="<?php echo esc_attr($attribute->get_position()); ?>" />
					</td>
					<td rowspan="3">
						<label></label>
						<?php
if ($attribute->is_taxonomy() && $attribute_taxonomy = $attribute->get_taxonomy_object())
{
    $attribute_types = wc_get_attribute_types();

    if (!array_key_exists($attribute_taxonomy->attribute_type, $attribute_types))
    {
        $attribute_taxonomy->attribute_type = 'select';
    }

    if ('select' === $attribute_taxonomy->attribute_type)
    {
?>
								<select style="width:100%" multiple="multiple" data-placeholder="<?php esc_attr_e('Select '.wc_attribute_label($attribute->get_name()), 'woocommerce'); ?>" class="arabinda_select<?php echo esc_attr($i);?> multiselect attribute_values wc-enhanced-select" data-sortable="true" name="attribute_values[<?php echo esc_attr($i); ?>][]">
									<?php
        $args = array(
            'orderby' => !empty($attribute_taxonomy->attribute_orderby) ? $attribute_taxonomy->attribute_orderby : 'name',
            'hide_empty' => 0,
        );
        $all_terms = get_terms($attribute->get_taxonomy() , apply_filters('woocommerce_product_attribute_terms', $args));
        if ($all_terms)
        {
            foreach ($all_terms as $term)
            {
                $options = $attribute->get_options();
                $options = !empty($options) ? $options : array();
                echo '<option value="' . esc_attr($term->term_id) . '"' . wc_selected($term->term_id, $options) . '>' . esc_html(apply_filters('woocommerce_product_attribute_term_name', $term->name, $term)) . '</option>';
            }
        }
?>
								</select>
							
								<?php
    }

    do_action('woocommerce_product_option_terms', $attribute_taxonomy, $i, $attribute);
}
else
{
    /* translators: %s: WC_DELIMITER */
?>
							<textarea name="attribute_values[<?php echo esc_attr($i); ?>]" cols="5" rows="5" placeholder="<?php printf(esc_attr__('Enter some text, or some attributes by "%s" separating values.', 'woocommerce') , WC_DELIMITER); ?>"><?php echo esc_textarea(wc_implode_text_attributes($attribute->get_options())); ?></textarea>
							<?php
}
?>
					</td>
				</tr>
				<input type="hidden" class="checkbox" <?php checked($attribute->get_visible() , true); ?> name="attribute_visibility[<?php echo esc_attr($i); ?>]" value="1" /> 
				<input  type="hidden" class="checkbox" <?php checked($attribute->get_variation() , true); ?> name="attribute_variation[<?php echo esc_attr($i); ?>]" value="1" />
					
				<?php do_action('woocommerce_after_product_attribute_settings', $attribute, $i); ?>
			</tbody>
		</table>
	</div>
</div>
        
        
        
        
        
        
        
        </div><!--inside end--->
</div><!---top end--->
<script>
if (jQuery().select2){
        jQuery('.arabinda_select<?php echo esc_attr($i);?>').select2();
    }
</script>