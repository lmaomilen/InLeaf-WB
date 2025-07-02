<?php
session_start();
require_once('../db.php');

header('Content-Type: application/json; charset=UTF-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$action = $_GET["action"] ?? null;
$product_id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;

if (!$action) {
    echo json_encode(["status" => "error", "message" => "No action specified"]);
    exit;
}


if (in_array($action, ['add', 'remove']) && $product_id > 0) {
    $check = $conn->prepare("SELECT id FROM products WHERE id = ?");
    $check->bind_param("i", $product_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows == 0) {
        echo json_encode(["status" => "error", "message" => "Product not found"]);
        exit;
    }
    $check->close();
}

if ($action === "add") {
    if ($product_id <= 0) {
        echo json_encode(["status" => "error", "message" => "Invalid product ID"]);
        exit;
    }

    if (isset($_SESSION["user_id"])) {
        $user_id = $_SESSION["user_id"];
        $stmt = $conn->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $stmt->close();
    } else {
        $_SESSION["wishlist"][$product_id] = true;
    }

    echo json_encode(["status" => "success", "message" => "Added to favorites!"]);
    exit;
}
if ($action === "remove") {
    if ($product_id <= 0) {
        echo json_encode(["status" => "error", "message" => "Invalid product ID"]);
        exit;
    }

    if (isset($_SESSION["user_id"])) {
        $user_id = $_SESSION["user_id"];
        $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $stmt->close();
    } else {
        unset($_SESSION["wishlist"][$product_id]);
    }

    echo json_encode(["status" => "success", "message" => "Removed from favorites!"]);
    exit;
}

// Показать избранное
if ($action === "view") {
    $items = [];

    if (isset($_SESSION["user_id"])) {
        $user_id = $_SESSION["user_id"];
        $stmt = $conn->prepare("SELECT id, name, price, image FROM products WHERE id IN (SELECT product_id FROM wishlist WHERE user_id = ?)");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }

        $stmt->close();
    } else {
        if (!empty($_SESSION["wishlist"])) {
            foreach (array_keys($_SESSION["wishlist"]) as $pid) {
                $stmt = $conn->prepare("SELECT id, name, price, image FROM products WHERE id = ?");
                $stmt->bind_param("i", $pid);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($product = $result->fetch_assoc()) {
                    $items[] = $product;
                }

                $stmt->close();
            }
        }
    }

    echo json_encode(["status" => "success", "wishlist" => $items]);
    exit;
}

echo json_encode(["status" => "error", "message" => "Invalid action"]);
exit;
