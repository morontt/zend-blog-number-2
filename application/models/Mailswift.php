<?php

class Application_Model_Mailswift
{

    public function sendEmailToSpool($recipientEmail, $recipientName, $subject, $mailBody)
    {
        $options = Zend_Registry::get('options');

        //swift mailer
        $pathToSwiftmailer = realpath(APPLICATION_PATH . '/../library/Swiftmailer/lib/swift_required.php');
        require_once($pathToSwiftmailer);

        $message = Swift_Message::newInstance();
        $message->setFrom(array($options['mail_smtp']['from'] => '[' . $options['blog']['title'] . ']'))
                ->setContentType('text/html');

        //create file spool
        $spoolPath = realpath(APPLICATION_PATH . '/../spool');
        $spool     = new Swift_FileSpool($spoolPath);

        $emailValidatorObject = new Zml_EmailValidator();
        if ($emailValidatorObject->isValid($recipientEmail)) {

            $message->setSubject($subject)
                    ->setTo(array($recipientEmail => $recipientName))
                    ->setBody($mailBody);

            //Queues a message
            $spool->queueMessage($message);
        }
    }

}

