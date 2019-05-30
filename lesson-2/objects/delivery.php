<?php
class Delivery {
 
    // database connection and table name
    private $conn;
    private $table_name = "delivery";
 
    // object properties
    public $Id;
	public $InvoiceId;
	public $DateDelivered;
    public $DeliveredTo;

    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
	}
	
	// read products
	function read($invoiceId){
	
		// select all query
		$query = "SELECT Id, InvoiceId, DateDelivered, DeliveredTo
				FROM
					" . $this->table_name . " c
				WHERE c.InvoiceId = " . $invoiceId . "
				ORDER BY
					c.DateDelivered DESC";
	
		// prepare query statement
		$stmt = $this->conn->prepare($query);
	
		// execute query
		$stmt->execute();
		
		return $stmt;
	}

	function read_queued(){
	
		// select all query
		$query = "SELECT Id, InvoiceId, DateDelivered, DeliveredTo
				FROM
					" . $this->table_name . " c
				WHERE c.DateDelivered IS NULL
				ORDER BY
					c.Id DESC";
	
		// prepare query statement
		$stmt = $this->conn->prepare($query);
	
		// execute query
		$stmt->execute();
		
		return $stmt;
	}

	//	read single product
	function read_one($id) {
	
		// select all query
		$query = "SELECT Id, InvoiceId, DateDelivered, DeliveredTo
				FROM
					" . $this->table_name . " c
				WHERE
					Id = " . $id;
	
		// prepare query statement
		$stmt = $this->conn->prepare($query);
	
		// execute query
		$stmt->execute();
	
		return $stmt;
	}

	function create(){
	
		$data = json_decode(file_get_contents('php://input'), true);

		$this->InvoiceId = $data["InvoiceId"];
		$this->DeliveredTo = $data["DeliveredTo"];

		// query to insert record
		$query = "INSERT INTO " . $this->table_name . "
				(InvoiceId, DeliveredTo)
				VALUES (:InvoiceId, :DeliveredTo)";
	
		// prepare query
		$stmt = $this->conn->prepare($query);
	
        // sanitize
		$this->InvoiceId=htmlspecialchars(strip_tags($this->InvoiceId));
		$this->DeliveredTo=htmlspecialchars(strip_tags($this->DeliveredTo));
	
        // bind values
		$stmt->bindParam(":InvoiceId", $this->InvoiceId);
		$stmt->bindParam(":DeliveredTo", $this->DeliveredTo);
	
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
		$this->DateDelivered = $data["DateDelivered"];
		$this->DeliveredTo = $data["DeliveredTo"];

		// update query
		$query = "UPDATE
					" . $this->table_name . "
				SET
					InvoiceId=:InvoiceId, DateDelivered=:DateDelivered, DeliveredTo=:DeliveredTo
				WHERE
					Id = :Id";
	
		// prepare query statement
		$stmt = $this->conn->prepare($query);
	
		// sanitize
		$this->Id=htmlspecialchars(strip_tags($this->Id));
		$this->InvoiceId=htmlspecialchars(strip_tags($this->InvoiceId));
		$this->DateDelivered=htmlspecialchars(strip_tags($this->DateDelivered));
		$this->DeliveredTo=htmlspecialchars(strip_tags($this->DeliveredTo));

		if ($this->DateDelivered == "0000-00-00") {
			$this->DateDelivered = null;
		}
		
		// bind new values
        $stmt->bindParam(":Id", $this->Id);
		$stmt->bindParam(":InvoiceId", $this->InvoiceId);
		$stmt->bindParam(":DateDelivered", $this->DateDelivered);
		$stmt->bindParam(":DeliveredTo", $this->DeliveredTo);

		// execute the query
		if ($stmt->execute()) {
			return true;
		}
	
		return false;
	}
		
	function updateDeliveryDate($id) {

        $this->Id = $id;

		// update query
		$query = "UPDATE
					" . $this->table_name . "
				SET
					DateDelivered=NOW()
				WHERE
					Id = :Id";
	
		// prepare query statement
		$stmt = $this->conn->prepare($query);
		
		// bind new values
        $stmt->bindParam(":Id", $this->Id);

		// execute the query
		if ($stmt->execute()) {
			return $stmt;
		}
	
		return $stmt;
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