function tableOrdering( order, dir, task ) {
	var form = document.adminForm;

	form.filter_order.value 	= order;
	form.filter_order_Dir.value	= dir;
	submitform( task );
}

function submitform(pressbutton){
	if (pressbutton) {
		document.adminForm.task.value=pressbutton;
	}

	if( typeof(CodeMirror) == 'function'){
		for (x in CodeMirror.instances){
			document.getElementById(x).value = CodeMirror.instances[x].getCode();
		}
	}

	if (typeof document.adminForm.onsubmit == "function") {
		document.adminForm.onsubmit();
	}
	document.adminForm.submit();
	return false;
}

function hikashopCheckChangeForm(type,form){
	var varform = eval('document.'+form);
	if(typeof hikashop != 'undefined' && typeof hikashop['reqFieldsComp'] != 'undefined' && typeof hikashop['reqFieldsComp'][type] != 'undefined' && hikashop['reqFieldsComp'][type].length > 0){
		for(var i =0;i<hikashop['reqFieldsComp'][type].length;i++){
			elementName = 'data['+type+']['+hikashop['reqFieldsComp'][type][i]+']';
			if( typeof varform.elements[elementName]=='undefined'){
				elementName = type+'_'+hikashop['reqFieldsComp'][type][i];
			}
			elementToCheck = varform.elements[elementName];
			elementId = 'hikashop_'+type+'_'+ hikashop['reqFieldsComp'][type][i];
			el = document.getElementById(elementId);
			if(elementToCheck && (typeof el == 'undefined' || el == null || typeof el.style == 'undefined' || el.style.display!='none') && !hikashopCheckField(elementToCheck,type,i,elementName,varform.elements)){
				if(typeof hikashop['entry_id'] != 'undefined'){
					for(var j =1;j<=hikashop['entry_id'];j++){
						elementName = 'data['+type+'][entry_'+j+']['+hikashop['reqFieldsComp'][type][i]+']';
						elementToCheck = varform.elements[elementName];
						elementId = 'hikashop_'+type+'_'+ hikashop['reqFieldsComp'][type][i] + '_' + j;
						el = document.getElementById(elementId);
						if(elementToCheck && (typeof el == 'undefined' || el == null || typeof el.style == 'undefined' || el.style.display!='none') && !hikashopCheckField(elementToCheck,type,i,elementName,varform.elements)){
							return false;
						}
					}
				}else{
					return false;
				}
			}
		}

		if(type=='register'){
			//check password
			if(typeof varform.elements['data[register][password]'] != 'undefined' && typeof varform.elements['data[register][password2]'] != 'undefined'){
				passwd = varform.elements['data[register][password]'];
				passwd2 = varform.elements['data[register][password2]'];
				if(passwd.value!=passwd2.value){
					alert(hikashop['password_different']);
					return false;
				}
			}

			//check email
			var emailField = varform.elements['data[register][email]'];
			emailField.value = emailField.value.replace(/ /g,"");
	        var filter = /^([a-z0-9_'&\.\-\+])+\@(([a-z0-9\-])+\.)+([a-z0-9]{2,10})+$/i;
	        if(!emailField || !filter.test(emailField.value)){
	          alert(hikashop['valid_email']);
	          return false;
	        }
		}else if(type=='address'){
			if(typeof varform.elements['data[address][address_telephone]'] != 'undefined'){
				var phoneField = varform.elements['data[address][address_telephone]'];
				if(phoneField){
					phoneField.value = phoneField.value.replace(/ /g,"");
					if(phoneField.value.length > 0){
						var filter = /[0-9]+/i;
						if( !filter.test(phoneField.value)){
							 alert(hikashop['valid_phone']);
					         return false;
						}
					}
				}
			}
		}
	}
	return true;
}

function hikashopCheckField(elementToCheck,type,i,elementName,form){
	if(elementToCheck){
		var isValid = false;
		if(typeof elementToCheck.value != 'undefined'){
			if(elementToCheck.value==' ' && typeof form[elementName+'[]'] != 'undefined'){
				if(form[elementName+'[]'].checked){
					isValid = true;
				}else{
					for(var a=0; a < form[elementName+'[]'].length; a++){
						  if(form[elementName+'[]'][a].checked && form[elementName+'[]'][a].value.length>0) isValid = true;
					}
				}
			}else{
				if(elementToCheck.value.length>0) isValid = true;
			}
		}else{
			for(var a=0; a < elementToCheck.length; a++){
			   if(elementToCheck[a].checked && elementToCheck[a].value.length>0) isValid = true;
			}
		}
		if(!isValid){
			elementToCheck.className = elementToCheck.className +' invalid';
			alert(hikashop['validFieldsComp'][type][i]);
			return false;
		}
	}
	return true;
}

if (typeof(jQuery) != "undefined") {
	if (typeof(jQuery.noConflict) == "function") {
		jQuery.noConflict();
	}
}


