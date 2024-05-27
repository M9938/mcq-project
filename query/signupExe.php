<?php
include("../../../conn.php");

extract($_POST);

$username = $_POST["username"];
$password = $_POST["pass"];
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$selAcc = $conn->prepare("INSERT INTO `admin_acc`(`admin_user`,`admin_pass`) VALUES (:username, :password)");
$selAcc->bindParam(':username', $username);
$selAcc->bindParam(':password', $hashedPassword);
$selAcc->execute();

if($selAcc)
{
    $res = array("res" => "success");
}
else
{
    $res = array("res" => "invalid");
}

echo json_encode($res);
?>
