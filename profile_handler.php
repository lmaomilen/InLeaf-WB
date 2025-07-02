<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('../db.php');

header('Content-Type: application/json');

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["status" => "error", "message" => "You are not logged in!"]);
    exit;
}

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["avatar"])) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

    $mimeType = $_FILES["avatar"]["type"];

    if (!in_array($mimeType, $allowedTypes)) {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid file type!",
            "debug" => [
                "received_type" => $mimeType
            ]
        ]);
        exit;
    }


    $ext = pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION);
    $avatarName = uniqid("avatar_") . "." . $ext;
    $uploadDir = realpath(__DIR__ . '/../uploads');
    $avatarPath = 'uploads/' . $avatarName; 

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

       
    $uploadDir = realpath(__DIR__ . '/../uploads');
    if (!$uploadDir) {
        echo json_encode(["status" => "error", "message" => "Upload dir doesn't exist"]);
        exit;
    }
    if (!is_writable($uploadDir)) {
        echo json_encode(["status" => "error", "message" => "Upload dir not writable"]);
        exit;
    }

    $avatarFullPath = $uploadDir . '/' . $avatarName;
    $avatarRelativePath = 'uploads/' . $avatarName;


    if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $avatarFullPath)) {
        $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        $stmt->bind_param("si", $avatarRelativePath, $user_id);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "avatar" => "/progetto/" . $avatarRelativePath . "?t=" . time()]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database update failed"]);
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "move_uploaded_file failed",
            "debug" => [
                "tmp_name" => $_FILES["avatar"]["tmp_name"],
                "target" => $avatarFullPath
            ]
        ]);
    }
    exit;

}
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["username"], $_POST["email"])) {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = isset($_POST["new_password"]) && $_POST["new_password"] !== '' ? password_hash($_POST["new_password"], PASSWORD_DEFAULT) : null;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Invalid email format"]);
        exit;
    }

    if ($password) {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
        $stmt->bind_param("sssi", $username, $email, $password, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $username, $email, $user_id);
    }

    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update user info"]);
    }
    exit;
}


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["street"], $_POST["city"], $_POST["postal_code"], $_POST["country"])) {
    $stmt = $conn->prepare("INSERT INTO addresses (user_id, street, city, postal_code, country) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $_POST["street"], $_POST["city"], $_POST["postal_code"], $_POST["country"]);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to add address"]);
    }
    exit;
}


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_address_id"])) {
    $address_id = intval($_POST["delete_address_id"]);

    $stmt = $conn->prepare("DELETE FROM addresses WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $address_id, $user_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete address"]);
    }
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_order_id"])) {
    $order_id = intval($_POST["delete_order_id"]);

    $conn->begin_transaction();
    try {

        $stmt = $conn->prepare("DELETE FROM payments WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->close();


        $stmt = $conn->prepare("DELETE FROM orders WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $order_id, $user_id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        echo json_encode(["status" => "success"]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["status" => "error", "message" => "Failed to delete order"]);
    }
    exit;
}


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_address_id"])) {
    $id = intval($_POST["update_address_id"]);

    $stmt = $conn->prepare("UPDATE addresses SET street = ?, city = ?, postal_code = ?, country = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ssssii", $_POST["street"], $_POST["city"], $_POST["postal_code"], $_POST["country"], $id, $user_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update address"]);
    }
    exit;
}


echo json_encode(["status" => "error", "message" => "Invalid request"]);
?>
