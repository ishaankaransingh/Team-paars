
<!DOCTYPE html>
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
  <header>Admin Menu</header>
  <a href="admin-dashboard.php" class="<?= ($activePage == 'dashboard') ? 'active' : ''; ?>">
    <i class="fas fa-qrcode"></i>
    <span>Dashboard</span>
  </a>
  <a href="admin-docenten.php" class="<?= ($activePage == 'docenten') ? 'active' : ''; ?>">
    <i class="fas fa-chalkboard-teacher"></i>
    <span>Docenten</span>
  </a>
  <a href="admin-studenten.php" class="<?= ($activePage == 'studenten') ? 'active' : ''; ?>">
    <i class="fas fa-user-graduate"></i>
    <span>Studenten</span>
  </a>
  <a href="admin-klassen.php" class="<?= ($activePage == 'klassen') ? 'active' : ''; ?>">
    <i class="fas fa-chalkboard"></i>
    <span>Klassen</span>
  </a>
  <a href="admin-vakken.php" class="<?= ($activePage == 'vakken') ? 'active' : ''; ?>">
    <i class="fas fa-book"></i>
    <span>Vakken</span>
  </a>
  <a href="../login/logout.php">
    <i class="fas fa-sign-out-alt"></i>
    <span>Log out</span>
  </a>
</div>