<?php
$conn = new mysqli('localhost', 'root', '', 'machines_db');
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

$machine_id = intval($_GET['id']);
$sql = "SELECT m.*, 
        (SELECT GROUP_CONCAT(c.name, ': ', mc.value SEPARATOR '; ') 
         FROM machine_characteristics mc 
         JOIN characteristics c ON mc.characteristic_id = c.id
         WHERE mc.machine_id = m.machine_id) as characteristics 
        FROM machines m
        WHERE m.machine_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $machine_id);
$stmt->execute();
$result = $stmt->get_result();
$machine = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Заказ станка</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Оформление заказа</h1>
        <div class="order-details">
            <h2><?= htmlspecialchars($machine['model_name']) ?></h2>
            <img src="<?= htmlspecialchars($machine['image'] ?? 'placeholder.jpg') ?>" alt="<?= htmlspecialchars($machine['model_name']) ?>">
            <p>Характеристики: <?= htmlspecialchars($machine['characteristics'] ?? 'Нет данных') ?></p>
            <p>Цена: <?= number_format($machine['price'], 2) ?> руб.</p>
            <p>В наличии: <?= $machine['stock_quantity'] ?> шт.</p>
            
            <form action="process_order.php" method="post">
                <input type="hidden" name="machine_id" value="<?= $machine_id ?>">
                <label for="name">Ваше имя:</label>
                <input type="text" id="name" name="name" required>
                
                <label for="phone">Телефон:</label>
                <input type="tel" id="phone" name="phone" required>
                
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
                
                <label for="quantity">Количество:</label>
                <input type="number" id="quantity" name="quantity" min="1" max="<?= $machine['stock_quantity'] ?>" required>
                
                <button type="submit" class="order-btn">Подтвердить заказ</button>
            </form>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>