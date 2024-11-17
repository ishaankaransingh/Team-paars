<?php
session_start();

if (isset($_SESSION['gebruiker_id']) && isset ($_SESSION['user_name'])){

?>
<!DOCTYPE html>
<!-- Created By CodingNepal -->
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="../CSS/navbar.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
  </head>
  <body>
    <input type="checkbox" id="check">
    <label for="check">
      <i class="fas fa-bars" id="btn"></i>
      <i class="fas fa-times" id="cancel"></i>
    </label>
    <div class="sidebar">
      <header>Docent</header>
      <a href="student-dashboard.php" class="active">
        <i class="fas fa-qrcode"></i>
        <span>Dashboard</span>
      </a>
      <a href="student-docenten.php">
        <i class="fas fa-chalkboard-teacher" ></i>
        <span>Aanwezigheid</span>
      </a>
      <a href="../login/logout.php">
        <i class="fas fa-sign-out-alt"></i>
        <span>Log out</span>
      </a>
    </div>

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