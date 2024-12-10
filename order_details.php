<?php
$conn = new mysqli('localhost', 'root', '', 'machines_db');
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

$order_id = intval($_GET['id']);

// Запрос на получение информации о заказе
$order_sql = "SELECT o.*, m.model_name, m.manufacturer 
              FROM orders o 
              JOIN machines m ON o.machine_id = m.machine_id 
              WHERE o.order_id = ?";
$stmt = $conn->prepare($order_sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_result = $stmt->get_result();
$order = $order_result->fetch_assoc();

// Запрос на получение истории статусов
$log_sql = "SELECT * FROM order_status_log WHERE order_id = ? ORDER BY change_time DESC";
$log_stmt = $conn->prepare($log_sql);
$log_stmt->bind_param("i", $order_id);
$log_stmt->execute();
$log_result = $log_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Детали заказа #<?= $order_id ?></title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background-color: #f4f4f4; 
        }
        .container { 
            background-color: white; 
            padding: 20px; 
            border-radius: 5px; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
            max-width: 800px; 
            margin: 0 auto;
        }
        .order-info, .status-history {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Детали заказа #<?= $order_id ?></h1>
        
        <div class="order-info">
            <h2>Информация о заказе</h2>
            <table>
                <tr>
                    <th>Станок</th>
                    <td><?= htmlspecialchars($order['model_name']) ?></td>
                </tr>
                <tr>
                    <th>Производитель</th>
                    <td><?= htmlspecialchars($order['manufacturer']) ?></td>
                </tr>
                <tr>
                    <th>Клиент</th>
                    <td><?= htmlspecialchars($order['customer_name']) ?></td>
                </tr>
                <tr>
                    <th>Телефон</th>
                    <td><?= htmlspecialchars($order['customer_phone']) ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?= htmlspecialchars($order['customer_email']) ?></td>
                </tr>
                <tr>
                    <th>Количество</th>
                    <td><?= $order['quantity'] ?></td>
                </tr>
                <tr>
                    <th>Сумма</th>
                    <td><?= number_format($order['total_price'], 2) ?> руб.</td>
                </tr>
                <tr>
                    <th>Дата заказа</th>
                    <td><?= date('d.m.Y H:i', strtotime($order['order_date'])) ?></td>
                </tr>
                <tr>
                    <th>Текущий статус</th>
                    <td><?= $order['status'] ?></td>
                </tr>
            </table>
        </div>

        <div class="status-history">
            <h2>История изменения статусов</h2>
            <table>
                <thead>
                    <tr>
                        <th>Старый статус</th>
                        <th>Новый статус</th>
                        <th>Кем изменено</th>
                        <th>Время изменения</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($log = $log_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $log['old_status'] ?></td>
                        <td><?= $log['new_status'] ?></td>
                        <td><?= htmlspecialchars($log['changed_by']) ?></td>
                        <td><?= date('d.m.Y H:i', strtotime($log['change_time'])) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <a href="admin_orders.php">Вернуться к списку заказов</a>
    </div>
</body>
</html>
<?php 
$stmt->close();
$log_stmt->close();
$conn->close(); 
?>
