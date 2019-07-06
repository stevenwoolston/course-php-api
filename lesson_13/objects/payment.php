<?php
class Payment {

    private $conn;
    private $table_name = "payment";

    public $Id;
    public $InvoiceId;
    public $DatePaid;
    public $Amount;

    public function __construct($db) {
        $this->conn = $db;
    }

    function read($invoiceId) {

        $query = "SELECT Id, InvoiceId, DatePaid, Amount
            FROM " . $this->table_name . " p
            WHERE p.InvoiceId = " . $invoiceId . "
            ORDER BY p.DatePaid DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function read_one($id) {

        $query = "SELECT Id, InvoiceId, DatePaid, Amount
            FROM " . $this->table_name . " p
            WHERE p.Id = " . $id;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function sanitize() {
        $this->DatePaid = htmlspecialchars(strip_tags($this->DatePaid));
        $this->Amount = htmlspecialchars(strip_tags($this->Amount));
        return $this;
    }

    function create() {

        $query = "INSERT INTO " . $this->table_name . "
            SET
                InvoiceId = :InvoiceId, DatePaid = :DatePaid, Amount = :Amount";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":InvoiceId", $this->InvoiceId);
        $stmt->bindParam(":DatePaid", $this->DatePaid);
        $stmt->bindParam(":Amount", $this->Amount);

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
                InvoiceId = :InvoiceId, DatePaid = :DatePaid, Amount = :Amount
            WHERE Id = :Id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":Id", $this->Id);
        $stmt->bindParam(":InvoiceId", $this->InvoiceId);
        $stmt->bindParam(":DatePaid", $this->DatePaid);
        $stmt->bindParam(":Amount", $this->Amount);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    function delete($id) {

        $this->Id = $id;

        $query = "DELETE FROM " . $this->table_name . " WHERE Id = :Id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":Id", $this->Id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
}