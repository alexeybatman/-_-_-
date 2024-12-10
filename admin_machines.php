<?php
session_start();
// TODO: Добавить аутентификацию администратора
// Если не авторизован, редирект на страницу входа

$conn = new mysqli('localhost', 'root', '', 'machines_db');
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Получение списка станков
$sql = "SELECT m.*, 
    (SELECT GROUP_CONCAT(c.name, ': ', mc.value SEPARATOR '; ') 
     FROM machine_characteristics mc 
     JOIN characteristics c ON mc.characteristic_id = c.id 
     WHERE mc.machine_id = m.machine_id) as characteristics 
    FROM machines m 
    ORDER BY m.machine_id";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Административная панель - Управление станками</title>
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
        .actions {
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 3px;
        }
        .btn-edit {
            background-color: #4CAF50;
            color: white;
        }
        .btn-delete {
            background-color: #f44336;
            color: white;
        }
        .btn-add {
            background-color: #2196F3;
            color: white;
            display: inline-block;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Управление станками</h1>
        <a href="admin.php" class="btn btn-add">Добавить новый станок</a>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Модель</th>
                    <th>Производитель</th>
                    <th>Цена</th>
                    <th>Характеристики</th>
                    <th>Количество</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($machine = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $machine['machine_id'] ?></td>
                    <td><?= htmlspecialchars($machine['model_name']) ?></td>
                    <td><?= htmlspecialchars($machine['manufacturer']) ?></td>
                    <td><?= number_format($machine['price'], 2) ?> руб.</td>
                    <td><?= htmlspecialchars($machine['characteristics'] ?? 'Нет данных') ?></td>
                    <td><?= $machine['stock_quantity'] ?></td>
                    <td>
                        <div class="actions">
                            <a href="edit_machine.php?id=<?= $machine['machine_id'] ?>" class="btn btn-edit">Изменить</a>
                            <a href="delete_machine.php?id=<?= $machine['machine_id'] ?>" 
                               class="btn btn-delete" 
                               onclick="return confirm('Вы уверены, что хотите удалить этот станок?')">Удалить</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
<?php $conn->close(); ?>
