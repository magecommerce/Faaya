<?php
$sku = 98989;

try {
    $soap = new SoapClient('http://developmentbox.online/faaya/index.php/api/soap/?wsdl');
    $sessionId = $soap->login('faayadeveloper', 'faayadeveloper');
    $result = $soap->call($sessionId, 'deleteapi.deleteapi',$sku);
    $resultSet = json_decode($result);
    print_R($resultSet);
}
catch(Exception $e) {
 echo $e->getMessage();
}





?>