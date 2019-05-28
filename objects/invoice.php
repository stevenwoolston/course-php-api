<?php
class Invoice {
 
    // database connection and table name
    private $conn;
    private $table_name = "invoice";
 
    // object properties
	public $Id;
	public $CustomerId;
	public $InvoiceDate;
    public $InvoiceDueDate;
    public $EmailSubject;
    public $DateSent;
    public $DatePaid;
	public $IsCanceled;
	public $TotalCost;
	public $TotalPayments;
	public $CustomerName;

    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
	}
	
	// read products
	function read($customerId){
	
		// select all query
		$query = "SELECT i.Id, CustomerId, InvoiceDate, InvoiceDueDate, EmailSubject, 
				DateSent, i.DatePaid, IsCanceled, 
                COALESCE(ii.TotalCost, 0) TotalCost,
                COALESCE(p.TotalPayments, 0) TotalPayments,
				c.Name CustomerName
				FROM " . $this->table_name . " i
				INNER JOIN Customer c ON c.Id = i.CustomerId
                LEFT JOIN ( SELECT InvoiceId, SUM(Amount) TotalPayments FROM payment GROUP BY InvoiceId) p ON p.InvoiceId = i.Id
                LEFT JOIN ( SELECT InvoiceId, SUM(Cost) TotalCost FROM invoiceitem GROUP BY InvoiceId) ii ON ii.InvoiceId = i.Id
				GROUP BY Id, CustomerId, InvoiceDate, InvoiceDueDate, EmailSubject, DateSent, DatePaid, IsCanceled, c.Name
				HAVING CustomerId = " . $customerId . "
				ORDER BY i.InvoiceDate DESC";
	
		// prepare query statement
		$stmt = $this->conn->prepare($query);
	
		// execute query
		$stmt->execute();
	
		return $stmt;
	}

	//	read single product
	function read_one($id) {
	
		// select all query
		$query = "SELECT i.Id, CustomerId, InvoiceDate, InvoiceDueDate, EmailSubject, 
				DateSent, i.DatePaid, IsCanceled, 
                COALESCE(ii.TotalCost, 0) TotalCost,
                COALESCE(p.TotalPayments, 0) TotalPayments,
				c.Name CustomerName
				FROM " . $this->table_name . " i
				INNER JOIN Customer c ON c.Id = i.CustomerId
                LEFT JOIN ( SELECT InvoiceId, SUM(Amount) TotalPayments FROM payment GROUP BY InvoiceId) p ON p.InvoiceId = i.Id
                LEFT JOIN ( SELECT InvoiceId, SUM(Cost) TotalCost FROM invoiceitem GROUP BY InvoiceId) ii ON ii.InvoiceId = i.Id
				GROUP BY Id, CustomerId, InvoiceDate, InvoiceDueDate, EmailSubject, DateSent, DatePaid, IsCanceled, c.Name
				HAVING i.Id = " . $id . "
				ORDER BY i.Id ASC";

				// prepare query statement
		$stmt = $this->conn->prepare($query);
	
		// execute query
		$stmt->execute();
	
		return $stmt;
	}

	function create(){
	
		$data = json_decode(file_get_contents('php://input'), true);

		$this->CustomerId = $data["CustomerId"];
		$this->InvoiceDate = $data["InvoiceDate"];
		$this->InvoiceDueDate = $data["InvoiceDueDate"];
		$this->EmailSubject = $data["EmailSubject"];
		$this->DateSent = $data["DateSent"];
		$this->DatePaid = $data["DatePaid"];
		$this->IsCanceled = $data["IsCanceled"];

		// query to insert record
		$query = "INSERT INTO
					" . $this->table_name . "
				SET
					CustomerId=:CustomerId, InvoiceDate=:InvoiceDate, InvoiceDueDate=:InvoiceDueDate, 
					EmailSubject=:EmailSubject, DateSent=:DateSent, 
					DatePaid=:DatePaid, IsCanceled=:IsCanceled";
	
		// prepare query
		$stmt = $this->conn->prepare($query);
	
        // sanitize
		$this->CustomerId=htmlspecialchars(strip_tags($this->CustomerId));
		$this->EmailSubject=htmlspecialchars(strip_tags($this->EmailSubject));
	
        // bind values
		$stmt->bindParam(":CustomerId", $this->CustomerId);
		$stmt->bindParam(":InvoiceDate", $this->InvoiceDate);
		$stmt->bindParam(":InvoiceDueDate", $this->InvoiceDueDate);
		$stmt->bindParam(":EmailSubject", $this->EmailSubject);
		$stmt->bindParam(":DateSent", $this->DateSent);
        $stmt->bindParam(":DatePaid", $this->DatePaid);
        $stmt->bindParam(":IsCanceled", $this->IsCanceled);

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
		$this->CustomerId = $data["CustomerId"];
		$this->InvoiceDate = $data["InvoiceDate"];
		$this->InvoiceDueDate = $data["InvoiceDueDate"];
		$this->EmailSubject = $data["EmailSubject"];
		$this->DateSent = $data["DateSent"];
		$this->DatePaid = $data["DatePaid"];
		$this->IsCanceled = $data["IsCanceled"];

		// update query
		$query = "UPDATE
					" . $this->table_name . "
				SET
                    CustomerId = :CustomerId,
                    InvoiceDate = :InvoiceDate,
                    InvoiceDueDate = :InvoiceDueDate,
                    EmailSubject = :EmailSubject,
                    DateSent = :DateSent,
                    DatePaid = :DatePaid,
                    IsCanceled = :IsCanceled
				WHERE
					Id = :Id";
	
		// prepare query statement
		$stmt = $this->conn->prepare($query);
	
		// sanitize
		$this->CustomerId=htmlspecialchars(strip_tags($this->CustomerId));
		$this->EmailSubject=htmlspecialchars(strip_tags($this->EmailSubject));

		// bind new values
		$stmt->bindParam(':Id', $this->Id);
		$stmt->bindParam(":CustomerId", $this->CustomerId);
		$stmt->bindParam(":InvoiceDate", $this->InvoiceDate);
		$stmt->bindParam(":InvoiceDueDate", $this->InvoiceDueDate);
		$stmt->bindParam(":EmailSubject", $this->EmailSubject);
		$stmt->bindParam(":DateSent", $this->DateSent);
        $stmt->bindParam(":DatePaid", $this->DatePaid);
		$stmt->bindParam(":IsCanceled", $this->IsCanceled);

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