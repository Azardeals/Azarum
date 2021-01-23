
function doAjax(strURL,strPostData,strObj,method,strProgressMessage,nTime,fn) {
    var xmlHttpReq = false;
    var self = this;
	var msg="";
    // Mozilla/Safari
	
	www=(window.location.href.toLowerCase().indexOf("//www.")>0)?"http://www.":"http://";
	strURL=strURL.replace("http://",www);
	updateMessage(strObj,strProgressMessage);

    if (window.XMLHttpRequest) {
        self.xmlHttpReq = new XMLHttpRequest();
    }
    // IE
    else if (window.ActiveXObject) {
        self.xmlHttpReq = new ActiveXObject("Microsoft.XMLHTTP");
    }
    self.xmlHttpReq.open(method, strURL, true);
    self.xmlHttpReq.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    self.xmlHttpReq.onreadystatechange = function() {
		
        if (self.xmlHttpReq.readyState == 4) 
		{
			msg=(strObj+"|"+self.xmlHttpReq.responseText).split("|");
			for (i=0;i<msg.length;i=i+2)
			{
				updateMessage(msg[i],msg[i+1]);
			}
			
			switch(fn)
			{
				case "renderProductOptions":
					removeItemRow();
					updateMessage(strObj,"Inventory updated successfully!");
					renderProductOptions(self.xmlHttpReq.responseText);
					
					break;
				case null:
					break;
			}
			
			if (nTime>0) setTimeout("hide('"+strObj+"');",nTime);
        }
		else
		{
			//alert(self.xmlHttpReq.readyState);
		}
    }
    self.xmlHttpReq.send(strPostData);
}

function removeItemRow()
{
  var tbl = document.getElementById('tblGroups');
  var lastRow = tbl.rows.length;
  var iteration = lastRow;
  for (i=3;i<iteration;i++)
  {
  	tbl.deleteRow(tbl.rows.length-1);
	
  }

}


function updateMessage(obj,str)
{
	show(obj);	
	document.getElementById(obj).innerHTML=str;
}

function showHide(id)
{
	if (document.getElementById(id).style.display=='')
		document.getElementById(id).style.display='none';
	else
		document.getElementById(id).style.display='';
		
}
function show(id)
{
		document.getElementById(id).style.display='block';
		
}
function hide(id)
{
		document.getElementById(id).style.display='none';
}