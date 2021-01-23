function isValidURL(url){
    
	var RegExp = /^[A-Za-z0-9\_-]$/
//var RegExp = /[a-zA-Z0-9\.\/:]+/
 

//("^[A-Za-z]+://[A-Za-z0-9-_]+\\+$"

    if(RegExp.test(url)){
	alert("true");
        return true;
		
    }else{
	alert("false");
        return false;
    }
}
