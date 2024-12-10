<?php
$conn = new mysqli('localhost', 'root', '', 'machines_db');
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

$sql = "SELECT m.*, 
        (SELECT GROUP_CONCAT(c.name, ': ', mc.value SEPARATOR '; ') 
         FROM machine_characteristics mc 
         JOIN characteristics c ON mc.characteristic_id = c.id
         WHERE mc.machine_id = m.machine_id) as characteristics 
        FROM machines m";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Магазин Станков</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Каталог Станков</h1>
        <div class="machine-grid">
            <?php while ($machine = $result->fetch_assoc()): ?>
                <div class="machine-card">
                    <a href="machine_detail.php?id=<?= $machine['machine_id'] ?>">
                        <img src="<?= htmlspecialchars($machine['image'] ?? 'placeholder.jpg') ?>" alt="<?= htmlspecialchars($machine['model_name']) ?>">
                        <h2><?= htmlspecialchars($machine['model_name']) ?></h2>
                    </a>
                    <p>Производитель: <?= htmlspecialchars($machine['manufacturer']) ?></p>
                    <div class="card-footer">
                        <span class="price"><?= number_format($machine['price'], 2) ?> руб.</span>
                        <a href="order.php?id=<?= $machine['machine_id'] ?>" class="order-btn">Заказать</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>