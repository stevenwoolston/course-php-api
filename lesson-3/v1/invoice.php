<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: authorization, content-type, accept, origin");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, HEAD, OPTIONS, POST, PUT, DELETE");

include_once '../config/database.php';
include_once '../objects/invoice.php';

$database = new Database();
$db = $database->getConnection();
$invoice = new Invoice($db);
$data = json_decode(file_get_contents("php://input"));

if (!empty($_GET["customerid"]))
{
    $customerid = intval($_GET["customerid"]);
}

if (!empty($_GET["id"]))
{
    $id = intval($_GET["id"]);
}

$request_method=$_SERVER["REQUEST_METHOD"];
switch($request_method)
{
    case 'GET':
        if (empty($id) && empty($customerid)) {
            http_response_code(404);
            echo json_encode(array("message" => "No record found. You did not supply an Id or CustomerId.", "data" => $invoice));
            die();
        }

        if (empty($id)) {
            $stmt = $invoice->read($customerid);
        } else {
            $stmt = $invoice->read_one($id);
        }

        if (empty($stmt)) die();

        $num = $stmt->rowCount();
        $results_arr = array("totalRecords" => $num, "data" => array());
         
        if ($num > 0) {
        
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                extract($row); 
                $results_item = array(
                    "Id" => $Id,
                    "CustomerId" => $CustomerId,
                    "InvoiceDate" => $InvoiceDate,
                    "InvoiceDueDate" => $InvoiceDueDate,
                    "EmailSubject" => $EmailSubject,
                    "DateSent" => $DateSent,
                    "DatePaid" => $DatePaid,
                    "IsCanceled" => (bool)$IsCanceled,
                    "TotalCost" => $TotalCost,
                    "TotalPayments" => $TotalPayments,
                    "CustomerName" => $CustomerName
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
            echo json_encode(array("message" => "No record found. You did not supply an Id.", "data" => $invoice));
            die();
        }

        $status = $invoice->update($id);
        if ($status) {
            http_response_code(200);
            echo json_encode(array("message" => "Record updated successfully.", "data" => $invoice));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Unable to create record."));
        }
        break;
    case 'POST':
        $status = $invoice->create();
        if ($status) {
            http_response_code(200);
            echo json_encode(array("message" => "Record created successfully.", "data" => $invoice));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => $status));
        }
        die();
		break;
	case 'DELETE':
        if (empty($id)) {
            http_response_code(404);
            echo json_encode(array("message" => "No record found. You did not supply an Id.", "data" => $invoice));
            die();
        }

        if ($invoice->delete($id)) {
            http_response_code(200);
            echo json_encode(array("message" => "Record was deleted."));
        } else{
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
		// Invalid Request Method
		header("HTTP/1.0 405 Method Not Allowed");
		die();
		break;
}
