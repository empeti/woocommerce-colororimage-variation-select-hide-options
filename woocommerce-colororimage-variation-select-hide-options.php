<?php
/*
	Plugin Name: Woocommerce Color or Image Hide Unavailable Option Combinations
	Description: Hide unavailable variations for selected option
	Version:     1.0b
	Author:      Peter Matyas
	License:     GPL2
	License URI: https://www.gnu.org/licenses/gpl-2.0.html
	Domain Path: /languages
	Text Domain: woocommerce-color-or-image-hide-unavaliable-options
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
/**
 * Check if color or image variation select plugin is activated
 */
if (!is_plugin_active('woocommerce-colororimage-variation-select/woocommerce-colororimage-variation-select.php')){
	error_log('Woocommerce Colorimage Hide Unavailable Options plugin requires 	
				Woocommerce color or image variation select plugin to be activated!');
}
else{
	/**
	 * Variable products -  hide unavailable options
	 */
	add_action('woocommerce_after_variations_form', 'variation_form_hide_options', 10, 0);

	/**
	 * Set script to hide unavailable options
	 */
	function variation_form_hide_options(){
		global $woocommerce, $post;

		$product              = wc_get_product($post->ID);
		$variations_array     = get_wc_variations_array($product);

		echo '<script type="text/javascript">
				var variations_json = \'' . json_encode($variations_array) . '\';
				var variations      = JSON.parse(variations_json);

				jQuery(".swatchinput").click(function(){
					// get selected attribute from variations object 
					var selected_attribute = eval("variations.attribute_" 
												+ jQuery(this).find("label").attr("selectid") 
												+ "[\'" + jQuery(this).find("label").data("option") + "\']"
											 );
					
					// foreach other attribure names in selected attribute object							 						 
					for (label_selectid in selected_attribute){
						var s = selected_attribute[label_selectid];
						
						// foreach attribute name options 
						jQuery(".attribute-swatch label[selectid=\'" + label_selectid + "\']").each(function(){
					
							// if option exists in "attribute name" array in selected attribute object   
							if (s.indexOf(jQuery(this).data("option")) != -1){
								// show this option
								jQuery(this).parents("div").first().show();
							}
							else{
								// hide this option
								jQuery(this).parents("div").first().hide();
							}
						});
					}		
				});
			</script>';
	}

	/**
	 * Get available options
	 *
	 * @param WC_Product $product
	 *
	 * @return array
	 */
	function get_wc_variations_array($product){
		$options = array();

		$available_options = $product->get_available_variations();

		if (is_array($available_options)){
			foreach ($available_options as $avail_option){
				if (!empty($avail_option['attributes'])){
					$attributes = $avail_option['attributes'];
					foreach ($avail_option['attributes'] as $attr_name => $attr_value){
						foreach ($attributes as $attr_opt_name => $attr_opt_value){
							if ($attr_name != $attr_opt_name){
								$options[$attr_name]
									[$attr_value]
										[str_replace('attribute_','',$attr_opt_name)][] = $attr_opt_value;
							}
						}
					}
				}
			}
		}

		return $options;
	}
}
