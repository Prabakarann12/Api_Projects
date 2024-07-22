<?php

header('Content-Type: application/json'); // Set the content type to JSON

$servername = "localhost";
$username = "id22045533_admin";
$password = "";
$database = "id22045533_unistudy";

$response = array();
// Establish a database connection
$dsn = "mysql:host=$servername;dbname=$database";
try {
    $databaseConnection = new PDO($dsn, $username, $password);
    $databaseConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] == 'POST' || $_SERVER['REQUEST_METHOD'] == 'GET') {
        if (isset($_REQUEST['requestby'])) {

            switch ($_REQUEST['requestby']) {

                case 'Registerdata':
                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                        // List of required fields
                        $requiredFields = ['first_name', 'last_name', 'email', 'password', 'dob', 'address'];
                        $missingFields = [];

                        // Check if all required fields are set
                        foreach ($requiredFields as $field) {
                            if (empty($_POST[$field])) {
                                $missingFields[] = $field;
                            }
                        }

                        if (!empty($missingFields)) {
                            echo json_encode(['status' => 'error', 'message' => 'Missing fields: ' . implode(', ', $missingFields)]);
                            exit;
                        }

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

                        // Check if email is already registered
                        $checkEmailQuery = "SELECT COUNT(*) FROM registerunistudy WHERE email = :email";
                        $stmt = $databaseConnection->prepare($checkEmailQuery);
                        $stmt->bindParam(':email', $registerEmail);
                        $stmt->execute();
                        $emailCount = $stmt->fetchColumn();

                        if ($emailCount > 0) {
                            echo json_encode(['status' => 'error', 'message' => 'Email already registered']);
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
                        $stmt = $databaseConnection->prepare($query);
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
                    }
                    break;

                case 'Logindata':
                    $requestData = json_decode(file_get_contents('php://input'), true);

                    // Extract user credentials
                    $userEmail = $_REQUEST['email'];
                    $userPassword = $_REQUEST['password'];

                    // Prepare and execute the SQL query
                    $sql = "SELECT id FROM registerunistudy WHERE email = :email AND password = :password";
                    $stmt = $databaseConnection->prepare($sql);
                    $stmt->bindParam(":email", $userEmail);
                    $stmt->bindParam(":password", $userPassword);
                    $stmt->execute();
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($user) {
                        // If user is found, return the user's ID
                        $response = array(
                            'success' => true,
                            'message' => 'Login successful',
                            'userId' => $user['id']
                        );
                        echo json_encode($response);
                    } else {
                        // If user is not found, return a failure message
                        $response = array(
                            'success' => false,
                            'message' => 'Invalid credentials'
                        );
                        echo json_encode($response);
                    }
                    break;
                    case 'showuserdata':
                        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
                        $action = isset($_GET['action']) ? $_GET['action'] : '';
                    
                        if ($id > 0) {
                            if ($action === 'get_image') {
                                // Use the correct database connection variable
                                $stmt = $databaseConnection->prepare("SELECT file_path FROM registerunistudy WHERE id = :id");
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
                                $stmt = $databaseConnection->prepare("SELECT first_name, last_name, email, dob, file_path, address FROM registerunistudy WHERE id = :id");
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
                        break;
                    
                    case 'edituserdata':
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
                
                        $stmt = $databaseConnection->prepare($query);
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
                    
                    break;

                case 'forgotpassword':
                    $requestData = json_decode(file_get_contents('php://input'), true);
                
                    // Extract email and new password from the request data
                    if (isset($requestData['email']) && isset($requestData['newPassword'])) {
                        $userEmail = $requestData['email'];
                        $newPassword = $requestData['newPassword']; // No hashing
                
                        // Update the password in the database
                        $sql = "UPDATE registerunistudy SET password = :newPassword WHERE email = :email";
                        $stmt = $databaseConnection->prepare($sql);
                        $stmt->bindParam(':email', $userEmail);
                        $stmt->bindParam(':newPassword', $newPassword);
                
                        if ($stmt->execute()) {
                            // If update is successful, return a success message
                            echo json_encode(array(
                                'success' => true,
                                'message' => 'Password updated successfully'
                            ));
                        } else {
                            // If update fails, return a failure message
                            echo json_encode(array(
                                'success' => false,
                                'message' => 'Failed to update password'
                            ));
                        }
                    } else {
                        // If required data is missing, return an error message
                        echo json_encode(array(
                            'success' => false,
                            'message' => 'Invalid input'
                        ));
                    }
                    break;
                    case 'consultantdata':
                        
                        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                            $ImageFolder = "savefile/"; // Define the image folder path
                            $ImageName = $_FILES["image"]["name"];
                            $ImageTemp = $_FILES["image"]["tmp_name"];
                            $ImagePath = $ImageFolder . $ImageName;
                        
                            if (!file_exists($ImageFolder)) {
                                mkdir($ImageFolder, 0755, true);
                            }
                        
                            if (move_uploaded_file($ImageTemp, $ImagePath)) {
                                // Collect the other form data
                                $ConsultantName = $_POST['Consultant_name'];
                                $ConsultantEmail = $_POST['Consultant_email'];
                                $ConsultantPhone = $_POST['Consultant_phone'];
                        
                                // Prepare the query to insert the data into the database
                                $stmt = $databaseConnection->prepare("INSERT INTO `Concultant_data` (`Concultants_name`, `Concultants_email`, `Concultants_Image`, `Concultants_imagepath`, `Consultant_phone`) VALUES (:consultant_name, :consultant_email, :image_name, :image_path, :consultant_phone)");
                                $stmt->bindParam(':consultant_name', $ConsultantName);
                                $stmt->bindParam(':consultant_email', $ConsultantEmail);
                                $stmt->bindParam(':image_name', $ImageName);
                                $stmt->bindParam(':image_path', $ImagePath);
                                $stmt->bindParam(':consultant_phone', $ConsultantPhone);
                        
                                // Execute the query
                                if ($stmt->execute()) {
                                    $response['status'] = "Data and image uploaded and inserted into database.";
                                } else {
                                    $response['status'] = "Failed to insert data into database.";
                                }
                            } else {
                                $response['status'] = "Failed to move uploaded file.";
                            }
                        } elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
                            // Prepare the query to fetch data from the database
                            $stmt = $databaseConnection->prepare("SELECT `id`, `Concultants_name`, `Concultants_email`, `Concultants_Image`, `Concultants_imagepath`, `Consultant_phone` FROM `Concultant_data`");
                            $stmt->execute();
                        
                            // Fetch all the results
                            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                            // Return the results as JSON
                            $response['data'] = $results;
                        }
                        
                        // Encode the response as JSON
                        echo json_encode($response);
                        break;
                    
                
                    case 'universitiesdata':
                        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                            // Debugging output
                            $response['files'] = $_FILES;
                            
                            if (isset($_FILES["image_path"])) {
                                if ($_FILES["image_path"]["error"] == 0) {
                                    $ImageFolder = "savefile/"; // Define the image folder path
                                    $ImageName = basename($_FILES["image_path"]["name"]);
                                    $ImageTemp = $_FILES["image_path"]["tmp_name"];
                                    $ImagePath = $ImageFolder . $ImageName;
                        
                                    if (!file_exists($ImageFolder)) {
                                        mkdir($ImageFolder, 0755, true);
                                    }
                        
                                    if (move_uploaded_file($ImageTemp, $ImagePath)) {
                                        // Collect the other form data
                                        $UniversitiesTitle = $_POST['universities_title'];
                                        $UniversitiesSubtitle = $_POST['universities_subtitle'];
                        
                                        // Prepare the query to insert the data into the database
                                        $stmt = $databaseConnection->prepare("INSERT INTO `universities_listdata` (`image_path`, `universities_title`, `universities_subtitle`) VALUES (:image_path, :universities_title, :universities_subtitle)");
                                        $stmt->bindParam(':image_path', $ImagePath);
                                        $stmt->bindParam(':universities_title', $UniversitiesTitle);
                                        $stmt->bindParam(':universities_subtitle', $UniversitiesSubtitle);
                        
                                        // Execute the query
                                        if ($stmt->execute()) {
                                            $response['status'] = "Data and image uploaded and inserted into database.";
                                        } else {
                                            $response['status'] = "Failed to insert data into database.";
                                        }
                                    } else {
                                        $response['status'] = "Failed to move uploaded file.";
                                    }
                                } else {
                                    $response['status'] = "Upload error: " . $_FILES["image_path"]["error"];
                                }
                            } else {
                                $response['status'] = "No file uploaded.";
                            }
                        } elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
                            // Prepare the query to fetch data from the database
                            $stmt = $databaseConnection->prepare("SELECT `id`, `image_path`, `universities_title`, `universities_subtitle` FROM `universities_listdata`");
                            $stmt->execute();
                        
                            // Fetch all the results
                            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                            // Return the results as JSON
                            $response['data'] = $results;
                        }
                        
                        // Encode the response as JSON
                        echo json_encode($response);
                        break;
                case 'schooldata':
                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                        // Debugging output
                        $response['files'] = $_FILES;
                        
                        if (isset($_FILES["School_image_path"])) {
                            if ($_FILES["School_image_path"]["error"] == 0) {
                                $ImageFolder = "savefile/"; // Define the image folder path
                                $ImageName = basename($_FILES["School_image_path"]["name"]);
                                $ImageTemp = $_FILES["School_image_path"]["tmp_name"];
                                $ImagePath = $ImageFolder . $ImageName;
                    
                                if (!file_exists($ImageFolder)) {
                                    mkdir($ImageFolder, 0755, true);
                                }
                    
                                if (move_uploaded_file($ImageTemp, $ImagePath)) {
                                    // Collect the other form data
                                    $UniversitiesTitle = $_POST['School_title'];
                                    $UniversitiesSubtitle = $_POST['School_subtitle'];
                                    $UniversitiesSubtitle = $_POST['school_website'];
                    
                                    // Prepare the query to insert the data into the database
                                    $stmt = $databaseConnection->prepare("INSERT INTO `School_datalist` (`School_image_path`, `School_title`, `School_subtitle`,`school_website`) VALUES (:School_image_path, :School_title, :School_subtitle , :school_website)");
                                    $stmt->bindParam(':School_image_path', $ImagePath);
                                    $stmt->bindParam(':School_title', $UniversitiesTitle);
                                    $stmt->bindParam(':School_subtitle', $UniversitiesSubtitle);
                                    $stmt->bindParam(':school_website', $UniversitiesSubtitle);
                    
                                    // Execute the query
                                    if ($stmt->execute()) {
                                        $response['status'] = "Data and image uploaded and inserted into database.";
                                    } else {
                                        $response['status'] = "Failed to insert data into database.";
                                    }
                                } else {
                                    $response['status'] = "Failed to move uploaded file.";
                                }
                            } else {
                                $response['status'] = "Upload error: " . $_FILES["image_path"]["error"];
                            }
                        } else {
                            $response['status'] = "No file uploaded.";
                        }
                    } elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
                        // Prepare the query to fetch data from the database
                        $stmt = $databaseConnection->prepare("SELECT `id`, `School_image_path`, `School_title`, `School_subtitle`,`school_website` FROM `School_datalist`");
                        $stmt->execute();
                    
                        // Fetch all the results
                        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                        // Return the results as JSON
                        $response['data'] = $results;
                    }  
                    echo json_encode($response);
                    break;      
                
                    default:
                        $response['status'] = "Invalid action";
                        break;
                    
            }
        } else {
            echo 'No request received';
        }
    }

} catch (PDOException $error) {
    echo "Connection failed: " . $error->getMessage();
}
?>
