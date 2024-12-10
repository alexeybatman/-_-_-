<?php
// Подключение к базе данных
$conn = new mysqli('localhost', 'root', '', 'machines_db');
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Начало транзакции
$conn->begin_transaction();

try {
    // Получение параметров
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['status'];
    $admin_name = 'Администратор'; // В реальном сценарии - из сессии

    // Получение текущего статуса
    $stmt = $conn->prepare("SELECT status FROM orders WHERE order_id = ? FOR UPDATE");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $old_status = $order['status'];
    $stmt->close();

    // Обновление статуса заказа
    $update_stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $update_stmt->bind_param("si", $new_status, $order_id);
    $update_stmt->execute();
    $update_stmt->close();

    // Логирование изменения статуса
    $log_stmt = $conn->prepare("INSERT INTO order_status_log (order_id, old_status, new_status, changed_by) VALUES (?, ?, ?, ?)");
    $log_stmt->bind_param("isss", $order_id, $old_status, $new_status, $admin_name);
    $log_stmt->execute();
    $log_stmt->close();

    // Commit транзакции
    $conn->commit();

    // Перенаправление обратно на страницу управления заказами
    header("Location: admin_orders.php");
    exit();

} catch (Exception $e) {
    // Откат транзакции в случае ошибки
    $conn->rollback();
    die("Ошибка: " . $e->getMessage());
} finally {
    $conn->close();
}
?>
