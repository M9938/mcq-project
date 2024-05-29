<?php 
include("../../../conn.php");

extract($_POST);

$selExamineeFullname = $conn->query("SELECT * FROM examinee_tbl WHERE exmne_fullname='$fullname'");
$selExamineeEmail = $conn->query("SELECT * FROM examinee_tbl WHERE exmne_email='$email'");


if ($gender == "0") {
    $res = array("res" => "noGender");
} elseif ($course == "0") {
    $res = array("res" => "noCourse");
} elseif ($year_level == "0") {
    $res = array("res" => "noLevel");
} elseif ($selExamineeFullname->rowCount() > 0) {
    $res = array("res" => "fullnameExist", "msg" => $fullname);
} elseif ($selExamineeEmail->rowCount() > 0) {
    $res = array("res" => "emailExist", "msg" => $email);
} else {
    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $insData = $conn->query("INSERT INTO examinee_tbl(exmne_fullname,exmne_course,exmne_gender,exmne_birthdate,exmne_year_level,exmne_email,exmne_password) VALUES('$fullname','$course','$gender','$bdate','$year_level','$email','$hashedPassword')");
    if ($insData) {
        $res = array("res" => "success", "msg" => $email);
    } else {
        $res = array("res" => "failed");
    }
}

echo json_encode($res);
?>
