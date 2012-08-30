<?php
/**
 * Copy Compete Unique Visitors to a Prospect Notes Field
 * LAST TESTED 8/30/2012
 */

//These are libraries and credential storage
include('../credentials.php');
include('./PardotConnector.class.php');
include('./CompeteConnector.class.php');

//Create a Pardot Connector
$pardot = new PardotConnector();
$pardot->authenticate(
	$credentials['pardot']['email'],
	$credentials['pardot']['password'],
	$credentials['pardot']['user_key']
);

//Initialize our Compete Library
$compete = new CompeteConnector();
$compete->setApiKey($credentials['compete']['api_key']);

//These are just cache arrays for efficiency and reporting
$competeResults = array();
$changedProspects = array();


//Query for recently update prospects, limit to 10. A collection of SimpleXML Objects
$prospects = $pardot->prospectQuery(
        array(
                'limit'=>'10',
                'updated_after'=>'yesterday'
        )
);

//Loop through each of our prospects returned by the Query
foreach($prospects as $prospect){
	//Change this prospect into an array for simplicity
	$prospect = get_object_vars($prospect);

	$company = $prospect['company'];
	$website = $prospect['website'];

	//Only act if we have a company and a website (website is company website)
	if(strlen($company) && strlen($website)){
		//Compete doesn't accept the protocol
		$website = str_replace('http://','',$website);

		//See if the Unique Visitors Entry Exists in our Cache
		if(array_key_exists($website,$competeResults)){
			//we already have results
			$uv = $competeResults[$website];
		} else {
			//Fetch the Unique Visitors Information from Compete
			$uv = $compete->query($website,'uv');
			//get the last result
			$uv = $uv['0']->value;
			$competeResults[$website]=$uv;
		}

		//Concatenate the new results on the notes field
		$prospect['notes'] = $prospect['notes'].' Compete Results:'.$uv;
		
		//Send the Updated Prospect Back to Pardot
		$res = $pardot->prospectUpdate(array(
			'id'		=>$prospect['id'],
			'notes'		=>$prospect['notes']
		));

	
		//This is purely for logging
		$changedProspects[] = array(
			'id'		=>$prospect['id'],
			'email'		=>$prospect['email'],
			'website'	=>$prospect['website'],
			'company'	=>$prospect['company'],
			'CompeteResults'=>$uv
		);
	}
}
//Display our very basic log
var_dump($changedProspects);
