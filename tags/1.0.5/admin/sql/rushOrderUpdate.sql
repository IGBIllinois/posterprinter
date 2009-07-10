CREATE TABLE tbl_rushOrder (
	rushOrder_id INT NOT NULL AUTO_INCREMENT,
	rushOrder_name VARCHAR(40),
	rushOrder_cost DECIMAL(5,2),
	rushOrder_available BOOLEAN,
	rushOrder_default BOOLEAN DEFAULT 0,
	PRIMARY KEY (rushOrder_id)
);

INSERT INTO tbl_rushOrder(rushOrder_name,rushOrder_cost,rushOrder_available,rushOrder_default) VALUES("No",0.00,1,1);
INSERT INTO tbl_rushOrder(rushOrder_name,rushOrder_cost,rushOrder_available) VALUES("Yes",25.00,1);

ALTER TABLE tbl_orders ADD COLUMN orders_rushOrderId INT REFERENCES tbl_rushOrder(rushOrder_id);
UPDATE tbl_orders SET orders_rushOrderId=1;
