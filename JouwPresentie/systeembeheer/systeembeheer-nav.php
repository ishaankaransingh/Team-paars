

<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>systeembeheer Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Arial', sans-serif;
      background: radial-gradient(circle, #0a0a1a, #000);
      color: white;
      min-height: 100vh;
      overflow-x: hidden;
      display: flex;
    }

    /* Sidebar */
    .sidebar {
      width: 250px;
      height: 100vh;
      background: rgba(30, 30, 47, 0.95);
      backdrop-filter: blur(20px);
      position: fixed;
      top: 0;
      left: 0;
      z-index: 1000;
      transition: transform 0.3s ease;
      padding: 20px;
    }

    .sidebar header {
      text-align: center;
      font-size: 1.8rem;
      color: #00ff88;
      padding: 20px;
      text-shadow: 0 0 10px #00ff88;
    }

    .sidebar a {
      color: white;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px;
      border-radius: 10px;
      transition: background 0.3s ease, transform 0.3s ease;
    }

    .sidebar a:hover {
      background: rgba(0, 255, 136, 0.2);
      transform: translateX(5px);
    }

    .sidebar a.active {
      background: rgba(0, 255, 136, 0.2);
      color: #00ff88;
      box-shadow: 0 0 10px rgba(0, 255, 136, 0.5);
    }

    /* Sidebar toggle voor kleine schermen */
    #sidebarToggle {
      display: none;
      position: fixed;
      top: 10px;
      left: 10px;
      z-index: 1001;
      background: rgba(30, 30, 47, 0.9);
      border: none;
      color: white;
      padding: 10px;
      border-radius: 5px;
      cursor: pointer;
    }

    @media (max-width: 768px) {
      #sidebarToggle {
        display: block;
      }

      .sidebar {
        transform: translateX(-100%);
      }

      .sidebar.open {
        transform: translateX(0);
      }

      .main-content {
        margin-left: 0;
      }
    }

    /* Main content */
    .main-content {
      margin-left: 250px;
      padding: 40px;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      transition: margin 0.3s ease;
    }

    @media (max-width: 768px) {
      .main-content {
        margin-left: 0;
      }
    }

    /* Titel */
    .dashboard-title {
      text-align: center;
      margin-bottom: 20px;
      font-size: clamp(2rem, 6vw, 3.5rem);
      background: linear-gradient(45deg, #00ff88, #00aaff);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      text-shadow: 0 0 20px rgba(0, 255, 136, 0.5);
    }

    /* Welcome Text */
    .welcome-text {
      text-align: center;
      font-size: 1.5rem;
      margin-bottom: 40px;
      color: #00ff88;
      text-shadow: 0 0 10px rgba(0, 255, 136, 0.3);
    }

    /* Clock styles */
    .clock-container {
      display: flex;
      justify-content: center;
      margin-bottom: 40px;
    }

    .clock {
      width: 200px;
      height: 200px;
      background: rgba(30, 30, 47, 0.9);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2.5rem;
      color: #00ff88;
      box-shadow: 0 8px 30px rgba(0, 255, 136, 0.3);
      border: 3px solid #00ff88;
      text-shadow: 0 0 15px rgba(0, 255, 136, 0.5);
    }

    /* Footer */
    .footer {
      margin-top: 40px;
      padding: 15px;
      text-align: center;
      background: rgba(30, 30, 47, 0.9);
      backdrop-filter: blur(20px);
      width: 100%;
    }

    .footer p {
      color: #00ff88;
      margin: 0;
      font-size: 0.9rem;
      text-shadow: 0 0 5px rgba(0, 255, 136, 0.3);
    }
  </style>
</head>
<body>
  <!-- Sidebar Toggle Button -->
  <button id="sidebarToggle">
    <i class="fas fa-bars"></i>
  </button>

  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <header>systeembeheer Menu</header>
    <a href="systeembeheer-dashboard.php" class="<?= ($activePage == 'dashboard') ? 'active' : ''; ?>">
        <i class="fas fa-tachometer-alt"></i> <!-- Dashboard icon -->
        <span>Dashboard</span>
    </a>
    <a href="systeembeheer-docenten.php" class="<?= ($activePage == 'docenten') ? 'active' : ''; ?>">
        <i class="fas fa-chalkboard-teacher"></i> <!-- Docenten icon -->
        <span>Docenten</span>
    </a>
    <a href="systeembeheer-studenten.php" class="<?= ($activePage == 'studenten') ? 'active' : ''; ?>">
        <i class="fas fa-users"></i> <!-- Studenten icon -->
        <span>Studenten</span>
    </a>
    <a href="systeembeheer-systeembeheerder.php" class="<?= ($activePage == 'systeembeheerder') ? 'active' : ''; ?>">
        <i class="fas fa-tools"></i> <!-- Systeembeheerder icon -->
        <span>Systeembeheerder</span>
    </a>
    <a href="systeembeheer-directeur.php" class="<?= ($activePage == 'directeur') ? 'active' : ''; ?>">
        <i class="fas fa-user-tie"></i> <!-- Directeur icon -->
        <span>Directeur</span>
    </a>
    <a href="systeembeheer-Richtingcoordinator.php" class="<?= ($activePage == 'Richtingcoordinator') ? 'active' : ''; ?>">
        <i class="fas fa-user-cog"></i> <!-- Richtingcoordinator icon -->
        <span>Richtingcoordinator</span>
    </a>
    <a href="systeembeheer_od.php" class="<?= ($activePage == 'od') ? 'active' : ''; ?>">
        <i class="fas fa-user-shield"></i> <!-- Onderdirecteur icon -->
        <span>Onderdirecteur</span>
    </a>
    <a href="../login/logout.php">
        <i class="fas fa-sign-out-alt"></i> <!-- Logout icon -->
        <span>Log out</span>
    </a>
</div>

  <!-- Main Content -->
  <div class="main-content">
    <h1 class="dashboard-title">systeembeheer Dashboard</h1>
    <div class="welcome-text">Welcome, <?php echo htmlspecialchars($voornaam . ' ' . $naam); ?>!</div>

    <!-- Fysieke Klok -->
    <div class="clock-container">
      <div class="clock" id="clock"></div>
    </div>

    <div class="footer">
      <p>© 2099 Quantum Admin System</p>
    </div>
  </div>

  <script>
    // Sidebar toggle functionaliteit
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');

    sidebarToggle.addEventListener('click', () => {
      sidebar.classList.toggle('open');
    });

    // Sluit sidebar bij klikken buiten de sidebar
    document.addEventListener('click', (event) => {
      if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
        sidebar.classList.remove('open');
      }
    });

    // Klok functionaliteit
    function updateClock() {
      const now = new Date();
      const hours = String(now.getHours()).padStart(2, '0');
      const minutes = String(now.getMinutes()).padStart(2, '0');
      const seconds = String(now.getSeconds()).padStart(2, '0');
      document.getElementById('clock').textContent = `${hours}:${minutes}:${seconds}`;
    }
    setInterval(updateClock, 1000);
    updateClock(); // Initialiseer direct
  </script>
</body>
</html>