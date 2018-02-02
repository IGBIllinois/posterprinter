ALTER TABLE tbl_orders ADD orders_cc_emails VARCHAR(255) AFTER orders_email;
ALTER TABLE tbl_orders ADD orders_status ENUM('New','In Progress','Completed','Cancel','On Hold') AFTER orders_statusId;
UPDATE tbl_orders,tbl_status SET orders_status=tbl_status.status_name WHERE orders_statusId=tbl_status.status_id;
ALTER TABLE tbl_orders DROP COLUMN orders_statusId;
DROP TABLE tbl_status;
