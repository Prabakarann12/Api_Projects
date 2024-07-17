<?php
$servername = "localhost";
$dbUsername = "id22045533_admin"; 
$dbPassword = "Admin@123"; 
$database = "id22045533_unistudy";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$database", $dbUsername, $dbPassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    header('Content-type: application/json');
    header("Access-Control-Allow-Origin: *");

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        parse_str(file_get_contents("php://input"), $_POST);

        // Check if 'id' field is set
        if (!isset($_REQUEST['id']) || empty($_REQUEST['id'])) {
            echo json_encode(['status' => 'error', 'message' => "Field 'id' is required"]);
            exit;
        }

        $id = $_REQUEST['id'];
        $fieldsToUpdate = [];
        $params = [];

        // Optional fields
        $optionalFields = ['first_name', 'last_name', 'email', 'dob', 'address'];

        foreach ($optionalFields as $field) {
            if (isset($_REQUEST[$field]) && !empty($_REQUEST[$field])) {
                if ($field == 'dob') {
                    // Validate date format
                    $date = DateTime::createFromFormat('Y-m-d', $_REQUEST[$field]);
                    if (!$date || $date->format('Y-m-d') !== $_REQUEST[$field]) {
                        echo json_encode(['status' => 'error', 'message' => 'Invalid date format. Date should be in the format YYYY-MM-DD.']);
                        exit;
                    }
                }
                $fieldsToUpdate[] = "$field = :$field";
                $params[":$field"] = $_REQUEST[$field];
            }
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
                exit;
            }

            $fieldsToUpdate[] = "file = :image_name, file_path = :image_path";
            $params[':image_name'] = $image_name;
            $params[':image_path'] = $image_path;
        }

        if (empty($fieldsToUpdate)) {
            echo json_encode(['status' => 'error', 'message' => 'No fields to update']);
            exit;
        }

        // Prepare query to update data in the database
        $query = "UPDATE registerunistudy SET " . implode(", ", $fieldsToUpdate) . " WHERE id = :id";
        $params[':id'] = $id;

        $stmt = $conn->prepare($query);
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Update successful']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Connection failed: ' . $e->getMessage()]);
}
?>
