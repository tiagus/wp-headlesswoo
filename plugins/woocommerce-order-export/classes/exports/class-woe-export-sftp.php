<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

//init composer
include_once WOE_PLUGIN_BASEPATH .'/vendor/autoload.php';
use phpseclib\Net\SFTP;

class WOE_Export_Sftp extends WOE_Export {
	var $timeout = 15; //in seconds 
	var $user_errors;

	public function run_export( $filename, $filepath ) {
		//use default port?
		if ( empty( $this->destination['sftp_port'] ) ) {
			$this->destination['sftp_port'] = 22;
		}
		
		//adjust path final /
		if ( substr( $this->destination['sftp_path'], -1 ) != '/' ) {
			$this->destination['sftp_path'] .= '/' ;
		}
		
		$sftp = new SFTP( $this->destination['sftp_server'], $this->destination['sftp_port'], $this->timeout );
		
		$this->user_errors = array();
		$prev_error_handler =  set_error_handler ( array($this, 'record_user_errors'), E_USER_NOTICE);
		
		do{ 
			//1
			if ( !$sftp->login( $this->destination['sftp_user'], $this->destination['sftp_pass']) ) {
				$message = sprintf( __( "Can't login to SFTP as user '%s'. SFTP errors: %s", 'woocommerce-order-export' ),
					$this->destination['sftp_user'], join("\n", $this->get_errors($sftp)  ) );
				break;	
			}
			//2 
			if ( !$sftp->put( $this->destination['sftp_path'].$filename, $filepath, SFTP::SOURCE_LOCAL_FILE ) ) {
				$message = sprintf( __( "Can't upload file '%s'. SFTP errors: %s", 'woocommerce-order-export' ), $filename, join("\n", $this->get_errors($sftp) ) );
				break;
			}
			//done 
			$message = sprintf( __( "We have uploaded file '%s' to '%s'", 'woocommerce-order-export' ), $filename, $this->destination['sftp_server'] . $this->destination['sftp_path'] );
		} while(0);	
			
		set_error_handler (	$prev_error_handler , E_USER_NOTICE);
		return $message;
	}
	
	public function record_user_errors($errno, $errstr, $errfile, $errline) {
		$this->user_errors[] = $errstr;
	}
	
	public function get_errors($sftp) {
		return $this->user_errors + $sftp->getSFTPErrors();
	}
}