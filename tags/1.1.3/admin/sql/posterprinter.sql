CREATE database posterprinter
	CHARACTER SET utf8;
USE posterprinter;

CREATE TABLE tbl_paperTypes (
	paperTypes_id INT NOT NULL AUTO_INCREMENT,
	paperTypes_name VARCHAR(40),
	paperTypes_cost DECIMAL(5,2),
	paperTypes_width INT,
	paperTypes_available BOOLEAN,
	paperTypes_default BOOLEAN DEFAULT 0,
	PRIMARY KEY (paperTypes_id)
);

CREATE TABLE tbl_finishOptions (
	finishOptions_id INT NOT NULL AUTO_INCREMENT,
	finishOptions_name VARCHAR(40),
	finishOptions_cost DECIMAL(5,2),
	finishOptions_maxWidth INT,
	finishOptions_maxLength INT,
	finishOptions_available BOOLEAN,
	finishOptions_default BOOLEAN DEFAULT 0,
	PRIMARY KEY (finishOptions_id)

);

CREATE TABLE tbl_status (
	status_id INT NOT NULL AUTO_INCREMENT,
	status_name VARCHAR(20),
	PRIMARY KEY(status_id)

);

CREATE TABLE tbl_rushOrder (
	rushOrder_id INT NOT NULL AUTO_INCREMENT,
	rushOrder_name VARCHAR(40),
	rushOrder_cost DECIMAL(5,2),
	rushOrder_available BOOLEAN,
	rushOrder_default BOOLEAN DEFAULT 0,
	PRIMARY KEY (rushOrder_id)
);

CREATE TABLE tbl_posterTube (
	posterTube_id INT NOT NULL AUTO_INCREMENT,
	posterTube_name VARCHAR(20),
	posterTube_cost DECIMAL(5,2),
	posterTube_available BOOLEAN,
	posterTube_default BOOLEAN DEFAULT 0,
	PRIMARY KEY (posterTube_id)
);

CREATE TABLE tbl_orders (
	orders_id INT NOT NULL AUTO_INCREMENT,
	orders_email VARCHAR(30),
	orders_name VARCHAR(50),
	orders_timeCreated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	orders_timeFinished DATETIME,
	orders_fileName VARCHAR(100),
	orders_totalCost DECIMAL(6,2),
	orders_cfop VARCHAR(22),
	orders_activityCode VARCHAR(6),
	orders_width INT,
	orders_length INT,
	orders_statusId INT REFERENCES tbl_status(status_id),
	orders_paperTypesId INT REFERENCES tbl_paperTypes(paperTypes_id),
	orders_finishOptionsId INT REFERENCES tbl_finishOptions(finishOptions_id),
	orders_posterTubeId INT REFERENCES tbl_posterTube(posterTube_id),
	orders_rushOrderId INT REFERENCES tbl_rushOrder(rushOrder_id),
	orders_widthSwitched BOOLEAN DEFAULT 0,
	orders_comments MEDIUMTEXT,
	PRIMARY KEY(orders_id)

);

INSERT INTO tbl_paperTypes(paperTypes_name,paperTypes_cost,paperTypes_width,paperTypes_available) VALUES('Graphic Matte Canvas',1.50,42,True);
INSERT INTO tbl_paperTypes(paperTypes_name,paperTypes_cost,paperTypes_width,paperTypes_available) VALUES('Heavyweight Glossy',1.00,42,True);
INSERT INTO tbl_paperTypes(paperTypes_name,paperTypes_cost,paperTypes_width,paperTypes_available,paperTypes_default) VALUES('Heavyweight Satin',1.00,42,True,True);
INSERT INTO tbl_paperTypes(paperTypes_name,paperTypes_cost,paperTypes_width,paperTypes_available) VALUES('Premium RC Photogloss',1.25,42,True);
INSERT INTO tbl_paperTypes(paperTypes_name,paperTypes_cost,paperTypes_width,paperTypes_available) VALUES('Premium RC Photomatte',1.25,42,True);
INSERT INTO tbl_paperTypes(paperTypes_name,paperTypes_cost,paperTypes_width,paperTypes_available) VALUES('Banner Vinyl 15 Mil',1.50,42,True);

INSERT INTO tbl_finishOptions(finishOptions_name,finishOptions_cost,finishOptions_maxWidth,finishOptions_maxLength,finishOptions_available,finishOptions_default) VALUES('None',0.00,42,999,True,True);
INSERT INTO tbl_finishOptions(finishOptions_name,finishOptions_cost,finishOptions_maxWidth,finishOptions_maxLength,finishOptions_available) VALUES('Laminating',5.00,42,999,True);
INSERT INTO tbl_finishOptions(finishOptions_name,finishOptions_cost,finishOptions_maxWidth,finishOptions_maxLength,finishOptions_available) VALUES('Laminating with 3/16 Foam Core',14.00,32,40,True);
INSERT INTO tbl_finishOptions(finishOptions_name,finishOptions_cost,finishOptions_maxWidth,finishOptions_maxLength,finishOptions_available) VALUES('Laminating with 1/4 Foam Core',55.00,37,49,True);


INSERT INTO tbl_status(status_name) VALUES("New");
INSERT INTO tbl_status(status_name) VALUES("In Progress");
INSERT INTO tbl_status(status_name) VALUES("Completed");
INSERT INTO tbl_status(status_name) VALUES("Cancel");
INSERT INTO tbl_status(status_name) VALUES("On Hold");

INSERT INTO tbl_posterTube(posterTube_name,posterTube_cost,posterTube_available) VALUES("Yes",5.00,True);
INSERT INTO tbl_posterTube(posterTube_name,posterTube_cost,posterTube_available,posterTube_default) VALUES("No",0.00,True,True);

INSERT INTO tbl_rushOrder(rushOrder_name,rushOrder_cost,rushOrder_available) VALUES("Yes",25.00,1);
INSERT INTO tbl_rushOrder(rushOrder_name,rushOrder_cost,rushOrder_available,rushOrder_default) VALUES("No",0.00,1,1);

ALTER TABLE tbl_orders AUTO_INCREMENT = 1000;
