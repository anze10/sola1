<?php
// Povezava na MySQL
$conn = mysqli_connect("158.180.230.254", "username", "Kaks123!@", "sola", 3306);

if (!$conn) {
    die("Napaka pri povezavi s MySQL: " . mysqli_connect_error());
}

// Add reusable "Return Home" button
function addReturnHomeButton() {
    echo "<button onclick=\"window.location.href='/sola';\" class='home-button'>Return Home</button>";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        .home-button {
            display: inline-block;
            padding: 10px 15px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .home-button:hover {
            background-color: #0056b3;
        }
        .success, .error {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .success {
            background-color: #dff0d8;
            color: #3c763d;
            border-color: #d6e9c6;
        }
        .error {
            background-color: #f2dede;
            color: #a94442;
            border-color: #ebccd1;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php addReturnHomeButton(); ?>

        <?php
        // Handle user registration
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['register'])) {
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);
            $mail = trim($_POST['mail']);
            $role = isset($_POST['role']) ? trim($_POST['role']) : 'user'; // Default role is 'user'

            if (empty($username) || empty($password) || empty($mail)) {
                echo "<div class='error'>Vsa obvezna polja morajo biti izpolnjena!</div>";
            } elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                echo "<div class='error'>Neveljaven format e-poštnega naslova!</div>";
            } else {
                $stmt = $conn->prepare("INSERT INTO users (username, password, mail, role) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $username, $password, $mail, $role);

                if ($stmt->execute()) {
                    echo "<div class='success'>Uporabnik '$username' uspešno dodan!</div>";
                } else {
                    echo "<div class='error'>Napaka pri dodajanju uporabnika: " . $stmt->error . "</div>";
                }

                $stmt->close();
            }
        }

        // Handle user login and dashboard
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);

            if (empty($username) || empty($password)) {
                echo "<div class='error'>Vsa obvezna polja morajo biti izpolnjena!</div>";
            } else {
                $stmt = $conn->prepare("SELECT id, role FROM users WHERE username = ? AND password = ?");
                $stmt->bind_param("ss", $username, $password);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result && $result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $role = $row['role'];
                    $user_id = $row['id'];

                    if ($role == 'super_admin') {
                        echo "<h1>Welcome Super Admin</h1>";
                        echo "<h2>Manage Users:</h2>";

                        $users_result = $conn->query("SELECT id, username, mail, role, password FROM users");

                        while ($user = $users_result->fetch_assoc()) {
                            echo "<p>
                                    User: {$user['username']} - Email: {$user['mail']} - Role: {$user['role']}
                                    <br>
                                    <form method='POST'>
                                        <input type='hidden' name='user_id' value='{$user['id']}'>
                                        <input type='text' name='new_username' value='{$user['username']}'>
                                        <input type='text' name='new_password' value='{$user['password']}'>
                                        <input type='text' name='new_mail' value='{$user['mail']}'>
                                        <select name='new_role'>
                                            <option value='user' " . ($user['role'] == 'user' ? 'selected' : '') . ">User</option>
                                            <option value='admin' " . ($user['role'] == 'admin' ? 'selected' : '') . ">Admin</option>
                                            <option value='super_admin' " . ($user['role'] == 'super_admin' ? 'selected' : '') . ">Super Admin</option>
                                        </select>
                                        <button type='submit' name='update_user'>Update</button>
                                        <button type='submit' name='delete_user' onclick=\"return confirm('Are you sure you want to delete this user?');\">Delete</button>
                                    </form>
                                  </p>";
                        }
                    } elseif ($role == 'admin') {
                        echo "<h1>Welcome Admin</h1>";
                        echo "<h2>All Users:</h2>";

                        $users_result = $conn->query("SELECT username, mail FROM users");

                        while ($user = $users_result->fetch_assoc()) {
                            echo "<p>User: {$user['username']} - Email: {$user['mail']}</p>";
                        }
                    } else {
                        echo "<h1>Welcome $username</h1>";
                        echo "<h2>Your Information:</h2>";

                        $stmt = $conn->prepare("SELECT username, mail FROM users WHERE id = ?");
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $user_info_result = $stmt->get_result();
                        $user_info = $user_info_result->fetch_assoc();

                        echo "<p>Username: {$user_info['username']} - Email: {$user_info['mail']}</p>";
                    }
                } else {
                    echo "<div class='error'>Invalid credentials!</div>";
                }

                $stmt->close();
            }
        }
        ?>

    </div>
</body>
</html>
