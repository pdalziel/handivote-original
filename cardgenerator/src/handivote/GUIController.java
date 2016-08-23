package handivote;

import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;
import java.awt.event.ItemEvent;
import java.awt.event.ItemListener;

public class GUIController {
	private CardGenModel model;
	private CardGenGUI gui;

	public GUIController(CardGenModel model, CardGenGUI gui) {
		this.model = model;
		this.gui = gui;
		this.gui.addGenerateListener(new GenerateListener());
		this.gui.addCheckboxListener(new IDcheckboxListener());
		this.gui.addChooseStartListener(new chooseStartListener());
	}

	class GenerateListener implements ActionListener {

		@Override
		public void actionPerformed(ActionEvent event) {
			try {
				model.setNumberOfCards(gui.getNumberOfCards());
				model.setIdDigits(gui.getIdDigits());
				model.setPinDigits(gui.getPinDigits());
				if (gui.isStartIDChosen()) {
					model.setIdStart(gui.getIdStart());
					if (Integer.toString(model.getIdStart()).length() != model
							.getIdDigits()) {
						throw new StartIdException();
					}
					if(model.getNumberOfCards() > (Math.pow(10, model.idDigits) - model.idStart)){
						throw new IdRangeException();
					}
				}

				if (model.alphaNumID) {
					if (model.getNumberOfCards() > Math.pow(36, model.idDigits)) {
						throw new IdRangeException();
					}
				} else {
					if (model.getNumberOfCards() > Math.pow(10, model.idDigits)) {
						throw new IdRangeException();
					}
				}

				model.generate();
				model.writeToFile();
				gui.showDialog();

			} catch (NumberFormatException e) {
				gui.inputError();
			} catch (IdRangeException e) {
				gui.IdRangeError();
			} catch (StartIdException e) {
				gui.startIdError();
			}
		}

	}

	class IdRangeException extends Exception {
	}

	class StartIdException extends Exception {
	}

	class IDcheckboxListener implements ItemListener {

		@Override
		public void itemStateChanged(ItemEvent e) {
			if (e.getStateChange() == ItemEvent.SELECTED) {
				model.setAlphaNumID(true);
				gui.chooseStartDisable();
			} else if (e.getStateChange() == ItemEvent.DESELECTED) {
				model.setAlphaNumID(false);
				gui.chooseStartEnable();
			}
		}

	}

	class chooseStartListener implements ItemListener {

		@Override
		public void itemStateChanged(ItemEvent e) {
			if (e.getStateChange() == ItemEvent.SELECTED) {
				gui.idStartEnable();
				model.setStartID(true);
			} else if (e.getStateChange() == ItemEvent.DESELECTED) {
				gui.idStartDisable();
				model.setStartID(false);
			}
		}

	}

}
