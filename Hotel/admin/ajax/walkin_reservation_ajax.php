<script>
    // REGULAR CLIENT
$(document).ready(function() {
    function fetchReservations(page = 1, query = '') {
        $.ajax({
            url: '../admin/fetch/walkin_reservation_fetch.php', // Updated fetch file
            method: 'GET',
            data: {
                page: page,
                query: query
            },
            dataType: 'json',
            success: function(response) {
                $('#results').html(response.reservations); // Updated response key
                $('#pagination').html(response.pagination);
            }
        });
    }

    // Initial fetch
    fetchReservations();

    // Search functionality
    $('#search').on('keyup', function() {
        const query = $(this).val();
        fetchReservations(1, query); // Reset to page 1
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