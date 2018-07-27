# aits_enterprise_user

PHP Library for using the AITS Enterprise User API (contact AITS for additional details on API).

## Usage
To use the library, you need to:

### Include library in your program 
```
include_once(aits_enterprise_user.php');
```
### or use composer `composer require dpazuic\aits_enterprise_user`
```
include_once('vendor/autoload.php');
```
### Instantiate an object of class `dpazuic\aits_enterprise_user`
```
$netid = 'sparky'; 
$campusDomain = 'uic.edu'; // Allowed: (uic.edu|uiuc.edu|illinois.edu|uis.edu|100|200|400)
$senderAppID = 'YOUR_SENDER_APP_ID'; // Contact AITS for this
$personApi = new dpazuic\aits_enterprise_user($netid, $campusDomain, $senderAppID);
```

### Getting Results from an API call
The default response will be JSON, but you can also request the raw data which will be an object of StdClass. Contact AITS for additional details on API schema.
```
$personApi->findPerson(); // Conduct the person lookup
$response = $personApi->getResponse(); // See JSON response
```

## Examples:
You can use the attached `examples/cli-test.php` file from the command line to test functionality.
`php cli-test.php YOUR_SENDER_APP_ID NETID CAMPUS_DOMAIN`