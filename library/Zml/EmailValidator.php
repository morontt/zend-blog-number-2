<?php

class Zml_EmailValidator extends Zend_Validate_EmailAddress
{
    public function isValid($value)
    {
        if (parent::isValid($value)) {
            $pathToSwiftmailer = realpath(APPLICATION_PATH . '/../library/Swiftmailer/lib/swift_required.php');
            require_once($pathToSwiftmailer);

            $swiftGrammarObject = new Swift_Mime_Grammar();
            $addressDefinition  = $swiftGrammarObject->getDefinition('addr-spec');

            if (!preg_match('/^' . $addressDefinition . '$/D', $value)) {
                $this->_error(self::INVALID);
                return false;
            }

            return true;
        } else {
            $this->_error(self::INVALID);
            return false;
        }
    }
}
