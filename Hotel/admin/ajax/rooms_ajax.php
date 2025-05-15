<script>
    $(document).ready(function() {
        let debounceTimer; // Debounce timer

        function fetchRooms(page = 1, query = '') {
            // Disable pagination buttons while waiting for the request
            $('.page-link').addClass('disabled'); 

            $.ajax({
                url: '../admin/fetch/rooms_fetch.php',
                method: 'GET',
                data: { page: page, query: query },
                dataType: 'json',
                success: function(response) {
                    $('#results').html(response.clients);
                    $('#pagination').html(response.pagination);
                },
                complete: function() {
                    // Re-enable pagination buttons after the request is completed
                    $('.page-link').removeClass('disabled'); 
                }
            });
        }

        // Initial fetch of rooms when the page loads
        fetchRooms();

        // Debounced search functionality
        $('#search').on('keyup', function() {
            clearTimeout(debounceTimer);
            const query = $(this).val().trim(); // Trim spaces

            debounceTimer = setTimeout(() => {
                fetchRooms(1, query); // Reset to page 1 on search
            }, 300); // 300ms delay
        });

        // Pagination functionality
        $(document).on('click', '.page-link', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            const query = $('#search').val().trim(); // Trim spaces for search query
            fetchRooms(page, query);
        });
    });
</script>
