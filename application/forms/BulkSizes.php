<?php

class Atlas_Form_BulkSizes extends Zend_Form {

    public function init() {
        /* FORM META DATA ************************* */
        $this->setDisableLoadDefaultDecorators(true);
        $this->addDecorator('FormElements');
        $this->addDecorator('Form');

        /* HIDDEN FORM FIELDS *************************** */
        $bulk_size_id = new Zend_Form_Element_Hidden("bulk_size_id");
        $bulk_size_id->setDecorators(array('ViewHelper'));

        /* TEXT FIELD FOR UNIT LABEL ************ */
        $bulk_size = new Zend_Form_Element_Text("bulk_size");
        $bulk_size->setRequired(true)
                ->setDecorators(array('ViewHelper'))
                ->setFilters(array('StringTrim', 'StripTags'))
                ->setValidators(array(
                    array('NotEmpty', true),
                    array('StringLength', false, array(1, 35))
                ))
                ->setAttribs(array(
                    'maxlength' => '35',
                    'class' => 'form-element',
                    'size' => '50'
        ));

        /* SUBMIT BUTTON **************************************** */
        $submit = new Zend_Form_Element_Submit('submit', 'Submit');
        $submit->setAttrib('class', 'form-element')
                ->setDecorators(array('ViewHelper'));

        /* ADD ELEMENTS TO FORM ********* */
        $this->addElements(array($bulk_size_id, $bulk_size, $submit));
    }

}

?>