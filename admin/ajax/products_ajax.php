<script>
    // Handle fetching products and pagination
    $(document).ready(function() {
        function fetchProducts(page = 1, query = '') {
            $.ajax({
                url: '../admin/fetch/products_fetch.php',
                method: 'GET',
                data: {
                    page: page,
                    query: query
                },
                dataType: 'json',
                success: function(response) {
                    $('#results').html(response.products);  // Update the products table
                    $('#pagination').html(response.pagination);  // Update pagination links
                }
            });
        }

        // Initial fetch of products when the page loads
        fetchProducts();

        // Handle search input
        $('#search').on('keyup', function() {
            const query = $(this).val();
            fetchProducts(1, query);  // Reset to page 1 for fresh search
        });

        // Handle pagination link click
        $(document).on('click', '.page-link', function(e) {
            e.preventDefault();
            const page = $(this).data('page');  // Get the page number from the link
            const query = $('#search').val();  // Get the current search query
            fetchProducts(page, query);
        });
    });
</script>
