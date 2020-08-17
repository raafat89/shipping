<?php

class Atlas_Form_CaptabPackages extends Zend_Form {

    public function init() {
        $this->setDisableLoadDefaultDecorators(true);

        //Initialize Dropdown values UNITS/COLORS/BULK_TYPES
        $units_mapper = new Atlas_Model_UnitsMapper();
        $unit_values = $units_mapper->fetch($units_mapper->selectAll());
        $colors_mapper = new Atlas_Model_ColorsMapper();
        $color_values = $colors_mapper->buildColors();
        $bulk_types_mapper = new Atlas_Model_BulkTypesMapper();
        $bulk_types_values = $bulk_types_mapper->fetch($bulk_types_mapper->selectAll());

        // ** HIDDEN FIELD FOR ID ***********************/
        $field = new Zend_Form_Element_Hidden("id");
        $field->setDecorators(array("ViewHelper"));
        $this->addElement($field);

        // ** NAME TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Text("name");
        $field->setRequired(false)
                ->setDecorators(array("ViewHelper"))
                ->setFilters(array("StringTrim", "StripTags"))
                ->setAttribs(array(
                    "maxlength" => "50",
                    "class" => "form-element",
                    "size" => "30"
        ));
        $this->addElement($field);

        // ** CODE TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Text("code");
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
        // ** CAP_TAB_TYPE TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Select("captab_type");
        $field->setRequired(true)
                ->setDecorators(array('ViewHelper'))
                ->setValidators(array(array('StringLength', false, array(1, 35)), array('NotEmpty', true)))
                ->setAttribs(array('class' => 'form-element'));
        $field->addMultiOption('', '- SELECT TYPE -');
        foreach ($bulk_types_values as $value) {
            $field->addMultiOption($value['bulk_type'], html_entity_decode($value['bulk_type']));
        }
        $this->addElement($field);

        // ** CAP_TAB_COLOR TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Select("captab_color");
        $field->setRequired(false)
                ->setDecorators(array('ViewHelper'))
                ->setValidators(array(array('StringLength', false, array(1, 35)), array('NotEmpty', true)))
                ->setAttribs(array('class' => 'form-element'));
        $field->addMultiOption('', '- SELECT COLOR -');
        foreach ($color_values as $value) {
            $field->addMultiOption($value['color'], $value['color']);
        }
        $this->addElement($field);

        // ** CAP_TAB_WEIGHT TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Select("captab_weight");
        $field->setRequired(true)
                ->setDecorators(array('ViewHelper'))
                ->setValidators(array(array('StringLength', false, array(1, 35)), array('NotEmpty', true)))
                ->setAttribs(array('class' => 'form-element'));
        $field->addMultiOption('1', 'YES');
        $field->addMultiOption('0', 'NO');

        $this->addElement($field);

        // ** CAP_TAB_WEIGHT TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Select("captab_weight_unit");
        $field->setRequired(false)
                ->setDecorators(array('ViewHelper'))
                ->setValidators(array(array('NotEmpty', true)))
                ->setAttribs(array('class' => 'form-element'));
        $field->addMultiOption('', '- SELECT -');
        foreach ($unit_values as $value) {
            $field->addMultiOption($value['unit'], html_entity_decode($value['unit']));
        }
        $this->addElement($field);

        // ** CAPSULE_SELL_WEIGHT TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Text("capsule_shell_weight");
        $field->setRequired(false)
                ->setDecorators(array("ViewHelper"))
                ->setFilters(array("StringTrim", "StripTags"))
                ->setValidators(array(
                    array("NotEmpty", true),
                    array("StringLength", false, array(1, 50))
                ))
                ->setAttribs(array(
                    "maxlength" => "50",
                    "class" => "form-element",
                    "size" => "5"
        ));
        $this->addElement($field);

        // ** CAPSULE_SELL_WEIGHT TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Select("capsule_shell_weight_unit");
        $field->setRequired(false)
                ->setDecorators(array('ViewHelper'))
                ->setValidators(array(array('StringLength', false, array(1, 35)), array('NotEmpty', true)))
                ->setAttribs(array('class' => 'form-element'));
        $field->addMultiOption('', '- SELECT -');
        foreach ($unit_values as $value) {
            $field->addMultiOption($value['unit'], html_entity_decode($value['unit']));
        }
        $this->addElement($field);

        // ** ACTIVE TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Select("active");
        $field->setRequired(true)
                ->setDecorators(array('ViewHelper'))
                ->setValidators(array(array('StringLength', false, array(1, 35)), array('NotEmpty', true)))
                ->setAttribs(array('class' => 'form-element'));
        $field->addMultiOption('1', 'YES');
        $field->addMultiOption('0', 'NO');

        $this->addElement($field);

        // ** VEGAN FIELD ******************************/
        $field = new Zend_Form_Element_Select("vegan");
        $field->setRequired(true)
                ->setDecorators(array('ViewHelper'))
                ->setValidators(array(array('StringLength', false, array(1, 35)), array('NotEmpty', true)))
                ->setAttribs(array('class' => 'form-element'));

        $field->addMultiOption('0', 'NO');
        $field->addMultiOption('1', 'YES');
        $this->addElement($field);

        // ** VEGAN FIELD ******************************/
        $field = new Zend_Form_Element_Select("gelatin_capsule");
        $field->setRequired(true)
                ->setDecorators(array('ViewHelper'))
                ->setValidators(array(array('StringLength', false, array(1, 35)), array('NotEmpty', true)))
                ->setAttribs(array('class' => 'form-element'));

        $field->addMultiOption('0', 'NO');
        $field->addMultiOption('1', 'YES');
        $this->addElement($field);
        // ** SUBMIT BUTTON *************************************/
        $submit = new Zend_Form_Element_Submit("submit", "Save");
        $submit->setAttrib("class", "submit");
        $submit->setDecorators(array("ViewHelper"));
        $this->addElement($submit);
    }

}

?>