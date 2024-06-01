<?php
require '../PHPExcel-v7.4/PHPExcel.php';
include("../../../conn.php");
extract($_POST);
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == "validate") {

        $response = array();

        if ($_FILES['spreedsheetfile']['error'] === UPLOAD_ERR_OK) {
            $uploadedFileName = $_FILES['spreedsheetfile']['name'];
            $uploadedFileTmp = $_FILES['spreedsheetfile']['tmp_name'];
            $uploadedFileType = $_FILES['spreedsheetfile']['type'];

            if (in_array($uploadedFileType, array('application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'))) {

                require '../PHPExcel-v7.4/PHPExcel/IOFactory.php';

                if ($uploadedFileType === 'text/csv') {
                    $fileData = file_get_contents($uploadedFileTmp);
                    $csvData = str_getcsv($fileData, "\n");
                    $uploadedHeaders = str_getcsv($csvData[0]);

                    $errors = array();
                    $emptyCells = array();

                    foreach ($csvData as $key => $row) {
                        $rowData = str_getcsv($row);
                        foreach ($rowData as $index => $value) {
                            if (empty($value)) {
                                $errors[] = "Error: Field '{$uploadedHeaders[$index]}' in row " . ($key + 1) . " is empty.";
                                $emptyCells[] = "Row: " . ($key + 1) . ", Column: " . ($index + 1);
                            }
                        }
                        $email = $rowData[1];
                        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $errors[] = "Error: Invalid email format in row " . ($key + 1) . ".";
                        }
                    }

                    if (!empty($errors)) {
                        $response['errors'] = $errors;
                        echo json_encode($response);
                        exit;
                    }
                } else {
                    $objPHPExcel = PHPExcel_IOFactory::load($uploadedFileTmp);
                    $uploadedHeaders = $objPHPExcel->getActiveSheet()->toArray()[0];

                    $errors = array();
                    $emptyCells = array();
                    foreach ($objPHPExcel->getActiveSheet()->toArray() as $key => $row) {
                        foreach ($row as $index => $value) {
                            if ($key === 0 && empty($value)) {
                                $errors[] = "Error: Field '{$uploadedHeaders[$index]}' in row " . ($key + 1) . " is empty.";
                            }
                            if (empty($value)) {
                                $emptyCells[] = "Row: " . ($key + 1) . ", Column: " . ($index + 1);
                            }
                        }
                        if (count(array_filter($row)) !== count($row)) {
                            $emptyCells[] = "Row: " . ($key + 1) . ", Column: " . ($index + 1);
                        }
                    }
                }

                if (!empty($errors)) {
                    $response['errors'] = $errors;
                    echo json_encode($response);
                    exit;
                }

                $tableHTML = '<table>';
                $tableHTML .= '<thead><tr>';
                foreach ($uploadedHeaders as $header) {
                    $tableHTML .= '<th>' . $header . '</th>';
                }
                $tableHTML .= '</tr></thead><tbody>';
                foreach ($objPHPExcel->getActiveSheet()->toArray() as $key => $row) {
                    if ($key !== 0) {
                        $tableHTML .= '<tr>';
                        foreach ($row as $value) {
                            $tableHTML .= '<td>' . $value . '</td>';
                        }
                        $tableHTML .= '</tr>';
                    }
                }
                $tableHTML .= '</tbody></table><br>';
                $tableHTML .= '<button type="button" class="btn btn-success" id="uploadButton">Upload</button>';

                $response['tableHTML'] = $tableHTML;

                if (!empty($emptyCells)) {
                    $response['emptyCells'] = $emptyCells;
                }
            } else {
                $response['error'] = "Please upload a valid file (CSV, XLS, or XLSX).";
            }
        } else {
            $response['error'] = "Error uploading file.";
        }

        echo json_encode($response);
    } elseif ($action == "excelupload") {
        $response = array(); // Initialize response array
    
        // Check if the file was uploaded successfully
        if ($_FILES['spreedsheetfile']['error'] === UPLOAD_ERR_OK) {
            // Access uploaded file details
            $uploadedFileName = $_FILES['spreedsheetfile']['name'];
            $uploadedFileTmp = $_FILES['spreedsheetfile']['tmp_name'];
    
            $insertCount = 0;
            $objPHPExcel = PHPExcel_IOFactory::load($uploadedFileTmp); // Load Excel file
    
            // Example code to insert data into the database
            $data = array();
            foreach ($objPHPExcel->getActiveSheet()->toArray() as $key => $row) {
                if ($key !== 0) { // Skip header row
                    // Check if the index exists before accessing it
                    if (isset($row[5])) {
                        $question = $row[0];
                        // Check if the question already exists in the database
                        $sqlselect ="SELECT * FROM exam_question_tbl WHERE exam_id=:exam_id AND exam_question=:question";
                        $stmt = $conn->prepare($sqlselect);
                        $stmt->execute(array(':exam_id' => $examId, ':question' => $question));
                        $result = $stmt->fetchAll();
                        if (count($result) == 0) {
                            $data[] = array(
                                'exam_id' => $examId,
                                'exam_question' => $question,
                                'exam_ch1' => $row[1],
                                'exam_ch2' => $row[2],
                                'exam_ch3' => $row[3],
                                'exam_ch4' => $row[4],
                                'exam_answer' => $row[5]
                            );
                        }
                    }
                }
            }
    
            // Prepare and execute the SQL statement
            $sql = "INSERT INTO exam_question_tbl (exam_id, exam_question, exam_ch1, exam_ch2, exam_ch3, exam_ch4, exam_answer) VALUES (:exam_id, :exam_question, :exam_ch1, :exam_ch2, :exam_ch3, :exam_ch4, :exam_answer)";
            $stmt = $conn->prepare($sql);
            foreach ($data as $row) {
                if ($stmt->execute($row)) {
                    $insertCount++;
                } else {
                    // Handle SQL error
                    $errorInfo = $stmt->errorInfo();
                    $response['error'] = "Error: " . $sql . "<br>" . $errorInfo[2];
                    echo json_encode($response);
                    exit;
                }
            }
    
            // Prepare response
            $response['message'] = "$insertCount records inserted successfully.";
        } else {
            // Error uploading file
            $response['error'] = "Error uploading file: " . $_FILES['spreedsheetfile']['error'];
        }
    
        // Send JSON response
        echo json_encode($response);
    }
    

    else {
        // Invalid action error
        echo json_encode(array("error" => "Invalid action."));
    }
} else {
    // No action specified error
    echo json_encode(array("error" => "No action specified."));
}
?>
