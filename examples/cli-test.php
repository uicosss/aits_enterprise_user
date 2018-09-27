<?php
/**
 * Created by PhpStorm.
 * User: Daniel-Paz-Horta
 * Date: 9/22/17
 * Time: 10:27 AM
 */

try {

    include_once(__DIR__ . '/../src/aits_enterprise_user.php');

    print_r($argv);

    // AITS Sender APP ID
    if(empty($argv[1])){

        throw new \Exception("Error: Specify senderAppId ID as provided from AITS as the 3rd argument.");

    }

    // NetID
    if(empty($argv[2])){

        throw new \Exception("Error: Specify NetID as the 3rd argument.");

    }

    // Campus
    if(empty($argv[3])){

        throw new \Exception("Error: Specify Campus Domain as the 4th argument.");

    }

    // Call the AITS EnterpriseUser API
    $personAPI = new dpazuic\aits_enterprise_user($argv[2], $argv[3], $argv[1]);

    // Get the results of a call
    $personAPI->findPerson();

    print_r($personAPI->getResponse('raw'));

} catch (\Exception $e){

    print_r($e->getMessage());
    echo PHP_EOL;
    echo PHP_EOL;

}