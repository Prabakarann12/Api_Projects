<?php
$servername = "localhost";
$dbUsername = "id22045533_admin"; 
$dbPassword = "Admin@123"; 
$database = "id22045533_unistudy";

try {
  $conn = new PDO("mysql:host=$servername;dbname=$database", $dbUsername, $dbPassword);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  header('Content-type:application/json');
  header("Access-Control-Allow-Orgin:*");

  $userEmail = $_REQUEST['email'];
  $userPassword = $_REQUEST['password'];

  $sql ="SELECT * FROM registerunistudy WHERE email = :email AND password = :password";
  $stmp = $conn -> prepare($sql);
  $stmp-> bindParam(":email", $email);
  $stmp-> bindParam(":password",$password);
  $stmp->execute();
  $returnValue = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode($returnValue);
  



 
} catch(PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}
?>
