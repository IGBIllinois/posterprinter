<?php

class statistics {

	private $db;
	private $startDate;
	private $endDate;
	public function __construct($db,$startDate,$endDate) {

		$this->db = $db;
		$this->startDate = $startDate;
		$this->endDate = $endDate;
		
	}

	public function __destruct() {


	}

	public function cost() {

		$sql = "SELECT SUM(orders_totalCost) AS totalCost ";
		$sql .= "FROM orders ";
		$sql .= "WHERE DATE(orders_timeCreated) BETWEEN :start_date AND :end_date ";
		$sql .= "AND orders.orders_status='Completed' ";
		$sql .= "GROUP BY orders.orders_status";
		$parameters = array(
			':start_date'=>$this->startDate,
			':end_date'=>$this->endDate
		);
		$result = $this->db->query($sql,$parameters);
		if (count($result) > 0) { return $result[0]['totalCost']; }
		else { return 0; }
	}
	
	public function pretty_cost() {
		return number_format($this->cost(),2);

	}
	public function popularPaperTypes() {

		$sql = "SELECT paperTypes_id,paperTypes_name,COUNT(*) AS count ";
		$sql .= "FROM orders ";
		$sql .= "LEFT JOIN paperTypes ON orders.orders_paperTypesId=paperTypes.paperTypes_id ";
		$sql .= "WHERE (orders_timeCreated >= :start_date AND orders_timeCreated <= :end_date) ";
		$sql .= "AND orders.orders_status='Completed' ";
		$sql .= "GROUP BY paperTypes_name ORDER BY count DESC";
		$parameters = array(
                        ':start_date'=>$this->startDate,
                        ':end_date'=>$this->endDate
                );
		return $this->db->query($sql,$parameters);

	}


	public function paperTypesTotalInches() {

		$sql = "SELECT paperTypes_name,SUM(orders.orders_length) AS totalLength ";
		$sql .= "FROM orders ";
		$sql .= "LEFT JOIN paperTypes ON orders.orders_paperTypesId=paperTypes.paperTypes_id ";
		$sql .= "WHERE (orders_timeCreated >= :start_date AND orders_timeCreated <= :end_date) ";
		$sql .= "AND orders.orders_status='Completed' ";
		$sql .= "GROUP BY paperTypes_name ORDER BY totalLength DESC";
		$parameters = array(
                        ':start_date'=>$this->startDate,
                        ':end_date'=>$this->endDate
                );
		return $this->db->query($sql,$parameters);

	}

	public function popularFinishOptions() {

		$sql = "SELECT finishOptions_id,finishOptions_name,COUNT(*) AS count ";
		$sql .= "FROM orders ";
		$sql .= "LEFT JOIN finishOptions ON orders.orders_finishOptionsId=finishOptions.finishOptions_id ";
		$sql .= "WHERE (orders_timeCreated >=:start_date AND orders_timeCreated <=:end_date) ";
		$sql .= "AND orders.orders_status='Completed' ";
		$sql .= "GROUP BY finishOptions_name ORDER BY count DESC";
		$parameters = array(
                        ':start_date'=>$this->startDate,
                        ':end_date'=>$this->endDate
                );
		return $this->db->query($sql,$parameters);

	}


	public function finishOptionsTotalInches() {

		$sql = "SELECT finishOptions_name,SUM(orders.orders_length) AS totalLength ";
		$sql .= "FROM orders ";
		$sql .= "LEFT JOIN finishOptions ON orders.orders_finishOptionsId=finishOptions.finishOptions_id ";
		$sql .= "WHERE (orders_timeCreated >=:start_date AND orders_timeCreated <=:end_date) ";
		$sql .= "AND orders.orders_status='Completed'";
		$parameters = array(
                        ':start_date'=>$this->startDate,
                        ':end_date'=>$this->endDate
                );

		return $this->db->query($sql,$parameters);

	}

	public function totalInches() {
		$sql = "SELECT SUM(orders.orders_length) AS total ";
		$sql .= "FROM orders ";
		$sql .= "WHERE (orders_timeCreated >=:start_date AND orders_timeCreated <=:end_date) ";
		$sql .= "AND orders.orders_status='Completed'";
		$parameters = array(
                        ':start_date'=>$this->startDate,
                        ':end_date'=>$this->endDate
                );

		$result = $this->db->query($sql,$parameters);
		$total = $result[0]['total'];
		if ($total == "") {
			$total = 0;
		}
		return $total;


	}
	
	public function pretty_totalInches() {
		return number_format($this->totalInches(),0);
		
	}
	public function orders() {

		$sql = "SELECT COUNT(1) As count ";
		$sql .= "FROM orders ";
		$sql .= "WHERE DATE(orders_timeCreated) BETWEEN :start_date AND :end_date ";
		$sql .= "AND orders.orders_status='Completed'";
		$parameters = array(
                        ':start_date'=>$this->startDate,
                        ':end_date'=>$this->endDate
                );

		$ordersData = $this->db->query($sql,$parameters);
		$count = $ordersData[0]['count'];
		return $count;
	}

	public function ordersPerMonth($year) {
		$sql = "SELECT MONTH(orders_timeCreated) as month,COUNT(1) AS count ";
		$sql .= "FROM orders ";
		$sql .= "LEFT JOIN rushOrder ON orders.orders_rushOrderId=rushOrder.rushOrder_id ";
		$sql .= "WHERE YEAR(orders_timeCreated)=:year ";
		$sql .= "AND orders.orders_status='Completed' ";
		$sql .= "GROUP BY MONTH(orders_timeCreated)";
		$parameters = array(
			':year'=>$year
		);
		$result = $this->db->query($sql,$parameters);
		return $this->get_month_array($result,"month","count");
	}
	public function avgOrdersPerMonth() {
		$sql = "SELECT MONTH(a.timeCreated) as month, MONTHNAME(a.timeCreated) as month_name, AVG(a.count) as avg FROM ( ";
		$sql .= "SELECT orders_timeCreated as timeCreated,COUNT(1) AS count ";
		$sql .= "FROM orders ";
		$sql .= "LEFT JOIN rushOrder ON orders.orders_rushOrderId=rushOrder.rushOrder_id ";
		$sql .= "WHERE ";
		$sql .= "orders.orders_status='Completed' ";
		$sql .= "GROUP BY MONTH(orders_timeCreated),YEAR(orders_timeCreated)) a ";
		$sql .= "GROUP BY month ORDER BY month ASC";
		$result = $this->db->query($sql);
		return $this->get_month_array($result,"month","avg");
	}
	
	public function percentRushOrder() {
		$sql = "SELECT rushOrder.rushOrder_name,COUNT(1) AS count ";
		$sql .= "FROM orders ";
		$sql .= "LEFT JOIN rushOrder ON orders.orders_rushOrderId=rushOrder.rushOrder_id ";
		$sql .= "WHERE (orders_timeCreated >=:start_date AND orders_timeCreated <=:end_date) ";
		$sql .= "AND orders.orders_status='Completed' ";
		$sql .= "GROUP BY rushOrder_name";
		$parameters = array(
			':start_date'=>$this->startDate,
			':end_date'=>$this->endDate
		);
		$result = $this->db->query($sql,$parameters);
		$rushOrderYes = 0;
		$rushOrderNo = 0;
		for ($i=0;$i<count($result);$i++) {
			if ($result[$i]['rushOrder_name'] == 'No') {
				$rushOrderNo = $result[$i]['count'];

			}
			if ($result[$i]['rushOrder_name'] == 'Yes') {
				$rushOrderYes = $result[$i]['count'];
			}
		}
		if (($rushOrderYes == 0) && ($rushOrderNo == 0)) {
			$rushOrderPercent = 0;
		}
		else {
			$rushOrderPercent = ($rushOrderYes / ($rushOrderYes + $rushOrderNo)) * 100;
			$rushOrderPercent = round($rushOrderPercent,2);
		}

		return $rushOrderPercent;

	}

	public function percentPosterTube() {
		$sql = "SELECT posterTube.posterTube_name,COUNT(1) AS count ";
		$sql .= "FROM orders ";
		$sql .= "LEFT JOIN posterTube ON orders.orders_posterTubeId=posterTube.posterTube_id ";
		$sql .= "WHERE (orders_timeCreated >=:start_date AND orders_timeCreated <=:end_date) ";
		$sql .= "AND orders.orders_status='Completed' ";
		$sql .= "GROUP BY posterTube_name";
		$parameters = array(
                        ':start_date'=>$this->startDate,
                        ':end_date'=>$this->endDate
                );
		$result = $this->db->query($sql,$parameters);
		$posterTubeYes = 0;
		$posterTubeNo = 0;
		for ($i=0;$i<count($result);$i++) {
			if ($result[$i]['posterTube_name'] == 'No') {
				$posterTubeNo = $result[$i]['count'];

			}
			if ($result[$i]['posterTube_name'] == 'Yes') {
				$posterTubeYes = $result[$i]['count'];
			}
		}

		if (($posterTubeYes == 0) && ($posterTubeYes == 0)) {
			$posterTubePercent = 0;
		}
		else {
			$posterTubePercent = ($posterTubeYes / ($posterTubeYes + $posterTubeNo)) * 100;
			$posterTubePercent = round($posterTubePercent,2);
		}
		return $posterTubePercent;

	}


	public function averagePosterCost() {
		$numberOrders = $this->orders();
		$cost = $this->cost();
		if ($numberOrders > 0) {
			$averageCost = $cost / $numberOrders;
			$averageCost = round($averageCost,2);
		}
		else {
			$averageCost = 0;
		}

		return number_format($averageCost,2,'.','');
	}

	public static function get_month_array($data,$month_column,$data_column) {
		$new_data = array();
		for($i=1;$i<=12;$i++){
			$exists = false;
			if (count($data) > 0) {
				foreach($data as $row) {
					$month = $row[$month_column];
					if ($month == $i) {
						$month_name = date('F', mktime(0,0,0,$month,1));
						array_push($new_data,array('month_name'=>$month_name,
									$data_column=>$row[$data_column]));
						$exists = true;
						break(1);
					}
				}
			}
			if (!$exists) {
				$month_name = date('F', mktime(0,0,0,$i,1));
				array_push($new_data,array('month_name'=>$month_name,
                                                                        $data_column=>0));
			}
			$exists = false;
		}
		return $new_data;
	}
}

?>
