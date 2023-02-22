jQuery(document).ready(function ($) {

    let timer = null;
    let lastSearch = null;


    const searchForm = $('#scholar-scraper-search-form');
    const searchInput = searchForm.find('#search');
    const searchIcon = searchForm.find('.icon-container');


    /**
     * Active the parent of the given element.
     * @param element The element to get the parent of.
     * @since 1.1.0
     */
    function activeParent(element) {
        $(element).parent().addClass('active');
    }

    /**
     * Inactive the parent of the given element.
     * @param element The element to get the parent of.
     * @since 1.1.0
     */
    function inactiveParent(element) {
        $(element).parent().removeClass('active');
    }


    /**
     * Search for papers.
     * @since 1.1.0
     */
    function searchInPapers() {
        clearTimeout(timer);

        const searchQuery = searchInput.val().trim().toLowerCase();

        if (lastSearch === searchQuery || (
            lastSearch === null && searchQuery === ''
        )) {
            return;
        }

        timer = setTimeout(function () {

            lastSearch = searchQuery;

            var blockId = js_data.block_id ?? searchForm.attr('data-block_id');

            if (blockId === undefined) {
                console.error('An error occurred while searching.');
                return;
            }

            var post_id = js_data.post_id ?? searchForm.attr('data-post_id');

            if (post_id === undefined) {
                console.error('An error occurred while searching.');
                return;
            }

            console.log('searchQuery: ', searchQuery);


            $.ajax({
                url: js_data.ajax_url ?? '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'search_in_papers',
                    search_query: searchQuery,
                    block_id: blockId,
                    post_id: post_id,
                },
                success: function (response) {
                    if (response.success === false || response.data === undefined) {
                        console.log('An error occurred while searching.');
                        return;
                    }

                    let publicationsContainer = $('.scholar-scraper-publications');
                    publicationsContainer.replaceWith(response.data);
                },
                error: function () {
                    console.log('An error occurred while searching.');
                }
            });
        }, js_data.search_delay ?? 500);
    }


    searchForm.on('submit', function (event) {
        event.preventDefault();
        searchInPapers();
    });
    searchInput.on('keyup paste', searchInPapers);
    searchIcon.on('click', searchInPapers);

    searchInput.on('focus active', function () {
        activeParent(this);
    });

    searchInput.on('blur inactive', function () {
        inactiveParent(this);
    });

    searchIcon.on('focus active', function () {
        activeParent(this);
    });

    searchIcon.on('blur inactive', function () {
        inactiveParent(this);
    });
});
