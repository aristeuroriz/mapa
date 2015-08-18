<?php

$banco = "mapa";
$usuario = "root";
$senha = "456901";
$hostname = "localhost:3306";
$conn = mysql_connect($hostname,$usuario,$senha); 

mysql_select_db($banco) or die( "Não foi possível conectar ao banco MySQL");

if (!$conn) {
    error_log("A conexão com o banco falhou: ".$conn->connect_error, 0);
    echo "Não foi possível conectar ao banco MySQL.";
    exit;
}else {
    error_log("A conexão ao banco de dados ocorreu normalmente!");
    echo "A conexão ao banco de dados ocorreu normalmente!";
}

//seleciona o banco de dados que será usado

mysql_select_db("mapa")or die( "Não foi possível conectar ao Schema no DB!");

$dados = "SELECT id, nome, descricao, endereco, cidade, uf, cep, lng, lat FROM empreendimentos;";


$resultado = mysql_query($dados);

$n_result = mysql_num_rows($resultado);

echo "<pre>\r\n";
echo "Quantidade de empreendimentos: ".$n_result;


$fp = fopen('pontos.json', 'w');
fwrite($fp, "[\n");

while($row = mysql_fetch_array($resultado)) {

    $address = str_replace(' ', '+', trim($row['endereco'])).",".str_replace(' ', '+', trim($row['cidade']))."-".str_replace(' ', '+', trim($row['uf']));
    // get latitude, longitude and formatted address
    $data_arr = geocode($address);

    // if able to geocode the address
    if($data_arr){

        $latitude = $data_arr[0];
        $longitude = $data_arr[1];
        $formatted_address = $data_arr[2];

        echo "<pre>\r\n";
        echo "Latitude: ".$latitude;
        echo "<pre>\r\n";
        echo "Longitude: ".$longitude;
        echo "<pre>\r\n";
        echo "Enredeço formatado: ".$formatted_address;

    // if unable to geocode the address
    }else{
        echo "<pre>\r\n";
        echo "No map found.";
        
    }

      $json = array(
                    'Id' => $row['id'],
                    'Title' => $row['nome'],
                    'Descricao' => utf8_encode($row['descricao']),
                    'Latitude' => $latitude,
                    'Longitude' => $longitude,
                    'Endereco' => $formatted_address);

    if($row['id'] < $n_result){
        fwrite($fp, json_encode($json, JSON_UNESCAPED_UNICODE).",\n");
    }else{
        fwrite($fp, json_encode($json, JSON_UNESCAPED_UNICODE)."\n");
    }


    if($row['lng']==null or $row['lat']==null){

        $update = "UPDATE empreendimentos SET lng='".$longitude."',lat='".$latitude."',endereco_maps='".$formatted_address."' WHERE id=".$row['id'].";";
        $res_up = mysql_query($update);

            if($res_up){

            error_log("Coordenadas atualizadas no banco com sucesso!");
            echo "<pre>\r\n";
            echo "Coordenadas atualizadas no banco com sucesso!";
            echo "<pre>\r\n";
            echo $update;
            echo "<pre>\r\n";
            echo $res_up;

            }else{

            error_log("Falha ao atualizar coordenadas!");
            echo "<pre>\r\n";
            echo "Falha ao atualizar coordenadas!";
            }
    }else{
        error_log("Campos já possuem coordenadas!");
        echo "<pre>\r\n";
        echo "Campos já possuem coordenadas!";
    }
 
} // Fecha While

fwrite($fp, substr(0, 2));
fwrite($fp, "]");
fclose($fp);



//Fecha conexão com o BD
        mysql_close($conn);
// ====================

// function to geocode address, it will return false if unable to geocode address
function geocode($address){

    // url encode the address
    //$address = urlen$address);
    echo "<pre>\r\n";
    echo "===================================";
    echo "<pre>\r\n";
    echo "Endereço de busca: ".utf8_encode($address);

    // google map geocode api url
    $url = utf8_encode("http://maps.google.com/maps/api/geocode/json?sensor=false&oe=utf-8&address={$address}");

    echo "<pre>\r\n";
    echo "Url gerado: ".$url;

     $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $resp_json = curl_exec($ch);
    curl_close($ch);

  
    // get the json response
    //$resp_json = file_get_contents($url);

    // decode the json
    $resp = json_decode($resp_json, true);
    //var_dump($resp);

    // response status will be 'OK', if able to geocode given address 
    if($resp['status']='OK'){

        $status = $resp['status'];
        echo "<pre>\r\n";
        echo "Very good! Status is ".$status."! :-)"; 
    
        // get the important data
        //$lati = $resp->results[0]->geometry->location->lat;
        //$longi = $resp->results[0]->geometry->location->lng;

        $lati = $resp['results'][0]['geometry']['location']['lat'];
        $longi = $resp['results'][0]['geometry']['location']['lng'];
        $formatted_address = $resp['results'][0]['formatted_address'];

        // verify if data is complete
        if($lati && $longi && $formatted_address){

            echo "<pre>\r\n";
            echo "Very good! Returned Coordinates :-)";

            // put the data in the array
            $data_arr = array();            

            array_push(
                $data_arr, 
                $lati, 
                $longi, 
                $formatted_address
                );

            return $data_arr;

        }else{
            echo "<pre>\r\n";
            echo "Bad response! NOT Returned Coordinates :-(";
            return false;
        }

    }else{
        echo "<pre>\r\n";
        echo "Bad geocode! NOT Returned Good State :-(";
        return false;
    }
}

?>