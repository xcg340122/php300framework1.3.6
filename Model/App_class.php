<?php

/**
* PHP300Framework - App控制器
*/

class App_class Extends System_class {

    public function Index() {
    	
        Assign('Welcome', '更轻便,更强大,更灵活,让开发更简单!');
        
        Show('Built/index');
    }
}

?>