(function($){
    $(function(){
        $('.precise_featured_post_star').click(function(e){
            e.preventDefault();

            var $featured = $(this);

            var data = {
                action: 'precise_featured_post_action',
                post_id: $featured.find(".post_id").val(),
                field_name: $featured.find(".field_name").val()
            };

            $.post(window.ajaxurl, data, function(response) {
                if (response.status == "ok") {
                    if ( response.action == "uncheck" ) {
                        $featured.removeClass("precise_featured_post_checked");
                    } else {
                        $featured.addClass("precise_featured_post_checked");
                    }
                }
            }, 'json');

        });
    });
})(jQuery);
