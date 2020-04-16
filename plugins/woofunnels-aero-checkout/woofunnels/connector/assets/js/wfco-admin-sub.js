(function ($) {

    jQuery(document).ready(
        function () {
            jQuery("html body").on(
                "click",
                ".wfco-copy-clipboard",
                function () {
                    var elem = jQuery(this)[0];
                    elem.select();
                    copyToClipboard(elem);
                }
            );
            jQuery("html body").on(
                "click",
                ".wfco-text-copy-btn",
                function (e) {
                    e.preventDefault();
                    var $this = jQuery(this);
                    $this.siblings('.wrapper').find(".wfco-copy-clipboard").trigger("click");

                }
            );
            wfco_open_success_swal();
            wfco_load_int_settings();
        }
    );

    jQuery(window).on(
        'load',
        function () {
            wfco_handle_sync_connector();
            wfco_handle_delete_connector();
            get_autoresponder_fields();
            wfco_install_connector();
        }
    );

    var last = function (array) {
        return array[array.length - 1];
    };

    function ArrayToObject(arr) {
        var obj = {};
        for (var i = 0; i < arr.length; i++) {
            obj[arr[i]] = arr[i];
        }
        return obj
    }

    function copyToClipboard(elem) {

        var targetId = "_hiddenCopyText_";

        var isInput = elem.tagName === "INPUT" || elem.tagName === "TEXTAREA";

        var origSelectionStart, origSelectionEnd;

        if (isInput) {

            target = elem;

            origSelectionStart = elem.selectionStart;

            origSelectionEnd = elem.selectionEnd;

        } else {

            target = document.getElementById(targetId);

            if (!target) {

                var target = document.createElement("textarea");

                target.style.position = "absolute";

                target.style.left = "-9999px";

                target.style.top = "0";

                target.id = targetId;

                document.body.appendChild(target);

            }

            target.textContent = elem.textContent;

        }

        var currentFocus = document.activeElement;

        target.focus();

        target.setSelectionRange(0, target.value.length);

        var succeed;

        try {

            succeed = document.execCommand("copy");
            jQuery.toast(
                {
                    heading: wfcoParams.texts.text_copied,
                    // text: 'Text Copied',
                    position: 'bottom-right',
                    // icon: 'warning',
                    // stack: false,
                    allowToastClose: false,
                }
            );

        } catch (e) {

            succeed = false;

        }

        if (currentFocus && typeof currentFocus.focus === "function") {

            currentFocus.focus();

        }

        if (isInput) {

            elem.setSelectionRange(origSelectionStart, origSelectionEnd);

        } else {

            target.textContent = "";

        }

        return succeed;
    }

    function ol(obj) {
        let c = 0;
        if (obj != null && typeof obj === "object") {
            c = Object.keys(obj).length;
        }
        return c;
    }

    function wfco_getUrlParameter(name) {
        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
        var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
        var results = regex.exec(location.search);
        return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
    };

    function in_Array(value, array) {
        return array.indexOf(value) > -1;
    }

    function wfco_load_int_settings() {
        var selected_value = wfco_getUrlParameter('int');
        var $this = jQuery(this);
        if (selected_value != '') {
            var selected_connector = wp.template('connector-' + selected_value);
            jQuery('#wfco_connector_edit_fields').html('');
            wfco_make_html(1, '#wfco_connector_edit_fields', selected_connector());
        } else {
            jQuery('#wfco_connector_edit_fields').html('');
        }
    }

    function wfco_open_success_swal() {
        var int = wfco_getUrlParameter('wfco_connector');
        var access_token = wfco_getUrlParameter('access_token');
        var connector = wfcoParams.oauth_connectors;
        if (in_Array(int, connector) == true && access_token != '') {
            swal(
                {
                    title: wfcoParams.texts.sync_wait,
                    text: wfcoParams.texts.sync_progress,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    onOpen: () => {
                        swal.showLoading();
                    }
                }
            );
            let wp_ajax = new wp_admin_ajax();
            let add_query = {"_wpnonce": wfcoParams.oauth_nonce, "wfco_connector": int, "access_token": access_token};

            wp_ajax.ajax('save_connector', add_query);

            wp_ajax.success = function (rsp) {
                if (rsp.status == true) {
                    swal(
                        {
                            title: wfcoParams.texts.sync_success_title,
                            text: wfcoParams.texts.sync_success_text,
                            type: "success",
                            // showConfirmButton: false,
                        }
                    );
                    setTimeout(
                        function () {
                            window.location.href = rsp.redirect_url;
                        },
                        3000
                    );
                } else {
                    $("#wfco-modal-connect").iziModal('close');
                    setTimeout(
                        function () {
                            swal(
                                {
                                    title: "Oops",
                                    text: 'There was some error. Please try again later.',
                                    type: "error",
                                }
                            );
                        },
                        1000
                    );
                }
            };
            return false;
        }
    }

    function wfco_install_connector() {
        jQuery(document).on(
            'click',
            '.wfco_connector_install',
            function () {
                var $this = jQuery(this);
                var sync_nonce = jQuery(this).attr('data-nonce');
                var connector_slug = jQuery(this).attr('data-connector');
                var loading_text = jQuery(this).attr('data-load-text');
                var page_text = jQuery(this).attr('data-text');
                var slug = jQuery(this).attr('data-connector-slug');
                $this.text(loading_text);

                var add_query = {
                    'install_nonce': sync_nonce,
                    'connector_slug': connector_slug,
                    'slug': slug,
                };

                let wp_ajax = new wp_admin_ajax();
                wp_ajax.ajax('connector_install', add_query);
                wp_ajax.success = function (resp) {
                    var redirect = /({.+})/img;
                    var matches = redirect.exec(resp);
                    var responseObj = resp;
                    if (null !== matches && matches.length > 0) {
                        responseObj = jQuery.parseJSON(matches[0]);
                    }

                    if (responseObj.status == true) {
                        let connector_licence = new wp_admin_ajax();
                        connector_licence.ajax('create_connector_license', add_query);
                        connector_licence.success = function (resp) {
                            swal(
                                {
                                    title: '',
                                    text: responseObj.msg,
                                    type: "success",
                                    showConfirmButton: false,
                                }
                            );
                            setTimeout(
                                function () {
                                    window.location.reload();
                                },
                                1000
                            );
                        };
                    } else {
                        console.log(responseObj.msg);
                        let msg = responseObj.msg;
                        if (responseObj.hasOwnProperty('error_code')) {
                            if (wfcoParams.errors[responseObj.connector].hasOwnProperty(responseObj.error_code)) {
                                msg = wfcoParams.errors[responseObj.connector][responseObj.error_code];
                            }
                        }

                        let sw = swal(
                            {
                                title: wfcoParams.texts.oops_title,
                                text: msg,
                                type: "error",
                            }
                        );
                        sw.catch(
                            function () {
                                console.log('Cs');
                            }
                        )
                    }

                    $this.text(page_text);

                };
            }
        );
    }

    function wfco_handle_sync_connector() {
        jQuery(document).on('click', '.wfco-connector-sync', function () {

                var sync_nonce = jQuery(this).attr('data-nonce');
                var connector_id = jQuery(this).attr('data-id');
                var slug = jQuery(this).attr('data-slug');
                var sync_swal = {
                    title: wfcoParams.texts.sync_title,
                    text: wfcoParams.texts.sync_text,
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Yes, proceed",
                    cancelButtonText: "No",
                    allowOutsideClick: false,

                };

                var swal_then = swal(sync_swal);
                swal_then.then(function (result) {
                        if (true !== result) {
                            return;
                        }

                        var swal_data = {
                            title: wfcoParams.texts.sync_wait,
                            text: wfcoParams.texts.sync_progress,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            allowEnterKey: false,
                            onOpen: () => {
                                swal.showLoading();
                            }
                        };
                        // swal.showLoading();
                        swal(swal_data);


                        $.post(wfcoParams.ajax_url, {'sync_nonce': sync_nonce, 'id': connector_id, 'slug': slug, 'action': 'wfco_sync_connector'}, function (resp) {
                                if (resp.status == true) {
                                    if (resp.data_changed == 1) {
                                        swal(
                                            {
                                                title: wfcoParams.texts.sync_success_title,
                                                type: "success",
                                                text: wfcoParams.texts.sync_success_text,
                                                // showConfirmButton: false,
                                            }
                                        );
                                    } else {
                                        swal(
                                            {
                                                title: wfcoParams.texts.sync_success_title,
                                                type: "success",
                                                showConfirmButton: false,
                                            }
                                        );
                                        setTimeout(
                                            function () {
                                                window.location.reload();
                                            },
                                            1000
                                        );
                                    }
                                } else {
                                    swal({
                                            title: wfcoParams.texts.oops_title,
                                            text: resp.hasOwnProperty('msg') ? resp.msg : wfcoParams.texts.oops_text,
                                            type: "error",
                                        }
                                    );
                                }

                            }
                        );
                    }
                ).catch(
                    function (result) {

                    }
                );
            }
        );

    }

    function wfco_handle_delete_connector() {
        jQuery(document).on(
            'click',
            '.wfco-connector-delete',
            function () {
                var delete_nonce = jQuery(this).attr('data-nonce');
                var connector_id = jQuery(this).attr('data-id');

                swal(
                    {
                        title: wfcoParams.texts.delete_int_prompt_title,
                        text: wfcoParams.texts.delete_int_prompt_text,
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Yes, proceed",
                        cancelButtonText: "No",
                        allowOutsideClick: false,

                    }
                ).then(
                    function (result) {
                        if (result) {
                            // swal.showLoading();
                            swal(
                                {
                                    title: wfcoParams.texts.delete_int_wait_title,
                                    text: wfcoParams.texts.delete_int_wait_text,
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    allowEnterKey: false,
                                    onOpen: () => {
                                        swal.showLoading();
                                    }
                                }
                            );

                            $.post(
                                wfcoParams.ajax_url,
                                {'delete_nonce': delete_nonce, 'id': connector_id, 'action': 'wfco_delete_connector'},
                                function (resp) {
                                    if (resp.status == true) {
                                        swal(
                                            {
                                                title: wfcoParams.texts.delete_int_success,
                                                type: "success",
                                                showConfirmButton: false,
                                            }
                                        );
                                        setTimeout(
                                            function () {
                                                window.location.href = resp.redirect_url;
                                            },
                                            1000
                                        );
                                    } else {
                                        swal(
                                            {
                                                title: wfcoParams.texts.oops_title,
                                                text: wfcoParams.texts.oops_text,
                                                type: "error",
                                            }
                                        );
                                    }

                                }
                            );

                        }
                    }
                ).catch(
                    function (result) {

                    }
                );
            }
        );

    }

    function get_autoresponder_fields() {
        if ($('.wfco_connector_field').length > 0) {
            var slug = $('.wfco_connector_field').data('slug');
            var data_db = ($('.wfco_connector_field').data('db'));
            var data_saved = ($('.wfco_connector_field').data('saved'));

            var selected_task = wp.template(slug);
            jQuery('#wfco_i_field').html('');
            wfco_make_html(1, '#wfco_i_field', selected_task({db_data: data_db}));

            // var selected_task = wp.template(slug);
            // jQuery('#wfco_i_field').html('');

            if ((data_saved in data_db) && data_saved != '') {
                $('#wfco_remove_c_i').val(data_saved);
            } else {
                $('#wfco_remove_c_i').val("");
            }

            if ($('.sub-field').length > 0) {
                var s_slug = $('.wfco_connector_field').data('s_slug');
                var s_data_db = ($('.wfco_connector_field').data('s_db'));
                var s_data_saved = ($('.wfco_connector_field').data('s_saved'));
                var s_selected_task = wp.template(s_slug);
                wfco_make_html(1, '.wfco-fields-meta', s_selected_task({ajax_data: s_data_db}));

                if ((s_data_saved in s_data_db) && s_data_saved != '') {
                    $('.wfco-fields-meta select').val(s_data_saved);
                } else {
                    $('.wfco-fields-meta select').val("");
                }
            }
        }
    }

})(jQuery);
