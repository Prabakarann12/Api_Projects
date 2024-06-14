<?php
$servername = "localhost";
$username = "id22045533_admin";
$password = "Admin@123";
$database = "id22045533_unistudy";

header('Content-Type: application/json');

try {
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $action = isset($_GET['action']) ? $_GET['action'] : '';
   
    if ($id > 0) {
        if ($action === 'get_image') {
            $stmt = $conn->prepare("SELECT file_path FROM registerunistudy WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $filePath = $row['file_path'];

                if (file_exists($filePath)) {
                    $mimeType = mime_content_type($filePath);
                    header("Content-Type: $mimeType");
                    header("Content-Length: " . filesize($filePath));
                    readfile($filePath);
                    exit;
                } else {
                    http_response_code(404);
                    echo json_encode(array('error' => 'File not found'));
                    exit;
                }
            } else {
                http_response_code(404);
                echo json_encode(array('error' => 'No data found for the given ID'));
                exit;
            }
        } else {
            $stmt = $conn->prepare("SELECT first_name, last_name, email, dob, file_path, address FROM registerunistudy WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $data = array(
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'],
                    'email' => $row['email'],
                    'dob' => $row['dob'],
                    'file_path' => $row['file_path'],
                    'address' => $row['address']
                );

                echo json_encode($data);
            } else {
                echo json_encode(array('error' => 'No data found for the given ID'));
            }
        }
    } else {
        http_response_code(400);
        echo json_encode(array('error' => 'Invalid ID'));
    }
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(array('error' => 'Connection failed: ' . $e->getMessage()));
}
?>
