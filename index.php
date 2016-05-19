<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$outp = '[ {"name":"Compass","icon":"compass.jpg", "desc":"The compact compass for climbers.", "price":2},'.
			'{"name":"Hunting backpack","icon":"hunting.jpg", "desc":"This is a nice backpack for hunters.", "price":30},'.
			'{"name":"Flag","icon":"flag.jpg", "desc":"Ukrainian flag.", "price":4},'.
			'{"name":"Butterfly", "icon":"butterflyFlag.jpg", "desc":"Nice ukrainian flag.", "price":1.1} ]';
echo($outp);
?>