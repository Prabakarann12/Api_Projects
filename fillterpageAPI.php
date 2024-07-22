<?php
header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Database configuration
$servername = "localhost";
$dbUsername = "id22045533_admin"; 
$dbPassword = ""; 
$database = "id22045533_unistudy";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$database", $dbUsername, $dbPassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare the SQL statement
    $stmt = $conn->prepare("INSERT INTO filters (currentLocation, searchLocation, today, tomorrow, thisWeek, thisMonth, chooseDate, dayTime, nightTime, selectTime, concerts, parties, listeningEvent, festivals, tours, budgetRange) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Bind parameters
    $stmt->bindParam(1, $data['currentLocation'], PDO::PARAM_INT);
    $stmt->bindParam(2, $data['searchLocation'], PDO::PARAM_INT);
    $stmt->bindParam(3, $data['today'], PDO::PARAM_INT);
    $stmt->bindParam(4, $data['tomorrow'], PDO::PARAM_INT);
    $stmt->bindParam(5, $data['thisWeek'], PDO::PARAM_INT);
    $stmt->bindParam(6, $data['thisMonth'], PDO::PARAM_INT);
    $stmt->bindParam(7, $data['chooseDate'], PDO::PARAM_INT);
    $stmt->bindParam(8, $data['dayTime'], PDO::PARAM_INT);
    $stmt->bindParam(9, $data['nightTime'], PDO::PARAM_INT);
    $stmt->bindParam(10, $data['selectTime'], PDO::PARAM_INT);
    $stmt->bindParam(11, $data['concerts'], PDO::PARAM_INT);
    $stmt->bindParam(12, $data['parties'], PDO::PARAM_INT);
    $stmt->bindParam(13, $data['listeningEvent'], PDO::PARAM_INT);
    $stmt->bindParam(14, $data['festivals'], PDO::PARAM_INT);
    $stmt->bindParam(15, $data['tours'], PDO::PARAM_INT);
    $stmt->bindParam(16, $data['budgetRange'], PDO::PARAM_INT);

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(array("message" => "Filters applied successfully"));
    } else {
        echo json_encode(array("message" => "Failed to apply filters"));
    }

} catch(PDOException $e) {
    echo json_encode(array("message" => "Connection failed: " . $e->getMessage()));
}


?>
