<?php

	require('../config.php');
	dol_include_once('/comm/propal/class/propal.class.php');
	dol_include_once('/commande/class/commande.class.php');
	dol_include_once('/compta/facture/class/facture.class.php');

	global $db;

	$fk_object = GETPOST('fk_object', 'int');
	$className = GETPOST('element', 'alpha');
	$className = ucfirst($className);
	$className = strtolower($className);

	if(($className == 'propal')) {
		$doli_det = "doli_propaldet";
		$chtotal = 'total';
	} elseif(($className == 'commande')) {
		$doli_det = "doli_commandedet";
		$chtotal = 'total_ttc';
	}	


	$listpost = GETPOST('listpost');
	$listpost = rtrim($listpost, ':'); 
	$tablistpost = explode(":", $listpost);
	foreach($tablistpost as $valeur) {
		$date_start = GETPOST('date_start_'.$valeur.'');
		$date_end = GETPOST('date_end_'.$valeur.''); 				
		$db->query("UPDATE $doli_det SET date_start = '$date_start', date_end = '$date_end' where rowid = '$valeur' ");

	}






	
