<?php
class Customer {

    private $conn;
    private $table_name = "customer";

    public $Id;
    public $Name;
    public $IsVisible;
    public $Address;
    public $Suburb;
    public $State;
    public $Postcode;
    public $InvoicingText;

    public function __construct($db) {
        $this->conn = $db;
    }

    function read() {

        $query = "SELECT Id, Name, IsVisible, Address, Suburb, State, Postcode, InvoicingText
            FROM " . $this->table_name . " c
            ORDER BY c.Name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function read_one($id) {

        $query = "SELECT Id, Name, IsVisible, Address, Suburb, State, Postcode, InvoicingText
            FROM " . $this->table_name . " c
            WHERE c.Id = " . $id;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function create() {

        $query = "INSERT INTO " . $this->table_name . "
            SET 
                Name = :Name, IsVisible = :IsVisible, Address = :Address,
                Suburb = :Suburb, State = :State, Postcode = :Postcode, 
                InvoicingText = :InvoicingText";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":Name", $this->Name);
        $stmt->bindParam(":IsVisible", $this->IsVisible);
        $stmt->bindParam(":Address", $this->Address);
        $stmt->bindParam(":Suburb", $this->Suburb);
        $stmt->bindParam(":State", $this->State);
        $stmt->bindParam(":Postcode", $this->Postcode);
        $stmt->bindParam(":InvoicingText", $this->InvoicingText);

        if ($stmt->execute()) {
            $this->Id = $this->conn->lastInsertId();
            return $this->Id;
        }

        return false;
    }
}