<?php
require 'config.php';

$query = "SELECT DATE(record_date) as date, item, SUM(consumption) as total_consumption 
          FROM consumption 
          GROUP BY DATE(record_date), item 
          ORDER BY date";
$result = $conn->query($query);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Graphs</title>
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

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <header>
    <div class="header-container">
      <div class="inventory-logo"><i class="fas fa-boxes"></i></div>
      <h2>Consumption Graph</h2>
      <div class="user-profile"><i class="fas fa-user"></i></div>
    </div>
  </header>

  <main>
    <div class="content-container">
      <canvas id="consumptionChart" width="600" height="400"></canvas>
      <a href="index.php" class="button">Back to Home</a>
    </div>
  </main>

  <footer>
    BATCH-A TEAM-6
  </footer>

  <script>
    const rawData = <?php echo json_encode($data); ?>;
    const dates = [];
    const dataByItem = {};

    rawData.forEach((row) => {
      if (!dates.includes(row.date)) {
        dates.push(row.date);
      }
      if (!dataByItem[row.item]) {
        dataByItem[row.item] = {};
      }
      dataByItem[row.item][row.date] = parseFloat(row.total_consumption);
    });

    // Create datasets for each item
    const datasets = [];
    const colors = ["rgba(41, 128, 185, 0.5)", "rgba(46, 204, 113, 0.5)"];
    let index = 0;
    for (const item in dataByItem) {
      const consumptionData = [];
      dates.forEach((date) => {
        consumptionData.push(dataByItem[item][date] || 0);
      });
      datasets.push({
        label: item,
        data: consumptionData,
        backgroundColor: colors[index % colors.length],
        borderColor: colors[index % colors.length].replace("0.5", "1"),
        borderWidth: 1,
      });
      index++;
    }

    const ctx = document.getElementById("consumptionChart").getContext("2d");
    new Chart(ctx, {
      type: "bar",
      data: {
        labels: dates,
        datasets: datasets,
      },
      options: {
        scales: {
          y: {
            beginAtZero: true,
          },
        },
      },
    });
  </script>
</body>
</html>
