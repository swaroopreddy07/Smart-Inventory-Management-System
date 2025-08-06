<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Auto Order Management System</title>

  <!-- Font Awesome for Icons -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
  />

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap"
    rel="stylesheet"
  />

  <!-- Main CSS -->
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <header>
    <div class="header-container">
      <div class="inventory-logo"><i class="fas fa-boxes"></i></div>
      <h1>Auto Order Management System</h1>
      <div class="user-profile"><i class="fas fa-user"></i></div>
    </div>
  </header>

  <main>
    <div class="content-container">
      <div class="nav-container">
        <a href="consumption.php" class="button">CONSUMPTION</a>
        <a href="orders.php" class="button">ORDERED</a>
        <a href="availability.php" class="button">AVAILABILITY</a>
        <a href="temperature_alert.php" class="button">TEMPERATURE ALERT</a>
        <a href="security_alert.php" class="button">SECURITY ALERT</a>
        <a href="graphs.php" class="button">GRAPHS</a>
      </div>
    </div>
  </main>

  <footer style="text-align: center;display:flex; margin-top: auto;">
    BATCH-A TEAM-6
  </footer>
</body>
</html>
