<?php
    require_once "login.php"; // get login params

    $url="https://api.dclink.com.ua/api/GetPrice"; //адрес api
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, array (
        'login' => 'LOGIN',
        'password' => 'PASS',
        
    )); //параметры запроса
    curl_setopt($ch, CURLOPT_URL, $url);
    $output = curl_exec($ch);  //ответ


    curl_close($ch);

    $Pricelist = new SimpleXMLElement($output);
/*	$answer = $Pricelist->Product[0]->Name;*/
$data = fopen("data.xml", "w") or die ("Unable to open file!");
$header = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>
<Pricelist>
";
fwrite($data, $header);
/*$newline = "\r\n";
fwrite($data, $newline);*/
 foreach ($Pricelist as $Product) {
$toXML = <<<EOD
    <Product>
        <Code>$Product->Code</Code>
        <Name>$Product->Name</Name>
        <Availability>$Product->Availability</Availability>
        <RetailPriceUAH>$Product->RetailPriceUAH</RetailPriceUAH>
    </Product>

EOD;

fwrite($data, $toXML);

    }

$footer = "\n </Pricelist>";
fwrite($data, $footer);

fclose($data);

/*$data = fopen("data.xml", "w") or die ("Unable to open file!");
	fwrite($data, $answer);
	fclose($data);*/

    $conn = new mysqli($hn, $un, $pw, $db); // connecting to mysql
    if ($conn->connect_error) die($conn->connect_error);

    foreach ($Pricelist as $Product) { // looping through pricelist array
        $price = floatval($Product->RetailPriceUAH);

        switch ($Product->Availability) { //transform stars to integer
            case "*":
                $quant = 5;
                break;
            case "**":
                $quant = 10;
                break;
            case "***":
                $quant = 15;
            break;
            case "****":
                $quant = 20;
            break;
            case "*****":
                $quant = 25;
            break;
        }

        $id = intval($Product->Code);

        $price_quant_id = "INSERT INTO oc_product (price, quantity,product_id)
VALUES ($price, $quant,$id)"; // query to oc_product table

        if ($conn->query($price_quant_id) === TRUE) { // sending query to mysql and feedback
        echo "New price and quantity created successfully";
        } else {
            echo "Error: " . $price_quant_id . "<br>" . $conn->error;
        }


        $name = "$Product->Name";
        //$id = intval($Product->Code); // transform string id to integer

        $name_id = "INSERT INTO oc_product_description (product_id, name)
VALUES ('$id', '$name')"; // query to oc_product_description

        if ($conn->query($name_id) === TRUE) { // sending query to mysql and feedback
        echo "New id and name created successfully";
        } else {
            echo "Error: " . $name_id . "<br>" . $conn->error;
        }

    }

    $conn->close(); // closing connection

?>