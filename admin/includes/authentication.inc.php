<?php
/////////////////////////////////////////////////
//
//	Poster Printer Order Submission		
//	authentication.inc.php
//
//	Functions to verify IGB users
//
//	David Slater
//	April 2007
//
/////////////////////////////////////////////////

function authenticate($username,$password,$authenticationSettings) {

	$ldaphost = $authenticationSettings['host'];
	$baseDN = $authenticationSettings['baseDN'];	
	$peopleDN = $authenticationSettings['peopleOU'] . "," . $baseDN;
	$groupDN = $authenticationSettings['groupOU'] . "," . $baseDN;
	$ssl = $authenticationSettings['ssl'];
	$port = $authenticationSettings['port'];
	$group = $authenticationSettings['group'];
	$connect;
	
	if ($ssl == 1) {
		$connect = ldap_connect("ldaps://" . $ldaphost,$port);
		
	}
	elseif ($ssl == 0) {
		$connect = ldap_connect("ldap://" . $ldaphost,$port);
		
	}
			
	$bindDN = "uid=" . $username . "," . $peopleDN;
	
	$success = @ldap_bind($connect, $bindDN, $password);

	if ($success == 1) {
	
		$search = ldap_search($connect,$groupDN,"(cn=" . $group . ")");
		$data = ldap_get_entries($connect,$search);
		ldap_unbind($connect);
		
		foreach($data[0]['memberuid'] as $groupMember) {
			
			if ($username == $groupMember) {
				$success = 1;
				return $success;
			}
			else {
				$success = 0;
			}
		}
		
	}
	return $success;
	
}

?>