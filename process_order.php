<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$conn = new mysqli('localhost', 'root', '', 'machines_db');
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Validate input data
$required_fields = ['machine_id', 'name', 'phone', 'email', 'quantity'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        die("Поле $field не заполнено");
    }
}

$machine_id = intval($_POST['machine_id']);
$name = trim($_POST['name']);
$phone = trim($_POST['phone']);
$email = trim($_POST['email']);
$quantity = intval($_POST['quantity']);

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Некорректный email");
}

// Validate quantity
if ($quantity <= 0) {
    die("Количество должно быть положительным");
}

// Start transaction
$conn->begin_transaction();

try {
    // Retrieve machine details with row-level lock
    $machine_sql = "SELECT price, stock_quantity FROM machines WHERE machine_id = ? FOR UPDATE";
    $stmt = $conn->prepare($machine_sql);
    $stmt->bind_param("i", $machine_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Станок не найден");
    }
    
    $machine = $result->fetch_assoc();
    
    // Check stock availability
    if ($machine['stock_quantity'] < $quantity) {
        throw new Exception("Недостаточно станков на складе. В наличии: " . $machine['stock_quantity']);
    }

    // Calculate total price
    $total_price = $machine['price'] * $quantity;

    // Insert order
    $order_sql = "INSERT INTO orders (machine_id, customer_name, customer_phone, customer_email, quantity, total_price) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($order_sql);
    $stmt->bind_param("isssid", $machine_id, $name, $phone, $email, $quantity, $total_price);
    $stmt->execute();

    // Update machine stock
    $update_sql = "UPDATE machines SET stock_quantity = stock_quantity - ? WHERE machine_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ii", $quantity, $machine_id);
    $stmt->execute();

    // Commit transaction
    $conn->commit();

    // Redirect to success page
    header("Location: order_success.php");
    exit;

} catch (Exception $e) {
    // Rollback transaction
    $conn->rollback();
    
    // Log error (you might want to use a proper logging mechanism)
    error_log("Order processing error: " . $e->getMessage());
    
    // Display error to user
    die("Ошибка при оформлении заказа: " . $e->getMessage());
}

$conn->close();
?>
