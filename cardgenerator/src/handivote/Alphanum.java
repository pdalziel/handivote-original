package handivote;

public class Alphanum {

	
	public static void increment(char[] s){
	    for(int pos = s.length - 1; pos >= 0; pos--){
	    	if(s[pos] != ('Z' + 10)){
	            s[pos]++;
	            break;
	    	}
	    	else{
	    		s[pos] = 'A';
	    	}
	    }
	}
	
	public static String toAlphaNum(char[] s){
		char[] sequence = s.clone();
		for(int i = 0; i < sequence.length;i++){
			if(sequence[i] > 'Z'){
				sequence[i] -= 43;
			}
		}
		String rString = new String(sequence);
		return rString;
	}

}
