<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - PohjisBoard</title>
    <style>

    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.1);
    }

    .modal-content {
        background-color: #7d8ba1;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 50%;
        text-align: center;
    }

    .get-out {
        cursor: pointer;
        background-color: #f1f1f1;
        color: black;
        padding: 12px 24px;
        border: none;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 16px;
        margin: 4px 2px;
        transition-duration: 0.4s;
        cursor: pointer;
    }

    .get-out:hover {
        background-color: lightgray;
        color: black;
    }

    body.modal-open {
        overflow: hidden;
    }

    body.modal-open::before {
        content: "";
        display: block;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        backdrop-filter: blur(50px);
        z-index: -1;
    }
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        h1 {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 1rem;
        }

        #board {
            display: flex;
            flex-direction: column;
            align-items: center;
            max-width: 800px;
            margin: 0 auto;
            padding: 1rem;
        }

        .post {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 1rem;
            margin: 1rem;
            width: 100%;
        }

        small {
            color: #888;
        }

        form {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 1rem;
            margin: 1rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
        }

        textarea {
            width: 100%;
            margin-bottom: 1rem;
        }

        input[type="file"] {
            margin-bottom: 1rem;
        }

        input[type="submit"] {
            display: block;
            width: 100%;
            background-color: #333;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 0.75rem;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #444;
        }

        img {
            max-width: 100%;
            height: auto;
        }

        .post small {
           display: block;
           font-size: 0.8rem;
           margin-top: 0.5rem;
       	   text-align: right;
    	}

    @media (prefers-color-scheme: dark) {
        body {
            background-color: #222;
            color: #f4f4f4;
        }

        h1 {
            background-color: #444;
        }

        #board .post,
        form {
            background-color: #333;
            border-color: #444;
        }

        small {
            color: #ccc;
        }

        input[type="submit"] {
            background-color: #444;
            border: 1px solid #555;
        }

        input[type="submit"]:hover {
            background-color: #555;
        }
	
	input#nickname {
	   background-color: #00000;
	   border: none;
	   border-radius: 10px;
	} 
	
    }

    </style>
</head>
<body>

<?php
session_start();

if (isset($_GET['logout'])) {
    unset($_SESSION['admin']);
    header("Location: admin.php");
    exit();
}

if (isset($_POST['username']) && isset($_POST['password'])) {
    include "config.php";

    $username = $_POST['username'];
    $password = md5($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ? AND password_hash = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $_SESSION['admin'] = $username;
    } else {
        echo "Invalid username or password.";
    }

    $conn->close();
}

if (isset($_SESSION['admin'])) {
    include "config.php";

    if (isset($_GET['delete'])) {
        $id = $_GET['delete'];
        $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}
?>

<form action="upload.php" method="post" enctype="multipart/form-data">
    <label for="content">Viesti:</label>
    <label for="nickname">Nimike (optional):</label>
    <input type="text" name="nickname" id="nickname">
    <br>
    <textarea name="content" id="content" rows="4" cols="50"></textarea>
    <br>
    <label for="image">Kuva:</label>
    <input type="file" name="image" id="image">
    <br>
    <input type="submit" name="submit" value="Post">
</form>

<div id="board">
    <?php
        include "config.php";

        $sql = "SELECT * FROM posts ORDER BY created_at DESC";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='post'>";
                if (!empty($row["nickname"])) {
                    echo "<strong>" . htmlspecialchars($row["nickname"]) . ":</strong> ";
                }
                echo "<p>" . htmlspecialchars($row["content"]) . "</p>";
                if (!empty($row["image_path"])) {
                    echo "<img src='" . htmlspecialchars($row["image_path"]) . "' alt='Image' width='300px'>";
                }
                echo "<small>" . $row["created_at"];
                if ($row["is_admin"]) {
                    echo " - Admin";
                }
                echo "</small>";
                echo "<br><a href='admin.php?delete=" . $row["id"] . "'>Delete</a>";
                echo "</div>";
            }
        } else {
            echo "No posts yet.";
        }
        $conn->close();
    ?>
</div>

<?php
if (isset($_SESSION['admin'])) {
    // Admin tools - Logout and Post as Admin
    echo "<h1>Admin Bulletin Board</h1>";
    echo "<a href='admin.php?logout=1'>Logout</a>";
        echo <<<HTML
<form action="upload.php" method="post" enctype="multipart/form-data">
    <label for="content">Viesti:</label>
    <label for="nickname">Nimike (optional):</label>
    <input type="text" name="nickname" id="nickname">
    <br>
    <textarea name="content" id="content" rows="4" cols="50"></textarea>
    <br>
    <label for="image">Kuva:</label>
    <input type="file" name="image" id="image">
    <br>
    <input type="submit" name="submit" value="Post">
</form>
HTML;
} else {
    // If not logged in, display the admin login form
    ?>
    <h1>Admin Login</h1>
    <form action="admin.php" method="post">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>
        <br>
        <input type="submit" name="submit" value="Login">
    </form>
    <?php
}
?>

</body>
</html>

