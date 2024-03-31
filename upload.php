<?php
// Check if the user is logged in
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// Logout functionality
if (isset($_POST['logout'])) {
    session_start();
    $_SESSION = array();
    session_destroy();
    header('Location: login.php');
    exit;
}

// Function to create the user's uploads directory if it doesn't exist
function createUserUploadsDirectory($userId) {
    $uploadDir = 'uploads/' . $userId . '/';
    if (!is_dir($uploadDir)) {
        // Create the directory with read, write, and execute permissions for everyone
        mkdir($uploadDir, 0777, true);
    }
    return $uploadDir;
}

// Handle file upload
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    // Get the current user's ID
    $userId = $_SESSION['id'];

    // Create the user's uploads directory if it doesn't exist
    $uploadDir = createUserUploadsDirectory($userId);

    $uploadFile = $uploadDir . basename($_FILES['file']['name']);

    // Try to upload the file
    if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
        // Redirect to home.php after successful upload
        header('Location: home.php?file=' . urlencode(basename($_FILES['file']['name'])));
        exit;
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #818589;
        }

        .logout-button {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: #ff0000;
            color: #ffffff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .file-upload-form {
            width: fit-content;
            height: fit-content;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .file-upload-label input {
            display: none;
        }

        .file-upload-label svg {
            height: 50px;
            fill: rgb(82, 82, 82);
            margin-bottom: 20px;
        }

        .file-upload-label {
            cursor: pointer;
            background-color: #ddd;
            padding: 30px 70px;
            border-radius: 40px;
            border: 2px dashed rgb(82, 82, 82);
            box-shadow: 0px 0px 200px -50px rgba(0, 0, 0, 0.719);
            margin-bottom: 20px;
        }

        .file-upload-design {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .browse-button {
            background-color: rgb(82, 82, 82);
            padding: 5px 15px;
            border-radius: 10px;
            color: white;
            transition: all 0.3s;
        }

        .browse-button:hover {
            background-color: rgb(14, 14, 14);
        }

        .upload-button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .upload-button:hover {
            background-color: #45a049;
        }
    </style>
    <title>PDF File Upload</title>
</head>
<body>
    <form method="post" enctype="multipart/form-data" class="file-upload-form">
        <button class="logout-button" type="submit" name="logout">Logout</button>
        <label for="file" class="file-upload-label">
            <div class="file-upload-design">
                <svg viewBox="0 0 640 512" height="1em">
                    <path d="M144 480C64.5 480 0 415.5 0 336c0-62.8 40.2-116.2 96.2-135.9c-.1-2.7-.2-5.4-.2-8.1c0-88.4 71.6-160 160-160c59.3 0 111 32.2 138.7 80.2C409.9 102 428.3 96 448 96c53 0 96 43 96 96c0 12.2-2.3 23.8-6.4 34.6C596 238.4 640 290.1 640 352c0 70.7-57.3 128-128 128H144zm79-217c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l39-39V392c0 13.3 10.7 24 24 24s24-10.7 24-24V257.9l39 39c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-80-80c-9.4-9.4-24.6-9.4-33.9 0l-80 80z"></path>
                </svg>
                <p>Drag and Drop</p>
                <p>or</p>
                <span class="browse-button">Browse file</span>
            </div>
            <input id="file" type="file" name="file" accept=".pdf" />
        </label>
        <button type="submit" class="upload-button">Upload</button>
    </form>
</body>
</html>
