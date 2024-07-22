<?php
$servername = "localhost";
$dbUsername = "id22045533_admin"; 
$dbPassword = ""; 
$database = "id22045533_unistudy";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$database", $dbUsername, $dbPassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    header('Content-type: application/json');
    header("Access-Control-Allow-Origin: *");

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Check if all required fields are set
        if (isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['dob']) && isset($_POST['address'])) {
            $firstName = $_POST['first_name'];
            $lastName = $_POST['last_name'];
            $registerEmail = $_POST['email'];
            $registerPassword = $_POST['password'];
            $registerDOB = $_POST['dob'];
            $registerAddress = $_POST['address'];

            // Validate date format
            $date = DateTime::createFromFormat('Y-m-d', $registerDOB);
            if (!$date || $date->format('Y-m-d') !== $registerDOB) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid date format. Date should be in the format YYYY-MM-DD.']);
                exit;
            }

            $image_name = null;
            $image_path = null;

            // Handle file upload
            if (isset($_FILES["image"]) && $_FILES["image"]["error"] == UPLOAD_ERR_OK) {
                $image_name = $_FILES["image"]["name"];
                $image_temp = $_FILES["image"]["tmp_name"];
                $image_folder = "savefile/";
                $image_path = $image_folder . $image_name;

                if (!move_uploaded_file($image_temp, $image_path)) {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to move uploaded file']);
                    exit; // Stop execution if image upload fails
                }
            }

            // Insert data into the database
            $query = "INSERT INTO registerunistudy (first_name, last_name, email, password, address, dob, file, file_path) VALUES (:first_name, :last_name, :email, :password, :address, :dob, :image_name, :image_path)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':first_name', $firstName);
            $stmt->bindParam(':last_name', $lastName);
            $stmt->bindParam(':email', $registerEmail);
            $stmt->bindParam(':password', $registerPassword);
            $stmt->bindParam(':address', $registerAddress);
            $stmt->bindParam(':dob', $registerDOB);
            $stmt->bindParam(':image_name', $image_name);
            $stmt->bindParam(':image_path', $image_path);

            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Registration successful']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to register']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
        }
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Connection failed: ' . $e->getMessage()]);
}
?>
