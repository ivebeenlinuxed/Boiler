<?php
namespace Controller;

class Home {
	function index() {
		echo "I'm using in total ".((memory_get_usage()-START_MEM)/1024/100)."MB of memory :)";
	}
}