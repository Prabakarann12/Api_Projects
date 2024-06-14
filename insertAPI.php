<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "imguploadtest";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "connection failed : " . $e->getMessage();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $registerName = $_REQUEST['name'];
    $registerEmail = $_REQUEST['email'];
    $registerPassword = $_REQUEST['password'];
    $registerAddress = $_REQUEST['address'];
    $registerDOB = $_REQUEST['dob'];
    $image_name = $_FILES["image"]["name"];
    $image_temp = $_FILES["image"]["tmp_name"];
    $image_folder = "savefile/";
    $image_path = $image_folder . $image_name;

    if (move_uploaded_file($image_temp, $image_path)) {
        $query = "INSERT INTO uploadimg(name, email, password, address, dob, file, file_path) VALUES (:name, :email, :password, :address, :dob, :image_name, :image_path)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':name', $registerName);
        $stmt->bindParam(':email', $registerEmail);
        $stmt->bindParam(':password', $registerPassword);
        $stmt->bindParam(':address', $registerAddress);
        $stmt->bindParam(':dob', $registerDOB);
        $stmt->bindParam(":image_name", $image_name);
        $stmt->bindParam(":image_path", $image_path);

        if ($stmt->execute()) {
            echo "upload success";
        } else {
            echo "Not upload";
        }
    } else {
        echo "error uploading img";
    }
}
?>
