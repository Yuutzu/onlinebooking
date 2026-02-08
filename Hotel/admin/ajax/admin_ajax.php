<script>
    // REGULAR CLIENT
    $(document).ready(function() {
        function fetchClients(page = 1, query = '') {
            $.ajax({
                url: '../admin/fetch/admin_fetch.php', // Updated to fetch from clients table
                method: 'GET',
                data: {
                    page: page,
                    query: query
                },
                dataType: 'json',
                success: function(response) {
                    $('#results').html(response.clients);
                    $('#pagination').html(response.pagination);
                }
            });
        }

        // Initial fetch
        fetchClients();

        // Search functionality
        $('#search').on('keyup', function() {
            const query = $(this).val();
            fetchClients(1, query); // Reset to page 1
        });

        // Pagination functionality
        $(document).on('click', '.page-link', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            const query = $('#search').val();
            fetchClients(page, query);
        });
    });
</script>
