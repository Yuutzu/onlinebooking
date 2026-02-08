<script>
    $(document).ready(function() {
        // Function to fetch inquiries based on search query and page
        function fetchInquiries(page = 1, query = '') {
            $.ajax({
                url: '../admin/fetch/inquiry_fetch.php', // Updated fetch file
                method: 'GET',
                data: {
                    page: page,
                    query: query
                },
                dataType: 'json',
                success: function(response) {
                    $('#results').html(response.inquiries);  // Update table with new rows
                    $('#pagination').html(response.pagination);  // Update pagination links
                }
            });
        }

        // Initial fetch on page load
        fetchInquiries();

        // Search functionality - triggered on keyup event
        $('#search').on('keyup', function() {
            const query = $(this).val();  // Get the search input value
            fetchInquiries(1, query);  // Fetch inquiries on page 1 for the new query
        });

        // Pagination functionality - triggered when a page number is clicked
        $(document).on('click', '.page-link', function(e) {
            e.preventDefault();  // Prevent the default behavior of the link
            const page = $(this).data('page');  // Get the page number from data attribute
            const query = $('#search').val();  // Get the current search query
            fetchInquiries(page, query);  // Fetch the inquiries for the selected page
        });
    });
</script>
