
/*-------------------------------------------
    Main document ready
---------------------------------------------*/

$(document).ready(function() {

        /* Sum alimentaire */

	$('.classcatmalim').on('change',function(){
  		var totalPoints = 0;
  		$(this).find('input[type="number"]').each(function(){
    			totalPoints += parseInt($(this).val());
  		});
  		document.getElementsByName("qtytotalalim")[0].value = totalPoints;
		});
	$('.classpcef').on('change',function(){
  		var totalPoints = 0;
  		$(this).find('input[type="number"]').each(function(){
    			totalPoints += parseInt($(this).val());
  		});
  		document.getElementsByName("qtypcef")[0].value = totalPoints;
		});
	$('.classpcec').on('change',function(){
  		var totalPoints = 0;
  		$(this).find('input[type="number"]').each(function(){
    			totalPoints += parseInt($(this).val());
  		});
  		document.getElementsByName("qtypcec")[0].value = totalPoints;
	});

	/* Restricted numeric in input[number] */

	$('input[type="number"]').keyup(function(){
    			reg = new RegExp("[^0-9\.]", "g");
    			_val = $(this).val();
    			_val.replace(reg, "");
   			 $(this).val( _val );
	});

	/* Mise en page ul li ul li ul li ... */

	$("ul.toggle_container").hide();
	   $("label.trigger").click(function(){
	      $(this).toggleClass("active").next().slideToggle("fast");
              return false; 
        });

	/* Links selected dad son */

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


/*-------------------------------------------
    Quantity by default
---------------------------------------------*/

function qtydefaul(id)	{
	  
	var qtydef = document.getElementById("qtydef").value;
  	  
	if (document.getElementById(id).checked==1) {

  	      	document.getElementsByName("Qteproduit"+id)[0].value = qtydef;

	} else {

        	document.getElementsByName("Qteproduit"+id)[0].value = '0';
        }
}




