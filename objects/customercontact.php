<?php
class CustomerContact {
 
    // database connection and table name
    private $conn;
    private $table_name = "customercontact";
 
    // object properties
    public $Id;
	public $FirstName;
    public $Surname;
    public $EmailAddress;
    public $CustomerId;
	public $IsVisible;
	public $CustomerName;
 
    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
	}
	
	// read products
	function read($customerId){
	
		// select all query
		$query = "SELECT c.Name 'CustomerName', cc.*
				FROM
					" . $this->table_name . " cc
				INNER JOIN customer c ON c.Id = cc.CustomerId
				WHERE cc.CustomerId = " . $customerId . "
				ORDER BY
					cc.FirstName ASC";
	
		// prepare query statement
		$stmt = $this->conn->prepare($query);

		// execute query
		$stmt->execute();
	
		return $stmt;
	}

	//	read single product
	function read_one($id) {
	
		// select all query
		$query = "SELECT c.Name 'CustomerName', cc.*
				FROM
					" . $this->table_name . " cc
				INNER JOIN customer c ON c.Id = cc.CustomerId
				WHERE
					cc.Id = " . $id . "
				ORDER BY
					cc.FirstName DESC";
	
		// prepare query statement
		$stmt = $this->conn->prepare($query);
	
		// execute query
		$stmt->execute();
	
		return $stmt;
	}

	function create(){
	
		$data = json_decode(file_get_contents('php://input'), true);

		$this->FirstName = $data["FirstName"];
		$this->Surname = $data["Surname"];
		$this->EmailAddress = $data["EmailAddress"];
		$this->CustomerId = $data["CustomerId"];
		$this->IsVisible = $data["IsVisible"];

		// query to insert record
		$query = "INSERT INTO
					" . $this->table_name . "
				SET
					FirstName=:FirstName, Surname=:Surname, 
					EmailAddress=:EmailAddress, CustomerId=:CustomerId,
					IsVisible=:IsVisible";
	
		// prepare query
		$stmt = $this->conn->prepare($query);
	
		// sanitize
		$this->FirstName=htmlspecialchars(strip_tags($this->FirstName));
		$this->Surname=htmlspecialchars(strip_tags($this->Surname));
		$this->EmailAddress=htmlspecialchars(strip_tags($this->EmailAddress));
		$this->CustomerId=htmlspecialchars(strip_tags($this->CustomerId));
		$this->IsVisible=htmlspecialchars(strip_tags($this->IsVisible));
	
		// bind values
		$stmt->bindParam(":FirstName", $this->FirstName);
		$stmt->bindParam(":Surname", $this->Surname);
		$stmt->bindParam(":EmailAddress", $this->EmailAddress);
		$stmt->bindParam(":CustomerId", $this->CustomerId);
		$stmt->bindParam(":IsVisible", $this->IsVisible);
	
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
		$this->FirstName = $data["FirstName"];
		$this->Surname = $data["Surname"];
		$this->EmailAddress = $data["EmailAddress"];
		$this->CustomerId = $data["CustomerId"];
		$this->IsVisible = $data["IsVisible"];

		// update query
		$query = "UPDATE
					" . $this->table_name . "
				SET
					FirstName = :FirstName,
					Surname = :Surname,
					EmailAddress = :EmailAddress,
					CustomerId = :CustomerId,
					IsVisible = :IsVisible
				WHERE
					Id = :Id";
	
		// prepare query statement
		$stmt = $this->conn->prepare($query);
	
		// sanitize
		$this->Id=htmlspecialchars(strip_tags($this->Id));
		$this->FirstName=htmlspecialchars(strip_tags($this->FirstName));
		$this->Surname=htmlspecialchars(strip_tags($this->Surname));
		$this->EmailAddress=htmlspecialchars(strip_tags($this->EmailAddress));
		$this->CustomerId=htmlspecialchars(strip_tags($this->CustomerId));
		$this->IsVisible=htmlspecialchars(strip_tags($this->IsVisible));
	
		// bind new values
		$stmt->bindParam(':Id', $this->Id);
		$stmt->bindParam(':FirstName', $this->FirstName);
		$stmt->bindParam(':Surname', $this->Surname);
		$stmt->bindParam(':EmailAddress', $this->EmailAddress);
		$stmt->bindParam(':CustomerId', $this->CustomerId);
		$stmt->bindParam(':IsVisible', $this->IsVisible);
	
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