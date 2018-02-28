RENAME TABLE tbl_finishOptions to finishOptions;
RENAME TABLE tbl_orders to orders;
RENAME TABLE tbl_paperTypes to paperTypes;
RENAME TABLE tbl_posterTube to posterTube;
RENAME TABLE tbl_rushOrder to rushOrder;

ALTER TABLE orders ADD orders_cc_emails VARCHAR(255) AFTER orders_email;
ALTER TABLE orders ADD orders_status ENUM('New','In Progress','Completed','Cancel','On Hold') AFTER orders_statusId;
UPDATE orders,tbl_status SET orders_status=tbl_status.status_name WHERE orders_statusId=tbl_status.status_id;
ALTER TABLE orders DROP COLUMN orders_statusId;
DROP TABLE tbl_status;

