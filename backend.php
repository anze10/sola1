<?php
// Povezava na PostgreSQL
$conn = pg_connect("host=aws-0-eu-central-1.pooler.supabase.com port=6543 dbname=postgres user=postgres.lxxgpjzntcdejaqtozbf password=SCNM!jesola");

if (!$conn) {
    die("Napaka pri povezavi s PostgreSQL: " . pg_last_error());
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
    $sql = "INSERT INTO users (username, password, mail, role) VALUES ($1, $2, $3, $4)";
    $result = pg_query_params($conn, $sql, [$username, $password, $mail, $role]);

    // Provide feedback to user
    if ($result) {
        echo "Uporabnik '$username' uspešno dodan!";
    } else {
        echo "Napaka pri dodajanju uporabnika: " . pg_last_error($conn);
    }
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
    $sql = "SELECT role FROM users WHERE username = $1 AND password = $2";
    $result = pg_query_params($conn, $sql, [$username, $password]);

    if ($result && pg_num_rows($result) > 0) {
        // Fetch the role
        $row = pg_fetch_assoc($result);
        $role = $row['role'];

        // Display the dashboard based on role
        if ($role == 'admin') {
            echo "<h1>Welcome Admin</h1>";
            echo "<h2>All Users:</h2>";

            // Fetch all users for admin
            $users_sql = "SELECT username, mail FROM users";
            $users_result = pg_query($conn, $users_sql);

            while ($user = pg_fetch_assoc($users_result)) {
                echo "<p>User: " . $user['username'] . " - Email: " . $user['mail'] . "</p>";
            }
        } else {
            echo "<h1>Welcome $username</h1>";
            echo "<h2>Your Information:</h2>";

            // Fetch user data for regular user
            $user_info_sql = "SELECT username, mail FROM users WHERE username = $1";
            $user_info_result = pg_query_params($conn, $user_info_sql, [$username]);

            $user_info = pg_fetch_assoc($user_info_result);
            echo "<p>Username: " . $user_info['username'] . " - Email: " . $user_info['mail'] . "</p>";
        }
    } else {
        echo "Invalid credentials!";
    }
}

// Close the connection
pg_close($conn);
?>