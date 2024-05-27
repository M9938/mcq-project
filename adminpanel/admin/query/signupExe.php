<?php
// Include the connection file
include("../../../conn.php");

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Extract POST data
    $username = $_POST["username"];
    $password = $_POST["pass"];
    $cpassword = $_POST['cpass'];

    // Verify password match
    if ($password == $cpassword) {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Prepare and bind parameters for insertion
        $selAcc = $conn->prepare("INSERT INTO `admin_acc`(`admin_user`,`admin_pass`) VALUES (:username, :password)");
        $selAcc->bindParam(':username', $username);
        $selAcc->bindParam(':password', $hashedPassword);

        // Execute the query
        if ($selAcc->execute()) {
            // Registration successful
            $res = array("res" => "success");
        } else {
            // Registration failed
            $res = array("res" => "failed");
        }
    } else {
        // Passwords don't match
        $res = array("res" => "password_mismatch");
    }
} else {
    // Invalid request method
    $res = array("res" => "invalid_request");
}

// Output JSON response
echo json_encode($res);
?>
