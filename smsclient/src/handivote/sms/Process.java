package handivote.sms;

/*

check if directory for person
if not, create
create directory within with id from database
if yes, copy file across 

run simulation and use directory as destination
zip up output
email to user

*/

import java.util.*;
import java.io.*;
import java.text.*;

import org.apache.commons.httpclient.*;
import org.apache.commons.httpclient.methods.*;
import org.apache.commons.httpclient.params.HttpMethodParams;


public class Process {

	static Boolean testing=false;
	static Boolean ackEnable=false;
    static Vector <String>locations = new Vector<String>();

	static Calendar today = Calendar.getInstance();
	static int day, month, yr, hour, minute;
    
    static String url="http://localhost/handivote/handivote.php";
    final static long delay = 300000;//5*60*1000;//60* 60 * 1000;
    final static Object waitObject = new Object();
    static PrintWriter logfile, anomaliesfile, invokefile;
    static String fname="gammu.mobilenumbers";
    static String logfname="gammu.invokes";

    static String anomaliesfname="anomalous.messages";
    static String errorMessage="";
    static DB db;

    public static void removeMessages(String num) {
	if (testing) return;
	System.out.println("Removing message " + num);
	try {
	    //System.out.println("java -jar attends.jar "+filename + " " + coursename);
	    //String [] cmd = {"/bin/tcsh","sudo /usr/bin/gammu getallsms"};
	    String [] cmd = {"/bin/bash","-c","/usr/bin/gammu deletesms 1 "+num};
	    //System.out.println("sudo /usr/local/bin/gammu getallsms");
	    java.lang.Process p = Runtime.getRuntime().exec(cmd);
	    p.waitFor();
	    //System.out.println("Gammu delete exit value = " +p.exitValue());

	}
	catch (Exception ee) {
	    System.out.println("Problem running Gammu");
	    ee.printStackTrace();
	}

    }

    public static String getSMSMessages() {
	//	Vector<SMS> messages = new Vector<SMS>();
	String messages="";
	try {
	    //System.out.println("java -jar attends.jar "+filename + " " + coursename);
	    //String [] cmd = {"/bin/tcsh","sudo /usr/bin/gammu getallsms"};
	    String [] cmd = {"/bin/bash","-c","/usr/bin/gammu getallsms"};
	    //System.out.println("sudo /usr/local/bin/gammu getallsms");
	    java.lang.Process p = Runtime.getRuntime().exec(cmd);
	    p.waitFor();
	    System.out.println("Gammu read exit value = " +p.exitValue());

	    InputStreamReader out = new InputStreamReader(p.getInputStream());
	    BufferedReader in = new BufferedReader(out);
	    String outstream="";
	    String line="";
	    while ((line=in.readLine()) != null) {
		outstream=outstream+"\n"+line;
	    }
	    out.close();
	    in.close();
	    messages=outstream;
	    //System.out.println("$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$");
	    //System.out.println("output produced " + outstream);
	    //System.out.println("$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$");
	    errorMessage=outstream;
	}
	catch (Exception ee) {
	    System.out.println("Problem running Gammu");
	    ee.printStackTrace();
	}
	return messages;
	
    }
    

    public static void waitABit() {
	try {
	    
	    System.out.println("Sleeping for 5 minutes ...." + getTime());
	    synchronized(waitObject) {
		waitObject.wait(delay);
	    }
	}
	catch (Exception ee) {
	    // do nowt
	    ee.printStackTrace();
	    System.exit(0);
	}
    }
    
    public static String getTime() {
        java.util.Date date = new java.util.Date();
        Format formatter = new SimpleDateFormat("hh:mm");
        String s = formatter.format(date);
        return s;
    }
    
    public static String getDayTime() {
        java.util.Date date = new java.util.Date();
        Format formatter = new SimpleDateFormat("hh.mm");
        String s = formatter.format(date);
        s= "."+day+"."+month+"."+yr+"."+s;
        return s;
    }

    public static Vector<Integer> getPositions(String s) {
	int thepos=s.indexOf("Location ");
	int len=s.length();
	int pos=0;
	Vector<Integer> positions = new Vector<Integer>();
	while (thepos>=0) {
	    //System.out.println("Position" + thepos);
	    pos = pos + thepos+1;
	    //System.out.println("pos is " + pos);
	    positions.addElement(new Integer(pos));
	    s = s.substring(thepos+1);
	    //System.out.println("thepos= " + pos);
	    thepos=s.indexOf("Location ");
	    //if (thepos>=0) {
	    //	pos=pos+s.indexOf("Location ");
	    //}
	    //else break;
	    
	}
	//System.exit(0);
	//positions.addElement(new Integer(s.length()));
	//System.out.println("Position" + s.length());
	//thepos=s.indexOf("SMS parts");
	//pos = pos + thepos-3;
	//positions.addElement(new Integer(pos));
	return positions;
    }

    public static String getFrom(String s, int pos) {
	int posit = s.substring(pos).indexOf("Remote number");
	posit=posit+13;
	int posit2 = s.substring(pos).indexOf("Status");
	String fromnumber = s.substring(pos+posit,pos+posit2);
	fromnumber=fromnumber.replace("\"","");
	fromnumber=fromnumber.replace("+","");
	fromnumber=fromnumber.replace(":","");
	fromnumber=fromnumber.trim();
	return fromnumber;
    }

    public static Vector<SMS> getMessages(String s,Vector<Integer> positions) {
	// remove last bits
		
	Vector<SMS> mess = new Vector<SMS>();
	if (s == null) return mess;
	if (s.length() ==0) return mess;
	int pos, pos2=0;
	for (int i=0; i<positions.size()-1;i++) {
	    pos=positions.elementAt(i).intValue();
	    pos2 = positions.elementAt(i+1).intValue();
		if ((pos>=0) && (pos2>=0)) {
	    //System.out.println("s="+s);
	    int pp = s.substring(pos).indexOf(" ");
	    int pp1 = s.substring(pos).indexOf(",");
		if ((pp >= 0) && (pp1 >=0)) {
	    //System.out.println(pp + " " + pp1);
	    String locnum = s.substring(pos+pp,pos+pp1).trim();
	    //System.out.println("locnum is " + locnum);
	    
	    if (!processed(locnum)) {
		System.out.println("==================");
		System.out.println("Processing location " + locnum);
		String fromnumber = getFrom(s,pos);
		if(testing)System.out.println("from number is " + fromnumber);
	
		//locations.addElement(locnum);
		//	    int x= pos2-pos;
		//for (int j=0; j<=x; j++) {
		//	System.out.println(j + " " + pos + " " + x);
		//System.out.println(s.substring(pos,j));
		//}
		SMS sms = new SMS(locnum,fromnumber,extractMessage(s.substring(pos,pos2+2)));
		mess.addElement(sms);
		//locations.addElement(locnum);
	    }
	    
	    }}
	    //System.out.println("&&&&&&&&&&&&message: " + extractMessage(s.substring(pos,pos2)));
	}
	int pp = s.substring(pos2).indexOf(" ");
	int pp1 = s.substring(pos2).indexOf(",");
	
	if ((pp >=0) && (pp1 >=0)) {
	String locnum = s.substring(pos2+pp,pos2+pp1).trim();
	if (!processed(locnum)) {
	    if(testing)System.out.println("Processing location " + locnum);
	    String fromnumber = getFrom(s,pos2);
	    if(testing)System.out.println("from number is " + fromnumber);


	    //int posit = s.substring(pos2).indexOf("+44");
	    //int posit2 = s.substring(pos2).indexOf("Sent");
	    //String fromnumber = s.substring(pos2+posit,pos2+posit2).trim();
	    

	    //System.out.println("from number is " + fromnumber);

	    mess.addElement(new SMS(locnum,fromnumber,extractMessage(s.substring(pos2))));
	    //locations.addElement(locnum);
	//System.out.println("message: " + extractMessage(s.substring(pos2)));
	}}
	return mess;
    }

    public static boolean processed(String loc) {
	for (int i=0; i<locations.size(); i++) {
	    if (locations.elementAt(i).equals(loc)) return true;
	}
	return false;
    }

    public static String extractMessage(String s) {
	int pos = s.indexOf(": Read");
	if (pos >=0) {
	    s = s.substring(pos+7).trim();
	}
	if (pos < 0) {
	    pos = s.indexOf(": UnRead");
	    s = s.substring(pos+9).trim();
	}
	    
	return s;
    }
	public static void recordNumber(String number) {
		try 	{
		if (logfileok) {
		    logfile.println(number); 
		    logfile.flush();
		}
		}
		catch (Exception ee) {
			if (testing) System.out.println("Couldn't record number");
		}
	}

	public static void recordInvoke(String number) {
		try 	{
		if (invokefileok) {
		    invokefile.println(number); 
		    invokefile.flush();
		}
		}
		catch (Exception ee) {
			if (testing) System.out.println("Couldn't record invoke");
		}
	}



    public static void ack(String number) {
    if(testing) return;
    

	String url="http://www.kapow.co.uk/scripts/sendsms.php?"+
	    "username=handivote&password=xxxx&mobile="+number+
	    "&sms="+"Handivote+thanks+you+for+your+vote";

    // Create an instance of HttpClient.
    HttpClient client = new HttpClient( );
    //client.getHostConfiguration().setProxy("wwwcache.dcs.gla.ac.uk", 8080);
    //Properties props = System.getProperties();
   
    // Create a method instance.
    GetMethod method = new GetMethod(url);
    System.out.println("Invoked " + url);
    
    
    // Provide custom retry handler is necessary
    method.getParams().setParameter(HttpMethodParams.RETRY_HANDLER, 
    		new DefaultHttpMethodRetryHandler(3, false));

    try {
      // Execute the method.
	
      //int statusCode = client.executeMethod(method);

      //if (statusCode != HttpStatus.SC_OK) {
        //System.err.println("Method failed: " + method.getStatusLine());
      //}


      // Read the response body.
      InputStream responseBody = method.getResponseBodyAsStream();

      // Deal with the response.
      // Use caution: ensure correct character encoding and is not binary data
      
      //System.out.println("Done!");

    } catch (HttpException e) {
      System.err.println("Fatal protocol violation: " + e.getMessage());
      e.printStackTrace();
    } catch (IOException e) {
      System.err.println("Fatal transport error: " + e.getMessage());
      e.printStackTrace();
    } finally {
      // Release the connection.
      method.releaseConnection();
    }  

    }




    public static void callServer(SMS sms) {

	if (testing) return; // REMOVE....
	String barcode=sms.barcode;
	String pin=sms.pin;
	String option1=sms.option1;
	//String option2=sms.option2;
	//String option3=sms.option3;


    // Create an instance of HttpClient.
    HttpClient client = new HttpClient( );
    //client.getHostConfiguration().setProxy("wwwcache.dcs.gla.ac.uk", 8080);
    Properties props = System.getProperties();
   
    // Create a method instance.
    GetMethod method = new GetMethod(url+"?barcode="+barcode+"&pin="+pin
				     +"&option1="+option1);
    recordInvoke(url+"?barcode="+barcode+"&pin="+pin
				     +"&option1="+option1);
    System.out.println("Invoked " + url+"?barcode="+barcode+"&pin="+pin
				     +"&option1="+option1);
    
    // Provide custom retry handler is necessary
    method.getParams().setParameter(HttpMethodParams.RETRY_HANDLER, 
    		new DefaultHttpMethodRetryHandler(3, false));

    try {
      // Execute the method.
	
      int statusCode = client.executeMethod(method);

      if (statusCode != HttpStatus.SC_OK) {
        System.err.println("Method failed: " + method.getStatusLine());
      }

      // Read the response body.
      InputStream responseBody = method.getResponseBodyAsStream();

      // Deal with the response.
      // Use caution: ensure correct character encoding and is not binary data
      
      //System.out.println("Done!");

    } catch (HttpException e) {
      System.err.println("Fatal protocol violation: " + e.getMessage());
      e.printStackTrace();
    } catch (IOException e) {
      System.err.println("Fatal transport error: " + e.getMessage());
      e.printStackTrace();
    } finally {
      // Release the connection.
      method.releaseConnection();
    }  

    }


    public static void process(String s) {
	if (s==null) return;
	int k = s.indexOf("SMS parts");
	if (k<0) return;
	s = s.substring(0,k-3);

	Vector<Integer> positions = getPositions(s);
	Vector<SMS> mess = getMessages(s,positions);
	for (int i=0; i<mess.size(); i++) {
	    SMS sms = mess.elementAt(i);
	    if(testing)System.out.println("message " + i + " " + sms.validSMS);
	   if (sms.validSMS) {
		sms.print();
		recordNumber(sms.fromnumber);
		if(ackEnable)ack(sms.fromnumber);
		callServer(sms);
		removeMessages(sms.location);
	    }
	else {

		if (anomfileok) {
		    anomaliesfile.println(sms.fromnumber+":"+sms.originalsms); 
		    anomaliesfile.flush();
		removeMessages(sms.location);
		}
		// add to exceptions file
		// delete
	}	
	}
    }

    static boolean logfileok=false,anomfileok=false, invokefileok=false;
    
    public static void main(String [] args) {
	
    if (args.length > 1) testing=true;
    
	System.out.println("-----Launching gammu.Process");
	if (testing) System.out.println("TESTING - NOT LIVE!");
	String destination;
	//System.setProperty("proxySet","true");
	//System.setProperty("proxyHost","wwwcache.dcs.gla.ac.uk");
	//System.setProperty("proxyPort","8080");

	db = new DB();
	
	try {
	    File file = new File(fname);
	    if (file.exists()) {
		logfile = new PrintWriter(new FileWriter(fname, true));
		logfileok=true;
		if (testing) System.out.println("mobile num file opened");
	    }
	    else {
		logfile = new PrintWriter(new FileWriter(fname));
		logfileok=true;
		if (testing) System.out.println("mobile num file created");
	    }
		File file2 = new File(anomaliesfname);	
	    if (file2.exists()) {
		anomaliesfile = new PrintWriter(new FileWriter(anomaliesfname, true));
		if (testing) System.out.println("Anomalies file opened");
		anomfileok=true;
	    }
	    else {
		anomaliesfile = new PrintWriter(new FileWriter(anomaliesfname));
		anomfileok=true;
		if (testing) System.out.println("anomalies file created");
	    }
		File file3 = new File(logfname);	
	    if (file3.exists()) {
		invokefile = new PrintWriter(new FileWriter(logfname, true));
		if (testing) System.out.println("Invoke file opened");
		invokefileok=true;
	    }
	    else {
		invokefile = new PrintWriter(new FileWriter(logfname));
		invokefileok=true;
		if (testing) System.out.println("invoke file created");
	    }

	}
	catch (Exception ee) {
	    ee.printStackTrace();
	    
	}
	int locNumber=0;

	try {
	    if (args.length>0) locNumber=new Integer(args[0]).intValue(); 
		//int pos = new Integer(args[0]).intValue();
		for (int i=0; i<=locNumber; i++) {
		    locations.addElement(""+i);
		}
	    }
	    catch (Exception ee) {
	    }
	    
	
	//else locations.addElement(""+19);


		today = Calendar.getInstance();
		day=today.get(Calendar.DAY_OF_MONTH);
		month=today.get(Calendar.MONTH)+1;
		yr=today.get(Calendar.YEAR);

	try {
	    while (true) {
		
		System.out.println("*****Checking for SMS Messages");
		boolean success=false; 
		hour=today.get(Calendar.HOUR);
		
		String messages = getSMSMessages();
		System.out.println("message string = " + messages);
		//if (logfileok) {
		  //  logfile.println(messages); 
		    //removeMessages();
		//}
		process(messages);
		
		if (testing) break; // remove
		waitABit();
		
	    }
	    
	}
	catch (Exception ee) {
	    System.out.println("Serious Problem - Terminating");
	    ee.printStackTrace();
	    System.exit(0);

	}
	try {
	    if (logfileok) logfile.close();
	    if (anomfileok) anomaliesfile.close();
	    if (invokefileok) invokefile.close();
	}
	catch (Exception ee) {
	}
	System.out.println("-----gammu.Process Completed");

    }

}
