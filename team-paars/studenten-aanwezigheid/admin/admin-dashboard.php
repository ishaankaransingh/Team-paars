<?php
session_start();

if (isset($_SESSION['gebruiker_id']) && isset($_SESSION['user_name'])) {
  $activePage = 'dashboard';
  include('../includes/admin-nav.php');
?>

<?php
} else {
  header("Location: index.php");
  exit();
}
?>