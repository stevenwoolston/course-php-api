<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: authorization, content-type, accept, origin");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, HEAD, OPTIONS, POST, PUT, DELETE");

include_once "../config/database.php";
include_once "../objects/invoiceitem.php";

$database = new Database();
$db = $database->getConnection();
$invoiceitem = new InvoiceItem($db);
$data = json_decode(file_get_contents("php://input"), true);

if (!empty($_GET["invoiceid"])) {
    $invoiceId = intval($_GET["invoiceid"]);
}

if (!empty($_GET["id"])) {
    $id = intval($_GET["id"]);
}

$request_method = $_SERVER["REQUEST_METHOD"];
switch ($request_method) {

    case 'GET': 
        if (empty($id) && empty($invoiceId)) {
            http_response_code(404);
            echo json_encode(array("message" => "No record found. You did not supply an Id or InvoiceId."));
            die();
        }

        if (empty($id)) {
            $stmt = $invoiceitem->read($invoiceId);
        } else {
            $stmt = $invoiceitem->read_one($id);
        }

        if (empty($stmt)) die();

        $num = $stmt->rowCount();
        $results_arr = array("totalRecords" => $num, "data" => array());

        if ($num > 0) {
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $results_item = array(
                    "Id" => $Id,
                    "InvoiceId" => $InvoiceId,
                    "Sequence" => $Sequence,
                    "Description" => $Description,
                    "Cost" => $Cost
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

        $invoice->Id = $id;
        $invoice->InvoiceId = $data["InvoiceId"];
        $invoice->Sequence = $data["Sequence"];
        $invoice->Description = $data["Description"];
        $invoice->Cost = $data["Cost"];

        $status = $invoiceitem->sanitize()->update($id);
        if ($status) {
            http_response_code(200);
            echo json_encode(array("message" => "Record updated successfully.", "data" => $invoice));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Unable to update record."));
        }

        die();
        break;

    case 'POST': 

        $invoice->CustomerId = $data["CustomerId"];
        $invoice->InvoiceDate = $data["InvoiceDate"];
        $invoice->InvoiceDueDate = $data["InvoiceDueDate"];
        $invoice->EmailSubject = $data["EmailSubject"];
        $invoice->DateSent = $data["DateSent"];
        $invoice->DatePaid = $data["DatePaid"];
        $invoice->IsCanceled = $data["IsCanceled"];
        $invoice->TotalCost = $data["TotalCost"];
        $invoice->TotalPayments = $data["TotalPayments"];
        $invoice->CustomerName = $data["CustomerName"];
        $invoice->IsVisible = $data["IsVisible"];

        $status = $invoice->sanitize()->create();
        if ($status) {
            http_response_code(200);
            echo json_encode(array("message" => "Record created successfully.", "data" => $invoice));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Unable to create record."));
        }

        die();
        break;
    case 'DELETE':
        
        if ($invoice->delete($id)) {
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