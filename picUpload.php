 <?php //curl settings

    $url="https://api.dclink.com.ua/api/GetPicturesUrl"; //адрес api
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


    $Pictures = new SimpleXMLElement($output);

    $pics = fopen("pictures.xml", "w") or die ("Unable to open file!");
    $header = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>
<Pricelist>
";
    fwrite($pics, $header);

     foreach ($Pictures as $Product) {
$toXML = <<<EOD
    <Product>
        <Code>$Product->Code</Code>
        <URL>$Product->URL</URL>
    </Product>

EOD;

fwrite($pics, $toXML);

    }

    $footer = "\n </Pricelist>";

    fwrite($pics, $footer);

    fclose($pics);

    echo "Pictures uploaded";
?>