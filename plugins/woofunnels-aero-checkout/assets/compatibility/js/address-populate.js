(function ($) {

    console.log("test");
    let address_el = ["#shipping_address_1", "#shipping_address_2", "#shipping_city", "#shipping_state", "#shipping_postcode", "#billing_address_1", "#billing_address_2", "#billing_city", "#billing_state", "#billing_postcode"];
    let timeOut = null;
    function remove_animate_class(from_ele) {
        clearTimeout(timeOut);
        timeOut = setTimeout(() => {

            for (var i = 0; i < address_el.length; i++) {

                let from_ele = $(address_el[i]);
                var element_val = '';


                if (from_ele.length > 0) {
                    element_val = from_ele.val();
                    if (element_val != '' && !$(address_el[i] + "_field").hasClass('wfacp-anim-wrap')) {
                        $(address_el[i] + "_field").addClass('wfacp-anim-wrap');
                    }
                }


            }
        }, 200);


    }

    $(document).ready(function () {
        /* Remove animate class from div when auto complete address selected*/
        for (var i = 0; i < address_el.length; i++) {
            $(document).on("change", address_el[i], remove_animate_class);
        }
        if($(".pcaitem").length > 0){
            $(document.body).on("click", '.pcaitem', remove_animate_class);
        }


    })
})(jQuery);