<?php

namespace Main\Action;

class App_class
{
    public function Index()
    {
        Assign('Welcome', '更轻便,更强大,更灵活,让开发更简单!');

        Show('Php300/Welcome/index');
    }
}
