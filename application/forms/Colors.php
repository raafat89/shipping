<?php

class Atlas_Form_Colors extends Zend_Form {

    public function init() {
        $this->setDisableLoadDefaultDecorators(true);

        // ** HIDDEN FIELD FOR ID ***********************/
        $field = new Zend_Form_Element_Hidden("id");
        $field->setDecorators(array("ViewHelper"));
        $this->addElement($field);

        // ** COLOR TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Text("color");
        $field->setRequired(true)
                ->setDecorators(array("ViewHelper"))
                ->setFilters(array("StringTrim", "StripTags"))
                ->setValidators(array(
                    array("NotEmpty", true),
                    array("StringLength", false, array(1, 50))
                ))
                ->setAttribs(array(
                    "maxlength" => "50",
                    "class" => "form-element",
                    "size" => "30"
        ));
        $this->addElement($field);

        // ** SUBMIT BUTTON *************************************/
        $submit = new Zend_Form_Element_Submit("submit", "Save");
        $submit->setAttrib("class", "submit");
        $submit->setDecorators(array("ViewHelper"));
        $this->addElement($submit);
    }

}

?>