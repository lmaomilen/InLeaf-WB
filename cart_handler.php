<?php
session_start();
require_once('../db.php');

$action = $_GET["action"] ?? ($_POST["action"] ?? null);
$product_id = $_GET["id"] ?? ($_POST["id"] ?? null);

if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

if (!$action) {
    die(json_encode(["status" => "error", "message" => "No action specified"]));
}


if ($action === "add" && is_numeric($product_id)) {
    $product_id = intval($product_id);
    $quantity = isset($_GET["quantity"]) ? max(1, intval($_GET["quantity"])) : 1;

    if (isset($_SESSION["user_id"])) {
        $user_id = $_SESSION["user_id"];
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) 
                                VALUES (?, ?, ?) 
                                ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)");
        $stmt->bind_param("iii", $user_id, $product_id, $quantity);
        $stmt->execute();
        $stmt->close();
    } else {
        $_SESSION["cart"][$product_id] = ($_SESSION["cart"][$product_id] ?? 0) + $quantity;
    }

    echo json_encode(["status" => "success", "message" => "Added to cart!"]);
}



if ($action === "remove" && is_numeric($product_id)) {
    $product_id = intval($product_id);

    if (isset($_SESSION["user_id"])) {
        $user_id = $_SESSION["user_id"];
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $stmt->close();
    } else {
        unset($_SESSION["cart"][$product_id]);
    }

    echo json_encode(["status" => "success", "message" => "Removed from cart!"]);
}



if ($action === "increase" && is_numeric($product_id)) {
    $product_id = intval($product_id);

    if (isset($_SESSION["user_id"])) {
        $user_id = $_SESSION["user_id"];
        $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $stmt->close();
    } else {
        $_SESSION["cart"][$product_id] = ($_SESSION["cart"][$product_id] ?? 1) + 1;
    }

    echo json_encode(["status" => "success", "message" => "Quantity increased!"]);
}

// Уменьшить количество
if ($action === "decrease" && is_numeric($product_id)) {
    $product_id = intval($product_id);

    if (isset($_SESSION["user_id"])) {
        $user_id = $_SESSION["user_id"];

        $stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if ($row) {
            if ($row["quantity"] > 1) {
                $stmt = $conn->prepare("UPDATE cart SET quantity = quantity - 1 WHERE user_id = ? AND product_id = ?");
                $stmt->bind_param("ii", $user_id, $product_id);
                $stmt->execute();
                $stmt->close();
            } else {
                $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                $stmt->bind_param("ii", $user_id, $product_id);
                $stmt->execute();
                $stmt->close();
            }
        }

    } else {
        if (isset($_SESSION["cart"][$product_id])) {
            if ($_SESSION["cart"][$product_id] > 1) {
                $_SESSION["cart"][$product_id]--;
            } else {
                unset($_SESSION["cart"][$product_id]);
            }
        }
    }

    echo json_encode(["status" => "success", "message" => "Quantity updated!"]);
}


// Просмотр корзины
if ($action === "view") {
    $cart_items = [];

    if (isset($_SESSION["user_id"])) {
        $user_id = $_SESSION["user_id"];
        $result = $conn->query("SELECT products.id, products.name, products.price,products.image, cart.quantity 
                                FROM cart 
                                JOIN products ON cart.product_id = products.id 
                                WHERE cart.user_id = '$user_id'");
        while ($row = $result->fetch_assoc()) {
            $cart_items[] = $row;
        }
    } else {
        if (isset($_SESSION["cart"])) {
            foreach ($_SESSION["cart"] as $product_id => $quantity) {
                $result = $conn->query("SELECT id, name, price,image FROM products WHERE id = '$product_id'");
                if ($product = $result->fetch_assoc()) {
                    $product["quantity"] = $quantity;
                    $cart_items[] = $product;
                }
            }
        }
    }

    echo json_encode(["status" => "success", "cart" => $cart_items]);
}

$conn->close();
?>
