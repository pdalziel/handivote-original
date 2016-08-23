package handivote;

import java.security.SecureRandom;
import java.util.ArrayList;
import java.io.BufferedWriter;
import java.io.File;
import java.io.FileWriter;
import java.io.IOException;


public class CardGenModel {
	int numberOfCards;
	int idStart;
	int idDigits;
	int pinDigits;
	
	boolean alphaNumID;
	boolean startID;
	
	SecureRandom random = new SecureRandom();
	ArrayList<IDPair> results = new ArrayList<IDPair>();

	public CardGenModel(){
	}

	public CardGenModel(int numberOfCards, int idStart, int idDigits) {
		super();
		this.numberOfCards = numberOfCards;
		this.idStart = idStart;
		this.idDigits = idDigits;
	}

	public int getNumberOfCards() {
		return numberOfCards;
	}

	public void setNumberOfCards(int numberOfCards) {
		this.numberOfCards = numberOfCards;
	}

	public int getIdStart() {
		return idStart;
	}

	public void setIdStart(int idStart) {
		this.idStart = idStart;
	}

	public int getIdDigits() {
		return idDigits;
	}

	public void setIdDigits(int idDigits) {
		this.idDigits = idDigits;
	}

	public int getPinDigits() {
		return pinDigits;
	}

	public void setPinDigits(int pinDigits) {
		this.pinDigits = pinDigits;
	}

	public boolean isAlphaNumID() {
		return alphaNumID;
	}

	public void setAlphaNumID(boolean alphaNum) {
		this.alphaNumID = alphaNum;
	}


	public boolean isStartID() {
		return startID;
	}

	public void setStartID(boolean startID) {
		this.startID = startID;
	}

	public ArrayList<IDPair> getResults() {
		return results;
	}

	public void generate(){		
		int id;
		if(startID){
			id = idStart;
		}
		else{
			id = (int)Math.pow(10, idDigits);
		}
		
		int startNum = (int) Math.pow(10, pinDigits-1);
		int range = (int) (Math.pow(10, pinDigits) - startNum + 1);
		
		char[] alphid = new char[idDigits];
		for(int i = 0; i < alphid.length; i++){
			alphid[i] = 'A';
		}
		
		for(int i=0;i < numberOfCards;i++){
			int pin = random.nextInt(range) + startNum;
			if(alphaNumID){
				results.add(new IDPair(Alphanum.toAlphaNum(alphid),Integer.toString(pin)));
				Alphanum.increment(alphid);
			}
			else{
				results.add(new IDPair(id,pin));
				id++;
			}
		}
	}

	public void writeToFile(){
		try{

			File file = new File("./idlist.txt");
			if (!file.exists()) {
				file.createNewFile();
			}
			FileWriter fw = new FileWriter(file.getAbsoluteFile());
			BufferedWriter bw = new BufferedWriter(fw);
			for(IDPair p:results){
				bw.write(p.toString() + "\n");
			}
			bw.close();
		}catch(IOException e){
			e.printStackTrace();
		}
	}
}
