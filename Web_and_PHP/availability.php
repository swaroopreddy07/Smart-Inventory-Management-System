<?php
require 'config.php';

$selectedItem = isset($_GET['item']) ? $_GET['item'] : '';

$query = "SELECT 
    items.item,
    COALESCE(o.total_ordered, 0) AS total_ordered,
    COALESCE(c.total_consumption, 0) AS total_consumption,
    (COALESCE(o.total_ordered, 0) - COALESCE(c.total_consumption, 0)) AS availability
FROM (SELECT item FROM orders
      UNION
      SELECT item FROM consumption) items
LEFT JOIN (SELECT item, SUM(quantity) AS total_ordered FROM orders GROUP BY item) o 
      ON items.item = o.item
LEFT JOIN (SELECT item, SUM(consumption) AS total_consumption FROM consumption GROUP BY item) c 
      ON items.item = c.item";

if ($selectedItem !== '') {
    // Using HAVING after the query's SELECT to filter
    $query .= " HAVING items.item = '" . $conn->real_escape_string($selectedItem) . "'";
}

$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Availability Data</title>
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
      <h2>Availability Data</h2>
      <div class="user-profile"><i class="fas fa-user"></i></div>
    </div>
  </header>

  <main>
    <div class="content-container">
      <!-- Search Form -->
      <form class="search-form" method="get" action="availability.php">
        <select name="item">
          <option value="">Select Item</option>
          <option value="choclates1" <?php if($selectedItem=='choclates1') echo 'selected'; ?>>Choclates1</option>
          <option value="choclates2" <?php if($selectedItem=='choclates2') echo 'selected'; ?>>Choclates2</option>
        </select>
        <button type="submit">Enter</button>
      </form>

      <!-- Results Table -->
      <div class="results-box">
        <table>
          <thead>
            <tr>
              <th>Item</th>
              <th>Total Ordered</th>
              <th>Total Consumption</th>
              <th>Availability</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td><?php echo $row['item']; ?></td>
                  <td><?php echo $row['total_ordered']; ?></td>
                  <td><?php echo $row['total_consumption']; ?></td>
                  <td><?php echo $row['availability']; ?></td>
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
