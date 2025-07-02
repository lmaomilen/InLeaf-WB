<?php
session_start();

$target_dir = "../uploads/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

// Проверка, действительно ли это изображение
if (isset($_POST["submit"])) {
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if ($check === false) {
        echo "Файл не является изображением.";
        $uploadOk = 0;
    }
}

// Проверка: существует ли файл
if (file_exists($target_file)) {
    echo "Файл уже существует.";
    $uploadOk = 0;
}

// Проверка размера
if ($_FILES["fileToUpload"]["size"] > 2 * 1024 * 1024) {
    echo "Файл слишком большой (макс. 2MB).";
    $uploadOk = 0;
}

// Разрешённые форматы
$allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
if (!in_array($imageFileType, $allowed_types)) {
    echo "Допустимы только JPG, JPEG, PNG и GIF.";
    $uploadOk = 0;
}

// Если всё ок — сохраняем
if ($uploadOk) {
    $new_name = uniqid("img_", true) . "." . $imageFileType;
    $final_path = $target_dir . $new_name;

    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $final_path)) {
        echo "Файл успешно загружен: " . $new_name;
        // Если хочешь сохранить в БД — делаем тут INSERT
    } else {
        echo "Ошибка при загрузке файла.";
    }
} else {
    echo "Файл не загружен.";
}
?>
