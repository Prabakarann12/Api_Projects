<?php
$servername = "localhost";
$dbUsername = "id22045533_admin";
$dbPassword = "";
$database = "id22045533_unistudy";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$database", $dbUsername, $dbPassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set headers for JSON response
    header('Content-type: application/json');
    header("Access-Control-Allow-Origin: *");

    // Assuming the request is POST and the parameters are sent in the body
    $requestData = json_decode(file_get_contents('php://input'), true);

    // Extract user credentials
    $userEmail = $_REQUEST['email'];
    $userPassword = $_REQUEST['password'];

    // Prepare and execute the SQL query
    $sql = "SELECT id FROM registerunistudy WHERE email = :email AND password = :password";
    $stmt = $conn->prepare($sql);
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

} catch (PDOException $e) {
    echo json_encode(array(
        'success' => false,
        'message' => 'Connection failed: ' . $e->getMessage()
    ));
}
?>
