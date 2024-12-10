<?php
$conn = new mysqli('localhost', 'root', '', 'machines_db');
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Validate machine ID
$machine_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($machine_id <= 0) {
    die("Некорректный ID станка");
}

// Get machine details with characteristics
$sql = "SELECT m.*, 
        (SELECT GROUP_CONCAT(CONCAT(c.name, ': ', mc.value) SEPARATOR '; ') 
         FROM machine_characteristics mc 
         JOIN characteristics c ON mc.characteristic_id = c.id
         WHERE mc.machine_id = m.machine_id) as characteristics 
        FROM machines m
        WHERE m.machine_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $machine_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if machine exists
if ($result->num_rows === 0) {
    die("Станок не найден");
}

$machine = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($machine['model_name']) ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="machine-detail">
            <div class="machine-detail-image">
                <img src="<?= htmlspecialchars($machine['image'] ?? 'placeholder.jpg') ?>" alt="<?= htmlspecialchars($machine['model_name']) ?>">
            </div>
            <div class="machine-detail-info">
                <h1><?= htmlspecialchars($machine['model_name']) ?></h1>
                <p>Производитель: <?= htmlspecialchars($machine['manufacturer']) ?></p>
                <p>Характеристики: <?= htmlspecialchars($machine['characteristics'] ?? 'Нет данных') ?></p>
                <p>Цена: <?= number_format($machine['price'], 2) ?> руб.</p>
                <p>В наличии: <?= $machine['stock_quantity'] ?> шт.</p>
                
                <a href="order.php?id=<?= $machine['machine_id'] ?>" class="order-btn">Заказать</a>
                <a href="index.php" class="back-btn">Вернуться в каталог</a>
            </div>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>