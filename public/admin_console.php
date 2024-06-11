<?php
// Ensure the user is logged in and has admin permissions
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect user to login page if no login data is set
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: /login.html");
    exit();
}

// Check for admin permissions
if (!$_SESSION["admin"]) {
    echo "Access denied: User does not have admin permission";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Console</title>
    <link rel="stylesheet" href="style/admin_console.css">
</head>
<body>
<div class="centertable">
    <header>
        <div class="overlay">
            <h1>Admin Konsole</h1>
        </div>
    </header>
    <br>
    <br>
    <div class="table-container">
        <table>
            <tr class="tableheader">
                <th>User</th>
                <th>Delete</th>
                <th>Admin</th>
            </tr>
            <tr>
                <td>Udo Erdmann</td>
                <td>
                    <form action="/deleteuser.php" method="post">
                        <button class="delete btn" type="submit">X</button>
                    </form>
                </td>
                <td>
                    <form action="/makeadmin.php" method="post">
                        <button class="admin btn" type="submit">Admin</button>
                    </form>
                </td>
            </tr>
        </table>
    </div>
</div>
</body>
</html>