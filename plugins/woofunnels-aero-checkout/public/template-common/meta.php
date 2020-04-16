<?php
$id = WFACP_Common::get_id();
do_action( 'wfacp_meta_added', $id );
?>
<meta name="viewport" content="width=device-width, initial-scale=1.0,minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="wfacp_aero_checkout_id" id="wfacp_aero_checkout_id" content="<?php echo $id; ?>">
