<?php
$servername = "localhost";
$username = "id22045533_admin";
$password = "Admin@123";
$database = "id22045533_unistudy";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully"; // Changed "connect" to "Connected successfully"
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ImageFolder = "savefile/"; // Define the image folder path
    $ImageName = $_FILES["image"]["name"];
    $ImageTemp = $_FILES["image"]["tmp_name"];
    $ImagePath = $ImageFolder . $ImageName;

    if (!file_exists($ImageFolder)) {
        mkdir($ImageFolder, 0755, true);
    }

    if (move_uploaded_file($ImageTemp, $ImagePath)) {
        // Prepare the query to insert the image data into the database
        $stmt = $conn->prepare("INSERT INTO `Uniapp_imagedata` (`Uni_appImage`, `appImagePath`) VALUES (:image_name, :image_path)");
        $stmt->bindParam(':image_name', $ImageName);
        $stmt->bindParam(':image_path', $ImagePath);

        // Execute the query
        if ($stmt->execute()) {
            echo "Image uploaded and inserted into database.";
        } else {
            echo "Failed to insert image into database.";
        }
    } else {
        echo "Failed to move uploaded file.";
    }
}
?>
