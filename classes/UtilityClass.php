<?php

class UtilityClass {
	function __construct(){
		throw new Exception(get_class($this).'is a static class!');
	}
}

?>