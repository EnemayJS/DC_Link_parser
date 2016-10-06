 <?php //curl settings

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

?>