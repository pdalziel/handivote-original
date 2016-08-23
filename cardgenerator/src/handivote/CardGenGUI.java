package handivote;


import handivote.GUIController.IDcheckboxListener;

import java.awt.GridBagConstraints;
import java.awt.GridBagLayout;
import java.awt.event.ActionListener;
import java.awt.event.ItemListener;

import javax.swing.JButton;
import javax.swing.JCheckBox;
import javax.swing.JFrame;
import javax.swing.JLabel;
import javax.swing.JOptionPane;
import javax.swing.JPanel;
import javax.swing.JTextField;

@SuppressWarnings("serial")
public class CardGenGUI extends JFrame{
	private JLabel numCardLabel = new JLabel("Number of cards:");
	private JTextField numCardField = new JTextField(10);
	
	private JLabel idDigitsLabel = new JLabel("Number of digits (ID):");
	private JTextField idDigitsField = new JTextField(10);
	
	private JLabel pinDigitsLabel = new JLabel("Number of digits (PIN):");
	private JTextField pinDigitsField = new JTextField(10);
	
	private JLabel idStartLabel = new JLabel("Start ID:");
	private JTextField idStartField = new JTextField(10);
	private JButton genButton = new JButton("Generate ID/PIN list");
	
	private JLabel alphaNumIDLabel = new JLabel("Alphanumeric IDs:");
	private JCheckBox alphaNumIDCheckBox = new JCheckBox();
	
	private JLabel chooseStartLabel = new JLabel("Choose Start ID");
	private JCheckBox chooseStartCheckBox = new JCheckBox();
	
	
	CardGenGUI(){
		this.setTitle("Handivote Card Generator");
		this.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
		this.setSize(350, 200);
		
		JPanel panel = new JPanel(new GridBagLayout());
		GridBagConstraints c = new GridBagConstraints();
		
		c.gridx = 0;
		c.gridy = 0;
		panel.add(numCardLabel,c);
		c.gridx = 1;
		c.gridy = 0;
		panel.add(numCardField,c);
		c.gridx = 0;
		c.gridy = 1;
		panel.add(idDigitsLabel,c);
		c.gridx = 1;
		c.gridy = 1;
		panel.add(idDigitsField,c);
		c.gridx = 0;
		c.gridy = 2;
		panel.add(pinDigitsLabel,c);
		c.gridx = 1;
		c.gridy = 2;
		panel.add(pinDigitsField,c);
		c.gridx = 0;
		c.gridy = 3;
		panel.add(alphaNumIDLabel,c);
		c.gridx = 1;
		c.gridy = 3;
		panel.add(alphaNumIDCheckBox,c);
		c.gridx = 0;
		c.gridy = 4;
		panel.add(chooseStartLabel,c);
		c.gridx = 1;
		c.gridy = 4;
		panel.add(chooseStartCheckBox,c);
		c.gridx = 0;
		c.gridy = 5;
		panel.add(idStartLabel,c);
		c.gridx = 1;
		c.gridy = 5;
		panel.add(idStartField,c);
		c.gridx = 1;
		c.gridy = 6;
		panel.add(genButton,c);
		this.add(panel);
		
		idStartDisable();
		
	}
	
	public int getNumberOfCards(){
		return Integer.parseInt(numCardField.getText());
	}
	
	public int getIdDigits(){
		return Integer.parseInt(idDigitsField.getText());
	}
	
	public boolean isStartIDChosen(){
		if(chooseStartCheckBox.isSelected()){
			return true;
		}
		else{
			return false;
		}
	}
	
	public int getPinDigits(){
		return Integer.parseInt(pinDigitsField.getText());
	}
	
	public int getIdStart(){
		return Integer.parseInt(idStartField.getText());
	}
	
	public void showDialog(){
		JOptionPane.showMessageDialog ( null, "Output file written to idlist.txt" ); 
	}
	
	public void inputError(){
		JOptionPane.showMessageDialog ( null, "Error parsing input" ); 
	}
	
	public void IdRangeError(){
		JOptionPane.showMessageDialog ( null, "Out of range - increase ID digits" ); 
	}
	
	public void startIdError(){
		JOptionPane.showMessageDialog ( null, "Start ID has wrong number of digits" ); 
	}
	
	public void idStartEnable(){
		idStartField.setEnabled(true);
		idStartLabel.setEnabled(true);
	}
	
	public void idStartDisable(){
		idStartField.setEnabled(false);
		idStartLabel.setEnabled(false);
	}
	
	public void chooseStartEnable(){
		chooseStartCheckBox.setEnabled(true);
		chooseStartLabel.setEnabled(true);

	}
	
	public void chooseStartDisable(){
		chooseStartCheckBox.setEnabled(false);
		chooseStartLabel.setEnabled(false);
	}
	
	void addGenerateListener(ActionListener genListener){
		genButton.addActionListener(genListener);
	}
	
	void addCheckboxListener(ItemListener checkboxListener){
		alphaNumIDCheckBox.addItemListener(checkboxListener);
	}
	
	void addChooseStartListener(ItemListener chooseStartListener){
		chooseStartCheckBox.addItemListener(chooseStartListener);
	}
}
