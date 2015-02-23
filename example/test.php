<?php
	require 'oddqc.class.php';
	require 'ol.class.php';
	$o = new ol('dummy_access_key', 'dummy_access_secret', 'pek2', 'i-foobar');
	var_dump($o->start_instance());