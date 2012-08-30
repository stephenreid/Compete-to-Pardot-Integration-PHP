<?php
/**
 * Copy Compete Unique Visitors to a Prospect Notes Field
 * LAST TESTED 8/30/2012
 */
include('../credentials.php');
include('./PardotConnector.class.php');
include('./CompeteConnector.class.php');

$pardot = new PardotConnector();
$pardot->authenticate($credentials['pardot']['email'],$credentials['pardot']['password'],$credentials['pardot']['user_key']);
$prospects = $pardot->prospectQuery(
	array(
		'limit'=>'10',
		'updated_after'=>'yesterday'
	)
);

$compete = new CompeteConnector();
$compete->setApiKey($credentials['compete']['api_key']);

$competeResults = array();
$changedProspects = array();
foreach($prospects as $prospect){
	$prospect = get_object_vars($prospect);
	$company = $prospect['company'];
	$website = $prospect['website'];
	if(strlen($company) && strlen($website)){
		$website = str_replace('http://','',$website);
		if(array_key_exists($website,$competeResults)){
			//we already have results
			$uv = $competeResults[$website];
		} else {
			$uv = $compete->query($website,'uv');
			//get the last result
			$uv = $uv['0']->value;
			$competeResults[$website]=$uv;
		}
		$prospect['notes'] = $prospect['notes'].' Compete Results:'.$uv;
		
		$res = $pardot->prospectUpdate(array(
			'id'		=>$prospect['id'],
			'notes'		=>$prospect['notes']
		));
		$changedProspects[] = array(
			'id'		=>$prospect['id'],
			'email'		=>$prospect['email'],
			'website'	=>$prospect['website'],
			'company'	=>$prospect['company'],
			'CompeteResults'=>$uv
		);
	}
}
var_dump($changedProspects);
