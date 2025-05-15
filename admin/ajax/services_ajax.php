<script>
    // Handle fetching services and pagination
    $(document).ready(function() {
        function fetchServices(page = 1, query = '') {
            $.ajax({
                url: '../admin/fetch/services_fetch.php',
                method: 'GET',
                data: {
                    page: page,
                    query: query
                },
                dataType: 'json',
                success: function(response) {
                    $('#results').html(response.clients); // Update the results table
                    $('#pagination').html(response.pagination); // Update pagination links
                }
            });
        }

        // Initial fetch of services when the page loads
        fetchServices();

        // Handle search input
        $('#search').on('keyup', function() {
            const query = $(this).val();
            fetchServices(1, query); // Reset to page 1 for fresh search
        });

        // Handle pagination link click
        $(document).on('click', '.page-link', function(e) {
            e.preventDefault();
            const page = $(this).data('page'); // Get the page number from the link
            const query = $('#search').val(); // Get the current search query
            fetchServices(page, query);
        });
    });
</script>
