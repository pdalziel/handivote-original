package handivote;


public class CardGenerator {

	public static void main(String[] args) {
		CardGenGUI gui = new CardGenGUI();
		CardGenModel model = new CardGenModel();
		@SuppressWarnings("unused")
		GUIController controller = new GUIController(model,gui);
		gui.setVisible(true);
	}

}
