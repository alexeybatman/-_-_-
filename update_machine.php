<?php
// Подключение к базе данных
$conn = new mysqli('localhost', 'root', '', 'machines_db');
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Начало транзакции для целостности данных
$conn->begin_transaction();

try {
    // Получение данных из формы
    $machine_id = intval($_POST['machine_id']);
    $model_name = $_POST['model_name'];
    $manufacturer = $_POST['manufacturer'];
    $price = floatval($_POST['price']);
    $stock_quantity = intval($_POST['stock_quantity']);

    // Обработка загрузки изображения
    $image_path = '';
    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image'];
        $image_name = uniqid() . '_' . basename($image['name']);
        $target_directory = 'images/';
        $target_file = $target_directory . $image_name;

        if (move_uploaded_file($image['tmp_name'], $target_file)) {
            $image_path = $target_file;
        } else {
            throw new Exception("Ошибка при загрузке изображения");
        }
    }

    // Обновление основной информации о станке
    $update_sql = "UPDATE machines SET 
        model_name = ?, 
        manufacturer = ?, 
        price = ?, 
        stock_quantity = ? 
        " . (!empty($image_path) ? ", image = ?" : "") . "
        WHERE machine_id = ?";

    $stmt = $conn->prepare($update_sql);
    
    if (!empty($image_path)) {
        $stmt->bind_param("ssdisi", $model_name, $manufacturer, $price, $stock_quantity, $image_path, $machine_id);
    } else {
        $stmt->bind_param("ssdis", $model_name, $manufacturer, $price, $stock_quantity, $machine_id);
    }
    
    $stmt->execute();
    $stmt->close();

    // Удаление существующих характеристик
    // Удаление существующих характеристик
    $delete_chars_sql = "DELETE FROM machine_characteristics WHERE machine_id = ?";
    $delete_stmt = $conn->prepare($delete_chars_sql);
    $delete_stmt->bind_param("i", $machine_id);
    $delete_stmt->execute();
    $delete_stmt->close();

    // Вставка новых характеристик
    if (isset($_POST['characteristics']) && isset($_POST['values'])) {
        $insert_char_sql = "INSERT INTO machine_characteristics (machine_id, characteristic_id, value) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_char_sql);

        $characteristics = $_POST['characteristics'];
        $values = $_POST['values'];

        for ($i = 0; $i < count($characteristics); $i++) {
            $char_id = intval($characteristics[$i]);
            $value = $values[$i];

            $insert_stmt->bind_param("iis", $machine_id, $char_id, $value);
            $insert_stmt->execute();
        }

        $insert_stmt->close();
    }

    // Фиксация транзакции
    $conn->commit();
    
    // Перенаправление на страницу списка станков
    header("Location: index.php");
    exit();

} catch (Exception $e) {
    // Откат транзакции в случае ошибки
    $conn->rollback();
    
    // Вывод сообщения об ошибке
    die("Ошибка: " . $e->getMessage());
} finally {
    // Закрытие соединения с базой данных
    $conn->close();
}