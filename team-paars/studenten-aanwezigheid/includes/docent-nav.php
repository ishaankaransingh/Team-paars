<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
  <title>Docenten</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="../CSS/navbar.css" >
    <link rel="stylesheet" href="../CSS/main.css" >
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
      <a href="docent-dashboard.php" class="<?= ($activePage == 'dashboard') ? 'active' : ''; ?>">
        <i class="fas fa-qrcode"></i>
        <span>Dashboard</span>
      </a>
      <!--<a href="docent-aanwezigheid.php" class="<?= ($activePage == 'aanwezigheid') ? 'active' : ''; ?>">
        <i class="fas fa-chalkboard-teacher" ></i>
        <span>Aanwezigheid</span>
      </a>-->
      <a href="../login/logout.php">
        <i class="fas fa-sign-out-alt"></i>
        <span>Log out</span>
      </a>
    </div>