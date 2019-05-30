<?php
class InvoiceItem {
 
    // database connection and table name
    private $conn;
    private $table_name = "invoiceitem";
 
    // object properties
    public $Id;
	public $InvoiceId;
	public $Sequence;
    public $Description;
    public $Cost;

    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
	}
	
	// read products
	function read($invoiceId){
	
		// select all query
		$query = "SELECT Id, InvoiceId, Sequence, Description, Cost
				FROM
					" . $this->table_name . " c
				WHERE c.InvoiceId = " . $invoiceId . "
				ORDER BY
					c.Sequence ASC";
	
		// prepare query statement
		$stmt = $this->conn->prepare($query);
	
		// execute query
		$stmt->execute();
	
		return $stmt;
	}

	//	read single product
	function read_one($id) {
	
		// select all query
		$query = "SELECT Id, InvoiceId, Sequence, Description, Cost
				FROM
					" . $this->table_name . " c
				WHERE
					Id = " . $id . "
				ORDER BY
					c.InvoiceId, c.Sequence ASC";
	
		// prepare query statement
		$stmt = $this->conn->prepare($query);
	
		// execute query
		$stmt->execute();
	
		return $stmt;
	}

	// create product
	function create(){
	
		$data = json_decode(file_get_contents('php://input'), true);

		$this->InvoiceId = $data["InvoiceId"];
		$this->Sequence = $data["Sequence"];
		$this->Description = $data["Description"];
		$this->Cost = $data["Cost"];

		// query to insert record
		$query = "INSERT INTO " . $this->table_name . "
				(InvoiceId, Sequence, Description, Cost)
				VALUES (:InvoiceId, :Sequence, :Description, :Cost)";
	
		// prepare query
		$stmt = $this->conn->prepare($query);
	
        // sanitize
		$this->InvoiceId=htmlspecialchars(strip_tags($this->InvoiceId));
		$this->Sequence=htmlspecialchars(strip_tags($this->Sequence));
		$this->Description=htmlspecialchars(strip_tags($this->Description));
		$this->Cost=htmlspecialchars(strip_tags($this->Cost));
	
        // bind values
		$stmt->bindParam(":InvoiceId", $this->InvoiceId);
		$stmt->bindParam(":Sequence", $this->Sequence);
		$stmt->bindParam(":Description", $this->Description);
		$stmt->bindParam(":Cost", $this->Cost);
	
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
		$this->Sequence = $data["Sequence"];
		$this->Description = $data["Description"];
		$this->Cost = $data["Cost"];

		// update query
		$query = "UPDATE
					" . $this->table_name . "
				SET
                InvoiceId = :InvoiceId,
                Sequence = :Sequence,
                Description = :Description,
                Cost = :Cost
				WHERE
					Id = :Id";
	
		// prepare query statement
		$stmt = $this->conn->prepare($query);
	
		// sanitize
		$this->Id=htmlspecialchars(strip_tags($this->Id));
		$this->InvoiceId=htmlspecialchars(strip_tags($this->InvoiceId));
		$this->Sequence=htmlspecialchars(strip_tags($this->Sequence));
		$this->Description=htmlspecialchars(strip_tags($this->Description));
		$this->Cost=htmlspecialchars(strip_tags($this->Cost));
	
		// bind new values
        $stmt->bindParam(":Id", $this->Id);
		$stmt->bindParam(":InvoiceId", $this->InvoiceId);
		$stmt->bindParam(":Sequence", $this->Sequence);
		$stmt->bindParam(":Description", $this->Description);
		$stmt->bindParam(":Cost", $this->Cost);
	
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