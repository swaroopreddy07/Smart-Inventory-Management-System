<?php
require 'config.php';

$selectedDate = isset($_GET['date']) ? $_GET['date'] : '';

$query = "SELECT * FROM security_alerts";
if ($selectedDate !== '') {
    $query .= " WHERE DATE(datatime) = '" . $conn->real_escape_string($selectedDate) . "'";
}
$query .= " ORDER BY datatime DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Security Alerts</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Font Awesome -->
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
      <h2>Security Alerts</h2>
      <div class="user-profile"><i class="fas fa-user"></i></div>
    </div>
  </header>

  <main>
    <div class="content-container">
      <!-- Search Form -->
      <form class="search-form" method="get" action="security_alert.php">
        <input type="date" name="date" value="<?php echo htmlspecialchars($selectedDate); ?>" />
        <button type="submit">Enter</button>
      </form>

      <!-- Results Table -->
      <div class="results-box">
        <table>
          <thead>
            <tr>
              <th>Serial No</th>
              <th>Date/Time</th>
              <th>Alert</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td><?php echo $row['serial_no']; ?></td>
                  <td><?php echo $row['datatime']; ?></td>
                  <td><?php echo $row['security_alert']; ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="3" style="text-align:center;">No records found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <a href="index.php" class="button">Back to Home</a>
    </div>
  </main>

  <footer>
    BATCH-A TEAM-6
  </footer>
</body>
</html>
