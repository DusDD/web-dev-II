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
    <form action="logout.php" method="post">
            <button class="logoutbutton" type="submit">Logout</button>
    </form>
    <br>
    <div class="table-container">
        <table id="users-table">
            <tr class="tableheader">
                <th>ID</th>
                <th>Username</th>
                <th>Delete</th>
                <th>Admin</th>
            </tr>
            <!-- Users will be inserted by JS here -->
        </table>
    </div>
</div>

<!-- Data export -->
<div class="centertable">
    <h3>Data Export:</h3>
    <button id="dataExportButton">Export</button>
    <br>
    <label for="dataExportArea">Data:</label>
    <textarea id="dataExportArea" readonly></textarea>
</div>

<!-- Load JQuery Libraries before admin_console.js! -->
<script src="scripts/jquery-3.7.1.js"></script>
<script src="scripts/jquery-ui.js"></script>

<!-- Load the javascript functionality for this site -->
<script src="scripts/admin_console.js"></script>
</body>
</html>