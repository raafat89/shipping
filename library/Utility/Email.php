<?php

class Utility_Email {

    protected $_domain;
    protected $_mail;
    protected $_to;
    protected $_cc;
    protected $_bcc;

    public function __construct() {
        require_once(Zend_Registry::get("root_path") . "/library/PHPMailer_2/PHPMailerAutoload.php");
        $settings = Zend_Registry::get("mail");
        $params = $settings->mail->params;
        $this->_domain = $params->domain;
        $this->_mail = new phpmailer();
        $this->_mail->isSMTP();
        $this->_mail->Host = $params->Host;
        $this->_mail->Username = $params->Username;
        $this->_mail->Password = $params->Password;
        $this->_mail->From = $params->From;
        $this->_mail->FromName = $params->FromName;
        $this->_mail->Sender = $params->Sender;
        $this->_mail->WordWrap = $params->WordWrap;
        $this->_mail->CharSet = $params->CharSet;
        $this->_mail->Encoding = $params->Encoding;
        $this->_mail->AltBody = $params->AltBody;
        $this->_mail->SMTPDebug = $params->SMTPDebug;
        $this->_mail->Debugoutput = $params->Debugoutput;
        //$this->_mail->SMTPAuth      = false;
        $this->_mail->SMTPAutoTLS = false;
        //$this->_mail->Port          = 25; 
    }

    public function setReply($reply_to) {
        $this->_mail->From = $reply_to['email'];
        $this->_mail->FromName = $reply_to['name'];
        $this->_mail->Sender = $reply_to['email'];
        return $this;
    }

    public function setTo($to_array) {
        if (strtolower(APPLICATION_ENV) == "production") {
            foreach ($to_array as $to) {
                $this->_to[] = $to;
                $this->_mail->AddAddress($to['email']);
            }
        } else {
            $this->_to[] = array("name" => "Primary", "email" => "primary@jarrow.com");
            $this->_mail->AddAddress("primary@jarrow.com");
        }
        return $this;
    }

    public function setAtt($to_array) {
        if (strtolower(APPLICATION_ENV) == "production") {
            foreach ($to_array as $too) {
                $this->_too[] = $too;
            }
            return $this;
        }
    }

    public function setCc($cc_array) {
        if (strtolower(APPLICATION_ENV) == "production") {
            foreach ($cc_array as $cc) {
                $this->_cc[] = $cc;
                $this->_mail->AddCC($cc['email']);
            }
        } else {
            $this->_cc[] = array("name" => "Primary", "email" => "primary@jarrow.com");
            $this->_mail->AddCC("primary@jarrow.com");
        }
        return $this;
    }

    public function setBcc($bcc_array) {
        $this->_bcc = $bcc_array;
        if (strtolower(APPLICATION_ENV) == "production") {
            foreach ($bcc_array as $bcc) {
                $this->_bcc[] = $bcc;
                $this->_mail->AddBCC($bcc['email']);
            }
        } else {
            $this->_bcc[] = array("name" => "Primary", "email" => "primary@jarrow.com");
            $this->_mail->AddBCC("primary@jarrow.com");
        }
        return $this;
    }

    public function setSubject($subject) {
        $this->_mail->Subject = $subject;
        return $this;
    }

    public function setFrom($fromname) {
        $this->_mail->FromName = $fromname;
        return $this;
    }

    public function setBody($body) {
        $this->_mail->IsHTML(false);
        $this->_mail->Body = $body;
        return $this;
    }

    public function setHTML($body) {
        $this->_mail->IsHTML(true);
        $this->_mail->Body = $body;
        return $this;
    }

    public function setIcal($data) {
        $this->_mail->addCustomHeader('MIME-version', "1.0");
        $this->_mail->addCustomHeader('Content-type', "text/calendar; method=REQUEST; charset=UTF-8;");
        $this->_mail->addCustomHeader('Content-Transfer-Encoding', "8bit");
        $this->_mail->addCustomHeader('X-Mailer', "Microsoft Office Outlook 11.0");
        $this->_mail->addCustomHeader("Content-class: urn:content-classes:calendarmessage");

        $this->_mail->Body .= "\n\r" . $data['description'];
        $this->_mail->Priority = 1;


        $ical = "BEGIN:VCALENDAR" . "\r\n" .
                "PRODID:-//Microsoft Corporation//Outlook 11.0 MIMEDIR//EN" . "\r\n" .
                "VERSION:2.0" . "\r\n" .
                "METHOD:REQUEST" . "\r\n" .
                "BEGIN:VTIMEZONE" . "\r\n" .
                "TZID:America/Los_Angeles" . "\r\n" .
                "BEGIN:DAYLIGHT" . "\r\n" .
                "TZOFFSETFROM:-0800" . "\r\n" .
                "TZOFFSETTO:-0700" . "\r\n" .
                "TZNAME:PDT" . "\r\n" .
                "DTSTART:19700308T020000" . "\r\n" .
                "RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=2SU" . "\r\n" .
                "END:DAYLIGHT" . "\r\n" .
                "BEGIN:STANDARD" . "\r\n" .
                "TZOFFSETFROM:-0700" . "\r\n" .
                "TZOFFSETTO:-0800" . "\r\n" .
                "TZNAME:PST" . "\r\n" .
                "DTSTART:19701101T020000" . "\r\n" .
                "RRULE:FREQ=YEARLY;BYMONTH=11;BYDAY=1SU" . "\r\n" .
                "END:STANDARD" . "\r\n" .
                "END:VTIMEZONE" . "\r\n" .
                "BEGIN:VEVENT" . "\r\n" .
                "ORGANIZER;CN=\"" . $data['organizer']['name'] . "\":MAILTO:" . $data['organizer']['email'] . "\r\n" .
                "ATTENDEE;CN=\"" . $data['organizer']['name'] . "\";ROLE=REQ-PARTICIPANT;RSVP=TRUE:MAILTO:" . $data['organizer']['email'] . "\r\n";
        foreach ($this->_to as $recipient) {
            $ical .= "ATTENDEE;CN=\"" . $recipient['name'] . "\";ROLE=REQ-PARTICIPANT;RSVP=TRUE:MAILTO:" . $recipient['email'] . "\r\n";
        }
        $ical .= "LAST-MODIFIED:" . date("Ymd\TGis") . "\r\n" .
                "UID:" . date("Ymd\TGis", strtotime($data['start_time'])) . rand() . "@" . $this->_domain . "\r\n" .
                "DTSTAMP:" . date("Ymd\TGis") . "\r\n" .
                "DTSTART;TZID=\"America/Los_Angeles\":" . date("Ymd\THis", strtotime($data['start_time'])) . "\r\n" .
                "DTEND;TZID=\"America/Los_Angeles\":" . date("Ymd\THis", strtotime($data['end_time'])) . "\r\n" .
                "TRANSP:OPAQUE" . "\r\n" .
                "SEQUENCE:1" . "\r\n" .
                "SUMMARY:" . $data['subject'] . "\r\n" .
                "LOCATION:" . $data['location'] . "\r\n" .
                "DESCRIPTION:" . $data['description'] . "\r\n" .
                "CLASS:PUBLIC" . "\r\n" .
                "PRIORITY:3" . "\r\n" .
                "BEGIN:VALARM" . "\r\n" .
                "TRIGGER:-PT15M" . "\r\n" .
                "ACTION:DISPLAY" . "\r\n" .
                "DESCRIPTION:Reminder" . "\r\n" .
                "END:VALARM" . "\r\n" .
                "END:VEVENT" . "\r\n" .
                "END:VCALENDAR" . "\r\n";

        //$this->_mail->AddStringAttachment("$ical", "calendar.ics", "base64", "text/calendar; charset=utf-8; method=REQUEST");
        $this->_mail->Ical = $ical;

        return $this;
    }

    public function setIcalOrg($data) {
        $this->_mail->addCustomHeader('MIME-version', "1.0");
        $this->_mail->addCustomHeader('Content-type', "text/calendar; method=REQUEST; charset=UTF-8;");
        $this->_mail->addCustomHeader('Content-Transfer-Encoding', "8bit");
        $this->_mail->addCustomHeader('X-Mailer', "Microsoft Office Outlook 11.0");
        $this->_mail->addCustomHeader("Content-class: urn:content-classes:calendarmessage");

        $this->_mail->Body .= "\n\r" . $data['description'];
        $this->_mail->Priority = 1;
        $ical = "BEGIN:VCALENDAR" . "\r\n" .
                "PRODID:-//Microsoft Corporation//Outlook 11.0 MIMEDIR//EN" . "\r\n" .
                "VERSION:2.0" . "\r\n" .
                "METHOD:REQUEST" . "\r\n" .
                "BEGIN:VTIMEZONE" . "\r\n" .
                "TZID:America/Los_Angeles" . "\r\n" .
                "BEGIN:DAYLIGHT" . "\r\n" .
                "TZOFFSETFROM:-0800" . "\r\n" .
                "TZOFFSETTO:-0700" . "\r\n" .
                "TZNAME:PDT" . "\r\n" .
                "DTSTART:19700308T020000" . "\r\n" .
                "RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=2SU" . "\r\n" .
                "END:DAYLIGHT" . "\r\n" .
                "BEGIN:STANDARD" . "\r\n" .
                "TZOFFSETFROM:-0700" . "\r\n" .
                "TZOFFSETTO:-0800" . "\r\n" .
                "TZNAME:PST" . "\r\n" .
                "DTSTART:19701101T020000" . "\r\n" .
                "RRULE:FREQ=YEARLY;BYMONTH=11;BYDAY=1SU" . "\r\n" .
                "END:STANDARD" . "\r\n" .
                "END:VTIMEZONE" . "\r\n" .
                "BEGIN:VEVENT" . "\r\n" .
                "ORGANIZER;CN=\"" . Zend_Registry::get("name") . "\":MAILTO:no-reply@jarrow.com\r\n" .
                "ATTENDEE;CN=\"" . Zend_Registry::get("name") . "\";ROLE=REQ-PARTICIPANT;RSVP=TRUE:MAILTO:" . Zend_Registry::get("email") . "\r\n";
        foreach ($this->_too as $recipient1) {
            $ical .= "ATTENDEE;CN=\"" . $recipient1['name'] . "\";ROLE=REQ-PARTICIPANT;RSVP=TRUE:MAILTO:" . $recipient1['email'] . "\r\n";
        }
        $ical .= "LAST-MODIFIED:" . date("Ymd\TGis") . "\r\n" .
                "UID:" . date("Ymd\TGis", strtotime($data['start_time'])) . rand() . "@" . $this->_domain . "\r\n" .
                "DTSTAMP:" . date("Ymd\TGis") . "\r\n" .
                "DTSTART;TZID=\"America/Los_Angeles\":" . date("Ymd\THis", strtotime($data['start_time'])) . "\r\n" .
                "DTEND;TZID=\"America/Los_Angeles\":" . date("Ymd\THis", strtotime($data['end_time'])) . "\r\n" .
                "TRANSP:OPAQUE" . "\r\n" .
                "SEQUENCE:1" . "\r\n" .
                "SUMMARY:" . $data['subject'] . "\r\n" .
                "LOCATION:" . $data['location'] . "\r\n" .
                "DESCRIPTION:" . $data['description'] . "\r\n" .
                "CLASS:PUBLIC" . "\r\n" .
                "PRIORITY:3" . "\r\n" .
                "BEGIN:VALARM" . "\r\n" .
                "TRIGGER:-PT15M" . "\r\n" .
                "ACTION:DISPLAY" . "\r\n" .
                "DESCRIPTION:Reminder" . "\r\n" .
                "END:VALARM" . "\r\n" .
                "END:VEVENT" . "\r\n" .
                "END:VCALENDAR" . "\r\n";



        //$this->_mail->AddStringAttachment("$ical", "calendar.ics", "base64", "text/calendar; charset=utf-8; method=REQUEST");
        $this->_mail->Ical = $ical;
        return $this;
    }

    public function cancelIcal($data) {
        $this->_mail->addCustomHeader('MIME-version', "1.0");
        $this->_mail->addCustomHeader('Content-type', "text/calendar; method=CANCEL; charset=UTF-8;");
        $this->_mail->addCustomHeader('Content-Transfer-Encoding', "8bit");
        $this->_mail->addCustomHeader('X-Mailer', "Microsoft Office Outlook 11.0");
        $this->_mail->addCustomHeader("Content-class: urn:content-classes:calendarmessage");

        $this->_mail->Body .= "\n\r" . $data['description'];
        $this->_mail->Priority = 1;

        $ical = "BEGIN:VCALENDAR" . "\r\n" .
                "PRODID:-//Microsoft Corporation//Outlook 11.0 MIMEDIR//EN" . "\r\n" .
                "VERSION:2.0" . "\r\n" .
                "METHOD:CANCEL" . "\r\n" .
                "BEGIN:VTIMEZONE" . "\r\n" .
                "TZID:America/Los_Angeles" . "\r\n" .
                "BEGIN:DAYLIGHT" . "\r\n" .
                "TZOFFSETFROM:-0800" . "\r\n" .
                "TZOFFSETTO:-0700" . "\r\n" .
                "TZNAME:PDT" . "\r\n" .
                "DTSTART:19700308T020000" . "\r\n" .
                "RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=2SU" . "\r\n" .
                "END:DAYLIGHT" . "\r\n" .
                "BEGIN:STANDARD" . "\r\n" .
                "TZOFFSETFROM:-0700" . "\r\n" .
                "TZOFFSETTO:-0800" . "\r\n" .
                "TZNAME:PST" . "\r\n" .
                "DTSTART:19701101T020000" . "\r\n" .
                "RRULE:FREQ=YEARLY;BYMONTH=11;BYDAY=1SU" . "\r\n" .
                "END:STANDARD" . "\r\n" .
                "END:VTIMEZONE" . "\r\n" .
                "BEGIN:VEVENT" . "\r\n" .
                "ORGANIZER;CN=\"" . $data['organizer']['name'] . "\":MAILTO:" . $data['organizer']['email'] . "\r\n" .
                "ATTENDEE;CN=\"" . $data['organizer']['name'] . "\";ROLE=REQ-PARTICIPANT;RSVP=TRUE:MAILTO:" . $data['organizer']['email'] . "\r\n";
        foreach ($this->_to as $recipient) {
            $ical .= "ATTENDEE;CN=\"" . $recipient['name'] . "\";ROLE=REQ-PARTICIPANT;RSVP=TRUE:MAILTO:" . $recipient['email'] . "\r\n";
        }
        $ical .= "LAST-MODIFIED:" . date("Ymd\TGis") . "\r\n" .
                "UID:" . date("Ymd\TGis", strtotime($data['start_time'])) . rand() . "@" . $this->_domain . "\r\n" .
                "DTSTAMP:" . date("Ymd\TGis") . "\r\n" .
                "DTSTART;TZID=\"America/Los_Angeles\":" . date("Ymd\THis", strtotime($data['start_time'])) . "\r\n" .
                "DTEND;TZID=\"America/Los_Angeles\":" . date("Ymd\THis", strtotime($data['end_time'])) . "\r\n" .
                "TRANSP:OPAQUE" . "\r\n" .
                "SEQUENCE:1" . "\r\n" .
                "STATUS:CANCELLED" . "\r\n" .
                "SUMMARY:" . $data['subject'] . "\r\n" .
                "LOCATION:" . $data['location'] . "\r\n" .
                "DESCRIPTION:" . $data['description'] . "\r\n" .
                "CLASS:PUBLIC" . "\r\n" .
                "PRIORITY:3" . "\r\n" .
                "BEGIN:VALARM" . "\r\n" .
                "TRIGGER:-PT15M" . "\r\n" .
                "ACTION:DISPLAY" . "\r\n" .
                "DESCRIPTION:Reminder" . "\r\n" .
                "END:VALARM" . "\r\n" .
                "END:VEVENT" . "\r\n" .
                "END:VCALENDAR" . "\r\n";

        //$this->_mail->AddStringAttachment("$ical", "calendar.ics", "base64", "text/calendar; charset=utf-8; method=REQUEST");
        $this->_mail->Ical = $ical;

        return $this;
    }

    public function cancelIcalOrg($data) {
        $this->_mail->addCustomHeader('MIME-version', "1.0");
        $this->_mail->addCustomHeader('Content-type', "text/calendar; method=CANCEL; charset=UTF-8;");
        $this->_mail->addCustomHeader('Content-Transfer-Encoding', "8bit");
        $this->_mail->addCustomHeader('X-Mailer', "Microsoft Office Outlook 11.0");
        $this->_mail->addCustomHeader("Content-class: urn:content-classes:calendarmessage");

        $this->_mail->Body .= "\n\r" . $data['description'];
        $this->_mail->Priority = 1;

        $ical = "BEGIN:VCALENDAR" . "\r\n" .
                "PRODID:-//Microsoft Corporation//Outlook 11.0 MIMEDIR//EN" . "\r\n" .
                "VERSION:2.0" . "\r\n" .
                "METHOD:CANCEL" . "\r\n" .
                "BEGIN:VTIMEZONE" . "\r\n" .
                "TZID:America/Los_Angeles" . "\r\n" .
                "BEGIN:DAYLIGHT" . "\r\n" .
                "TZOFFSETFROM:-0800" . "\r\n" .
                "TZOFFSETTO:-0700" . "\r\n" .
                "TZNAME:PDT" . "\r\n" .
                "DTSTART:19700308T020000" . "\r\n" .
                "RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=2SU" . "\r\n" .
                "END:DAYLIGHT" . "\r\n" .
                "BEGIN:STANDARD" . "\r\n" .
                "TZOFFSETFROM:-0700" . "\r\n" .
                "TZOFFSETTO:-0800" . "\r\n" .
                "TZNAME:PST" . "\r\n" .
                "DTSTART:19701101T020000" . "\r\n" .
                "RRULE:FREQ=YEARLY;BYMONTH=11;BYDAY=1SU" . "\r\n" .
                "END:STANDARD" . "\r\n" .
                "END:VTIMEZONE" . "\r\n" .
                "BEGIN:VEVENT" . "\r\n" .
                "ORGANIZER;CN=\"" . Zend_Registry::get("name") . "\":MAILTO:no-reply@jarrow.com\r\n" .
                "ATTENDEE;CN=\"" . Zend_Registry::get("name") . "\";ROLE=REQ-PARTICIPANT;RSVP=TRUE:MAILTO:" . Zend_Registry::get("email") . "\r\n";
        foreach ($this->_too as $recipient) {
            $ical .= "ATTENDEE;CN=\"" . $recipient['name'] . "\";ROLE=REQ-PARTICIPANT;RSVP=TRUE:MAILTO:" . $recipient['email'] . "\r\n";
        }
        $ical .= "LAST-MODIFIED:" . date("Ymd\TGis") . "\r\n" .
                "UID:" . date("Ymd\TGis", strtotime($data['start_time'])) . rand() . "@" . $this->_domain . "\r\n" .
                "DTSTAMP:" . date("Ymd\TGis") . "\r\n" .
                "DTSTART;TZID=\"America/Los_Angeles\":" . date("Ymd\THis", strtotime($data['start_time'])) . "\r\n" .
                "DTEND;TZID=\"America/Los_Angeles\":" . date("Ymd\THis", strtotime($data['end_time'])) . "\r\n" .
                "TRANSP:OPAQUE" . "\r\n" .
                "SEQUENCE:1" . "\r\n" .
                "STATUS:CANCELLED" . "\r\n" .
                "SUMMARY:" . $data['subject'] . "\r\n" .
                "LOCATION:" . $data['location'] . "\r\n" .
                "DESCRIPTION:" . $data['description'] . "\r\n" .
                "CLASS:PUBLIC" . "\r\n" .
                "PRIORITY:3" . "\r\n" .
                "BEGIN:VALARM" . "\r\n" .
                "TRIGGER:-PT15M" . "\r\n" .
                "ACTION:DISPLAY" . "\r\n" .
                "DESCRIPTION:Reminder" . "\r\n" .
                "END:VALARM" . "\r\n" .
                "END:VEVENT" . "\r\n" .
                "END:VCALENDAR" . "\r\n";

        //$this->_mail->AddStringAttachment("$ical", "calendar.ics", "base64", "text/calendar; charset=utf-8; method=REQUEST");
        $this->_mail->Ical = $ical;

        return $this;
    }

    public function setAttachment($url) {
        $this->_mail->AddAttachment($url);
    }

    public function addStringAttachment($url, $filename) {
        $this->_mail->AddStringAttachment($url, $filename);
    }

    public function send() {
        try {
            $this->setBcc(array(
                array('name' => 'Jarrow IT Department', 'email' => 'primary@jarrow.com')
            ));
            $this->_mail->send();

            return array("status" => true, "message" => "sent");
        } catch (Exception $e) {
            echo "Could not send out emails: <br />";
            echo $e->getMessage();

            return array("status" => false, "message" => $e->getMessage());
        }
    }

}

?>