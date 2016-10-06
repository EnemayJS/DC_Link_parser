<?php
 require_once "login.php"; // get login params
 require_once "curl.php"; // get curl 

$Pricelist = new SimpleXMLElement($output); //creating array from curl output

//***** Creating log file section

$data = fopen("update_log.xml", "w") or die ("Unable to open file!"); // open file for update log
// header for xml file
$header = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>
<Pricelist>
";
fwrite($data, $header); // write header to file

foreach ($Pricelist as $Product) { //looping through pricelist array
$toXML = <<<EOD
    <Product>
        <Code>$Product->Code</Code>
        <Name>$Product->Name</Name>
        <Availability>$Product->Availability</Availability>
        <RetailPriceUAH>$Product->RetailPriceUAH</RetailPriceUAH>
    </Product>

EOD;

fwrite($data, $toXML); // write array values to file

}

$footer = "\n </Pricelist>"; // add and write footer to file
fwrite($data, $footer);

fclose($data); // closing log file

// **** MYSQL QUERY SECTION

$conn = new mysqli($hn, $un, $pw, $db); // connecting to mysql
    if ($conn->connect_error) die($conn->connect_error);

$log_data = fopen("update_log.txt", "w") or die ("Unable to open file!"); // open file for update log

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

        $is_code_exist = "SELECT * FROM oc_product WHERE product_id = $id";

        $result = $conn->query($is_code_exist);

        if ($result->num_rows > 0) { // if there any rows then it updates them
        $price_quant_id_update = "UPDATE oc_product SET price = $price, quantity = $quant, date_modified = NOW() WHERE product_id = $id"; // query to oc_product table

            if ($conn->query($price_quant_id_update) === TRUE) { // sending query to mysql and feedback
                $log_price = "Price and quantity updated successfully \r\n";
            } else {
                $log_price = "Error: " . $price_quant_id_update . "\r\n" . $conn->error;
            }

        fwrite($log_data, $log_price);

        $name = "$Product->Name";

            $name_id_update = "UPDATE oc_product_description SET name = '$name' WHERE product_id = $id"; // query to oc_product_description

            if ($conn->query($name_id_update) === TRUE) { // sending query to mysql and feedback
                $log_name = "Name updated successfully \r\n";
            } else {
                $log_name = "Error: " . $name_id_update . "\r\n" . $conn->error;
            }

         fwrite($log_data, $log_name); // write to aql update log name;


        } else { // if id doesn't exist in db it creates new product

            $price_quant_id = "INSERT INTO oc_product (price, quantity, product_id, date_added, date_modified)
VALUES ($price, $quant, $id, NOW(), NOW())"; // query to oc_product table

            if ($conn->query($price_quant_id) === TRUE) { // sending query to mysql and feedback
                $log_price = "ID, Price and quantity created successfully \r\n";
            } else {
                $log_price = "Error: " . $price_quant_id . "\r\n" . $conn->error;
            }

        fwrite($log_data, $log_price);


        $name = "$Product->Name";
        
        $name_id = "INSERT INTO oc_product_description (product_id, name)
VALUES ('$id', '$name')"; // query to oc_product_description

            if ($conn->query($name_id) === TRUE) { // sending query to mysql and feedback
                $log_name = "Name and ID created successfully \r\n";
            } else {
                $log_name = "Error: " . $name_id . "\r\n" . $conn->error;
            }

        fwrite($log_data, $log_name);


        }


        
}

    $check_availability = "UPDATE oc_product SET quantity = 0 WHERE date_modified < CURDATE()"; // if update time less then current day then set quantity to zero

    if ($conn->query($check_availability) === TRUE) { // sending query to mysql and feedback
            $log_avail = "Unavailable products quantity set to zero \r\n";
        } else {
            $log_avail = "Error: " . $check_availability . "\r\n" . $conn->error;
        }

    fwrite($log_data, $log_avail);

	fclose($log_data); // closing sql update log file 

	$conn->close(); // closing connection

?>