<?php
/**
 * Created by PhpStorm.
 * User: Daniel-Paz-Horta
 * Date: 9/22/17
 * Time: 10:27 AM
 */

namespace dpazuic;


class aits_enterprise_user
{

    /**
     * The UIN you are querying for
     *
     * @var string
     */
    protected $netid;

    /**
     * @var
     */
    protected $domain;

    /**
     * The senderAppID provided by AITS when registering your app's access with the AITS Term API
     *
     * @var string
     */
    protected $senderAppID;

    /**
     * The result of an AITS term request call
     *
     * @var object
     */
    protected $response;

    /**
     * The UIN returned after running findPerson()
     *
     * @var string
     */
    protected $uin;

    /**
     * aits_enterprise_user constructor.
     * @param null $netID
     * @param null $domain
     * @param null $senderAppID
     * @throws \Exception
     */
    public function __construct($netID = NULL, $domain = NULL, $senderAppID = NULL)
    {

        // Set the netID to be queried
        $this->setNetId($netID);

        // Set the Campus Domain where the netID should be queried from
        $this->setDomain($domain);

        // Set the AITS provided SenderAppId
        $this->setSenderAppId($senderAppID);

    }

    /**
     * @param $netID
     * @throws \Exception
     */
    private function setNetId($netID)
    {

        // Validate NetID
        if(!empty($netID)) {

            $netID = $this->checkNetID($netID);

        } else {

            // Throw an Error
            throw new \Exception('NetID cannot be blank. Provide a fully qualified NetID.');

        }

        $this->netid = $netID;

    }

    /**
     * Void method that sets the senderAppID property
     *
     * @param $senderAppID
     * @throws \Exception
     */
    private function setSenderAppId($senderAppID)
    {

        // Check to see if the $senderAppID was set
        if(empty($senderAppID)) {

            throw new \Exception('The senderAppId cannot be blank. Please contact AITS for a senderAppId');

        }

        $this->senderAppID = $senderAppID;

    }

    /**
     * @param mixed $domain
     */
    public function setDomain($domain)
    {

        if(empty($domain)) {

            throw new \Exception('The campus domain cannot be blank.');

        }

        $this->domain = $this->checkDomain($domain);
    }

    /**
     * Method that returns the value of $this->senderAppID
     *
     * @return string
     */
    public function getSenderAppId()
    {

        return $this->senderAppID;

    }

    /**
     * Method that returns the value of $this->response
     *
     * @return object
     */
    public function getResponse($outputFormat = 'json')
    {

        // Validate format
        $outputFormat = $this->checkFormatParam($outputFormat);


        switch(strtolower($outputFormat)) {
            case 'raw':
                // Convert to Array
                $array = json_decode($this->response->data);

                // Check to see that $obj is an object, thus has data
                if (!is_object($array)) {

                    throw new \Exception('Communication with the AITS Enterprise Person API is not available. Try again later.');

                }
                return $array;
                break;

            case 'json':
            default:
                return $this->response->data;
                break;
        }

    }

    /**
     * @return string
     */
    public function getNetID()
    {
        return $this->netid;
    }

    /**
     * @return mixed
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return mixed
     */
    public function getUin()
    {
        return $this->uin;
    }

    /**
     * Method used to communicate with the AITS Term API and return data in a specified format
     * https://www.aits.uillinois.edu/cms/One.aspx?portalId=558&pageId=632773
     *
     * @param string $outputFormat
     */
    public function findPerson()
    {

        // AITS Term API Source
        $source = 'https://webservices-dev.admin.uillinois.edu/xfunctionalWS/data/' . $this->senderAppID . '/EnterpriseUser/1_1/' . $this->netid . '/' . $this->domain; // todo - determine how to handle switching resource in production

        // Initialize a curl resource
        $curl = curl_init();

        // Set curl options
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $source);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array());

        // JSON Response
        $response = curl_exec($curl);

        if(curl_getinfo($curl, CURLINFO_HTTP_CODE) == 200) {

            // Cache the response in $this->response
            $this->response = new \stdClass();
            $this->response->type = 'JSON';
            $this->response->data = $response;

            // Set the UIN
            $this->uin = $this->findUIN($response);

        } else {

            throw new \Exception($response);

        }

    }

    /**
     * Method used to validate the uin against an expected format
     *
     * @param $netID
     * @return null|string|string[]
     * @throws \Exception
     */
    private function checkNetID($netID)
    {

        if(empty($netID)){

            // Throw exception, banner term code is not valid
            throw new \Exception('NetID cannot be blank.');

        }

        // Check if the @ and domain was included, if so strip it out
        $netID = preg_replace("/@(.*?)$/", "", $netID);

        return $netID;

    }


    /**
     * Method used to validate the format for the response
     *
     * @param $format (json|xml)
     * @return string
     * @throws Exception
     */
    private function checkFormatParam($format)
    {
        $format = strtolower($format);

        // Clean the code, check that it matches
        $formatArray = preg_grep("/^(json|raw)$/", explode("\n", $format));

        if(empty($formatArray)){

            // Throw exception, banner term code is not valid
            throw new \Exception('The format: "' . $format . '" is not supported. Use json or xml');

        }

        return $format;

    }

    /**
     * Method used to validate a campus domain code against expected values
     *
     * @param $domain (uic|uic.edu|uiuc|uiuc.edu|illinois|illinois.edu|uis|uis.edu|100|200|400)
     * @return null|string
     * @throws Exception
     */
    private function checkDomain($domain)
    {

        // Convert $domain to lowercase
        $domain = strtolower($domain);

        // Clean the code, check that it matches
        $domainArray = preg_grep("/(uic|uiuc|illinois|uis|100|200|400)/", explode("\n", $domain));

        // If $domain matches exactly one
        if(count($domainArray) > 1) {
            // $domain matches more than one check
            throw new \Exception('The campus domain code: "' . $domain . '" has too many matches. Specify uic, illinois or uis.');

        } else if(count($domainArray) == 1){

            switch($domain){
                case 'illinois':
                case 'illinois.edu':
                case 'uiuc':
                case 'uiuc.edu':
                case '100':
                    $domain = 'illinois.edu';
                    break;
                case 'uic':
                case 'uic.edu':
                case '200':
                    $domain = 'uic.edu';
                    break;
                case 'uis':
                case 'uis.edu':
                case '400':
                    $domain = 'uis.edu';
                    break;
                default:
                    $domain = null;
                    break;
            }

            return $domain;

        }  else {

            // $domain does not match checks
            throw new \Exception('The campus domain: "' . $domain . '" is not valid. Specify uic, illinois or uis.');

        }

    }

    /**
     * Method used to parse a response body from findPerson() and pluck out the institutionalId (UIN)
     *
     * @param $responseBody
     * @return null
     */
    private function findUIN($responseBody)
    {

        $data = json_decode($responseBody);

        // Check if the instructionalId is set
        if(!empty($data->list[0]->lightweightPerson->institutionalId)) {

            return $data->list[0]->lightweightPerson->institutionalId;  // todo - Will the array always consist of 1 object?

        }

        return null;

    }

}