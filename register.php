
<!-- register.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <form action="register_action.php" method="post">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <select name="role">
            <option value="volunteer">Volunteer</option>
            <option value="admin">Admin</option>
        </select>
        <button type="submit">Register</button>
    </form>
</body>
</html>
