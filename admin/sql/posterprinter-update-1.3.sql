UPDATE tbl_orders SET orders_timeFinished=orders_timeCreated WHERE orders_timeFinished IS NULL;
