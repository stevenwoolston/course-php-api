<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
 
include_once '../config/database.php';
include_once '../inc/fpdf.php';
include_once '../config/config.php';
include_once '../objects/customer.php';
include_once '../objects/customercontact.php';
include_once '../objects/invoice.php';
include_once '../objects/invoiceitem.php';
include_once '../objects/delivery.php';
 
$database = new Database();
$db = $database->getConnection();

$delivery = new Delivery($db);

//  get deliveries to be queued
$deliveries_queued = array();
$deliveries_stmt = $delivery->read_queued();
$num = $deliveries_stmt->rowCount();
if ($num == 0) {
    http_response_code(200);
    echo json_encode(array("message" => "There were no deliveries queued to send."));
    die();
}

//  write deliveries to an array
while ($row = $deliveries_stmt->fetch(PDO::FETCH_ASSOC)){
    extract($row);
    $delivery_item = array(
        "Id" => $Id,
        "InvoiceId" => $InvoiceId,
        "DateDelivered" => $DateDelivered,
        "DeliveredTo" => $DeliveredTo
    );
    array_push($deliveries_queued, $delivery_item);
}

$customer = new Customer($db);
$invoice = new Invoice($db);
$invoiceitem = new InvoiceItem($db);
$customercontact = new CustomerContact($db);

//  iterate over the deliveries queue
foreach($deliveries_queued as $delivery_queued) {

    $data = array(
        "Customer" => array(),
        "CustomerContact" => array(),
        "Invoice" => array(),
        "InvoiceItems" => array()
    );

    $id = $delivery_queued["Id"];
    $invoiceId = $delivery_queued["InvoiceId"];

    //  Get Contact
    $contactId = $delivery_queued["DeliveredTo"];
    $contact_stmt = $customercontact->read_one($contactId);
    $num = $contact_stmt->rowCount();
    if ($num == 0) {
        http_response_code(404);
        echo json_encode(array("message" => "FAILED. There was no contacts configured to send for Invoice Id " . $invoiceId));
        die();
    }

    while ($row = $contact_stmt->fetch(PDO::FETCH_ASSOC)){
        extract($row);
        $contact_item = array(
            "Id" => $Id,
            "CustomerId" => $CustomerId,
            "FirstName" => $FirstName,
            "Surname" => $Surname,
            "EmailAddress" => $EmailAddress
        );
        array_push($data["CustomerContact"], $contact_item);
    }

    //  Get Invoice
    $invoice_stmt = $invoice->read_one($invoiceId);
    $num = $invoice_stmt->rowCount();
    if ($num == 0) {
        http_response_code(404);
        echo json_encode(array("message" => "FAILED. There was no invoice associated with Invoice Id " . $invoiceId));
        die();
    }

    while ($row = $invoice_stmt->fetch(PDO::FETCH_ASSOC)){
        extract($row);
        $invoice_item = array(
            "Id" => $Id,
            "CustomerId" => $CustomerId,
            "InvoiceDate" => $InvoiceDate,
            "InvoiceDueDate" => $InvoiceDueDate,
            "EmailSubject" => $EmailSubject,
            "DateSent" => $DateSent,
            "DatePaid" => $DatePaid,
            "IsCanceled" => (bool)$IsCanceled,
            "TotalCost" => $TotalCost,
            "TotalPayments" => $TotalPayments
        );
        array_push($data["Invoice"], $invoice_item);
    }

    //  Get Customer
    $customerId = $data["Invoice"][0]["CustomerId"];
    $customer_stmt = $customer->read_one($customerId);
    $num = $customer_stmt->rowCount();
    if ($num == 0) {
        http_response_code(404);
        echo json_encode(array("message" => "FAILED. There was no customer associated with Invoice Id " . $invoiceId));
        die();
    }

    while ($row = $customer_stmt->fetch(PDO::FETCH_ASSOC)){
        extract($row);
        $customer_item = array(
            "Id" => $Id,
            "Name" => $Name,
            "IsVisible" => (bool)$IsVisible,
            "Address" => $Address,
            "Suburb" => $Suburb,
            "State" => $State,
            "Postcode" => $Postcode,
            "InvoicingText" => $InvoicingText
        );
        array_push($data["Customer"], $customer_item);
    }

    //  Get Invoice Items
    $invoiceitem_stmt = $invoiceitem->read($invoiceId);
    $num = $invoiceitem_stmt->rowCount();
    if ($num == 0) {
        http_response_code(404);
        echo json_encode(array("message" => "FAILED. There were no items associated with Invoice Id " . $invoiceId));
        die();
    }

    while ($row = $invoiceitem_stmt->fetch(PDO::FETCH_ASSOC)){
        extract($row);
        $invoiceitem_item=array(
            "Id" => $Id,
            "InvoiceId" => $InvoiceId,
            "Sequence" => $Sequence,
            "Description" => $Description,
            "Cost" => $Cost
        );
        array_push($data["InvoiceItems"], $invoiceitem_item);
    }

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(40,10,'Hello World!');

    $config = new Config();
    $config->email_to_address = $data["CustomerContact"][0]["EmailAddress"];
    $config->email_to_name = $data["CustomerContact"][0]["FirstName"] . ' ' . $data["CustomerContact"][0]["Surname"];
    $config->email_subject = $data["Invoice"][0]["EmailSubject"];
    $config->smtp_debug = 0;    //  0/1/2
    $config->smtp_attachment = $pdf->Output('', 'S');
    $config->smtp_attachment_name = 'WWD Invoice ' . $invoiceId . '.pdf';

    $body_html = '<p>Hi all.</p>';
    $body_html .= '<p>Thank you for your recent business.</p>';
    $body_html .= '<p>I have attached the invoice number <b>'. $invoiceId . '</b> for the work we did and look forward ';
    $body_html .= 'to continuing to work with you in the future.</p>';
    $body_html .= '<p>Thanks for your business.<br/>';
    $body_html .= '<i>Steven Woolston</i></p>';
    $config->email_body = html_entity_decode($body_html);
    
    // array_push($data, $body_html);
    // array_push($data, $customerId);
    // array_push($data, $config);
    // http_response_code(500);
    // echo json_encode(array("message" => "Invoice Id " . $invoiceId ." successfully delivered", "data" => $data));
    // die();

    if ($config->send_email()) {
        $deliveryDateUpdate = $delivery->updateDeliveryDate($id);
        if ($deliveryDateUpdate) {
            http_response_code(200);
            echo json_encode(array("message" => "Invoice Id " . $invoiceId ." successfully delivered", "data" => $data));
            die();
        }
    }

    http_response_code(500);
    echo json_encode(array("message" => "Unable to send email for Invoice Id " . $invoiceId, "data" => $data));
}
?>