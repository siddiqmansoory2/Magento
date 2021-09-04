$(document).ready(() => {
    jQuery('.burger-menu').on('click', () => {
        jQuery('.left-side-menu-mob').css({
            'left': '0',
        })
    });

    jQuery('.close-btn').on('click', () => {
        jQuery('.left-side-menu-mob').css({
            'left': '-300px',
        })
    });


    jQuery('.left-side-icons a .fa-search').on('click', () => {
        jQuery('.search-fied').css({
            'width': '85%',
        });
    })

    jQuery('.close-search').on('click', () => {
        jQuery('.search-fied').css({
            'width': '0',
        });
    })

    jQuery('.search-mob').on('click', () => {
        jQuery('.mobile-search').toggle();
    })

    jQuery('.cancel').on('click', () => {
        jQuery('.mobile-search').toggle();
    })
})