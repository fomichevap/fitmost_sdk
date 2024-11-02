<?php

/**
 * php 7.0+
 * Класс (обертка) для обращения партнеров к API mostcrm, использование:
 * 
 * инициализация
 * $fitmostRequest = new requestObj($headerToken,$bearerToken);
 * header токен отдается полностью строкой (взять у фитмоста), берер токен - самим токеном, генерируется в ЛК партнерки фитмост.
 * 
 * использование
 * $r = $fitmostRequest->bookings('decline','POST',['id'=>123456]);
 * Где bookings - корень, аргумент 1- субзапрос или null, 2 - Метод (POST,PUT,GET,DELETE), 3 - массив c нагрузкой
 * class->method(null|string,string(POST|GET|DELETE|PUT),array[])
 * в случае пустого ответа возвраает респонс-код ошибки, либо [message=>string] о выполнении запроса.
 * в случае наличия ответа возвращает массив
 * 
 * 
 * 
 * Распространяется "как есть"
 * 
 * @author SELFCLICK
 * Сеть фотостудий автопортрета, SELFCLICK.RU - секретный промокод на скидку 25% в любую из 30+ студий в России для айтишников - HELLOWORLD
 * Кстати, попробуйте, мы очень технологичные :)
 */
class requestObj {
    
    private $headerToken;
    private $route = 'https://api.mostcrm.ru/v1/';
    private $bearer;
    private $codes = [
        200=>'Класс удален',
        401=>'Пользователь не авторизован',
        404=>'Не найдено',
        422=>'Ошибка валидации данных'
    ];
    private $debug = false;//Режим отладки
    private $outputAsArray = true;
    public $rawResponse;
    public $meta;
    public $url,$responseCode;
    private $cacheFile = __DIR__.'/cache/fitmost_requests.cache';
    private $defaultTtl = 300; # time of cache livings
    private $cache = [];
    public $noCache = false;
    
   
    function __construct(string $headerToken, string $bearer) {
        if (!$headerToken) {
            throw new Exception('Не передан header - токен');
        }
        if (!$bearer) {
            throw new Exception('Не передан bearer - токен');
        }
        $this->headerToken = $headerToken;
        $this->bearer = $bearer;
        if (!file_exists($this->cacheFile)) {
            if (!file_exists(__DIR__.'/cache/')) {
                mkdir(__DIR__.'/cache');
            }
            file_put_contents($this->cacheFile, json_encode([]));
        }
        $this->cache = json_decode(file_get_contents($this->cacheFile),true);
        foreach ($this->cache as $k=>$e) {
            if ($e['ttl'] < time()) {
                unset($this->cache[$e]);
            }
        }
        
    }
    
    private function toCache($url,$payload, $result) {
        $this->cache[md5($url.json_encode($payload))] = [
            'ttl'=>time() + $this->defaultTtl * 60,
            'response'=> $result
        ];
    }
    
    private function fromCache($url,$payload) {
        if (isset($this->cache[md5($url. json_encode($payload))])) {
            return $this->cache[md5($url. json_encode($payload))]['response'];
        } else {
            return false;
        }
    }
    
    private function saveCache() {
        file_put_contents($this->cacheFile, json_encode($this->cache));
    }
    
    private function request(string $subRoute,$action,$method,$payload = []) {
        
        $ch = curl_init();  
        $url = $this->route.$subRoute.($action ? '/'.$action : '');
        switch ($method) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($payload));
                break;
            case 'GET':
                $params = '';
                foreach($payload as $key=>$value) {
                    $params .= $key .'='.$value.'&';
                }
                if ($params) {
                    $url .= '?'. $params;
                }
                break;
            case 'PATCH':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($payload));
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($payload));
                break;

            default:
                break;
        }
        curl_setopt($ch, CURLOPT_URL,$url);
//        return ['url'=>$url];
        
        if ($method == 'GET' && $this->fromCache($url, $payload) && !$this->noCache) {
            $output = json_encode($this->fromCache($url, $payload));
        } else {
            $this->url = $url;
            $this->method = $method;
            $authorization = "Authorization: Bearer ".$this->bearer; // Prepare the authorisation token
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                  $this->headerToken,
                  $authorization,
                  'Accept: application/vnd.api+json',
                  'Content-type: application/json',

            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $output = curl_exec($ch);
            $this->rawResponse = $output;
            $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $this->responseCode = $responseCode;
            $debug = curl_getinfo($ch);
            if ($responseCode !== 200) {
                if ($this->debug) {
                    throw new Exception($this->codes[$responseCode]);
                } else {
                    throw new Exception('Something wrong with service FITMOST with code '.$responseCode.' while requesting to '.$url.' with '.$method.' method. Payload: '.print_r($payload,true));
                }
            } else if ($responseCode == 200 && !$output) {
                $output = [
                    'message'=>$subRoute.($action ? '/'.$action : '') .' ok '
                ];
            }
             curl_close($ch);

//            if($method == 'GET') {
//                $output = json_decode($output,true);
//                $this->toCache($url, $payload, $output);
//    //            $output['data'][] = $url;
//                $output = json_encode($output);
//            }
            
        }
        if (@is_array(json_decode($output))) {
            return $output;
        } else if (is_array($output)) {
            return $output;
        } else {
            return json_decode($output,$this->outputAsArray);
        }
    }
    
    function __call($name, $arguments) {
        $response = $this->request($name,$arguments[0],$arguments[1],$arguments[2]);
        if ( isset($response['data']) ) {
            $this->meta = $response['meta'];
            return $response['data'];
        } else {
            return $response;
        }
    }
    
    
    
}
