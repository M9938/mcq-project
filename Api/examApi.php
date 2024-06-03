<?php 
header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Method: GET');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Request-with');
require_once '../conn.php';
$requestmethod = $_SERVER['REQUEST_METHOD'];
if ($requestmethod == "GET") {
    $response = array();
    $selCourse = $conn->query("SELECT * FROM course_tbl ORDER BY cou_id DESC ");
    if ($selCourse->rowCount() > 0) {
        $response['status'] = 200;
        $response['message'] = "Course List";
        $response['data'] = array(); // Initialize data array
        while ($selCourseRow = $selCourse->fetch(PDO::FETCH_ASSOC)) { 
            $course = array(
                'cid' => $selCourseRow['cou_id'],
                'cname' => $selCourseRow['cou_name'],
                'ccreated' => $selCourseRow['cou_created']
            );
            $response['data'][] = $course; // Add course data to the data array
        }
        header("HTTP/1.0 200 OK");
        echo json_encode($response, JSON_PRETTY_PRINT);
    } else {
        header("HTTP/1.0 404 Not Found");
        echo json_encode(array("status" => 404, "message" => "No courses found"));
    }
} else {
    $data = array(
        'status' => 405,
        'message' => $requestmethod . " method is not allowed."
    );
    header("HTTP/1.0 405 Method Not Allowed");
    echo json_encode($data);
}
?>
