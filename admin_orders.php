<?php

$conn = new mysqli('localhost', 'root', '', 'machines_db');
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Проверка на запрос экспорта
// Проверка на запрос экспорта
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    // Формируем имя файла с текущей датой
    $filename = 'orders_report_' . date('Y-m-d_H-i-s') . '.csv';
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Получаем данные из таблицы orders с JOIN
    $sql = "SELECT 
                o.order_id, 
                m.model_name AS machine, 
                o.customer_name, 
                o.customer_phone, 
                o.customer_email, 
                o.quantity, 
                o.total_price, 
                o.order_date, 
                o.status
            FROM orders o
            JOIN machines m ON o.machine_id = m.machine_id 
            ORDER BY o.order_date DESC";

    $result = $conn->query($sql);

    // Открываем вывод в формате CSV
    $output = fopen('php://output', 'w');

    // Записываем заголовки CSV
    fputcsv($output, ['Order ID', 'Machine', 'Customer Name', 'Phone', 'Email', 'Quantity', 'Total Price', 'Order Date', 'Status']);

    // Записываем строки с данными
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit();
}


// Получение списка заказов с информацией о станке
$sql = "SELECT o.*, m.model_name, m.manufacturer 
        FROM orders o 
        JOIN machines m ON o.machine_id = m.machine_id 
        ORDER BY o.order_date DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление заказами</title>
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
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px; 
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 10px; 
            text-align: left; 
        }
        th { 
            background-color: #f2f2f2; 
        }
        .status-select {
            width: 100%;
        }
        .status-new { background-color: #ffeb3b; }
        .status-processed { background-color: #4CAF50; color: white; }
        .status-completed { background-color: #2196F3; color: white; }
        .status-cancelled { background-color: #f44336; color: white; }
        .export-btn {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Управление заказами</h1>
        <a href="?export=csv" class="export-btn">Скачать SQL-отчет</a>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Станок</th>
                    <th>Клиент</th>
                    <th>Телефон</th>
                    <th>Email</th>
                    <th>Количество</th>
                    <th>Сумма</th>
                    <th>Дата заказа</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $order['order_id'] ?></td>
                    <td><?= htmlspecialchars($order['model_name']) ?></td>
                    <td><?= htmlspecialchars($order['customer_name']) ?></td>
                    <td><?= htmlspecialchars($order['customer_phone']) ?></td>
                    <td><?= htmlspecialchars($order['customer_email']) ?></td>
                    <td><?= $order['quantity'] ?></td>
                    <td><?= number_format($order['total_price'], 2) ?> руб.</td>
                    <td><?= date('d.m.Y H:i', strtotime($order['order_date'])) ?></td>
                    <td>
                        <form action="update_order_status.php" method="post">
                            <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                            <select name="status" class="status-select status-<?= $order['status'] ?>">
                                <option value="new" <?= $order['status'] == 'new' ? 'selected' : '' ?>>Новый</option>
                                <option value="processed" <?= $order['status'] == 'processed' ? 'selected' : '' ?>>Обработан</option>
                                <option value="completed" <?= $order['status'] == 'completed' ? 'selected' : '' ?>>Завершен</option>
                                <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Отменен</option>
                            </select>
                            <input type="submit" value="Обновить">
                        </form>
                    </td>
                    <td>
                        <a href="order_details.php?id=<?= $order['order_id'] ?>">Подробнее</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <script>
        // Динамическая смена цвета селекта в зависимости от статуса
        document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', function() {
                this.className = 'status-select status-' + this.value;
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>
