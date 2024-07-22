<?php
header('Content-Type: application/json'); // Set the content type to JSON

$servername = "localhost";
$username = "id22045533_admin";
$password = "";
$database = "id22045533_unistudy";

$response = array(); // Initialize an array to hold the response

try {
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $response['status'] = "Connected successfully";
} catch(PDOException $e) {
    $response['status'] = "Connection failed: " . $e->getMessage();
    echo json_encode($response);
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
        // Collect the other form data
        $ConsultantName = $_POST['Consultant_name'];
        $ConsultantEmail = $_POST['Consultant_email'];
        $ConsultantPhone = $_POST['Consultant_phone'];

        // Prepare the query to insert the data into the database
        $stmt = $conn->prepare("INSERT INTO `Concultant_data` (`Concultants_name`, `Concultants_email`, `Concultants_Image`, `Concultants_imagepath`, `Consultant_phone`) VALUES (:consultant_name, :consultant_email, :image_name, :image_path, :consultant_phone)");
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
    $stmt = $conn->prepare("SELECT `id`, `Concultants_name`, `Concultants_email`, `Concultants_Image`, `Concultants_imagepath`, `Consultant_phone` FROM `Concultant_data`");
    $stmt->execute();

    // Fetch all the results
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the results as JSON
    $response['data'] = $results;
}

// Encode the response as JSON
echo json_encode($response);
?>

