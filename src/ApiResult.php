<?php
declare(strict_types=1);

namespace PhpRest2;

class ApiResult 
{
    public int $ret = 1;

    public string $msg = 'success';

    public $data;

    public function __construct($ret, $msg) {
        $this->ret = $ret;
        $this->msg = $msg;
    }

    public static function success($data = null): ApiResult {
        $result = new ApiResult(1, 'success');
        $result->data = $data;
        return $result;
    }

    public static function error($msg, $ret = -1): ApiResult {
        return new ApiResult($ret, $msg);
    }
}