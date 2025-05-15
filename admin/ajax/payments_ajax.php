<script>
$(document).ready(function() {
    function fetchReservations(page = 1, query = '') {
        $.ajax({
            url: '../admin/fetch/payments_fetch.php', // Ensure this matches your actual PHP file
            method: 'GET',
            data: {
                page: page,
                query: query
            },
            dataType: 'json',
            success: function(response) {
                $('#results').html(response.clients); // Updated to match the PHP response key
                $('#pagination').html(response.pagination);
            }
        });
    }

    // Initial fetch
    fetchReservations();

    // Search functionality
    $('#search').on('keyup', function() {
        const query = $(this).val();
        fetchReservations(1, query); // Reset to page 1 on search
    });

    // Pagination functionality
    $(document).on('click', '.page-link', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        const query = $('#search').val();
        fetchReservations(page, query);
    });
});
</script>
