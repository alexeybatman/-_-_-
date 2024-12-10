<?php
// Подключение к базе данных
$conn = new mysqli('localhost', 'root', '', 'machines_db');
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Получение данных из формы
$model_name = $_POST['model_name'];
$manufacturer = $_POST['manufacturer'];
$price = $_POST['price'];
$stock_quantity = $_POST['stock_quantity'];
$characteristics = $_POST['characteristics'];
$values = $_POST['values'];

// Обработка загрузки изображения
$image = $_FILES['image'];
$image_path = "";  // Путь к изображению на сервере

if ($image['error'] === UPLOAD_ERR_OK) {
    // Генерируем уникальное имя для файла
    $image_name = uniqid() . '_' . basename($image['name']);
    $target_directory = 'images/';
    $target_file = $target_directory . $image_name;

    // Перемещаем загруженный файл в папку images
    if (move_uploaded_file($image['tmp_name'], $target_file)) {
        $image_path = $target_file;  // Путь к изображению, который будет храниться в БД
    } else {
        echo "Ошибка при загрузке изображения.";
        exit;
    }
}

// Вставка станка в базу данных
$sql = "INSERT INTO machines (model_name, manufacturer, price, stock_quantity, image) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssdis", $model_name, $manufacturer, $price, $stock_quantity, $image_path);
$stmt->execute();
$machine_id = $stmt->insert_id;
$stmt->close();

// Вставка характеристик
$sql = "INSERT INTO machine_characteristics (machine_id, characteristic_id, value) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
foreach ($characteristics as $index => $characteristic_id) {
    $value = $values[$index];
    $stmt->bind_param("iis", $machine_id, $characteristic_id, $value);
    $stmt->execute();
}
$stmt->close();

// Закрытие соединения
$conn->close();

// Перенаправление или вывод сообщения
header("Location: success.html");
exit;
?>
