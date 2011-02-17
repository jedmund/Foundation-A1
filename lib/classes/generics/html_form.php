<?php

/**
 * HTML_Form.php
 *
 * This class helps create HTML Forms easily.
 * Version Date: March 2010
 * Source and documentation at http://www.dyn-web.com/code/form_builder/
 *
 */

class HTML_Form {

  public static $MONTHS_LONG = array('January', 'February', 'March', 'April', 'May', 'June', 'July',
      'August', 'September', 'October', 'November', 'December');

	/** 
	 * Starts the form by opening the <form> tag and setting its attributes.
	 *
	 * @param				$action				Destination URL to which form is submitted
	 * @param				$method				Form method - get or post (default is post)
	 * @param				$id						Unique id to be assigned to the form element's
	 *														id attribute.
	 * @param				$attr_ar			Associative array of additional attributes.
	 * @return			$str					The string containing the form element.
	 *
	 */
  public function startForm($action = '#', $method = 'post', $id = NULL, $attr_ar = array()) {
    $str = "<form action=\"$action\" method=\"$method\"";
    if ( isset($id) ) {
        $str .= " id=\"$id\"";
    }
    $str .= $attr_ar? $this->addAttributes($attr_ar) . '>': '>';
    return $str;
  }

	/**
	 * Helper method that adds optional attributes passed in via 
	 * associative array, $attr_ar.
	 *
	 * @param				$attr_ar			Associative array of additional attributes.
	 * @return			$str					The string containing the attributes and their
	 *														values.
	 *
	 */
  private function addAttributes($attr_ar) {
		$str = '';
		
		// check minimized attributes
		$min_atts = array('checked', 'disabled', 'readonly', 'multiple');
		
		foreach($attr_ar as $key=>$val) {
			if (in_array($key, $min_atts)) {
				if (!empty($val)) {
					$str .= " $key=\"$key\"";
				}
			} else {
				$str .= " $key=\"$val\"";
			}
		}
		return $str;
  }

	/**
	 * Adds input elements of type text, checkbox, radio, hidden,
	 * password, submit, and image.
	 *
 	 * @param				$type					Input element's type attribute value. 
 	 *														Possible values: text, checkbox, radio, 
 	 *														hidden, password, submit and image.
	 * @param				$name					The value to assign the name attribute
	 *														of the element.
	 * @param				$value				The default value of the element.
	 * @param				$attr_ar			Associative array of additional attributes.
	 * @return			$str					The string containing the input element.
	 *
	 */
  public function addInput($type, $name, $value, $attr_ar = array()) {
    $str = "<input type=\"$type\" name=\"$name\" value=\"$value\"";
    
    if ($attr_ar) {
			$str .= $this->addAttributes( $attr_ar );
    }
    
    $str .= ' />';
    return $str;
  }

	/**
	 * Adds a textarea element.
	 * Default values are provided for rows (4) and columns (30)
	 *
	 * @param				$name					The value to assign the name attribute
	 *														of the element.
	 * @param				$rows					The number of rows in the textarea.
	 * @param				$cols					The number of columns in the textarea.
	 * @param				$value 				The default value of the element.
	 * @param				$attr_ar			Associative array of additional attributes.
	 * @return			$str					The string containing the textarea element.
	 *
	 */
  public function addTextarea($name, $rows = 4, $cols = 30, $value = '', $attr_ar = array()) {
    $str = "<textarea name=\"$name\" rows=\"$rows\" cols=\"$cols\"";
    
    if ($attr_ar) {
			$str .= $this->addAttributes($attr_ar);
    }
    
    $str .= ">$value</textarea>";
    return $str;
  }

  /**
   * Adds a label element. Label elements correspond to the id attribute
   * of an element.
   *
   * @param				$forID				The ID of the element the label corresponds to.
   * @param				$text					The label's text.
   * @param				$attr_ar			Associative array of additional attributes.
   * @param				$str					The string containing the label element.
   *
   */
  public function addLabelFor($forID, $text, $attr_ar = array()) {
		$str = "<label for=\"$forID\"";
		
		if ($attr_ar) {
			$str .= $this->addAttributes($attr_ar);
		}
		
		$str .= ">$text</label>";
		return $str;
  }

  /**
   * Generates a select list element from parallel arrays for option
   * values and text.
   *
   * @param				$name					The value to assign the name attribute
	 *														of the element.
	 * @param				$val_list			The array containing option values.
	 * @param				$txt_list			The array containing option text.
	 * @param				$sel_value		The default selected value.
	 * @param				$header 			Header selected in a list with an empty value.
	 * @param				$attr_ar			Associative array of additional attributes.
   * @return			$str					The string containing the select list element.
   *
   */
  public function addSelectListArrays($name, $val_list, $txt_list, $sel_value = NULL, $header = NULL, $attr_ar = array()) {
		$opt_list = array_combine($val_list, $txt_list);
		$str = $this->addSelectList($name, $opt_list, true, $sel_value, $header, $attr_ar);
		return $str;
  }

  // $bVal false if text serves as value (no value attr)
	/**
   * Generates a select list element from a single array, that can be 
   * associative.
   *
   * @param				$name					The value to assign the name attribute
	 *														of the element.
	 * @param				$opt_list			The array containing option text, and
	 *														optionally, values.
	 * @param				$bVal					Boolean set to false if the text serves as value.
	 *														(no value attribute)
	 * @param				$sel_value		The default selected value.
	 * @param				$header 			Header selected in a list with an empty value.
	 * @param				$attr_ar			Associative array of additional attributes.
   * @return			$str					The string containing the select list element.
   *
   */
  public function addSelectList($name, $opt_list, $bVal = true, $sel_value = NULL, $header = NULL, $attr_ar = array()) {
		$str = "<select name=\"$name\"";
		
		if ($attr_ar) {
			$str .= $this->addAttributes($attr_ar);
		}
		
		$str .= ">\n";
		
		if (isset($header)) {
			$str .= "  <option value=\"\">$header</option>\n";
		}
		
		foreach ($opt_list as $val => $text) {
			$str .= $bVal? "  <option value=\"$val\"": "  <option";	
			if (isset($sel_value) && ($sel_value === $val || $sel_value === $text)) {
				$str .= ' selected="selected"';
			}
			$str .= ">$text</option>\n";
		}
		
		$str .= "</select>";
		return $str;
  }

	/** 
	 * Ends the form element.
	 *
	 * @return			string				The end form tag.
	 *
	 */
  public function endForm() {
		return "</form>";
  }

}

?>