<?php
namespace Controller;

class Home {
	function index() {
		\Core\Router::loadView("landing");
	}
}