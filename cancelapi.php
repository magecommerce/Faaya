<?php
$orderData = '{"incrementId":100000123,"items":[1527684786]}';
try {
    $client = new SoapClient('http://developmentbox.online/faaya/index.php/api/soap/?wsdl');
    $session = $client->login('faayadeveloper', 'faayadeveloper');
    $result = $client->call($session, 'cancelapi.cancelapi', $orderData);
    $resultSet = json_decode($result);
    print_R($resultSet);
    //$client->endSession($session);
}
catch(Exception $e) {
 echo $e->getMessage();
}


?>