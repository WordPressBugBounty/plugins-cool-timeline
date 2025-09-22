jQuery(document).ready(function($) {

    Object.keys(window).forEach(function(key) {
        if (key.startsWith('CtlNoticeData_')) {
            var noticeData = window[key];
            var wrapperSelector = noticeData.review 
                ? noticeData.id + '-feedback-notice-wrapper'  // review box
                : noticeData.id + '_admin_notice';           // normal notice

            // Only attach dismiss handler if NOT a review box
            if (!noticeData.review) {
                $(document).on("click", "." + wrapperSelector + " button.notice-dismiss, ." + wrapperSelector + " a._dismiss_notice", function(e) {
                    e.preventDefault();
                    var $wrapper = $(this).closest("." + wrapperSelector);
                    if ($wrapper.length) {
                        $.post(noticeData.ajax_url, {
                            action: noticeData.ajax_callback,
                            slug: noticeData.plugin_slug,
                            id: noticeData.id,
                            _nonce: noticeData.wp_nonce
                        }, function() {
                            $wrapper.slideUp("fast");
                        }, "json");
                    }
                });
            }
            // For review boxes, you can attach your own buttons separately:
          if (noticeData.review) {
                    $(document).on("click", "#" + noticeData.id + " .already_rated_btn", function(e) {
                        e.preventDefault();

                        var $wrapper = $("#" + noticeData.id); // Use ID directly
                        $.post(noticeData.ajax_url, {
                            action: noticeData.ajax_callback,
                            slug: noticeData.plugin_slug,
                            id: noticeData.id,
                            _nonce: noticeData.wp_nonce
                        }, function() {
                            $wrapper.slideUp("fast"); // Slide up wrapper
                        }, "json");
                    });
         }


        }
    });

});
