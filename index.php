<?php
function normalizeMobilePhoneNumber($phone, $isoCountryCode = "se")
{
    $phoneFormat = array(
        "se" => array(
            "countryCode" => "46",
            "phoneLength" => 9,
            "startCharacters" => array("07"),
        ),
    );

    if (!array_key_exists(strtolower($isoCountryCode), $phoneFormat)) {
        throw new Exception("iso country code does not exists");
    }

    $originalPhone = $phone;
    $countryCode = $phoneFormat[strtolower($isoCountryCode)]["countryCode"];
    $phoneLength = $phoneFormat[strtolower($isoCountryCode)]["phoneLength"];
    $startCharacters = $phoneFormat[strtolower($isoCountryCode)]["startCharacters"];
    $phone = preg_replace("/[\+\-()\s]/", "", $phone);

    if ((strlen($phone) == $phoneLength + strlen($countryCode))) {
        $phone = preg_replace("/^$countryCode/", "", $phone);
    }

    $phoneIncludeZeroBeginning = $phone;
    $phone = preg_replace("/^0/", "", $phone);

    if ((preg_match("/[^0-9]+/", $phone)) || (strlen($phone) > $phoneLength + strlen($countryCode))
        || ((strlen($phone) == $phoneLength + strlen($countryCode)) && (!preg_match("/^$countryCode/", $phone)))
        || (strlen($phone) < $phoneLength) || (strlen($phone) == 0)
        || ((count($startCharacters) > 0) && (!preg_match("/^(" . join("|", $startCharacters) .")/", $phoneIncludeZeroBeginning)))
    ) {
        return array(
            "original" => "$originalPhone",
            "result" => "INVALID",
            "explain" => "invalid phone number format",
            "normalized" => "",
        );
    };

    return array(
        "original" => "$originalPhone",
        "result" => "VALID",
        "explain" => "it's mobile phone number",
        "normalized" => "+{$countryCode}{$phone}",
    );
}

$fileIn = fopen(realpath(dir(__FILE__)). DIRECTORY_SEPARATOR . "data/cellphones.txt", "r") or die("Unable to open file!");;
$fileOut = fopen(realpath(dir(__FILE__)). DIRECTORY_SEPARATOR . "data/cellphones_validate.txt", "w") or die("Unable to open file!");;

$outputDatas = array();

while(!feof($fileIn)){
    $phone = fgetcsv($fileIn);
    $outputDatas[] = normalizeMobilePhoneNumber( $phone[0],"se");
}
fclose($fileIn);

$count = 0;
fwrite($fileOut, "Order |original|result|explain|normalized\n");
foreach($outputDatas as $data){
    $count++;
    $line = "{$count}|{$data["original"]}|{$data["result"]}|{$data["explain"]}|{$data["normalized"]}\n";
    fwrite($fileOut, $line);
}
fclose($fileOut);