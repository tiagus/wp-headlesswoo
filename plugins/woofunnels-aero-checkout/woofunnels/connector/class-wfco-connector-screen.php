<?php

class WFCO_Connector_Screen {

	private $slug = '';
	private $image = '';
	private $name = '';
	private $is_active = false;
	private $activation_url = '';
	private $file = '';
	private $connector_class = '';
	private $source = '';
	private $support = [];
	private $type = 'Basic';

	public function __construct( $slug, $data ) {
		$this->slug = $slug;

		if ( is_array( $data ) && count( $data ) > 0 ) {
			foreach ( $data as $property => $val ) {

				if ( is_array( $val ) ) {
					$this->{$property} = $val;
					continue;
				}
				$this->{$property} = trim( $val );
			}
		}

	}

	public function get_logo() {
		return $this->image;
	}

	public function get_name() {
		$this->name;
	}

	public function is_active() {


		$this->is_active;
	}


	public function is_installed() {

	}

	public function activation_url() {

		$this->activation_url;
	}

	public function get_path() {

		$this->file;
	}

	public function get_class() {

		return $this->connector_class;
	}

	public function get_source() {
		return $this->source;
	}

	public function get_support() {

		return $this->support;
	}

	public function get_type() {
		return $this->type;
	}

	public function get_slug() {

		return $this->slug;
	}

	public function is_activated() {
		if ( class_exists( $this->connector_class ) ) {

			return true;
		}

		return false;
	}

	public function is_present() {
		$plugins = get_plugins();
		$file    = trim( $this->file );
		if ( '' !== $this->file && isset( $plugins[ $file ] ) ) {
			return true;

		}

		return false;
	}


	private function button() {
		$edit_nonce    = wp_create_nonce( 'wfco-connector-edit' );
		$install_nonce = wp_create_nonce( 'wfco-connector-install' );
		$delete_nonce  = wp_create_nonce( 'wfco-connector-delete' );
		$sync_nonce    = wp_create_nonce( 'wfco-connector-sync' );
		/**
		 * @var $connector BWF_CO
		 */
		// Plugin activated
		if ( $this->is_activated() ) {
			$source_slug = $this->slug;
			$connector   = WFCO_Load_Connectors::get_connector( $source_slug );
			//connector data present or not
			if ( isset( WFCO_Common::$connectors_saved_data[ $source_slug ] ) ) {
				$id          = WFCO_Common::$connectors_saved_data[ $source_slug ]['id'];
				$modal_title = __( 'Connect with ', 'woofunnels' ) . $this->name;
				/** Settings and Installed button */
				if ( true === $connector->has_settings() ) {
					?>
                    <a href="javascript:void(0)" data-nonce="<?php echo $edit_nonce; ?>" data-id="<?php echo $id; ?>" data-slug="<?php echo $source_slug; ?>" class="wfco_save_btn_style wfco-connector-edit" data-izimodal-open="#modal-edit-connector" data-iziModal-title="<?php echo $modal_title; ?>" data-izimodal-transitionin="fadeInDown"><?php echo __( 'Settings', 'woofunnels' ); ?> </a>
					<?php
				} else {
					?>
                    <a href="javascript:void(0)" data-id="<?php echo $id; ?>" data-slug="<?php echo $source_slug; ?>" class="wfco_save_btn_style wfco-connector-installed"><?php echo __( 'Installed', 'woofunnels' ); ?> </a>
					<?php
				}

				/** Sync button */
				if ( $connector->is_syncable() ) {
					?>
                    <a href="javascript:void(0)" data-nonce="<?php echo $sync_nonce; ?>" data-id="<?php echo $id; ?>" data-slug="<?php echo $source_slug; ?>" class="wfco_save_btn_style wfco-connector-sync"><?php echo __( 'Sync', 'woofunnels' ); ?> </a>
					<?php
				}

				/** Disconnect button */
				if ( true === $connector->has_settings() ) {
					?>
                    <a href="javascript:void(0)" data-nonce="<?php echo $delete_nonce; ?>" data-id="<?php echo $id; ?>" data-slug="<?php echo $source_slug; ?>" class="wfco_save_btn_style wfco-connector-delete"><?php echo __( 'Disconnect', 'woofunnels' ); ?> </a>
					<?php
				}

			} else {
				// api data not set for current connector;
				$connector_type = 'indirect';
				if ( true == $connector->has_settings() ) {
					$connector_type = 'direct';
					?>
                    <a href="javascript:void(0)" data-type="<?php echo $connector_type; ?>" data-slug="<?php echo $source_slug; ?>" class="wfco_save_btn_style wfco-connector-connect wfco_save_btn_style"><?php echo __( 'Connect', 'woofunnels' ); ?> </a>
					<?php
				} else {
					$modal_title = __( 'Connect with ', 'woofunnels' ) . $this->name;
					?>
                    <a href="javascript:void(0)" data-type="<?php echo $connector_type; ?>" data-slug="<?php echo $source_slug; ?>" class="wfco_save_btn_style wfco-connector-connect" data-izimodal-open="#wfco-modal-connect" data-iziModal-title="<?php echo $modal_title; ?>" data-izimodal-transitionin="fadeInDown"><?php echo __( 'Connect', 'woofunnels' ); ?> </a>
					<?php
				}
			}
		} elseif ( $this->is_present() ) {
			$activate_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . urlencode( $this->file ) . '&amp;plugin_status=all&amp;paged=1', 'activate-plugin_' . $this->file );
			?>
            <a href="<?php echo $activate_url ?>" data-id="" data-slug="<?php echo $this->slug ?>" class="wfco_save_btn_style wfco-connector-installed" target="_blank"><?php _e( 'Activate', 'woofunnels' ) ?></a>
			<?php

		} else {
			?>
            <a href="javascript:void(0)" data-nonce="<?php echo $install_nonce; ?>" data-connector="<?php echo $this->slug; ?>" class="wfco_save_btn_style wfco_connector_install" data-load-text="<?php echo __( 'Installing..', 'woofunnels' ); ?>" data-text="<?php echo __( 'Install', 'woofunnels' ); ?>" data-connector-slug="WFAB_ENCODE"><?php echo __( 'Install', 'woofunnels' ); ?> </a>
			<?php
		}

	}


	public function print_card() {
		?>
        <div class="wfco-col-md-4">
            <div class="wfco-connector-wrap">
                <div class="wfco-ribbon wfco-ribbon-top-right" data-type="<?php echo strtolower( $this->type ); ?>">
                    <span><?php echo $this->type; ?></span>
                </div>
                <div class="wfco-connector-img">
                    <img src="<?php echo $this->image; ?>"/>
                </div>
                <div class="clear"></div>
                <div class="wfco-connector-action">
                    <div class="wfco-connector-btns wfco_body">
						<?php
						$this->button();
						?>
                    </div>
                </div>
            </div>
        </div>
		<?php
	}
}
