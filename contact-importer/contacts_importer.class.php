<?php

/**
 * Contacts Importer class
 * 
 * PHP versions 5
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>. 
 *
 * @category   class
 * @package    Contacts Importer
 * @author     Karol Janyst (LapKom)
 * @copyright  2009 Karol Janyst
 * @license    http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License v3
 * @version    0.1
 * */
/**
 * Include Windows Live Login library
 * */
include ('hotmail/windowslivelogin.php');

/**
 * Include yahoo library
 * */
include ('yahoo/ybrowserauth.class.php');

class ContactsImporter
{

    /**
     * Imported contacts
     * 
     * @var array
     * @access private
     */
    private $Contacts = array();

    /**
     * Path to temp directory
     * 
     * @var resource
     * @access public
     */
    public $TempDir = '/tmp/';

    /**
     * Return URL after authorization
     * 
     * @var resource
     * @access public
     */
    public $returnURL = 'http%3A%2F%2Fbahroupon.bitfatdeals.com';

    /**
     * Scope for GMail authorization
     * 
     * @var resource
     * @access private
     */
    private $GMailScope = 'https%3A%2F%2Fwww.google.com%2Fm8%2Ffeeds%2F';

    /**
     * Windows Live Login private policy
     * 
     * @var resource
     * @access public
     */
    public $WLLPolicy = 'http%3A%2F%2Fbahroupon.bitfatdeals.com%2Fpolicy.php';

    /**
     * Windows Live Login config file
     * 
     * @var resource
     * @access private
     */
    private $WLLConfig;

    /**
     * Windows Live Login API id
     * 
     * @var resource
     * @access public
     */
    public $WLLAPIid = '00000000480D1DEF';

    /**
     * Windows Live Login secret
     * 
     * @var resource
     * @access public
     */
    public $WLLSecret = 'X5UxOYVwQsGYAvSPfgWlMJg0kubth4Jb';

    /**
     * Yahoo API id
     * 
     * @var resource
     * @access public
     */
    public $YahooAPIid = 'xxx';

    /**
     * Yahoo secret
     * 
     * @var resource
     * @access public
     */
    public $YahooSecret = 'xxx';

    /**
     * Yahoo timestamp
     * 
     * @var resource
     * @access public
     */
    public $YahooTimestamp;

    /**
     * Constructor
     *
     * @param $returnURL as return URL
     * @access public
     * @return void
     */
    public function __construct($returnURL = '')
    {
        $this->returnURL = $returnURL;
    }

    /**
     * Get imported contacts
     *
     * @access public
     * @return object Contacts 
     */
    public function getContacts()
    {
        $this->CreateWLLConfig();
        if (!empty($this->Contacts)) {
            return $this->Contacts;
        } else {
            if ($this->checkGMail())
                $this->fetchGMailContacts();
            if ($this->checkWLL())
                $this->fetchWLContacts();
            if ($this->checkYahoo())
                $this->fetchYahooContacts();
        }
        return $this->Contacts;
    }

    /**
     * Check GMail authorization 
     *
     * @access public
     * @return boolean True if GMail authorized
     */
    public function checkGMail()
    {
        if (isset($_GET['token']) && !empty($_GET['token']) && !$this->checkYahoo()) {
            return true;
        }
        return false;
    }

    /**
     * Change GMail Scope
     *
     * @param $GMailScope as new scope
     * @access public
     * @return void
     */
    public function ChangeGMailScope($GMailScope)
    {
        $this->GmailScope = urlencode($GMailScope);
    }

    /**
     * Get GMail authorization link
     *
     * @access public
     * @return string Link to authorize GMail account
     */
    public function getGMailLink()
    {
        $link = 'https://www.google.com/accounts/AuthSubRequest?scope=' . $this->GMailScope;
        $link .= '&session=1&secure=0&next=' . urlencode($this->returnURL);
        return $link;
    }

    /**
     * Fetch GMail contacts
     *
     * @access public
     * @return void
     */
    public function fetchGMailContacts()
    {
        $token = $_GET['token'];
        $this->Contacts = array();
        $GMailAuthSubUrl = "https://www.google.com/accounts/AuthSubSessionToken";
        $GMailContactsUrl = "https://www.google.com/m8/feeds/contacts/default/full";
        $headers = array('Authorization: AuthSub token=' . $token,
            'Content-Type: application/x-www-form-urlencoded');

        $cURLHandle = curl_init();
        curl_setopt($cURLHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($cURLHandle, CURLOPT_TIMEOUT, 60);
        curl_setopt($cURLHandle, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($cURLHandle, CURLOPT_URL, $GMailAuthSubUrl);
        curl_setopt($cURLHandle, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($cURLHandle);

        $newToken = substr($response, 6);
        $headers = array('Authorization: AuthSub token=' . $newToken,
            'Accept-Charset: utf-8, iso-8859-2, iso-8859-1',
            'Content-Type: application/x-www-form-urlencoded');

        $cURLHandle = curl_init();
        curl_setopt($cURLHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($cURLHandle, CURLOPT_TIMEOUT, 60);
        curl_setopt($cURLHandle, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($cURLHandle, CURLOPT_URL, $GMailContactsUrl);
        curl_setopt($cURLHandle, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($cURLHandle);
        curl_close($cURLHandle);

        $namespaceChanged = str_replace("gd:email", "gdemail", $response);

        $contacts = new SimpleXMLElement($namespaceChanged);

        if (!empty($contacts->entry)) {
            foreach ($contacts->entry as $contact) {
                $insert = (object) array('name' => strip_tags($contact->title), 'email' => strip_tags($contact->gdemail['address']));
                array_push($this->Contacts, $insert);
            }
        }
    }

    /**
     * Convert to 64Decimal
     *
     * @access private
     * @return string
     */
    private function hexaTo64SignedDecimal($hexa)
    {
        $bin = $this->extended_base_convert($hexa, 16, 2);
        if (64 === strlen($bin) and 1 == $bin[0]) {
            $inv_bin = strtr($bin, '01', '10');
            $i = 63;
            while (0 !== $i) {
                if (0 == $inv_bin[$i]) {
                    $inv_bin[$i] = 1;
                    $i = 0;
                } else {
                    $inv_bin[$i] = 0;
                    $i--;
                }
            }
            return '-' . $this->extended_base_convert($inv_bin, 2, 10);
        } else {
            return $this->extended_base_convert($hexa, 16, 10);
        }
    }

    /**
     * Extended version of PHP base_convert
     *
     * @access private
     * @return string
     */
    private function extended_base_convert($numstring, $frombase, $tobase)
    {
        $chars = "0123456789abcdefghijklmnopqrstuvwxyz";
        $tostring = substr($chars, 0, $tobase);

        $length = strlen($numstring);
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $number[$i] = strpos($chars, $numstring{$i});
        }
        do {
            $divide = 0;
            $newlen = 0;
            for ($i = 0; $i < $length; $i++) {
                $divide = $divide * $frombase + $number[$i];
                if ($divide >= $tobase) {
                    $number[$newlen++] = (int) ($divide / $tobase);
                    $divide = $divide % $tobase;
                } elseif ($newlen > 0) {
                    $number[$newlen++] = 0;
                }
            }
            $length = $newlen;
            $result = $tostring{$divide} . $result;
        } while ($newlen != 0);
        return $result;
    }

    /**
     * Create temporary Windows Live Login config file
     *
     * @access private
     * @return void
     */
    private function CreateWLLConfig()
    {
        $configXML = '<windowslivelogin>
                    <appid>' . $this->WLLAPIid . '</appid>
                    <secret>' . $this->WLLSecret . '</secret>
                    <securityalgorithm>wsignin1.0</securityalgorithm>
                    <returnurl>' . urldecode($this->returnURL) . '</returnurl>
                    <policyurl>' . urldecode($this->WLLPolicy) . '</policyurl>
                  </windowslivelogin>';

        $tmpFile = $this->TempDir . uniqid();
        $file = fopen($tmpFile, 'a+');
        flock($file, 2);
        if (fwrite($file, $configXML)) {
            $this->WLLConfig = $tmpFile;
        }
        flock($file, 3);
        fclose($file);
    }

    /**
     * Get Windows Live Login authorization link
     *
     * @access public
     * @return string Link to authorize Hotmail account
     */
    public function getWLLLink()
    {
        $link = 'https://consent.live.com/Delegation.aspx?RU=' . urlencode($this->returnURL);
        $link .= '&ps=Contacts.View&pl=' . urlencode($this->WLLPolicy);
        return $link;
    }

    /**
     * Check Windows Live Login authorization 
     *
     * @access public
     * @return boolean True if WLL authorized
     */
    public function checkWLL()
    {
        $WLL = WindowsLiveLogin::initFromXml($this->WLLConfig);
        $conn = $WLL->processConsent($_REQUEST);
        if ($conn != null) {
            $WLL = null;
            return true;
        }
        $WLL = null;
        return false;
    }

    /**
     * Fetch Hotmail contacts 
     *
     * @access public
     * @return void
     */
    public function fetchWLContacts()
    {
        $this->Contacts = array();
        $WLL = WindowsLiveLogin::initFromXml($this->WLLConfig);
        $conn = $WLL->processConsent($_REQUEST);
        if ($conn != null) {
            $delegationToken = $conn->getDelegationToken();
            $locationId64 = $this->hexaTo64SignedDecimal($conn->getLocationID(), 16, 10);
            $WLLContactsURL = 'https://livecontacts.services.live.com/users/@C@' . $locationId64 . '/rest/livecontacts';
            $headers = array('Authorization: DelegatedToken dt="' . $delegationToken . '"');

            $cURLHandle = curl_init();
            curl_setopt($cURLHandle, CURLOPT_URL, $WLLContactsURL);
            curl_setopt($cURLHandle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($cURLHandle, CURLOPT_TIMEOUT, 60);
            curl_setopt($cURLHandle, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($cURLHandle, CURLOPT_SSL_VERIFYPEER, FALSE);

            $response = curl_exec($cURLHandle);
            curl_close($cURLHandle);

            $contacts = new SimpleXMLElement($response);

            if (!empty($contacts->Contacts->Contact)) {
                foreach ($contacts->Contacts->Contact as $contact) {
                    $name = strip_tags($contact->Profiles->Personal->FirstName) . " " . strip_tags($contact->Profiles->Personal->LastName);
                    $insert = (object) array('name' => $name, 'email' => strip_tags($contact->Emails->Email->Address));
                    array_push($this->Contacts, $insert);
                }
            }
        }
    }

    /**
     * Validate Yahoo signature 
     *
     * @access public
     * @return boolean True if signature valid
     */
    public function validateSig($secret)
    {
        $ts = $_GET["ts"];
        $sig = $_GET["sig"];
        $relative_url = getenv('REQUEST_URI');
        $match = array();

        $match_rv = preg_match("/^(.+)&sig=(\w{32})$/", $relative_url, $match);

        if ($match_rv == 1) {
            if ($match[2] != $sig) {
                return false;
            }
        } else {
            return true;
        }

        $relative_url_without_sig = $match[1];
        $current_time = time();
        $clock_skew = abs($current_time - $ts);
        if ($clock_skew >= 600) {
            return false;
        }

        $sig_input = $relative_url_without_sig . $secret;
        $calculated_sig = md5($sig_input);
        if ($calculated_sig == $sig) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check Yahoo authorization 
     *
     * @access public
     * @return boolean True if Yahoo authorized
     */
    public function checkYahoo()
    {
        if (isset($_GET['token']) && !empty($_GET['token']) && !empty($_GET['sig'])) {
            return true;
        }
        return false;
    }

    /**
     * Get Yahoo authorization link
     *
     * @access public
     * @return string Link to authorize Yahoo account
     */
    public function getYahooLink()
    {
        $authObj = new YBBauthREST($this->YahooAPIid, $this->YahooSecret);
        return $authObj->getAuthURL('', true);
    }

    /**
     * Fetch Yahoo contacts
     *
     * @access public
     * @return void
     */
    public function fetchYahooContacts()
    {
        $this->Contacts = array();
        $authObj = new YBBauthREST($this->YahooAPIid, $this->YahooSecret);

        if ($authObj->validate_sig()) {
            $authURL = 'http://address.yahooapis.com/api/ws/v1/searchContacts?format=xml';
            $response = $authObj->makeAuthWSgetCall($authURL);

            $contacts = new SimpleXMLElement($response);

            if (!empty($contacts->contact)) {
                foreach ($contacts->contact as $contact) {
                    $name = strip_tags($contact->name->first) . " " . strip_tags($contact->name->last);
                    $insert = (object) array('name' => $name, 'email' => strip_tags($contact->email));
                    array_push($this->Contacts, $insert);
                }
            }
        }
        return true;
    }

}

?>