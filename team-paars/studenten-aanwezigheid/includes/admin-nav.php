<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    /* Reset en basisstijlen */
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
    }

    /* Sidebar */
    .sidebar {
      width: 250px;
      height: 100vh;
      background: rgba(30, 30, 47, 0.9);
      backdrop-filter: blur(20px);
      position: fixed;
      top: 0;
      left: 0;
      z-index: 1000;
      transition: transform 0.3s ease;
    }

    .sidebar header {
      text-align: center;
      font-size: 1.5rem;
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
      padding: 10px;
      border-radius: 10px;
      transition: background 0.3s ease, transform 0.3s ease;
    }

    .sidebar a:hover {
      background: rgba(0, 255, 136, 0.1);
      transform: translateX(5px);
    }

    .sidebar a.active {
      background: rgba(0, 255, 136, 0.2);
      color: #00ff88;
      box-shadow: 0 0 10px rgba(0, 255, 136, 0.5);
    }

    .sidebar a i {
      font-size: 1.2rem;
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
      padding: 20px;
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
      margin: 1rem 0;
      font-size: clamp(1.5rem, 5vw, 2.5rem);
      background: linear-gradient(45deg, #00ff88, #00aaff);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      text-shadow: 0 0 20px rgba(0, 255, 136, 0.3);
    }

    /* Chart Container */
    .chart-container {
      width: 100%;
      max-width: 1200px;
      margin: 1rem auto;
      background: rgba(30, 30, 47, 0.9);
      backdrop-filter: blur(20px);
      border-radius: 15px;
      padding: 15px;
      box-shadow: 0 8px 32px rgba(0, 255, 136, 0.1);
    }

    /* Kaarten Grid */
    .cards-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 15px;
      max-width: 1200px;
      margin: 1rem auto;
    }

    @media (max-width: 600px) {
      .cards-grid {
        grid-template-columns: 1fr;
      }
    }

    .card {
      background: linear-gradient(145deg, rgba(30,30,47,0.9), rgba(45,45,70,0.9));
      backdrop-filter: blur(20px);
      border-radius: 15px;
      padding: 15px;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 24px rgba(0, 255, 136, 0.2);
    }

    .card h2 {
      color: #00ff88;
      margin-bottom: 10px;
      font-size: 1.2rem;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .card p {
      color: rgba(255,255,255,0.9);
      font-size: 1rem;
    }

    /* Footer */
    .footer {
      margin-top: 2rem;
      padding: 15px;
      text-align: center;
      background: rgba(30, 30, 47, 0.9);
      backdrop-filter: blur(20px);
    }

    .footer p {
      color: #00ff88;
      margin: 0;
      font-size: 0.9rem;
    }
  </style>

  </style>
</head>
<body>
  <!-- Sidebar Toggle Knop -->
  <button id="sidebarToggle">
    <i class="fas fa-bars"></i>
  </button>

  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
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
    <a href="admin-systeembeheerder.php" class="<?= ($activePage == 'systeembeheerder') ? 'active' : ''; ?>">
      <i class="fas fa-chalkboard"></i>
      <span>Systeembeheerder</span>
    </a>
    <a href="admin-directeur.php" class="<?= ($activePage == 'directeur') ? 'active' : ''; ?>">
            <i class="fas fa-chalkboard-teacher"></i>
            <span>directeur</span>
    </a>

    <a href="admin-Richtingcoordinator.php" class="<?= ($activePage == 'Richtingcoordinator') ? 'active' : ''; ?>">
            <i class="fas fa-chalkboard-teacher"></i>
            <span>Richtingcoordinator</span>
    </a>

    
    <a href="admin_od.php" class="<?= ($activePage == 'od') ? 'active' : ''; ?>">
      <i class="fas fa-book-open"></i>
      <span>Onderdirecteur</span>
    </a>
  
    </a>
   
    <a href="../login/logout.php">
      <i class="fas fa-sign-out-alt"></i>
      <span>Log out</span>
    </a>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <h1 class="dashboard-title">Admin Panel</h1>

    <!-- Chart -->
    <div class="chart-container">
      <canvas id="performanceChart"></canvas>
    </div>

    <!-- Kaarten Grid -->
    <div class="cards-grid">
      <div class="card">
        <h2><i class="fas fa-users"></i> Gebruikers</h2>
        <p>1,240 actieve accounts</p>
      </div>
      <div class="card">
        <h2><i class="fas fa-chart-line"></i> Prestaties</h2>
        <p>89% succesratio</p>
      </div>
      <div class="card">
        <h2><i class="fas fa-bell"></i> Meldingen</h2>
        <p>12 nieuwe updates</p>
      </div>
    </div>

    <div class="footer">
      <p>&copy; 2099 Quantum Admin System</p>
    </div>
  </div>

  <script>
    // Sidebar toggle functionaliteit
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');

    sidebarToggle.addEventListener('click', () => {
      sidebar.classList.toggle('open');
    });

    // Sluit sidebar bij klik buiten
    document.addEventListener('click', (event) => {
      if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
        sidebar.classList.remove('open');
      }
    });

    // Geanimeerde Line Chart
    const ctx = document.getElementById('performanceChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(0,255,136,0.4)');
    gradient.addColorStop(1, 'rgba(0,255,136,0)');

    new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
        datasets: [{
          label: 'Prestaties',
          data: [65, 78, 82, 75, 88 , 95],
          borderColor: '#00ff88',
          backgroundColor: gradient,
          borderWidth: 3,
          pointRadius: 5,
          pointBackgroundColor: '#00ff88',
          tension: 0.4,
          fill: true
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          x: {
            grid: {
              color: 'rgba(255,255,255,0.1)'
            },
            ticks: {
              color: '#fff'
            }
          },
          y: {
            grid: {
              color: 'rgba(255,255,255,0.1)'
            },
            ticks: {
              color: '#fff'
            }
          }
        }
      }
    });
  </script>
</body>
</html>