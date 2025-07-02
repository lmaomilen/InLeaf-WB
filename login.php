<?php
session_start();
require_once('db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST["login"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT id, username, password, is_admin, is_blocked FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $login, $login);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        if ($user["is_blocked"]) {
            $error = "Your account has been blocked. Contact support.";
        } elseif (password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["admin"] = (bool)$user["is_admin"];

            header("Location: " . ($user["is_admin"] ? "admin/admin.php" : "index.php"));
            exit();
        } else {
            $error = "Wrong login or password!";
        }
    } else {
        $error = "Wrong login or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Login | inleaf</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
</head>
<body class="bg-[#1A1A23] min-h-screen flex items-center justify-center">

  <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md">
    <h2 class="text-3xl font-bold text-center text-indigo-600 mb-6">Welcome Back</h2>

    <?php if (isset($error)): ?>
      <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4 text-sm">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="login.php" class="space-y-5">
      <input 
        type="text" 
        name="login" 
        placeholder="Username or Email" 
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
      <button 
        type="submit" 
        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-lg font-semibold transition"
      >
        Login
      </button>
    </form>

    <p class="text-center text-sm text-gray-600 mt-4">
      Don't have an account?
      <a href="register.php" class="text-indigo-500 hover:underline font-medium">Register now</a>
    </p>
  </div>

</body>
</html>
