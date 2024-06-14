<?php
$servername = "localhost";
$dbUsername = "id22045533_admin";
$dbPassword = "Admin@123";
$database = "id22045533_unistudy";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$database", $dbUsername, $dbPassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' || $_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_REQUEST['requestby'])) {
        switch ($_REQUEST['requestby']) {
            // Register API
            case 'RegisterData':
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $registerName = $_REQUEST['name'];
                    $registerEmail = $_REQUEST['email'];
                    $registerPassword = $_REQUEST['password'];
                    $registerAddress = $_REQUEST['address'];
                    $registerDOB = $_REQUEST['dob'];
                    $image = $_REQUEST["image"];
                    $image_name = uniqid() . '.png';
                    $image_folder = "savefile/";
                    $image_path = $image_folder . $image_name;

                    $image = str_replace('data:image/png;base64,', '', $image);
                    $image = str_replace(' ', '+', $image);
                    $image_data = base64_decode($image);
                    file_put_contents($image_path, $image_data);

                    // Check if email already exists
                    $checkEmailQuery = "SELECT email FROM registerunistudy WHERE email = :email";
                    $checkStmt = $conn->prepare($checkEmailQuery);
                    $checkStmt->bindParam(':email', $registerEmail);
                    $checkStmt->execute();

                    if ($checkStmt->rowCount() > 0) {
                        echo "Error: Email already exists";
                    } else {
                        $query = "INSERT INTO registerunistudy(name, email, password, address, dob, file, file_path) VALUES (:name, :email, :password, :address, :dob, :image_name, :image_path)";
                        $stmt = $conn->prepare($query);
                        $stmt->bindParam(':name', $registerName);
                        $stmt->bindParam(':email', $registerEmail);
                        $stmt->bindParam(':password', $registerPassword);
                        $stmt->bindParam(':address', $registerAddress);
                        $stmt->bindParam(':dob', $registerDOB);
                        $stmt->bindParam(":image_name", $image_name);
                        $stmt->bindParam(":image_path", $image_path);

                        if ($stmt->execute()) {
                            echo "Upload success";
                        } else {
                            echo "Not uploaded";
                        }
                    }
                }
                break;

            case 'logindata':
                try {
                    $email = isset($_REQUEST['email']) ? $_REQUEST['email'] : null;
                    $password = isset($_REQUEST['password']) ? $_REQUEST['password'] : null;

                    $stmt = $conn->prepare("SELECT * FROM unistudy WHERE email = :email AND password = :password");
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':password', $password);
                    $stmt->execute();

                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($user) {
                        echo 'success';
                    } else {
                        echo 'Login failed';
                    }
                } catch(PDOException $e) {
                    echo "Error: " . $e->getMessage();
                }
                break;

            case 'ShowData':
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
                        $stmt = $conn->prepare("SELECT name, email, dob, file_path, file, address FROM registerunistudy WHERE id = :id");
                        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                        $stmt->execute();

                        if ($stmt->rowCount() > 0) {
                            $row = $stmt->fetch(PDO::FETCH_ASSOC);
                            $data = array(
                                'name' => $row['name'],
                                'email' => $row['email'],
                                'dob' => $row['dob'],
                                'file_path' => $row['file_path'],
                                'address' => $row['address'],
                                'file' => $row['file'],
                            );

                            echo json_encode($data);
                        } else {
                            echo json_encode(array('error' => 'No data found'));
                        }
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(array('error' => 'Invalid ID'));
                }
                break;

            case 'UpdateData':
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
                    $updateName = $_REQUEST['name'];
                    $updateEmail = $_REQUEST['email'];
                    $updatePassword = $_REQUEST['password'];
                    $updateAddress = $_REQUEST['address'];
                    $updateDOB = $_REQUEST['dob'];

                    if ($id > 0) {
                        // Check if the record exists
                        $checkRecordQuery = "SELECT * FROM registerunistudy WHERE id = :id";
                        $checkStmt = $conn->prepare($checkRecordQuery);
                        $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
                        $checkStmt->execute();

                        if ($checkStmt->rowCount() > 0) {
                            $query = "UPDATE registerunistudy SET name = :name, email = :email, password = :password, address = :address, dob = :dob WHERE id = :id";
                            $stmt = $conn->prepare($query);
                            $stmt->bindParam(':name', $updateName);
                            $stmt->bindParam(':email', $updateEmail);
                            $stmt->bindParam(':password', $updatePassword);
                            $stmt->bindParam(':address', $updateAddress);
                            $stmt->bindParam(':dob', $updateDOB);
                            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

                            if ($stmt->execute()) {
                                echo "Update success";
                            } else {
                                echo "Update failed";
                            }
                        } else {
                            echo "Error: Record not found";
                        }
                    } else {
                        echo "Error: Invalid ID";
                    }
                }
                break;

            default:
                echo 'Invalid request';
        }
    } else {
        echo 'No request received';
    }
}
?>
