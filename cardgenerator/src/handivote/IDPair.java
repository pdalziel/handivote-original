package handivote;

public class IDPair {
	private final String id;
	private final String pin;
	
	public IDPair(String id, String pin) {
		super();
		this.id = id;
		this.pin = pin;
	}
	
	public IDPair(int id,int pin){
		super();
		this.id = Integer.toString(id);
		this.pin = Integer.toString(pin);
	}

	public String getId() {
		return id;
	}

	public String getPin() {
		return pin;
	}

	@Override
	public String toString() {
		return id + " " + pin;
	}
	
	

	
	
}
