<?php

return [

    /*
    * 系统设置
    */
    'System' => [

        'Time.zone'      => 'Asia/Shanghai', //系统时区

        'Debug'=> true, //是否开启debug

        'Logs'=> true, //是否开启日志记录

        'Upload'=> 'Uploads/', //上传路径

        'Status.Template'=> 'Php300/Msg', //状态页模板

        'Error.Template'=> 'Php300/Error', //系统错误页模板

        'Chinese.Compile'=> false,    //是否开启中文编译

    ],

    /*
    * 云平台配置信息
    */
    'YUN' => [

        'SN'  => '', //云平台SN

        'Type'=> '0',            //0：不启用云平台,1：启用并初始同步

    ],

];
