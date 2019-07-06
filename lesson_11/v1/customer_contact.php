<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: authorization, content-type, accept, origin");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, HEAD, OPTIONS, POST, PUT, DELETE");

include_once "../config/database.php";
include_once "../objects/customercontact.php";

$database = new Database();
$db = $database->getConnection();
$customercontact = new CustomerContact($db);
$data = json_decode(file_get_contents("php://input"), true);

if (!empty($_GET["customerid"])) {
    $customerId = intval($_GET["customerid"]);
}

if (!empty($_GET["id"])) {
    $id = intval($_GET["id"]);
}

$request_method = $_SERVER["REQUEST_METHOD"];
switch ($request_method) {

    case 'GET': 
        if (empty($id) && empty($customerId)) {
            http_response_code(404);
            echo json_encode(array("message" => "No record found. You did not supply an Id or CustomerId."));
            die();
        }

        if (empty($id)) {
            $stmt = $customercontact->read($customerId);
        } else {
            $stmt = $customercontact->read_one($id);
        }

        if (empty($stmt)) die();

        $num = $stmt->rowCount();
        $results_arr = array("totalRecords" => $num, "data" => array());

        if ($num > 0) {
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $results_item = array(
                    "Id" => $Id,
                    "FirstName" => $FirstName,
                    "Surname" => $Surname,
                    "EmailAddress" => $EmailAddress,
                    "CustomerId" => $CustomerId,
                    "CustomerName" => $CustomerName,
                    "IsVisible" => (bool)$IsVisible
                );
                array_push($results_arr["data"], $results_item);
            }
        }

        http_response_code(200);
        echo json_encode($results_arr, JSON_NUMERIC_CHECK);
        break;

    case 'PUT':

        if (empty($id)) {
            http_response_code(404);
            echo json_encode(array("message" => "No record found. You did not supply an Id."));
            die();
        }

        $customercontact->Id = $id;
        $customercontact->FirstName = $data["FirstName"];
        $customercontact->Surname = $data["Surname"];
        $customercontact->EmailAddress = $data["EmailAddress"];
        $customercontact->CustomerId = $data["CustomerId"];
        $customercontact->IsVisible = $data["IsVisible"];

        $status = $customercontact->sanitize()->update($id);
        if ($status) {
            http_response_code(200);
            echo json_encode(array("message" => "Record updated successfully.", "data" => $customercontact));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Unable to update record."));
        }

        die();
        break;

    case 'POST': 

        $customercontact->FirstName = $data["FirstName"];
        $customercontact->Surname = $data["Surname"];
        $customercontact->EmailAddress = $data["EmailAddress"];
        $customercontact->CustomerId = $data["CustomerId"];
        $customercontact->IsVisible = $data["IsVisible"];

        $status = $customercontact->sanitize()->create();
        if ($status) {
            http_response_code(200);
            echo json_encode(array("message" => "Record created successfully.", "data" => $customercontact));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Unable to create record."));
        }

        die();
        break;
    case 'DELETE':
        
        if ($customercontact->delete($id)) {
            http_response_code(200);
            echo json_encode(array("message" => "Record deleted successfully."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Unable to delete record."));
        }

        die();
        break;
    
    case 'OPTIONS':
        http_response_code(200);
        die();
        break;
    
    default:
        header("HTTP/1.0 405 Method Not Allowed");
        die();
        break;
    
}