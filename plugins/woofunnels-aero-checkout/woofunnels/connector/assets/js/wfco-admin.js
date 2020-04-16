(function ($) {
    'use strict';
    $(document).ready(
        function () {
            // wfco_modal_add_connector();
            wfco_modal_edit_connector();
            wfco_update_connector();
            // wfco_add_connector();
            // wfco_sync_connector();

            /** Metabox panel close */
            $(".wfco_allow_panel_close .hndle").on(
                "click",
                function () {
                    var $this = $(this);
                    var parentPanel = $(this).parents(".wfco_allow_panel_close");
                    parentPanel.toggleClass("closed");
                }
            );

            wfco_connector_settings_html();

        }
    );

    function wfco_modal_add_connector() {
        if ($("#wfco-modal-connect").length > 0) {
            $("#wfco-modal-connect").iziModal(
                {
                    headerColor: '#6dbe45',
                    background: '#efefef',
                    borderBottom: false,
                    history: false,
                    width: 600,
                    overlayColor: 'rgba(0, 0, 0, 0.6)',
                    transitionIn: 'bounceInDown',
                    transitionOut: 'bounceOutDown',
                    navigateCaption: true,
                    navigateArrows: "false",
                    onOpening: function (modal) {
                        modal.startLoading();
                    },
                    onOpened: function (modal) {
                        modal.stopLoading();
                        $('.wfco_submit_btn_style').text(wfcoParams.texts.update_btn);
                    },
                    onClosed: function (modal) {
                        //console.log('onClosed');
                    }
                }
            );
        }
    }

    function wfco_modal_edit_connector() {
        if ($("#modal-edit-connector").length > 0) {
            $("#modal-edit-connector").iziModal(
                {
                    headerColor: '#6dbe45',
                    background: '#efefef',
                    borderBottom: false,
                    history: false,
                    width: 600,
                    overlayColor: 'rgba(0, 0, 0, 0.6)',
                    transitionIn: 'bounceInDown',
                    transitionOut: 'bounceOutDown',
                    navigateCaption: true,
                    navigateArrows: "false",
                    onOpening: function (modal) {
                        modal.startLoading();
                    },
                    onOpened: function (modal) {
                        modal.stopLoading();
                        // vue_add_automation(modal);
                    },
                    onClosed: function (modal) {
                        //console.log('onClosed');
                    }
                }
            );
        }
    }

    function wfco_update_connector() {
        if ($('.wfco_update_connector').length > 0) {
            let wp_form_ajax = new wp_admin_ajax('.wfco_update_connector', true, function (ajax) {
                ajax.before_send = function (element, action) {
                    console.log('update connector form ' + ajax.action);
                    if (ajax.action === 'wfco_update_connector') {
                        $('.wfco_update_btn_style').val(wfcoParams.texts.update_btn_process);
                    }
                };
                ajax.success = function (rsp) {
                    if (ajax.action === 'wfco_update_connector') {

                        if (rsp.status === true) {
                            if (rsp.data_changed == 1) {
                                $('form.wfco_update_connector').hide();
                                $('.wfco-automation-update-success-wrap').show();
                                // $('.wfco_form_response').html(rsp.msg);
                                setTimeout(
                                    function () {
                                        window.location.href = rsp.redirect_url;
                                    },
                                    3000
                                );
                            } else {
                                $("#modal-edit-connector").iziModal('close');
                                setTimeout(
                                    function () {
                                        swal(
                                            {
                                                title: wfcoParams.texts.update_int_prompt_title,
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
                                    },
                                    1000
                                );
                            }
                        } else {
                            $('.wfco_form_response').html(rsp.msg);
                            $('.wfco_update_connector').find("input[type=submit]").prop('disabled', false);
                            $('.wfco_save_btn_style').val('update');
                        }
                    }
                };
            });
        }
    }

    function wfco_add_connector() {
        if ($('.wfco_add_connector').length > 0) {
            let wp_form_ajax = new wp_admin_ajax(
                '.wfco_add_connector',
                true,
                function (ajax) {
                    ajax.before_send = function (element, action) {
                        if (ajax.action === 'wfco_save_connector') {
                            $('.wfco_save_btn_style').val(wfcoParams.texts.connect_btn_process);
                        }
                    };
                    ajax.success = function (rsp) {
                        if (ajax.action === 'wfco_save_connector') {

                            if (rsp.status === true) {

                                $('form.wfco_add_connector').hide();
                                $('.wfco-connector-create-success-wrap').show();
                                $('.wfco_form_response').html(rsp.msg);

                                setTimeout(
                                    function () {
                                        window.location.href = rsp.redirect_url;
                                    },
                                    3000
                                );

                            } else {
                                $('.wfco_form_response').html(rsp.msg);
                                $('.wfco_add_connector').find("input[type=submit]").prop('disabled', false);
                                $('.wfco_save_btn_style').val('save');
                            }
                        }
                    };
                }
            );
        }
    }

    function wfco_connector_settings_html() {
        jQuery(document).on('click', '.wfco-connector-connect', function () {
            var $this = jQuery(this);
            // var selected_value = $this.val();
            var selected_value = $this.attr('data-slug');
            var type = $this.attr('data-type');
            var Title = $this.attr('data-iziModal-title');
            if (selected_value != '') {
                var selected_connector = wp.template('connector-' + selected_value);
                jQuery('#wfco_connector_fields').html('');
                wfco_make_html(1, '#wfco_connector_fields', selected_connector());
                if (type == 'direct') {
                    console.log('direct');
                    jQuery('.wfco_add_connector').trigger('submit');
                    $this.addClass('wfco_btn_spin');
                }
            } else {
                jQuery('#wfco_connector_fields').html('');
            }
            $("#wfco-modal-connect").iziModal('setTitle', Title);
        });
        jQuery(document).on('click', '.wfco-connector-edit', function () {
            var $this = jQuery(this);
            var selected_value = $this.data('slug');
            var Title = $this.attr('data-iziModal-title');
            console.log(selected_value);
            if (selected_value != '') {
                var selected_connector = wp.template('connector-' + selected_value);
                jQuery('#wfco_connector_edit_fields').html('');
                wfco_make_html(1, '#wfco_connector_edit_fields', selected_connector());
            } else {
                jQuery('#wfco_connector_edit_fields').html('');
            }
            $("#modal-edit-connector").iziModal('setTitle', Title);
        });
    }

    function wfco_sync_connector() {
        if ($('.wfco_sync_connector').length > 0) {
            let wp_form_ajax = new wp_admin_ajax(
                '.wfco_sync_connector',
                true,
                function (ajax) {
                    ajax.before_send = function (element, action) {
                        if (ajax.action === 'wfco_save_connector') {
                            $('.wfco_save_btn_style').text(wfcoParams.texts.connect_btn_process);
                        }
                    };
                    ajax.success = function (rsp) {
                        if (ajax.action === 'wfco_save_connector') {

                            if (rsp.status === true) {
                                // $('form.wfco_sync_connector').hide();
                                $('.wfco-autoresponder-sync-success-wrap').show();
                                // $('.wfco_form_response').html(rsp.msg);
                                setTimeout(
                                    function () {
                                        window.location.href = rsp.redirect_url;
                                    },
                                    3000
                                );

                            } else {
                                $('.wfco_form_response').html(rsp.msg);
                            }
                        }
                    };
                }
            );
        }
    }

    function wfco_make_html(empty_old_html, container_element, new_html) {
        var output_container = jQuery(container_element);
        if (empty_old_html == 1) {
            jQuery(container_element).html('');
            var output_container_html = jQuery(container_element).html();
            output_container.html(output_container_html + new_html);
        } else if (empty_old_html == 2) {
            output_container.append(new_html);
        }
    }

})(jQuery);
