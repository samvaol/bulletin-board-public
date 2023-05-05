<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PohjisBoard</title>
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
<link rel="stylesheet" href="https://cdn.plyr.io/3.6.8/plyr.css" />
<script src="https://cdn.plyr.io/3.6.8/plyr.js"></script>
<body>

<div id="modal" class="modal">
    <div class="modal-content">
        <p style="color: red;">Error: You cannot submit an empty post.</p>
        <button class="get-out">Close</button>
    </div>
</div>


<?php

/*
if (isset($_GET["error"]) && $_GET["error"] == "empty") {
    echo "<p style='color: red;'>Error: You cannot submit an empty post.</p>";
}
*/

?>
    <h1>xxx</h1>
<form action="upload.php" method="post" enctype="multipart/form-data">
    <label for="content">Viesti:</label>
    <label for="nickname">Nimike (optional):</label>
    <input type="text" name="nickname" id="nickname">
    <br>
    <textarea name="content" id="content" rows="4" cols="50"></textarea>
    <br>
    <label for="media">Kuva / Video:</label>
    <input type="file" name="media" id="media">
    <br>
    <input type="submit" name="submit" value="Post">

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
        if (!empty($row["media_path"])) {
            $media_ext = strtolower(pathinfo($row["media_path"], PATHINFO_EXTENSION));
            if (in_array($media_ext, array("jpg", "jpeg", "png", "gif"))) {
                echo "<img src='" . htmlspecialchars($row["media_path"]) . "' alt='Image' width='300px'>";
            } elseif (in_array($media_ext, array("mp4", "webm", "ogg"))) {
                echo "<video controls class='plyr-video' width='300px'><source src='" . htmlspecialchars($row["media_path"]) . "' type='video/" . $media_ext . "'></video>";
            }
        }
        echo "<small>" . $row["created_at"] . "</small>";
        echo "</div>";
        }
        } else {
        echo "No posts yet.";
        }
        $conn->close();
        ?>
    </div>

</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    Plyr.setup('.plyr-video');
});
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const modal = document.getElementById("modal");
        const getOutButton = document.querySelector(".get-out");

        <?php
        if (isset($_GET["error"]) && $_GET["error"] == "empty") {
            echo "showModal();";
        }
        ?>

        function showModal() {
            modal.style.display = "block";
            document.body.classList.add("modal-open");
        }

        function hideModal() {
            modal.style.display = "none";
            document.body.classList.remove("modal-open");
        }

        getOutButton.addEventListener("click", function () {
            hideModal();
        });
    });
</script>

<a href="xxx" style="text-align: center; color: #82847d;"> Pricavy Policy/Tietosuojaseloste </a>
</body>
</html>
