<!--
 	Koalasc <koalas@cilos.fr>
	Date de creation : 28 novembre 2014
	Creation Commande fournisseur à partir d'une commande client
	Creation d'une base ".MAIN_DB_PREFIX."cmdeclient_over_cmdefournisseur (rowid, id_cmdeclient, id_fournisseur, id_cmdefournisseur, status)
-->
<html>
<head>
   
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
   <title>Dispatch fournisseur </title>
   <link href="../css/catalogue.css" rel="stylesheet" >
   <script type="text/javascript" charset="utf-8"src="../js/jquery.js"></script>
   <style>
	#button {
		align:right;
		background-color: #197489;
		border-color: #0083A2;
		width: 100px;
		height:30px;
		font-size:15px;
		font-family: 'Titillium Web', sans-serif, bold;
		-webkit-box-shadow: inset 0 1px 0 rgba(235,235,235,.6);
		box-shadow: inset 0 1px 0 rgba(235,235,235,.6);
		color: #fff;
	}
	#prod li {
	   list-style-type:none;
	}
	
	#qtyprod {
	   width:100%;
	   text-align:center;
	   padding:2px; 
	   margin-left: 50px;
	   border:solid 1px black; 
  	   border-radius:5px;
	}
	#qtydef {
	   text-align:center;
	   padding:2px; 
	   margin-left: 50px;
	   border:solid 1px black; 
  	   border-radius:5px;
	}

	#qtylabel {
	   display: block;
	   float:left;
	   text-align: left;
	   width:70%;
	}
	#sousous {
    		height: 20px;		
	}
   </style>
   <script>
	//function empeche de remplir lettre A-Z dans les input number
	$(function() {
		$('input[type="number"]').keyup(function(){
    			reg = new RegExp("[^0-9\.]", "g");
    			_val = $(this).val();
    			_val.replace(reg, "");
   			 $(this).val( _val );
		});
	});
	// function decalage ul li ul li ul li ...
	$(document).ready(function(){
	   $("ul.toggle_container").hide();
	   $("label.trigger").click(function(){
	      $(this).toggleClass("active").next().slideToggle("fast");
              return false; 
           });
         });
	// function auto liason mere fille petite-fille .... ( sectionne automatiquement li mere si fille est selectionner et ainsi de suite )
	$(function() {
 	   $('input[type="checkbox"]').change(function(e) {
              var checked = $(this).prop("checked"),
              
	      container = $(this).parent(),
              siblings = container.siblings();
  		
              container.find('input[type="checkbox"]').prop({
                 indeterminate: false,
                 checked: checked
              });
  
              function checkSiblings(el) {
                 var parent = el.parent().parent(),
                 all = true;
                 el.siblings().each(function() {
                   return all = ($(this).children('input[type="checkbox"]').prop("checked") === checked);
                 });
  
                 if (all && checked) {
                 parent.children('input[type="checkbox"]').prop({
                    indeterminate: false,
                    checked: checked,
		    
                 });
                 checkSiblings(parent);
                 } else if (all && !checked) {
                     parent.children('input[type="checkbox"]').prop("checked", checked);
		     parent.children('input[type="checkbox"]').prop("indeterminate", (parent.find('input[type="checkbox"]:checked').length > 0));	
                     //parent.children('input[type="checkbox"]').prop("indeterminate", (parent.find('input[type="checkbox"]:checked').length > 0));
                     checkSiblings(parent);
                 } else {
                     el.parents("li").children('input[type="checkbox"]').prop({
                     indeterminate: true,
                     checked: true
                 });
                 }
              }
           checkSiblings(container);
         });
       });


</script>
</head>

<body>
	
<?php

require('../config.php');
require('../../conf/conf.php');

session_start();

$mysqli = new mysqli($dolibarr_main_db_host, $dolibarr_main_db_user, $dolibarr_main_db_pass, $dolibarr_main_db_name);
$mysqli->set_charset("utf8");
if ($mysqli->connect_error) {
        die('Erreur de connexion ('.$mysqli->connect_errno.')'. $mysqli->connect_error);
}


$idvar=$_GET['idvar'];
$iduser=$_GET['iduser'];

// recuperation client commande date-reception
$sqlnotepriv = "select dc.ref as ref, ds.nom as nom, dce.dhd as dhd from ".MAIN_DB_PREFIX."commande as dc, ".MAIN_DB_PREFIX."societe as ds, ".MAIN_DB_PREFIX."commande_extrafields as dce where dc.fk_soc = ds.rowid and dc.rowid = dce.fk_object and dc.rowid = '$idvar'";
$reqsqlnotepriv = $mysqli->query($sqlnotepriv) or die('Erreur SQL !<br>'.$sqlnoteprive.'<br>'.mysql_error());
while($datanotepriv = $reqsqlnotepriv->fetch_assoc()) {
	$daterecep = $datanotepriv['dhd'];
	$daterecep = explode(" ", $daterecep);
	$daterecep = $daterecep[0];
	$refcc = $datanotepriv['ref'];
	$refclient = $datanotepriv['nom']; 
	//echo $daterecep;
	//$noteprivate = $datanotepriv['nom'].' - '.date("d/m/Y", strtotime($datanotepriv['datel'])).' - '.$datanotepriv['ref'];
	//$noteprivate = str_replace("'", "\'", "$noteprivate");
	//$notepublic = "Ref Commande client : ".$datanotepriv['ref']."\t\nReception prévu le :".date("d/m/Y", strtotime($datanotepriv['datel'])); 
}
//echo $noteprivate;

$labelp='<label class="trigger">';
$labelc='</label>';
$ulp='<ul class="toggle_container">';
$ulpp='<ul class="toggle_container" id="prod">';
$ulc='</ul>';

echo '<form action="#" method="post" name="multiple">';
echo '<input type="hidden" name="envoi" value="yes">';
echo '<div id="header"> <div style="float:left"> Creation commande fourniseur </div><div style="float:right;margin-right: 150px;;"><input id="button" type="submit" name="submit" value="Creer"> </div></div>';

echo '<div id="sousous"> <div style="float:left">Créer commande par fourniseur</div></div>';

echo '<div id="contprod">';
echo '<table style="margin-left:3%; width:100%;font-size: 14px;"><tr><th width="10%"> &nbsp; </th><th width="43%"> Fournisseur </th><th width="11%"> Quantité </th><th width="13%"> Ref fourn. </th><th width="5%"> &nbsp; </th></tr></table>';
echo '<ul>';

// recuperer liste des fournisseurs de la commande

$sqlidfour = "select DISTINCT(".MAIN_DB_PREFIX."product_fournisseur_price.fk_soc) as idfourniseur from ".MAIN_DB_PREFIX."commandedet, ".MAIN_DB_PREFIX."product_fournisseur_price where ".MAIN_DB_PREFIX."commandedet.fk_product = ".MAIN_DB_PREFIX."product_fournisseur_price.fk_product and ".MAIN_DB_PREFIX."commandedet.fk_product is not null and ".MAIN_DB_PREFIX."commandedet.fk_commande = '$idvar' order by idfourniseur";
$reqsqlidfour = $mysqli->query($sqlidfour) or die('Erreur SQL !<br>'.$sqlidfoure.'<br>'.mysql_error()); 
while($datafour = $reqsqlidfour->fetch_assoc()) {

	$idfourn = $datafour['idfourniseur'];
	$namef = "select nom from ".MAIN_DB_PREFIX."societe where rowid = '$idfourn'";
	$reqnamef = $mysqli->query($namef) or die('Erreur SQL !<br>'.$namefe.'<br>'.mysql_error());
	
	while($datareqnamef = $reqnamef->fetch_assoc()) {

		
		// Verifi si la commande à déjà été créé
		$sqkckcmde = "select dcoc.status as status, dcoc.id_cmdefournisseur , dcf.ref as ref from ".MAIN_DB_PREFIX."cmdeclient_over_cmdefournisseur as dcoc, ".MAIN_DB_PREFIX."commande_fournisseur as dcf where dcoc.id_cmdeclient = '$idvar' and id_fournisseur = '$idfourn' and dcf.rowid = dcoc.id_cmdefournisseur";
		$reqsqkckcmde = $mysqli->query($sqkckcmde) or die('Erreur SQL !<br>'.$sqkckcmdee.'<br>'.mysql_error());
		while($datackcmde = $reqsqkckcmde->fetch_assoc()) {
			$statuscmde = $datackcmde['status'];
			if ($statuscmde === '1'){
				echo '<a style="color: #FF0000;font-size: 12px;"> Attention cette commande à déjà été généré ( N° '.$datackcmde['ref'].' )</a>';
			} 
		}
		echo '<li id="catmere" > <input type="checkbox" name="idproduit[]" value="'.$idfourn.'"><label class="trigger">'.$datareqnamef['nom'].$labelc;
	}
	echo $ulpp;

	//recuperer les articles de la commande du fournisseur
	$sqlprodf = "select ".MAIN_DB_PREFIX."commandedet.fk_product as idprod , ".MAIN_DB_PREFIX."commandedet.buy_price_ht as prix_achat , ".MAIN_DB_PREFIX."commandedet.qty as qty , ".MAIN_DB_PREFIX."product_fournisseur_price.ref_fourn as reffourn, ".MAIN_DB_PREFIX."product.label as label from ".MAIN_DB_PREFIX."commandedet, ".MAIN_DB_PREFIX."product_fournisseur_price, ".MAIN_DB_PREFIX."product where ".MAIN_DB_PREFIX."commandedet.fk_product = ".MAIN_DB_PREFIX."product_fournisseur_price.fk_product and ".MAIN_DB_PREFIX."commandedet.fk_product = ".MAIN_DB_PREFIX."product.rowid and ".MAIN_DB_PREFIX."commandedet.fk_product is not null and ".MAIN_DB_PREFIX."commandedet.fk_commande = '$idvar' and fk_soc = '$idfourn'"; 
	$reqsqlprodf = $mysqli->query($sqlprodf) or die('Erreur SQL !<br>'.$sqlprodfe.'<br>'.mysql_error()); 
	echo '<table width="100%">';
	while($dataprodf = $reqsqlprodf->fetch_assoc()) {
		echo '<tr>';
		echo '<td width="60%"><input type="text" id="qtyprod" name="idprod" style="text-align:left;" value="'.$dataprodf['label'].'" checked ></td>';
		//echo '<td width="15%"><input type="text" id="qtyprod" name="prix_achat'.$dataprodf['prix_achat'].'" size="3" value="'.$dataprodf['prix_achat'].'" readonly></td>';
		echo '<td width="15%"><input type="text" id="qtyprod" name="qty'.$dataprodf['qty'].'" size="3" value="'.$dataprodf['qty'].'" readonly></td>';
		echo '<td width="15%"><input type="text" id="qtyprod" name="reffourn'.$dataprodf['reffourn'].'" size="10" value="'.$dataprodf['reffourn'].'" readonly></td>';
	    	echo '<td><input type="hidden" name="Hideproduit'.$dataprodf['idprod'].' value="'.$dataprodf['label'].'"></td>';
		echo '</tr>';
	}
	echo '</table>';
	echo $ulc;
	echo '</li><br>';
}
echo $ulc;
echo '<div style="font-size:12px;margin:0px 0px 0px 170px;width=500px;background-color:#f07b6e;border-radius: 10px;padding: 12px;width:70%">';
echo '<img src="../../theme/azely/img/info.png" border="0" alt="" title=""> <span> Attention !!! </span><br>';
echo '<span>Ce formulaire permet de généré les commandes fournisseurs.<br>';
echo 'Si vous re-généré les commandes fournisseurs, les existantes seront surpprimés.<br>';
echo 'La gestion des stocks n\'est pour l\'instant pas prise en compte.<br>';
echo 'Les produits non présent dans la base ne pourront pas être dispatchés vers un fournisseur, ils ne seront donc pas traités.<br> ';
echo '</div><br>';
echo '</div>';
echo '</div>';
echo '</form>';

/// fin traitement de l'affichage ----

?>

<?php

/// debut traitement insertion dans la base

if (isset($_POST) && isset($_POST['idproduit'])){

	foreach($_POST['idproduit'] as $name1 => $value_checkbox){
		$cmdetotal_ht = '0';
		$cmdetotal_ttc = '0';
		$cmdetotal_tva = '0';
		// Creer les commandes fournisseurs selectionnées

		// D'abord on recupere la valeur max

		$yymm = date("ym");
        	$sqlref = "SELECT MAX(CAST(SUBSTRING(ref FROM 8) AS SIGNED)) as max FROM ".MAIN_DB_PREFIX."commande_fournisseur WHERE ref like 'CF".$yymm."%' AND entity = '1'";
        	$resqlref = $mysqli->query($sqlref);
		while($dataresqlref = $resqlref->fetch_assoc()) {
			$max = $dataresqlref['max'];
		}
		
		if ($max) {
			$max++;
			$nbcaractcat = strlen($max);
			if(($nbcaractcat == '1' )) {
				$max = '000'.$max;
			} elseif(($nbcaractcat == '2' )) {
				$max = '00'.$max;
			} elseif(($nbcaractcat == '2' )) {
				$max = '0'.$max;
			}
		} else {
			$max = '0001';
		}
		$idcmdef = 'CF'.$yymm."-".$max;

 	        // Verifi si la commande à déjà été créé
		$sqkckcmde = "select dcoc.rowid as rowid, dcoc.status as status, id_cmdefournisseur, dcf.ref as ref from ".MAIN_DB_PREFIX."cmdeclient_over_cmdefournisseur as dcoc, ".MAIN_DB_PREFIX."commande_fournisseur as dcf where id_cmdeclient = '$idvar' and id_fournisseur = '$value_checkbox'";
		$reqsqkckcmde = $mysqli->query($sqkckcmde) or die('Erreur SQL !<br>'.$sqkckcmdee.'<br>'.mysql_error());
		while($datackcmde = $reqsqkckcmde->fetch_assoc()) {
			$cocrowid = $datackcmde['rowid'];
			$cfrowid = $datackcmde['id_cmdefournisseur'];
			$confirmdel = 'yes';
		}

		if(($confirmdel = 'yes')){
			$delcmdf = "delete from ".MAIN_DB_PREFIX."cmdeclient_over_cmdefournisseur where rowid = '$cocrowid'";
			$reqdelcmdf = $mysqli->query($delcmdf) or die('Erreur SQL !<br>'.$delcmdf.'<br>'.mysql_error());
			$delcmdfdet = "delete from ".MAIN_DB_PREFIX."commande_fournisseurdet where fk_commande = '$cfrowid'";
			$reqdelcmdf = $mysqli->query($delcmdfdet) or die('Erreur SQL !<br>'.$delcmdfdet.'<br>'.mysql_error());

			$delcmdf = "delete from ".MAIN_DB_PREFIX."commande_fournisseur where rowid = '$cfrowid'";
			$reqdelcmdf = $mysqli->query($delcmdf) or die('Erreur SQL !<br>'.$delcmdf.'<br>'.mysql_error());
			
			$delcmdfcf = "delete from ".MAIN_DB_PREFIX."commande_fournisseur_customfields where fk_commande_fournisseur = '$cfrowid'";
			$reqdelcmdfcf = $mysqli->query($delcmdfcf) or die('Erreur SQL !<br>'.$delcmdfcf.'<br>'.mysql_error());
		}
		
		// Create commande fournisseur
		$createcmdf = "insert into ".MAIN_DB_PREFIX."commande_fournisseur (ref, entity, fk_soc, fk_user_author, model_pdf, fk_statut) values ('$idcmdef', '1', '$value_checkbox', '$iduser', 'muscadet', '4')";
		$reqcreatecmdf = $mysqli->query($createcmdf) or die('Erreur SQL !<br>'.$createcmdfe.'<br>'.mysql_error());

		$ckidcmdf = "select rowid from ".MAIN_DB_PREFIX."commande_fournisseur where ref = '$idcmdef'";
		$reqckidcmdf = $mysqli->query($ckidcmdf) or die('Erreur SQL !<br>'.$ckidcmdfe.'<br>'.mysql_error());
		while($datackidcmdf = $reqckidcmdf->fetch_assoc()) {
			$idcmdf = $datackidcmdf['rowid'];
		}
		
		$ckdesfourn = "select descfourn from ".MAIN_DB_PREFIX."commande_customfields where fk_commande = '$idcmdf'";
		$reqckdesfourn = $mysqli->query($ckdesfourn) or die('Erreur SQL !<br>'.$ckdesfourn.'<br>'.mysql_error());
		while($datadesfourn = $reqckdesfourn->fetch_assoc()) {
			$descfourn = $datadesfourn['descfourn'];
		}

		// Marque facture est creer
		$createref = "insert into ".MAIN_DB_PREFIX."cmdeclient_over_cmdefournisseur (id_cmdeclient, id_fournisseur, id_cmdefournisseur, status) values ('$idvar', '$value_checkbox', '$idcmdf', '1')";
		$reqcreateref = $mysqli->query($createref) or die('Erreur SQL !<br>'.$createrefe.'<br>'.mysql_error());

		// insertcustomfields commande client
		$createcmdf = "insert into ".MAIN_DB_PREFIX."commande_fournisseur_customfields (fk_commande_fournisseur, daterecep, refcc, refclient, descfourn) values ('$idcmdf', '$daterecep', '$refcc', '$refclient', '$descfourn')";
		$reqcreatecmdf = $mysqli->query($createcmdf) or die('Erreur SQL !<br>'.$createcmdfe.'<br>'.mysql_error());


		// recuperation de la liste des produits à commander pour ce fournisseur
		// fk_commande, fk_product, ref, label, tva_tx, qty, subprice, total_ht, total_tva, total_ttc, product_type, 
		// idcommande     id prod   ref_four                 prix_achat 
		$sqlprodcmde = "select ".MAIN_DB_PREFIX."commandedet.fk_product as idprod , ".MAIN_DB_PREFIX."commandedet.buy_price_ht as prix_achat , ".MAIN_DB_PREFIX."commandedet.qty as qty , ".MAIN_DB_PREFIX."commandedet.product_type as product_type, ".MAIN_DB_PREFIX."product_fournisseur_price.ref_fourn as reffourn, ".MAIN_DB_PREFIX."product.label as label, ".MAIN_DB_PREFIX."product_fournisseur_price.tva_tx as tva from ".MAIN_DB_PREFIX."commandedet, ".MAIN_DB_PREFIX."product_fournisseur_price, ".MAIN_DB_PREFIX."product where ".MAIN_DB_PREFIX."commandedet.fk_product = ".MAIN_DB_PREFIX."product_fournisseur_price.fk_product and ".MAIN_DB_PREFIX."commandedet.fk_product = ".MAIN_DB_PREFIX."product.rowid and ".MAIN_DB_PREFIX."commandedet.fk_product is not null and ".MAIN_DB_PREFIX."commandedet.fk_commande = '$idvar' and fk_soc = '$value_checkbox'";
		$reqsqlprodcmde = $mysqli->query($sqlprodcmde) or die('Erreur SQL !<br>'.$sqlprodcmdee.'<br>'.mysql_error());

		while($dataprodcmde = $reqsqlprodcmde->fetch_assoc()) {
			
			$fk_product = $dataprodcmde['idprod'];
			$ref = $dataprodcmde['reffourn'];
			$label = $dataprodcmde['label'];
			$label = str_replace("'", "\'", "$label"); 
			$tvatx = $dataprodcmde['tva'];
			$qty = $dataprodcmde['qty'];
			$subprice = $dataprodcmde['prix_achat'];
			$total_ht = $subprice*$qty;
			$total_ttc = ($subprice*$qty)*($tvatx/100)+($subprice*$qty);
			$total_tva = $total_ttc-$total_ht;
			$product_type = $dataprodcmde['product_type'];
			//echo $idcmdf.', '.$fk_product.', '.$ref.', '.$label.', '.$tvatx.', '.$qty.', '.$subprice.', '.$total_ht.', '.$total_tva.', '.$total_ttc.', '.$product_type.'<br>';
			$sqlprodcmde = "insert into ".MAIN_DB_PREFIX."commande_fournisseurdet (fk_commande, fk_product, ref, label, tva_tx, qty, subprice, total_ht, total_tva, total_ttc, product_type) values ('$idcmdf', '$fk_product', '$ref', '$label', '$tvatx', '$qty', '$subprice', '$total_ht', '$total_tva', '$total_ttc', '$product_type')";
			//echo $sqlprodcmde;
			$mysqli->query($sqlprodcmde) or die('Erreur SQL !<br>'.$sqlprodcmdee.'<br>'.mysql_error());
			$cmdetotal_ht = $cmdetotal_ht+$total_ht;
			$cmdetotal_ttc = $cmdetotal_ttc+$total_ttc;
			$cmdetotal_tva = $cmdetotal_tva+$total_tva;
		}
	$sqpupdatecmdf = "update ".MAIN_DB_PREFIX."commande_fournisseur set total_ht = '$cmdetotal_ht', total_ttc = '$cmdetotal_ttc ', tva = '$cmdetotal_tva' where rowid = '$idcmdf'";
	$mysqli->query($sqpupdatecmdf) or die('Erreur SQL !<br>'.$sqpupdatecmdfe.'<br>'.mysql_error());
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
