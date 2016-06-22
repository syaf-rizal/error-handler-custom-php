<?php
	include("class.error_handler.php"); 
	$handler = new error_handler("127.0.0.1", 1, 6, TRUE, NULL);

	$handler->smtp_host("smtp.gmail.com");
	$handler->smtp_port("587");
	$handler->smtp_username("syafrizal.asgard@gmail.com");
	$handler->smtp_password("Proliant8500");
	$handler->smtp_from("syafrizal.asgard@gmail.com");
	$handler->smtp_address("syafrizal@edi-indonesia.co.id");
	/*$handler->smtp_address("isol.retro@gmail.com");*/

	/*$handler->smtp_host("mail2.edi-indonesia.co.id");
	$handler->smtp_port("25");
	$handler->smtp_from("syafrizal@edi-indonesia.co.id");
	$handler->smtp_address("syafrizal.asgard@gmail.com");*/

	set_error_handler(array(&$handler, "handler"));
	
	if(!mysqli_connect("localhost","obat","1234")){   
		trigger_error('Can not connect to database',E_ERROR); 
	}
?>