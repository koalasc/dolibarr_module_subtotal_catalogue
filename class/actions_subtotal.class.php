<?php
class ActionsSubtotal
{
	/** Overloading the doActions function : replacing the parent's function with the one below
	 * @param      $parameters  array           meta datas of the hook (context, etc...)
	 * @param      $object      CommonObject    the object you want to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param      $action      string          current action (if set). Generally create or edit or null
	 * @param      $hookmanager HookManager     current hook manager
	 * @return     void
	 */
    
    var $module_number = 104777;
    
    function formObjectOptions($parameters, &$object, &$action, $hookmanager) 
    {  
      	global $langs,$db,$user, $conf;
		
		$langs->load('subtotal@subtotal');
		
		$contexts = explode(':',$parameters['context']);
		
		if(in_array('ordercard',$contexts) || in_array('propalcard',$contexts) || in_array('invoicecard',$contexts)) {
        		
        	if ($object->statut == 0  && $user->rights->{$object->element}->creer) {
			
			
				if($object->element=='facture')$idvar = 'facid';
				else $idvar='id';
				
				
				if($action=='add_title_line' || $action=='add_total_line' || $action=='add_subtitle_line' || $action=='add_subtotal_line') {
					
					$level = GETPOST('level', 'int'); //New avec SUBTOTAL_USE_NEW_FORMAT
					
					if($action=='add_title_line') {
						$title = GETPOST('title');
						if(empty($title)) $title = $langs->trans('title');
						$qty = $level ? $level : 1;
					}
					else if($action=='add_subtitle_line') {
						$title = GETPOST('title');
						if(empty($title)) $title = $langs->trans('subtitle');
						$qty = 2;
					}
					else if($action=='add_subtotal_line') {
						$title = $langs->trans('SubSubTotal');
						$qty = 98;
					}
					else {
						$title = $langs->trans('SubTotal');
						$qty = $level ? 100-$level : 99;
					}
					dol_include_once('/subtotal/class/subtotal.class.php');
	    			TSubtotal::addSubTotalLine($object, $title, $qty);
				}
				else if($action==='ask_deleteallline') {
						$form=new Form($db);
						
						$lineid = GETPOST('lineid','integer');
						$TIdForGroup = $this->getArrayOfLineForAGroup($object, $lineid);
					
						$nbLines = count($TIdForGroup);
					
						$formconfirm=$form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&lineid='.$lineid, $langs->trans('deleteWithAllLines'), $langs->trans('ConfirmDeleteAllThisLines',$nbLines), 'confirm_delete_all_lines','',0,1);
						print $formconfirm;
				}

				// New format is for 3.8
				if ($conf->global->SUBTOTAL_USE_NEW_FORMAT) 
				{
					$this->printNewFormat($object, $conf, $langs, $idvar);
				}
				else 
				{
					$this->printOldFormat($object, $conf, $langs, $idvar);
				}
				
			}
		//////// Modif : Ajoute bouton dispatch commande fournisseur
		if ($object->statut == 1 && $object->element == 'commande' ) { ?>
			<script type="text/javascript">
				$(document).ready(function() {
					$('div.fiche div.tabsAction').append('<div class="inline-block divButAction"><a style="background-color:#5591A4;color:#f781f3;" id="createcmdefourn" href="javascript:;" class="butAction"><?php echo  $langs->trans('createcmdefourn' )?></a></div>');
				$('#createcmdefourn').click(function() {
					var win = window.open ("/dolibarr/subtotal/class/create_commande_fourniseur.php?idvar=<?php echo $object->id ?>&iduser=<?php echo $user->id ?>", "Ajout Multiple", config='height=700, width=1200, top=100, left=200, toolbar=no, menubar=no, scrollbars=1, resizable=no, location=no, directories=no, status=no');
					var timer = setInterval(function() {   
   					 if(win.closed) {  
      						  clearInterval(timer);  
     					 	  document.location.reload(true)  
   					  }  
					 }, 1000); 
				}); 
				});
		 	</script>
		<?php } 
		//////// FIN Modif
		//////// Modif : Ajoute bouton retour commande après reception
		if ($object->statut == 1 && $object->element == 'commande' ) { 

		global $langs;
		global $db;
		global $object;
		dol_include_once('/comm/propal/class/propal.class.php');
		dol_include_once('/commande/class/commande.class.php');
		dol_include_once('/compta/facture/class/facture.class.php');
		dol_include_once('/customfields/lib/customfields_aux.lib.php');
		$customfields = customfields_fill_object($object, null, $langs);
		//print_r($object);
		$ligneretourreception .= '<table width="100%">';
		$ligneretourreception .= '<th width="15%">Reférence</th><th width="60%">Produit/Services</th><th width="25%">Quantité commandé</th><th width="25%">Quantité retour</th>';
		foreach($object->lines as $l) {
		
			if(($l->fk_product_type == '1' || $l->fk_product_type == '0')) {
				$ligneretourreception .= '<tr><td style="text-align:center">'.$l->ref.'</td>';
				$ligneretourreception .= '<td>'.$l->libelle.'</td>';
				$ligneretourreception .= '<td style="text-align:center">'.$l->qty.'</td>';
				$ligneretourreception .= '<td><input id="qtyr_'.$l->rowid.'" value="" style="text-align:center"/></td>';
				$ligneretourreception .= '<tr>';
				$ligneretourreceptionpost .= $l->rowid.':';
				$ligneretourreceptionc .= ',qtyr_'.$l->rowid.': $(this).find("#qtyr_'.$l->rowid.'").val()';
			}
	
		}
		$ligneretourreception .= '</table>';
		$ligneretourreception = str_replace("'", "\'", "$ligneretourreception");
		//echo $ligneretourreceptionpost;

			?><script type="text/javascript">
				$(document).ready(function() {
				$('div.fiche div.tabsAction').append('<div class="inline-block divButAction"><a style="background-color:#E7FFC9;color:#000;" id="retourreception" href="javascript:;" class="butAction">Retour reception</a></div>');

					$('#retourreception').click(function() {
						promptretourreception(
						'<?php echo dol_buildpath($url, 1); ?>'
						,'<?php echo dol_buildpath('/subtotal/class/interface_retourreception.php', 2); ?>'
					 	);
					});
	
					function promptretourreception(url_to, url_ajax) {
				    		
				    		$( "#dialog-prompt-retourreception" ).remove();
				    		$('body').append('<div id="dialog-prompt-retourreception"> <?php echo $ligneretourreception; ?></div>');
				    	
				    		$( "#dialog-prompt-retourreception" ).dialog({
                    					resizable: false,
							width:1200,
                        				height:500,
                        				modal: true,
                        				title: "Retour après réception ",
                        				buttons: {
                            				   	"Ok": function() {
                                				$.ajax({
                                				     	url: url_ajax
                                						,data: {
                                							fk_object: <?php echo (int) $object->id; ?>
                                							,element: "<?php echo $object->element; ?>"
											,listpost: "<?php echo $ligneretourreceptionpost; ?>"
											<?php echo $ligneretourreceptionc; ?>
                                							
                                						}
                                					}).then(function (data) {
                                						document.location.href='?id=<?php echo $object->id; ?>';
                                					});
									console.log('ok');
                                					$( this ).dialog( "close" );
                            					},
                            					"<?php echo $langs->trans('Cancel') ?>": function() {
                                					$( this ).dialog( "close" );
                            					}
                        				}
                    				}).keypress(function(e) {
                    					if (e.keyCode == $.ui.keyCode.ENTER) {
					          		$('.ui-dialog').find('button:contains("Ok")').trigger('click');
					    	}
                    			});
					}
				});
		 	</script>
		<?php } 
		//////// FIN Modif
		}
		
		return 0;
	}
     
	function printNewFormat(&$object, &$conf, &$langs, $idvar)
	{

		global $langs;
		global $db;
		global $object;
		dol_include_once('/comm/propal/class/propal.class.php');
		dol_include_once('/commande/class/commande.class.php');
		dol_include_once('/compta/facture/class/facture.class.php');
		dol_include_once('/customfields/lib/customfields_aux.lib.php');
		$customfields = customfields_fill_object($object, null, $langs);
		$lignedetailservice .= '<span style="color:blue">Format Date => YYYY-MM-DD HH:MM:SS</span><br><br>';
		$lignedetailservice .= '<table width="100%">';
		$lignedetailservice .= '<th>Nombre</th><th>Description</th><th>Date de début</th><th>Date de fin</th>';
		$ligneremiseredevance .= '<table width="100%">';
		$ligneremiseredevance .= '<th width="40%">Catégorie</th><th width="30%">Remise (%)</th><th width="30%">Redevance (%)</th></th>';
		$vowels = array("TOTAL", "SOUS TOTAL", "SOUS", "-"," ", "&");
		$vowelss = array("TOTAL", "SOUS TOTAL", "SOUS", "-");
		//print_r($object);
		foreach($object->lines as $l) {
		
			if(($l->fk_product_type == '1')) {
				$desc = $l->libelle;
				$qty = $l->qty;
				$date_end = $l->date_end;
				$date_start = $l->date_start;
				$lignedetailservice .= '<tr><td align="center">'.$qty.'</td>';
				$lignedetailservice .= '<td>'.$desc.'</td>';
				//$lignedetailservice .= '<td>'.$l->rowid.'</td>';
				$lignedetailservice .= '<td><input id="date_start_'.$l->rowid.'" value="'.$date_start.'" /></td>';
				$lignedetailservice .= '<td><input id="date_end_'.$l->rowid.'" value="'.$date_end.'" /></td>';
				$lignedetailservice .= '<tr>';
				$lignedetailservicescpost .= $l->rowid.':';
				$lignedetailservicesc .= ',date_start_'.$l->rowid.': $(this).find("#date_start_'.$l->rowid.'").val()';
				$lignedetailservicesc .= ',date_end_'.$l->rowid.': $(this).find("#date_end_'.$l->rowid.'").val()';		

			}
			if(($l->product_type == '9' && $l->qty == '99')) {

				$catego = str_replace($vowels,"",strtoupper($l->label));
				$ligneremiseredevance .= '<tr><td>'.str_replace($vowelss,"",strtoupper($l->label)).'</td>';
				$ligneremiseredevance .= '';
				$ligneremiseredevance .= '<td><input id="remise'.$catego.'" value="" /></td>';
				$ligneremiseredevance .= '<td><input id="redevance'.$catego.'" value="" /></td>';
				$ligneremiseredevancepost .= $catego.':';
				//echo ',remise'.$catego.': $(this).find("remise'.$catego.'").val()';
				$ligneremiseredevancesc .= ',remise'.$catego.': $(this).find("#remise'.$catego.'").val()';
				$ligneremiseredevancesc .= ',redevance'.$catego.': $(this).find("#redevance'.$catego.'").val()';
				$ligneremiseredevance .= '<tr>';
			}		
		}
		$ligneremiseredevance .= '</table>';
		$lignedetailservice .= '</table>';
		$ligneremiseredevance = str_replace("'", "\'", "$ligneremiseredevance");
		$lignedetailservice = str_replace("'", "\'", "$lignedetailservice");

		?>
		 	<script type="text/javascript">
				$(document).ready(function() {
					$('div.fiche div.tabsAction').append('<br />');
					
					var label = "<label for='subtotal_line_level'></label>";
					var select = "<select name='subtotal_line_level' style='padding: 0px 8px ! important;'>";
					for (var i=1;i<10;i++)
					{
						select += "<option value="+i+"><?php echo $langs->trans('Level'); ?> "+i+"</option>";
					}
					select += "</select>";
				
		<?php	if ( $object->element != 'facture' ) { ?>

					$('div.fiche div.tabsAction').append('<div class="inline-block divButAction">'+label+select+'</div>');
					$('div.fiche div.tabsAction').append('<div class="inline-block divButAction"><a id="add_title_line" rel="add_title_line" href="javascript:;" class="butAction"><?php echo  $langs->trans('AddTitle' )?></a></div>');
					$('div.fiche div.tabsAction').append('<div class="inline-block divButAction"><a id="add_total_line" rel="add_total_line" href="javascript:;" class="butAction"><?php echo  $langs->trans('AddSubTotal')?></a></div>');
		
					//////// Modif : Ajoute bouton pour ajout d'articles groupe + Detaille service / Remise Redevance
					$('div.fiche div.tabsAction').append('<div class="inline-block divButAction"><a style="background-color:#5591A4;color:#f781f3;font-weight:bold" id="add_multiple_prod" href="javascript:;" class="butAction">Catalogue Produits</a></div>');

					$('#add_multiple_prod').click(function() {
						var win = window.open ("/dolibarr/subtotal/class/article_multiple.php?idvar=<?php echo $object->id ?>&type=<?php echo $object->element ?>", "Ajout Multiple", config='height=700, width=1200, top=100, left=200, toolbar=no, menubar=no, scrollbars=1, resizable=no, location=no, directories=no, status=no');
						var timer = setInterval(function() {   
   						 if(win.closed) {  
      							  clearInterval(timer);  
     						 	  document.location.reload(true) ;
   						  }  
						 }, 1000); 
													
					});

					$('div.fiche div.tabsAction').append('<div class="inline-block divButAction"><a style="background-color:#E7FFC9;color:#000;" id="detailservice" href="javascript:;" class="butAction">Détailler les Services</a></div>');

					$('#detailservice').click(function() {
						promptdetailservice(
						'<?php echo dol_buildpath($url, 1); ?>'
						,'<?php echo dol_buildpath('/subtotal/class/interface_detailservices.php', 2); ?>'
					 	);
					});
	
					function promptdetailservice(url_to, url_ajax) {
				    		
				    		$( "#dialog-prompt-detailservice" ).remove();
				    		$('body').append('<div id="dialog-prompt-detailservice"> <?php echo $lignedetailservice; ?></div>');

				    		$( "#dialog-prompt-detailservice" ).dialog({
                    					resizable: false,
							width:900,
                        				height:300,
                        				modal: true,
                        				title: "Detail des Services ",
                        				buttons: {
                            				   	"Ok": function() {
                                				$.ajax({
                                				     	url: url_ajax
                                						,data: {
                                							fk_object: <?php echo (int) $object->id; ?>
                                							,element: "<?php echo $object->element; ?>"
											,listpost: "<?php echo $lignedetailservicescpost; ?>"
											<?php echo $lignedetailservicesc; ?>
                                							
                                						}
                                					}).then(function (data) {
                                						document.location.href='?<?php echo $idvar ?>=<?php echo $object->id; ?>';
                                					});

                                					$( this ).dialog( "close" );
                            					},
                            					"<?php echo $langs->trans('Cancel') ?>": function() {
                                					$( this ).dialog( "close" );
                            					}
                        				}
                    				}).keypress(function(e) {
                    					if (e.keyCode == $.ui.keyCode.ENTER) {
					          		$('.ui-dialog').find('button:contains("Ok")').trigger('click');
					    	}
                    			});
					}

					$('div.fiche div.tabsAction').append('<div class="inline-block divButAction"><a style="background-color:#FFD000;color:#000;" id="remiseredevance" href="javascript:;" class="butAction">Remise / Redevance</a></div>');

					$('#remiseredevance').click(function() {
						promptremiseredevance(
						'<?php echo dol_buildpath($url, 1); ?>'
						,'<?php echo dol_buildpath('/subtotal/class/interface_remiseredevance.php', 2); ?>'
					    	);
					});

					function promptremiseredevance(url_to, url_ajax) {
				    		
				    		$( "#dialog-prompt-remiseredevance" ).remove();
				    		$('body').append('<div id="dialog-prompt-remiseredevance"> <?php echo $ligneremiseredevance; ?></div>');
                    		    		
				    		$( "#dialog-prompt-remiseredevance" ).dialog({
                    					resizable: false,
							width:900,
                        				height:300,
                        				modal: true,
                        				title: "Ajouter des remises ou des redevances ",
                        				buttons: {
                            				   	"Ok": function() {
                                				$.ajax({
                                				     	url: url_ajax
                                						,data: {
                                							fk_object: <?php echo (int) $object->id; ?>
                                							,element: "<?php echo $object->element; ?>"
											,listpost: "<?php echo $ligneremiseredevancepost; ?>"
											<?php echo $ligneremiseredevancesc; ?>
                                							
                                						}
                                					}).then(function (data) {
                                						document.location.href='?<?php echo $idvar ?>=<?php echo $object->id; ?>';
                                					});

                                					$( this ).dialog( "close" );
                            					},
                            					"<?php echo $langs->trans('Cancel') ?>": function() {
                                					$( this ).dialog( "close" );
                            					}
                        				}
                    				}).keypress(function(e) {
                    					if (e.keyCode == $.ui.keyCode.ENTER) {
					          		$('.ui-dialog').find('button:contains("Ok")').trigger('click');
					    	}
                    			});
					}


		<?php } else {?>
					$('div.fiche div.tabsAction').append('<div class="inline-block divButAction"><a style="background-color:#5591A4;color:#fff;" id="add_multiple_prod" href="javascript:;" class="butAction">Réorganisation facture via le catalogue</a></div>');
		
					$('#add_multiple_prod').click(function() {
						var win = window.open ("/dolibarr/subtotal/class/article_multiple.php?idvar=<?php echo $object->id ?>&type=<?php echo $object->element ?>&disable=yes", "Ajout Multiple", config='height=700, width=1200, top=100, left=200, toolbar=no, menubar=no, scrollbars=1, resizable=no, location=no, directories=no, status=no');
						var timer = setInterval(function() {   
   						 if(win.closed) {  
      							  clearInterval(timer);  
     						 	  document.location.reload(true) ;
   						  }  
						 }, 1000); 
													
					});

		<?php } ?>
					//////// FIN Modif
					function promptSubTotal(titleDialog, label, url_to, url_ajax) {
					     $( "#dialog-prompt-subtotal" ).remove();
					     $('body').append('<div id="dialog-prompt-subtotal"><input id="sub-total-title" size=30 value="'+label+'" /></div>');
					    
					     $( "#dialog-prompt-subtotal" ).dialog({
	                        resizable: false,
	                        height:140,
	                        modal: true,
	                        title: titleDialog,
	                        buttons: {
	                            "Ok": function() {
	                                $.get(url_ajax+'&title='+encodeURIComponent( $(this).find('#sub-total-title').val() ), function() {
	                                    document.location.href=url_to;
	                                })
	
                                    $( this ).dialog( "close" );
	                            },
	                            "<?php echo $langs->trans('Cancel') ?>": function() {
	                                $( this ).dialog( "close" );
	                            }
	                        }
	                     });
					}
					
					$('a[rel=add_title_line]').click(function() 
					{
						promptSubTotal("<?php echo $langs->trans('YourTitleLabel') ?>"
						     , "<?php echo $langs->trans('title'); ?>"
						     , '?<?php echo $idvar ?>=<?php echo $object->id; ?>'
						     , '?<?php echo $idvar ?>=<?php echo $object->id; ?>&action=add_title_line&level='+$('select[name=subtotal_line_level]').val()
						     
						);
					});
					
					$('a[rel=add_total_line]').click(function() {
						$.get('?<?php echo $idvar ?>=<?php echo $object->id ?>&action=add_total_line&level='+$('select[name=subtotal_line_level]').val(), function() {
							document.location.href='?<?php echo $idvar ?>=<?php echo $object->id; ?>';
						});
						
					});
				});
		 	</script>
		 <?php
	}
	 
	function printOldFormat(&$object, &$conf, &$langs, $idvar)
	{
		?>
			<script type="text/javascript">
				$(document).ready(function() {
					
					<?php
						if($conf->global->SUBTOTAL_MANAGE_SUBSUBTOTAL==1) {
							?>$('div.fiche div.tabsAction').append('<br /><br />');<?php
						}
					?>

					$('div.fiche div.tabsAction').append('<div class="inline-block divButAction"><a id="add_title_line" rel="add_title_line" href="javascript:;" class="butAction"><?php echo  $langs->trans('AddTitle' )?></a></div>');
					$('div.fiche div.tabsAction').append('<div class="inline-block divButAction"><a id="add_total_line" rel="add_total_line" href="javascript:;" class="butAction"><?php echo  $langs->trans('AddSubTotal')?></a></div>');
					//////// Modif : Ajoute bouton pour ajout d'articles groupe	
					$('div.fiche div.tabsAction').append('<div class="inline-block divButAction"><a style="background-color:#5591A4;color:#fff;" id="add_multiple_prod" href="javascript:;" class="butAction"><?php echo  $langs->trans('AddMultipleProd' )?></a></div>');

					$('#add_multiple_prod').click(function() {
						var win = window.open ("/dolibarr/subtotal/class/article_multiple.php?idvar=<?php echo $object->id ?>&type=<?php echo $object->element ?>", "Ajout Multiple", config='height=700, width=1200, top=100, left=200, toolbar=no, menubar=no, scrollbars=1, resizable=no, location=no, directories=no, status=no');
						var timer = setInterval(function() {   
   						 if(win.closed) {  
      							  clearInterval(timer);  
     						 	  document.location.reload(true) ;
   						  }  
						 }, 1000); 
													
					});
					//////// FIN Modif
			
					<?php
						if($conf->global->SUBTOTAL_MANAGE_SUBSUBTOTAL==1) {
						?>
							$('div.fiche div.tabsAction').append('<div class="inline-block divButAction"><a id="add_subtitle_line" rel="add_subtitle_line" href="javascript:;" class="butAction"><?php echo  $langs->trans('AddSubTitle' )?></a></div>');
							$('div.fiche div.tabsAction').append('<div class="inline-block divButAction"><a id="add_subtotal_line" rel="add_subtotal_line" href="javascript:;" class="butAction"><?php echo  $langs->trans('AddSubSubTotal')?></a></div>');
	
						<?php								
						}
					?>
					function promptSubTotal(titleDialog, label, url_to, url_ajax) {
					    
					     $( "#dialog-prompt-subtotal" ).remove();
					     $('body').append('<div id="dialog-prompt-subtotal"><input id="sub-total-title" size=30 value="'+label+'" /></div>');
					    
					     $( "#dialog-prompt-subtotal" ).dialog({
	                        resizable: false,
	                        height:140,
	                        modal: true,
	                        title: titleDialog,
	                        buttons: {
	                            "Ok": function() {
	                                
	                                $.get(url_ajax+'&title='+encodeURIComponent( $(this).find('#sub-total-title').val() ), function() {
	                                    document.location.href=url_to;
	                                })
	
	                                    $( this ).dialog( "close" );
	                                
	                            },
	                            "<?php echo $langs->trans('Cancel') ?>": function() {
	                                $( this ).dialog( "close" );
	                            }
	                        }
	                     });
	                     
					}
					
					$('a[rel=add_title_line]').click(function() {
						
						
						promptSubTotal("<?php echo $langs->trans('YourTitleLabel') ?>"
						     , "<?php echo $langs->trans('title') ?>"
						     , '?<?php echo $idvar ?>=<?php echo $object->id ?>'
						     , '?<?php echo $idvar ?>=<?php echo $object->id ?>&action=add_title_line'
						);
						
						
					});
					$('a[rel=add_subtitle_line]').click(function() {
					    
					    promptSubTotal(
					        "<?php echo $langs->trans('YourTitleLabel') ?>"
					        , "<?php echo $langs->trans('title') ?>"
					        , '?<?php echo $idvar ?>=<?php echo $object->id ?>'
	                        , '?<?php echo $idvar ?>=<?php echo $object->id ?>&action=add_subtitle_line'
					    );
					    
					});
					
					$('a[rel=add_total_line]').click(function() {
						
						$.get('?<?php echo $idvar ?>=<?php echo $object->id ?>&action=add_total_line', function() {
							document.location.href='?<?php echo $idvar ?>=<?php echo $object->id ?>';
						});
						
					});
					
					$('a[rel=add_subtotal_line]').click(function() {
						
						$.get('?<?php echo $idvar ?>=<?php echo $object->id ?>&action=add_subtotal_line', function() {
							document.location.href='?<?php echo $idvar ?>=<?php echo $object->id ?>';
						});
						
					});
					
					
				});
				
			</script>
			<?php
		return 0;
	}
	 
	function formBuilddocOptions($parameters) {
	/* Réponse besoin client */		
			
		global $conf, $langs, $bc, $var;
			
		$action = GETPOST('action');	
		//print_r($conf);
		if (
				in_array('invoicecard',explode(':',$parameters['context']))
				|| in_array('propalcard',explode(':',$parameters['context']))
				|| in_array('ordercard',explode(':',$parameters['context']))
			)
	        {
			if ($conf->global->SUBTOTAL_PDF_EXTRA == 1) {
				
				$hideInnerLines	= isset( $_SESSION['subtotal_hideInnerLines_'.$parameters['modulepart']] ) ?  $_SESSION['subtotal_hideInnerLines_'.$parameters['modulepart']] : 0;
				$hidedetails	= isset( $_SESSION['subtotal_hidedetails_'.$parameters['modulepart']] ) ?  $_SESSION['subtotal_hidedetails_'.$parameters['modulepart']] : 0;	
					
					
		     	$out.= '<tr '.$bc[$var].'>
		     			<td colspan="4" align="right">
		     				<label for="hideInnerLines">'.$langs->trans('HideInnerLines').'</label>
		     				<input type="checkbox" onclick="if($(this).is(\':checked\')) { $(\'#hidedetails\').attr(\'checked\', \'checked\')  }" id="hideInnerLines" name="hideInnerLines" value="1" '.(( $hideInnerLines ) ? 'checked="checked"' : '' ).' />
		     			</td>
		     			</tr>';
				$var = -$var;
				 
				 
				
				$out.= '<tr '.$bc[$var].'>
		     			<td colspan="4" align="right">
		     				<label for="hidedetails">'.$langs->trans('SubTotalhidedetails').'</label>
		     				<input type="checkbox" id="hidedetails" name="hidedetails" value="1" '.(( $hidedetails ) ? 'checked="checked"' : '' ).' />
		     			</td>
		     			</tr>';
				$var = -$var;
				 
				
				
				$this->resprints = $out;	
			}
		}
		
        return 1;
	} 
	 
    function formEditProductOptions($parameters, &$object, &$action, $hookmanager) 
    {
		
    	if (in_array('invoicecard',explode(':',$parameters['context'])))
        {
        	
        }
		
        return 0;
    }
	
	function ODTSubstitutionLine($parameters, &$object, $action, $hookmanager) {
		global $conf;
		
		if($action === 'builddoc') {
			
			$line = &$parameters['line'];
			$object = &$parameters['object'];
			$substitutionarray = &$parameters['substitutionarray'];
			
			if($line->product_type == 9 && $line->special_code == $this->module_number) {
				$substitutionarray['line_modsubtotal'] = true;	
				
				$substitutionarray['line_price_ht']
					 = $substitutionarray['line_price_vat'] 
					 = $substitutionarray['line_price_ttc']
					 = $substitutionarray['line_vatrate']
					 = $substitutionarray['line_qty'] 
					 = '';
				
				if($line->qty>90) {
					$substitutionarray['line_modsubtotal_total'] = true;
					
					$substitutionarray['line_price_ht'] = $substitutionarray['line_price_ttc'] = $this->getTotalLineFromObject($object, $line, $conf->global->SUBTOTAL_MANAGE_SUBSUBTOTAL);
				} else {
					$substitutionarray['line_modsubtotal_title'] = true;
				}
				
				
			}	
			else{
				$substitutionarray['line_not_modsubtotal'] = true;
				$substitutionarray['line_modsubtotal'] = false;
			}
			
		}
		
	}
	
	function createFrom($parameters, &$object, $action, $hookmanager) {
	
		if (
				in_array('invoicecard',explode(':',$parameters['context']))
				|| in_array('propalcard',explode(':',$parameters['context']))
				|| in_array('ordercard',explode(':',$parameters['context']))
		) {
			
			global $db;
			
			$objFrom = $parameters['objFrom'];
			
			foreach($objFrom->lines as $k=> &$lineOld) {
				
					if($lineOld->product_type == 9 && $lineOld->info_bits > 0 ) {
							
							$line = & $object->lines[$k];
				
							$idLine = (int) ($line->id ? $line->id : $line->rowid); 
				
							$db->query("UPDATE ".MAIN_DB_PREFIX.$line->table_element."
							SET info_bits=".(int)$lineOld->info_bits."
							WHERE rowid = ".$idLine."
							");
						
					}
				
				
			}
			
			
		}
		
	}
	
	function doActions($parameters, &$object, $action, $hookmanager) {
		global $conf;
		
		if($action === 'builddoc') {
			
			if (
				in_array('invoicecard',explode(':',$parameters['context']))
				|| in_array('propalcard',explode(':',$parameters['context']))
				|| in_array('ordercard',explode(':',$parameters['context']))
			)
	        {								
				if(in_array('invoicecard',explode(':',$parameters['context']))) {
					$sessname = 'subtotal_hideInnerLines_facture';	
					$sessname2 = 'subtotal_hidedetails_facture';
				}
				elseif(in_array('propalcard',explode(':',$parameters['context']))) {
					$sessname = 'subtotal_hideInnerLines_propal';
					$sessname2 = 'subtotal_hidedetails_propal';	
				}
				elseif(in_array('ordercard',explode(':',$parameters['context']))) {
					$sessname = 'subtotal_hideInnerLines_commande';
					$sessname2 = 'subtotal_hidedetails_commande';	
				}
				else {
					$sessname = 'subtotal_hideInnerLines_unknown';
					$sessname2 = 'subtotal_hidedetails_unknown';
				}
								
				$hideInnerLines = (int)isset($_REQUEST['hideInnerLines']);
				$_SESSION[$sessname] = $hideInnerLines;		
				
				$hidedetails= (int)isset($_REQUEST['hidedetails']);	
				$_SESSION[$sessname2] = $hidedetails;
				
	           	foreach($object->lines as &$line) {
					if ($line->product_type == 9 && $line->special_code == $this->module_number) {
					    
                        if($line->qty>=90) {
                            $line->modsubtotal_total = 1;
                        }
                        else{
                            $line->modsubtotal_title = 1;
                        }
                        
						$line->total_ht = $this->getTotalLineFromObject($object, $line, $conf->global->SUBTOTAL_MANAGE_SUBSUBTOTAL);
					}
	        	}
	        }
			
		}
		else if($action === 'confirm_delete_all_lines' && GETPOST('confirm')=='yes') {
			
			$Tab = $this->getArrayOfLineForAGroup($object, GETPOST('lineid'));
			
			foreach($Tab as $idLine) {
				/**
				 * @var $object Facture
				 */
				if($object->element=='facture') $object->deleteline($idLine);
				/**
				 * @var $object Propal
				 */
				else if($object->element=='propal') $object->deleteline($idLine);
				/**
				 * @var $object Commande
				 */
				else if($object->element=='commande') $object->deleteline($idLine);
			}
			
			header('location:?id='.$object->id);
			exit;
			
		}

		return 0;
	}
	
	function formAddObjectLine ($parameters, &$object, &$action, $hookmanager) {
		return 0;
	}

	function getArrayOfLineForAGroup(&$object, $lineid) {
		$rang = $line->rang;
		$qty_line = $line->qty;
		
		$total = 0;
		
		$found = false;

		$Tab= array();
		//print_r($object->lines);
		$inserttab='0';
		foreach($object->lines as $l) {

			if($l->special_code ==	'104777') {
				$inserttab = '0';
			}			

			if($l->rowid == $lineid || $inserttab == '1') {
				$Tab[] = $l->rowid;	
				$inserttab = '1';
			}
		}


/*		foreach($object->lines as $l) {
		
			if($l->rowid == $lineid) {
				$found = true;
				$qty_line = $l->qty;
			}
			
			if($found) {
				
				$Tab[] = $l->rowid;
				
				if($l->special_code==$this->module_number && (($l->qty==99 && $qty_line==1) || ($l->qty==98 && $qty_line==2))   ) {
					break; // end of story
				}
			}
			
			
		}*/
		
		
		return $Tab;
		
	}

	function getTotalLineFromObject(&$object, &$line, $use_level=false) {
		
		$rang = $line->rang;
		$qty_line = $line->qty;
		
		$total = 0;

		foreach($object->lines as $l) {
			//print $l->rang.'>='.$rang.' '.$total.'<br/>';
			if($l->rang>=$rang) {
				//echo 'return!<br>';
				return $total;
			} 
			else if($l->special_code==$this->module_number && $l->qty == 100 - $qty_line) 
		  	{
				$total = 0;
			}
			elseif($l->product_type!=9) {
				$total += $l->total_ht;
			}
			
		}
		
		return $total;
	}

	/**
	 * @param $pdf          TCPDF               PDF object
	 * @param $object       CommonObject        dolibarr object
	 * @param $line         CommonObjectLine    dolibarr object line
	 * @param $label        string
	 * @param $description  string
	 * @param $posx         float               horizontal position
	 * @param $posy         float               vertical position
	 * @param $w            float               width
	 * @param $h            float               height
	 */
	function pdf_add_total(&$pdf,&$object, &$line, $label, $description,$posx, $posy, $w, $h) {
		global $conf;
		
		$hideInnerLines = (int)isset($_REQUEST['hideInnerLines']);
		
		$hidePriceOnSubtotalLines = (int) isset($_REQUEST['hide_price_on_subtotal_lines']);
				
		if($line->qty==99)
			$pdf->SetFillColor(220,220,220);
		elseif ($line->qty==98)
			$pdf->SetFillColor(230,230,230);
		else
			$pdf->SetFillColor(240,240,240);
		
		$pdf->SetFont('', 'B', 9);

		$y1 = $pdf->GetY();
		//Print label 
		$pdf->writeHTMLCell($w, $h, $posx, $posy, $label, 0, 1, false, true, 'R',true);
		$y2 = $pdf->GetY();
		
		//Print background
		$pdf->SetXY($posx, $posy);
		$pdf->MultiCell(200-$posx, $y2-$y1-2, '', 0, '', 1);
		
		if (!$hidePriceOnSubtotalLines) {
			if($line->total == 0) {
				$total = $this->getTotalLineFromObject($object, $line, $conf->global->SUBTOTAL_MANAGE_SUBSUBTOTAL);
			
				$line->total_ht = $total;
				$line->total = $total;
			}
			
			$pdf->SetXY($pdf->postotalht, $posy);
			$pdf->MultiCell($pdf->page_largeur-$pdf->marge_droite-$pdf->postotalht, 3, price($line->total), 0, 'R', 0);
		}
	}

	/**
	 * @param $pdf          TCPDF               PDF object
	 * @param $object       CommonObject        dolibarr object
	 * @param $line         CommonObjectLine    dolibarr object line
	 * @param $label        string
	 * @param $description  string
	 * @param $posx         float               horizontal position
	 * @param $posy         float               vertical position
	 * @param $w            float               width
	 * @param $h            float               height
	 */
	function pdf_add_title(&$pdf,&$object, &$line, $label, $description,$posx, $posy, $w, $h) {
		
		global $db,$conf;
		
		$pdf->SetXY ($posx, $posy);
		
		$hideInnerLines = (int)isset($_REQUEST['hideInnerLines']);
		if($hideInnerLines) {

			if($line->qty==1)$pdf->SetFont('', 'BU', 9);
			else $pdf->SetFont('', $conf->global->SUBTOTAL_STYLE_TITRES_SI_LIGNES_CACHEES, 9);
			
		}
		else {

			if($line->qty==1)$pdf->SetFont('', 'BU', 9);
			else $pdf->SetFont('', 'BUI', 9);
			
		}
		
		$pdf->MultiCell($w, $h, $label, 0, 'L');
		
		if($description && !$hidedesc) {
			$posy = $pdf->GetY();
			
			$pdf->SetFont('', '', 8);
			
			$pdf->writeHTMLCell($w, $h, $posx, $posy, $description, 0, 1, false, true, 'J',true);

		}
	}

	function pdf_writelinedesc_ref($parameters=array(), &$object, &$action='') {
	// ultimate PDF hook O_o
		
		return $this->pdf_writelinedesc($parameters,$object,$action);
		
	}

	function isModSubtotalLine(&$parameters, &$object) {
		
		$i = & $parameters['i'];
		
		if($object->lines[$i]->special_code == $this->module_number && $object->lines[$i]->product_type == 9) {
			return true;
		}
		
		return false;
		
	}

	function pdf_getlineqty($parameters=array(), &$object, &$action='') {
		
		if($this->isModSubtotalLine($parameters,$object) ){
			
			$this->resprints = ' ';
			
			if((float)DOL_VERSION>=3.8) {
				return 1;
			}
			
		}
		
	}
	
	function pdf_getlinetotalexcltax($parameters=array(), &$object, &$action='') {
		
		if($this->isModSubtotalLine($parameters,$object) ){
			
			$this->resprints = ' ';
			
			if((float)DOL_VERSION>=3.8) {
				return 1;
			}
			
		}
	}
	
	function pdf_getlinetotalwithtax($parameters=array(), &$object, &$action='') {
		if($this->isModSubtotalLine($parameters,$object) ){
			
			$this->resprints = ' ';
		
			if((float)DOL_VERSION>=3.8) {
				return 1;
			}
		}
	}
	
	function pdf_getlineunit($parameters=array(), &$object, &$action='') {
		if($this->isModSubtotalLine($parameters,$object) ){
			$this->resprints = ' ';
		
			if((float)DOL_VERSION>=3.8) {
				return 1;
			}
		}
	}
	
	function pdf_getlineupexcltax($parameters=array(), &$object, &$action='') {
		if($this->isModSubtotalLine($parameters,$object) ){
			$this->resprints = ' ';
		
			if((float)DOL_VERSION>=3.8) {
				return 1;
			}
		}
	}
	
	function pdf_getlineupwithtax($parameters=array(), &$object, &$action='') {
		if($this->isModSubtotalLine($parameters,$object) ){
			$this->resprints = ' ';
			if((float)DOL_VERSION>=3.8) {
				return 1;
			}
		}
	}
	
	function pdf_getlinevatrate($parameters=array(), &$object, &$action='') {
		if($this->isModSubtotalLine($parameters,$object) ){
			$this->resprints = ' ';
			
			if((float)DOL_VERSION>=3.8) {
				return 1;
			}
		}
	}
		
	function pdf_getlineprogress($parameters=array(), &$object, &$action='') {
		if($this->isModSubtotalLine($parameters,$object) ){
			$this->resprints = ' ';
			if((float)DOL_VERSION>=3.8) {
				return 1;
			}
		}
	}

	function pdf_writelinedesc($parameters=array(), &$object, &$action='')
	{
		/**
		 * @var $pdf    TCPDF
		 */
		global $pdf,$conf;

		foreach($parameters as $key=>$value) {
			${$key} = $value;
		}
		
		$hideInnerLines = (int)isset($_REQUEST['hideInnerLines']);	
		$hidedetails = (int)isset($_REQUEST['hidedetails']);	
		
		if($this->isModSubtotalLine($parameters,$object) ){
		
			if ($hideInnerLines) { // si c une ligne de titre
		    	$fk_parent_line=0;
				$TLines =array();
			
				foreach($object->lines as $k=>&$line) 
				{
					if($line->product_type==9 && $line->rowid>0) 
					{
						$fk_parent_line = $line->rowid;
						
						if($line->qty>90 && $line->total==0) 
						{
							$total = $this->getTotalLineFromObject($object, $line, $conf->global->SUBTOTAL_MANAGE_SUBSUBTOTAL);
						
							$line->total_ht = $total;
							$line->total = $total;
						} 
						
					} 
				
					if ($hideInnerLines)
					{
						if($line->product_type==9 && $line->rowid>0) 
						{
							$TLines[] = $line; //Cas où je doit cacher les produits et afficher uniquement les sous-totaux avec les titres
						}
					}
					elseif ($hidedetails)
					{
						$TLines[] = $line; //Cas où je cache uniquement les prix des produits	
					}
					
					if ($line->product_type != 9) { // jusqu'au prochain titre ou total
						//$line->fk_parent_line = $fk_parent_line;
						
					}
				
					/*if($hideTotal) {
						$line->total = 0;
						$line->subprice= 0;
					}*/
				}
				
				$object->lines = $TLines;
				
				if($i>count($object->lines)) return 1;
		    }
		
	 
			
			
			
				$line = &$object->lines[$i];
				
				if($line->info_bits>0) { // PAGE BREAK
					$pdf->addPage();
					$posy = $pdf->GetY();
				}
				
				if($line->label=='') {
					$label = $outputlangs->convToOutputCharset($line->desc);
					$description='';
				}
				else {
					$label = $outputlangs->convToOutputCharset($line->label);
					$description=$outputlangs->convToOutputCharset(dol_htmlentitiesbr($line->desc));
				}
				
				if($line->qty>90) {
					
					if ($conf->global->SUBTOTAL_USE_NEW_FORMAT)	$label .= ' '.$this->getTitle($object, $line);
					
					$pageBefore = $pdf->getPage();
					$this->pdf_add_total($pdf,$object, $line, $label, $description,$posx, $posy, $w, $h);
					$pageAfter = $pdf->getPage();	
/*
					if($pageAfter>$pageBefore) {
						print "ST $pageAfter>$pageBefore<br>";
						$pdf->rollbackTransaction(true);	
						$pdf->addPage('','', true);
						$posy = $pdf->GetY();
						$this->pdf_add_total($pdf,$object, $line, $label, $description,$posx, $posy, $w, $h);
						$posy = $pdf->GetY();
						print 'add ST'.$pdf->getPage().'<br />';
					}
	*/				
					$posy = $pdf->GetY();
					
				}	
				else{
					$pageBefore = $pdf->getPage();

					$this->pdf_add_title($pdf,$object, $line, $label, $description,$posx, $posy, $w, $h); 
					$pageAfter = $pdf->getPage();	

					/*if($pageAfter>$pageBefore) {
						print "T $pageAfter>$pageBefore<br>";
						$pdf->rollbackTransaction(true);
						$pdf->addPage('','', true);
						print 'add T'.$pdf->getPage().' '.$line->rowid.' '.$pdf->GetY().' '.$posy.'<br />';
						
						$posy = $pdf->GetY();
						$this->pdf_add_title($pdf,$object, $line, $label, $description,$posx, $posy, $w, $h);
						$posy = $pdf->GetY();
					}
				*/
					$posy = $pdf->GetY();
				}
//	if($line->rowid==47) exit;
			
			return 1;
		}
		/* TODO je desactive parce que je comprends pas PH Style, mais à test
		else {
			
			if($hideInnerLines) {
				$pdf->rollbackTransaction(true);
			}
			else {
				$labelproductservice=pdf_getlinedesc($object, $i, $outputlangs, $hideref, $hidedesc, $issupplierline);
				$pdf->writeHTMLCell($w, $h, $posx, $posy, $outputlangs->convToOutputCharset($labelproductservice), 0, 1);
			}
			
		}*/


		
	}

	/**
	 * Permet de récupérer le titre lié au sous-total
	 * 
	 * @return string
	 */
	function getTitle(&$object, &$currentLine)
	{
		$res = '';
		
		foreach ($object->lines as $line)
		{
			if ($line->id == $currentLine->id) break;
			
			$qty_search = 100 - $currentLine->qty;
			
			if ($line->product_type == 9 && $line->special_code == $this->module_number && $line->qty == $qty_search) 
			{
				$res = ($line->label) ? $line->label : (($line->description) ? $line->description : $line->desc);
			}
		}
		
		return $res;
	}
	
	/**
	 * @param $parameters   array
	 * @param $object       CommonObject
	 * @param $action       string
	 * @param $hookmanager  HookManager
	 * @return int
	 */
	function printObjectLine ($parameters, &$object, &$action, $hookmanager){
		
		global $conf,$langs,$user;
		
		$num = &$parameters['num'];
		$line = &$parameters['line'];
		$i = &$parameters['i'];

		$contexts = explode(':',$parameters['context']);

		if($line->special_code!=$this->module_number || $line->product_type!=9) {
			null;
		}	
		else if (in_array('invoicecard',$contexts) || in_array('propalcard',$contexts) || in_array('ordercard',$contexts)) 
        {
        	
			if($object->element=='facture')$idvar = 'facid';
			else $idvar='id';
					
					if($action=='savelinetitle' && $_POST['lineid']===$line->id) {
						
						$description = ($line->qty>90) ? '' : GETPOST('linedescription');
						$pagebreak = (int)GETPOST('pagebreak');

						/**
						 * @var $object Facture
						 */
						if($object->element=='facture') $object->updateline($line->id,$description, 0,$line->qty,0,'','',0,0,0,'HT',$pagebreak,9,0,0,null,0,$_POST['linetitle'], $this->module_number);
						/**
						* @var $object Propal
						*/
						else if($object->element=='propal') $object->updateline($line->id, 0,$line->qty,0,0,0,0, $description ,'HT',$pagebreak,$this->module_number,0,0,0,0,$_POST['linetitle'],9);
						/**
						 * @var $object Commande
						 */
						else if($object->element=='commande') $object->updateline($line->id,$description, 0,$line->qty,0,0,0,0,'HT',$pagebreak,'','',9,0,0,null,0,$_POST['linetitle'], $this->module_number);
						
					}
					else if($action=='editlinetitle') {
						?>
						<script type="text/javascript">
							$(document).ready(function() {
								$('#addproduct').submit(function () {
									$('input[name=saveEditlinetitle]').click();
									return false;
								}) ;
							});
							
						</script>
						<?php
					}
					else {
						if((float)DOL_VERSION <= 3.4) {
							
							?>
							<script type="text/javascript">
								$(document).ready(function() {
									$('#tablelines tr[rel=subtotal]').mouseleave(function() {
										
										id_line =$(this).attr('id');
										
										$(this).find('td[rel=subtotal_total]').each(function() {
											$.get(document.location.href, function(data) {
												var total = $(data).find('#tablelines tr#'+id_line+' td[rel=subtotal_total]').html();
												
												$('#tablelines tr#'+id_line+' td[rel=subtotal_total]').html(total);
												
											});
										});
									});
								});
								
							</script>
							<?php
							
						}
					}
					
					if(empty($line->description)) $line->description = $line->desc;
					$colspan = 5;
					if($conf->margin->enabled) $colspan++;
					if($conf->global->DISPLAY_MARGIN_RATES) $colspan++;
					if($conf->global->DISPLAY_MARK_RATES) $colspan++;
					if($object->element == 'facture' && $conf->global->INVOICE_USE_SITUATION && $object->type == Facture::TYPE_SITUATION) $colspan++;
					if($conf->global->PRODUCT_USE_UNITS) $colspan++;
					
					/* Titre */
					//var_dump($line);
					?>
					<tr class="drag drop" rel="subtotal" id="row-<?php echo $line->id ?>" style="<?php
							if ($conf->global->SUBTOTAL_USE_NEW_FORMAT)
							{
								if($line->qty==99) print 'background-color:#E7FFC9'; //cilos
						   		else if($line->qty==98) print 'background-color:#F2F5FF;';
						   		else if($line->qty<=97 && $line->qty>=91) print 'background-color:#eeeeff;';
						   		else if($line->qty==1) print 'background-color:#E7D8FF;';
						   		else if($line->qty==2) print 'background-color:#F2EBFD;';
						   		else print 'background-color:#eeeeff;';
								
								//A compléter si on veux plus de nuances de couleurs avec les niveau 4,5,6,7,8 et 9
							}
							else 
							{
								if($line->qty==99) print 'background-color:#ddffdd';
						   		else if($line->qty==98) print 'background-color:#ddddff;';
						   		else if($line->qty==2) print 'background-color:#eeeeff; ';
						   		else print 'background-color:#eeffee;' ;	
							}
						   
					?>;">
					<td colspan="<?php echo $colspan; ?>" style="font-weight:bold;  <?php echo ($line->qty>90)?'text-align:right':' font-style: italic;' ?> "><?php
					
							if($action=='editlinetitle' && $_REQUEST['lineid']===$line->id ) {
								
								if ($conf->global->SUBTOTAL_USE_NEW_FORMAT)
								{
									$qty_displayed = ($line->qty >=1 && $line->qty <= 9) ? $line->qty : 100 - $line->qty;
									print img_picto('', 'subsubtotal@subtotal').'<span style="font-size:9px;margin-left:-3px;color:#0075DE;">'.$qty_displayed.'</span>&nbsp;&nbsp;';
								}
								else
								{
									if($line->qty<=1) print img_picto('', 'subtotal@subtotal');
									else if($line->qty==2) print img_picto('', 'subsubtotal@subtotal').'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
								}
								
								if($line->label=='') {
									$line->label = $line->description;
									$line->description='';
								}
								
								?>
								<label>
									<input type="text" name="line-title" id-line="<?php echo $line->id ?>"
									       value="<?php echo $line->label ?>" size="80"/>
								</label>

								<input type="checkbox" name="line-pagebreak" id="subtotal-pagebreak" value="8" <?php print ($line->info_bits > 0) ? 'checked="checked"' : '' ?> /> <label for="subtotal-pagebreak"><?php print $langs->trans('AddBreakPageBefore') ?></label>
								<br />
								<?php
								
								if($line->qty<10) {
									// WYSIWYG editor
									require_once DOL_DOCUMENT_ROOT . '/core/class/doleditor.class.php';
									$nbrows = ROWS_2;
									$cked_enabled = (!empty($conf->global->FCKEDITOR_ENABLE_DETAILS) ? $conf->global->FCKEDITOR_ENABLE_DETAILS : 0);
									if (!empty($conf->global->MAIN_INPUT_DESC_HEIGHT)) {
										$nbrows = $conf->global->MAIN_INPUT_DESC_HEIGHT;
									}
									$toolbarname = 'dolibarr_details';
									if (!empty($conf->global->FCKEDITOR_ENABLE_DETAILS_FULL)) {
										$toolbarname = 'dolibarr_notes';
									}
									$doleditor = new DolEditor('line-description', $line->description, '', 100, $toolbarname, '',
										false, true, $cked_enabled, $nbrows, '98%');
									$doleditor->Create();
								}
								
							}
							else {
								
								 if ($conf->global->SUBTOTAL_USE_NEW_FORMAT)
								 {
								 	if($line->qty<=9) 
								 	{
								 		for ($i=1;$i<$line->qty;$i++) print '&nbsp;&nbsp;&nbsp;';
								 		print img_picto('', 'subtotal@subtotal').'<span style="font-size:9px;margin-left:-3px;">'.$line->qty.'</span>&nbsp;&nbsp;';
										
									}
								 }
								 else 
								 {
									if($line->qty<=1) print img_picto('', 'subtotal@subtotal');
								 	else if($line->qty==2) print img_picto('', 'subsubtotal@subtotal').'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; 
								 }
								
								 if (empty($line->label)) {
								 	if ($line->qty >= 91 && $line->qty <= 99 && $conf->global->SUBTOTAL_USE_NEW_FORMAT) print  $line->description.' '.$this->getTitle($object, $line);
									else print  $line->description;
								 } 
								 else {
								     
                                    if (! empty($conf->global->PRODUIT_DESC_IN_FORM) && !empty($line->description)) {
                                        print $line->label.'<br><span style="font-weight:normal;">'.dol_htmlentitiesbr($line->description).'</span>';
                                    }
                                    else{
                                        print '<span class="classfortooltip" title="'.$line->description.'">'.$line->label.'</span>';    
                                    }
								 	
								 } 
								
								 if($line->info_bits > 0) echo img_picto($langs->trans('Pagebreak'), 'pagebreak@subtotal');
								
								 if($line->qty>90) { print ' : '; }
								 
								
							}
					 ?></td>
					 
					  <?php	
						
							 if($line->qty>90) {
							/* Total */
								$total_line = $this->getTotalLineFromObject($object, $line, $conf->global->SUBTOTAL_MANAGE_SUBSUBTOTAL);
								?>
								<td align="right" style="font-weight:bold;" rel="subtotal_total"><?php echo price($total_line) ?></td>
								<?php
								
							}
							 else {
							 	
								?>
								<td>&nbsp;</td>
								<?php
							 }	
						?>
					
					<td align="center">
						<?php
							if($action=='editlinetitle' && $_REQUEST['lineid']==$line->id ) {
								?>
								<input class="button" type="button" name="saveEditlinetitle" value="<?php echo $langs->trans('Save') ?>" />
								<script type="text/javascript">
									$(document).ready(function() {
										$('input[name=saveEditlinetitle]').click(function () {
											$.post("<?php echo '?'.$idvar.'='.$object->id ?>",{
													action:'savelinetitle'
													,lineid:<?php echo $line->id ?>
													,linetitle:$('input[name=line-title]').val()
												<?php if ($cked_enabled) { ?>
													,linedescription: CKEDITOR.instances['line-description'].getData()
												<?php } else { ?>
													, linedescription: $('textarea[name=line-description]').val()
												<?php } ?>
													,pagebreak:($('input[name=line-pagebreak]').is(':checked') ? 8 : 0)
											}
											,function() {
												document.location.href="<?php echo '?'.$idvar.'='.$object->id ?>";	
											});
											
										});
										
										$('input[name=cancelEditlinetitle]').click(function () {
											document.location.href="<?php echo '?'.$idvar.'='.$object->id ?>";
										});
										
									});
									
								</script>
								<?php
							}
							else{
								
								if ($object->statut == 0  && $user->rights->{$object->element}->creer) {
								
								?>
									<a href="<?php echo '?'.$idvar.'='.$object->id.'&action=editlinetitle&lineid='.$line->id ?>">
										<?php echo img_edit() ?>		
									</a>
								<?php
								
								}								
							}
						?>
					</td>

					<td align="center" nowrap="nowrap">	
						<?php
							if($action=='editlinetitle' && $_REQUEST['lineid']===$line->id ) {
								?>
								<input class="button" type="button" name="cancelEditlinetitle" value="<?php echo $langs->trans('Cancel') ?>" />
								<?php
							}
							else{
								if ($object->statut == 0  && $user->rights->{$object->element}->creer) {
								
									?>
										<a href="<?php echo '?'.$idvar.'='.$object->id.'&action=ask_deleteline&lineid='.$line->id ?>">
											<?php echo img_delete() ?>		
										</a>
									<?php								
									
									
									if($line->qty<10) {
										
									?><a href="<?php echo '?'.$idvar.'='.$object->id.'&action=ask_deleteallline&lineid='.$line->id ?>">
											<?php echo img_picto($langs->trans('deleteWithAllLines'), 'delete_all@subtotal') ?>		
										</a><?php								
									}
									
								}
								
																	
							}
						?>	
						
					</td>

					<?php if ($num > 1 && empty($conf->browser->phone)) { ?>
					<td align="center" class="tdlineupdown">
					</td>
				    <?php } else { ?>
				    <td align="center"<?php echo ((empty($conf->browser->phone) && ($object->statut == 0  && $user->rights->{$object->element}->creer))?' class="tdlineupdown"':''); ?>></td>
					<?php } ?>

					</tr>
					<?php
					
					
			return 1;	
			
		}
		
		return 0;

	}

	
}
