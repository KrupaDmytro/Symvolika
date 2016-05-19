<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$outp = '[ {"name":"Компас US-Typ, металевий корпус","icon":"compass.jpg", "desc":"Виробник:: Max Fuchs", "price":145},'.
			'{"name":"Рюкзак мисливський: РО-1т","icon":"hunting.jpg", "desc":"Виробник: ARMPOLIS (Україна)", "price":1269},'.
			'{"name":"Прапор Украіна з бахромою і тризубом","icon":"flag.jpg", "desc":"Виробник: Україна", "price":112.88},'.
			'{"name":"Значок Українська символіка - Метелик с/ж з тризубом", "icon":"butterflyFlag.jpg", "desc":"Виробник: Україна", "price":30} ]';
echo($outp);
?>