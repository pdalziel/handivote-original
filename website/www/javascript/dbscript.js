

function trust(opt,refid) {
    $.ajax({type: "GET",
            url: "trust.cfm?option="+opt,
            dataType: "text",
            timeout: 5000,
            async: false,
            success: function(request){ 
                //alert("Done") ;
                //errmessage=request;
                //alert(request.responseText);
            },
            error: function(request,error){
                //alert("Something went wrong, sorry!");
                //alert("error:" + request.responseText);
                //errmessage="Something went wrong :-(";

            } 
      }); 
if(opt == 1) return false;
	var x = document.getElementById("verify");
	var cardnum = document.getElementById("cardnumber");
	var vote="No found"; 
    $.ajax({type: "GET",
            url: "getvote.cfm?cardnum="+cardnum.value+"&refid="+refid,
            dataType: "text",
            timeout: 5000,
            async: false,
            success: function(request){
		vote=request; 
                //alert(request) ;
                //errmessage=request;
                //alert(request.responseText);
            },
            error: function(request,error){
                //alert("Something went wrong, sorry!");
                //alert("error:" + request.responseText);
                //errmessage="Something went wrong :-(";

            } 
      });
	x.value=vote;
      return false;
}



function storeAnswer(qnumber,answer) {
    voted="-";
    if (qnumber == 1) {
	var yes = document.getElementById('yes');
	var no = document.getElementById('no');
        if (yes.checked) voted="yes";
	if (no.checked) voted="no";
    }
    var theanswer = document.getElementById(answer).value;
    $.ajax({type: "GET",
            url: "saveq.cfm?qnumber="+qnumber+"&answer="+theanswer+"&voted="+voted,
            dataType: "text",
            timeout: 5000,
            async: false,
            success: function(request){ 
                //alert("Done") ;
		if (qnumber<3) {
                document.getElementById("question"+String(qnumber)).style.display="none";
	
                document.getElementById("thanks"+String(qnumber)).style.display="block";
		}
                errmessage=request;
                //alert(request.responseText);
            },
            error: function(request,error){
                //alert("Something went wrong, sorry!");
                //alert("error:" + request.responseText);
                //errmessage="Something went wrong :-(";

            } 
      }); 
      return false;
}



