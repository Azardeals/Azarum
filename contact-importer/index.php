<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

    <head>
        <title>Import Contacts</title>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
        <meta http-equiv="Pragma" content="no-cache" />
        <meta http-equiv="Expires" content="0" />
    </head>
    <body>
        <?php

//ini_set('display_errors',1);
        function toArray($obj)
        {
            if (is_object($obj))
                $obj = (array) $obj;
            if (is_array($obj)) {
                $new = array();
                foreach ($obj as $key => $val) {
                    $new[$key] = toArray($val);
                }
            } else {
                $new = $obj;
            }

            return $new;
        }

        // include class file
        require_once './contacts_importer.class.php';

        // creating new Contacts Importer object
        $import = new ContactsImporter;

        // set temp directory (necessary for storage Windows Live config)
        $import->TempDir = '/tmp/';

        // set URL to which script will return after authorization (GMail and Windows Live)
        $import->returnURL = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

        // Windows Live requires policy file, it could be anything
        $import->WLLPolicy = 'http://' . $_SERVER['HTTP_HOST'] . '/policy.php';
        // set API key for created application on Windows Live
        $import->WLLAPIid = '00000000480D1DEF';
        // set your secret phrase for Windows Live application
        $import->WLLSecret = 'X5UxOYVwQsGYAvSPfgWlMJg0kubth4Jb';

        // set API key for Yahoo application
        //$import->YahooAPIid = 'xxx';
        // set secret phrase for Yahoo application
        //$import->YahooSecret = 'xxx';
        //prints out authorization links for all 3 services
        //echo '<a href="'.$import->getGMailLink().'">GMail</a>';
        //echo '<a href="'.$import->getWLLLink().'">Hotmail</a>';

        if ($_GET['provider'] == 'gmail') {
            echo "<script type='text/javascript'>window.location='" . $import->getGMailLink() . "';</script>";
        } else if ($_GET['provider'] == 'live') {
            echo "<script type='text/javascript'>window.location='" . $import->getWLLLink() . "';</script>";
        }
        //echo '<a href="'.$import->getYahooLink().'">Yahoo</a>';
        // fetches contacts from authorized mail service
        $contacts = $import->getContacts();

        // prints out all fetched contacts
        // data structure is:
        // $contact->name - for name of the contact
        // $contact->email - for email address
        if (!empty($contacts)) {
            $contact = toArray($contacts);
            $act = "../";
            include_once 'mailer.php';
        }
        ?>
    </body>
</html>