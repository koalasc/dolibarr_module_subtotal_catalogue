<?php

	require('../config.php');
	dol_include_once('/comm/propal/class/propal.class.php');
	dol_include_once('/commande/class/commande.class.php');
	dol_include_once('/compta/facture/class/facture.class.php');
	
	$qtypcef = GETPOST('qtypcef');
	$qtypcec = GETPOST('qtypcec');

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

	global $db;

	$rang = '0';
	$sqlrang = $db->query("select MAX(rang) as rang from $doli_det where fk_$className = '$fk_object'");
	$fetchsqlrang = $sqlrang->fetch_assoc();
	$rang = $fetchsqlrang['rang'];
	$rang++;

	$listpost = GETPOST('listpost');
	
	$listpost = rtrim($listpost, ':'); 
	$tablistpost = explode(":", $listpost);
	foreach($tablistpost as $valeur) {
		$remise = '0';
		$redevance = '0';
		$remise = GETPOST('remise'.$valeur.'');
		$redevance = GETPOST('redevance'.$valeur.'');
	
	switch ($valeur) {
		case "ALIMENTAIRE":
        		$searchp = "A";
			$catego = "ALIMENTAIRE";
        		break;
		case (preg_match('/CARTE/', $valeur) ? true : false):
        		$searchp = "A";
			$catego = "ALIMENTAIRE";
        		break;
		case "BOISSONS":
        		$searchp = "B";
			$catego = "BOISSONS";
        		break;
		case "PERSONNEL":
        		$searchp = "P";
			$catego = "PERSONNEL";
        		break;
    		case "MOBILIERARTSDELATABLE":
        		$searchp = "R";
			$catego = "MOBILIER & ARTS DE LA TABLE";
        		break;
    		case "LIVRAISONREPRIS":
        		$searchp = "U";
			$catego = "LIVRAISON & REPRISE";
        		break;
	    }

	
		if(($remise>0)){
			$sqlsum = $db->query("select SUM(total_ht) as sumht FROM $doli_det as dpd, doli_product as dp WHERE dpd.fk_product = dp.rowid and dp.ref like '$searchp%' and fk_$className = '$fk_object'");
			$fetchsqlsum = $sqlsum->fetch_assoc();
			$sum = $fetchsqlsum['sumht'];
			$remisec = $sum*$remise/100;
			$db->query("INSERT into $doli_det (fk_$className, description, tva_tx, qty, price , subprice, total_ht, total_tva, total_ttc, buy_price_ht, rang ) values ( $fk_object, 'Remise $catego ( $remise % )', '0', '1', '-$remisec' , '-$remisec', '-$remisec', '0', '-$remisec', '0', '$rang') ");
			$rang++;
		}
		

		if(($redevance>0)){
			$sqlsumr = $db->query("select SUM(total_ht) as sumht FROM $doli_det as dpd, doli_product as dp WHERE dpd.fk_product = dp.rowid and dp.ref like '$searchp%' and fk_$className = '$fk_object'");
			$fetchsqlsumr = $sqlsumr->fetch_assoc();
			$sumr = $fetchsqlsumr['sumht'];
			$redevancec = $sumr*$redevance/100;
			$db->query("INSERT into $doli_det (fk_$className, description, tva_tx, qty, price , subprice, total_ht, total_tva, total_ttc, buy_price_ht, rang ) values ( $fk_object, 'Redevance $catego ( $redevance % )', '0', '1', '$redevancec' , '$redevancec', '$redevancec', '0', '$redevancec', '0', '$rang')");
			$rang++;
		}

	}


	$reqsumt = $db->query("SELECT SUM(total_ht) as sumtht, SUM(total_tva) as sumtva, SUM(total_ttc) as sumttc FROM $doli_det where fk_$className = $fk_object");
	$sumt = $reqsumt->fetch_assoc();	
	$total_ht = round($sumt['sumtht'], 2, PHP_ROUND_HALF_UP);
	$tva = round($sumt['sumtva'], 2, PHP_ROUND_HALF_UP);
	$total = round($sumt['sumttc'], 2, PHP_ROUND_HALF_UP);
	
	
	//$db->query("UPDATE doli_".$className."_customfields set nombre_d_invites = $newTotal ");	
	$db->query("UPDATE doli_".$className." set total_ht = $total_ht, tva = $tva, $chtotal = $total where rowid = $fk_object ");






	
