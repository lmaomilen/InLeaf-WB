<?php
session_start();
require_once('db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $repassword = $_POST["repassword"];

    if ($password !== $repassword) {
        $error = "Passwords do not match!";
    } else {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $passwordHash);

        if ($stmt->execute()) {
            $_SESSION["user_id"] = $stmt->insert_id;
            $_SESSION["username"] = $username;
            header("Location: index.php");
            exit();
        } else {
            $error = "Error: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Register | inleaf</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
</head>
<body class="bg-[#1A1A23] min-h-screen flex items-center justify-center">

  <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md">
    <h2 class="text-3xl font-bold text-center text-indigo-600 mb-6">Create Your Account</h2>

    <?php if (isset($error)): ?>
      <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4 text-sm">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="register.php" class="space-y-5">
      <input 
        type="text" 
        name="username" 
        placeholder="Username" 
        required 
        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400"
      >
      <input 
        type="email" 
        name="email" 
        placeholder="Email" 
        required 
        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400"
      >
      <input 
        type="password" 
        name="password" 
        placeholder="Password" 
        required 
        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400"
      >
      <input 
        type="password" 
        name="repassword" 
        placeholder="Repeat Password" 
        required 
        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400"
      >
      <button 
        type="submit" 
        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-lg font-semibold transition"
      >
        Register
      </button>
    </form>

    <p class="text-center text-sm text-gray-600 mt-4">
      Already have an account?
      <a href="login.php" class="text-indigo-500 hover:underline font-medium">Login</a>
    </p>
  </div>

</body>
</html>

