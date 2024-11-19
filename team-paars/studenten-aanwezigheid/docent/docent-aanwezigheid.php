<?php 

session_start();

if (isset($_SESSION['gebruiker_id']) && isset ($_SESSION['user_name'])){
  $activePage = 'aanwezigheid';
include ('../includes/docent-nav.php') 
?>


    <script>
      // Check the state of the checkbox from localStorage when the page loads
      document.addEventListener('DOMContentLoaded', function() {
        const checkBox = document.getElementById('check');
        
        // Retrieve the saved state from localStorage
        if (localStorage.getItem('checkboxState') === 'checked') {
          checkBox.checked = true;
        } else {
          checkBox.checked = false;
        }

        // Listen for changes to the checkbox state
        checkBox.addEventListener('change', function() {
          if (this.checked) {
            localStorage.setItem('checkboxState', 'checked');
          } else {
            localStorage.setItem('checkboxState', 'unchecked');
          }
        });
      });
    </script>
  </body>
</html>
<?php
}else{
    header("Location: index.php");
    exit();
}
?>