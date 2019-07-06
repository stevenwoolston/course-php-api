<?php
class Delivery {

    private $conn;
    private $table_name = "delivery";

    public $Id;
    public $InvoiceId;
    public $DateDelivered;
    public $DeliveredTo;

    public function __construct($db) {
        $this->conn = $db;
    }

    function read($invoiceId) {

        $query = "SELECT Id, InvoiceId, DateDelivered, DeliveredTo
            FROM " . $this->table_name . " d
            WHERE d.InvoiceId = " . $invoiceId . "
            ORDER BY d.DateDelivered DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function read_one($id) {

        $query = "SELECT Id, InvoiceId, DateDelivered, DeliveredTo
            FROM " . $this->table_name . " d
            WHERE d.Id = " . $id;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function sanitize() {
        $this->DeliveredTo = htmlspecialchars(strip_tags($this->DeliveredTo));
        return $this;
    }

    function create() {

        $query = "INSERT INTO " . $this->table_name . "
            SET
                InvoiceId = :InvoiceId, DateDelivered = :DateDelivered, DeliveredTo = :DeliveredTo";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":InvoiceId", $this->InvoiceId);
        $stmt->bindParam(":DateDelivered", $this->DateDelivered);
        $stmt->bindParam(":DeliveredTo", $this->DeliveredTo);

        if ($stmt->execute()) {
            $this->Id = $this->conn->lastInsertId();
            return $this->Id;
        }

        return false;
    }

    function update($id) {

        $this->Id = $id;

        $query = "UPDATE " . $this->table_name . "
            SET 
                InvoiceId = :InvoiceId, DateDelivered = :DateDelivered, DeliveredTo = :DeliveredTo
            WHERE Id = :Id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":Id", $this->Id);
        $stmt->bindParam(":InvoiceId", $this->InvoiceId);
        $stmt->bindParam(":DateDelivered", $this->DateDelivered);
        $stmt->bindParam(":DeliveredTo", $this->DeliveredTo);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    function delete($id) {

        $this->Id = $id;

        $query = "DELETE FROM " . $this->table_name . " WHERE Id = :Id";
        $stmt = $this->conn-prepare($query);
        $stmt->bindParam(":Id", $this->Id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
}