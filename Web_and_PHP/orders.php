<?php
require 'config.php';

$selectedItem = isset($_GET['item']) ? $_GET['item'] : '';
$selectedDate = isset($_GET['date']) ? $_GET['date'] : '';

$query = "SELECT * FROM orders";
$conditions = [];
if ($selectedItem !== '') {
    $conditions[] = "item = '" . $conn->real_escape_string($selectedItem) . "'";
}
if ($selectedDate !== '') {
    $conditions[] = "DATE(nodatetime) = '" . $conn->real_escape_string($selectedDate) . "'";
}
if (count($conditions) > 0) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}
$query .= " ORDER BY nodatetime DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Ordered Data</title>
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
      <h2>Ordered Data</h2>
      <div class="user-profile"><i class="fas fa-user"></i></div>
    </div>
  </header>

  <main>
    <div class="content-container">
      <!-- Search Form -->
      <form class="search-form" method="get" action="orders.php">
        <select name="item">
          <option value="">Select Item</option>
          <option value="choclates1" <?php if($selectedItem=='choclates1') echo 'selected'; ?>>Choclates1</option>
          <option value="choclates2" <?php if($selectedItem=='choclates2') echo 'selected'; ?>>Choclates2</option>
        </select>
        <input type="date" name="date" value="<?php echo htmlspecialchars($selectedDate); ?>" />
        <button type="submit">Enter</button>
      </form>

      <!-- Results Table -->
      <div class="results-box">
        <table>
          <thead>
            <tr>
              <th>SR</th>
              <th>Date/Time</th>
              <th>Item</th>
              <th>Quantity</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td><?php echo $row['sr']; ?></td>
                  <td><?php echo $row['nodatetime']; ?></td>
                  <td><?php echo $row['item']; ?></td>
                  <td><?php echo $row['quantity']; ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="4" style="text-align:center;">No records found.</td>
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
