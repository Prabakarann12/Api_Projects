<?php

$servername = "localhost";
$dbUsername = "id22045533_admin";
$dbPassword = "Admin@123";
$database = "id22045533_unistudy";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$database", $dbUsername, $dbPassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        
        // Collect data from POST request
        $user_id = $_POST['user_id'];
        $budget_range = $_POST['budget_range'];
        $province = $_POST['province'];
        $study_field = $_POST['study_field'];
        $program_length = $_POST['program_length'];
        $scholarships_available = isset($_POST['scholarships_available']) ? 1 : 0;
        $application_fee = $_POST['application_fee'];
        $additional_criteria = $_POST['additional_criteria'];

        // Debugging: Print received data
        error_log("Received data: user_id=$user_id, budget_range=$budget_range, province=$province, study_field=$study_field, program_length=$program_length, scholarships_available=$scholarships_available, application_fee=$application_fee, additional_criteria=$additional_criteria");

        // Prepare SQL insert statement
        $stmt = $conn->prepare("INSERT INTO responses (user_id, budget_range, province, study_field, program_length, scholarships_available, application_fee, additional_criteria) 
                               VALUES (:user_id, :budget_range, :province, :study_field, :program_length, :scholarships_available, :application_fee, :additional_criteria)");

        // Bind parameters
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':budget_range', $budget_range, PDO::PARAM_STR);
        $stmt->bindParam(':province', $province, PDO::PARAM_STR);
        $stmt->bindParam(':study_field', $study_field, PDO::PARAM_STR);
        $stmt->bindParam(':program_length', $program_length, PDO::PARAM_STR);
        $stmt->bindParam(':scholarships_available', $scholarships_available, PDO::PARAM_INT);
        $stmt->bindParam(':application_fee', $application_fee, PDO::PARAM_STR);
        $stmt->bindParam(':additional_criteria', $additional_criteria, PDO::PARAM_STR);

        // Execute SQL insert statement
        if ($stmt->execute()) {
            echo json_encode(array("message" => "Response submitted successfully"));
        } else {
            error_log("SQL error: " . $stmt->errorInfo()[2]);
            echo json_encode(array("message" => "Error: " . $stmt->errorInfo()[2]));
        }

        
    } else {
        error_log("Invalid request method");
        echo json_encode(array("message" => "Invalid request method"));
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo "Database error: " . $e->getMessage();
}
?>
