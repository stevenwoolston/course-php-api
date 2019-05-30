<?php
class Customer {
 
    // database connection and table name
    private $conn;
    private $table_name = "customer";
 
    // object properties
    public $Id;
	public $Name;
	public $IsVisible;
    public $Address;
	public $Suburb;
	public $State;
    public $Postcode;
    public $InvoicingText;
 
    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
	}
	
	// read products
	function read() {
	
		// select all query
			$query = "SELECT Id, Name, IsVisible, Address, Suburb, State, Postcode, InvoicingText 
				FROM
					" . $this->table_name . " c
				ORDER BY
					c.name ASC";
	
		// prepare query statement
		$stmt = $this->conn->prepare($query);
	
		// execute query
		$stmt->execute();
	
		return $stmt;
	}

	//	read single product
	function read_one($id) {
	
		// select all query
		$query = "SELECT Id, Name, IsVisible, Address, Suburb, State, Postcode, InvoicingText 
		FROM
					" . $this->table_name . " c
				WHERE
					Id = " . $id . "
				ORDER BY
					c.name ASC";
	
		// prepare query statement
		$stmt = $this->conn->prepare($query);
	
		// execute query
		$stmt->execute();
		
		return $stmt;
	}

	function create(){
	
		$data = json_decode(file_get_contents('php://input'), true);

		$this->Name = $data["Name"];
		$this->Address = $data["Address"];
		$this->Suburb = $data["Suburb"];
		$this->State = $data["State"];
		$this->Postcode = $data["Postcode"];
		$this->InvoicingText = $data["InvoicingText"];
		$this->IsVisible = $data["IsVisible"];

		$query = "INSERT INTO
					" . $this->table_name . "
				SET
					Name=:Name, IsVisible=:IsVisible, Address=:Address, 
					Suburb=:Suburb, State=:State, Postcode=:Postcode, 
					InvoicingText=:InvoicingText";
	
		// prepare query
		$stmt = $this->conn->prepare($query);
        
		// sanitize
		$this->Name=htmlspecialchars(strip_tags($this->Name));
		$this->IsVisible=htmlspecialchars(strip_tags($this->IsVisible));
		$this->Address=htmlspecialchars(strip_tags($this->Address));
		$this->Suburb=htmlspecialchars(strip_tags($this->Suburb));
		$this->State=htmlspecialchars(strip_tags($this->State));
		$this->Postcode=htmlspecialchars(strip_tags($this->Postcode));
		$this->InvoicingText=htmlspecialchars(strip_tags($this->InvoicingText));
	
        // bind values
		$stmt->bindParam(":Name", $this->Name);
		$stmt->bindParam(":IsVisible", $this->IsVisible);
		$stmt->bindParam(":Address", $this->Address);
		$stmt->bindParam(":Suburb", $this->Suburb);
		$stmt->bindParam(':State', $this->State);
		$stmt->bindParam(":Postcode", $this->Postcode);
		$stmt->bindParam(":InvoicingText", $this->InvoicingText);
	
		// execute query
		if ($stmt->execute()) {
            $this->Id=$this->conn->lastInsertId();
			return $this->Id;
		}
	
		return false;
		
	}

	function update($id) {
		$data = json_decode(file_get_contents('php://input'), true);

        $this->Id = $id;
		$this->Name = $data["Name"];
		$this->Address = $data["Address"];
		$this->Suburb = $data["Suburb"];
		$this->State = $data["State"];
		$this->Postcode = $data["Postcode"];
		$this->InvoicingText = $data["InvoicingText"];
		$this->IsVisible = $data["IsVisible"];

		$query = "UPDATE
					" . $this->table_name . "
				SET
					Name = :Name,
					Address = :Address,
					Suburb = :Suburb,
					State = :State,
					Postcode = :Postcode,
					InvoicingText = :InvoicingText,
					IsVisible = :IsVisible
				WHERE
					Id = :Id";
	
		// prepare query statement
		$stmt = $this->conn->prepare($query);
	
		// sanitize
		$this->Name=htmlspecialchars(strip_tags($this->Name));
		$this->Address=htmlspecialchars(strip_tags($this->Address));
		$this->Suburb=htmlspecialchars(strip_tags($this->Suburb));
		$this->State=htmlspecialchars(strip_tags($this->State));
		$this->Postcode=htmlspecialchars(strip_tags($this->Postcode));
		$this->InvoicingText=htmlspecialchars(strip_tags($this->InvoicingText));
		$this->IsVisible=htmlspecialchars(strip_tags($this->IsVisible));
	
        // bind values
        $stmt->bindParam(":Id", $this->Id);
		$stmt->bindParam(":Name", $this->Name);
		$stmt->bindParam(":IsVisible", $this->IsVisible);
		$stmt->bindParam(":Address", $this->Address);
		$stmt->bindParam(":Suburb", $this->Suburb);
		$stmt->bindParam(':State', $this->State);
		$stmt->bindParam(":Postcode", $this->Postcode);
		$stmt->bindParam(":InvoicingText", $this->InvoicingText);
	
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