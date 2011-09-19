<?php
namespace System\Library;

abstract class Security {
	const STRIP_HTML = 0x01;
	const STRIP_SHELL = 0x02;
	const STRIP_SQL = 0x04;
}