# fitmost_sdk
unofficial php sdk for fitmost api
php 7.0+

официальная документация к API: https://api.mostcrm.ru/api/documentation/

Класс (обертка) для обращения партнеров к API mostcrm, использование:

## инициализация
$fitmostRequest = new requestObj($headerToken,$bearerToken);

header токен отдается полностью строкой (взять у фитмоста), берер токен - самим токеном, генерируется в ЛК партнерки фитмост.

## использование
**$r = $fitmostRequest->bookings('decline','POST',['id'=>123456]);**

**Где:**

  bookings - корень запроса
  
  аргумент 1- субзапрос или null
  
  2 - Метод (POST,PUT,GET,DELETE)
  
  3 - массив c нагрузкой 

**$fitmostRequest = new requestObj($headerToken,$bearerToken);**

**$ftimostRequest->class->method(null|string,string(POST|GET|DELETE|PUT),array[])**

в случае пустого ответа возвращает респонс-код ошибки, либо [message=>string] о выполнении запроса, в случае наличия ответа возвращает массив, в случае response-code отличного от 200 бросает исключение. В случае установки параметра private bool $debug = true в исключение будут выброшены все данные по выполненному запросу.

Передача массива для GET запроса создает urlencoded параметры GET

для GET запросов по умолчанию включено кеширование. Отключается установкой флага $fitmostRequest->noCache = true; 

Также присутствует свойство $fitmost->meta где отображаются данные ответа (пагинация и прочее)


Распространяется "как есть"
**SELFCLICK**

Сеть фотостудий автопортрета, SELFCLICK.RU - секретный промокод на скидку 25% в любую из 30+ студий в России для айтишников - **HELLOWORLD**
Кстати, попробуйте, мы очень технологичные :)
