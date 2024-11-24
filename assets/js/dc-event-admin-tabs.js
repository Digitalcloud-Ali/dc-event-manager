(function($) {
    $(document).ready(function() {
        $('.dc-event-tab-nav a').on('click', function(e) {
            e.preventDefault();
            var target = $(this).attr('href');
            $('.dc-event-tab-nav li').removeClass('active');
            $(this).parent().addClass('active');
            $('.dc-event-tab-pane').removeClass('active');
            $(target).addClass('active');
        });
    });
})(jQuery);