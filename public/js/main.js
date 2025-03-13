$(document).ready(function() {
    // Load initial content
    $('#content-area').addClass('content-loading').load('dashboard.php', function() {
        $(this).removeClass('content-loading');
    });

    // Handle menu clicks
    $('.menu-item').click(function() {
        if (!$(this).hasClass('active')) {
            $('.menu-item').removeClass('active');
            $(this).addClass('active');
            
            const page = $(this).data('page');
            $('#content-area').addClass('content-loading').load(page + '.php', function() {
                $(this).removeClass('content-loading');
            });
        }
    });
});