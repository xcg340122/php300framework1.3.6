<?php

namespace Main\Action;

use \Libs\Deal\System;
class App_class extends System{
	
	public function index()
	{
		Assign('Welcome','更轻便,更强大,更灵活,让开发更简单!');
		
		Show('Php300/Welcome/index');
	}
	
}