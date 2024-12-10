<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавление станка</title>
    <style>
        /* Стили остаются без изменений */
    </style>
</head>
<body>
<style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
        }
        form {
            width: 100%;
            max-width: 600px;
            margin: auto;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="number"], select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn:hover {
            background: #0056b3;
        }
        .characteristics-group {
            margin-bottom: 20px;
        }
        .characteristics-row {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .remove-characteristic {
            background: #ff4d4d;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .remove-characteristic:hover {
            background: #cc0000;
        }
    </style>
<h2>Добавить новый станок</h2>

<form action="add_machine.php" method="post" enctype="multipart/form-data">
    <!-- Основная информация о станке -->
    <div class="form-group">
        <label for="model_name">Модель станка</label>
        <input type="text" id="model_name" name="model_name" required>
    </div>
    <div class="form-group">
        <label for="manufacturer">Производитель</label>
        <input type="text" id="manufacturer" name="manufacturer" required>
    </div>
    <div class="form-group">
        <label for="price">Цена</label>
        <input type="number" id="price" name="price" step="0.01" required>
    </div>
    <div class="form-group">
        <label for="stock_quantity">Количество на складе</label>
        <input type="number" id="stock_quantity" name="stock_quantity" required>
    </div>

    <!-- Блок для добавления характеристик -->
    <div class="form-group">
        <label>Характеристики</label>
        <div class="characteristics-group" id="characteristics-group">
            <div class="characteristics-row">
                <select name="characteristics[]" required>
                    <?php
                    // Подключение к базе данных
                    $conn = new mysqli('localhost', 'root', '', 'machines_db');
                    if ($conn->connect_error) {
                        die("Ошибка подключения: " . $conn->connect_error);
                    }
                    // Получение списка характеристик
                    $result = $conn->query("SELECT id, name FROM characteristics");
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['name']}</option>";
                    }
                    $conn->close();
                    ?>
                </select>
                <input type="text" name="values[]" placeholder="Введите значение" required>
                <button type="button" class="remove-characteristic">Удалить</button>
            </div>
        </div>
        <button type="button" id="add-characteristic" class="btn">Добавить характеристику</button>
    </div>

    <!-- Загрузка изображения -->
    <div class="form-group">
        <label for="image">Фотография станка</label>
        <input type="file" id="image" name="image" accept="image/*">
    </div>

    <button type="submit" class="btn">Сохранить станок</button>
</form>

<script>
    // JavaScript для добавления характеристик
    document.getElementById('add-characteristic').addEventListener('click', function() {
        const group = document.getElementById('characteristics-group');
        const row = document.createElement('div');
        row.classList.add('characteristics-row');
        row.innerHTML = `
            <select name="characteristics[]" required>
                <?php
                // Переменная $result уже была обработана выше
                $conn = new mysqli('localhost', 'root', '', 'machines_db');
                $result = $conn->query("SELECT id, name FROM characteristics");
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='{$row['id']}'>{$row['name']}</option>";
                }
                $conn->close();
                ?>
            </select>
            <input type="text" name="values[]" placeholder="Введите значение" required>
            <button type="button" class="remove-characteristic">Удалить</button>
        `;
        group.appendChild(row);
        row.querySelector('.remove-characteristic').addEventListener('click', function() {
            row.remove();
        });
    });

    document.querySelectorAll('.remove-characteristic').forEach(button => {
        button.addEventListener('click', function() {
            button.parentElement.remove();
        });
    });
</script>

</body>
</html>
