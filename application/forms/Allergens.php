<?php

class Atlas_Form_Allergens extends Zend_Form {

    public function init() {
        $this->setDisableLoadDefaultDecorators(true);

        // ** HIDDEN FIELD FOR ALLERGENS_ID ***********************/
        $field = new Zend_Form_Element_Hidden("allergens_id");
        $field->setDecorators(array("ViewHelper"));
        $this->addElement($field);

        // ** ALLERGENS_ITEM TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Text("allergens_item");
        $field->setRequired(true)
                ->setDecorators(array("ViewHelper"))
                ->setFilters(array("StringTrim", "StripTags"))
                ->setValidators(array(
                    array("NotEmpty", true),
                    array("StringLength", false, array(1, 50))
                ))
                ->setAttribs(array(
                    "maxlength" => "30",
                    "class" => "form-element",
                    "size" => "15"
        ));
        $this->addElement($field);

        // ** ALLERGENS_DATA TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Text("allergens_data");
        $field->setRequired(false)
                ->setDecorators(array("ViewHelper"))
                ->setFilters(array("StringTrim", "StripTags"))
                ->setAttribs(array(
                    "maxlength" => "255",
                    "class" => "form-element",
                    "size" => "100"
        ));
        $this->addElement($field);


        // ** ORGANIC_COOLER TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Checkbox("allergens_organic");
        $field->setRequired(false)
                ->setDecorators(array('ViewHelper'))
                ->setFilters(array('StringTrim'))
                ->setValue(0);
        $this->addElement($field);

        // ** ALLERGENS_COOLER TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Checkbox("allergens_cooler");
        $field->setRequired(false)
                ->setDecorators(array('ViewHelper'))
                ->setFilters(array('StringTrim'))
                ->setValue(0);
        $this->addElement($field);

        // ** SUBMIT BUTTON *************************************/
        $submit = new Zend_Form_Element_Submit("submit", "Save");
        $submit->setAttrib("class", "submit");
        $submit->setDecorators(array("ViewHelper"));
        $this->addElement($submit);
    }

}

?>