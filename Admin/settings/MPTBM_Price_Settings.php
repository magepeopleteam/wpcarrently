<?php
/*
   * @Author 		engr.sumonazma@gmail.com
   * Copyright: 	mage-people.com
   */
if (!defined('ABSPATH')) {
	die;
} // Cannot access pages directly.
if (!class_exists('MPTBM_Price_Settings')) {
	class MPTBM_Price_Settings
	{
		public function __construct()
		{
			add_action('add_mptbm_settings_tab_content', [$this, 'price_settings'], 10, 1);
			add_action('save_post', [$this, 'save_price_settings'], 10, 1);
		}
		public function price_settings($post_id)
		{	
			$time_price = MP_Global_Function::get_post_info($post_id, 'mptbm_day_price');
			$manual_prices = MP_Global_Function::get_post_info($post_id, 'mptbm_manual_price_info', []);
			$terms_location_prices = MP_Global_Function::get_post_info($post_id, 'mptbm_terms_price_info', []);
			$location_terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false));

?>
			<div class="tabsItem" data-tabs="#mptbm_settings_pricing">
				<h2><?php esc_html_e('Price Settings', 'wpcarrently'); ?></h2>
				<p><?php esc_html_e('here you can set initial price, Waiting Time price, price calculation model', 'wpcarrently'); ?></p>

				<section class="bg-light" >
					<h6><?php esc_html_e('Price Settings', 'wpcarrently'); ?></h6>
					<span><?php esc_html_e('Here you can set price', 'wpcarrently'); ?></span>
				</section>
				<section>
					<label class="label">
						<div>
							<h6><?php esc_html_e('Price/Day', 'wpcarrently'); ?></h6>
							<span class="desc"><?php MPTBM_Settings::info_text('mptbm_day_price'); ?></span>
						</div>
						<input class="formControl mp_price_validation" name="mptbm_day_price" value="<?php echo esc_attr($time_price); ?>" type="text" placeholder="<?php esc_html_e('EX:10', 'wpcarrently'); ?>" />
					</label>
				</section>
				
				<!-- Manual price -->
				<section class="bg-light" style="margin-top: 20px;" data-collapse="#mp_manual">
					<h6><?php esc_html_e('Manual Price Settings', 'wpcarrently'); ?></h6>
					<span><?php esc_html_e('Manual Price Settings', 'wpcarrently'); ?></span>
				</section>
				
				
			</div>
		<?php
		}
		
		
		public function save_price_settings($post_id)
		{
			if (!isset($_POST['mptbm_transportation_type_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['mptbm_transportation_type_nonce'])), 'mptbm_transportation_type_nonce') && defined('DOING_AUTOSAVE') && DOING_AUTOSAVE && !current_user_can('edit_post', $post_id)) {
				return;
			}
			if (get_post_type($post_id) == MPTBM_Function::get_cpt()) {

				$price_based = "manual";
				update_post_meta($post_id, 'mptbm_price_based', $price_based);

				$hour_price = isset($_POST['mptbm_day_price']) ? sanitize_text_field($_POST['mptbm_day_price']) : 0;
				update_post_meta($post_id, 'mptbm_day_price', $hour_price);
				$manual_price_infos = array();
				$start_location = isset($_POST['mptbm_manual_start_location']) ? array_map('sanitize_text_field', $_POST['mptbm_manual_start_location']) : [];
				$end_location = isset($_POST['mptbm_manual_end_location']) ? array_map('sanitize_text_field', $_POST['mptbm_manual_end_location']) : [];
				$manual_price = isset($_POST['mptbm_manual_price']) ? array_map('sanitize_text_field', $_POST['mptbm_manual_price']) : [];

				if (sizeof($start_location) > 1 && sizeof($end_location) > 1) {
					$count = 0;
					foreach ($start_location as $key => $location) {
						if ($location && $end_location[$key] && $manual_price[$key]) {
							$manual_price_infos[$count]['start_location'] = $location;
							$manual_price_infos[$count]['end_location'] = $end_location[$key];
							$count++;
						}
					}
				}

				update_post_meta($post_id, 'mptbm_manual_price_info', $manual_price_infos);
				$terms_price_infos = array();
				$start_terms_location = isset($_POST['mptbm_terms_start_location']) ? array_map('sanitize_text_field', $_POST['mptbm_terms_start_location']) : [];
				$end_terms_location = isset($_POST['mptbm_terms_end_location']) ? array_map('sanitize_text_field', $_POST['mptbm_terms_end_location']) : [];
				
				if (sizeof($start_terms_location) > 1 && sizeof($end_terms_location) > 1) {
					$count = 0;
					foreach ($start_terms_location as $key => $location) {
						if ($location && $end_terms_location[$key]) {
							$terms_price_infos[$count]['start_location'] = $location;
							$terms_price_infos[$count]['end_location'] = $end_terms_location[$key];
							$count++;

						}
					}
				}
				update_post_meta($post_id, 'mptbm_terms_price_info', $terms_price_infos);
				
			}
		}
	}
	new MPTBM_Price_Settings();
}