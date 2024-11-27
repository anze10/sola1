<?php
// Povezava na MySQL
$conn = mysqli_connect("158.180.230.254", "username", "Kaks123!@", "sola", 3306);

if (!$conn) {
    die("Napaka pri povezavi s MySQL: " . mysqli_connect_error());
}

// Handle user registration
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['register'])) {
    // Get and clean the input data for registration
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $mail = trim($_POST['mail']);
    $role = isset($_POST['role']) ? trim($_POST['role']) : 'user'; // Default to 'user' role

    // Validate inputs
    if (empty($username) || empty($password) || empty($mail)) {
        die("Vsa obvezna polja (username, password, mail) morajo biti izpolnjena!");
    }

    if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
        die("Neveljaven format e-poštnega naslova!");
    }

    // Prepare and execute SQL query to insert user
    $stmt = $conn->prepare("INSERT INTO users (username, password, mail, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $password, $mail, $role);

    if ($stmt->execute()) {
        echo "Uporabnik '$username' uspešno dodan!";
    } else {
        echo "Napaka pri dodajanju uporabnika: " . $stmt->error;
    }

    $stmt->close();
}

// Handle user login and dashboard
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
    // Get username and password for login
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validate inputs
    if (empty($username) || empty($password)) {
        die("Vsa obvezna polja morajo biti izpolnjena!");
    }

    // Check if user exists and get their role
    $stmt = $conn->prepare("SELECT role FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        // Fetch the role
        $row = $result->fetch_assoc();
        $role = $row['role'];

        // Display the dashboard based on role
        if ($role == 'admin') {
            echo "<h1>Welcome Admin</h1>";
            echo "<h2>All Users:</h2>";

            // Fetch all users for admin
            $users_sql = "SELECT username, mail FROM users";
            $users_result = $conn->query($users_sql);

            while ($user = $users_result->fetch_assoc()) {
                echo "<p>User: " . $user['username'] . " - Email: " . $user['mail'] . "</p>";
            }
        } else {
            echo "<h1>Welcome $username</h1>";
            echo "<h2>Your Information:</h2>";

            // Fetch user data for regular user
            $stmt = $conn->prepare("SELECT username, mail FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $user_info_result = $stmt->get_result();

            $user_info = $user_info_result->fetch_assoc();
            echo "<p>Username: " . $user_info['username'] . " - Email: " . $user_info['mail'] . "</p>";
        }
    } else {
        echo "Invalid credentials!";
    }

    $stmt->close();
}

// Close the connection
$conn->close();
?>
