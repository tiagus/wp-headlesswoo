<?php
$checkout = WC()->checkout();


/**
 * @var $this WFACP_template_layout1
 */
$page_meta_title        = WFACP_Common::get_option( 'wfacp_header_section_page_meta_title' );
$selected_template_slug = $this->get_template_slug();
$mobile_layout_order    = $this->mobile_layout_order();
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
    <style>
        fieldset {
            border: 1px solid #000;
            padding: 5px;
            margin-top: 10px;
        }

        fieldset legend {
            display: inline;
            /*max-width: 150px;*/
        }

        .wfacp_page.two_step {
            display: none;
        }

        button.button.button-primary {
            width: 120px;
            padding: 10px;
            margin-top: 14px;
            background: #3665A6;
            color: #Fff;
            font-weight: 600;
            margin-bottom: 15px;
        }

        label.wfacp-input-animated {
            display: none;
        }
    </style>
</head>
<body class="<?php echo $this->get_class_from_body() ?>">
<!--main panel wrapper open -->
<div class="wrapper wfacp-main-container">
    <div class="wfacp-wrapper-decoration">
        <!--header section wrapper -->
		<?php include( $this->wfacp_get_header() ); ?>
        <!--header section wrapper close -->

		<?php
		if ( is_array( $mobile_layout_order ) && count( $mobile_layout_order ) > 0 ) {
			?>

            <!-- contener wrapper open -->
            <div class="wfacp-panel-wrapper clearfix <?php echo $selected_template_slug . '_temp'; ?>">
                <div class="wfacp-container wfacp-contenter-inner-wrapper clearfix">
                    <div class="wfacp-form-panel clearfix">

                        <div class="wfacp-comm-wrapper clearfix <?php echo $selected_template_slug; ?>_temp">
                            <div class="wfacp-right-wrapper">
								<?php
								foreach ( $mobile_layout_order as $key => $value ) {
									$section_key = $value;

									if ( isset( $this->customizer_fields_data[ $section_key ] ) ) {
										$data = $this->customizer_fields_data[ $section_key ];
										switch ( $value ) {
											case 'wfacp_product':
												include( $this->wfacp_get_product() );
												break;
											case 'wfacp_form':
												echo '<div class=wfacp-form clearfix>';
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
								}
								?>
                            </div>
                        </div>

                    </div>
                    <!-- wfacp-form panel close-->
                </div>
            </div>
            <!--content wrappre close -->
			<?php
		}
		?>

        <!--footer wrapper -->
		<?php include( $this->wfacp_get_footer() ); ?>
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
