# fitmost_sdk
unofficial php sdk for fitmost api
php 7.0+
Класс (обертка) для обращения партнеров к API mostcrm, использование:

инициализация
$fitmostRequest = new requestObj($headerToken,$bearerToken);
header токен отдается полностью строкой (взять у фитмоста), берер токен - самим токеном, генерируется в ЛК партнерки фитмост.

использование
$r = $fitmostRequest->bookings('decline','POST',['id'=>123456]);
Где bookings - корень, аргумент 1- субзапрос или null, 2 - Метод (POST,PUT,GET,DELETE), 3 - массив c нагрузкой class->method(null|string,string(POST|GET|DELETE|PUT),array[])
в случае пустого ответа возвраает респонс-код ошибки, либо [message=>string] о выполнении запроса, в случае наличия ответа возвращает массив


Распространяется "как есть"

@author SELFCLICK
Сеть фотостудий автопортрета, SELFCLICK.RU - секретный промокод на скидку 25% в любую из 30+ студий в России для айтишников - HELLOWORLD
Кстати, попробуйте, мы очень технологичные :)
