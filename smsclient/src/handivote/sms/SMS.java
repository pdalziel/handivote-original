package handivote.sms;

import java.util.regex.*;

public class SMS {
	int barcodelength = 8;
    String sms, originalsms, barcode, pin, option1;
    String fromnumber="";
	String location;
    
    boolean validSMS=false;
    Pattern p = Pattern.compile("([0-9]*)");
    

    public SMS(String s) {
	sms=s;
	
	extract();
    }

    public SMS(String locnum, String num, String s) {
	sms=s;
	location=locnum;
	fromnumber=num;
	extract();
    }


    /*public int countSpaces(String s) {
	int count=0;
	int pos=s.indexOf(" ");
	String ss = s.trim();
	while (pos>=0) {
	    count++;
	    ss = ss.substring(pos).trim();
	}
	return count;
	}*/

    public boolean isNumber(String n) {
	try {
	    Integer i = new Integer(n);
	    return true;
	}
	catch (Exception ee) {
	    return false;
	}
    }

    public String getNumber(String s, int len) {
	//grab barcode+pin digits from message
	String num="";
	if (s.length() == 0) return num;
	if (s.length() < len) return num;
	String ss = s.substring(0);
	System.out.println("ss is " + ss);
	while (num.length() < len) {
	    String one = ss.substring(0,1);
	    //System.out.println("one is " + one);
	    if (isNumber(one)) {
		num=num+one;
		
	    }
		else return "";
	    ss = ss.substring(1).trim();
	    if (ss.equals("")) break;
	}
	return num;
	
    }

    
    public void extract() {
	System.out.println("sms is " + sms);
	sms = sms.toLowerCase();
	int pos=sms.indexOf(" sms parts");
	//System.out.println("============message="+sms);
	//System.out.println(sms+"position of sms parts is " + pos);
	if (pos > 2) {
	    sms = sms.substring(0,pos-1).trim();
	    //System.out.println("sms is " + sms);
	}
	sms = sms.replace("\n"," ");
	sms = sms.replace("%"," ");
	sms = sms.replace("\t"," ");
	sms = sms.replace("loc","");
	sms = sms.toLowerCase();
	sms = sms.replace("don't build","1");
	sms = sms.replace("dont build","1");
	sms = sms.replace("dontbuild","1");
	sms = sms.replace("don'tbuild","1");
	sms = sms.replace("build","2");

	originalsms = new String(sms);	

	char alph='a';
	for (int i=0; i<26; i++) {
	    sms = sms.replace(""+alph,"");
	    alph++;
	}

	//sms = sms.replace("barcode","");
	//sms = sms.replace("pin","");
	//sms = sms.replace("option","");
	sms = sms.replaceAll("[-+.^:,=:]","");
	sms = sms.trim();
	//System.out.println("sms is " + sms);

	pos=0;
	pos = sms.indexOf("  ");
	while (pos>=0) {
	    sms = sms.replace("  "," ");
	    pos = sms.indexOf("  ");
	}
	if (Process.testing) System.out.println("message="+sms);

	
	pos=0;

	barcode = getNumber(sms,barcodelength);
	if (Process.testing) System.out.println("card num " + barcode);
	if (barcode.length() != barcodelength) {
	    return;
	}
	String rest = sms.substring(barcodelength);
	if (Process.testing) System.out.println("rest is " + rest);
	//rest = (rest.replace(barcode.substring(4),"")).trim();
	pin = getNumber(rest,4);
	//System.out.println("pin " + pin);
	if (pin.length() != 4) {
	    return;
	}

	if (Process.testing) System.out.println("pin " + pin);	
	// valid nums
	//rest = rest.replace(barcode,"");
	rest = rest.substring(4).trim();
	
if (Process.testing) 	System.out.println("rest is " + rest);
	if (rest.length() ==0) { // bad vote
	    return;
	}

	// try to pull out the 3 numbers
	//int pos2 = rest.indexOf(" ");
	//if (pos2 <=0){
	    // only one number
	    option1=rest;
	    if (isNumber(option1)) {
		validSMS=true;
		//option1=Process.db.getNumber(1,option1);
		if (Process.testing) System.out.println("option1 " + option1);
		return;
	    }

	//}
	//option1 = rest.substring(0,pos2).trim();
	//System.out.println("option1 " + option1);
	
	/*rest = rest.substring(pos2).trim();
	int pos3 = rest.indexOf(" ");
	if (pos3<=0) {
	    option2=rest;
	    if (isNumber(option1) && isNumber(option2)) {
		option1=Process.db.getNumber(1,option1);
		option2=Process.db.getNumber(2,option2);
		validSMS=true;
		return;
	    }
	}
	option2 = rest.substring(0,pos3).trim();
	option3 = rest.substring(pos3).trim();
	*/
	
	/*
	if (isNumber(option1) && isNumber(option2) && isNumber(option3)) {
	    option1=Process.db.getNumber(2,option1);
	    option2=Process.db.getNumber(3,option2);
	    option3=Process.db.getNumber(4,option3);
		
	    validSMS=true;
	}
	*/
	

    }

    public void print() {
	if (validSMS)
		System.out.println("barcode="+barcode+",pin="+pin);
		else System.out.println("Invalid message");
    }

}
