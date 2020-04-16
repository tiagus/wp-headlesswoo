<?php
defined( 'ABSPATH' ) || exit;

class WFACP_Product_Switcher_Field {

	public static $customizer_key_prefix = 'wfacp_';
	public static $_instance = null;

	/**
	 * @var $template_common  WFACP_Template_Common
	 */
	public $template_common;

	protected function __construct( $template_common = null ) {
		if ( ! is_null( $template_common ) ) {
			$this->template_common = $template_common;
		}
	}

	public static function get_instance( $template_common ) {
		if ( self::$_instance == null ) {
			self::$_instance = new self( $template_common );
		}

		return self::$_instance;
	}

	public function get_settings() {

		$selected_template_slug = $this->template_common->get_template_slug();
		$fields                 = $this->template_common->get_checkout_fields();

		/** PANEL: Form Setting */
		$form_cart_panel = array();
		if ( ! is_array( $fields ) || count( $fields ) == 0 ) {
			return $form_cart_panel;
		}

		$form_cart_panel['wfacp_form_product_switcher']['sections']['section']['fields'][]                                                 = [];
		$form_cart_panel['wfacp_form_product_switcher']                                                                                    = array(
			'panel'    => 'no',
			'data'     => array(
				'priority'    => 45,
				'title'       => __( 'Product List', 'woofunnels-aero-checkout' ),
				'description' => '',

			),
			'sections' => array(
				'section' => array(
					'data'   => array(
						'title'       => __( 'Product List', 'woofunnels-aero-checkout' ),
						'priority'    => 20,
						'description' => 'To manage content of this section,<br> <a href="//buildwoofunnels.com/docs/aerocheckout/forms/manage-product-list/?origin_team=T03EW76TW" target="_blank">follow this documentation</a> ',
					),
					'fields' => [],
				),
			),
		);
		$form_cart_panel['wfacp_form_product_switcher']['sections']['section']['fields']['cta_advanced_setting']                           = [
			'type'          => 'custom',
			'default'       => '<div class="options-title-divider">' . esc_html__( 'Advanced', 'woofunnels-aero-checkout' ) . '</div>',
			'priority'      => 190,
			'wfacp_partial' => [
				'container_inclusive' => false,
				'elem'                => '.wfacp_whats_included',
			],
		];
		$form_cart_panel['wfacp_form_product_switcher']['sections']['section']['fields'][ $selected_template_slug . '_rbox_border_type' ]  = [
			'type'            => 'select',
			'label'           => esc_attr__( 'Border Type', 'woofunnels-aero-checkout' ),
			'default'         => 'solid',
			'choices'         => array(
				'none'   => 'None',
				'solid'  => 'Solid',
				'double' => 'Double',
				'dotted' => 'Dotted',
				'dashed' => 'Dashed',
			),
			'priority'        => 200,
			'transport'       => 'postMessage',
			'wfacp_transport' => [
				[
					'internal' => true,
					'type'     => 'css',
					'prop'     => [ 'border-style' ],
					'elem'     => 'body .wfacp_main_form .wfacp_whats_included',
				],

			],
		];
		$form_cart_panel['wfacp_form_product_switcher']['sections']['section']['fields'][ $selected_template_slug . '_rbox_border_width' ] = [
			'type'            => 'slider',
			'label'           => esc_attr__( 'Border Width', 'woofunnels-aero-checkout' ),
			'default'         => 1,
			'choices'         => array(
				'min'  => '1',
				'max'  => '12',
				'step' => '1',
			),
			'priority'        => 210,
			'active_callback' => array(
				array(
					'setting'  => 'wfacp_form_product_switcher_section_' . $selected_template_slug . '_rbox_border_type',
					'operator' => '!=',
					'value'    => 'none',
				),
			),
			'transport'       => 'postMessage',
			'wfacp_transport' => [
				[
					'internal' => true,
					'type'     => 'css',
					'prop'     => [ 'border-width' ],
					'elem'     => 'body .wfacp_main_form .wfacp_whats_included',
				],
			],
		];
		$form_cart_panel['wfacp_form_product_switcher']['sections']['section']['fields'][ $selected_template_slug . '_rbox_border_color' ] = [
			'type'            => 'color',
			'label'           => esc_attr__( 'Border Color', 'woofunnels-aero-checkout' ),
			'default'         => '#efefef',
			'choices'         => array(
				'alpha' => true,
			),
			'priority'        => 220,
			'active_callback' => array(
				array(
					'setting'  => 'wfacp_form_product_switcher_section_' . $selected_template_slug . '_rbox_border_type',
					'operator' => '!=',
					'value'    => 'none',
				),
			),
			'transport'       => 'postMessage',
			'wfacp_transport' => [
				[
					'internal' => true,
					'type'     => 'css',
					'prop'     => [ 'border-color' ],
					'elem'     => 'body .wfacp_main_form .wfacp_whats_included',
				],
			],
		];
		$form_cart_panel['wfacp_form_product_switcher']['sections']['section']['fields'][ $selected_template_slug . '_rbox_padding' ]      = [
			'type'            => 'number',
			'label'           => __( 'Padding', 'woofunnels-aero-checkout' ),
			'default'         => 10,
			'priority'        => 220,
			'active_callback' => array(
				array(
					'setting'  => 'wfacp_form_product_switcher_section_' . $selected_template_slug . '_rbox_border_type',
					'operator' => '!=',
					'value'    => 'none',
				),
			),
			'transport'       => 'postMessage',
			'wfacp_transport' => [
				[
					'internal' => true,
					'type'     => 'css',
					'prop'     => [ 'padding' ],
					'elem'     => 'body .wfacp_main_form .wfacp_whats_included',
				],
			],
		];

		$form_cart_panel['wfacp_form_product_switcher']['sections']['section']['fields']['ct_colors']                                              = [
			'type'     => 'custom',
			'default'  => '<div class="options-title-divider">' . esc_html__( 'Colors', 'woofunnels-aero-checkout' ) . '</div>',
			'priority' => 230,
		];
		$form_cart_panel['wfacp_form_product_switcher']['sections']['section']['fields'][ $selected_template_slug . '_sec_bg_color' ]              = [
			'type'            => 'color',
			'label'           => esc_attr__( 'Section Background', 'woofunnels-aero-checkout' ),
			'default'         => '#fafafa',
			'choices'         => [
				'alpha' => true,
			],
			'priority'        => 250,
			'transport'       => 'postMessage',
			'wfacp_transport' => [
				[
					'internal' => true,
					'type'     => 'css',
					'prop'     => [ 'background-color' ],
					'elem'     => 'body .wfacp_main_form .wfacp_whats_included',
				],
			],
		];
		$form_cart_panel['wfacp_form_product_switcher']['sections']['section']['fields'][ $selected_template_slug . '_sec_heading_color' ]         = [
			'type'            => 'color',
			'label'           => esc_attr__( 'Heading', 'woofunnels-aero-checkout' ),
			'default'         => '#333333',
			'choices'         => [
				'alpha' => true,
			],
			'priority'        => 250,
			'transport'       => 'postMessage',
			'wfacp_transport' => [
				[
					'internal' => true,
					'type'     => 'css',
					'prop'     => [ 'color' ],
					'elem'     => 'body .wfacp_main_form .wfacp_whats_included h3',
				],
			],
		];
		$form_cart_panel['wfacp_form_product_switcher']['sections']['section']['fields'][ $selected_template_slug . '_product_title_color' ]       = [
			'type'            => 'color',
			'label'           => esc_attr__( 'Product Title', 'woofunnels-aero-checkout' ),
			'default'         => '#666666',
			'choices'         => [
				'alpha' => true,
			],
			'priority'        => 250,
			'transport'       => 'postMessage',
			'wfacp_transport' => [
				[
					'internal' => true,
					'type'     => 'css',
					'prop'     => [ 'color' ],
					'elem'     => 'body .wfacp_main_form .wfacp_whats_included .wfacp_product_switcher_description h4',
				],

			],
		];
		$form_cart_panel['wfacp_form_product_switcher']['sections']['section']['fields'][ $selected_template_slug . '_product_description_color' ] = [
			'type'            => 'color',
			'label'           => esc_attr__( 'Product Description', 'woofunnels-aero-checkout' ),
			'default'         => '#666666',
			'choices'         => [
				'alpha' => true,
			],
			'priority'        => 250,
			'transport'       => 'postMessage',
			'wfacp_transport' => [
				[
					'internal' => true,
					'type'     => 'css',
					'prop'     => [ 'color' ],
					'elem'     => 'body .wfacp_main_form .wfacp_whats_included .wfacp_description',
				],
				[
					'internal' => true,
					'type'     => 'css',
					'prop'     => [ 'color' ],
					'elem'     => 'body .wfacp_main_form .wfacp_whats_included .wfacp_description p',
				],
				[
					'internal' => true,
					'type'     => 'css',
					'prop'     => [ 'color' ],
					'elem'     => 'body .wfacp_main_form .wfacp_whats_included .wfacp_description li',
				],
				[
					'internal' => true,
					'type'     => 'css',
					'prop'     => [ 'color' ],
					'elem'     => 'body .wfacp_main_form .wfacp_whats_included .wfacp_description li a',
				],

			],
		];

		$section_data_keys = [];

		$section_data_keys['colors'][ $selected_template_slug . '_sec_bg_color' ]      = [
			[
				'type'   => 'background-color',
				'class'  => 'body .wfacp_main_form .wfacp_whats_included',
				'device' => 'desktop',
			],
		];
		$section_data_keys['colors'][ $selected_template_slug . '_sec_heading_color' ] = [
			[
				'type'   => 'color',
				'class'  => 'body .wfacp_main_form .wfacp_whats_included h3',
				'device' => 'desktop',
			],
		];

		$section_data_keys['colors'][ $selected_template_slug . '_product_title_color' ]       = [
			[
				'type'   => 'color',
				'class'  => 'body .wfacp_main_form .wfacp_whats_included .wfacp_product_switcher_description h4',
				'device' => 'desktop',
			],
		];
		$section_data_keys['colors'][ $selected_template_slug . '_product_description_color' ] = [
			[
				'type'   => 'color',
				'class'  => 'body .wfacp_main_form .wfacp_whats_included .wfacp_description',
				'device' => 'desktop',
			],
			[
				'type'   => 'color',
				'class'  => 'body .wfacp_main_form .wfacp_whats_included .wfacp_description p',
				'device' => 'desktop',
			],
			[
				'type'   => 'color',
				'class'  => 'body .wfacp_main_form .wfacp_whats_included .wfacp_description li',
				'device' => 'desktop',
			],
			[
				'type'   => 'color',
				'class'  => 'body .wfacp_main_form .wfacp_whats_included .wfacp_description li a',
				'device' => 'desktop',
			],
		];

		$this->template_common->set_section_keys_data( 'wfacp_form_product_switcher', $section_data_keys );
		$form_cart_panel                                = apply_filters( 'wfacp_checkout_product_switcher', $form_cart_panel, $this );
		$form_cart_panel['wfacp_form_product_switcher'] = apply_filters( 'wfacp_layout_default_setting', $form_cart_panel['wfacp_form_product_switcher'], 'wfacp_form_product_switcher' );

		return $form_cart_panel;
	}
}
