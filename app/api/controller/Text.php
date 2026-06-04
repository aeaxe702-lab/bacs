<?php

namespace app\api\controller;

use app\api\validate\AppNameValidate;
use app\service\Text as TextService;

class Text extends Base
{
    public function notice (){
        $data = (new AppNameValidate()) -> goCheck();
        return self::returnData(TextService::getTextByAppName($data['app_name']));
    }

}
