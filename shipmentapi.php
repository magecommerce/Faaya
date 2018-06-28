<?php
$orderData = '{"incrementId":100000158,"items":[1528279282],"tracking":["Tracking name","DDT220000"]}';

try {
    $client = new SoapClient('http://developmentbox.online/faaya/index.php/api/soap/?wsdl');
    $session = $client->login('faayadeveloper', 'faayadeveloper');
    $result = $client->call($session, 'shipmentapi.createshipment', $orderData);
    $resultSet = json_decode($result);
    print_R($resultSet);
    //$client->endSession($session);
}
catch(Exception $e) {
 echo $e->getMessage();
}


?>