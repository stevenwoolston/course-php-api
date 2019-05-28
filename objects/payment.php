<?php
class Payment {
 
    // database connection and table name
    private $conn;
    private $table_name = "payment";
 
    // object properties
    public $Id;
	public $InvoiceId;
	public $DatePaid;
    public $Amount;

    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
	}
	
	// read products
	function read($invoiceId){
	
		// select all query
		$query = "SELECT Id, InvoiceId, DatePaid, Amount
				FROM
					" . $this->table_name . " c
				WHERE c.InvoiceId = " . $invoiceId . "
				ORDER BY
					c.DatePaid DESC";
	
		// prepare query statement
		$stmt = $this->conn->prepare($query);
	
		// execute query
		$stmt->execute();
		
		return $stmt;
	}

	//	read single product
	function read_one($id) {
	
		// select all query
		$query = "SELECT Id, InvoiceId, DatePaid, Amount
				FROM
					" . $this->table_name . " c
				WHERE
					Id = " . $id . "
				ORDER BY
					c.DatePaid DESC";
	
		// prepare query statement
		$stmt = $this->conn->prepare($query);
	
		// execute query
		$stmt->execute();
	
		return $stmt;
	}

	function create(){
	
		$data = json_decode(file_get_contents('php://input'), true);

		$this->InvoiceId = $data["InvoiceId"];
		$this->DatePaid = $data["DatePaid"];
		$this->Amount = $data["Amount"];

		// query to insert record
		$query = "INSERT INTO " . $this->table_name . "
				(InvoiceId, DatePaid, Amount)
				VALUES (:InvoiceId, :DatePaid, :Amount)";
	
		// prepare query
		$stmt = $this->conn->prepare($query);
	
        // sanitize
		$this->InvoiceId=htmlspecialchars(strip_tags($this->InvoiceId));
		$this->DatePaid=htmlspecialchars(strip_tags($this->DatePaid));
		$this->Amount=htmlspecialchars(strip_tags($this->Amount));
	
        // bind values
		$stmt->bindParam(":InvoiceId", $this->InvoiceId);
		$stmt->bindParam(":DatePaid", $this->DatePaid);
		$stmt->bindParam(":Amount", $this->Amount);
	
		// execute query
		if ($stmt->execute()) {
            $this->Id=$this->conn->lastInsertId();
			return $this->Id;
		}
	
		return false;
		
	}

	// update the product
	function update($id) {
		$data = json_decode(file_get_contents('php://input'), true);

        $this->Id = $id;
		$this->InvoiceId = $data["InvoiceId"];
		$this->DatePaid = $data["DatePaid"];
		$this->Amount = $data["Amount"];

		// update query
		$query = "UPDATE
					" . $this->table_name . "
				SET
					InvoiceId=:InvoiceId, DatePaid=:DatePaid, Amount=:Amount
				WHERE
					Id = :Id";
	
		// prepare query statement
		$stmt = $this->conn->prepare($query);
	
		// sanitize
		$this->Id=htmlspecialchars(strip_tags($this->Id));
		$this->InvoiceId=htmlspecialchars(strip_tags($this->InvoiceId));
		$this->DatePaid=htmlspecialchars(strip_tags($this->DatePaid));
		$this->Amount=htmlspecialchars(strip_tags($this->Amount));

		if ($this->DatePaid == "0000-00-00") {
			$this->DatePaid = null;
		}
		
		// bind new values
        $stmt->bindParam(":Id", $this->Id);
		$stmt->bindParam(":InvoiceId", $this->InvoiceId);
		$stmt->bindParam(":DatePaid", $this->DatePaid);
		$stmt->bindParam(":Amount", $this->Amount);

		// execute the query
		if ($stmt->execute()) {
			return true;
		}
	
		return false;
	}

	function delete($id){
	
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