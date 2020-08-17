<?php

class Atlas_Form_GeneralSearch extends Zend_Form {

    protected $_user_id;

    public function __construct($user_id = 0) {
        $this->_user_id = $user_id;
        parent::__construct();
    }

    public function init() {
        /* FORM META DATA ************************* */
        $this->setDisableLoadDefaultDecorators(true);
        $this->addDecorator('FormElements');
        $this->addDecorator('Form');

        /* SEARCH FIELD TEXT LINE ************************ */
        $search_field = new Zend_Form_Element_Text("field");
        $search_field->setLabel("Search:")
                ->setRequired(true)
                ->setDecorators(array('ViewHelper'))
                ->setFilters(array('StringTrim', 'StripTags'))
                ->setValidators(array(
                    array('NotEmpty', true),
                    array('StringLength', false, array(1, 35))
                ))
                ->setAttribs(array(
                    'maxlength' => '35',
                    'class' => 'form-element',
                    'size' => '30'
        ));

        /* DATE FIELD TEXT LINE ************************ */
        $date_field = new Zend_Form_Element_Text("date");
        $date_field->setLabel("Date:")
                ->setRequired(true)
                ->setDecorators(array('ViewHelper'))
                ->setFilters(array('StringTrim', 'StripTags'))
                ->setValidators(array(
                    array('NotEmpty', true),
                    array('StringLength', false, array(1, 35))
                ))
                ->setAttribs(array(
                    'maxlength' => '35',
                    'class' => 'form-element',
                    'size' => '30'
        ));

        /* SUBMIT BUTTON **************************************** */
        $submit = new Zend_Form_Element_Submit('search', 'Search');
        $submit->setAttrib('class', 'form-element')
                ->setDecorators(array('ViewHelper'));

        /* ADD ELEMENTS TO FORM ************************************** */
        $this->addElements(array($search_field, $date_field, $submit));
    }

}

?>