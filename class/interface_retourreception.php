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
		$doli_det = 'doli_propaldet';
		$chtotal = 'total';
		$typevar = 'propal';
	} elseif(($className == 'commande')) {
		$doli_det = 'doli_commandedet';
		$chtotal = 'total_ttc';
		$typevar = 'commande';
	}
	$rang = '0';
	$sqltirece = $db->query("select rang FROM $doli_det WHERE label like '%Retour après reception%' and fk_commande = '$fk_object'");
	$fetchsqltirece = $sqltirece->fetch_assoc();
	$rang = $fetchsqltirece['rang'];
	
	if(($rang>0)){
		$rang++;
	} else {
		$sqlrang = $db->query("select MAX(rang) as rang from $doli_det where fk_commande = '$fk_object'");
		$fetchsqlrang = $sqlrang->fetch_assoc();
		$rang = $fetchsqlrang['rang'];
		$rang++;
		$db->query("insert into $doli_det (fk_$typevar, description, label, qty, product_type, special_code, rang ) value ('$fk_object', ' Retour après reception ',' Retour après reception ', '1', '9', '104777', '$rang')");
	}

	$listpost = GETPOST('listpost');
	$listpost = rtrim($listpost, ':'); 
	$tablistpost = explode(":", $listpost);
	foreach($tablistpost as $valeur) {
		$newqty = GETPOST('qtyr_'.$valeur.'');
		$rang++;
		if(($newqty>0)){
			$sqlrecinf = $db->query("select price, tva_tx FROM $doli_det WHERE rowid = '$valeur'");
			$fetchsqlrecinf = $sqlrecinf->fetch_assoc();
			$tva = $fetchsqlrecinf['tva_tx'];
			$price = $fetchsqlrecinf['price'];
			$total_ht = -$newqty*$price;
			$total_tva = $total_ht*$tva/100;
			$total_ttc = $total_ht+$total_ttc;

			$db->query("INSERT into $doli_det (fk_commande, fk_product, label, description, tva_tx, qty, price , subprice, total_ht, total_tva, total_ttc, product_type, date_start, date_end, buy_price_ht, fk_product_fournisseur_price, special_code, rang ) select fk_commande ,fk_product, label, 'Retour reception', tva_tx, '-$newqty', price , subprice, '$total_ht', '$total_tva', '$total_ttc', product_type, date_start, date_end, '$price', '', special_code, $rang FROM $doli_det WHERE rowid = '$valeur'");
		}

	}


	$reqsumt = $db->query("SELECT SUM(total_ht) as sumtht, SUM(total_tva) as sumtva, SUM(total_ttc) as sumttc FROM $doli_det where fk_$className = $fk_object");
	$sumt = $reqsumt->fetch_assoc();	
	$total_ht = round($sumt['sumtht'], 2, PHP_ROUND_HALF_UP);
	$tva = round($sumt['sumtva'], 2, PHP_ROUND_HALF_UP);
	$total = round($sumt['sumttc'], 2, PHP_ROUND_HALF_UP);
	
	$db->query("UPDATE doli_".$className." set total_ht = $total_ht, tva = $tva, $chtotal = $total where rowid = $fk_object ");
	$rang++;
	$sqlsstt = $db->query("select rang FROM $doli_det WHERE label like '%Sous-total Retour%' and fk_commande = '$fk_object'");
	$fetchsqlsstt = $sqlsstt->fetch_assoc();
	$rangss = $fetchsqlsstt['rang'];
	if((!$rangss>0)){
		$db->query("insert into $doli_det (fk_$typevar, label, qty, product_type, special_code, rang ) value ('$fk_object', 'Sous-total Retour ', '99', '9', '104777', '$rang')");
	}




	
