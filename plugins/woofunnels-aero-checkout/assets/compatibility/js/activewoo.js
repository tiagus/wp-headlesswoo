"use strict";

(function ($) {

    var activeData = {};

    function ValidateEmail(mail) {
        if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(mail)) {
            return true;
        }
        return false;
    }

    function send_to_active_campaing(email) {

        var first_name = $("#billing_first_name");
        var last_name = $("#billing_last_name");
        var s_first_name = $("#shipping_first_name");
        var s_last_name = $("#shipping_last_name");
        var phone = $("#billing_phone");
        var data = {
            'action': 'aw_rc',
            'fname': '',
            'lname': '',
            'email': email,
            'phone': '',
            '_wpnonce': aw_rc.nonce
        };

        if (first_name.length > 0 && first_name.is(":visible") && "" != first_name.val()) {
            data.fname = first_name.val();
        } else {
            if (s_first_name.length > 0 && s_first_name.is(":visible") && "" != s_first_name.val()) {
                data.fname = s_first_name.val();
            }
        }

        if (last_name.length > 0 && last_name.is(":visible") && "" != last_name.val()) {
            data.lname = last_name.val();
        } else {
            if (s_last_name.length > 0 && s_last_name.is(":visible") && "" != s_last_name.val()) {
                data.lname = s_last_name.val();
            }
        }

        if (phone.length > 0 && phone.is(":visible") && "" != phone.val()) {
            data.phone = phone.val();
        }
        $.ajax({
            url: aw_rc.ajax_url,
            method: 'POST',
            cache: false,
            data: JSON.stringify(data),
            contentType: "application/json; charset=utf-8",
            crossDomain: false,
            dataType: "json",
            success: function success(response_json) {
                var response = JSON.parse(response_json);
                if (response.is_subscribed) {
                }
            }
        });
    }

    $(document).ready(function () {

        var billing_email = $('#billing_email');
        var shipping_email = $('#shipping_email');
        var billing_phone = $('#billing_phone');

        if (billing_email.length > 0) {
            billing_email.on('change', function () {
                var email = $(this).val();
                if ('' !== email && ValidateEmail(email)) {
                    send_to_active_campaing(email);
                }
            });
        } else if (shipping_email.length > 0) {
            shipping_email.on('change', function () {
                var email = $(this).val();
                if ('' !== email && ValidateEmail(email)) {
                    send_to_active_campaing(email);
                }
            });
        }

        if (billing_email.length > 0 || shipping_email.length > 0) {
            billing_phone.on('change', function () {
                var billing_phone = $(this).val();

                var email = $('#billing_email').val();
                if ('' === email || '' == email) {
                    email = $('#shipping_email').val();
                }
                if ('' !== billing_phone && '' !== email && ValidateEmail(email)) {
                    send_to_active_campaing(email);
                }
            });

        }

    });
})(jQuery);