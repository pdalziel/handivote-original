package handivote.sms;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;

public class DB {

	static boolean emailing=true;
	static Connection conn;
	static String username="root",password="";
	static String connstring="jdbc:mysql://localhost";

	public DB () {
		conn=null;

		try { // 1) LOAD DRIVER
			Class.forName("org.gjt.mm.mysql.Driver"); 
		}
		catch (Exception ee) {
			System.out.println("Problem loading driver: org.gjt.mm.mysql.Driver");
			ee.printStackTrace();
			System.exit(-1);
		}
		System.out.println("loaded driver ok");

		try { // 2) ESTABLISH CONNECTION
			conn = DriverManager.getConnection(connstring,username,password);
		}
		catch (SQLException se) {
			System.out.println("Problem establishing connection "+connstring);
			se.printStackTrace();
			System.exit(-1);

		}
		System.out.println("Established Connection OK");
	}
    public String getNumber(int optnum, String optval) {
	if (optval == "50") optval="66";
	try {
	    Statement stmt = conn.createStatement();
	    
	    String query = "select * from questionoption where refID=2 and qID="+optnum+
		" and percent= "+optval;
	    //System.out.println(query);
	    ResultSet rs2 = stmt.executeQuery(query);
	    while (rs2.next()) {
		int optionnumber = rs2.getInt("optionnum");
		return ""+optionnumber;
	    }
	    
	    
	}
	catch (Exception ee) {
	    ee.printStackTrace();
	}
	return "N";
	
	
    }


    public void close() {
	try {conn.close();}
	catch (Exception ee) {
	    
	}

    }






}