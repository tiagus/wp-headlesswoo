<?php
defined( 'ABSPATH' ) || exit;
$checkout = WC()->checkout();

/**
 * @var $this WFACP_template_layout9
 */

$page_meta_title        = WFACP_Common::get_option( 'wfacp_header_section_page_meta_title' );
$selected_template_slug = $this->get_template_slug();
$header_layout_is       = $this->get_temaplete_header_layout();


$numOfSteps = $this->get_step_count();

$fullWidthCls = 'full_width_bar';
if ( $numOfSteps > 1 ) {
	$fullWidthCls = 'multistep_bar';
}


$wfacp_shopcheckout_sidebar = 'wfacp_shopcheckout_sidebar_no';
if ( is_array( $this->active_sidebar() ) && count( $this->active_sidebar() ) > 0 ) {
	$wfacp_shopcheckout_sidebar = 'wfacp_shopcheckout_sidebar_yes';
}
?>
<html lang="<?php echo get_locale() ?>">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $page_meta_title ? $page_meta_title : get_bloginfo( 'name' ); ?></title>
	<?php wp_head(); ?>
	<?php
	do_action( 'wfacp_header_print_in_head' );
	?>
</head>
<body class="<?php echo $this->get_class_from_body() ?> <?php echo $wfacp_shopcheckout_sidebar ?>">
<!--main panel wrapper open -->
<div class="wrapper wfacp-main-container">
    <div class="wfacp-wrapper-decoration <?php echo $fullWidthCls; ?>">


        <!--header section wrapper -->
        <!--Breadcrumb-->
		<?php

		if ( isset( $header_layout_is ) && $header_layout_is != 'outside_header' ) {
			include( $this->wfacp_get_header() );
			$this->custom_add_form_steps();
			/*add_outside_header*/
		}

		?>

        <!--header section wrapper close -->


        <!--Breadcrumb close-->


        <!-- contener wrapper open -->
        <div class="wfacp-panel-wrapper <?php echo 'wfacp_' . $header_layout_is; ?> ">

            <div class="wfacp-middle-container">
                <div class="wfacp-form-panel clearfix">


                    <div class="wfacp-comm-wrapper wfacp-clearfix">
						<?php

						if ( $this->device_type != 'mobile' ) {

							$footer = $this->customizer_fields_data[ $this->customizer_keys['footer'] ];

							if ( ( is_array( $footer ) && count( $footer ) <= 0 ) || is_null( $footer ) ) {
								return;
							}

							?>
                            <!--left wrapper -->
                            <div class="wfacp-left-wrapper">
                                <div class="wfacp-form">
									<?php
									include( $this->wfacp_get_form() );


									if ( ( is_array( $this->excluded_other_widget() ) && count( $this->excluded_other_widget() ) > 0 ) && $this->device_type != 'mobile' ) {

										foreach ( $this->excluded_other_widget() as $key => $value ) {
											$data        = array();
											$section_key = $value;

											if ( isset( $this->customizer_fields_data[ $section_key ] ) ) {
												$data = $this->customizer_fields_data[ $section_key ];
											}


											switch ( $value ) {
												case strpos( $section_key, 'wfacp_promises_' ):
													$promise_data[ $section_key ] = $data;
													$this->get_module( $data, false, 'promises', $section_key );
													break;
												case strpos( $section_key, 'wfacp_customer_' ):
													$this->get_module( $data, false, 'customer-support', $section_key );
													break;
												case strpos( $section_key, 'wfacp_html_widget_' ):

													$this->get_module( $data, false, 'wfacp_html_widget', $section_key );
													break;
											}
										}
									}
									?>


                                    <!-- testimonial panel close -->
                                </div>
								<?php
								if ( isset( $header_layout_is ) && $header_layout_is == 'outside_header' ) {

									?>
                                    <div class="wfacp_inner_footer_m wfacp-footer wfacp_footer ">
                                        <div class="wfacp_footer_sec clearfix">
											<?php
											if ( isset( $footer['footer_data']['ft_ct_content'] ) && $footer['footer_data']['ft_ct_content'] != '' ) {
												?>
                                                <div class=" wfacp_footer_n">
                                                    <div class=" wfacp_footer_wrap_n">
                                                        <div class="wfacp-footer-text">
															<?php echo apply_filters( 'wfacp_the_content', $footer['footer_data']['ft_ct_content'] ); ?>
                                                        </div>
                                                    </div>

                                                </div>
												<?php
											}
											?>
                                        </div>
                                    </div>
									<?php
								}
								?>


                            </div>

                            <!--left wrapper close-->

                            <!-- right wrapper -->


							<?php include( $this->wfacp_get_sidebar() ); ?>
							<?php
						} else {
							$mobile_layout_order = $this->mobile_layout_order();

							if ( isset( $header_layout_is ) && $header_layout_is == 'outside_header' ) {
								echo "<header class='mb_header_section'>";
								$this->add_outside_header();
								echo '</header>';
							}
							$cart_collapse_title = '';
							$cart_expanded_title = '';

							$no_cart_text = 'no_text_available';


							if ( isset( $this->customizer_fields_data['wfacp_form']['form_data']['cart_collapse_title'] ) && $this->customizer_fields_data['wfacp_form']['form_data']['cart_collapse_title'] != '' ) {
								$cart_collapse_title = $this->customizer_fields_data['wfacp_form']['form_data']['cart_collapse_title'];
								$no_cart_text        = '';
							}

							if ( isset( $this->customizer_fields_data['wfacp_form']['form_data']['cart_expanded_title'] ) && $this->customizer_fields_data['wfacp_form']['form_data']['cart_expanded_title'] != '' ) {
								$cart_expanded_title = $this->customizer_fields_data['wfacp_form']['form_data']['cart_expanded_title'];
							}


							?>
                            <div class="wfacp_mb_mini_cart_wrap ">
                                <div class="wfacp_mb_cart_accordian clearfix" attr-collaps="<?php echo $cart_collapse_title; ?>" attr-expend="<?php echo $cart_expanded_title; ?>">
                                    <div class="wfacp_show_icon_wrap <?php echo $no_cart_text; ?>">
                                        <a href="#">
                                            <span><?php echo $cart_collapse_title; ?></span>
                                            <img src="<?php echo $this->get_url() . 'images/down-arrow.svg'; ?>" alt="">
                                        </a>
                                    </div>
                                    <div class="wfacp_show_price_wrap">
                                        <div class="wfacp_cart_mb_fragment_price">
                                            <span><?php echo wc_price( WC()->cart->total ); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wfacp_mb_mini_cart_sec_accordion_content wfacp_display_none">
									<?php do_action( 'wfacp_before_sidebar_content' ); ?>
                                </div>

                            </div>


							<?php

							echo '<div class="wfacp_layout_content_wrapper">';
							foreach ( $mobile_layout_order as $key => $value ) {
								$section_key = $value;

								if ( isset( $this->customizer_fields_data[ $section_key ] ) ) {
									$data = $this->customizer_fields_data[ $section_key ];
								}


								switch ( $value ) {
									case 'wfacp_product':
										include( $this->wfacp_get_product() );
										break;
									case 'wfacp_form':
										printf( '<div class="wfacp-form clearfix">' );

										echo '   <div class="wfacp-left-wrapper clearfix">';
										include( $this->wfacp_get_form() );
										echo '</div>';
										echo '</div>';
										break;
									case strpos( $section_key, 'wfacp_benefits_' ):
										$this->get_module( $data, false, 'benefits', $section_key );
										break;
									case strpos( $section_key, 'wfacp_testimonials_' ):
										$this->get_module( $data, false, 'testimonials', $section_key );
										break;
									case strpos( $section_key, 'wfacp_assurance_' ):
										$this->get_module( $data, false, 'assurance', $section_key );
										break;
									case strpos( $section_key, 'wfacp_promises_' ):
										$this->get_module( $data, false, 'promises', $section_key );
										break;
									case strpos( $section_key, 'wfacp_customer_' ):
										$this->get_module( $data, false, 'customer-support', $section_key );
										break;
									case strpos( $section_key, 'wfacp_html_widget_' ):
										$this->get_module( $data, false, 'wfacp_html_widget', $section_key );
										break;

								}
							}
							echo '</div>';
							?>


							<?php
						}
						?>

                    </div>
                </div>
                <!-- wfacp-form panel close-->
            </div>

        </div>
        <!--content wrappre close -->

		<?php


		if ( ( isset( $header_layout_is ) && $header_layout_is != 'outside_header' ) || $this->device_type == 'mobile' ) {
			include( $this->wfacp_get_footer() );
		}
		?>
        <!--footer wrapper -->


        <!--footer wrapper close -->
    </div>
</div>
<!--main panel wrapper close -->

<?php
do_action( 'wfacp_footer_before_print_scripts' );
//WFACP_Core()->assets->print_scripts();
echo '<div class=wfacp_footer_sec_for_script>';
wp_footer();
echo '</div>';
do_action( 'wfacp_footer_after_print_scripts' );
?>
<style data-type='wfacp'></style>
</body>
</html>
