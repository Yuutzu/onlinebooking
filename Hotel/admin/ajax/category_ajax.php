<script>
    $(document).ready(function() {
        function fetchCategories(page = 1, query = '') {
            $.ajax({
                url: '../admin/fetch/category_fetch.php',
                method: 'GET',
                data: { page: page, query: query },
                dataType: 'json',
                success: function(response) {
                    $('#results').html(response.clients);
                    $('#pagination').html(response.pagination);  // Inject pagination
                }
            });
        }

        // Initial fetch
        fetchCategories();

        // Search functionality
        $('#search').on('keyup', function() {
            const query = $(this).val();
            fetchCategories(1, query);  // Reset to page 1 on search
        });

        // Pagination functionality
        $(document).on('click', '.page-link', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            const query = $('#search').val();  // Get the current search query
            fetchCategories(page, query);
        });
    });
</script>
