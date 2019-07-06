<?php
class Invoice {

    private $conn;
    private $table_name = "invoice";

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

    public function __construct($db) {
        $this->conn = $db;
    }

    function read($customerId) {

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
                
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function read_one($id) {

        $query = "SELECT c.Name 'CustomerName', cc.* 
            FROM " . $this->table_name . " cc 
            INNER JOIN customer c ON c.Id = cc.CustomerId
            WHERE cc.Id = " . $id;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function create() {

        $query = "INSERT INTO " . $this->table_name . "
            SET
                FirstName = :FirstName, Surname = :Surname, 
                EmailAddress = :EmailAddress, CustomerId = :CustomerId,
                IsVisible = :IsVisible";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":FirstName", $this->FirstName);
        $stmt->bindParam(":Surname", $this->Surname);
        $stmt->bindParam(":EmailAddress", $this->EmailAddress);
        $stmt->bindParam(":CustomerId", $this->CustomerId);
        $stmt->bindParam(":IsVisible", $this->IsVisible);

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
                FirstName = :FirstName, Surname = :Surname, 
                EmailAddress = :EmailAddress, CustomerId = :CustomerId,
                IsVisible = :IsVisible
            WHERE Id = :Id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":Id", $this->Id);
        $stmt->bindParam(":FirstName", $this->FirstName);
        $stmt->bindParam(":Surname", $this->Surname);
        $stmt->bindParam(":EmailAddress", $this->EmailAddress);
        $stmt->bindParam(":CustomerId", $this->CustomerId);
        $stmt->bindParam(":IsVisible", $this->IsVisible);

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