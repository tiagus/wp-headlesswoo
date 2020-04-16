<?php
defined( 'ABSPATH' ) || exit;


/**
 * Abstract Class for all the Template Loading
 * Class WFACP_Template_Common
 */
abstract class WFACP_Template_Common {
	public $customizer_fields = [];
	public $customizer_fields_data = [];
	public $customizer_keys = [
		'style'            => 'wfacp_style',
		'header'           => 'wfacp_header',
		'footer'           => 'wfacp_footer',
		'gbadge'           => 'wfacp_gbadge',
		'product_switcher' => 'wfacp_form_product_switcher',

	];
	public $wfacp_html_fields = [
		'wfacp_html_widget_1' => 'Custom HTML Sidebar-1',
		'wfacp_html_widget_2' => 'Custom HTML Sidebar-2',
		'wfacp_html_widget_3' => 'Custom HTML Below Form',
	];

	public $customizer_css = [];
	public $default_badges = [];
	public $selected_font_family = '';
	public $web_google_fonts = [
		'Open Sans' => 'Open Sans',
	];
	public $device_type = 'not-mobile';
	public $enabled_product_switching = 'no';
	public $have_billing_address = false;
	public $have_shipping_address = false;
	public $have_billing_address_index = 2;
	public $have_shipping_address_index = 1;
	public $setting_new_version = false;
	protected $available_fields = [
		'layout',
		'header',
		'product',
		'guarantee',
		'listing',
		'testimonial',
		'widget',
		'customer-care',
		'promises',
		'footer',
		'style',
		'gbadge',
		'product_switcher',
		'html_widget_1',
		'html_widget_2',
		'html_widget_3',
	];
	protected $data = null;
	protected $layout_setting = [];
	protected $internal_css = [];
	protected $customizer_data = [];
	protected $fields = [];
	protected $section_keys_data = [];
	protected $change_set = [];
	protected $template_dir = __DIR__;
	protected $template_type = 'pre_built';
	protected $template_slug = 'layout_4';
	protected $sections = array( 'wfacp_section' );
	protected $steps = [];
	protected $fieldsets = [];
	protected $checkout_fields = [];
	protected $css_classes = [];
	protected $current_step = 'single_step';
	protected $wfacp_id = 0;
	protected $sidebar_layout_order = [];
	protected $mobile_layout_order = [];
	protected $exluded_sidebar_sections = [];
	protected $url = '';
	protected $current_active_sidebar = [];
	protected $woo_checkout_keys = [
		'billing_first_name'  => 'John',
		'billing_last_name'   => 'Doe',
		'billing_company'     => 'ABC Company',
		'billing_email'       => 'abc@example.com',
		'billing_phone'       => '999-999-9999',
		'billing_address_1'   => '258 Worthington Drive',
		'billing_city'        => 'Texas',
		'billing_postcode'    => '75038',
		'shipping_first_name' => 'John',
		'shipping_last_name'  => 'Doe',
		'shipping_address_1'  => '258 Worthington Drive',
		'shipping_city'       => 'Texas',
		'shipping_postcode'   => '75038',
	];
	protected $have_coupon_field = false;
	protected $have_shipping_method = true;


	protected function __construct() {
		$this->img_path        = WFACP_PLUGIN_URL . '/admin/assets/img/';
		$this->img_public_path = WFACP_PLUGIN_URL . '/assets/img/';
		$this->url             = WFACP_PLUGIN_URL . '/public/templates/' . $this->get_template_slug() . '/views/';

		$this->setup_data_hooks();

		$this->css_js_hooks();
		$this->woocommerce_field_hooks();
		$this->remove_actions();
	}

	public function get_template_slug() {
		return $this->template_slug;
	}

	private function setup_data_hooks() {

		add_action( 'wfacp_after_checkout_page_found', array( $this, 'setup_sidebar_data' ), 100 );
		add_action( 'wfacp_after_checkout_page_found', array( $this, 'remove_action_at_page_found' ), 100 );
		add_filter( 'wfacp_body_class', [ $this, 'add_custom_cls' ] );

		add_filter( 'wfacp_forms_field', [ $this, 'merge_customizer_data' ], 10, 2 );
		add_filter( 'wfacp_default_values', [ $this, 'pre_populate_from_get_parameter' ], 10, 3 );
		add_filter( 'wfacp_layout_default_setting', [ $this, 'change_default_setting' ], 10, 2 );
		add_filter( 'wfacp_layout_default_setting', [ $this, 'order_btn_sticky' ], 10, 2 );
		add_filter( 'wfacp_style_default_setting', [ $this, 'multitab_default_setting' ], 11, 2 );
		add_filter( 'wfacp_customizer_layout', [ $this, 'customizer_layout_order' ], 11, 2 );
		add_action( 'woocommerce_before_checkout_form', [ $this, 'add_form_steps' ], 11, 2 );
		add_filter( 'woocommerce_update_order_review_fragments', [ $this, 'check_cart_coupons' ], - 1 );
		add_filter( 'woocommerce_update_order_review_fragments', [ $this, 'remove_default_order_summary_table' ] );
		add_filter( 'woocommerce_checkout_posted_data', [ $this, 'assign_email_as_a_username' ] );
		add_action( 'customize_controls_print_footer_scripts', array( $this, 'form_pop_up_content' ) );
		add_filter( 'wfacp_layout_default_setting', [ $this, 'change_setting_for_default_checkout' ], 99, 2 );
		add_filter( 'wfacp_native_checkout_cart', function () {
			return true;
		} );


		/** Adding the_content default filters on 'wfacp_the_content' handle */
		add_filter( 'wfacp_the_content', 'wptexturize' );
		add_filter( 'wfacp_the_content', 'convert_smilies', 20 );
		add_filter( 'wfacp_the_content', 'wpautop' );
		add_filter( 'wfacp_the_content', 'shortcode_unautop' );
		add_filter( 'wfacp_the_content', 'prepend_attachment' );
		add_filter( 'wfacp_the_content', 'do_shortcode', 11 );
		add_filter( 'wfacp_the_content', array( $GLOBALS['wp_embed'], 'run_shortcode' ), 8 );
		add_filter( 'wfacp_the_content', array( $GLOBALS['wp_embed'], 'autoembed' ), 8 );
		add_filter( 'wc_get_template', [ $this, 'remove_form_billing_and_shipping_html' ] );
		add_filter( 'wc_get_template', [ $this, 'replace_recurring_total_shipping' ], 999, 2 );
		add_action( 'wfacp_footer_before_print_scripts', [ $this, 'add_loader_to_custmizer' ] );
		add_action( 'wfacp_after_billing_email_field', [ $this, 'show_account_fields' ], 10, 3 );

		add_filter( 'show_admin_bar', [ $this, 'remove_admin_bar' ] );
		add_filter( 'woocommerce_country_locale_field_selectors', [ $this, 'remove_add1_add2_local_field_selector' ] );
		add_action( 'wfacp_before_product_switcher_html', [ $this, 'display_undo_message' ] );

		add_action( 'wfacp_template_after_step', [ $this, 'display_next_button' ], 11, 3 );

		add_filter( 'woocommerce_available_payment_gateways', [ $this, 'remove_extra_payment_gateways_in_customizer' ], 99 );

		add_action( 'woocommerce_checkout_process', [ $this, 'woocommerce_checkout_process' ], 0 );
		add_filter( 'wfacp_forms_field', [ $this, 'add_styling_class_to_country_field' ], 12, 2 );
		add_filter( 'wfacp_page_settings', [ $this, 'merge_default_page_settings' ] );

	}


	private function css_js_hooks() {
		add_action( 'wfacp_header_print_in_head', [ $this, 'no_follow_no_index' ] );
		add_action( 'wfacp_header_print_in_head', [ $this, 'add_style_inline' ] );
		add_action( 'wfacp_header_print_in_head', array( $this, 'typography_custom_css' ) );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_script' ], 100 );
		add_action( 'wp_print_styles', [ $this, 'remove_woocommerce_js_css' ], 99 );
		add_action( 'wp_print_styles', [ $this, 'remove_theme_css_and_scripts' ], 100 );
		add_action( 'wfacp_header_print_in_head', [ $this, 'add_header_script' ] );
		add_action( 'wfacp_footer_after_print_scripts', [ $this, 'add_footer_script' ] );
		add_action( 'woocommerce_before_checkout_form', [ $this, 'checkout_form_login' ] );
		add_action( 'woocommerce_before_checkout_form', [ $this, 'checkout_form_coupon' ] );
		add_action( 'wp_head', [ $this, 'add_viewport_meta' ], - 1 );

	}

	private function woocommerce_field_hooks() {
		add_filter( 'wfacp_default_field', [ $this, 'wfacp_default_field' ], 10, 2 );
		add_filter( 'woocommerce_order_button_html', [ $this, 'add_class_change_place_order' ], 11 );

		add_action( 'wfacp_template_after_step', [ $this, 'display_back_button' ], 10, 2 );
		add_action( 'wfacp_template_after_step', [ $this, 'close_back_button_div' ], 12, 2 );

		/* change text of next step*/
		add_filter( 'woocommerce_order_button_text', [ $this, 'change_order_placed_step_label' ] );
		add_filter( 'wfacp_change_next_btn_single_step', [ $this, 'change_single_step_label' ], 10, 2 );
		add_filter( 'wfacp_change_next_btn_two_step', [ $this, 'change_two_step_label' ], 10, 2 );

		add_filter( 'wfacp_change_back_btn', [ $this, 'change_back_step_label' ], 10, 2 );

		add_filter( 'woocommerce_update_order_review_fragments', [ $this, 'add_fragment_order_summary' ], 99, 2 );
		add_filter( 'woocommerce_update_order_review_fragments', [ $this, 'add_fragment_shipping_calculator' ], 99, 2 );
		add_filter( 'woocommerce_update_order_review_fragments', [ $this, 'add_fragment_order_total' ], 99, 2 );
		add_filter( 'woocommerce_update_order_review_fragments', [ $this, 'add_fragment_coupon' ], 99, 2 );
		add_filter( 'woocommerce_locate_template', [ $this, 'change_template_location' ], 99999, 3 );
		add_filter( 'woocommerce_checkout_fields', [ $this, 'woocommerce_checkout_fields' ], 0 );
		//for normal update_checkout hook
		add_filter( 'woocommerce_update_order_review_fragments', [ $this, 'add_fragment_product_switching' ], 99, 2 );
		add_action( 'wfacp_intialize_template_by_ajax', function () {
			//for when our fragments calls running
			add_filter( 'woocommerce_update_order_review_fragments', [ $this, 'add_fragment_product_switching' ], 99, 2 );
		}, 10 );


		add_filter( 'wfacp_checkout_fields', [ $this, 'set_priority_of_form_fields' ], 0, 2 );
		add_filter( 'wfacp_checkout_fields', [ $this, 'handling_checkout_post_data' ], 1 );
		add_filter( 'wfacp_checkout_fields', [ $this, 'correct_country_state_locals' ], 2 );
		add_filter( 'woocommerce_countries_shipping_countries', [ $this, 'woocommerce_countries_shipping_countries' ] );
		add_filter( 'woocommerce_countries_allowed_countries', [ $this, 'woocommerce_countries_allowed_countries' ] );
		// updating shipping and billing address vice-versa
		add_action( 'woocommerce_checkout_update_order_meta', [ $this, 'woocommerce_checkout_update_order_meta' ] );
		add_action( 'woocommerce_before_checkout_form', [ $this, 'reattach_neccessary_hooks' ] );
		add_action( 'woocommerce_review_order_before_submit', [ $this, 'display_hide_payment_box_heading' ] );
		add_filter( 'woocommerce_available_payment_gateways', [ $this, 'change_payment_gateway_text' ] );
		add_filter( 'woocommerce_get_cart_page_permalink', [ $this, 'change_cancel_url' ], 999 );
		add_action( 'wfacp_before_breadcrumb', [ $this, 'call_before_cart_link' ] );
	}


	private function remove_actions() {
		remove_action( 'woocommerce_checkout_order_review', 'woocommerce_order_review', 10 );
		remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );
		remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
	}

	public function add_loader_to_custmizer() {
		?>
        <div class="wfacpkirki-customizer-loading-wrapper wfacp_customizer_loader">
            <span class="wfacpkirki-customizer-loading"></span>
        </div>
		<?php
	}

	public function remove_action_at_page_found() {
		remove_all_actions( 'woocommerce_review_order_after_submit' );
		remove_all_actions( 'woocommerce_review_order_before_submit' );
	}

	public function setup_sidebar_data() {
		$selected_template_slug = $this->get_template_slug();
		$all_section_data       = WFACP_Common::get_option( 'wfacp_layout_section_customizer_data' );
		//WFACP_Common::pr( $all_section_data );

		if ( is_array( $all_section_data ) && count( $all_section_data ) > 0 ) {
			$this->sidebar_layout_order = $all_section_data;
		}
		$mobile_layout_order = WFACP_Common::get_option( 'wfacp_layout_section_' . $selected_template_slug . '_mobile_sections_page_order' );
		if ( is_array( $mobile_layout_order ) && count( $mobile_layout_order ) > 0 ) {
			$this->mobile_layout_order = $mobile_layout_order;
		}
		$this->assign_field_data();

	}


	public function assign_field_data() {
		$detect                 = new WFACP_Mobile_Detect();
		$selected_template_slug = $this->get_template_slug();
		$selected_template_type = $this->get_template_type();

		$layout_key = '';
		if ( isset( $selected_template_slug ) && $selected_template_slug != '' ) {
			$layout_key = $selected_template_slug . '_';
		}

		$this->get_wfacp_version();

		if ( $selected_template_type == 'embed_form' ) {
			$this->sidebar_layout_order[] = 'wfacp_form';
			$this->sidebar_layout_order[] = 'wfacp_style';
			$this->sidebar_layout_order[] = 'wfacp_form_product_switcher';
		} elseif ( $selected_template_slug == 'layout_9' ) {
			$this->sidebar_layout_order[] = 'wfacp_form_cart';
		}
		if ( $detect->isMobile() && ! $detect->istablet() ) {
			$this->device_type = 'mobile';
		}
		$customizer_support = apply_filters( 'wfacp_customizer_supports', $this->available_fields );
		foreach ( $this->customizer_keys as $ckey => $cvalue ) {
			if ( in_array( $ckey, $customizer_support ) ) {
				$this->sidebar_layout_order[] = $cvalue;
			}
		}

		if ( empty( $this->sidebar_layout_order ) ) {
			return false;
		}


		foreach ( $this->sidebar_layout_order as $s_key => $_value ) {
			$section_key = $_value;
			if ( strpos( $section_key, 'wfacp_benefits_' ) !== false ) {

				/*  Section Heading */
				$data                    = array();
				$data['heading_section'] = $this->get_heading_section( $section_key, $selected_template_slug );


				/*  Icon Text */
				$benefits_boxes                                       = array();
				$benefits_boxes                                       = WFACP_Common::get_option( $section_key . '_section_icon_text' );
				$data['benefit_content']['icon_text']                 = $benefits_boxes;
				$data['benefit_content']['list_icon']                 = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'list_icon' );
				$data['benefit_content']['hide_list_icon']            = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'hide_list_icon' );
				$data['benefit_content']['icon_color']                = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'icon_color' );
				$data['benefit_content']['display_list_heading']      = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'display_list_heading' );
				$data['benefit_content']['show_list_description']     = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'show_list_description' );
				$data['benefit_content']['display_list_bold_heading'] = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'display_list_bold_heading' );

				/* Advanced Setting */

				$data['advance_setting'] = $this->get_advance_setting( $section_key, $selected_template_slug );

				/* Color Setting */
				$data['colors']                               = $this->get_color_setting( $section_key, $selected_template_slug );
				$this->customizer_fields_data[ $section_key ] = $data;
				$this->prepare_dynamic_style( $data, $section_key );

				/* Section Style */

				if ( isset( $data['colors']['heading_text_color'] ) ) {
					$heading_text_color                                                        = $data['colors']['heading_text_color'];
					$this->customizer_css['desktop'][ '.' . $section_key . ' .loop_head_sec' ] = array(
						'color' => $heading_text_color,
					);
				}

				if ( isset( $data['benefit_content']['icon_color'] ) ) {
					$this->customizer_css['desktop'][ '.' . $section_key . ' .wfacp-icon-list' ] = array(
						'color' => $data['benefit_content']['icon_color'],
					);
				}
			} elseif ( strpos( $section_key, 'wfacp_testimonials_' ) !== false ) {

				$data = array();

				/*  Section Heading */

				$data['heading_section'] = $this->get_heading_section( $section_key, $selected_template_slug );

				/* Testimonial text */
				$testimonial_data                = array();
				$testimonial_data['layout_type'] = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'layout_type' );

				$testimonial_data['type']             = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'testimonial_type' );
				$testimonial_data['show_review']      = WFACP_Common::get_option( $section_key . '_section_show_review' );
				$testimonial_data['review_limit']     = WFACP_Common::get_option( $section_key . '_section_review_limit' );
				$testimonial_data['hide_name']        = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'hide_name' );
				$testimonial_data['hide_designation'] = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'hide_designation' );
				$testimonial_data['hide_image']       = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'hide_image' );
				$testimonial_data['hide_author_meta'] = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'hide_author_meta' );
				$testimonial_data['image_type']       = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'image_type' );

				if ( isset( $testimonial_data['type'] ) && $testimonial_data['type'] == 'automatic' ) {
					$page_products_reviews = WFACP_Core()->public->get_product_list();

					$pr_reviews = [];


					$is_global_checkout = WFACP_Core()->public->is_checkout_override();
					if ( $is_global_checkout === true ) {
						foreach ( WC()->cart->get_cart() as $cart_item ) {
							$pr_reviews[] = $cart_item['product_id'];
						}

					} else {

						if ( is_array( $page_products_reviews ) && count( $page_products_reviews ) > 0 ) {
							foreach ( $page_products_reviews as $pr_key => $pr_val ) {
								$pr_reviews[] = $pr_val['id'];
							}
						}
					}


					if ( is_array( $pr_reviews ) && count( $pr_reviews ) > 0 ) {


						$args = array(
							'post__in'   => $pr_reviews,
							'status'     => 'approve',
							'type'       => 'all',
							'number'     => $testimonial_data['review_limit'],
							'meta_query' => array(
								array(
									'key'     => 'rating',
									'value'   => $testimonial_data['show_review'],
									'compare' => '>=',
								),
							),
							'meta_key'   => 'rating',
							'orderby'    => 'meta_value_num',
							'order'      => 'DESC',
						);

						$comments = get_comments( $args );


						if ( is_array( $comments ) && count( $comments ) > 0 ) {
							$h = 1;
							foreach ( $comments as $comment ) {
								if ( ! empty( $comment->comment_content ) ) {
									$testimonial_boxes[ $comment->comment_ID ]['tmessage'] = $comment->comment_content;
								}
								if ( ! empty( $comment->comment_author ) ) {
									$testimonial_boxes[ $comment->comment_ID ]['tname'] = $comment->comment_author;
								}
								$testimonial_boxes[ $comment->comment_ID ]['tdate']   = $comment->comment_date;
								$testimonial_boxes[ $comment->comment_ID ]['trating'] = get_comment_meta( $comment->comment_ID, 'rating', true );
								$testimonial_boxes[ $comment->comment_ID ]['timage']  = $comment->comment_author_email ? get_avatar_url( $comment->comment_author_email, array(
									'size' => 96,
								) ) : '';

								$h ++;
							}
							$testimonial_data['testimonials'] = $testimonial_boxes;
						}
					}
				} else {

					$testimonial_data['testimonials'] = WFACP_Common::get_option( $section_key . '_section_testimonials' );
				}

				$data['testimonial_data'] = $testimonial_data;
				$data['advance_setting']  = $this->get_advance_setting( $section_key, $selected_template_slug );
				$data['colors']           = $this->get_color_setting( $section_key, $selected_template_slug );

				$this->customizer_fields_data[ $section_key ] = $data;

				$this->prepare_dynamic_style( $data, $section_key );

				/* Section Style */

				if ( isset( $data['colors']['heading_text_color'] ) ) {
					$heading_text_color                                                        = $data['colors']['heading_text_color'];
					$this->customizer_css['desktop'][ '.' . $section_key . ' .loop_head_sec' ] = array(
						'color' => $heading_text_color,
					);
				}

				if ( isset( $data['colors']['content_text_color'] ) ) {
					$content_text_color                                                                    = $data['colors']['content_text_color'];
					$this->customizer_css['desktop'][ '.' . $section_key . ' .wfacp-testi-content-color' ] = array(
						'color' => $content_text_color,
					);
				}
			} elseif ( strpos( $section_key, 'wfacp_promises_' ) !== false ) {
				$data = array();

				/* Promises Contents */

				$text_alignment                        = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'text_alignment' );
				$hide_text                             = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'hide_text' );
				$show_divider                          = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'show_divider' );
				$divider_color                         = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'divider_color' );
				$select_badge_structure                = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'select_badge_structure' );
				$promises_data['icon_text']            = WFACP_Common::get_option( $section_key . '_section_promise_icon_text' );
				$promises_data['show_promise_section'] = WFACP_Common::get_option( $section_key . '_section_show_promise_section' );

				$promises_data['text_alignment']         = $text_alignment;
				$promises_data['hide_text']              = $hide_text;
				$promises_data['show_divider']           = $show_divider;
				$promises_data['divider_color']          = $divider_color;
				$promises_data['select_badge_structure'] = $select_badge_structure;

				/* Advanced Setting */
				$data['advance_setting'] = $this->get_advance_setting( $section_key, $selected_template_slug );

				/* Color Setting */
				$data['colors']                               = $this->get_color_setting( $section_key, $selected_template_slug );
				$data['promises_data']                        = $promises_data;
				$this->customizer_fields_data[ $section_key ] = $data;

				$this->prepare_dynamic_style( $data, $section_key );
				$align_position = 'center';

				if ( $text_alignment == 'wfacp-text-left' ) {
					$align_position = 'left';
				} elseif ( $text_alignment == 'wfacp-text-right' ) {
					$align_position = 'right';
				}

				if ( isset( $this->customizer_css['desktop'][ '.' . $section_key . ' p ' ] ) ) {
					$p_content                                                     = $this->customizer_css['desktop'][ '.' . $section_key . ' p ' ];
					$p_content['text-align']                                       = $align_position;
					$this->customizer_css['desktop'][ '.' . $section_key . ' p ' ] = $p_content;
				}

				/* Section Style */

				if ( isset( $promises_data['divider_color'] ) ) {
					$show_divider                                                                                            = $promises_data['divider_color'];
					$this->customizer_css['desktop'][ '.' . $section_key . ' .wfacp-permission-icon ul li' ]['border-color'] = $show_divider;
				}
			} elseif ( strpos( $section_key, 'wfacp_assurance_' ) !== false ) {

				$data           = [];
				$assurance_data = [];

				$data['heading_section'] = $this->get_heading_section( $section_key, $selected_template_slug );

				/* Assurance Data  */
				$hide_title         = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'hide_title' );
				$enable_divider     = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'enable_divider' );
				$divider_color      = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'divider_color' );
				$mwidget_show_image = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'mwidget_show_image' );

				$assurance_data['hide_title']     = $hide_title;
				$assurance_data['enable_divider'] = $enable_divider;

				$assurance_data['list']                          = WFACP_Common::get_option( $section_key . '_section_mwidget_listw' );
				$data['assurance_data']                          = $assurance_data;
				$data['assurance_data']['section_mwidget_listw'] = $mwidget_show_image;

				/* Advanced Setting */
				$data['advance_setting']                      = $this->get_advance_setting( $section_key, $selected_template_slug );
				$this->customizer_fields_data[ $section_key ] = $data;

				/* Color Setting */
				$data['colors']                  = $this->get_color_setting( $section_key, $selected_template_slug );
				$data['colors']['divider_color'] = $divider_color;

				$this->customizer_fields_data[ $section_key ] = $data;

				$this->prepare_dynamic_style( $data, $section_key );

				if ( isset( $data['colors']['heading_text_color'] ) ) {
					$this->customizer_css['desktop'][ '.' . $section_key . ' h2' ] = array(
						'color' => $data['colors']['heading_text_color'],
					);
				}

				if ( isset( $data['colors']['divider_color'] ) ) {
					$this->customizer_css['desktop'][ '.' . $section_key . ' .wfacp-information-container .wfacp_enable_border' ]['border-color'] = $divider_color;
				}
			} elseif ( strpos( $section_key, 'wfacp_customer_' ) !== false ) {

				/*  Section Heading */
				$data                    = [];
				$data['heading_section'] = $this->get_heading_section( $section_key, $selected_template_slug );

				/*  Section Customer Support Data */
				$customer_support                              = array();
				$customer_support['supporter_name']            = WFACP_Common::get_option( $section_key . '_section_supporter_name' );
				$customer_support['supporter_image']           = WFACP_Common::get_option( $section_key . '_section_supporter_image' );
				$customer_support['supporter_designation']     = WFACP_Common::get_option( $section_key . '_section_supporter_designation' );
				$customer_support['supporter_signature_image'] = WFACP_Common::get_option( $section_key . '_section_supporter_signature_image' );
				$customer_support['contact_heading']           = WFACP_Common::get_option( $section_key . '_section_contact_heading' );
				$customer_support['contact_description']       = WFACP_Common::get_option( $section_key . '_section_contact_description' );
				$customer_support['contact_chat']              = WFACP_Common::get_option( $section_key . '_section_contact_chat' );
				$customer_support['contact_timing']            = WFACP_Common::get_option( $section_key . '_section_contact_timing' );
				$data['customer_support']                      = $customer_support;

				/* Sub heading section */
				$data['sub_heading_section'] = $this->get_sub_heading_section( $section_key, $selected_template_slug );

				/* Advanced Setting */
				$data['advance_setting'] = $this->get_advance_setting( $section_key, $selected_template_slug );

				$this->customizer_fields_data[ $section_key ] = $data;

				$this->prepare_dynamic_style( $data, $section_key );

				/* Color Setting */
				$data_keys = $this->get_section_keys_data( $section_key );
				if ( is_array( $data_keys ) && count( $data_keys ) ) {
					$color_meta = [
						'panel'    => $section_key,
						'section'  => 'section',
						'template' => $selected_template_slug,
						'key'      => 'colors',
					];
					$this->assign_colors( $data_keys, $color_meta );
				}

				$this->wfacp_font_size( $data['sub_heading_section'], array(
					'section_key' => $section_key,
					'target_to'   => '.' . $section_key . ' .wfacp-subtitle',
					'source_from' => 'heading_fs',
				) );
			} elseif ( $section_key == 'wfacp_header' ) {
				$data = array();


				if ( $selected_template_slug == 'layout_9' ) {
					$data['header_layout'] = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'header_layout' );
					$this->set_temaplete_header_layout( $data['header_layout'] );

				}

				/* Advanced Setting */
				$data['advance_setting'] = $this->get_advance_setting( $section_key, $selected_template_slug );

				//$data['colors'] = $this->get_color_setting( $section_key, $selected_template_slug );

				/* Header data  */
				$data['header_data']['logo']                 = WFACP_Common::get_option( $section_key . '_section_logo' );
				$data['header_data']['logo_link']            = WFACP_Common::get_option( $section_key . '_section_logo_link' );
				$data['header_data']['logo_link_target']     = WFACP_Common::get_option( $section_key . '_section_logo_link_target' );
				$data['header_data']['logo_width']           = WFACP_Common::get_option( $section_key . '_section_logo_width' );
				$data['header_data']['page_meta_title']      = WFACP_Common::get_option( $section_key . '_section_page_meta_title' );
				$data['header_data']['header_text']          = WFACP_Common::get_option( $section_key . '_section_header_text' );
				$data['header_data']['email']                = WFACP_Common::get_option( $section_key . '_section_email' );
				$data['header_data']['phone']                = WFACP_Common::get_option( $section_key . '_section_phone' );
				$data['header_data']['tel_number']           = WFACP_Common::get_option( $section_key . '_section_tel_number' );
				$data['header_data']['helpdesk_url']         = WFACP_Common::get_option( $section_key . '_section_helpdesk_url' );
				$data['header_data']['helpdesk_text']        = WFACP_Common::get_option( $section_key . '_section_helpdesk_text' );
				$data['header_data']['helpdesk_link_target'] = WFACP_Common::get_option( $section_key . '_section_helpdesk_link_target' );

				if ( isset( $data['header_layout'] ) && $data['header_layout'] == 'outside_header' ) {

					$data['colors']['section_bg_color']   = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'outside_section_bg_color' );
					$data['colors']['header_icon_color']  = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'outside_header_icon_color' );
					$data['colors']['content_text_color'] = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'outside_content_text_color' );

				} else {
					$data['colors']['section_bg_color']   = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'section_bg_color' );
					$data['colors']['header_icon_color']  = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'header_icon_color' );
					$data['colors']['content_text_color'] = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'content_text_color' );

				}

				$header_icon_color  = $data['colors']['header_icon_color'];
				$content_text_color = $data['colors']['content_text_color'];

				if ( isset( $header_icon_color ) ) {

					$this->customizer_css['desktop']['.wfacp_header span.wfacp-hd-list-help:before']['color']  = $header_icon_color;
					$this->customizer_css['desktop']['.wfacp_header span.wfacp-hd-list-email:before']['color'] = $header_icon_color;
					$this->customizer_css['desktop']['.wfacp_header span.wfacp-hd-list-phn:before']['color']   = $header_icon_color;

				}

				if ( isset( $content_text_color ) ) {

					$this->customizer_css['desktop']['.wfacp_header .wfacp-header-nav ul li']['color']   = $content_text_color;
					$this->customizer_css['desktop']['.wfacp_header .wfacp-header-nav ul li a']['color'] = $content_text_color;
				}

				if ( isset( $data['header_data']['logo_width'] ) ) {
					$this->customizer_css['desktop']['.wfacp_header .wfacp-logo'] = array(
						'max-width' => $data['header_data']['logo_width'] . 'px',
					);

				}

				/* Prepare Dynamic Style */
				$this->prepare_dynamic_style( $data, $section_key );

				/* Assign Data to customizer fields */
				$this->customizer_fields_data[ $section_key ] = $data;

			} elseif ( $section_key == 'wfacp_footer' ) {

				$data = [];

				/* Footer Data */

				$data['footer_data']['ft_ct_content'] = WFACP_Common::get_option( $section_key . '_section_ft_text' );
				$data['footer_data']['ft_text_fs']    = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'ft_text_fs' );

				/* colors Setting */
				$data['colors'] = $this->get_color_setting( $section_key, $selected_template_slug );

				if ( isset( $data['colors']['content_text_color'] ) ) {
					$content_text_color                                            = $data['colors']['content_text_color'];
					$this->customizer_css['desktop'][ '.' . $section_key . ' p' ]  = array(
						'color' => $content_text_color,
					);
					$this->customizer_css['desktop'][ '.' . $section_key . ' li' ] = array(
						'color' => $content_text_color,
					);
					$this->customizer_css['desktop'][ '.' . $section_key . ' a' ]  = array(
						'color' => $content_text_color,
					);
					$this->customizer_css['desktop'][ '.' . $section_key ]         = array(
						'color' => $content_text_color,
					);
				}

				/* Prepare Dynamic Style */
				$this->prepare_dynamic_style( $data, $section_key );

				/* Assign Data to customizer fields */
				$this->customizer_fields_data[ $section_key ] = $data;

				$this->wfacp_font_size( $data['footer_data'], array(
					'section_key' => 'wfacp_footer',
					'target_to'   => '.' . $section_key . ' .wfacp-footer-text',
					'source_from' => 'ft_text_fs',
				) );
				$this->wfacp_font_size( $data['footer_data'], array(
					'section_key' => 'wfacp_footer',
					'target_to'   => '.' . $section_key . ' .wfacp-footer-text p',
					'source_from' => 'ft_text_fs',
				) );

			} elseif ( strpos( $section_key, 'wfacp_product' ) !== false ) {
				$data = array();

				$layout_key = '';
				if ( isset( $selected_template_slug ) && $selected_template_slug != '' ) {
					$layout_key = $selected_template_slug . '_';
				}

				if ( $selected_template_slug == 'layout_9' ) {
					$data['heading_section'] = $this->get_heading_section( $section_key, $selected_template_slug );
					$data['advance_setting'] = $this->get_advance_setting( $section_key, $selected_template_slug );

					$data['product_data']['product_layouts'] = WFACP_Common::get_option( $section_key . '_section_layouts' );

				}

				$data['product_data']['layouts'] = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'section_height' );
				$product_section_height          = $data['product_data']['layouts'];

				$data['product_data']['enable_product_section'] = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'enable_product_section' );

				if ( isset( $data['product_data']['enable_product_section'] ) && $data['product_data']['enable_product_section'] == true ) {
					if ( $selected_template_slug == 'layout_1' || $selected_template_slug == 'layout_4' ) {

						$this->customizer_css['desktop'][ 'body .' . $section_key ] = array(
							'min-height' => $product_section_height . 'px',
						);
						$this->customizer_css['mobile'][ 'body .' . $section_key ]  = array(
							'min-height' => '1px',
						);
					}
				} else {

					$this->customizer_css['desktop'][ 'body .' . $section_key ] = array(
						'min-height' => '1px',
					);
					$this->customizer_css['mobile'][ 'body .' . $section_key ]  = array(
						'min-height' => '1px',
					);
				}

				$data['product_data']['section_height'] = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'section_height' );

				/* Product Data */

				$data['product_data']['enable_product_image'] = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'enable_product_image' );
				$data['product_data']['product_image']        = WFACP_Common::get_option( $section_key . '_section_product_image' );
				$data['product_data']['title']                = WFACP_Common::get_option( $section_key . '_section_title' );
				$data['product_data']['title_fs']             = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'title_fs' );

				$data['product_data']['desc']    = WFACP_Common::get_option( $section_key . '_section_desc' );
				$data['product_data']['desc_fs'] = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'desc_fs' );

				/* colors Setting */
				$data['colors'] = $this->get_color_setting( $section_key, $selected_template_slug );

				/* Prepare Dynamic Style */
				$this->prepare_dynamic_style( $data, $section_key );

				/* Assign Data to customizer fields */
				$this->customizer_fields_data[ $section_key ] = $data;

				if ( isset( $data['colors']['heading_text_color'] ) ) {
					$heading_text_color                                                             = $data['colors']['heading_text_color'];
					$this->customizer_css['desktop'][ '.' . $section_key . ' .wfacp_heading_text' ] = array(
						'color' => $heading_text_color,
					);
				}

				$this->wfacp_font_size( $data['product_data'], array(
					'section_key' => $section_key,
					'target_to'   => '.' . $section_key . ' .wfacp_heading_text',
					'source_from' => 'title_fs',
				) );
				$this->wfacp_font_size( $data['product_data'], array(
					'section_key' => $section_key,
					'target_to'   => '.' . $section_key . ' p',
					'source_from' => 'desc_fs',
				) );

			} elseif ( $section_key == 'wfacp_gbadge' ) {

				$layout_key = '';
				if ( isset( $selected_template_slug ) && $selected_template_slug != '' ) {
					$layout_key = $selected_template_slug . '_';
				}

				$data = array();

				$data['gbadge_data']['enable_icon']       = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'enable_icon' );
				$data['gbadge_data']['badge_icon']        = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'badge_icon' );
				$data['gbadge_data']['custom_list_image'] = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'custom_list_image' );
				$data['gbadge_data']['badge_max_width']   = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'badge_max_width' );

				$data['gbadge_data']['badge_margin_left'] = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'badge_margin_left' );
				$data['gbadge_data']['badge_margin_top']  = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'badge_margin_top' );

				if ( is_array( $this->default_badges ) && count( $this->default_badges ) > 0 ) {
					if ( isset( $data['gbadge_data']['badge_icon'] ) && $data['gbadge_data']['badge_icon'] != '' ) {
						$bage_icon                             = $data['gbadge_data']['badge_icon'];
						$data['gbadge_data']['badge_icon_src'] = $this->default_badges[ $bage_icon ];
					}
				}

				$this->customizer_fields_data[ $section_key ] = $data;

				/* Prepare Dynamic Style */
				$this->prepare_dynamic_style( $data, $section_key );

				if ( isset( $data['gbadge_data']['badge_max_width'] ) ) {
					$badge_max_width                                                             = $data['gbadge_data']['badge_max_width'] . 'px';
					$this->customizer_css['desktop'][ '.' . $section_key . ' .wfacp_max_width' ] = array(
						'max-width' => $badge_max_width,
					);

				}

				if ( isset( $data['gbadge_data']['badge_margin_left'] ) ) {
					$badge_margin_left                                                             = $data['gbadge_data']['badge_margin_left'] . 'px';
					$this->customizer_css['desktop'][ '.' . $section_key . ' img' ]['margin-left'] = $badge_margin_left;
					$this->customizer_css['mobile'][ '.' . $section_key . ' img' ]['margin-left']  = 'initial';
				}

				if ( isset( $data['gbadge_data']['badge_margin_top'] ) ) {
					$badge_margin_top = $data['gbadge_data']['badge_margin_top'] . 'px';

					$this->customizer_css['desktop'][ '.' . $section_key . ' img' ]['margin-top'] = $badge_margin_top;
					$this->customizer_css['mobile'][ '.' . $section_key . ' img' ]['margin-top']  = 'initial';
				}
			} elseif ( $section_key == 'wfacp_style' ) {

				$layout_key = '';
				if ( isset( $selected_template_slug ) && $selected_template_slug != '' ) {
					$layout_key = $selected_template_slug . '_';
				}
				$data = array();

				$data['wfacp_style']['body_background_color'] = WFACP_Common::get_option( $section_key . '_colors_' . $layout_key . 'body_background_color' );

				$data['wfacp_style']['sidebar_background_color'] = WFACP_Common::get_option( $section_key . '_colors_' . $layout_key . 'sidebar_background_color' );

				$data['wfacp_style']['content_fs'] = WFACP_Common::get_option( $section_key . '_typography_' . $layout_key . 'content_fs' );
				$data['wfacp_style']['content_ff'] = WFACP_Common::get_option( $section_key . '_typography_' . $layout_key . 'content_ff' );

				if ( isset( $data['wfacp_style']['content_fs']['desktop'] ) && $data['wfacp_style']['content_fs']['desktop'] != '' ) {
					$desktop_fs                                                                                                                    = $data['wfacp_style']['content_fs']['desktop'] + 4;
					$this->customizer_css['desktop']['.wfacp_main_wrapper .wc-amazon-payments-advanced-populated .create-account h3']['font-size'] = $desktop_fs . 'px';
				}

				if ( isset( $data['wfacp_style']['content_fs']['mobile'] ) && $data['wfacp_style']['content_fs']['mobile'] != '' ) {
					$mobile_fs                                                                                                                     = $data['wfacp_style']['content_fs']['mobile'] + 4;
					$this->customizer_css['desktop']['.wfacp_main_wrapper .wc-amazon-payments-advanced-populated .create-account h3']['font-size'] = $mobile_fs . 'px';
				}

				if ( isset( $data['wfacp_style']['content_ff'] ) && $data['wfacp_style']['content_ff'] != '' ) {
					$this->selected_font_family = $data['wfacp_style']['content_ff'];
				}

				$this->prepare_dynamic_style( $data, $section_key );

				if ( isset( $data['wfacp_style']['body_background_color'] ) ) {
					$this->customizer_css['desktop']['body']                  = array(
						'background' => $data['wfacp_style']['body_background_color'],
					);
					$this->customizer_css['desktop']['.wfacp-main-container'] = array(
						'background' => $data['wfacp_style']['body_background_color'],
					);

					if ( $selected_template_slug == 'layout_2' ) {
						$this->customizer_css['desktop']['body .wfacp-panel-wrapper'] ['background'] = $data['wfacp_style']['body_background_color'];

					}
				}

				if ( isset( $data['wfacp_style']['sidebar_background_color'] ) ) {

					$this->customizer_css['desktop']['.wfacp-right-panel']                                                       = array(
						'background' => $data['wfacp_style']['sidebar_background_color'],
					);
					$this->customizer_css['mobile'][ '.wfacp-mobile .' . $selected_template_slug . '_temp.wfacp-panel-wrapper' ] = array(
						'background' => $data['wfacp_style']['sidebar_background_color'],
					);
					$this->customizer_css['mobile'][ '.wfacp-mobile .' . $selected_template_slug . '_temp .wfacp-form' ]         = array(
						'margin-bottom' => '5px',
					);

					if ( $selected_template_slug == 'layout_9' ) {
						$this->customizer_css['desktop'][ 'body.wfacp_cls_' . $selected_template_slug . ' .wfacp-panel-wrapper.wfacp_outside_header:before' ]['background'] = $data['wfacp_style']['sidebar_background_color'];
						$this->customizer_css['mobile'][ 'body.wfacp_cls_' . $selected_template_slug . ' .wfacp-panel-wrapper:before' ]['display']                          = 'none';
					}
				}

				$this->wfacp_font_size( $data[ $section_key ], array(
					'section_key' => $section_key,
					'target_to'   => 'body p',
					'source_from' => 'content_fs',
				) );

				$this->wfacp_font_size( $data[ $section_key ], array(
					'section_key' => $section_key,
					'target_to'   => '.wfacp-comm-inner-inf p',
					'source_from' => 'content_fs',
				) );

				/* update layout 4 fonts */
				if ( $selected_template_slug == 'layout_4' ) {
					$this->wfacp_font_size( $data[ $section_key ], array(
						'section_key' => $section_key,
						'target_to'   => '.wfacp-list-panel p',
						'source_from' => 'content_fs',
					) );
					$this->wfacp_font_size( $data[ $section_key ], array(
						'section_key' => $section_key,
						'target_to'   => '.wfacp-testing-text p',
						'source_from' => 'content_fs',
					) );
				}

				$this->customizer_fields_data['wfacp_style'] = $data;

			} elseif ( $section_key == 'wfacp_form' ) {
				$data                    = array();
				$data['heading_section'] = $this->get_heading_section( $section_key, $selected_template_slug );
				$data['colors']          = $this->get_color_setting( $section_key, $selected_template_slug );

				$layout_key = '';
				if ( isset( $selected_template_slug ) && $selected_template_slug != '' ) {
					$layout_key = $selected_template_slug . '_';
					if ( 'layout_9' == $selected_template_slug ) {
						$data['order_hide_img'] = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'order_hide_img' );
					}
				}

				if ( $selected_template_slug == 'layout_9' ) {

					$data['form_data']['cart_collapse_title'] = WFACP_Common::get_option( $section_key . '_section_cart_collapse_title' );
					$data['form_data']['cart_expanded_title'] = WFACP_Common::get_option( $section_key . '_section_cart_expanded_title' );
				}

				/* heading section */
				$data['heading_section'] = $this->get_heading_section( $section_key, $selected_template_slug );

				/* Sub heading section */
				$data['sub_heading_section'] = $this->get_sub_heading_section( $section_key, $selected_template_slug );

				/* Field Style */
				$data['form_data']['field_style']                = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'field_style_fs' );
				$data['form_data']['field_style']['focus_color'] = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'field_focus_color' );

				$data['form_data']['text_below_placeorder_btn'] = WFACP_Common::get_option( $section_key . '_section_text_below_placeorder_btn' );

				/* product switcher */

				$data['form_data']['best_value']['best_value_product']    = WFACP_Common::get_option( $section_key . '_section_best_value_product' );
				$data['form_data']['best_value']['best_value_text']       = WFACP_Common::get_option( $section_key . '_section_best_value_text' );
				$data['form_data']['best_value']['best_value_text_color'] = WFACP_Common::get_option( $section_key . '_section_best_value_text_color' );
				$data['form_data']['best_value']['best_value_bg_color']   = WFACP_Common::get_option( $section_key . '_section_best_value_bg_color' );

				$data['form_data']['field_style_position'] = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'field_style_position' );

				$data['border']['form_head']['border-style']  = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'rbox_border_type' );
				$data['border']['form_head']['border-width']  = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'rbox_border_width' );
				$data['border']['form_head']['border-color']  = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'rbox_border_color' );
				$data['border']['form_head']['padding-left']  = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'rbox_padding' );
				$data['border']['form_head']['padding-right'] = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'rbox_padding' );


				$data['border']['form_field_style']['border-style'] = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'field_border_layout' );
				$data['border']['form_field_style']['border-width'] = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'field_border_width' );
				$data['border']['form_field_style']['border-color'] = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'field_border_color' );

				$data['form_data']['payment_methods_heading']     = WFACP_Common::get_option( $section_key . '_section_payment_methods_heading' );
				$data['form_data']['payment_methods_sub_heading'] = WFACP_Common::get_option( $section_key . '_section_payment_methods_sub_heading' );

				if ( is_array( $data['border']['form_head'] ) && count( $data['border']['form_head'] ) > 0 ) {
					foreach ( $data['border']['form_head'] as $key1 => $value1 ) {

						$unit = 'px';
						if ( $key1 == 'border-color' || $key1 == 'border-style' ) {
							$unit = '';
						}
						$this->customizer_css['desktop']['.wfacp_main_form .wfacp-comm-title'][ $key1 ] = $value1 . $unit;
					}
				}


				if ( is_array( $data['border']['form_field_style'] ) && count( $data['border']['form_field_style'] ) > 0 ) {
					foreach ( $data['border']['form_field_style'] as $key1 => $value1 ) {

						$unit = 'px';
						if ( $key1 == 'border-color' || $key1 == 'border-style' ) {
							$unit = '';
						}

						$this->customizer_css['desktop']['body .wfacp_main_form .woocommerce-input-wrapper select.wfacp-form-control'][ $key1 ]                       = $value1 . $unit;
						$this->customizer_css['desktop']['body .wfacp_main_form .woocommerce-input-wrapper .wfacp-form-control'][ $key1 ]                             = $value1 . $unit;
						$this->customizer_css['desktop']['body .wfacp_main_form .woocommerce-input-wrapper .wfacp-form-control-wrapper input'][ $key1 ]               = $value1 . $unit;
						$this->customizer_css['desktop']['body .wfacp_main_form .woocommerce-input-wrapper .wfacp-form-control-wrapper select'][ $key1 ]              = $value1 . $unit;
						$this->customizer_css['desktop']['body .wfacp_main_form select'][ $key1 ]                                                                     = $value1 . $unit;
						$this->customizer_css['desktop']['body .wfacp_main_form .select2-container .select2-selection--single .select2-selection__rendered'][ $key1 ] = $value1 . $unit . " !important";
						$this->customizer_css['desktop']['body .wfacp_main_form .form-row:not(.woocommerce-invalid-required-field) select:focus'][ $key1 ]            = $value1 . $unit . " !important";


						$this->customizer_css['desktop']['body .wfacp_main_form .wfacp_allowed_countries strong'][ $key1 ] = $value1 . $unit;

						$this->customizer_css['desktop']['body .wfacp_main_form select.wfacp-form-control'][ $key1 ] = $value1 . $unit;
						$this->customizer_css['desktop']['body .wfacp_main_form .wfacp-form-control'][ $key1 ]       = $value1 . $unit;

						$this->customizer_css['desktop']['body .wfacp_main_form .wc-amazon-payments-advanced-populated .create-account p.form-row input[type="text"]'][ $key1 ]     = $value1 . $unit;
						$this->customizer_css['desktop']['body .wfacp_main_form .wc-amazon-payments-advanced-populated .create-account p.form-row input[type="email"]'][ $key1 ]    = $value1 . $unit;
						$this->customizer_css['desktop']['body .wfacp_main_form .wc-amazon-payments-advanced-populated .create-account p.form-row input[type="password"]'][ $key1 ] = $value1 . $unit;
						$this->customizer_css['desktop']['body .wfacp_main_form .wc-amazon-payments-advanced-populated .create-account p.form-row select'][ $key1 ]                 = $value1 . $unit;

						if ( $key1 !== 'border-color' ) {

							$this->customizer_css['desktop']['body .wfacp_main_form .form-row:not(.woocommerce-invalid-email) input[type=email]:hover'][ $key1 ]                                                                   = $value1 . $unit;
							$this->customizer_css['desktop']['body .wfacp_main_form .form-row:not(.woocommerce-invalid-required-field) input[type=password]:hover'][ $key1 ]                                                       = $value1 . $unit;
							$this->customizer_css['desktop']['body .wfacp_main_form .form-row:not(.woocommerce-invalid-required-field) input[type=search]:hover'][ $key1 ]                                                         = $value1 . $unit;
							$this->customizer_css['desktop']['body .wfacp_main_form .form-row:not(.woocommerce-invalid-required-field) input[type=tel]:hover'][ $key1 ]                                                            = $value1 . $unit;
							$this->customizer_css['desktop']['body .wfacp_main_form .form-row:not(.woocommerce-invalid-required-field) input[type=text]:hover'][ $key1 ]                                                           = $value1 . $unit;
							$this->customizer_css['desktop']['body .wfacp_main_form .form-row:not(.woocommerce-invalid-required-field) input[type=url]:hover'][ $key1 ]                                                            = $value1 . $unit;
							$this->customizer_css['desktop']['body .wfacp_main_form .form-row:not(.woocommerce-invalid-required-field) textarea:hover'][ $key1 ]                                                                   = $value1 . $unit;
							$this->customizer_css['desktop']['body .wfacp_main_form .form-row:not(.woocommerce-invalid-required-field) select:hover'][ $key1 ]                                                                     = $value1 . $unit;
							$this->customizer_css['desktop']['body .wfacp_main_form .form-row:not(.woocommerce-invalid-required-field) .select2-container .select2-selection--single .select2-selection__rendered:hover'][ $key1 ] = $value1 . $unit . " !important";
						}
					}
				}

				/* Set data on global Field */
				$this->customizer_fields_data['wfacp_form'] = $data;

				/*  Color field*/
				$data_keys       = $this->get_section_keys_data( 'wfacp_form' );
				$form_color_meta = [
					'panel'    => $section_key,
					'section'  => 'section',
					'template' => $selected_template_slug,
					'key'      => 'colors',
				];
				$this->assign_colors( $data_keys, $form_color_meta );

				/* add button styling */
				$num_of_steps = $this->get_step_count();

				$bread_crumb_count = $num_of_steps + 1;

				if ( $bread_crumb_count > 1 ) {
					$steps_text = [];
					for ( $bi = 0; $bi < $bread_crumb_count; $bi ++ ) {
						$step_text_here = WFACP_Common::get_option( $section_key . '_section_breadcrumb_' . $bi . '_step_text' );

						if ( isset( $step_text_here ) && $step_text_here != '' ) {
							$steps_text[] = $step_text_here;
							unset( $step_text_here );
						}
					}

					if ( is_array( $steps_text ) && count( $steps_text ) > 0 ) {
						$this->customizer_fields_data['wfacp_form']['form_data']['breadcrumb'] = $steps_text;
					}
				}
				if ( $num_of_steps > 1 ) {

					$_enable_cart_in_breadcrumb = WFACP_Common::get_option( $section_key . '_section_' . $selected_template_slug . "_enable_cart_in_breadcrumb" );
					$cart_text                  = WFACP_Common::get_option( $section_key . '_section_cart_text' );

					$this->customizer_fields_data['wfacp_form']['form_data']['breadcrumb_before'] = [
						'enable_cart'      => $_enable_cart_in_breadcrumb,
						'enable_cart_text' => $cart_text,
					];

				}

				$step_btns = [ 'order-place' ];
				if ( $num_of_steps > 1 ) {
					$text        = 'next';
					$step_btns[] = $text;

				}
				$btn_arr = [];
				foreach ( $step_btns as $skey => $svalue ) {
					$st_id = '';

					$btn_text[ $svalue ] = WFACP_Common::get_option( $section_key . '_section_' . $selected_template_slug . '_btn_' . $svalue . '_btn_text' );


					if ( isset( $btn_text['next'] ) && $btn_text['next'] != '' ) {
						$btn_text['back'] = WFACP_Common::get_option( $section_key . '_section_' . $selected_template_slug . '_btn_back_btn_text' );

					}

					$btn_class_key        = 'button';
					$btn_parent_class_key = 'wfacp-' . $svalue . '-btn-wrap';
					if ( $svalue == 'order-place' ) {
						$st_id = '#place_order';
					} elseif ( $svalue == 'next' ) {
						$svalue = 'order-place';
					}

					$btn_wrap_class = '.wfacp_main_form .' . $btn_parent_class_key;

					$btn_class = 'body .wfacp_main_form .woocommerce-checkout .button.' . $btn_class_key . $st_id;


					$width                        = WFACP_Common::get_option( $section_key . '_section_' . $selected_template_slug . '_btn_' . $svalue . '_width' );
					$btn_talign                   = WFACP_Common::get_option( $section_key . '_section_' . $selected_template_slug . '_btn_' . $svalue . '_talign' );
					$btn_font_weight              = WFACP_Common::get_option( $section_key . '_section_' . $selected_template_slug . '_btn_' . $svalue . '_btn_font_weight' );
					$make_button_sticky_on_mobile = WFACP_Common::get_option( $section_key . '_section_' . $selected_template_slug . '_btn_' . $svalue . '_make_button_sticky_on_mobile' );
					$btn_fs                       = WFACP_Common::get_option( $section_key . '_section_' . $selected_template_slug . '_btn_' . $svalue . '_fs' );
					$btn_top_bottom_padding       = WFACP_Common::get_option( $section_key . '_section_' . $selected_template_slug . '_btn_' . $svalue . '_top_bottom_padding' );
					$left_right_padding           = WFACP_Common::get_option( $section_key . '_section_' . $selected_template_slug . '_btn_' . $svalue . '_left_right_padding' );
					$border_radius                = WFACP_Common::get_option( $section_key . '_section_' . $selected_template_slug . '_btn_' . $svalue . '_border_radius' );


					$btn_arr = [
						'btn_text'                     => $btn_text,
						'width'                        => $width,
						'talign'                       => $btn_talign,
						'btn_font_weight'              => $btn_font_weight,
						'make_button_sticky_on_mobile' => $make_button_sticky_on_mobile,
						'fs'                           => $btn_fs,
						'top_bottom_padding'           => $btn_top_bottom_padding,
						'left_right_padding'           => $left_right_padding,
						'border_radius'                => $border_radius,

					];


					$this->customizer_css['desktop'][ 'body .wfacp_main_form .woocommerce-checkout .' . $btn_parent_class_key ]['text-align'] = $btn_talign;

					$this->customizer_css['desktop'][ $btn_class ]['padding-top']    = $btn_top_bottom_padding . 'px';
					$this->customizer_css['desktop'][ $btn_class ]['padding-bottom'] = $btn_top_bottom_padding . 'px';
					$this->customizer_css['desktop'][ $btn_class ]['font-weight']    = $btn_font_weight;

					$this->customizer_css['desktop'][ $btn_class ]['padding-left']  = $left_right_padding . 'px';
					$this->customizer_css['desktop'][ $btn_class ]['padding-right'] = $left_right_padding . 'px';
					$this->customizer_css['desktop'][ $btn_class ]['border-radius'] = $border_radius . 'px';

					$this->customizer_css['desktop'][ $btn_class ]['width'] = $width;

					if ( isset( $data['form_data']['field_style']['focus_color'] ) ) {
						$field_focus_color = $data['form_data']['field_style']['focus_color'];

						$this->customizer_css['desktop']['body .wfacp_main_form .form-row:not(.woocommerce-invalid-email) input[type=email]:focus']['box-shadow']             = "0 0 0 1px $field_focus_color";
						$this->customizer_css['desktop']['body .wfacp_main_form .form-row:not(.woocommerce-invalid-required-field) input[type=password]:focus']['box-shadow'] = "0 0 0 1px $field_focus_color";
						$this->customizer_css['desktop']['body .wfacp_main_form .form-row:not(.woocommerce-invalid-required-field) input[type=search]:focus']['box-shadow']   = "0 0 0 1px $field_focus_color";
						$this->customizer_css['desktop']['body .wfacp_main_form .form-row:not(.woocommerce-invalid-required-field) input[type=tel]:focus']['box-shadow']      = "0 0 0 1px $field_focus_color";
						$this->customizer_css['desktop']['body .wfacp_main_form .form-row:not(.woocommerce-invalid-required-field) input[type=text]:focus']['box-shadow']     = "0 0 0 1px $field_focus_color";
						$this->customizer_css['desktop']['body .wfacp_main_form .form-row:not(.woocommerce-invalid-required-field) input[type=url]:focus']['box-shadow']      = "0 0 0 1px $field_focus_color";
						$this->customizer_css['desktop']['body .wfacp_main_form .form-row:not(.woocommerce-invalid-required-field) textarea:focus']['box-shadow']             = "0 0 0 1px $field_focus_color";

						$this->customizer_css['desktop']['body .wfacp_main_form .form-row:not(.woocommerce-invalid-required-field) select:focus']['box-shadow']                                                                     = "0 0 0 1px $field_focus_color";
						$this->customizer_css['desktop']['body .wfacp-right-panel #coupon_code:focus']['box-shadow']                                                                                                                = "0 0 0 1px $field_focus_color !important";
						$this->customizer_css['desktop']['form.checkout_coupon.woocommerce-form-coupon .wfacp-col-left-half input:focus']['border-color']                                                                           = "$field_focus_color !important;";
						$this->customizer_css['desktop']['body .wfacp_main_form .form-row:not(.woocommerce-invalid-required-field) .woocommerce-input-wrapper .select2-container .select2-selection--single:focus']['border-color'] = "$field_focus_color !important;";

						$this->customizer_css['desktop']['body .wfacp_main_form .form-row:not(.woocommerce-invalid-required-field) .woocommerce-input-wrapper .select2-container .select2-selection--single:focus > span.select2-selection__rendered']['border-color'] = "$field_focus_color !important;";
						$this->customizer_css['desktop']['body .wfacp_main_form .form-row:not(.woocommerce-invalid-required-field) .woocommerce-input-wrapper .select2-container .select2-selection--single:focus']['box-shadow']                                      = "0 0 0 1px $field_focus_color !important";


					}

					if ( $width == 'initial' ) {
						$this->customizer_css['desktop']["_:-ms-fullscreen, :root body .wfacp_main_form .woocommerce-checkout .button.button"]['width'] = "auto !important";
					}


					if ( isset( $data['heading_section']['heading_fs'] ) ) {

						$subscription_target = 'body .wfacp_main_form .ia_subscription_items h3';

						$this->wfacp_font_size( $data['heading_section'], array(
							'section_key' => $section_key,
							'target_to'   => $subscription_target,
							'source_from' => 'heading_fs',
						) );

						if ( isset( $data['heading_section']['heading_talign'] ) ) {
							if ( $data['heading_section']['heading_talign'] == 'wfacp-text-left' ) {
								$align_pos = 'left';
							} elseif ( $data['heading_section']['heading_talign'] == 'wfacp-text-right' ) {
								$align_pos = 'right';
							} else {
								$align_pos = 'center';
							}
						}

						if ( isset( $data['heading_section']['heading_font_weight'] ) ) {
							if ( $data['heading_section']['heading_font_weight'] == 'wfacp-normal' ) {
								$font_weight_subs = 'normal';
							} else {
								$font_weight_subs = 'bold';
							}
						}

						$this->customizer_css['desktop'][ $subscription_target ]['text-align']  = $align_pos;
						$this->customizer_css['desktop'][ $subscription_target ]['font-weight'] = $font_weight_subs;

					}

					if ( isset( $data['heading_section']['heading_fs'] ) ) {

						$subscription_target = 'body .wfacp_main_form #woocommerce_eu_vat_compliance #woocommerce_eu_vat_compliance_vat_number h3';

						$this->wfacp_font_size( $data['heading_section'], array(
							'section_key' => $section_key,
							'target_to'   => $subscription_target,
							'source_from' => 'heading_fs',
						) );

						if ( isset( $data['heading_section']['heading_talign'] ) ) {
							if ( $data['heading_section']['heading_talign'] == 'wfacp-text-left' ) {
								$align_pos = 'left';
							} elseif ( $data['heading_section']['heading_talign'] == 'wfacp-text-right' ) {
								$align_pos = 'right';
							} else {
								$align_pos = 'center';
							}
						}

						if ( isset( $data['heading_section']['heading_font_weight'] ) ) {
							if ( $data['heading_section']['heading_font_weight'] == 'wfacp-normal' ) {
								$font_weight_subs = 'normal';
							} else {
								$font_weight_subs = 'bold';
							}
						}

						$this->customizer_css['desktop'][ $subscription_target ]['text-align']  = $align_pos;
						$this->customizer_css['desktop'][ $subscription_target ]['font-weight'] = $font_weight_subs;

					}

					if ( isset( $data['sub_heading_section']['heading_fs'] ) ) {

						$subscription_target = 'body .wfacp_main_form #woocommerce_eu_vat_compliance #woocommerce_eu_vat_compliance_vat_number h3 + p';

						$this->wfacp_font_size( $data['sub_heading_section'], array(
							'section_key' => $section_key,
							'target_to'   => $subscription_target,
							'source_from' => 'heading_fs',
						) );

						if ( isset( $data['sub_heading_section']['heading_talign'] ) ) {
							if ( $data['sub_heading_section']['heading_talign'] == 'wfacp-text-left' ) {
								$align_pos = 'left';
							} elseif ( $data['sub_heading_section']['heading_talign'] == 'wfacp-text-right' ) {
								$align_pos = 'right';
							} else {
								$align_pos = 'center';
							}
						}

						if ( isset( $data['sub_heading_section']['heading_font_weight'] ) ) {
							if ( $data['sub_heading_section']['heading_font_weight'] == 'wfacp-normal' ) {
								$font_weight_subs = 'normal';
							} else {
								$font_weight_subs = 'bold';
							}
						}

						$this->customizer_css['desktop'][ $subscription_target ]['text-align']  = $align_pos;
						$this->customizer_css['desktop'][ $subscription_target ]['font-weight'] = $font_weight_subs;

					}

					$this->wfacp_font_size( $btn_arr, array(
						'section_key' => $section_key,
						'target_to'   => $btn_class,
						'source_from' => 'fs',
					) );

				}

				$this->customizer_fields_data['wfacp_form']['form_data']['btn_details'] = $btn_arr;

				$this->prepare_dynamic_style( $data, 'wfacp_main_form' );

				/* Set Font Size */
				$this->wfacp_font_size( $data['form_data'], array(
					'section_key' => $section_key,
					'target_to'   => '.wfacp_main_form label.wfacp-form-control-label',
					'source_from' => 'field_style',
				) );
				$this->wfacp_font_size( $data['sub_heading_section'], array(
					'section_key' => $section_key,
					'target_to'   => '.wfacp_main_form .wfacp-comm-title h4',
					'source_from' => 'heading_fs',
				) );

			} elseif ( $section_key == 'wfacp_form_cart' ) {

				$layout_key = '';
				if ( isset( $selected_template_slug ) && $selected_template_slug != '' ) {
					$layout_key = $selected_template_slug . '_';
				}

				$data = array();

				if ( isset( $selected_template_slug ) && $selected_template_slug != '' ) {
					$layout_key = $selected_template_slug . '_';
					if ( 'layout_9' == $selected_template_slug ) {
						$data['order_hide_img'] = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'order_hide_img' );
					}
				}

				/*  Section Heading */

				$data['heading_section'] = $this->get_heading_section( $section_key, $selected_template_slug );

				$data_keys = $this->get_section_keys_data( 'wfacp_form_cart' );

				$form_color_meta = [
					'panel'    => $section_key,
					'section'  => 'section',
					'template' => $selected_template_slug,
					'key'      => 'colors',
				];
				$this->assign_colors( $data_keys, $form_color_meta );

				$data['advance_setting'] = $this->get_advance_setting( $section_key, $selected_template_slug );
				$data['colors']          = $this->get_color_setting( $section_key, $selected_template_slug );

				$this->customizer_fields_data[ $section_key ] = $data;

				$data['product_cart_coupon'] = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'order_hide_right_side_coupon' );

				$this->prepare_dynamic_style( $data, $section_key );

				/* Section Style */

				$this->customizer_fields_data[ $section_key ] = $data;

			} elseif ( $section_key == 'wfacp_form_product_switcher' ) {

				$data     = [];
				$products = WFACP_Common::get_page_product( WFACP_Common::get_id() );
				if ( is_array( $products ) && count( $products ) > 0 ) {

					$layout_key = '';
					if ( isset( $selected_template_slug ) && $selected_template_slug != '' ) {
						$layout_key = $selected_template_slug . '_';
					}

					foreach ( $products as $product_key => $val ) {
						$data['products'][ $product_key ]['heading']     = WFACP_Common::get_option( $section_key . '_section_' . $this->get_template_slug() . '_' . $product_key . '_heading' );
						$data['products'][ $product_key ]['description'] = WFACP_Common::get_option( $section_key . '_section_' . $this->get_template_slug() . '_' . $product_key . '_description' );

					}

					$data['data']['hide_section'] = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'hide_section' );

					$data['border']['product_switcher_head']['border-style'] = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'rbox_border_type' );
					$data['border']['product_switcher_head']['border-width'] = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'rbox_border_width' );
					$data['border']['product_switcher_head']['border-color'] = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'rbox_border_color' );
					$data['border']['product_switcher_head']['padding']      = WFACP_Common::get_option( $section_key . '_section_' . $layout_key . 'rbox_padding' );

					if ( is_array( $data['border']['product_switcher_head'] ) && count( $data['border']['product_switcher_head'] ) > 0 ) {
						foreach ( $data['border']['product_switcher_head'] as $key1 => $value1 ) {

							$unit = 'px';
							if ( $key1 == 'border-color' || $key1 == 'border-style' ) {
								$unit = '';
							}

							$this->customizer_css['desktop']['body .wfacp_main_form .wfacp_whats_included'][ $key1 ] = $value1 . $unit;
						}
					}

					$this->customizer_fields_data[ $section_key ] = $data;

					/*  Color field*/

					$data_keys = $this->get_section_keys_data( $section_key );

					$form_color_meta = [
						'panel'    => $section_key,
						'section'  => 'section',
						'template' => $selected_template_slug,
						'key'      => 'colors',
					];
					$this->assign_colors( $data_keys, $form_color_meta );

					$this->prepare_dynamic_style( $data, $section_key );

				}
			} elseif ( strpos( $section_key, 'wfacp_html_widget_' ) !== false ) {

				$section_key1 = 'html_widgets_' . $section_key;

				$data                                    = [];
				$data[ $section_key ]['advance_setting'] = $this->get_advance_setting( $section_key1, $selected_template_slug, true );


				/* Get html content data */
				$data[ $section_key ]['data'] = WFACP_Common::get_option( $section_key1 . '_html_content' );


				/* Color Setting  */
				$data[ $section_key ]['colors']               = $this->get_color_setting( $section_key1, $selected_template_slug, true );
				$this->customizer_fields_data[ $section_key ] = $data[ $section_key ];


				$this->prepare_dynamic_style( $data[ $section_key ], $section_key );


				if ( isset( $data[ $section_key ]['colors']['content_text_color'] ) ) {

					$html_content_color                                             = $data[ $section_key ]['colors']['content_text_color'];
					$this->customizer_css['desktop'][ "." . $section_key ]['color'] = $html_content_color;

				}


			}


		}

	}

	public function get_template_type() {
		return $this->template_type;

	}

	public function get_heading_section( $section_key, $selected_template_slug = '' ) {

		if ( isset( $selected_template_slug ) && $selected_template_slug != '' ) {
			$selected_template_slug = $selected_template_slug . '_';
		} else {
			$selected_template_slug = '';
		}

		$heading_section = array();

		$enable_heading      = WFACP_Common::get_option( $section_key . '_section_' . $selected_template_slug . 'enable_heading' );
		$heading             = WFACP_Common::get_option( $section_key . '_section_heading' );
		$heading_font_size   = WFACP_Common::get_option( $section_key . '_section_' . $selected_template_slug . 'heading_fs' );
		$heading_talign      = WFACP_Common::get_option( $section_key . '_section_' . $selected_template_slug . 'heading_talign' );
		$heading_font_weight = WFACP_Common::get_option( $section_key . '_section_' . $selected_template_slug . 'heading_font_weight' );

		if ( isset( $enable_heading ) ) {
			$heading_section['enable_heading'] = $enable_heading;
		}
		if ( isset( $heading ) ) {
			$heading_section['heading'] = $heading;
		}
		if ( isset( $heading_font_size ) ) {
			$heading_section['heading_fs'] = $heading_font_size;
		}
		if ( isset( $heading_talign ) ) {
			$heading_section['heading_talign'] = $heading_talign;
		}

		if ( isset( $heading_font_weight ) ) {
			$heading_section['heading_font_weight'] = $heading_font_weight;
		}

		$heading_section = WFACP_Common::unset_blank_keys( $heading_section );

		return $heading_section;
	}

	public function get_advance_setting( $section_key, $selected_template_slug = '', $is_mult_tab = false ) {

		if ( isset( $selected_template_slug ) && $selected_template_slug != '' ) {
			$selected_template_slug = $selected_template_slug . '_';
		} else {
			$selected_template_slug = '';
		}

		$section_inner_key = "_section_";
		if ( true === $is_mult_tab ) {


			$section_inner_key = "_";
		}

		$advanced_setting                      = array();
		$advanced_setting['rbox_border_type']  = WFACP_Common::get_option( $section_key . $section_inner_key . $selected_template_slug . 'rbox_border_type' );
		$advanced_setting['rbox_border_width'] = WFACP_Common::get_option( $section_key . $section_inner_key . $selected_template_slug . 'rbox_border_width' );
		$advanced_setting['rbox_border_color'] = WFACP_Common::get_option( $section_key . $section_inner_key . $selected_template_slug . 'rbox_border_color' );
		$advanced_setting['rbox_padding']      = WFACP_Common::get_option( $section_key . $section_inner_key . $selected_template_slug . 'rbox_padding' );

		$advanced_setting = WFACP_Common::unset_blank_keys( $advanced_setting );

		return $advanced_setting;
	}


	public function get_color_setting( $section_key, $selected_template_slug = '', $is_mult_tab = false ) {
		$color_setting = array();
		if ( isset( $selected_template_slug ) && $selected_template_slug != '' ) {
			$selected_template_slug = $selected_template_slug . '_';
		} else {
			$selected_template_slug = '';
		}
		$section_inner_key = "_section_";
		if ( true === $is_mult_tab ) {
			$section_inner_key = "_";
		}

		$color_setting['section_bg_color']   = WFACP_Common::get_option( $section_key . $section_inner_key . $selected_template_slug . 'section_bg_color' );
		$color_setting['sec_heading_color']  = WFACP_Common::get_option( $section_key . $section_inner_key . $selected_template_slug . 'sec_heading_color' );
		$color_setting['heading_text_color'] = WFACP_Common::get_option( $section_key . $section_inner_key . $selected_template_slug . 'heading_text_color' );
		$color_setting['content_text_color'] = WFACP_Common::get_option( $section_key . $section_inner_key . $selected_template_slug . 'content_text_color' );

		$color_setting = WFACP_Common::unset_blank_keys( $color_setting );

		return $color_setting;
	}

	public function prepare_dynamic_style( $data = array(), $section_key ) {

		/* Heading Setting Start */


		/* Font Size */
		if ( isset( $data['heading_section']['heading_fs'] ) ) {

			$heading_fs   = $data['heading_section']['heading_fs'];
			$default_unit = 'px';
			$desktop_fs   = '';

			/* Desktop Font Size  */
			if ( isset( $heading_fs['desktop'] ) ) {
				$desktop_fs = $heading_fs['desktop'];
			}
			if ( isset( $heading_fs['desktop-unit'] ) ) {
				$default_unit = $heading_fs['desktop-unit'];
			}
			$desktop_font_size = $desktop_fs . $default_unit;

			$tablet_font_size = '';
			$tablet_fs        = '';

			/* Tablet Font Size  */
			if ( isset( $heading_fs['tablet'] ) ) {
				$tablet_fs = $heading_fs['tablet'];
			}
			if ( isset( $heading_fs['tablet-unit'] ) ) {
				$default_unit = $heading_fs['tablet-unit'];
			}
			$tablet_font_size = $tablet_fs . $default_unit;

			/* Mobile Font Size  */
			$mobile_font_size = '';
			$mobile_fs        = '';

			if ( isset( $heading_fs['mobile'] ) ) {
				$mobile_fs = $heading_fs['mobile'];
			}
			if ( isset( $heading_fs['mobile-unit'] ) ) {
				$default_unit = $heading_fs['mobile-unit'];
			}
			$mobile_font_size = $mobile_fs . $default_unit;

			$this->customizer_css['desktop'][ '.' . $section_key . ' .wfacp_section_title' ] = array(
				'font-size' => $desktop_font_size,
			);
			$this->customizer_css['tablet'][ '.' . $section_key . ' .wfacp_section_title' ]  = array(
				'font-size' => $tablet_font_size,
			);
			$this->customizer_css['mobile'][ '.' . $section_key . ' .wfacp_section_title' ]  = array(
				'font-size' => $mobile_font_size,
			);
		}

		/* Text Alignment */
		$align_position = 'center';
		if ( isset( $data['heading_section']['heading_talign'] ) ) {
			$heading_talign = $data['heading_section']['heading_talign'];
			if ( $heading_talign == 'wfacp-text-center' ) {
				$align_position = 'center';
			} elseif ( $heading_talign == 'wfacp-text-left' ) {
				$align_position = 'left';
			} elseif ( $heading_talign == 'wfacp-text-right' ) {
				$align_position = 'right';
			}
			$this->customizer_css['desktop'][ '.' . $section_key . " .wfacp-text-$align_position" ] = array(
				'text-align' => $align_position,
			);
		}

		/* Font  Weight */
		if ( isset( $data['heading_section']['heading_font_weight'] ) ) {
			$font_weight = $data['heading_section']['heading_font_weight'];
			if ( $font_weight == 'wfacp-normal' ) {
				$weight_size = 'normal';
			} elseif ( $font_weight == 'wfacp-bold' ) {
				$weight_size = 'bold';
			}
			$this->customizer_css['desktop'][ '.' . $section_key . " .wfacp-$weight_size" ] = array(
				'font-weight' => $weight_size,
			);
		}

		/* Advanced Setting Start*/
		$additional_setting = array();


		$rbox_border_type = '';
		if ( isset( $data['advance_setting']['rbox_border_type'] ) ) {
			$rbox_border_type                   = $data['advance_setting']['rbox_border_type'];
			$additional_setting['border-style'] = $rbox_border_type;

		}
		if ( isset( $data['advance_setting']['rbox_border_width'] ) ) {
			$rbox_border_width                  = $data['advance_setting']['rbox_border_width'];
			$additional_setting['border-width'] = $rbox_border_width . 'px';
		}
		if ( isset( $data['advance_setting']['rbox_border_color'] ) ) {
			$rbox_border_color                  = $data['advance_setting']['rbox_border_color'];
			$additional_setting['border-color'] = $rbox_border_color;
		}
		if ( isset( $data['advance_setting']['rbox_padding'] ) ) {
			$rbox_padding = $data['advance_setting']['rbox_padding'];

			$additional_setting['padding'] = $rbox_padding . 'px';
		}
		/* Advanced Setting Closed */

		/* Color Setting Start */
		if ( isset( $data['colors']['section_bg_color'] ) ) {
			$section_bg_color = $data['colors']['section_bg_color'];

			$additional_setting['background-color'] = $section_bg_color;
		}


		if ( isset( $data['colors']['sec_heading_color'] ) ) {
			$sec_heading_color                                                                = $data['colors']['sec_heading_color'];
			$this->customizer_css['desktop'][ '.' . $section_key . ' .wfacp_section_title ' ] = array(
				'color' => $sec_heading_color,
			);
		}

		if ( isset( $data['colors']['content_text_color'] ) ) {
			$content_text_color                                            = $data['colors']['content_text_color'];
			$this->customizer_css['desktop'][ '.' . $section_key . ' p ' ] = array(
				'color' => $content_text_color,
			);
		}
		if ( ( is_array( $additional_setting ) && count( $additional_setting ) > 0 ) ) {


			$this->customizer_css['desktop'][ '.' . $section_key ] = $additional_setting;

		}

	}

	public function get_sub_heading_section( $section_key, $selected_template_slug = '' ) {


		if ( isset( $selected_template_slug ) && $selected_template_slug != '' ) {
			$selected_template_slug = $selected_template_slug . '_';
		} else {
			$selected_template_slug = '';
		}

		$heading_section = array();

		$enable_sub_heading      = WFACP_Common::get_option( $section_key . '_section_' . $selected_template_slug . 'enable_sub_heading' );
		$sub_heading             = WFACP_Common::get_option( $section_key . '_section_sub_heading' );
		$sub_heading_fs          = WFACP_Common::get_option( $section_key . '_section_' . $selected_template_slug . 'sub_heading_fs' );
		$sub_heading_talign      = WFACP_Common::get_option( $section_key . '_section_' . $selected_template_slug . 'sub_heading_talign' );
		$sub_heading_font_weight = WFACP_Common::get_option( $section_key . '_section_' . $selected_template_slug . 'sub_heading_font_weight' );

		if ( isset( $enable_sub_heading ) ) {
			$heading_section['enable_heading'] = $enable_sub_heading;
		}
		if ( isset( $sub_heading ) ) {
			$heading_section['heading'] = $sub_heading;
		}
		if ( isset( $sub_heading_fs ) ) {
			$heading_section['heading_fs'] = $sub_heading_fs;
		}

		if ( isset( $sub_heading_talign ) ) {
			$heading_section['heading_talign'] = $sub_heading_talign;
		}

		if ( isset( $sub_heading_font_weight ) ) {
			$heading_section['heading_font_weight'] = $sub_heading_font_weight;
		}


		$heading_section = WFACP_Common::unset_blank_keys( $heading_section );

		return $heading_section;
	}

	public function get_section_keys_data( $section_key ) {
		if ( isset( $this->section_keys_data[ $section_key ] ) ) {
			return $this->section_keys_data[ $section_key ];
		}

		return $section_key;
	}

	public function set_section_keys_data( $section_key, $data ) {

		$this->section_keys_data[ $section_key ] = $data;
	}

	public function assign_colors( $data_keys, $form_color_meta = [] ) {
		$prefix_key = '';
		$panel_name = '';

		if ( isset( $form_color_meta['panel'] ) && $form_color_meta['panel'] != '' ) {
			$prefix_key = $prefix_key . $form_color_meta['panel'] . '_';
			$panel_name = $form_color_meta['panel'];
		}

		if ( isset( $form_color_meta['section'] ) && $form_color_meta['section'] != '' ) {
			$prefix_key = $prefix_key . $form_color_meta['section'] . '_';
		}

		$data = [];
		if ( ! is_array( $data_keys ) || count( $data_keys ) == 0 || $prefix_key == '' ) {
			return;
		}

		$local_css = [];
		$temp_type = $this->get_template_type();

		foreach ( $data_keys['colors'] as $key => $details ) {

			$key_name = $key;

			$important_to_class = '';

			if ( $panel_name == 'wfacp_form' && strpos( $key, '_next_' ) !== false ) {
				$key_name = str_replace( '_next_', '_order-place_', $key );
			}

			$data[ $form_color_meta['key'] ][ $key ] = WFACP_Common::get_option( $prefix_key . $key_name );

			if ( ! isset( $data[ $form_color_meta['key'] ][ $key ] ) || $data[ $form_color_meta['key'] ][ $key ] == '' ) {
				continue;
			}

			foreach ( $details as $index => $value ) {
				$device      = $value['device'];
				$class       = $value['class'];
				$type        = $value['type'];
				$field_value = $data[ $form_color_meta['key'] ][ $key ];

				if ( isset( $device ) && isset( $class ) && isset( $type ) && isset( $field_value ) ) {

					if ( $temp_type == 'embed_form' ) {
						if ( strpos( $key_name, 'btn_order-place' ) !== false && ( $type == 'background-color' || $type == 'color' ) ) {

							$important_to_class = ' !important';
						}
					}

					$this->customizer_css[ $device ][ $class ][ $type ] = $field_value . $important_to_class;
					$local_css[ $device ][ $class ][ $type ]            = $field_value . $important_to_class;
				}
			}
		}

		if ( $panel_name == '' ) {
			return;
		}

		foreach ( $data as $key => $value ) {
			$this->customizer_fields_data[ $panel_name ][ $key ] = $value;
		}

	}

	public function wfacp_font_size( $data, $metaData ) {

		extract( $metaData );

		if ( ! isset( $section_key ) || ! isset( $target_to ) || ! isset( $source_from ) ) {
			return;
		}

		if ( isset( $data[ $source_from ] ) ) {
			$heading_fs = $data[ $source_from ];
		}

		$desktop_font_size = '';
		$tablet_font_size  = '';
		$mobile_font_size  = '';

		$desktop_fs   = '';
		$tablet_fs    = '';
		$mobile_fs    = '';
		$default_unit = 'px';

		/* Desktop Font Size  */
		if ( isset( $heading_fs['desktop'] ) ) {
			$desktop_fs = $heading_fs['desktop'];
		}
		if ( isset( $heading_fs['desktop-unit'] ) ) {
			$default_unit = $heading_fs['desktop-unit'];
		}

		$desktop_font_size = $desktop_fs . $default_unit;

		/* Tablet Font Size  */

		if ( isset( $heading_fs['tablet'] ) ) {
			$tablet_fs = $heading_fs['tablet'];
		}
		if ( isset( $heading_fs['tablet-unit'] ) ) {
			$default_unit = $heading_fs['tablet-unit'];
		}

		$tablet_font_size = $tablet_fs . $default_unit;

		/* Mobile Font Size  */

		if ( isset( $heading_fs['mobile'] ) ) {
			$mobile_fs = $heading_fs['mobile'];
		}

		if ( isset( $heading_fs['mobile-unit'] ) ) {
			$default_unit = $heading_fs['mobile-unit'];
		}
		$mobile_font_size = $mobile_fs . $default_unit;

		if ( isset( $this->customizer_css['desktop'][ $target_to ] ) && is_array( $this->customizer_css['desktop'][ $target_to ] ) && count( $this->customizer_css['desktop'][ $target_to ] ) > 0 ) {

			if ( isset( $desktop_font_size ) ) {
				$this->customizer_css['desktop'][ $target_to ]['font-size'] = $desktop_font_size;
			}

			if ( isset( $tablet_font_size ) ) {

				$this->customizer_css['tablet'][ $target_to ]['font-size'] = $tablet_font_size;

			}
			if ( isset( $mobile_font_size ) ) {
				$this->customizer_css['mobile'][ $target_to ]['font-size'] = $mobile_font_size;
			}
		} else {

			if ( isset( $desktop_font_size ) ) {
				$this->customizer_css['desktop'][ $target_to ] = array(
					'font-size' => $desktop_font_size,
				);

			}
			if ( isset( $tablet_font_size ) ) {
				$this->customizer_css['tablet'][ $target_to ] = array(
					'font-size' => $tablet_font_size,
				);
			}
			if ( isset( $mobile_font_size ) ) {
				$this->customizer_css['mobile'][ $target_to ] = array(
					'font-size' => $mobile_font_size,
				);
			}
		}

	}

	public function get_step_count() {
		$form_current_step = $this->get_current_step();
		$no_of_fields      = 1;
		if ( isset( $form_current_step ) && $form_current_step == 'two_step' ) {
			$no_of_fields = 2;
		} elseif ( isset( $form_current_step ) && $form_current_step == 'third_step' ) {
			$no_of_fields = 3;
		}


		return $no_of_fields;
	}

	public function get_current_step() {
		return $this->current_step;
	}

	public function merge_customizer_data( $field, $field_index ) {

		$template_slug = $this->get_template_slug();
		$template_slug = sanitize_title( $template_slug );
		$css_ready     = WFACP_Common::get_option( 'wfacp_form_form_fields_1_' . $template_slug . '_' . $field_index );
		if ( '' !== $css_ready ) {
			$field['cssready'] = explode( ',', $css_ready );
		}

		$css_classes = $this->default_css_class();


		if ( isset( $this->css_classes[ $field_index ] ) ) {
			$css_classes = $this->css_classes[ $field_index ];
		}
		$wrapper_class = 'wfacp-form-control-wrapper ';

		if ( isset( $field['cssready'] ) && is_array( $field['cssready'] ) && count( $field['cssready'] ) > 0 ) {
			$wrapper_class .= implode( ' ', $field['cssready'] );
		} else {
			$wrapper_class .= ' ' . $css_classes['class'];
		}
		$input_class = 'wfacp-form-control';
		$label_class = 'wfacp-form-control-label';

		$field['class'][]       = $wrapper_class;
		$field['input_class'][] = $input_class;
		$field['label_class'][] = $label_class;
		$field['class']         = array_unique( $field['class'] );
		$field['input_class']   = array_unique( $field['input_class'] );
		$field['label_class']   = array_unique( $field['label_class'] );

		if ( $field_index == 'billing_address_2' || $field_index == 'street_address_2' ) {
			$search_index = array_search( 'screen-reader-text', $field['label_class'] );
			if ( false !== $search_index ) {
				unset( $field['label_class'][ $search_index ] );
			}
		}

		if ( isset( $field['type'] ) ) {
			if ( 'email' == $field['type'] ) {
				$field['validate'][] = 'email';
			} elseif ( 'checkbox' == $field['type'] ) {
				$field['class'][] = 'wfacp_checkbox_field';
				unset( $field['label_class'][0] );
				if ( isset( $field['field_type'] ) && $field['field_type'] != 'advanced' ) {
					unset( $field['input_class'][0] );
				}

			}

			if ( in_array( $field['type'], [ 'date' ] ) ) {
				$default = $field['default'];
				if ( '' !== $default ) {
					$default          = str_replace( '/', '-', $default );
					$field['default'] = date( 'Y-m-d', strtotime( $default ) );
				}
				unset( $default );
			}
		}

		if ( in_array( $field_index, [ 'billing_postcode', 'shipping_postcode', 'billing_city', 'shipping_city' ] ) ) {
			$field['class'][] = 'update_totals_on_change';
		}
		if ( in_array( $field_index, [ 'billing_state', 'shipping_state', 'billing_country', 'shipping_country' ] ) ) {
			$field['class'][] = 'wfacp_select2_country_state';
		}

		return $field;
	}

	public function default_css_class() {
		return [
			'input_class' => 'wfacp-form-control',
			'class'       => 'wfacp-col-full',
		];
	}

	public function change_default_setting( $panel_details, $panel_key ) {

		$fields_data = $panel_details['sections']['section']['fields'];


		foreach ( $fields_data as $key => $value ) {
			if ( isset( $this->layout_setting[ $panel_key ][ $key ] ) ) {


				$panel_details['sections']['section']['fields'][ $key ]['default'] = $this->layout_setting[ $panel_key ][ $key ];

			}
		}

		return $panel_details;
	}

	public function multitab_default_setting( $panel_details, $panel_key ) {


		if ( ( is_array( $panel_key ) && count( $panel_key ) > 0 ) && ( is_array( $this->layout_setting ) && count( $this->layout_setting ) ) ) {
			foreach ( $panel_key as $index_key => $value ) {
				if ( is_array( $panel_key ) && count( $panel_key ) > 0 ) {
					foreach ( $value as $pkey => $pvalue ) {
						$fields_data = $panel_details['sections'][ $pvalue ]['fields'];
						if ( is_array( $fields_data ) && count( $fields_data ) > 0 ) {
							foreach ( $fields_data as $key => $value ) {

								if ( array_key_exists( $key, $this->layout_setting[ $index_key ] ) ) {
									$panel_details['sections'][ $pvalue ]['fields'][ $key ]['default'] = $this->layout_setting[ $index_key ][ $key ];
								}
							}
						}
					}
				}
			}
		}

		return $panel_details;
	}

	public function customizer_layout_order( $panel_details, $section_key ) {

		return $panel_details;
	}

	public function add_form_steps() {

		$selected_template_type = $this->get_template_type();

		$num_of_steps = $this->get_step_count();

		if ( $selected_template_type != 'embed_form' && $num_of_steps > 1 ) {

			if ( isset( $this->customizer_fields_data['wfacp_form']['form_data']['breadcrumb'] ) && is_array( $this->customizer_fields_data['wfacp_form']['form_data']['breadcrumb'] ) && count( $this->customizer_fields_data['wfacp_form']['form_data']['breadcrumb'] ) > 0 ) {

				$steps_arr = [ 'single_step', 'two_step', 'third_step' ];

				$breadcrumb = $this->customizer_fields_data['wfacp_form']['form_data']['breadcrumb'];

				echo '<div class=wfacp_steps_wrap>';
				echo '<div class=wfacp_steps_sec>';

				echo '<ul>';
				do_action( 'wfacp_before_breadcrumb', $breadcrumb );

				foreach ( $breadcrumb as $key => $value ) {
					$active = '';

					if ( $key == 0 ) {
						$active = 'wfacp_bred_active wfacp_bred_visited';
					}

					$step          = ( isset( $steps_arr[ $key ] ) ) ? $steps_arr[ $key ] : '';
					$text_class    = ( ! empty( $value ) ) ? 'wfacp_step_text_have' : 'wfacp_step_text_nohave';
					$bredcrumb_cls = $step;

					echo "<li class='wfacp_step_$key wfacp_bred $active $step' step='$step'>";
					?>
                    <a href='javascript:void(0)' class="<?php echo $text_class; ?>" data-text="<?php echo sanitize_title( $value ); ?>"><?php echo $value; ?></a>
					<?php

					echo '</li>';
				}
				do_action( 'wfacp_after_breadcrumb' );
				echo '</ul></div></div>';
			}
		}
	}

	public function remove_default_order_summary_table( $fragments ) {
		unset( $fragments['.woocommerce-checkout-review-order-table'] );

		$total                    = WC()->cart->get_total( 'edit' );
		$fragments['.cart_total'] = $total;

		return $fragments;
	}

	public function assign_email_as_a_username( $posted_data ) {

		if ( ! isset( $posted_data['account_username'] ) ) {
			$posted_data['account_username'] = $posted_data['billing_email'];
		}

		return $posted_data;
	}

	public function no_follow_no_index() {
		echo '<meta name="robots" content="noindex,nofollow"/>';
	}

	public function add_style_inline() {

		$deskotp_css_style = '';
		$tablet_css_style  = '';
		$mobile_css_style  = '';

		if ( isset( $this->selected_font_family ) && '' != $this->selected_font_family ) {
			$style_url = 'https://fonts.googleapis.com/css?family=' . $this->selected_font_family;
			echo "<link href='" . $style_url . "' rel=stylesheet>";
			$fonts_arr = [ 'body', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ];
			if ( is_array( $fonts_arr ) && count( $fonts_arr ) > 0 ) {
				foreach ( $fonts_arr as $font_key => $font_value ) {
					if ( $font_value == '' ) {
						continue;
					}
					$this->customizer_css['desktop'][ $font_value ]['font-family'] = $this->selected_font_family;
				}
			}
		}

		$template_type = $this->get_template_type();

		if ( $template_type == 'embed_form' ) {
			return;
		}

		if ( isset( $this->customizer_css['desktop'] ) && is_array( $this->customizer_css['desktop'] ) && count( $this->customizer_css['desktop'] ) > 0 ) {

			foreach ( $this->customizer_css['desktop'] as $key => $value ) {

				foreach ( $value as $css_property => $css_value ) {

					$selector          = $css_property . ':' . $css_value;
					$style_inline      = $key . '{' . $selector . ';}';
					$deskotp_css_style .= $style_inline;

				}
			}
		}

		if ( isset( $this->customizer_css['tablet'] ) && is_array( $this->customizer_css['tablet'] ) && count( $this->customizer_css['tablet'] ) > 0 ) {
			$tablet_css_style .= '@media (max-width: 991px) {';
			foreach ( $this->customizer_css['tablet'] as $key => $value ) {
				foreach ( $value as $css_property => $css_value ) {
					$selector         = $css_property . ':' . $css_value;
					$style_inline     = $key . '{' . $selector . ';}';
					$tablet_css_style .= $style_inline;
				}
			}
			$tablet_css_style .= '}';
		}
		if ( isset( $this->customizer_css['mobile'] ) && is_array( $this->customizer_css['mobile'] ) && count( $this->customizer_css['mobile'] ) > 0 ) {
			$mobile_css_style .= '@media (max-width: 767px) {';
			foreach ( $this->customizer_css['mobile'] as $key => $value ) {
				foreach ( $value as $css_property => $css_value ) {
					$selector         = $css_property . ':' . $css_value;
					$style_inline     = $key . '{' . $selector . ';}';
					$mobile_css_style .= $style_inline;
				}
			}
			$mobile_css_style .= '}';
		}


		echo '<style>';
		echo $deskotp_css_style;
		echo $tablet_css_style;
		echo $mobile_css_style;
		echo '</style>';
	}


	public function typography_custom_css() {

		$_wfacp_global_settings = get_option( '_wfacp_global_settings' );

		if ( isset( $_wfacp_global_settings['wfacp_checkout_global_css'] ) && $_wfacp_global_settings['wfacp_checkout_global_css'] != '' ) {
			$global_custom_css = '<style>' . $_wfacp_global_settings['wfacp_checkout_global_css'] . '</style>';
			echo $global_custom_css;
		}

		$selected_template_slug = $this->get_template_slug();
		$style_custom_css       = WFACP_Common::get_option( 'wfacp_custom_css_section_' . $selected_template_slug . '_code' );

		$custom_css = '<style>' . $style_custom_css . '</style>';
		if ( isset( $style_custom_css ) && $style_custom_css != '' ) {
			echo $custom_css;
		}

	}

	public function enqueue_script() {
		$template_fields = $this->get_customizer_fields();
		$template_type   = $this->get_template_type();
		$pd              = array();
		//wp_enqueue_style( 'dashicons' );
		wp_enqueue_style( 'wfacp-style', plugin_dir_url( WFACP_PLUGIN_FILE ) . 'assets/css/wfacp_combined.min.css', false, WFACP_VERSION_DEV );
		wp_enqueue_style( 'wfacp-dashicons', plugin_dir_url( WFACP_PLUGIN_FILE ) . 'assets/css/wfacp_dashicons.css', false, WFACP_VERSION_DEV );
		if ( "pre_built" === $template_type ) {
			wp_enqueue_style( 'wfacp-flaticons-fonts', plugin_dir_url( WFACP_PLUGIN_FILE ) . 'assets/css/fonts/icons-fonts/flaticon.css', false, WFACP_VERSION_DEV );
		}


		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'wc-add-to-cart-variation' );
		if ( WFACP_Common::is_customizer() ) {
			wp_enqueue_script( 'underscore' );
			wp_enqueue_script( 'wp-util' );
			wp_enqueue_script( 'customizer' );
			wp_enqueue_script( 'customize-base' );
			wp_enqueue_script( 'customize-preview' );
			wp_enqueue_script( 'wfacp_customizer_live', plugin_dir_url( WFACP_PLUGIN_FILE ) . 'assets/js/customizer.js', [], WFACP_VERSION_DEV, true );
			wp_localize_script( 'wfacp_customizer_live', 'wfacp_customizer', array(
				'is_loaded' => 'yes',
				'wfacp_id'  => WFACP_Common::get_id(),
				'fields'    => $template_fields,
				'pd'        => $pd,
			) );
			wp_enqueue_script( 'customize-selective-refresh' );
		}

		$checkout_js_file = 'checkout.min.js';
		if ( defined( 'BWF_DEV' ) ) {
			$checkout_js_file = 'checkout.js';
		}
		wp_enqueue_script( 'wfacp_checkout_js', plugin_dir_url( WFACP_PLUGIN_FILE ) . 'assets/js/' . $checkout_js_file, [ 'jquery' ], WFACP_VERSION_DEV, true );

		$global_settings = WFACP_Common::global_settings( true );


		$autopopulatestate = 'yes';

		$wc_validation_fields = $this->get_wc_addr2_company_value();


		$page_settings = WFACP_Common::get_page_settings( WFACP_Common::get_id() );

		$preview_field_head = [
			'address'             => __( 'Billing', 'woocommerce' ),
			'shipping-address'    => __( 'Ship to', 'woocommerce' ),
			'shipping_calculator' => __( 'Method', 'woocommerce' ),
			'billing_first_name'  => __( 'Name', 'woocommerce' ),
		];

		$textLocal = esc_attr_x( 'Change', 'theme' );
		$data      = $switcher_settings = WFACP_Common::get_product_switcher_data( WFACP_Common::get_id() );
		wp_localize_script( 'wfacp_checkout_js', 'wfacp_frontend', [
			'id'                              => WFACP_Common::get_id(),
			'admin_ajax'                      => admin_url( 'admin-ajax.php' ),
			'wc_endpoints'                    => WFACP_AJAX_Controller::get_public_endpoints(),
			'wfacp_nonce'                     => wp_create_nonce( 'wfacp_secure_key' ),
			'cart_total'                      => WC()->cart->get_total( 'edit' ),
			'settings'                        => $global_settings,
			'products_in_cart'                => WFACP_Core()->public->products_in_cart,
			'autopopulate'                    => apply_filters( 'wfacp_autopopulate_fields', is_user_logged_in() ? 'no' : 'yes' ),
			'autopopulatestate'               => apply_filters( 'wfacp_autopopulatestate_fields', $autopopulatestate ),
			'is_global'                       => WFACP_Core()->public->is_checkout_override(),
			'is_registration_enabled'         => WC()->checkout()->is_registration_enabled(),
			'wc_customizer_validation_status' => $wc_validation_fields,
			'is_customizer'                   => WFACP_Common::is_customizer(),
			'switcher_settings'               => $data['settings'],
			'cart_is_virtual'                 => WFACP_Common::is_cart_is_virtual(),
			'show_on_next_step_fields'        => $page_settings['show_on_next_step'],
			'change_text_preview_fields'      => apply_filters( 'wfacp_preview_change_text', $textLocal ),
			'fields_label'                    => apply_filters( 'wfacp_preview_headings', $preview_field_head ),
			'select_options_text'             => __( 'Select options', 'woocommerce' ),
			'update_button_text'              => __( 'Update', 'woocommerce' ),

		] );


		wp_localize_script( 'wfacp_checkout_js', 'wfacp_pixel_data', [
			'pixel_id'          => trim( isset( $global_settings['wfacp_checkout_pixel_id'] ) ? $global_settings['wfacp_checkout_pixel_id'] : '' ),
			'pixel_checkout'    => WFACP_Common::pixel_InitiateCheckout(),
			'pixel_add_to_cart' => WFACP_Common::pixel_add_to_cart_product(),
		] );

		if ( apply_filters( 'wfacp_remove_woocommerce_style_dependency', true ) ) {
			wp_deregister_style( 'woocommerce-layout' );
			wp_deregister_style( 'woocommerce-smallscreen' );
			wp_deregister_style( 'woocommerce-general' );
		}

	}

	public function get_customizer_fields() {
		return $this->customizer_fields;
	}

	public function remove_woocommerce_js_css() {
		if ( WFACP_Common::is_customizer() ) {
			global $wp_scripts;

			$registered_script = $wp_scripts->registered;
			if ( ! empty( $registered_script ) ) {
				foreach ( $registered_script as $handle => $data ) {
					if ( false !== strpos( $data->src, '/plugins/woocommerce/' ) ) {
						unset( $wp_scripts->registered[ $handle ] );
						wp_dequeue_script( $handle );
					}
				}
			}
		}
	}

	public function remove_theme_css_and_scripts() {

		global $wp_scripts, $wp_styles;
		$registered_script = $wp_scripts->registered;
		if ( ! empty( $registered_script ) ) {
			foreach ( $registered_script as $handle => $data ) {
				if ( $this->find_js_css_handle( $data->src ) ) {
					unset( $wp_scripts->registered[ $handle ] );
					wp_dequeue_script( $handle );
				}
			}
		}

		$registered_style = $wp_styles->registered;
		if ( ! empty( $registered_style ) ) {
			foreach ( $registered_style as $handle => $data ) {
				if ( $this->find_js_css_handle( $data->src ) ) {
					unset( $wp_styles->registered[ $handle ] );
					wp_dequeue_script( $handle );
				}
			}
		}

	}

	/**
	 * Find removal folder path exist in enqueue js and css url
	 *
	 * @param $url
	 *
	 * @return bool
	 */
	private final function find_js_css_handle( $url ) {
		$paths = [ '/themes/', '/cache/' ];


		$template_type = $this->get_template_type();

		if ( 'pre_built' == $template_type ) {
			$plugins = [
				'revslider',
				'testimonial-slider-and-showcase',
				'woocommerce-product-addons',
				'contact-form-7',
				'wp-upg',
				'bonanza-',
				'affiliate-wp',
				'woofunnels-autobot',
				'woocommerce-quick-buy',
				'wp-admin/js/password-strength-meter.min.js',
				'woocommerce-product-bundles',
				'/fusion-styles/',
				'cart-fragments.min.js',
				'cart-fragments.js',
				'/uploads/oceanwp/main-style.css',
				'/uploads/dynamic_avia/',
				'/uploads/porto_styles/',
				'um-styles.css',
				'/fifu-premium/',
				'/uploads/bb-theme/',
				'/uploads/wp-less/pillar/style/css/',
				'/td-composer/legacy/common/wp_booster/js_dev',
			];
			$paths   = array_merge( $paths, $plugins );
		}

		$paths = apply_filters( 'wfacp_css_js_removal_paths', $paths, $this );
		if ( empty( $paths ) ) {
			return false;
		}

		foreach ( $paths as $path ) {
			if ( false !== strpos( $url, $path ) && true == apply_filters( 'wfacp_css_js_deque', true, $path, $url, $this ) ) {
				return true;
				break;
			}
		}

		return false;

	}

	public function add_header_script() {

		$settings = WFACP_Common::get_page_settings( WFACP_Common::get_id() );

		if ( isset( $settings['header_script'] ) && '' != $settings['header_script'] ) {
			echo sprintf( "\n \n %s \n \n", $settings['header_script'] );
		}
	}

	public function add_footer_script() {

		$settings = WFACP_Common::get_page_settings( WFACP_Common::get_id() );

		if ( isset( $settings['footer_script'] ) && '' != $settings['footer_script'] ) {
			echo sprintf( "\n \n %s \n\n", $settings['footer_script'] );
		}

		$_wfacp_global_settings = get_option( '_wfacp_global_settings' );

		if ( isset( $_wfacp_global_settings['wfacp_global_external_script'] ) && $_wfacp_global_settings['wfacp_global_external_script'] != '' ) {
			$global_script = $_wfacp_global_settings['wfacp_global_external_script'];
			echo $global_script;
		}

	}

	public function checkout_form_login() {
		include WFACP_TEMPLATE_COMMON . '/checkout/form-login.php';
	}

	public function checkout_form_coupon() {
		$settings          = WFACP_Common::get_page_settings( WFACP_Common::get_id() );
		$is_disable_coupon = ( isset( $settings['disable_coupon'] ) && 'true' == $settings['disable_coupon'] );
		if ( ! $is_disable_coupon ) {
			include WFACP_TEMPLATE_COMMON . '/checkout/form-coupon.php';
		}
	}

	public function wfacp_default_field( $default, $index ) {
		if ( isset( $this->css_classes[ $index ] ) ) {
			return $this->css_classes[ $index ]['class'];
		} else {


			return 'wfacp-col-full';
		}
	}

	public function display_back_button( $step, $current_step ) {

		$alignmentclass = '';
		$width_cls      = '';
		if ( isset( $this->customizer_fields_data['wfacp_form']['form_data']['btn_details']['talign'] ) ) {
			$alignmentclass = $this->customizer_fields_data['wfacp_form']['form_data']['btn_details']['talign'];
		}

		if ( isset( $this->customizer_fields_data['wfacp_form']['form_data']['btn_details']['width'] ) ) {
			$width_cls1 = $this->customizer_fields_data['wfacp_form']['form_data']['btn_details']['width'];
			if ( 'initial' === $width_cls1 ) {
				$width_cls = $width_cls1;
			}
		}


		if ( 'single_step' != $step && $step != $current_step ) {


			echo sprintf( '<div class="sec_text_wrap %s %s">', $alignmentclass, $width_cls );

			echo '<div class=btm_btn_sec>';
			$this->get_back_button( $step );
			echo '</div>';
		}
	}

	public function close_back_button_div( $step, $current_step ) {
		if ( 'single_step' != $step && $step != $current_step ) {
			echo '</div>';
		}
	}

	public function add_class_change_place_order( $btn_html ) {

		$stepCount         = $this->get_step_count();
		$get_template_slug = $this->get_template_slug();
		$get_template_type = $this->get_template_type();


		if ( ! empty( $_GET['woo-paypal-return'] ) && ! empty( $_GET['token'] ) && ! empty( $_GET['PayerID'] ) ) {
			return $btn_html;
		}


		$alignmentclass = '';
		$width_cls      = '';

		if ( $stepCount > 1 ) {
			$alignmentclass = WFACP_Common::get_option( 'wfacp_form_section_' . $get_template_slug . '_btn_order-place_talign' );
			$width_cls1     = WFACP_Common::get_option( 'wfacp_form_section_' . $get_template_slug . '_btn_order-place_width' );
			if ( 'initial' === $width_cls1 ) {
				$width_cls = $width_cls1;

			}

		}


		ob_start();

		echo sprintf( '<div class="wfacp-order-place-btn-wrap %s %s">', $alignmentclass, $width_cls );


		if ( $stepCount > 1 ) {
			$getKey = $stepCount - 2;

			$back_btn_text = WFACP_Common::get_option( 'wfacp_form_section_back_btn_text' );


			if ( '' === $back_btn_text && 'pre_built' === $get_template_type ) {
				$back_btn_text = '&laquo; Return to {step_name}';
			}

			if ( 'pre_built' === $get_template_type && strpos( $back_btn_text, '{step_name}' ) !== false ) {
				$sectionKeyText = WFACP_Common::get_option( 'wfacp_form_section_breadcrumb_' . $getKey . '_step_text' );

				$back_btn_text = str_replace( "{step_name}", "<span>$sectionKeyText</span>", $back_btn_text );

			}


			$back_text = __( '&laquo; Return', 'woofunnels-aero-checkout' );

			if ( ! isset( $back_btn_text ) || $back_btn_text == '' ) {
				$back_btn_text = $back_text;
			}
			echo "<div class='place_order_back_btn wfacp_none_class '><a class='wfacp_back_wrap' href='javascript:void(0)'>" . __( $back_btn_text, 'woofunnels-aero-checkout' ) . '</a> </div>';
		}

		echo $btn_html;

		echo '</div>';

		$orderPlaceHtml = ob_get_clean();

		return $orderPlaceHtml;
	}

	public function change_order_placed_step_label( $order_button_text ) {

		$template_slug = $this->template_slug;

		$orderText = WFACP_Common::get_option( 'wfacp_form_section_' . $template_slug . '_btn_order-place_btn_text' );

		if ( isset( $orderText ) && $orderText != '' ) {
			return $orderText;
		}

		$text = strtoupper( $order_button_text );

		return $text . '→';
	}

	public function change_single_step_label( $name, $current_action ) {
		$current_step           = $this->get_current_step();
		$selected_template_slug = $this->get_template_slug();


		if ( isset( $this->customizer_fields_data['wfacp_form']['form_data']['btn_details']['btn_text']['next'] ) && $this->customizer_fields_data['wfacp_form']['form_data']['btn_details']['btn_text']['next'] != '' ) {

			$nextButtonText = $this->customizer_fields_data['wfacp_form']['form_data']['btn_details']['btn_text']['next'];


			if ( strpos( $nextButtonText, '{step_name}' ) !== false ) {

				if ( $current_action == 'single_step' ) {

					$getKey = 1;

					$sectionKeyText = WFACP_Common::get_option( 'wfacp_form_section_breadcrumb_' . $getKey . '_step_text' );

					$back_btn_text = str_replace( "{step_name}", $sectionKeyText, $nextButtonText );

					return $back_btn_text;
				}

				return $nextButtonText;
			}


			return $nextButtonText;

		}


		return $name . '→';
	}

	public function change_two_step_label( $name, $current_action ) {
		$current_step = $this->get_current_step();


		if ( isset( $this->customizer_fields_data['wfacp_form']['form_data']['btn_details']['btn_text']['back'] ) && $this->customizer_fields_data['wfacp_form']['form_data']['btn_details']['btn_text']['back'] != '' ) {


			$nextButtonText = $this->customizer_fields_data['wfacp_form']['form_data']['btn_details']['btn_text']['back'];


			if ( strpos( $nextButtonText, '{step_name}' ) !== false ) {
				if ( $current_action == 'two_step' ) {
					$getKey         = 2;
					$sectionKeyText = WFACP_Common::get_option( 'wfacp_form_section_breadcrumb_' . $getKey . '_step_text' );
					$back_btn_text  = str_replace( "{step_name}", $sectionKeyText, $nextButtonText );

					return $back_btn_text;
				}

				return $nextButtonText;
			}

			return $this->customizer_fields_data['wfacp_form']['form_data']['btn_details']['btn_text']['back'];


		}


		return $name . '→';
	}


	public function change_back_step_label( $text, $next_action ) {

		$get_template_type = $this->get_template_type();

		$back_btn_text = WFACP_Common::get_option( 'wfacp_form_section_back_btn_text' );


		if ( $next_action == 'single_step' ) {
			$sectionKeyText = WFACP_Common::get_option( 'wfacp_form_section_breadcrumb_0_step_text' );
		} elseif ( $next_action == 'two_step' ) {
			$sectionKeyText = WFACP_Common::get_option( 'wfacp_form_section_breadcrumb_1_step_text' );
		}


		if ( '' === $back_btn_text && 'pre_built' === $get_template_type ) {
			$back_btn_text = '&laquo; Return to {step_name}';
		}


		if ( 'pre_built' === $get_template_type && strpos( $back_btn_text, '{step_name}' ) !== false ) {

			$back_btn_text = str_replace( "{step_name}", "<span>$sectionKeyText</span>", $back_btn_text );
		}

		if ( isset( $back_btn_text ) && $back_btn_text != '' ) {
			return $back_btn_text;
		}

		return '&laquo; Return';
	}

	public function add_fragment_order_summary( $fragments ) {
		if ( isset( $this->checkout_fields['advanced']['order_summary'] ) ) {
			ob_start();
			include WFACP_TEMPLATE_COMMON . '/order-summary.php';
			$order_summary                     = ob_get_clean();
			$fragments['.wfacp_order_summary'] = $order_summary;
		}

		return $fragments;
	}

	public function add_fragment_shipping_calculator( $fragments ) {
		if ( isset( $this->checkout_fields['advanced']['shipping_calculator'] ) ) {
			ob_start();
			include WFACP_TEMPLATE_COMMON . '/shipping-options.php';
			$order_shipping_calc                  = ob_get_clean();
			$fragments['.wfacp_shipping_options'] = $order_shipping_calc;
		}

		return $fragments;
	}

	public function add_fragment_product_switching( $fragments ) {

		if ( isset( WFACP_Common::$post_data['product_switcher_need_refresh'] ) && 0 == WFACP_Common::$post_data['product_switcher_need_refresh'] ) {
			return $fragments;
		}
		if ( isset( $this->checkout_fields['product']['product_switching'] ) || apply_filters( 'wfacp_allow_product_switcher_fragments', false ) ) {
			$fragments['.wfacp-product-switch-panel'] = WFACP_Common::get_product_switcher_table( true );
		}

		return $fragments;
	}

	public function add_fragment_order_total( $fragments ) {
		$fragments['.wfacp_order_total'] = WFACP_Common::get_order_total_fields( true );

		return $fragments;
	}

	public function add_fragment_coupon( $fragments ) {
		if ( isset( $this->checkout_fields['advanced']['order_coupon'] ) ) {
			$messages        = '';
			$success_message = $this->checkout_fields['advanced']['order_coupon']['coupon_success_message_heading'];
			ob_start();
			foreach ( WC()->cart->get_coupons() as $code => $coupon ) {
				$parse_message = WFACP_Product_Switcher_Merge_Tags::parse_coupon_merge_tag( $success_message, $coupon );
				$remove_link   = sprintf( "<a href='%s' class='wfacp_remove_coupon' data-coupon='%s'>%s</a>", add_query_arg( [
					'remove_coupon' => $code,
				], wc_get_checkout_url() ), $code, __( 'Remove', 'woocommerce' ) );
				$messages      .= sprintf( '<div class="wfacp_single_coupon_msg">%s %s</div>', $parse_message, $remove_link );
			}

			$fragments['.wfacp_coupon_field_msg'] = '<div class="wfacp_coupon_field_msg">' . $messages . '</div>';

		}

		return $fragments;
	}

	public function change_template_location( $template, $template_name, $template_path ) {
		if ( 'cart/cart-shipping.php' === $template_name ) {
			$template = WFACP_TEMPLATE_COMMON . '/shipping-options-form.php';
		} elseif ( 'checkout/payment.php' === $template_name ) {
			$template = WFACP_TEMPLATE_COMMON . '/checkout/payment.php';
		}

		return $template;
	}

	/**
	 * @param $fields
	 *
	 * @return array
	 * @since 1.0
	 */
	public function woocommerce_checkout_fields( $fields ) {

		$template_fields = $this->get_checkout_fields();
		if ( isset( $fields['account'] ) ) {
			$template_fields['account'] = $fields['account'];
		}

		$template_fields = apply_filters( 'wfacp_checkout_fields', $template_fields, $fields );
		$is_billing_only = wc_ship_to_billing_address_only();
		if ( true == $is_billing_only && ! isset( $template_fields['shipping'] ) ) {
			$template_fields['shipping'] = $fields['shipping'];

		}

		return $template_fields;
	}

	public function get_checkout_fields() {
		return apply_filters( 'wfacp_get_checkout_fields', $this->checkout_fields );
	}


	public function set_priority_of_form_fields( $template_fields, $fields ) {

		foreach ( $template_fields as $type => $sections ) {
			if ( empty( $sections ) ) {
				continue;
			}
			foreach ( $sections as $key => $field ) {
				$template_fields[ $type ][ $key ]['priority'] = 0;
				if ( ( 'wfacp_wysiwyg' == $field['type'] || 'hidden' == $field['type'] ) && isset( $field['required'] ) ) {
					unset( $template_fields[ $type ][ $key ]['required'] );
				}
			}
		}

		return $template_fields;
	}

	/**
	 * Handle first and last name of shipping and billing field
	 *
	 * @param $template_fields
	 *
	 * @return array
	 *
	 * @since 1.6.0
	 */
	public function handling_checkout_post_data( $template_fields ) {
		if ( isset( $_POST['ship_to_different_address'] ) ) {
			add_filter( 'woocommerce_cart_needs_shipping_address', [ $this, 'enable_need_shipping' ] );
		}

		if ( isset( $_POST['ship_to_different_address'] ) && isset( $_POST['wfacp_billing_same_as_shipping'] ) && $_POST['wfacp_billing_same_as_shipping'] == 0 ) {
			$address_fields = [ 'first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'postcode', 'country', 'state' ];
			foreach ( $address_fields as $key ) {
				$b_key = 'billing_' . $key;
				if ( isset( $template_fields['billing'][ $b_key ] ) && in_array( $b_key, [
						'billing_first_name',
						'billing_last_name',
					] ) && ! isset( $template_fields['billing'][ $b_key ]['address_group'] ) ) {

					continue;
				}

				if ( 'billing' == $this->get_shipping_billing_index() && isset( $template_fields['billing'][ $b_key ]['required'] ) ) {
					unset( $template_fields['billing'][ $b_key ]['required'] );
				}
				if ( $key == 'postcode' ) {
					unset( $template_fields['billing'][ $b_key ]['validate'] );
				}
			}
		}

		/**
		 * When billing address not present in form then we assign shipping field values to billing fields values
		 */
		if ( isset( $_POST['_wfacp_post_id'] ) && ! wc_string_to_bool( $this->have_billing_address ) && wc_string_to_bool( $this->have_shipping_address ) ) {

			$available_fields   = [ 'company', 'address_2', 'country', 'city', 'state', 'postcode', 'address_1' ];
			$billing_first_name = false;
			$billing_last_name  = false;
			if ( ! isset( $_POST['billing_first_name'] ) ) {
				$available_fields[] = 'first_name';

			} else {
				$billing_first_name = true;
			}

			if ( ! isset( $_POST['billing_last_name'] ) ) {
				$available_fields[] = 'last_name';

			} else {
				$billing_last_name = true;
			}

			foreach ( $available_fields as $key ) {
				$b_key = 'billing_' . $key;
				$s_key = 'shipping_' . $key;
				if ( isset( $template_fields['shipping'][ $s_key ] ) ) {
					$template_fields['billing'][ $b_key ]       = $template_fields['shipping'][ $s_key ];
					$template_fields['billing'][ $b_key ]['id'] = $b_key;
					if ( isset( $template_fields['billing'][ $b_key ]['required'] ) ) {
						unset( $template_fields['billing'][ $b_key ]['required'] );
					}
					$_POST[ $b_key ]    = wc_clean( $_POST[ $s_key ] );
					$_REQUEST[ $b_key ] = wc_clean( $_POST[ $s_key ] );
				}
			}


			if ( ! isset( $template_fields['shipping']['shipping_first_name'] ) && true == $billing_first_name ) {
				$template_fields['shipping']['shipping_first_name']       = $template_fields['billing']['billing_first_name'];
				$template_fields['shipping']['shipping_first_name']['id'] = 'shipping_first_name';
				if ( isset( $template_fields['shipping']['shipping_first_name']['required'] ) ) {
					unset( $template_fields['shipping']['shipping_first_name']['required'] );
				}
				$_POST['shipping_first_name']    = wc_clean( $_POST['billing_first_name'] );
				$_REQUEST['shipping_first_name'] = wc_clean( $_POST['billing_first_name'] );
			}

			if ( ! isset( $template_fields['shipping']['shipping_last_name'] ) && true == $billing_last_name ) {
				$template_fields['shipping']['shipping_last_name'] = $template_fields['billing']['billing_last_name'];
				if ( isset( $template_fields['shipping']['shipping_last_name']['required'] ) ) {
					unset( $template_fields['shipping']['shipping_last_name']['required'] );
				}
				$template_fields['shipping']['shipping_last_name']['id'] = 'shipping_last_name';
				$_POST['shipping_last_name']                             = wc_clean( $_POST['billing_last_name'] );
				$_REQUEST['shipping_last_name']                          = wc_clean( $_POST['billing_last_name'] );
			}
		}


		return $template_fields;
	}

	public function enable_need_shipping() {
		return true;
	}


	public function woocommerce_checkout_update_order_meta( $order_id ) {


		$address_fields = [ 'company', 'address_1', 'address_2', 'city', 'postcode', 'country', 'state' ];

		$billing_address  = $this->have_billing_address;
		$shipping_address = $this->have_shipping_address;
		$index            = $this->get_shipping_billing_index();

		// copy all data from billing to shipping
		if ( $billing_address && $shipping_address && $index == 'shipping' && ! isset( $_POST['shipping_same_as_billing'] ) && ! isset( $_POST['ship_to_different_address'] ) ) {

			foreach ( $address_fields as $field ) {
				$key = 'billing_' . $field;
				if ( isset( $_REQUEST[ $key ] ) ) {
					update_post_meta( $order_id, '_shipping_' . $field, wc_clean( $_REQUEST[ $key ] ) );
				}
			}
		}
		// copy all data from shipping to billing
		if ( $billing_address && $shipping_address && $index == 'billing' && ! isset( $_POST['billing_same_as_shipping'] ) && isset( $_POST['ship_to_different_address'] ) ) {

			foreach ( $address_fields as $field ) {
				$key = 'shipping_' . $field;
				if ( isset( $_REQUEST[ $key ] ) ) {
					update_post_meta( $order_id, '_billing_' . $field, wc_clean( $_REQUEST[ $key ] ) );
				}
			}
		}


		// handle first,and last name
		$arr_data = [];
		$arr      = [
			'billing_first_name'  => 'shipping_first_name',
			'billing_last_name'   => 'shipping_last_name',
			'shipping_first_name' => 'billing_first_name',
			'shipping_last_name'  => 'shipping_last_name',
		];


		foreach ( $arr as $a_key => $second_key ) {
			if ( isset( $_REQUEST[ $a_key ] ) ) {
				$arr_data[ '_' . $a_key ] = $_REQUEST[ $a_key ];
				continue;
			}
			if ( isset( $_REQUEST[ $second_key ] ) ) {
				$arr_data[ '_' . $a_key ] = $_REQUEST[ $second_key ];
				continue;
			}

		}


		if ( ! empty( $arr_data ) ) {
			foreach ( $arr_data as $meta_key => $meta_value ) {
				update_post_meta( $order_id, $meta_key, wc_clean( $meta_value ) );
			}

		}

		if ( isset( $_REQUEST['wfacp_source'] ) ) {
			$wfacp_source = $_REQUEST['wfacp_source'];
			if ( filter_var( $wfacp_source, FILTER_VALIDATE_URL ) ) {
				update_post_meta( $order_id, '_wfacp_source', $wfacp_source );
			}
		}
	}

	/**
	 * Return shipping or billing
	 * get which address field is hidden in form Shipping or billing
	 * @return string
	 */
	public function get_shipping_billing_index() {

		if ( $this->have_shipping_address && $this->have_billing_address ) {
			$have_billing_address_index  = absint( $this->have_billing_address_index );
			$have_shipping_address_index = absint( $this->have_shipping_address_index );
			if ( $have_billing_address_index < $have_shipping_address_index ) {
				return 'shipping';
			} else {
				return 'billing';
			}
		}

		return '';
	}

	/**
	 * @param $template_fields
	 *
	 * @return mixed
	 * @since 1.6.0
	 */
	public function correct_country_state_locals( $template_fields ) {

		// check for billing country locale values
		if ( '' !== WC()->checkout->get_value( 'billing_country' ) ) {
			$locale  = WC()->countries->get_country_locale();
			$country = WC()->checkout->get_value( 'billing_country' );
			if ( isset( $locale[ $country ] ) && isset( $template_fields['billing'] ) ) {

				$array_without_key = [];
				foreach ( $template_fields['billing'] as $key => $value ) {
					$array_without_key[ str_replace( 'billing_', '', $key ) ] = $value;
				}
				$get_filtered_array = wc_array_overlay( $array_without_key, $locale[ $country ] );
				foreach ( $template_fields['billing'] as $key => $value ) {
					$truncated_key = str_replace( 'billing_', '', $key );
					if ( isset( $get_filtered_array[ $truncated_key ] ) ) {
						$template_fields['billing'][ $key ] = $get_filtered_array[ $truncated_key ];
					}
				}
			}
		}

		// check for shipping country locale values
		if ( '' !== WC()->checkout->get_value( 'shipping_country' ) ) {
			$locale  = WC()->countries->get_country_locale();
			$country = WC()->checkout->get_value( 'shipping_country' );

			if ( isset( $locale[ $country ] ) && isset( $template_fields['shipping'] ) ) {

				$array_without_key = [];
				foreach ( $template_fields['shipping'] as $key => $value ) {
					$array_without_key[ str_replace( 'shipping_', '', $key ) ] = $value;
				}
				$get_filtered_array = wc_array_overlay( $array_without_key, $locale[ $country ] );
				foreach ( $template_fields['shipping'] as $key => $value ) {
					$truncated_key = str_replace( 'shipping_', '', $key );
					if ( isset( $get_filtered_array[ $truncated_key ] ) ) {
						$template_fields['shipping'][ $key ] = $get_filtered_array[ $truncated_key ];
					}
				}
			}
		}

		return $template_fields;
	}

	public function get_google_webfonts() {
		$url    = 'https://www.googleapis.com/webfonts/v1/webfonts?key=key_here&&sort=alpha';
		$raw    = file_get_contents( $url, 0, null, null );
		$result = json_decode( $raw );

		$font_list = array();
		foreach ( $result->items as $font ) {
			$font_list[] .= $font->family;
		}

	}

	public function mobile_layout_order() {
		return $this->mobile_layout_order;
	}

	public function get_setup_sidebar_data() {

		return $this->sidebar_layout_order;
	}

	public function get_view() {

		extract( array( 'data' => $this->data ) ); //@codingStandardsIgnoreLine

		do_action( 'wfacp_before_template_load' );
		include $this->get_template_url();
		do_action( 'wfacp_after_template_load' );
		exit;
	}

	public function get_template_url() {
		return $this->template_dir . '/views/view.php';
	}

	public function customizer_data() {
		return $this->customizer_data;
	}

	public function get_slug() {
		return $this->template_slug;
	}

	public function get_url() {
		return $this->url;
	}

	public function get_wfacp_id() {
		return $this->wfacp_id;
	}

	public function set_wfacp_id( $wfacp_id = false ) {
		if ( false !== $wfacp_id ) {
			$this->wfacp_id = $wfacp_id;
		}
	}

	public function get_fieldsets() {
		return apply_filters( 'wfacp_get_fieldsets', $this->fieldsets );
	}

	public function get_fields() {
		return $this->fields;
	}

	final public function set_data( $data = false ) {
		$data = WFACP_Common::get_fieldset_data( WFACP_Common::get_id() );

		foreach ( $data as $key => $val ) {
			$this->{$key} = $val;
		}
		$this->have_billing_address  = wc_string_to_bool( $data['have_billing_address'] );
		$this->have_shipping_address = wc_string_to_bool( $data['have_shipping_address'] );
		$this->have_coupon_field     = isset( $data['have_coupon_field'] ) ? wc_string_to_bool( $data['have_coupon_field'] ) : $this->have_coupon_field;
		$this->have_shipping_method  = isset( $data['have_shipping_method'] ) ? wc_string_to_bool( $data['have_shipping_method'] ) : $this->have_shipping_method;
		$this->checkout_fields       = WFACP_Common::get_checkout_fields( WFACP_Common::get_id() );

	}

	public function control_filter( $control ) {
		if ( in_array( $control->section, $this->get_sections() ) ) {
			return true;
		}

		return false;
	}

	public function get_sections() {
		return $this->sections;
	}

	public function get_section( $wp_customize = false ) {
		/** WFACPKirki is required to customizer */
		if ( ! class_exists( 'WFACPKirki' ) ) {
			return;
		}

		if ( false == $wp_customize ) {
			return;
		}

		if ( ! is_array( $this->customizer_data ) || count( $this->customizer_data ) == 0 ) {
			return;
		}

		foreach ( $this->customizer_data as $panel_single ) {
			foreach ( $panel_single as $panel_key => $panel_arr ) {
				/** Panel */
				$maybe_panel = true;
				if ( isset( $panel_arr['panel'] ) && 'no' == $panel_arr['panel'] ) {
					/** No need to register panel */
					$maybe_panel = false;
				} else {
					$arr = $panel_arr['data'];
					$arr = array_merge( $arr, array(
						'capability'     => 'edit_theme_options',
						'theme_supports' => '',
					) );
					$wp_customize->add_panel( $panel_key, $arr );
				}

				if ( ! is_array( $panel_arr['sections'] ) || count( $panel_arr['sections'] ) == 0 ) {
					continue;
				}

				/** Section */

				foreach ( $panel_arr['sections'] as $section_key => $section_arr ) {
					$section_key_final = $panel_key . '_' . $section_key;

					$this->sections[] = $section_key_final;

					if ( isset( $section_arr['data'] ) ) {
						$arr = $section_arr['data'];
					}

					if ( true === $maybe_panel ) {
						$arr = array_merge( $arr, array(
							'panel' => $panel_key,
						) );
					}

					$wp_customize->add_section( $section_key_final, $arr );

					/** Fields - will add using wfacpkirki */

					/** Set the selective part */
					if ( ! is_array( $section_arr['fields'] ) || count( $section_arr['fields'] ) == 0 ) {
						continue;
					}

					foreach ( $section_arr['fields'] as $field_key => $field_data ) {
						$field_key_final = $section_key_final . '_' . $field_key;
						$field_key_final = WFACP_Core()->template_loader->customizer_key_prefix . '[' . $field_key_final . ']';

						/** Checking if wfacp_partial class exist */
						if ( ! isset( $field_data['wfacp_partial'] ) || ! is_array( $field_data['wfacp_partial'] ) || ! isset( $field_data['wfacp_partial']['elem'] ) ) {
							continue;
						}

						$callback = isset( $field_data['wfacp_partial']['callback'] ) ? $field_data['wfacp_partial']['callback'] : 'render_callback';

						$wp_customize->selective_refresh->add_partial( $field_key_final, array(
							'selector'        => $field_data['wfacp_partial']['elem'],
							'render_callback' => array( $this, $callback ),
							'primary_setting' => $field_key_final,
						) );

					}
				}
			}
		}
	}

	public function get_changeset( $key ) {
		if ( ! empty( $this->change_set ) && $this->change_set[ $key ] ) {
			return $this->change_set[ $key ];
		}

		return '';
	}

	public function set_changeset( $changeset = [] ) {
		$this->change_set = $changeset;
	}

	public function render_callback( $data ) {

		$partial_key_base = $data->id_data();
		if ( is_array( $partial_key_base ) && isset( $partial_key_base['keys'] ) ) {
			$partial_key         = $partial_key_base['keys'][0];
			switch ( $partial_key ) {
				case 'wfacp_header_top_logo':
					$logo = WFACP_Common::get_option( $partial_key );
					$no_logo_img = WFACP_PLUGIN_URL . '/admin/assets/img/no_logo.jpg';
					?>
                    <img src="<?php echo $logo ? $logo : $no_logo_img; ?>" alt="<?php bloginfo( 'name' ); ?>"
                         title="<?php bloginfo( 'name' ); ?>"/>
					<?php
					$logo_img_html = ob_get_clean();

					return $logo_img_html;
					break;
				default:
					$value = WFACP_Common::get_option( $partial_key );
					if ( ! empty( $value ) ) {
						$value = nl2br( $value );
					}

					return $value;
					break;
			}
		}
	}

	public function get_customizer_data() {

		$customizer_support = apply_filters( 'wfacp_customizer_supports', $this->available_fields );
		$fontpath           = WFACP_WEB_FONT_PATH . '/fonts.json';
		$google_fonts       = json_decode( file_get_contents( $fontpath ) );
		$web_fonts          = apply_filters( 'wfacp_customizer_fonts', $google_fonts );

		foreach ( $web_fonts as $web_font_family ) {

			if ( $web_font_family != 'Open Sans' ) {
				$this->web_google_fonts[ $web_font_family ] = $web_font_family;
			}
		}

		if ( in_array( 'layout', $customizer_support ) ) {
			/** PANEL: LAYOUT */
			require_once __DIR__ . '/customizer-options/class-section-layout.php';
			/* Customer Suppor Layout */
			require_once __DIR__ . '/customizer-options/class-section-customer-care.php';
			$layout_panel            = WFACP_Sectionlayout::get_instance( $this )->layout_settings();
			$this->customizer_data[] = $layout_panel;

		}

		if ( in_array( 'header', $customizer_support ) ) {
			/** PANEL: HEADER */
			require_once __DIR__ . '/customizer-options/class-section-header.php';
			$header_panel            = WFACP_SectionHeader::get_instance( $this )->header_settings();
			$this->customizer_data[] = $header_panel;
		}

		if ( in_array( 'product', $customizer_support ) ) {
			/* Product PRODUCT */
			require_once __DIR__ . '/customizer-options/class-section-product-details.php';
			$productDetails_panel    = WFACP_SectionProductDetails::get_instance( $this )->productDetails_settings();
			$this->customizer_data[] = $productDetails_panel;
		}

		if ( in_array( 'gbadge', $customizer_support ) ) {
			/* Guarantee Badge GUARANTEE BADGE */
			require_once __DIR__ . '/customizer-options/class-section-guarantee-badge.php';
			$gbadge_panel            = WFACP_SectionGbadge::get_instance( $this )->gbadge_settings();
			$this->customizer_data[] = $gbadge_panel;
		}

		if ( in_array( 'listing', $customizer_support ) ) {
			/* Benefits BANEFITS */
			require_once __DIR__ . '/customizer-options/class-section-listing.php';
			$list_panel              = WFACP_SectionListing::get_instance( $this )->listing_settings();
			$this->customizer_data[] = $list_panel;
		}
		if ( in_array( 'testimonial', $customizer_support ) ) {
			/* Testimonial  */
			require_once __DIR__ . '/customizer-options/class-section-testimonial.php';
			$testimonial_panel       = WFACP_SectionTestimonial::get_instance( $this )->testimonial_settings();
			$this->customizer_data[] = $testimonial_panel;
		}
		if ( in_array( 'widget', $customizer_support ) ) {
			/* Assurance  */
			require_once __DIR__ . '/customizer-options/class-section-twidget.php';
			$twidget_panel           = WFACP_SectionTwidget::get_instance( $this )->twidget_settings();
			$this->customizer_data[] = $twidget_panel;
		}

		if ( in_array( 'customer-care', $customizer_support ) ) {
			/* Customer Suppor Layout */
			require_once __DIR__ . '/customizer-options/class-section-customer-care.php';
			$customer_care_panel     = WFACP_SectionCustomerCare::get_instance( $this )->customer_care_settings();
			$this->customizer_data[] = $customer_care_panel;
		}

		if ( in_array( 'promises', $customizer_support ) ) {
			/* Promises Layout */
			require_once __DIR__ . '/customizer-options/class-section-promises.php';
			$promises_panel          = WFACP_SectionPromises::get_instance( $this )->promises_settings();
			$this->customizer_data[] = $promises_panel;
		}


		/** PANEL: Form LAYOUT */
		require_once __DIR__ . '/customizer-options/class-section-form.php';
		$form_panel              = WFACP_SectionForm::get_instance( $this )->form_settings();
		$this->customizer_data[] = $form_panel;


		if ( in_array( 'html_widget_1', $customizer_support ) ) {

			/* Html Widget Layout */
			require_once __DIR__ . '/customizer-options/class-section-html-widget.php';
			$html_widget_settings_panel_1 = WFACP_SectionHtmlWidgets::get_instance( $this )->html_widget_settings();
			$this->customizer_data[]      = $html_widget_settings_panel_1;

		}


		if ( in_array( 'footer', $customizer_support ) ) {
			/** PANEL: FOOTER */
			require_once __DIR__ . '/customizer-options/class-section-footer.php';
			$footer_panel            = WFACP_SectionFooter::get_instance( $this )->footer_settings();
			$this->customizer_data[] = $footer_panel;
		}

		/** PANEL: Product Switcher*/
		require_once __DIR__ . '/customizer-options/class-product-switcher.php';
		$switcher_settings       = WFACP_Product_Switcher_Field::get_instance( $this )->get_settings();
		$this->customizer_data[] = $switcher_settings;

		/** PANEL: STYLE */
		require_once __DIR__ . '/customizer-options/class-section-styles.php';
		$style_panel             = WFACP_SectionStyles::get_instance( $this )->style_settings();
		$this->customizer_data[] = $style_panel;


		/** PANEL: Custom CSS LAYOUT */
		require_once __DIR__ . '/customizer-options/class-section-custom-css.php';
		$css_panel               = WFACP_SectionCustomCss::get_instance( $this )->custom_css_settings();
		$this->customizer_data[] = $css_panel;


		$this->customizer_data = apply_filters( 'wfacp_customizer_fieldset', $this->customizer_data, $this );

		/** Set default values against all customizer keys */
		WFACP_Common::set_customizer_fields_default_vals( $this->customizer_data );

	}

	public function wfacp_get_header() {
		return $this->template_dir . '/views/template-parts/header.php';
	}

	public function wfacp_get_footer() {
		return $this->template_dir . '/views/template-parts/footer.php';
	}

	public function wfacp_get_sidebar() {

		return $this->template_dir . '/views/template-parts/sidebar.php';
	}

	public function wfacp_get_product() {

		return $this->template_dir . '/views/template-parts/product.php';
	}

	public function have_shipping_address() {

		return $this->have_shipping_address;
	}

	public function have_billing_address() {

		return $this->have_billing_address;
	}

	public function wfacp_partial_product_image( $data ) {
		$partial_key_base = $data->id_data();

		if ( is_array( $partial_key_base ) && isset( $partial_key_base['keys'] ) ) {
			$partial_key = $partial_key_base['keys'][0];
			$logo        = WFACP_Common::get_option( $partial_key );
			$no_logo_img = $this->img_path . 'product_default_icon.jpg';
		}
		ob_start();
		?>
        <img class="wfacp-prodct-image" src="<?php echo $logo ? $logo : $no_logo_img; ?>" alt="<?php bloginfo( 'name' ); ?>" title="<?php bloginfo( 'name' ); ?>"/>
		<?php

		$wfacp_partial_product_image_html = ob_get_clean();

		return $wfacp_partial_product_image_html;

	}

	public function wfacp_partial_ft_image( $data ) {
		$partial_key_base = $data->id_data();

		if ( is_array( $partial_key_base ) && isset( $partial_key_base['keys'] ) ) {
			$partial_key = $partial_key_base['keys'][0];
			$logo        = WFACP_Common::get_option( $partial_key );
			$no_logo_img = $this->img_path . 'woo_checkout_logo.png';

			ob_start();
			?>
            <img src="<?php echo $logo ? $logo : $no_logo_img; ?>" alt="<?php bloginfo( 'name' ); ?>"
                 title="<?php bloginfo( 'name' ); ?>"/>
			<?php
			$wfacp_partial_ft_image = ob_get_clean();

			return $wfacp_partial_ft_image;
		}
	}

	public function wfacp_header_logo( $data ) {
		$partial_key_base = $data->id_data();

		if ( is_array( $partial_key_base ) && isset( $partial_key_base['keys'] ) ) {
			$partial_key = $partial_key_base['keys'][0];
			$logo        = WFACP_Common::get_option( $partial_key );
			$no_logo_img = $this->img_path . 'woo_checkout_logo.png';

			ob_start();
			?>


            <img class="wfacp-logo" src="<?php echo $logo ? $logo : $no_logo_img; ?>">


			<?php
			$wfacp_header_logo = ob_get_clean();

			return $wfacp_header_logo;
		}
	}

	public function wfacp_changed_step_text( $data ) {
		$partial_key_base = $data->id_data();

		if ( is_array( $partial_key_base ) && isset( $partial_key_base['keys'] ) ) {
			$partial_key     = $partial_key_base['keys'][0];
			$step_value_text = WFACP_Common::get_option( $partial_key );
			$text_class_nner = ( ! empty( $step_value_text ) ) ? 'wfacp_step_text_have' : 'wfacp_step_text_nohave';

			ob_start();
			?>
            <a href='javascript:void(0)' class="<?php echo $text_class_nner; ?>"><?php echo $step_value_text; ?></a>
			<?php
			$step_value_text_html = ob_get_clean();

			return $step_value_text_html;
		}
	}

	public function form_pop_up_content() {
		?>

        <div id="wfacp_form_popup_content" style="display: none;">

            <h3>
				<?php _e( 'CSS Ready Classes', 'woo-arrow-checkout' ); ?>
            </h3>
            <div class="wfacp_des_wrap">
				<?php _e( 'Here are set of CSS classes that can be used to style the checkout form', 'woo-arrow-checkout' ); ?>

            </div>


            <table class="table widefat">
                <thead>
                <tr>
                    <td><?php _e( 'Title', 'woo-arrow-checkout' ); ?></td>
                    <td style="width: 70%;">
						<?php _e( 'Classes', 'woo-arrow-checkout' ); ?>
                    </td>

                </tr>
                </thead>
                <tbody>

                <tr>
                    <td><?php _e( 'To create full width field', 'woo-arrow-checkout' ); ?></td>
                    <td><input type="text" readonly onClick="this.select()" value='wfacp-col-full'/></td>
                </tr>

                <tr>
                    <td><?php _e( 'To create two columns structure and set field left side', 'woo-arrow-checkout' ); ?></td>
                    <td><input type="text" readonly onClick="this.select()" value='wfacp-col-left-half'/></td>
                </tr>

                <tr>
                    <td><?php _e( 'To create two columns structure and set field right side', 'woo-arrow-checkout' ); ?></td>
                    <td><input type="text" readonly onClick="this.select()" value='wfacp-col-right-half'/></td>
                </tr>

                <tr>
                    <td><?php _e( 'To create three columns structure and set field left side', 'woo-arrow-checkout' ); ?></td>
                    <td><input type="text" readonly onClick="this.select()" value='wfacp-col-left-third'/></td>
                </tr>
                <tr>
                    <td><?php _e( 'To create three columns structure and set field middle', 'woo-arrow-checkout' ); ?></td>
                    <td><input type="text" readonly onClick="this.select()" value='wfacp-col-middle-third'/></td>
                </tr>
                <tr>
                    <td><?php _e( 'To create three columns structure and set field right side', 'woo-arrow-checkout' ); ?></td>
                    <td><input type="text" readonly onClick="this.select()" value='wfacp-col-right-third'/></td>
                </tr>
                <tr>
                    <td><?php _e( 'To create two third columns structure', 'woo-arrow-checkout' ); ?></td>
                    <td><input type="text" readonly onClick="this.select()" value='wfacp-col-two-third'/></td>
                </tr>
                <tr>
                    <td><?php _e( 'To create field from new line use clearfix', 'woo-arrow-checkout' ); ?></td>
                    <td><input type="text" readonly onClick="this.select()" value='wfacp-col-clearfix'/></td>
                </tr>


                </tbody>


            </table>

        </div>

		<?php
	}

	public function get_exluded_sidebar_sections() {
		return $this->exluded_sidebar_sections;
	}

	public function active_sidebar() {
		return $this->current_active_sidebar = WFACP_Common::get_option( 'wfacp_layout_section_' . $this->template_slug . '_sidebar_layout_order' );
	}

	public function excluded_other_widget() {

		$other_layout_widget = WFACP_Common::get_option( 'wfacp_layout_section_' . $this->template_slug . '_other_layout_widget' );

		$tempArr = [];
		if ( is_array( $other_layout_widget ) && count( $other_layout_widget ) > 0 ) {
			$this->current_active_sidebar = $other_layout_widget;
			$tempArr                      = $this->current_active_sidebar;
		}

		return $tempArr;
	}


	final public function wfacp_get_form() {

		$template = WFACP_TEMPLATE_COMMON . '/form.php';
		$temp     = apply_filters( 'wfacp_form_template', $template );

		if ( ! empty( $temp ) ) {
			$template = $temp;
		}

		return $template;
	}

	final public function get_back_button( $current_action, $formData = [] ) {
		include WFACP_TEMPLATE_COMMON . '/back-button.php';
	}

	final public function get_next_button( $current_action, $formData = [] ) {
		include WFACP_TEMPLATE_COMMON . '/next-button.php';
	}

	final public function get_module( $data, $return = false, $type, $section_key ) {

		$wfacp_device_type = $this->device_type;
		$file_path         = WFACP_TEMPLATE_MODULE_DIR;
		if ( 'wfacp_html_widget' === $type ) {
			$file_path = WFACP_TEMPLATE_COMMON . '/template-parts/sections';
		}


		if ( ! $return && ( $type != '' ) ) {

			if ( file_exists( $file_path . '/' . $type . '.php' ) ) {

				include( $file_path . '/' . $type . '.php' );

			}
		}

	}

	/**
	 * Prepopulate field data From URL
	 * if data not present in URL then we check default data and populate the data
	 *
	 * @param $value
	 * @param $key
	 * @param $field
	 *
	 * @return mixed|string
	 */
	public function pre_populate_from_get_parameter( $value, $key, $field ) {

		if ( '' == $key ) {
			return $value;
		}
		if ( isset( $_REQUEST[ $key ] ) ) {
			return urldecode( $_REQUEST[ $key ] );
		}
		if ( isset( $field['default'] ) && '' !== $field['default'] ) {
			return $field['default'];
		} else if ( isset( $field['type'] ) && 'select' == $field['type'] && ! empty( $field['options'] ) ) {
			$options = array_keys( $field['options'] );

			return $options[0];
		}

		return $value;
	}


	public function remove_form_billing_and_shipping_html( $template ) {

		if ( in_array( $template, [ 'checkout/form-billing.php', 'checkout/form-billing.php', 'cart/shipping-calculator.php' ] ) ) {
			return WFACP_TEMPLATE_DIR . '/empty.php';
		}

		return $template;

	}

	public function replace_recurring_total_shipping( $template, $template_name ) {

		if ( in_array( $template_name, [ 'checkout/recurring-totals.php' ] ) ) {
			return WFACP_TEMPLATE_COMMON . '/checkout/recurring-totals.php';
		}

		return $template;
	}

	public function remove_admin_bar() {
		return false;
	}

	public function unset_blank_key_value( $array_for_check ) {
		if ( is_array( $array_for_check ) && count( $array_for_check ) > 0 ) {
			foreach ( $array_for_check as $key => $value ) {
				if ( is_array( $value ) && count( $value ) > 0 ) {
					continue;
				}
				if ( $value == '' ) {
					unset( $array_for_check[ $key ] );
				}
			}
		}

		return $array_for_check;

	}


	public function show_account_fields( $key, $field, $dvalue ) {
		include WFACP_TEMPLATE_COMMON . '/account.php';
	}

	public function woocommerce_countries_shipping_countries( $countries ) {
		if ( is_array( $countries ) && count( $countries ) == 0 ) {
			$countries = WC()->countries->get_countries();
		}

		return $countries;
	}

	public function woocommerce_countries_allowed_countries( $countries ) {
		if ( is_array( $countries ) && count( $countries ) == 0 ) {
			$countries = WC()->countries->get_countries();
		}

		return $countries;
	}

	public function remove_add1_add2_local_field_selector( $locale_fields ) {
		if ( isset( $locale_fields['address_1'] ) ) {
			unset( $locale_fields['address_1'] );
		}
		if ( isset( $locale_fields['address_2'] ) ) {
			unset( $locale_fields['address_2'] );
		}

		return $locale_fields;
	}

	public function add_viewport_meta() {
		include WFACP_TEMPLATE_COMMON . '/meta.php';
	}

	public function reattach_neccessary_hooks() {
		if ( ! has_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment' ) ) {
			add_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment' );
		}

		if ( has_action( 'woocommerce_checkout_order_review', 'woocommerce_order_review' ) ) {
			remove_action( 'woocommerce_checkout_order_review', 'woocommerce_order_review' );
		}
	}


	public function display_hide_payment_box_heading() {

		if ( ! WC()->cart->needs_payment() ) {
			?>
            <style>
                .wfacp_payment .wfacp-comm-title {
                    display: none
                }
            </style>
			<?Php
		}

	}

	public function display_shipping_name() {
		$packages   = WC()->shipping->get_packages();
		$resultHtml = '';
		foreach ( $packages as $i => $package ) {
			$available_methods = $package['rates'];
			if ( is_array( $available_methods ) && count( $available_methods ) > 0 ) {
				foreach ( $available_methods as $method ) {
					$resultHtml .= sprintf( '<label style="font-weight: normal;" for="shipping_method_%1$s_%2$s">%3$s</label>', $i, esc_attr( sanitize_title( $method->id ) ), wc_cart_totals_shipping_method_label( $method ) );
				}
			}
		}

		return $resultHtml;
	}

	public function change_text_on_order_summary() {
		add_filter( 'wc_cart_totals_shipping_method_cost', [ $this, 'wc_check_matched_rate' ], 10 );
	}


	/**
	 * @param $total
	 *
	 * @return false|string
	 *
	 * check shipping total if its less then or zero and check shipping name
	 */
	public function wc_check_matched_rate( $total ) {

		$amt = (int) WC()->cart->get_shipping_total();

		if ( $amt == 0 ) {
			$label = $this->check_shipping_name();
			if ( $label != '' ) {
				return $label;
			} else {
				return $total;
			}
		}

		return $total;
	}

	/**
	 * @return false|string
	 *
	 * Return Shipping Name when Local Pickup Activate in shipping
	 */

	public function check_shipping_name() {
		$packages       = WC()->shipping->get_packages();
		$resultHtml     = '';
		$chooseShipping = wc_get_chosen_shipping_method_ids();

		foreach ( $packages as $i => $package ) {

			$available_methods = $package['rates'];

			if ( is_array( $available_methods ) && count( $available_methods ) > 0 ) {
				foreach ( $available_methods as $method ) {

					if ( strpos( $method->id, 'local_pickup' ) !== false && ( is_array( $chooseShipping ) && strpos( $chooseShipping[0], 'local_pickup' ) !== false ) ) {
						ob_start();
						printf( '<label style="font-weight: normal;" for="shipping_method_%1$s_%2$s">%3$s</label>', $i, esc_attr( sanitize_title( $method->id ) ), __( 'Free', 'woocommerce' ) );
						$resultHtml = ob_get_clean();
					}
				}
			}

			return $resultHtml;

		}
	}


	public function display_undo_message() {

		if ( ! wp_doing_ajax() ) {
			return;
		} else {
			//WC()->cart->removed_cart_contents = [];
		}
		$settings = WFACP_Core()->public->get_product_settings();

		if ( isset( $settings['add_to_cart_setting'] ) && $settings['add_to_cart_setting'] != 1 && ! WFACP_Core()->public->is_checkout_override() ) {
			return;
		}

		$cart_contents = WC()->cart->removed_cart_contents;
		if ( empty( WC()->cart->removed_cart_contents ) ) {
			return;
		}

		foreach ( $cart_contents as $cart_item_key => $cart_item ) {
			$item_data = wc_get_product( $cart_item['product_id'] );
			if ( ! $item_data instanceof WC_Product ) {
				continue;
			}
			if ( isset( $cart_item['_wfob_options'] ) ) {
				continue;
			}
			if ( isset( $cart_item['xlwcfg_gift_id'] ) ) {
				continue;
			}
			if ( true === apply_filters( 'wfacp_show_undo_message_for_item', false, $cart_item ) ) {
				continue;
			}

			$item_key   = $cart_item_key;
			$item_class = 'wfacp_restore_cart_item';
			$item_icon  = __( 'Undo?', 'woocommerce' );
			if ( isset( $cart_item['_wfacp_product'] ) && ! WFACP_Core()->public->is_checkout_override() ) {
				$item_key   = $cart_item['_wfacp_product_key'];
				$wfacp_data = $cart_item['_wfacp_options'];
				$item_title = $wfacp_data['title'];

			} else {
				$item_title = $item_data->get_name();
			}
			if ( $item_data && $item_data->is_in_stock() && $item_data->has_enough_stock( $cart_item['quantity'] ) ) {
				/* Translators: %s Product title. */
				$removed_notice = sprintf( __( '%s removed. ', 'woocommerce' ), $item_title );
				$removed_notice .= sprintf( '<a href="javascript:void(0)" class="%s" data-cart_key="%s" data-item_key="%s">%s</a>', $item_class, $cart_item_key, $item_key, $item_icon );
			} else {
				/* Translators: %s Product title. */
				$removed_notice = sprintf( __( '%s removed. ', 'woocommerce' ), $item_title );
			}
			echo "<div class='wfacp_product_restore_wrap'>" . $removed_notice . '</div>';
		}

	}

	/**
	 * Forcefully change order button text for authorize and paypal express gateway
	 *
	 * @param $gateways
	 *
	 * @return mixed
	 */
	public function change_payment_gateway_text( $gateways ) {
		$template_slug = $this->template_slug;
		$orderText     = WFACP_Common::get_option( 'wfacp_form_section_' . $template_slug . '_btn_order-place_btn_text' );
		if ( isset( $orderText ) && $orderText != '' ) {
			foreach ( $gateways as $gateway_id => $gateway ) {
				if ( in_array( $gateway_id, apply_filters( 'wfacp_allowed_gateway_order_button_text_change', [ 'authorize_net_cim_credit_card', 'ppec_paypal' ], $this ) ) ) {
					$gateways[ $gateway_id ]->order_button_text = $orderText;
				}
			}
		}

		return $gateways;
	}

	/**
	 * Change cancel url for dedicated only
	 *
	 * @param $url
	 *
	 * @return false|string
	 */
	public function change_cancel_url( $url ) {
		if ( WFACP_Core()->public->is_checkout_override() ) {
			return $url;
		}
		if ( ! WFACP_Core()->public->is_checkout_override() ) {
			$url = get_the_permalink( WFACP_Common::get_id() );
		}

		return $url;
	}

	public function have_coupon_field() {
		return $this->have_coupon_field;
	}

	public function have_shipping_method() {
		return $this->have_shipping_method;
	}

	public function get_wc_addr2_company_value() {

		$woocommerce_checkout_address_2_field = get_option( 'woocommerce_checkout_address_2_field', 'optional' );
		$woocommerce_checkout_company_field   = get_option( 'woocommerce_checkout_company_field', 'optional' );

		$get_wc_addr2_company = [
			'shipping_address_2_field' => 'wfacp_required_optional',
			'billing_address_2_field'  => 'wfacp_required_optional',
			'shipping_company_field'   => 'wfacp_required_optional',
			'billing_company_field'    => 'wfacp_required_optional',
		];

		if ( 'required' === $woocommerce_checkout_address_2_field ) {
			$get_wc_addr2_company['shipping_address_2_field'] = 'wfacp_required_active';
			$get_wc_addr2_company['billing_address_2_field']  = 'wfacp_required_active';
		}

		if ( 'required' === $woocommerce_checkout_company_field ) {
			$get_wc_addr2_company['shipping_company_field'] = 'wfacp_required_active';
			$get_wc_addr2_company['billing_company_field']  = 'wfacp_required_active';
		}

		return $get_wc_addr2_company;

	}

	public function order_btn_sticky( $panel_details, $panel_key ) {
		$selected_template_slug = $this->template_slug;
		if ( $panel_key == 'wfacp_form' ) {

			$pageID         = WFACP_Common::get_id();
			$_wfacp_version = WFACP_Common::get_post_meta_data( $pageID, '_wfacp_version' );

			if ( isset( $_wfacp_version ) && ! empty( $_wfacp_version ) ) {
				if ( isset( $panel_details['sections']['section']['fields'][ $selected_template_slug . '_btn_order-place_make_button_sticky_on_mobile' ] ) ) {
					$panel_details['sections']['section']['fields'][ $selected_template_slug . '_btn_order-place_make_button_sticky_on_mobile' ]['default'] = 'no_sticky';

				}
			}
		}

		return $panel_details;
	}

	public function display_next_button( $step, $current_step, $formData ) {
		if ( 'single_step' != $current_step && ( is_array( $formData ) && count( $formData ) ) ) {
			$this->get_next_button( $step, $formData );
		}

	}

	public function check_cart_coupons( $fragments ) {
		if ( ! is_null( WC()->cart ) ) {
			WC()->cart->check_cart_coupons();
		}

		return $fragments;
	}

	public function remove_extra_payment_gateways_in_customizer( $gateways ) {

		if ( WFACP_Common::is_customizer() ) {
			$gateways     = [];
			$payments     = WC_Payment_Gateways::instance();
			$all_gateways = $payments->payment_gateways();
			if ( isset( $all_gateways['cod'] ) ) {
				$gateways['cod']              = $all_gateways['cod'];
				$gateways['cod']->title       = __( 'Payment Gateway', 'woofunnels-aero-checkout' );
				$gateways['cod']->description = __( 'Enabled payment methods will display on the frontend.', 'woofunnels-aero-checkout' );

			}
		}

		return $gateways;
	}


	/**
	 * Create account if subscription product present in cart but create account not allowed from settings
	 * then we forcefully create a account
	 *
	 */
	public function woocommerce_checkout_process() {
		if ( isset( $_POST['wfacp_cart_contains_subscription'] ) && '1' == $_POST['wfacp_cart_contains_subscription'] ) {


			if ( 'yes' !== get_option( 'woocommerce_enable_signup_and_login_from_checkout' ) ) {
				$_POST['createaccount'] = true;
				if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) {
					$_POST['account_username'] = $_POST['billing_email'];
				}
				if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) {
					$_POST['account_password'] = wp_generate_password();
				}
			}
		}
	}

	public function get_class_from_body() {
		//$body_class = get_body_class();


		$wfacp_body_class = [
			'wfacp_main_wrapper',
			'wfacp-' . $this->device_type,
			'wfacp_cls_' . $this->template_slug,
			'single_step',
			'woocommerce-checkout'
		];

		if ( isset( $this->customizer_fields_data['wfacp_form']['form_data']['btn_details']['make_button_sticky_on_mobile'] ) ) {
			$wfacp_body_class[] = $this->customizer_fields_data['wfacp_form']['form_data']['btn_details']['make_button_sticky_on_mobile'];
		}

		$wfacp_body_class = apply_filters( 'wfacp_body_class', $wfacp_body_class );
		$body_cls_str     = '';
		if ( ! empty( $wfacp_body_class ) ) {

			$wfacp_body_class = array_unique( $wfacp_body_class );
			$body_cls_str     = implode( ' ', $wfacp_body_class );
		}

		return $body_cls_str;

	}

	public function change_setting_for_default_checkout( $field, $key ) {

		$selected_template_slug = $this->get_template_slug();
		$selected_template_type = $this->get_template_type();
		$num_of_steps           = $this->get_step_count();


		if ( $key == 'wfacp_form' && $selected_template_type != 'embed_form' && $num_of_steps > 1 ) {

			$field['sections']['section']['fields'][ $selected_template_slug . '_enable_cart_in_breadcrumb' ] = [
				'type'        => 'checkbox',
				'label'       => __( 'Add cart link to progress bar', 'woofunnels-aero-checkout' ),
				'description' => 'This Setting works for global checkout',
				'priority'    => 9,
				'default'     => true,
			];
			$field['sections']['section']['fields']['cart_text']                                              = [
				'type'            => 'text',
				'label'           => __( 'Cart Title', 'woofunnels-aero-checkout' ),
				'priority'        => 9,
				'default'         => __( 'Cart', 'woofunnels-aero-checkout' ),
				'transport'       => 'postMessage',
				'active_callback' => [
					[
						'setting'  => 'wfacp_form_section_' . $selected_template_slug . '_enable_cart_in_breadcrumb',
						'operator' => '=',
						'value'    => true,
					],
				],
				'wfacp_transport' => [
					[
						'type'                => 'html',
						'container_inclusive' => false,
						'elem'                => '.wfacp_steps_sec .df_cart_link a',
					],
				],
			];
		}


		return $field;
	}

	function add_custom_cls( $body_class ) {

		$wfacp_id            = $this->wfacp_id;
		$is_default_checkout = get_post_meta( $wfacp_id, 'is_default_checkout', true );

		if ( ( isset( $is_default_checkout ) && $is_default_checkout == 1 ) ) {


			$body_class[] = 'wfacp_default_created';
		}


		return $body_class;

	}

	public function call_before_cart_link( $breadcrumb ) {

		$is_global_checkout = WFACP_Core()->public->is_checkout_override();
		$before_link        = $this->customizer_fields_data['wfacp_form']['form_data']['breadcrumb_before'];
		if ( ! is_array( $before_link ) || count( $before_link ) == 0 ) {
			return;
		}


		if ( ( WFACP_Common::is_customizer() || $is_global_checkout === true ) ) {
			if ( isset( $before_link['enable_cart'] ) && $before_link['enable_cart'] == 1 ) {
				$cartURL  = wc_get_cart_url();
				$cartName = __( 'Cart', 'woocommerce' );
				if ( isset( $before_link['enable_cart_text'] ) && '' != $before_link['enable_cart_text'] ) {
					$cartName = $before_link['enable_cart_text'];
				}
				echo "<li class='df_cart_link wfacp_bred_visited'><a href='$cartURL'>$cartName</a></li>";
			}
		}


	}

	public function get_wfacp_version() {
		$pageID         = WFACP_Common::get_id();
		$_wfacp_version = WFACP_Common::get_post_meta_data( $pageID, '_wfacp_version' );
		if ( $_wfacp_version == WFACP_VERSION ) {
			$this->setting_new_version = true;

			return true;
		}

		return false;

	}

	public function add_styling_class_to_country_field( $field, $key ) {
		if ( in_array( $key, [ 'billing_country', 'shipping_country' ] ) ) {
			$billing_allowed_countries  = WC()->countries->get_allowed_countries();
			$shipping_allowed_countries = WC()->countries->get_shipping_countries();
			if ( count( $billing_allowed_countries ) == 1 || count( $shipping_allowed_countries ) == 1 ) {
				$field['class'][] = 'wfacp_allowed_countries';
				$field['class'][] = 'wfacp-anim-wrap';
			}
		}

		return $field;
	}

	public function wc_cart_totals_coupon_label( $coupon, $echo = true ) {
		if ( is_string( $coupon ) ) {
			$coupon = new WC_Coupon( $coupon );
		}


		$svg = '<svg id="668a2151-f22c-4f0f-8525-beec391fcabb" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 415.33">
<title>Untitled-2</title>
<path d="M222.67,0H270L47,223,213.67,389.67l-25,25L0,226Z" transform="translate(0 0)" style="fill:#999"/>
<path d="M318,0S94,222,95.33,222L288.67,415.33,512,192V0Zm97.67,133.33a41,41,0,1,1,41-41A41,41,0,0,1,415.67,133.33Z" transform="translate(0 0)" style="fill:#999"/>
</svg>';

		$couponText = __( 'Coupon', 'woocommerce' );
		$label      = apply_filters( 'woocommerce_cart_totals_coupon_label', sprintf( esc_html__( $couponText . ' %s %s', 'woocommerce' ), $svg, "<span class='wfacp_coupon_code'>" . $coupon->get_code() . "</span>" ), $coupon );
		if ( $echo ) {
			echo $label;
		} else {
			return $label;
		}
	}

	public function merge_default_page_settings( $settings ) {

		if ( ! isset( $settings['show_on_next_step'] ) ) {
			$settings['show_on_next_step'] = [];
		}
		if ( ! isset( $settings['show_on_next_step']['single_step'] ) ) {
			$settings['show_on_next_step']['single_step'] = new stdClass();
		}
		if ( ! isset( $settings['show_on_next_step']['two_step'] ) ) {
			$settings['show_on_next_step']['two_step'] = new stdClass();
		}
		if ( ! isset( $settings['show_on_next_step']['third_step'] ) ) {
			$settings['show_on_next_step']['third_step'] = new stdClass();
		}

		return $settings;
	}


	/**
	 * to avoid unserialize of the current class
	 */
	public function __wakeup() {
		throw new ErrorException( 'WFACP_Core classes can`t converted to string' );
	}

	/**
	 * to avoid serialize of the current class
	 */
	public function __sleep() {
		throw new ErrorException( 'WFACP_Core classes can`t converted to string' );
	}

	/**
	 * To avoid cloning of current template class
	 */
	protected function __clone() {
	}


}
