 <!-- BOOTSTRAP JS -->
 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

 <script>
     function getiBankAccs(val) {
         $.ajax({
             //get account rates
             type: "POST",
             url: "./admin_pages_ajax.php",
             data: { iBankAccountType: val },
             success: function(data) {
                 //alert(data);

                 $('#AccountRates').val(data);
             }
         });

         $.ajax({
             //get account transferable name
             type: "POST",
             url: "./admin_pages_ajax.php",
             data: 'iBankAccNumber=' + val,
             success: function(data) {
                 //alert(data);
                 $('#ReceivingAcc').val(data);
             }
         });

         $.ajax({
             //get account transferable holder | owner
             type: "POST",
             url: "./admin_pages_ajax.php",
             data: 'iBankAccHolder=' + val,
             success: function(data) {
                 //alert(data);
                 $('#AccountHolder').val(data);
             }
         });
     }
 </script>