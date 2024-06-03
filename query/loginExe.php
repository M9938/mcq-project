<?php 
session_start();
include("../conn.php");

extract($_POST);

$selAcc = $conn->prepare("SELECT exmne_id, exmne_password FROM examinee_tbl WHERE exmne_email = :username");
$selAcc->execute(array(':username' => $username));
$selAccRow = $selAcc->fetch(PDO::FETCH_ASSOC);

if($selAccRow && password_verify($pass, $selAccRow['exmne_password']))
{
  $_SESSION['examineeSession'] = array(
  	 'exmne_id' => $selAccRow['exmne_id'],
  	 'examineenakalogin' => true
  );
  $res = array("res" => "success");
}
else
{
  $res = array("res" => "invalid");
}

echo json_encode($res);
?>
