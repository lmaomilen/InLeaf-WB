<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION["admin"])) {
    die(json_encode(["status" => "error", "message" => "Access Denied"]));
}

$action = $_GET["action"] ?? null;


if ($action === "get_products") {
    $products = [];
    $result = $conn->query("SELECT * FROM products");
    while ($row = $result->fetch_assoc()) {
        $row["image"] = json_decode($row["image"], true);
        $row["main_image"] = $row["image"][0] ?? 'no-image.png';
        $products[] = $row;
    }
    header('Content-Type: application/json');
    echo json_encode(["status" => "success", "products" => $products]);
    exit;
}


if ($action === "add_product" && isset($_POST["name"], $_POST["description"], $_POST["price"])) {
    $name = $_POST["name"];
    $description = $_POST["description"];
    $price = $_POST["price"];
    $category = $_POST["category"] ?? 'Plants';

    if (!is_dir('uploads/')) {
        mkdir('uploads/', 0777, true);
    }

    $image_paths = [];

    if (!empty($_FILES["image"])) {
        $files = $_FILES["image"];

            
        if (!is_array($files["name"])) {
            $files = [
                "name" => [$files["name"]],
                "type" => [$files["type"]],
                "tmp_name" => [$files["tmp_name"]],
                "error" => [$files["error"]],
                "size" => [$files["size"]],
            ];
        }

        foreach ($files["tmp_name"] as $i => $tmp_name) {
            if ($files["error"][$i] !== UPLOAD_ERR_OK) continue;

            $file_type = mime_content_type($tmp_name);
            $file_size = $files["size"][$i];

            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($file_type, $allowed_types) || $file_size > 2 * 1024 * 1024) {
                continue;
            }

            $extension = pathinfo($files["name"][$i], PATHINFO_EXTENSION);
            $file_name = uniqid("img_", true) . '.' . strtolower($extension);
            $target_file = "uploads/" . $file_name;

            if (move_uploaded_file($tmp_name, $target_file)) {
                $image_paths[] = $file_name;
            }
        }
    }

    $image_json = json_encode($image_paths);

    $stmt = $conn->prepare("INSERT INTO products (name, description, price, category, image) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdss", $name, $description, $price, $category, $image_json);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Product added"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to add product"]);
    }
    exit;
}


if ($action === "delete_product" && isset($_POST["id"])) {
    $id = intval($_POST["id"]);
    $conn->query("DELETE FROM products WHERE id = $id");
    echo json_encode(["status" => "success", "message" => "Product deleted"]);
    exit;
}

if ($action === "delete_order" && isset($_POST["order_id"])) {
    $order_id = intval($_POST["order_id"]);

    $conn->begin_transaction();

    try {
        $conn->query("DELETE FROM order_items WHERE order_id = $order_id");
        $conn->query("DELETE FROM payments WHERE order_id = $order_id");
        $conn->query("DELETE FROM orders WHERE id = $order_id");

        $conn->commit();
        echo json_encode(["status" => "success", "message" => "Order deleted"]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["status" => "error", "message" => "DB error: " . $e->getMessage()]);
    }
    exit;
}


if ($action === "edit_product" && isset($_POST["id"])) {
    $id = intval($_POST["id"]);
    $name = $conn->real_escape_string($_POST["name"]);
    $price = floatval($_POST["price"]);
    $description = trim($_POST["description"]);
    $description = str_replace(["\r", "\n"], "\n", $description); 
    $description = stripslashes($description);

    $existing_images = $_POST["existing_images"] ?? [];
    if (!is_array($existing_images)) {
        $existing_images = [];
    }

    $new_image_paths = [];

    if (!empty($_FILES["image"]["name"][0])) {
        $files = $_FILES["image"];
        $imageCount = is_array($files["name"]) ? count($files["name"]) : 1;

        for ($i = 0; $i < $imageCount; $i++) {
            $file_tmp = is_array($files["tmp_name"]) ? $files["tmp_name"][$i] : $files["tmp_name"];
            $file_name = is_array($files["name"]) ? $files["name"][$i] : $files["name"];
            $file_type = mime_content_type($file_tmp);
            $file_size = is_array($files["size"]) ? $files["size"][$i] : $files["size"];

            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($file_type, $allowed_types) || $file_size > 2 * 1024 * 1024) {
                continue;
            }

            $extension = pathinfo($file_name, PATHINFO_EXTENSION);
            $unique_name = uniqid("img_", true) . '.' . strtolower($extension);
            $target_path = "uploads/" . $unique_name;

            if (move_uploaded_file($file_tmp, $target_path)) {
                $new_image_paths[] = $unique_name;
            }
        }
    }

    $final_images = array_merge($existing_images, $new_image_paths);
    $images_json = json_encode($final_images);

    $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, description = ?, image = ? WHERE id = ?");
    $stmt->bind_param("sdssi", $name, $price, $description, $images_json, $id);

    header('Content-Type: application/json');
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Product updated"]);
    } else {
        echo json_encode(["success" => false, "message" => "Update failed"]);
    }

    exit;
}
if ($action === "delete_image" && isset($_POST["product_id"], $_POST["image"])) {
    $productId = intval($_POST["product_id"]);
    $imageToDelete = basename($_POST["image"]);

    $result = $conn->query("SELECT image FROM products WHERE id = $productId");
    if ($result && $row = $result->fetch_assoc()) {
        $images = json_decode($row['image'], true) ?? [];

        if (($key = array_search($imageToDelete, $images)) !== false) {
            unset($images[$key]);
            $images = array_values($images);
            $jsonImages = json_encode($images);

            $stmt = $conn->prepare("UPDATE products SET image = ? WHERE id = ?");
            $stmt->bind_param("si", $jsonImages, $productId);
            $stmt->execute();

            $filePath = __DIR__ . "/uploads/" . $imageToDelete;
            if (file_exists($filePath)) unlink($filePath);

            echo json_encode(["status" => "success", "message" => "Image deleted"]);
            exit;
        }
    }

    echo json_encode(["status" => "error", "message" => "Image not found or already deleted"]);
    exit;
}



$conn->close();