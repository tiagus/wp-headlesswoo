<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WOE_Export_Email extends WOE_Export {

	private static $from = '';
	private static $from_name = '';

	public function run_export( $filename, $filepath ) {
		//must rename tmp file
		$newfilepath = dirname( $filepath ) . "/" . $filename;
		//die($newfilepath);
		if ( !@copy( $filepath, $newfilepath ) ) {
			return __( "Can't rename temporary file", 'woocommerce-order-export' );
		}

		$to		 = preg_split ("#,|\r?\n#", $this->destination[ 'email_recipients' ] , NULL, PREG_SPLIT_NO_EMPTY );
		$subject = apply_filters("woe_export_email_subject", WC_Order_Export_Engine::make_filename($this->destination[ 'email_subject' ]) );
		
		@$message = WC_Order_Export_Engine::make_filename($this->destination[ 'email_body' ]);
		// should use json/xml as body
		if( !empty($this->destination[ 'email_body_append_file_contents' ]) ) {
			$message .= file_get_contents($filepath);
		}
		if( empty($message) )
			$message = __( "Please, review the attachment", 'woocommerce-order-export' );
		
		$headers = array();
		if ( $message != strip_tags($message) )
			$headers[] = "Content-Type: text/html";
		else
			$headers[] = "Content-Type: text/plain";

		//From config
		self::$from_name = $this->destination[ 'email_from_name' ];
		add_action( 'wp_mail_from_name', function() { return self::$from_name; } );
		
		self::$from      = $this->destination[ 'email_from' ];
		if( self::$from )
			add_action( 'wp_mail_from', function() { return self::$from; } );
		
		// have to add CC?
		if( !empty($this->destination[ 'email_recipients_cc' ]) ) {
			$cc_emails = preg_split ("#,|\r?\n#", $this->destination[ 'email_recipients_cc' ] , NULL, PREG_SPLIT_NO_EMPTY );
			foreach( $cc_emails  as $cc_email )
				$headers[] = "Cc: " . $cc_email;
		}
		
		// have to add BCC?
		if( !empty($this->destination[ 'email_recipients_bcc' ]) ) {
			$bcc_emails = preg_split ("#,|\r?\n#", $this->destination[ 'email_recipients_bcc' ] , NULL, PREG_SPLIT_NO_EMPTY );
			foreach( $bcc_emails  as $bcc_email )
				$headers[] = "Bcc: " . $bcc_email;
		}

		$attachments = apply_filters("woe_export_email_attachments", array( $newfilepath ) );

		try {
			$result = wp_mail( $to, $subject, $message, $headers, $attachments );
		} catch (Exception $e) {
			//$e->getMessage();
			$result = false;
		}		
		
		//delete renamed copy 
		unlink($newfilepath);
		
		if ( !$result ) {
			global $ts_mail_errors;
			global $phpmailer;
			if ( !isset( $ts_mail_errors ) ) {
				$ts_mail_errors = array();
			}
			if ( isset( $phpmailer ) ) {
				$ts_mail_errors[] = $phpmailer->ErrorInfo;
			}
		}
		if ( empty( $ts_mail_errors ) ) {
			$return = sprintf( __( "File '%s' has sent to '%s'", 'woocommerce-order-export' ), $filename, join( ",", $to ) );
		} else {
			$return = implode( ';', $ts_mail_errors );
		}

		return $return;
	}
}
