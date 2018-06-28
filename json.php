<?php
function array_to_xml($array, &$xml_user_info) {
    foreach($array as $key => $value) {
        if(is_array($value)) {
            if(!is_numeric($key)){
                $subnode = $xml_user_info->addChild("$key");
                array_to_xml($value, $subnode);
            }else{
                $subnode = $xml_user_info->addChild("item$key");
                array_to_xml($value, $subnode);
            }
        }else {
            $xml_user_info->addChild("$key",htmlspecialchars("$value"));
        }
    }
}


function objToArray($obj, &$arr){

    if(!is_object($obj) && !is_array($obj)){
        $arr = $obj;
        return $arr;
    }

    foreach ($obj as $key => $value)
    {
        if (!empty($value))
        {
            $arr[$key] = array();
            objToArray($value, $arr[$key]);
        }
        else
        {
            $arr[$key] = $value;
        }
    }
    return $arr;
}


$json = file_get_contents("jsonfiles/JSON-StockLoadTestingReport.json");

$obj = json_decode($json);
$obj = (array) $obj;

objToArray($obj,$arr);
echo "<pre/>";print_r($arr);exit;
/*

$xml_user_info = new SimpleXMLElement("<?xml version=\"1.0\"?><user_info></user_info>");

//function call to convert array to xml
array_to_xml($arr,$xml_user_info);

//saving generated xml file
$xml_file = $xml_user_info->asXML('users.xml');

//success and error message based on xml creation
if($xml_file){
    echo 'XML file have been generated successfully.';
}else{
    echo 'XML file generation error.';
}*/

?>
