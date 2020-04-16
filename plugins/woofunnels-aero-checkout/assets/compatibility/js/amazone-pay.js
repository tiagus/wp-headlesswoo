'use strict';

(function ($) {
    $(document).ready(function () {
        if ($('.wc-amazon-payments-advanced-populated .create-account #billing_email_field').length > 0) {
            $('.wc-amazon-payments-advanced-populated .create-account #billing_email_field').addClass("wfacp-form-control-wrapper");
        }
    });
})(jQuery);
