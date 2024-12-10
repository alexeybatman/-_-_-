<?php
$conn = new mysqli('localhost', 'root', '', 'machines_db');
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Получение ID станка
$machine_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($machine_id <= 0) {
    die("Некорректный ID станка");
}

// Получение данных о станке
$sql = "SELECT m.* FROM machines m WHERE m.machine_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $machine_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Станок не найден");
}

$machine = $result->fetch_assoc();

// Получение характеристик станка
$char_sql = "SELECT mc.*, c.name as characteristic_name 
             FROM machine_characteristics mc 
             JOIN characteristics c ON mc.characteristic_id = c.id 
             WHERE mc.machine_id = ?";
$char_stmt = $conn->prepare($char_sql);
$char_stmt->bind_param("i", $machine_id);
$char_stmt->execute();
$char_result = $char_stmt->get_result();
$characteristics = [];
while ($char_row = $char_result->fetch_assoc()) {
    $characteristics[] = [
        'id' => $char_row['characteristic_id'],
        'name' => $char_row['characteristic_name'],
        'value' => $char_row['value']
    ];
}

// Получение всех доступных характеристик
$all_chars_sql = "SELECT * FROM characteristics";
$all_chars_result = $conn->query($all_chars_sql);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование станка</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background-color: #f4f4f4; 
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .characteristics-row {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }
        .remove-characteristic {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Редактирование станка</h1>
        <form action="update_machine.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="machine_id" value="<?= $machine_id ?>">
            
            <div class="form-group">
                <label for="model_name">Модель станка</label>
                <input type="text" id="model_name" name="model_name" 
                       value="<?= htmlspecialchars($machine['model_name']) ?>" required>
            </div>

            <div class="form-group">
                <label for="manufacturer">Производитель</label>
                <input type="text" id="manufacturer" name="manufacturer" 
                       value="<?= htmlspecialchars($machine['manufacturer']) ?>" required>
            </div>

            <div class="form-group">
                <label for="price">Цена</label>
                <input type="number" id="price" name="price" step="0.01" 
                       value="<?= $machine['price'] ?>" required>
            </div>

            <div class="form-group">
                <label for="stock_quantity">Количество на складе</label>
                <input type="number" id="stock_quantity" name="stock_quantity" 
                       value="<?= $machine['stock_quantity'] ?>" required>
            </div>

            <div class="form-group">
                <label>Характеристики</label>
                <div id="characteristics-group">
                    <?php foreach ($characteristics as $index => $char): ?>
                    <div class="characteristics-row">
                        <select name="characteristics[]">
                            <?php 
                            mysqli_data_seek($all_chars_result, 0); 
                            while ($row = $all_chars_result->fetch_assoc()): 
                            ?>
                            <option value="<?= $row['id'] ?>" 
                                <?= $row['id'] == $char['id'] ? 'selected' : '' ?>>
                                <?= $row['name'] ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                        <input type="text" name="values[]" 
                               value="<?= htmlspecialchars($char['value']) ?>" 
                               placeholder="Значение" required>
                        <button type="button" class="remove-characteristic">Удалить</button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="add-characteristic" class="btn">Добавить характеристику</button>
            </div>

            <div class="form-group">
                <label for="image">Изображение</label>
                <input type="file" id="image" name="image" accept="image/*">
                <?php if (!empty($machine['image'])): ?>
                <p>Текущее изображение: <?= basename($machine['image']) ?></p>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn">Сохранить изменения</button>
        </form>
    </div>

    <script>
    document.getElementById('add-characteristic').addEventListener('click', function() {
        const group = document.getElementById('characteristics-group');
        const row = document.createElement('div');
        row.classList.add('characteristics-row');
        row.innerHTML = `
            <select name="characteristics[]">
                <?php 
                mysqli_data_seek($all_chars_result, 0); 
                while ($row = $all_chars_result->fetch_assoc()): 
                ?>
                <option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
                <?php endwhile; ?>
            </select>
            <input type="text" name="values[]" placeholder="Значение" required>
            <button type="button" class="remove-characteristic">Удалить</button>
        `;
        group.appendChild(row);
        
        row.querySelector('.remove-characteristic').addEventListener('click', function() {
            row.remove();
        });
    });

    document.querySelectorAll('.remove-characteristic').forEach(button => {
        button.addEventListener('click', function() {
            this.parentElement.remove();
        });
    });
    </script>
</body>
</html>
<?php $conn->close(); ?>