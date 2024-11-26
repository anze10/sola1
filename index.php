<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register and Login</title>
  <meta name="description" content="User registration and login page">
  <link rel="icon" href="/favicon.ico">
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    .container {
      max-width: 800px;
      margin: 0 auto;
      padding: 20px;
      text-align: center;
    }

    h1 {
      color: #333;
      margin-bottom: 30px;
    }

    form {
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    label {
      margin-bottom: 10px;
      text-align: left;
      width: 100%;
    }

    input,
    select {
      width: 100%;
      padding: 10px;
      margin-bottom: 20px;
      border-radius: 5px;
      border: 1px solid #ccc;
      font-size: 16px;
    }

    input[type="submit"] {
      padding: 10px 20px;
      background-color: #4CAF50;
      color: white;
      border: none;
      border-radius: 5px;
      font-size: 16px;
      cursor: pointer;
      margin-bottom: 40px;
    }

    hr {
      margin: 40px 0;
      border: 1px solid #ddd;
      width: 100%;
    }

    .success {
      color: green;
      font-weight: bold;
    }

    .error {
      color: red;
      font-weight: bold;
    }

    p {
      color: #555;
    }
  </style>
</head>

<body>
  <?php
  // Initialize variables for messages
  $connectionMessage = '';
  $actionMessage = '';

  // Database connection
  $conn = pg_connect("host=aws-0-eu-central-1.pooler.supabase.com port=6543 dbname=postgres user=postgres.lxxgpjzntcdejaqtozbf password=SCNM!jesola");
  if ($conn) {
    $connectionMessage = "<div class='success'>Connected to the database successfully.</div>";
  } else {
    $connectionMessage = "<div class='error'>Connection failed: " . pg_last_error() . "</div>";
  }

  
 
  ?>

  <div class="container">
    <!-- Connection Status -->
    <?php echo $connectionMessage; ?>

    <!-- Action Feedback -->
    <?php echo $actionMessage; ?>

    <!-- Registration Form -->
    <h1>Register New User</h1>
    <form id="registrationForm" action="" method="POST">
      <label for="username">Username:</label>
      <input type="text" id="username" name="username" required>

      <label for="password">Password:</label>
      <input type="password" id="password" name="password" required>

      <label for="mail">Email:</label>
      <input type="email" id="mail" name="mail" required>

      <label for="role">Role:</label>
      <select id="role" name="role">
        <option value="">Select Role (optional)</option>
        <option value="admin">Admin</option>
        <option value="user">User</option>
      </select>

      <input type="submit" name="register" value="Register">
    </form>

    <hr>

    <!-- Login Form -->
    <h1>Login</h1>
    <form id="loginForm" action="" method="POST">
      <label for="username">Username:</label>
      <input type="text" id="username" name="username" required>

      <label for="password">Password:</label>
      <input type="password" id="password" name="password" required>

      <input type="submit" name="login" value="Login">
    </form>
  </div>
</body>

</html>