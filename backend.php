<?php
// Povezava na MySQL
$conn = mysqli_connect("158.180.230.254", "username", "Kaks123!@", "sola", 3306);

if (!$conn) {
    die("Napaka pri povezavi s MySQL: " . mysqli_connect_error());
}

// Handle user registration
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $mail = trim($_POST['mail']);
    $role = isset($_POST['role']) ? trim($_POST['role']) : 'user'; // Default role is 'user'

    if (empty($username) || empty($password) || empty($mail)) {
        die("Vsa obvezna polja (username, password, mail) morajo biti izpolnjena!");
    }

    if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
        die("Neveljaven format e-poštnega naslova!");
    }

    $stmt = $conn->prepare("INSERT INTO users (username, password, mail, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $password, $mail, $role);

    if ($stmt->execute()) {
        echo "<div class='success'>
                Uporabnik '$username' uspešno dodan! 
                <br><br>
                <button onclick=\"window.location.href='/';\">Kliknite tukaj za vrnitev na glavno stran</button>
              </div>";
    } else {
        echo "Napaka pri dodajanju uporabnika: " . $stmt->error;
    }

    $stmt->close();
}

// Handle user login and dashboard
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        die("Vsa obvezna polja morajo biti izpolnjena!");
    }

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

            // Fetch all users
            $users_result = $conn->query("SELECT id, username, mail, role FROM users");

            while ($user = $users_result->fetch_assoc()) {
                echo "<p>
                        User: {$user['username']} - Email: {$user['mail']} - Role: {$user['role']}
                        <br>
                        <form method='POST'>
                            <input type='hidden' name='user_id' value='{$user['id']}'>
                            <input type='text' name='new_mail' placeholder='New Email' value='{$user['mail']}'>
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
        echo "Invalid credentials!";
    }

    $stmt->close();
}

// Handle user updates and deletions
if ($_SERVER["REQUEST_METHOD"] === "POST" && (isset($_POST['update_user']) || isset($_POST['delete_user']))) {
    if (isset($_POST['update_user'])) {
        $user_id = $_POST['user_id'];
        $new_mail = trim($_POST['new_mail']);
        $new_role = $_POST['new_role'];

        if (!filter_var($new_mail, FILTER_VALIDATE_EMAIL)) {
            die("Neveljaven format e-poštnega naslova!");
        }

        $stmt = $conn->prepare("UPDATE users SET mail = ?, role = ? WHERE id = ?");
        $stmt->bind_param("ssi", $new_mail, $new_role, $user_id);

        if ($stmt->execute()) {
            echo "User updated successfully!";
        } else {
            echo "Error updating user: " . $stmt->error;
        }

        $stmt->close();
    }

    if (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];

        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            echo "User deleted successfully!";
        } else {
            echo "Error deleting user: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Close the connection
$conn->close();
?>
