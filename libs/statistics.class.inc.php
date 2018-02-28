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
		$sql .= "WHERE DATE(orders_timeCreated) BETWEEN '" . $this->startDate . "' AND '" . $this->endDate . "' ";
		$sql .= "AND orders.orders_status='Completed' ";
		$sql .= "GROUP BY orders.orders_status";
		$result = $this->db->query($sql);
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
		$sql .= "WHERE (orders_timeCreated >= '" . $this->startDate . "' AND orders_timeCreated <= '" . $this->endDate . "') ";
		$sql .= "AND orders.orders_status='Completed' ";
		$sql .= "GROUP BY paperTypes_name ORDER BY count DESC";
		return $this->db->query($sql);

	}


	public function paperTypesTotalInches() {

		$sql = "SELECT paperTypes_name,SUM(orders.orders_length) AS totalLength ";
		$sql .= "FROM orders ";
		$sql .= "LEFT JOIN paperTypes ON orders.orders_paperTypesId=paperTypes.paperTypes_id ";
		$sql .= "WHERE (orders_timeCreated >= '" . $this->startDate . "' AND orders_timeCreated <= '" . $this->endDate . "') ";
		$sql .= "AND orders.orders_status='Completed' ";
		$sql .= "GROUP BY paperTypes_name ORDER BY totalLength DESC";
		return $this->db->query($sql);

	}

	public function popularFinishOptions() {

		$sql = "SELECT finishOptions_id,finishOptions_name,COUNT(*) AS count ";
		$sql .= "FROM orders ";
		$sql .= "LEFT JOIN finishOptions ON orders.orders_finishOptionsId=finishOptions.finishOptions_id ";
		$sql .= "WHERE (orders_timeCreated >= '" . $this->startDate . "' AND orders_timeCreated <= '" . $this->endDate . "') ";
		$sql .= "AND orders.orders_status='Completed' ";
		$sql .= "GROUP BY finishOptions_name ORDER BY count DESC";
		return $this->db->query($sql);

	}


	public function finishOptionsTotalInches() {

		$sql = "SELECT finishOptions_name,SUM(orders.orders_length) AS totalLength ";
		$sql .= "FROM orders ";
		$sql .= "LEFT JOIN finishOptions ON orders.orders_finishOptionsId=finishOptions.finishOptions_id ";
		$sql .= "WHERE (orders_timeCreated >= '" . $this->startDate . "' AND orders_timeCreated <= '" . $this->endDate . "') ";
		$sql .= "AND orders.orders_status='Completed'";
		return $this->db->query($sql);

	}

	public function totalInches() {
		$sql = "SELECT SUM(orders.orders_length) AS total ";
		$sql .= "FROM orders ";
		$sql .= "WHERE (orders_timeCreated >= '" . $this->startDate . "' AND orders_timeCreated <= '" . $this->endDate . "') ";
		$sql .= "AND orders.orders_status='Completed'";
		$result = $this->db->query($sql);
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
		$sql .= "WHERE DATE(orders_timeCreated) BETWEEN '" . $this->startDate . "' AND '" . $this->endDate . "' ";
		$sql .= "AND orders.orders_status='Completed'";
		$ordersData = $this->db->query($sql);
		$count = $ordersData[0]['count'];
		return $count;
	}

	public function ordersPerMonth($year) {
		$sql = "SELECT orders_timeCreated,COUNT(1) AS count ";
		$sql .= "FROM orders ";
		$sql .= "LEFT JOIN rushOrder ON orders.orders_rushOrderId=rushOrder.rushOrder_id ";
		$sql .= "WHERE YEAR(orders_timeCreated)='" . $year . "' ";
		$sql .= "AND orders.orders_status='Completed' ";
		$sql .= "GROUP BY MONTH(orders_timeCreated)";
		$ordersData = $this->db->query($sql);
		$newOrdersData;
		for($i=1;$i<=12;$i++){
			$exists = false;

			if (count($ordersData) > 0) {
				foreach($ordersData as $row) {
					$timeCreated = strtotime($row['orders_timeCreated']);
					$month = date('m',$timeCreated);

					if ($month == $i) {
						$monthName = date('F',$timeCreated);
						$newOrdersData[$monthName] = $row['count'];

						$exists = true;
						break(1);
					}


				}
			}
			if ($exists == false) {

				$monthName = date('F',strtotime('2008-' . $i . '-01'));
				$newOrdersData[$monthName] = 0;

			}
			$exists = false;

		}
		return $newOrdersData;
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
		return $this->db->query($sql);
	}
	
	public function percentRushOrder() {
		$sql = "SELECT rushOrder.rushOrder_name,COUNT(1) AS count ";
		$sql .= "FROM orders ";
		$sql .= "LEFT JOIN rushOrder ON orders.orders_rushOrderId=rushOrder.rushOrder_id ";
		$sql .= "WHERE (orders_timeCreated >= '" . $this->startDate . "' AND orders_timeCreated <= '" . $this->endDate . "') ";
		$sql .= "AND orders.orders_status='Completed' ";
		$sql .= "GROUP BY rushOrder_name";
		$result = $this->db->query($sql);
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
		$sql .= "WHERE (orders_timeCreated >= '" . $this->startDate . "' AND orders_timeCreated <= '" . $this->endDate . "') ";
		$sql .= "AND orders.orders_status='Completed' ";
		$sql .= "GROUP BY posterTube_name";
		$result = $this->db->query($sql);
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

}

?>
