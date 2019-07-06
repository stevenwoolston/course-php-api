CREATE DATABASE `api` /*!40100 DEFAULT CHARACTER SET latin1 */;

CREATE TABLE `customer` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(200) NOT NULL,
  `IsVisible` tinyint(1) NOT NULL,
  `Address` varchar(800) DEFAULT NULL,
  `Suburb` varchar(200) DEFAULT NULL,
  `State` varchar(50) DEFAULT NULL,
  `Postcode` varchar(20) NOT NULL,
  `InvoicingText` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM AUTO_INCREMENT=48 DEFAULT CHARSET=utf8;

CREATE TABLE `customercontact` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `FirstName` varchar(100) NOT NULL,
  `Surname` varchar(100) NOT NULL,
  `EmailAddress` varchar(200) NOT NULL,
  `IsVisible` tinyint(1) NOT NULL,
  `CustomerId` int(11) NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;

CREATE TABLE `invoice` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `CustomerId` int(11) NOT NULL,
  `InvoiceDate` date NOT NULL,
  `InvoiceDueDate` date NOT NULL,
  `EmailSubject` varchar(200) NOT NULL,
  `DateSent` datetime DEFAULT NULL,
  `DatePaid` date DEFAULT NULL,
  `IsCanceled` tinyint(1) NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM AUTO_INCREMENT=1262 DEFAULT CHARSET=utf8;

CREATE TABLE `invoiceitem` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `InvoiceId` int(11) NOT NULL,
  `Sequence` int(11) DEFAULT NULL,
  `Description` varchar(500) NOT NULL,
  `Cost` decimal(10,0) NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM AUTO_INCREMENT=1104 DEFAULT CHARSET=utf8;

CREATE TABLE `delivery` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `DateDelivered` datetime DEFAULT NULL,
  `DeliveredTo` varchar(1000) NOT NULL,
  `InvoiceId` int(11) NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM AUTO_INCREMENT=1140 DEFAULT CHARSET=utf8;

CREATE TABLE `payment` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `DatePaid` date NOT NULL,
  `Amount` decimal(10,0) NOT NULL,
  `InvoiceId` int(11) NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;
