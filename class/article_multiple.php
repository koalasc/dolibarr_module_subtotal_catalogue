<!--
 	Koalasc <koalas@cilos.fr>
	Le but de ce script est de pouvoir selectionner plusieurs produits ( avec des quantitées différentes ) et 
	de les insérer directement à une propale ou à une commande client..

	La définition des catégories doit etre ( 1 Caratère catégorie Mere + 1 Caratère en plus à chaque sous catégorie )
 	Ce script est écrit pour gérer jusqu'à 6 Etages de sous catégories.

	A - Premiere catégorie
	|_ A1 - Premiere sous catégorie
	|  |_A11 - Premiere sous catégorie
	|  |	|_ A11A - produit1
	|  |	|_.....
	|  |_ ......
	B - Deuxieme catégorie 
	|_ B1 - ....
	|......

	Pour les Propale et les Commandes client les catégories méres ne seront pas repris comme titre mais utilisées 
	pour créer un sous total par catégories

	Exemple decalage :
	------------------
        <li> <input type="checkbox"> <label class="trigger">Tall Things</label> 
           <ul class="toggle_container"> <li> <input type="checkbox"> <label class="trigger">Buildings</label> 
			<ul class="toggle_container">
				<li> <input type="checkbox"> <label>Andre</label> </li>
				<li> <input type="checkbox"> <label>Andre</label> </li>
				<li> <input type="checkbox"> <label>Andre</label> </li>
				<li> <input type="checkbox"> <label>Andre</label> </li>
			</ul>
            </li> 
        </li>
-->
<html>
<head>
   
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
   <title>Catalogue</title>
   <link href="../css/catalogue.css" rel="stylesheet" >
   <script type="text/javascript" charset="utf-8"src="../js/jquery.js"></script>
   <script type="text/javascript" charset="utf-8"src="../js/catalogue.js"></script>

</head>

<body>
	
<?php

require('../config.php');
dol_include_once('/comm/propal/class/propal.class.php');
dol_include_once('/commande/class/commande.class.php');
dol_include_once('/compta/facture/class/facture.class.php');

global $db,$object;

print_r($object);

$db = mysql_connect($dolibarr_main_db_host, $dolibarr_main_db_user, $dolibarr_main_db_pass);
mysql_select_db($dolibarr_main_db_name, $db);
mysql_query("SET NAMES 'utf8'");
$labelp='<label class="trigger">';
$labelc='</label>';
$ulp='<ul class="toggle_container">';
$ulpp='<ul class="toggle_container" id="prod">';
$ulpp1='<ul class="toggle_container" id="prod" style="margin-left:35px">';
$ulpp3='<ul class="toggle_container" id="prod" style="margin-left:-35px">';
$ulpp4='<ul class="toggle_container" id="prod" style="margin-left:-70px">';
$ulc='</ul>';

echo '<form action="#" method="post" name="multiple">';
echo '<input type="hidden" name="envoi" value="yes">';
echo '<div id="header"> <div style="float:left"> <img src="../img/catalogue.png" style="margin:-2px 14px 0px 0px "> </div> <div style="float:left"><span>Catalogue des Produits & Services </span></div><div style="float:right;margin-right: 150px;;"><input id="button" type="submit" name="submit" value="Valider"> </div></div>';

$idvar=$_GET['idvar'];
$typevar=$_GET['type'];

if(($typevar == 'propal')) {
	$doli_customfields = MAIN_DB_PREFIX."propal_customfields";
	$doli_det = MAIN_DB_PREFIX."propaldet";
} elseif(($typevar == 'commande')) {
	$doli_customfields = MAIN_DB_PREFIX."commande_customfields";
	$doli_det = MAIN_DB_PREFIX."commandedet";
} elseif(($typevar == 'facture')) {
	$doli_customfields = MAIN_DB_PREFIX."facture_customfields";
	$doli_det = MAIN_DB_PREFIX."facturedet";
	$supref = "_ext";
}


/*-------------------------------------------
    		Main view
---------------------------------------------*/

echo '<div id="main">';
$sqltotalinvit = "SELECT piece_pers as nbi , nombre_d_invites as nbp FROM ".MAIN_DB_PREFIX.$typevar."_customfields where fk_$typevar = '$idvar'";
$reqtotalinvit = mysql_query($sqltotalinvit) or die('Erreur SQL !<br>'.$sqltotalinvit.'<br>'.mysql_error());
while($datatotalinvi = mysql_fetch_array($reqtotalinvit)) {
	$totalinvit = $datatotalinvi['nbi']*$datatotalinvi['nbp'];
}

$sqlNP = "select rowid, ref$supref as name from ".MAIN_DB_PREFIX."$typevar where rowid = '$idvar'";
$reqsqlNP = mysql_query($sqlNP) or die('Erreur SQL !<br>'.$sqlenp.'<br>'.mysql_error());
while($datanp = mysql_fetch_array($reqsqlNP)) {
	if(($typevar == 'propal')){
		echo '<div id="sousous"> <div style="float:left">Proposition Commercial N° '.$datanp['name'].'</div><div id="qtydefh">Quantité par default :<input id="qtydef" value="0" type="number" name="qtyc" ></div></div>';
		
		echo '<div id="totalalim"> <div style="float:left">Total quantitée alimentaire : <input id="qtytotalalim" type="text" name="qtytotalalim" size="4" disabled> / <input id="totalinvit" type="text" name="totalinvit" disabled size="4" value="'.$totalinvit.'"></div><br>
					   <div style="float:left">Total pièces froide : <input id="qtypcef" type="text" name="qtypcef" size="18" disabled></div><br>
					   <div style="float:left">Total pièces chaude : <input id="qtypcec" type="text" name="qtypcec" size="18" disabled></div></div>';
		$insertbase = MAIN_DB_PREFIX."propaldet";
	} elseif (($typevar == 'commande')){
		echo '<div id="sousous"> <div style="float:left">Commande Client N° '.$datanp['name'].'</div><div id="qtydefh">Quantité par default :<input id="qtydef" value="0" type="number" name="qtyc" ></div></div>';
		echo '<div id="totalalim"> <div style="float:left">Total quantitée alimentaire : <input id="qtytotalalim" type="text" name="qtytotalalim" size="4" disabled> / <input id="totalinvit" type="text" name="totalinvit" disabled size="4" value="'.$totalinvit.'"></div><br>
					   <div style="float:left">Total pièces froide : <input id="qtypcef" type="text" name="qtypcef" size="18" disabled></div><br>
					   <div style="float:left">Total pièces chaude : <input id="qtypcec" type="text" name="qtypcec" size="18" disabled></div></div>';
		$insertbase = MAIN_DB_PREFIX."commandedet";
	} elseif (($typevar == 'facture')){
		echo '<div id="sousous"> <div style="float:left">Facture Client N° '.$datanp['name'].'</div><div id="qtydefh">Quantité par default :<input id="qtydef" value="0" type="number" name="qtyc" ></div></div>';
		$insertbase = MAIN_DB_PREFIX."facturedet";
		echo '<div id="totalalim"> <div style="float:left">Total quantitée alimentaire : <input id="qtytotalalim" type="text" name="qtytotalalim" size="4" disabled> / <input id="totalinvit" type="text" name="totalinvit" disabled size="4" value="'.$totalinvit.'"></div><br>
					   <div style="float:left">Total pièces froide : <input id="qtypcef" type="text" name="qtypcef" size="18" disabled></div><br>
					   <div style="float:left">Total pièces chaude : <input id="qtypcec" type="text" name="qtypcec" size="18" disabled></div></div>';
	} 
}
echo '</div>';
$snomcategos = $nomcategos;

$sqlcktitre = "select type_de_prestation as cktitre from $doli_customfields where fk_$typevar = '$idvar' ";
$reqsqlcktitre = mysql_query($sqlcktitre) or die('Erreur SQL !<br>'.$sqlcktitree.'<br>'.mysql_error());
$num_rows_ckt = mysql_num_rows($reqsqlcktitre);
if ($num_rows_ckt < '1') {
	echo '<div id="notitre"> <center>!!! Attention il n\'y a pas de Type de prestation de renseigné </center> </div>';
} 

echo '<div id="contprod">';
echo '<ul>';

// Recherche les catégories niveau 1
$sqln1 = "select rowid, label from ".MAIN_DB_PREFIX."categorie where fk_parent = '0' order by label";
$reqsqln1 = mysql_query($sqln1) or die('Erreur SQL !<br>'.$sqlen1.'<br>'.mysql_error());

while($datan1 = mysql_fetch_array($reqsqln1))  {

    switch (true) {

		case strstr($datan1['label'], "A -"):
        		$classcatm = "classcatmalim";
        		break;
		case strstr($datan1['label'], "B -"):
        		$classcatm = "classcatmbois";
        		break;
		case strstr($datan1['label'], "P -"):
        		$classcatm = "classcatmpers";
        		break;
		case strstr($datan1['label'], "R -"):
        		$classcatm = "classcatmmob";
        		break;
    		case strstr($datan1['label'], "U -"):
        		$classcatm = "classcatmliv";
        		break;
     }

    echo '<li id="catmere" class="catmere '.$classcatm.'" > <input type="checkbox" name="idproduit[]" value="'.$datan1['label'].'"><label class="trigger">'.$datan1['label'].$labelc;

    //echo 'label'.$datan1['label'];
    //echo 'class'.$classcatm;

    $idcat1=$datan1['rowid'];			// Recherche s'il existe des catégories niveau 2
    $sqln2 = "select rowid, label from ".MAIN_DB_PREFIX."categorie where fk_parent = '$idcat1' order by label";
    $reqsqln2 = mysql_query($sqln2) or die('Erreur SQL !<br>'.$sqlen2.'<br>'.mysql_error());
    $nblignesn2 = mysql_num_rows($reqsqln2); 
    if ($nblignesn2 === 0) {      		// S'il n'y a pas de catégories niveau 2, on recherche les produits

	$sqln2p = "select ".MAIN_DB_PREFIX."product.rowid as idprod, ".MAIN_DB_PREFIX."product.label as label from ".MAIN_DB_PREFIX."product, ".MAIN_DB_PREFIX."categorie_product ".MAIN_DB_PREFIX."product.rowid = ".MAIN_DB_PREFIX."categorie_product.fk_product and ".MAIN_DB_PREFIX."categorie_product.fk_categorie = '$idcat1";
	$reqsqln2p = mysql_query($sqln2p) or die('Erreur SQL !<br>'.$sqlen2p.'<br>'.mysql_error());
	echo $ulpp;
	while($datan2p = mysql_fetch_array($reqsqln2p))
  	  {
	    echo '<li><input type="checkbox" name="idproduit[]" id="'.$datan2p['idprod'].'" value="'.$datan2p['idprod'].'" onclick="qtydefaul('.$datan2p['idprod'].')"><label id="qtylabel" for"'.$datan2p['idprod'].'"> &nbsp;&nbsp;&nbsp; '.substr(strip_tags($datan2p['label']), 0, 60).'</label>';
 	    echo '<input type="number" value="0" id="qtyprod" name="Qteproduit'.$datan2p['idprod'].'" size="3"></input>';
	    echo '<input type="hidden" name="Hideproduit'.$datan2p['idprod'].'" value="'.$datan2p['label'].'"> </li>';
	  }
	echo $ulc;	

    } else {
	echo $ulp;
	while($datan2 = mysql_fetch_array($reqsqln2)) {
		switch (true) {

			case strstr($datan2['label'], "A1 -"):
        			$classcatm2 = "classpcef";
        			break;
			case strstr($datan2['label'], "A2 -"):
        			$classcatm2 = "classpcec";
        			break;
			default:
				$classcatm2 = "classother";
			
		}
		echo '<li class="'.$classcatm2.'"> <input type="checkbox" name="idproduit[]" value="'.$datan2['label'].'"><label class="trigger">'.$datan2['label'].$labelc;
		$idcat2=$datan2['rowid'];		// Recherche s'il existe des catégories niveau 3
		$sqln3 = "select rowid,label from ".MAIN_DB_PREFIX."categorie where fk_parent = '$idcat2' order by label";
    		$reqsqln3 = mysql_query($sqln3) or die('Erreur SQL !<br>'.$sqlen3.'<br>'.mysql_error());
		$nblignesn3 = mysql_num_rows($reqsqln3); 
	 	if ($nblignesn3 === 0) { 		// S'il n'y a pas de catégories niveau 3, on recherche les produits
			$sqln3p = "select ".MAIN_DB_PREFIX."product.rowid as idprod, ".MAIN_DB_PREFIX."product.label as label from ".MAIN_DB_PREFIX."product, ".MAIN_DB_PREFIX."categorie_product where ".MAIN_DB_PREFIX."product.rowid = ".MAIN_DB_PREFIX."categorie_product.fk_product and ".MAIN_DB_PREFIX."categorie_product.fk_categorie = '$idcat2'";
			$reqsqln3p = mysql_query($sqln3p) or die('Erreur SQL !<br>'.$sqlen3p.'<br>'.mysql_error());
			echo $ulpp1;
			while($datan3p = mysql_fetch_array($reqsqln3p))
			{
			   echo '<li><input type="checkbox" name="idproduit[]" id="'.$datan3p['idprod'].'" value="'.$datan3p['idprod'].'" onclick="qtydefaul('.$datan3p['idprod'].')"><label id="qtylabel" for="'.$datan3p['idprod'].'"> &nbsp;&nbsp;&nbsp; '.substr(strip_tags($datan3p['label']), 0, 90).'</label>';
			   echo '<input type="number" value="0" id="qtyprod" name="Qteproduit'.$datan3p['idprod'].'" size="3">';
	    		   echo '<input type="hidden" name="Hideproduit'.$datan3p['idprod'].'" value="'.$datan3p['label'].'"> </li>';
			}
   			echo $ulc;
	
    		     } else {
			echo $ulp;
			while($datan3 = mysql_fetch_array($reqsqln3)) {
			    echo '<li> <input type="checkbox" name="idproduit[]" value="'.$datan3['label'].'"><label class="trigger">'.$datan3['label'].$labelc;
 			    $idcat3=$datan3['rowid'];
			    $sqln4 = "select rowid,label from ".MAIN_DB_PREFIX."categorie where fk_parent = '$idcat3' order by label";
    			    $reqsqln4 = mysql_query($sqln4) or die('Erreur SQL !<br>'.$sqlen4.'<br>'.mysql_error());
			    $nblignesn4 = mysql_num_rows($reqsqln4); 
			    if ($nblignesn4 === 0) {
				$sqln4p = "select ".MAIN_DB_PREFIX."product.rowid as idprod, ".MAIN_DB_PREFIX."product.label as label from ".MAIN_DB_PREFIX."product, ".MAIN_DB_PREFIX."categorie_product where ".MAIN_DB_PREFIX."product.rowid = ".MAIN_DB_PREFIX."categorie_product.fk_product and ".MAIN_DB_PREFIX."categorie_product.fk_categorie = '$idcat3'";
				$reqsqln4p = mysql_query($sqln4p) or die('Erreur SQL !<br>'.$sqlen4p.'<br>'.mysql_error());
				echo $ulpp;
				while($datan4p = mysql_fetch_array($reqsqln4p))
				{
				    echo '<li><input type="checkbox" name="idproduit[]" id="'.$datan4p['idprod'].'" value="'.$datan4p['idprod'].'" onclick="qtydefaul('.$datan4p['idprod'].')"><label id="qtylabel" for="'.$datan4p['idprod'].'"> &nbsp;&nbsp;&nbsp; '.substr(strip_tags($datan4p['label']), 0, 90).'</label>';
				    echo '<input type="number" value="0" id="qtyprod" name="Qteproduit'.$datan4p['idprod'].'" size="3">';
	    			    echo '<input type="hidden" name="Hideproduit'.$datan4p['idprod'].'" value="'.$datan4p['label'].'"> </li>';
				}
				echo $ulc;
			    } else {
				echo $ulp;
				while($datan4 = mysql_fetch_array($reqsqln4)) {
				    echo '<li> <input type="checkbox" name="idproduit[]" value="'.$datan4['label'].'"><label class="trigger">'.$datan4['label'].$labelc;
				    $idcat4=$datan4['rowid'];
				    $sqln5 = "select rowid,label from ".MAIN_DB_PREFIX."categorie where fk_parent = '$idcat4' order by label";
    				    $reqsqln5 = mysql_query($sqln5) or die('Erreur SQL !<br>'.$sqlen5.'<br>'.mysql_error());
				    $nblignesn5 = mysql_num_rows($reqsqln5);
				    if ($nblignesn5 === 0) {
					$sqln5p = "select ".MAIN_DB_PREFIX."product.rowid as idprod, ".MAIN_DB_PREFIX."product.label as label from ".MAIN_DB_PREFIX."product, ".MAIN_DB_PREFIX."categorie_product where ".MAIN_DB_PREFIX."product.rowid = ".MAIN_DB_PREFIX."categorie_product.fk_product and ".MAIN_DB_PREFIX."categorie_product.fk_categorie = '$idcat4'";
					$reqsqln5p = mysql_query($sqln5p) or die('Erreur SQL !<br>'.$sqlen5p.'<br>'.mysql_error());
					echo $ulpp3;
					while($datan5p = mysql_fetch_array($reqsqln5p))
					{
					    echo '<li><input type="checkbox" name="idproduit[]" id="'.$datan5p['idprod'].'" value="'.$datan5p['idprod'].'" onclick="qtydefaul('.$datan5p['idprod'].')"><label id="qtylabel" for="'.$datan5p['idprod'].'"> &nbsp;&nbsp;&nbsp; '.substr(strip_tags($datan5p['label']), 0, 90).'</label>';
					    echo '<input type="number" value="0" id="qtyprod" name="Qteproduit'.$datan5p['idprod'].'" size="3">';
	    				    echo '<input type="hidden" name="Hideproduit'.$datan5p['idprod'].'" value="'.$datan5p['label'].'"> </li>';
					}
					echo $ulc;
			    	     } else {
					echo $ulp;	
					while($datan5 = mysql_fetch_array($reqsqln5)) {
					   echo '<li> <input type="checkbox" name="idproduit[]" value="'.$datan5['label'].'"><label class="trigger">'.$datan5['label'].$labelc;
					   $idcat5=$datan5['rowid'];
					   $sqln6p = "select ".MAIN_DB_PREFIX."product.rowid as idprod, ".MAIN_DB_PREFIX."product.label as label from ".MAIN_DB_PREFIX."product, ".MAIN_DB_PREFIX."categorie_product where ".MAIN_DB_PREFIX."product.rowid = ".MAIN_DB_PREFIX."categorie_product.fk_product and ".MAIN_DB_PREFIX."categorie_product.fk_categorie = '$idcat5'";
					   $reqsqln6p = mysql_query($sqln6p) or die('Erreur SQL !<br>'.$sqlen6p.'<br>'.mysql_error());	
					   echo $ulpp4;	
					   while($datan6p = mysql_fetch_array($reqsqln6p))
				   	   {
						echo '<li><input type="checkbox" name="idproduit[]" id="'.$datan6p['idprod'].'" value="'.$datan6p['idprod'].'" onclick="qtydefaul('.$datan6p['idprod'].')"><label id="qtylabel" for="'.$datan6p['idprod'].'">  '.substr(strip_tags($datan6p['label']), 0, 90).'</label></input>';
						echo '<input type="number" value="0" id="qtyprod" name="Qteproduit'.$datan6p['idprod'].'" size="3">';
	    					echo '<input type="hidden" name="Hideproduit'.$datan6p['idprod'].'" value="'.$datan6p['label'].'"> </li>';
					   }
					   echo $ulc;
					   echo '</li>';
				        }
      					echo $ulc;
				     }
				     echo '</li>';
				}
				echo $ulc;
			    }
			    echo '</li>';
		     }
		     echo $ulc;
		}
		echo '</li>';	
	}
	echo $ulc;
    }
    echo '</li>';
  }
  echo $ulc;

echo '<div style="font-size:12px;margin:0px 0px 0px 170px;width=500px;background-color:#f07b6e;border-radius: 10px;padding: 12px;width:70%">';
echo '<img src="../../theme/azely/img/info.png" border="0" alt="" title=""> <span> Attention !!! </span><br>';
echo '<span>Ce formulaire reprend les produits et services potentiellement déjà insérés et ils sont donc sélectionnés.<br>';
echo 'Si vous dé-sélectionnez les produits il ne seront plus dans la proposition ou la commande.<br> ';
echo 'Les produits "dit orphelins" ( ajouter manuellement et non dans la base ) seront ajouter dans une catégories orphelins à vous de les replacer ensuite<br></span>';
echo '</div><br>';

echo '</div><br>';
echo '<div align="center" style="width:80%; height:70%; margin-right: auto; margin-left: auto;">';

echo '</div>';

echo '</form>';


/*-------------------------------------------
       Recup id  already selected
---------------------------------------------*/

//$sqlrecupdt = "select fk_product, qty from $doli_det where fk_$typevar = '$idvar' and fk_product != '66996699' and fk_product is not null";
$sqlrecupdt = "select fk_product, qty from $doli_det where fk_$typevar = '$idvar' and fk_product is not null and fk_product != '0'";
$reqsqlrecupdt = mysql_query($sqlrecupdt) or die('Erreur SQL !<br>'.$sqlrecupdt.'<br>'.mysql_error());
echo '<script> $(function() {';
while($datarecupdt = mysql_fetch_array($reqsqlrecupdt))  {
	echo 'document.getElementById("'.$datarecupdt['fk_product'].'").click();';
	echo 'document.getElementsByName("Qteproduit'.$datarecupdt['fk_product'].'")[0].value= "'.$datarecupdt['qty'].'";';
}
echo '}); </script>';
echo '<script> $(function(){
  		var totalqtyalim = 0;
  		$(\'.classcatmalim\').find(\'input[type="number"]\').each(function(){
    			totalqtyalim += parseInt($(this).val());
  		});
  		document.getElementsByName("qtytotalalim")[0].value = totalqtyalim;

  		var totalqtypcef = 0;
  		$(\'.classpcef\').find(\'input[type="number"]\').each(function(){
    			totalqtypcef += parseInt($(this).val());
  		});
  		document.getElementsByName("qtypcef")[0].value = totalqtypcef;

  		var totalpcec = 0;
  		$(\'.classpcec\').find(\'input[type="number"]\').each(function(){
    			totalpcec += parseInt($(this).val());
  		});
  		document.getElementsByName("qtypcec")[0].value = totalpcec;
	});

</script>';

/*-------------------------------------------
    	    Recup orphelin id
---------------------------------------------*/

$sqlrecupor = "select description, tva_tx, qty, total_ht, subprice, buy_price_ht, fk_product_fournisseur_price, total_ttc from $doli_det where fk_$typevar = '$idvar' and `fk_product` is null and `product_type` = '0'";
$reqsqlrecupor = mysql_query($sqlrecupor) or die('Erreur SQL !<br>'.$sqlrecupor.'<br>'.mysql_error());

/*-------------------------------------------
             Insert in base 
---------------------------------------------*/


if (isset($_POST) && isset($_POST['idproduit'])){


	//Clean de la propal actuel
	$sqlclean = "delete from $insertbase where fk_$typevar = '$idvar'";
	$reqsqlclean = mysql_query($sqlclean) or die('Erreur SQL !<br>'.$sqlclean.'<br>'.mysql_error());

	$rang = '0';
	$initnb = '0';
	$nameprodss = '';
	foreach($_POST['idproduit'] as $name1 => $value_checkbox){
 	    $output = '';
 	    $special_code = '104777';
 	    $qtyp = '';
            $name_input = "Qteproduit".$value_checkbox;
  	    $name_hidden = "Hideproduit".$value_checkbox;
 	    foreach($_POST as $name2 => $value_input){
	
	 	if ($name2 == $name_input && $value_input != ''){
	   		$qtyp = $value_input. " ".$_POST[$name_hidden];
	 	}
	    }
 	    if(strpos($value_checkbox, ' - '))	{		// On recupere les categorie pour les afficher ( definit avec un - )
 	    
		$catego = explode(" - ", $value_checkbox);
		$nbcaractcat = strlen($catego[0]);	
		$product_type= '9';
		if(($nbcaractcat == '1' )) {			// 1 caratère = cat niveau 1
		    $nomcatego = $catego[1];
		    $typeinsert = '10';
		    $qtyp = '99';
		} elseif(($nbcaractcat == '2' )) {
		    $typeinsert = '20';				// 2 caratère = cat niveau 2 = titre
		    $qtyp = '1';
		} else {
		    $typeinsert = '30';				// +2 caratère = cat niveau +2 = soustitre
		    $qtyp = '2';	
		}
		$prodid=$catego[1];				// Sinon recup id produit
	
 	    }else{
		$typeinsert = '40';
		$prodid = $value_checkbox;
 	    }
	    $prodid = str_replace("'", "\'", "$prodid");   	// Echap devant tout les apostrophes
	    // formatage du nom de la categorie de niv1 pour affichage total
	    $vowels = array("les","la","le");
	    	
	    $nomcategos = str_replace($vowels, "", $nomcategos);
	    
 	    $nomcategos = mb_strtolower($nomcatego, 'UTF-8');
	    

	    if(stristr($nomcatego, '\'') === FALSE) {
			$nomcatego = str_replace("'", "\'", "$nomcatego");
	    }

	    switch ($snomcategos) {
		case "carte alimentaire":
        		$snomcategos = "alimentaire";
        		break;
		case "livraison & reprise":
        		$snomcategos = "livraison & reprise";
        		break;
		case "le personnel":
        		$snomcategos = "personnel";
        		break;
    		case "les boissons":
        		$snomcategos = "boissons";
        		break;
    		case "le mobilier & les arts de la table":
        		$snomcategos = "mobilier & arts de la table";
        		break;
		case "Carte Printemps été 2016":
        		$snomcategos = "carte printemps été 2016";
        		break;
		case "Carte année 2015":
			$snomcategos = "carte année 2015";
        		break;
	    }

	
	if(($typeinsert == '10')) {

		if ($initnb != '0') {

	    		//echo 'Creation Sous Total <br>';
	    		//echo 'Somme des produits id :'.$nameprodss.'<br>';	
			
	    		$sqlinss = "insert into $insertbase (fk_$typevar, label, qty, product_type, special_code, rang ) value ('$idvar', 'Sous-total $snomcategos', '99', '9', '$special_code', '$rang')";
			
			//exit;
			
    	    		$reqsqlinss = mysql_query($sqlinss) or die('Erreur SQL !<br>'.$sqlinsse.'<br>'.mysql_error());
	    		$nameprodss = '';

	    		//echo 'Creation ligne vide titre : '.$prodid.'<br>';
	    		$snomcategos = $nomcategos;
			
	    		//echo 'Creation ligne titre : '.$prodid.'<br>';
		
                	$sqldesccatego = "select description from ".MAIN_DB_PREFIX."categorie where label like '%- $nomcatego'";
    	  		$reqsqldesccatego = mysql_query($sqldesccatego) or die('Erreur SQL !<br>'.$sqldesccatego.'<br>'.mysql_error()); 
                	while($datadesccato = mysql_fetch_array($reqsqldesccatego)) {
				$desccatego = $datadesccato['description'];
	        	}
                	$desccatego = str_replace("'", "\'", "$desccatego");   	// Echap devant tout les apostrophes
	    		$sqlinti = "insert into $insertbase (fk_$typevar, label, description, qty, product_type, special_code, rang ) value ('$idvar', '$nomcatego','$desccatego', '1', '9', '$special_code', '$rang')";
    	    		$reqsqlinti = mysql_query($sqlinti) or die('Erreur SQL !<br>'.$sqlintie.'<br>'.mysql_error());

		} else {
			$snomcategos = $nomcategos;
			$sqlpresta = "select type_de_prestation as presta, prestation_comp as comp from $doli_customfields where fk_$typevar = '$idvar' ";	
	    		$reqsqlpresta = mysql_query($sqlpresta) or die('Erreur SQL !<br>'.$sqlpresta.'<br>'.mysql_error());
	    		while($datapresta = mysql_fetch_array($reqsqlpresta)) {
				$presta = $datapresta['presta'];
				$desccatego = $datapresta['comp'];
	        	}
                	$presta = str_replace("'", "\'", "$presta");   	// Echap devant tout les apostrophes
                	$desccatego = str_replace("'", "\'", "$desccatego");   	// Echap devant tout les apostrophes
	    		//$sqlinti = "insert into $insertbase (fk_$typevar, fk_product, label, description, qty, product_type, special_code, rang ) value ('$idvar', '66996699', '$presta','$desccatego', '1', '9', '$special_code', '$rang')";	
			$sqlinti = "insert into $insertbase (fk_$typevar, fk_product, label, description, qty, product_type, special_code, rang ) value ('$idvar', '', '$presta','$desccatego', '1', '9', '$special_code', '$rang')";	
    	   		$reqsqlinti = mysql_query($sqlinti) or die('Erreur SQL !<br>'.$sqlintie.'<br>'.mysql_error());
		
		}


	} elseif(($typeinsert == '20')) {

	    //echo 'Creation Titre : '.$prodid.'<br>';
	    $sqlinti = "insert into $insertbase (fk_$typevar, description, qty, product_type, special_code, rang ) value ('$idvar', '$prodid', '2', '9', '$special_code', '$rang')";
    	    $reqsqlinti = mysql_query($sqlinti) or die('Erreur SQL !<br>'.$sqlintie.'<br>'.mysql_error());
	
	} elseif(($typeinsert == '30')) {

	    //echo 'Creation Sous-Titre : '.$prodid.'<br>';
	    $sqlinsti = "insert into $insertbase (fk_$typevar, description, qty, product_type, special_code, rang ) value ('$idvar', '$prodid', '2', '9', '$special_code', '$rang')";
    	    $reqsqlinsti = mysql_query($sqlinsti) or die('Erreur SQL !<br>'.$sqlinstie.'<br>'.mysql_error());

	} elseif(($typeinsert == '40')) {

	    //echo 'Creation Produit : '.$prodid.'<br>';
	    $sqlinprinfo = "select ".MAIN_DB_PREFIX."product.tva_tx as tva, ".MAIN_DB_PREFIX."product.price as subprice, ".MAIN_DB_PREFIX."product.price_ttc as pricettc, ".MAIN_DB_PREFIX."product.fk_product_type as product_type, ".MAIN_DB_PREFIX."product_fournisseur_price.unitprice as buy_price, ".MAIN_DB_PREFIX."product_fournisseur_price.rowid  as rowiddpfp from ".MAIN_DB_PREFIX."product, ".MAIN_DB_PREFIX."product_fournisseur_price where ".MAIN_DB_PREFIX."product.rowid = ".MAIN_DB_PREFIX."product_fournisseur_price.fk_product and ".MAIN_DB_PREFIX."product.rowid = '$prodid' ";
	    $reqsqlinprinfo = mysql_query($sqlinprinfo) or die('Erreur SQL !<br>'.$sqlinprinfoe.'<br>'.mysql_error());
	    while($datapinfo = mysql_fetch_array($reqsqlinprinfo)) {
		$tva =  $datapinfo['tva'];
		$subprice = $datapinfo['subprice'];
		$fk_product_fournisseur_pric = $datapinfo['rowiddpfp'];
		$total_ht = $datapinfo['subprice']*$qtyp;
		$total_ttc = $datapinfo['pricettc']*$qtyp;
		$total_tva = $total_ttc-$total_ht;
		$product_type = $datapinfo['product_type'];
		$buy_price = $datapinfo['buy_price'];
	    }
	    
	    $qtyp = str_replace("'", "\'", "$qtyp");   	// Echap devant tout les apostrophes
	    //echo $idvar.' - '.$prodid.' - '.$tva.' - '.$qtyp.' - '.$subprice.' - '.$total_ht.' - '.$total_tva.' - '.$total_ttc.' - '.$product_type.' - '.$buy_price;
	    $sqlinpr = "insert into $insertbase (fk_$typevar, fk_product, tva_tx, qty, subprice, total_ht, total_tva, total_ttc, product_type, buy_price_ht, fk_product_fournisseur_price, special_code, rang ) value ('$idvar', '$prodid', '$tva', '$qtyp', '$subprice', '$total_ht', '$total_tva','$total_ttc', '$product_type', '$buy_price', '$fk_product_fournisseur_pric','0', '$rang')";
    	    $reqsqlinpr = mysql_query($sqlinpr) or die('Erreur SQL !<br>'.$sqlinpre.'<br>'.mysql_error());
	    $nameprodss = $prodid.' '.$nameprodss;

	} // Fin du if
	$initnb++ ;
	$rang++;
     } // Fin du foreach
     //echo 'Creation Sous Total : <br>';
     //echo 'Somme des produits id :'.$nameprodss.'<br>';
     switch ($nomcatego) {
		case "Carte alimentaire":
        		$nomcatego = "alimentaire";
        		break;
		case "Livraison & Reprise":
        		$nomcatego = "livraison & reprise";
        		break;
		case "Le personnel":
        		$nomcatego = "personnel";
        		break;
    		case "Les boissons":
        		$nomcatego = "boissons";
        		break;
    		case "Le mobilier & les arts de la table":
        		$nomcatego = "mobilier & arts de la table";
        		break;
		case "Carte Printemps été 2016":
        		$nomcatego = "carte printemps été 2016";
        		break;
		case "Carte année 2015":
			$nomcatego = "carte année 2015";
        		break;
     }

	 $sqlinss = "insert into $insertbase (fk_$typevar, label, qty, product_type, special_code, rang ) value ('$idvar', 'Sous-total $nomcatego', '99', '9', '$special_code', '$rang')";
	
     $reqsqlinss = mysql_query($sqlinss) or die('Erreur SQL !<br>'.$sqlinsse.'<br>'.mysql_error());

    	// Ajout des produit orphelins
	$nbsqlrecupor = mysql_num_rows($reqsqlrecupor);
	if (($nbsqlrecupor != 0)){
		$rang++;
		$sqlinti = "INSERT INTO $insertbase (fk_$typevar, description, label, qty, product_type, special_code, rang ) value ('$idvar', 'A classer',' ******** Produits ou Service orphelins ******** ', '1', '9', '$special_code', '$rang')";
		$reqsqlinti = mysql_query($sqlinti) or die('Erreur SQL !<br>'.$sqlintie.'<br>'.mysql_error());
		$rang++;
		while($datarecupor = mysql_fetch_array($reqsqlrecupor))  {
			$description = $datarecupor['description'];
			$total_tva = $datarecupor['tva_tx'];
			$qtyp = $datarecupor['qty'];
			$total_ht = $datarecupor['total_ht'];
			$total_ttc = $datarecupor['total_ttc'];
			$fk_product_fournisseur_price = $datarecupor['fk_product_fournisseur_price']; 
			$subprice = $datarecupor['subprice'];
			$buy_price_ht = $datarecupor['buy_price_ht'];
			$sqloprec = "INSERT INTO $insertbase (fk_$typevar, description, tva_tx, qty, subprice, total_ht, total_tva, total_ttc, product_type, buy_price_ht, fk_product_fournisseur_price, special_code, rang ) value ('$idvar', '$description', '$tva', '$qtyp', '$subprice', '$total_ht', '$total_tva','$total_ttc', '0', '$buy_price_ht', '$fk_product_fournisseur_price', '0', '$rang')";
			$reqsqloprec = mysql_query($sqloprec) or die('Erreur SQL !<br>'.$sqloprec.'<br>'.mysql_error());
			$rang++;
		}
		
	}

	
     $nameprodss = '';
     if(($typevar == 'propal')){
       		$sqlend = "UPDATE ".MAIN_DB_PREFIX."$typevar set total_ht = (select sum(total_ht) from $insertbase where fk_$typevar = '$idvar'), tva = (select sum(total_tva) from $insertbase where fk_$typevar = '$idvar'), total = (select sum(total_ttc) from $insertbase where fk_$typevar = '$idvar') where rowid = '$idvar';";
       		$reqsqlend = mysql_query($sqlend) or die('Erreur SQL !<br>'.$sqlende.'<br>'.mysql_error());
     } elseif (($typevar == 'commande')){
       		$sqlend = "UPDATE ".MAIN_DB_PREFIX."$typevar set total_ht = (select sum(total_ht) from $insertbase where fk_$typevar = '$idvar'), tva = (select sum(total_tva) from $insertbase where fk_$typevar = '$idvar'), total_ttc = (select sum(total_ttc) from $insertbase where fk_$typevar = '$idvar') where rowid = '$idvar';";
       		$reqsqlend = mysql_query($sqlend) or die('Erreur SQL !<br>'.$sqlende.'<br>'.mysql_error());

     } elseif (($typevar == 'facture')){
       		$sqlend = "UPDATE ".MAIN_DB_PREFIX."$typevar set total_ht = (select sum(total_ht) from $insertbase where fk_$typevar = '$idvar'), tva = (select sum(total_tva) from $insertbase where fk_$typevar = '$idvar'), total_ttc = (select sum(total_ttc) from $insertbase where fk_$typevar = '$idvar') where rowid = '$idvar';";
       		$reqsqlend = mysql_query($sqlend) or die('Erreur SQL !<br>'.$sqlende.'<br>'.mysql_error());

     }
}



$envoi = $_POST['envoi'];
if ($envoi == 'yes') {
	echo '<script type="text/javascript">';
	echo 'window.close();';
	echo '</script>';
}

?>



</body>
</html>			
