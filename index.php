<?php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Convo Messenger</title>
</head>
<body>
<form action="authenticate.php" method="post">
    <label>
        Username:
        <input type="text" name="name" placeholder="Your Name" required>
    </label>
    <label>
        Password:
        <input type="password" name="password" placeholder="Your Password" required>
    </label>
    <button type="submit" name="action" value="login">Login</button>
    <button type="submit" name="action" value="register">Register</button>
</form>
</body>
</html>
?>