<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: authorization, content-type, accept, origin");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, HEAD, OPTIONS, POST, PUT, DELETE");

include_once "../config/database.php";
include_once "../objects/customer.php";

$database = new Database();
$db = $database->getConnection();
$customer = new Customer($db);
$data = json_decode(file_get_contents("php://input"), true);

if (!empty($_GET["id"])) {
    $id = intval($_GET["id"]);
}

$request_method = $_SERVER["REQUEST_METHOD"];
switch ($request_method) {

    case 'GET':
        if (empty($id)) {
            $stmt = $customer->read();
        } else {
            $stmt = $customer->read_one($id);
        }
}