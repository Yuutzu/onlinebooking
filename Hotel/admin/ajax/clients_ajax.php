<script>
    $(document).ready(function() {
        function fetchClients(page = 1, query = '') {
            $.ajax({
                url: '../admin/fetch/clients_fetch.php', // Updated to fetch from clients table
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

        // Open Modal and Populate Data
        $(document).on("click", ".open-modal", function () {
            let clientID = $(this).data("client_id");
            let name = $(this).data("name");
            let email = $(this).data("email");
            let phone = $(this).data("phone");
            let presentedID = $(this).data("presented_id");
            let idNumber = $(this).data("id_number");
            let idPicture = $(this).data("id_picture");
            let role = $(this).data("role");
            let failedAttempts = $(this).data("failed_attempts");
            let lastFailedAttempt = $(this).data("last_failed_attempt");
            let status = $(this).data("status");
            let profilePicture = $(this).data("profile_picture");

            // Populate fields
            $("#edit_client_id").val(clientID);
            $("#edit_client_id_display").val(clientID);
            $("#edit_client_name").val(name);
            $("#edit_client_email").val(email);
            $("#edit_client_phone").val(phone);
            $("#edit_client_presented_id").val(presentedID);
            $("#edit_client_id_number").val(idNumber);
            $("#edit_client_role").val(role);
            $("#edit_failed_attempts").val(failedAttempts);
            $("#edit_last_failed_attempt").val(lastFailedAttempt);
            $("#edit_client_status").val(status);

            // Profile Picture
            $("#edit_profile_picture").attr("src", "./dist/img/" + profilePicture);

            // Show modal
            $("#updateClientModal").modal("show");
        });
    });
</script>
