<?php

function alert($type, $msg)
{
    $icon = ($type == "success") ? "success" : "error";

    echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: '$icon',
                title: '$msg',
                position: 'top', // Top-left corner
                showConfirmButton: false,
                timer: 5000, // Auto close after 3 seconds
                toast: true
            });
        });
    </script>";
}
