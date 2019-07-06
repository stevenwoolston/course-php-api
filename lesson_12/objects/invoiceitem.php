<?php
class InvoiceItem {

    private $conn;
    private $table_name = "invoiceitem";

    public $Id;
    public $InvoiceId;
    public $Sequence;
    public $Description;
    public $Cost;

    public function __construct($db) {
        $this->conn = $db;
    }

    function read($invoiceId) {

        $query = "SELECT Id, InvoiceId, Sequence, Description, Cost
            FROM " . $this->table_name . " ii
            WHERE ii.InvoiceId = " . $invoiceId . "
            ORDER BY ii.Sequence ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function read_one($id) {

        $query = "SELECT Id, InvoiceId, Sequence, Description, Cost
            FROM " . $this->table_name . " ii
            WHERE ii.Id = " . $id;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function sanitize() {
        $this->Description = htmlspecialchars(strip_tags($this->Description));
        $this->Cost = htmlspecialchars(strip_tags($this->Cost));
        return $this;
    }

    function create() {

        $query = "INSERT INTO " . $this->table_name . "
            SET
                InvoiceId = :InvoiceId, Sequence = :Sequence, Description = :Description, Cost = :Cost";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":InvoiceId", $this->InvoiceId);
        $stmt->bindParam(":Sequence", $this->Sequence);
        $stmt->bindParam(":Description", $this->Description);
        $stmt->bindParam(":Cost", $this->Cost);

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
                InvoiceId = :InvoiceId, Sequence = :Sequence, Description = :Description, Cost = :Cost
            WHERE Id = :Id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":Id", $this->Id);
        $stmt->bindParam(":Sequence", $this->Sequence);
        $stmt->bindParam(":Description", $this->Description);
        $stmt->bindParam(":Cost", $this->Cost);

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