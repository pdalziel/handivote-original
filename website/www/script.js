
<script type="text/javascript">

/* 
Contains JavaScript functions used throughout the user interface
This file is included by the user interface pages
Author: Tsvetelina Valcheva
Last Modified: 29/01/13
*/

/* function invoked when the user attempts to delete a referendum
asks the user to confirm that they wish to delete the referendum

if the user confirms, then the user is redirected to delete_referendum.php and
the ID of the referendum is passed via GET
*/

function deleteAlert(id){
var conBox = confirm("Are you sure you want to delete this referendum?");
if(conBox){ 
location.href="delete_referendum.php?id="+ id;
}else{
return;
}
}

/* function to contract elements of the user interface */
/* element has been hardcoded for testing */
function removeElement() 
{
document.getElementById("q1paragraph").style.display="none";
document.getElementById("q1").style.display="none";

}

/* function to expand elements of the user interface */
function expandElement(divname)
{
document.getElementById(divname).style.display="block";
}


/* function to contract elements of the interface*/
/** due to change in requirements, this feature had to be discontinued - thus it is commendted out to allow no contracting of paragraph*/
function contractElement(divname)
{
//document.getElementById(divname).style.display="none";
}



//displays a thank you message after submitting vote

function displayThankyou(){
document.getElementById("thankyou").style.display="block";
}


/* function to display the input the user entered back to him/her so that they can verify their input
if no selection has been made, a message is displayed informing the user  */
function displayInput(input, output){
    var userInput = document.getElementById(input);           // get selection section
    var textInput = userInput.options[userInput.selectedIndex].text;  // get selected item, get its text
	var optionNumber = userInput.options[userInput.selectedIndex].value;
    if (optionNumber==null){
       document.getElementById(output).innerHTML = "Please select a value";
        
    }
    else {

	document.getElementById(output).innerHTML = "Your Choice: " + textInput;
    }
}


/* function to update hidden variables in HTML
used for capturing input in the handivote.php page */
function updateHidden(input, output){
    var userInput = document.getElementById(input); // get selection section
    var optionNumber = userInput.options[userInput.selectedIndex].value;  // get numerical value selected item
    document.getElementById(output).value = optionNumber;
    
}

/* function to display the correct set of options depending on the type of question
checks to see what question type has been selected and displays options accordingly
*/

function displayOption(question_number){
    var choiceQuestionType = document.getElementById("question_type_"+question_number); // get selection section
    var questionType = choiceQuestionType.options[choiceQuestionType.selectedIndex].value;  // get numerical value selected item
    
    if (questionType==2){
    document.getElementById("spend_options_"+question_number).style.display="block";
     document.getElementById("simple_options_"+question_number).style.display="none";
     document.getElementById("tax_options_"+question_number).style.display="none";
    }
     if (questionType==1){
    document.getElementById("simple_options_"+question_number).style.display="block";
     document.getElementById("spend_options_"+question_number).style.display="none";
     document.getElementById("tax_options_"+question_number).style.display="none";
    }
     if (questionType==3){
    document.getElementById("simple_options_"+question_number).style.display="none";
     document.getElementById("spend_options_"+question_number).style.display="none";
     document.getElementById("tax_options_"+question_number).style.display="block";
    }
    

  
}
</script>