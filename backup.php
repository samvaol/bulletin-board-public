<?php
include "config.php";
session_start();

if (isset($_POST["login"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Replace with your desired username and password
    if ($username === "xxx" && $password === "xxx") {
        $_SESSION["logged_in"] = true;
    } else {
        echo "<p>Invalid username or password.</p>";
    }
}

if (isset($_POST["logout"])) {
    unset($_SESSION["logged_in"]);
}

// Check if backup request is made
if (isset($_POST['backup']) && isset($_SESSION["logged_in"])) {
    $backup_count = 0;
    $new_backup_table = '';

    // Get list of tables with the prefix
    $tables = $conn->query("SHOW TABLES LIKE 'posts_backup_%'");
    if ($tables->num_rows > 0) {
        $backup_count = $tables->num_rows;
    }
    $new_backup_table = "posts_backup_" . ($backup_count + 1);

    // Create a new backup table
    $conn->query("CREATE TABLE " . $new_backup_table . " LIKE posts");
    $conn->query("INSERT " . $new_backup_table . " SELECT * FROM posts");

    echo "<p>Backup created successfully.</p>";
}

function list_backups() {
    global $conn;
    $backups = $conn->query("SHOW TABLES LIKE 'posts_backup_%'");

    if ($backups->num_rows > 0) {
        echo "<ul>";
        while ($row = $backups->fetch_row()) {
            $backup_table = $row[0];
            echo "<li>" . htmlspecialchars($backup_table) . " - <a href='backup.php?restore=" . urlencode($backup_table) . "'>Restore</a> - <a href='backup.php?delete=" . urlencode($backup_table) . "'>Delete</a> - <a href='backup.php?download=" . urlencode($backup_table) . "'>Download</a> - <a href='backup.php?visualize=" . urlencode($backup_table) . "'>Visualize</a></li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No backups found.</p>";
    }

    $conn->close();
}

if (isset($_GET["restore"]) && isset($_SESSION["logged_in"])) {
    $backup_table = $_GET["restore"];
    $conn->query("TRUNCATE posts");
    $conn->query("INSERT posts SELECT * FROM " . $backup_table);
    echo "<p>Backup restored successfully.</p>";
}

if (isset($_GET["delete"]) && isset($_SESSION["logged_in"])) {
    $backup_table = $_GET["delete"];
    $conn->query("DROP TABLE " . $backup_table);
    echo "<p>Backup deleted successfully.</p>";
}

if (isset($_GET["download"]) && isset($_SESSION["logged_in"])) {
    $backup_table = $_GET["download"];
    $cmd = "mysqldump -u " . DB_USER . " -p" . DB_PASS . " " . DB_NAME . " " . $backup_table . " > " . $backup_table . ".sql";
    exec($cmd, $output, $result);

    if ($result === 0) {
        header("Content-Disposition: attachment; filename=" . $backup_table . ".sql");
        header("Content-Type: application/sql");
        readfile($backup_table . ".sql");
        unlink($backup_table . ".sql");
        exit;
    } else {
        echo "<p>Error creating backup file for download.</p>";
    }
}

if (isset($_GET["visualize"]) && isset($_SESSION["logged_in"])) {
    $backup_table = $_GET["visualize"];
    $result = $conn->query("SELECT * FROM " . $backup_table);

    echo "<h2>Contents of " . htmlspecialchars($backup_table) . "</h2>";
    echo "<table border='1'>";
    echo "<tr>";

    $columnNames = $conn->query("SHOW COLUMNS FROM " . $backup_table);
    while ($column = $columnNames->fetch_assoc()) {
        echo "<th>" . htmlspecialchars($column["Field"]) . "</th>";
    }

    echo "</tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $cell) {
            echo "<td>" . htmlspecialchars($cell) . "</td>";
        }
        echo "</tr>";
    }

    echo "</table>";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup Management</title>
<style>
body {
    font-family: Arial, sans-serif;
    font-size: 16px;
    line-height: 1.5;
    color: #333;
    background-color: #f9f9f9;
    transition: background-color 0.3s ease-in-out, color 0.3s ease-in-out;
}

.container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

h1 {
    font-size: 28px;
    margin-bottom: 20px;
}

h2 {
    font-size: 24px;
    margin-bottom: 20px;
}

form {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

input[type="text"], input[type="password"] {
    padding: 10px;
    font-size: 14px;
}

input[type="submit"] {
    padding: 10px;
    font-size: 14px;
    cursor: pointer;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

th, td {
    padding: 10px;
    text-align: left;
    border: 1px solid #ccc;
}

th {
    background-color: #f2f2f2;
}

tr:nth-child(even) {
    background-color: #f9f9f9;
}

a {
    color: #333;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}


@media (prefers-color-scheme: dark) {
    body {
        color: #f9f9f9;
        background-color: #333;
    }

    th {
        background-color: #444;
    }

    tr:nth-child(even) {
        background-color: #555;
    }

    a {
        color: #f9f9f9;
    }
}
</style>
</head>
<body>
<?php if (!isset($_SESSION["logged_in"])): ?>
    <form action="backup.php" method="post">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>
        <br>
        <input type="submit" name="login" value="Log in">
    </form>
<?php else: ?>
    <h1>Backup Management</h1>
    <form action="backup.php" method="post">
        <input type="submit" name="backup" value="Create Backup">
        <input type="submit" name="logout" value="Logout">
    </form>
    <h2>Available Backups</h2>
    <?php list_backups(); ?>
<?php endif; ?>
</body>
</html>
