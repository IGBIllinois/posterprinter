<?php

class statistics {

	private $db;
	
	public function __construct($mysqlSettings) {

		//connects to the database.  Pulls the mysql settings from the file includes/settings.inc.php.
		$this->db = mysql_connect($mysqlSettings['host'],$mysqlSettings['username'],$mysqlSettings['password']);
		mysql_select_db($mysqlSettings['database'],$this->db) or die("Unable to select database");

	}
	
	public function __destruct() {

		mysql_close($this->db);

	}
	
	public function cost($startDate,$endDate) {
		
		$totalCostSql = "SELECT SUM(orders_totalCost) AS totalCost
		FROM tbl_orders LEFT JOIN tbl_status ON tbl_orders.orders_statusId=tbl_status.status_id
		WHERE orders_timeCreated >= '$startDate' 
		AND orders_timeCreated <= '$endDate'
		AND status_name='Completed' 
		GROUP BY status_name";
		
		$totalCostResult = mysql_query($totalCostSql,$this->db);
		
		if (mysql_num_rows($totalCostResult) > 0) {
			return mysql_result($totalCostResult,0,'totalCost');
		}
		else {
			return 0;
		}
	}
	public function popularPaperTypes($startDate,$endDate) {
		
		$paperTypesSql = "SELECT paperTypes_id,paperTypes_name,COUNT(*) AS count 
			FROM tbl_orders LEFT JOIN tbl_status ON tbl_orders.orders_statusId=tbl_status.status_id
			LEFT JOIN tbl_paperTypes ON tbl_orders.orders_paperTypesId=tbl_paperTypes.paperTypes_id
			WHERE (orders_timeCreated >= '$startDate' AND orders_timeCreated <= '$endDate')
			AND status_name='Completed'
			GROUP BY paperTypes_name";
		return $this->query($paperTypesSql);
	
	}
	
	public function paperTypesTotalInches($startDate,$endDate) {
		
		$paperTypesSql = "SELECT paperTypes_name,SUM(tbl_orders.orders_length) AS totalLength 
			FROM tbl_orders LEFT JOIN tbl_status ON tbl_orders.orders_statusId=tbl_status.status_id
			LEFT JOIN tbl_paperTypes ON tbl_orders.orders_paperTypesId=tbl_paperTypes.paperTypes_id
			WHERE (orders_timeCreated >= '$startDate' AND orders_timeCreated <= '$endDate')
			AND status_name='Completed'
			GROUP BY paperTypes_name";
		return $this->query($paperTypesSql);
	
	}
	
	public function popularFinishOptions($startDate,$endDate) {
		
		$finishOptionsSql = "SELECT finishOptions_id,finishOptions_name,COUNT(*) AS count 
			FROM tbl_orders LEFT JOIN tbl_status ON tbl_orders.orders_statusId=tbl_status.status_id
			LEFT JOIN tbl_finishOptions ON tbl_orders.orders_finishOptionsId=tbl_finishOptions.finishOptions_id
			WHERE (orders_timeCreated >= '$startDate' AND orders_timeCreated <= '$endDate')
			AND status_name='Completed'
			GROUP BY finishOptions_name";
		return $this->query($finishOptionsSql);
	
	}
	
	public function totalInches($startDate,$endDate) {
		$sql = "SELECT SUM(tbl_orders.orders_length) AS total 
			FROM tbl_orders LEFT JOIN tbl_status ON tbl_orders.orders_statusId=tbl_status.status_id
			WHERE (orders_timeCreated >= '$startDate' AND orders_timeCreated <= '$endDate')
			AND status_name='Completed'";
		$result = $this->query($sql);
		$total = $result[0]['total'];
		if ($total == "") {
			$total = 0;
		}
		return $total;
	
	
	}
	public function orders($startDate,$endDate) {
		//$ordersSql = "SELECT COUNT(1) AS count 
		//	FROM tbl_orders LEFT JOIN tbl_status ON tbl_orders.orders_statusId=tbl_status.status_id
		//	WHERE (orders_timeCreated >= '$startDate' AND orders_timeCreated <= '$endDate')
		//	AND status_name='Completed'";
		$ordersSql = "SELECT COUNT(1) As count
			FROM tbl_orders LEFT JOIN tbl_status ON tbl_orders.orders_statusId=tbl_status.status_id
			WHERE DATE(orders_timeCreated) BETWEEN '$startDate' AND '$endDate'
			AND status_name='Completed'";
		$ordersData = $this->query($ordersSql);
		$count = $ordersData[0]['count'];
		return $count;
	}
	
	public function ordersPerMonth($year) {
		$ordersSql = "SELECT orders_timeCreated,COUNT(1) AS count 
			FROM tbl_orders LEFT JOIN tbl_status ON tbl_orders.orders_statusId=tbl_status.status_id
			LEFT JOIN tbl_rushOrder ON tbl_orders.orders_rushOrderId=tbl_rushOrder.rushOrder_id
			WHERE YEAR(orders_timeCreated)=$year
			AND status_name='Completed'
			GROUP BY MONTH(orders_timeCreated)";
		$ordersData = $this->query($ordersSql);
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
					else {
						
						
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
	
	public function percentRushOrder($startDate,$endDate) {
		$rushOrderSql = "SELECT tbl_rushOrder.rushOrder_name,COUNT(1) AS count 
			FROM tbl_orders LEFT JOIN tbl_status ON tbl_orders.orders_statusId=tbl_status.status_id
			LEFT JOIN tbl_rushOrder ON tbl_orders.orders_rushOrderId=tbl_rushOrder.rushOrder_id
			WHERE (orders_timeCreated >= '$startDate' AND orders_timeCreated <= '$endDate')
			AND status_name='Completed'
			GROUP BY rushOrder_name";
		$rushOrderData = $this->query($rushOrderSql);
		$rushOrderYes;
		$rushOrderNo;
		for ($i=0;$i<=count($rushOrderData);$i++) {
			if ($rushOrderData[$i]['rushOrder_name'] == 'No') {
				$rushOrderNo = $rushOrderData[$i]['count'];
				
			}
			if ($rushOrderData[$i]['rushOrder_name'] == 'Yes') {
				$rushOrderYes = $rushOrderData[$i]['count'];
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
	public function percentPosterTube($startDate,$endDate) {
		$posterTubeSql = "SELECT tbl_posterTube.posterTube_name,COUNT(1) AS count 
			FROM tbl_orders LEFT JOIN tbl_status ON tbl_orders.orders_statusId=tbl_status.status_id
			LEFT JOIN tbl_posterTube ON tbl_orders.orders_posterTubeId=tbl_posterTube.posterTube_id
			WHERE (orders_timeCreated >= '$startDate' AND orders_timeCreated <= '$endDate')
			AND status_name='Completed'
			GROUP BY posterTube_name";
		$posterTubeData = $this->query($posterTubeSql);
		$posterTubeYes;
		$posterTubeNo;
		for ($i=0;$i<=count($posterTubeData);$i++) {
			if ($posterTubeData[$i]['posterTube_name'] == 'No') {
				$posterTubeNo = $posterTubeData[$i]['count'];
				
			}
			if ($posterTubeData[$i]['posterTube_name'] == 'Yes') {
				$posterTubeYes = $posterTubeData[$i]['count'];
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
	
	public function averagePosterCost($startDate,$endDate) {
		$numberOrders = $this->orders($startDate,$endDate);
		$cost = $this->cost($startDate,$endDate);
		if ($numberOrders > 0) {
			$averageCost = $cost / $numberOrders;
			$averageCost = round($averageCost,2);
		}
		else {
			$averageCost = 0;
		}
		
		return $averageCost;
	}
	//////////////////////////////Private Functions////////////////////////////
	private function query($sql) {
		$result = mysql_query($sql,$this->db);
		return $this->mysqlToArray($result);
	
	
	}
	private function mysqlToArray($mysqlResult) {
		$dataArray;
		$i =0;
		while($row = mysql_fetch_array($mysqlResult,MYSQL_ASSOC)){	
			foreach($row as $key=>$data) {
				$dataArray[$i][$key] = $data;
			}
			$i++;
		}
		return $dataArray;
	
	}

}

?>