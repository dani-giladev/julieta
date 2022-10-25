
$(function() {
    
    $('.jcarousel-type1').jcarousel();

    $('.jcarousel-type1-control-prev')
        .on('jcarouselcontrol:active', function() {
            $(this).removeClass('inactive');
        })
        .on('jcarouselcontrol:inactive', function() {
            $(this).addClass('inactive');
        })
        .jcarouselControl({
            target: '-=1'
        });

    $('.jcarousel-type1-control-next')
        .on('jcarouselcontrol:active', function() {
            $(this).removeClass('inactive');
        })
        .on('jcarouselcontrol:inactive', function() {
            $(this).addClass('inactive');
        })
        .on('click', function(e) {
            e.preventDefault();
        })
        .jcarouselControl({
            target: '+=1'
        });

    $('.jcarousel-type1-pagination')
        .on('jcarouselpagination:active', 'a', function() {
            $(this).addClass('active');
        })
        .on('jcarouselpagination:inactive', 'a', function() {
            $(this).removeClass('active');
        })
        .on('click', function(e) {
            e.preventDefault();
        })
        .jcarouselPagination({
            item: function(page) {
                return '<a href="#' + page + '">' + page + '</a>';
            }
        });
});