RENAME TABLE tbl_finishOptions to finishOptions;
RENAME TABLE tbl_orders to orders;
RENAME TABLE tbl_paperTypes to paperTypes;
RENAME TABLE tbl_posterTube to posterTube;
RENAME TABLE tbl_rushOrder to rushOrder;

ALTER TABLE orders ADD orders_cc_emails VARCHAR(255) AFTER orders_email;
ALTER TABLE orders ADD orders_status ENUM('New','In Progress','Completed','Cancel','On Hold') AFTER orders_statusId;
ALTER TABLE orders ADD orders_key VARCHAR(255);
ALTER TABLE orders CHANGE orders_widthSwitched orders_rotated BOOL DEFAULT 0;
UPDATE orders,tbl_status SET orders_status=tbl_status.status_name WHERE orders_statusId=tbl_status.status_id;
ALTER TABLE orders DROP COLUMN orders_statusId;
DROP TABLE tbl_status;
ALTER TABLE orders ENGINE=InnoDB;
ALTER TABLE finishOptions ENGINE=InnoDB;
AlTER TABLE paperTypes ENGINE=InnoDB;
ALTER TABLE posterTube ENGINE=InnoDB;
ALTER TABLE rushOrder ENGINE=InnoDB;

ALTER TABLE finishOptions ADD finishOptions_timeCreated TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE paperTypes ADD paperTypes_timeCreated TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE posterTube ADD posterTube_timeCreated TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE rushOrder ADD rushOrder_timeCreated TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
UPDATE finishOptions SET finishOptions_timeCreated="0000-00-00 00:00:00";
UPDATE paperTypes SET paperTypes_timeCreated="0000-00-00 00:00:00";
UPDATE posterTube SET posterTube_timeCreated="0000-00-00 00:00:00";
UPDATE rushOrder SET rushOrder_timeCreated="0000-00-00 00:00:00";
