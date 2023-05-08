<?php
declare(strict_types=1);

namespace PhpRest2;

class ApiResult 
{
    public int $ret = 1;

    public string $msg = 'success';

    public mixed $data;

    public function __construct(int $ret, string $msg) {
        $this->ret = $ret;
        $this->msg = $msg;
    }

    public static function success(mixed $data = null): ApiResult {
        $result = new ApiResult(1, 'success');
        $result->data = $data;
        return $result;
    }

    public static function error(string $msg, int $ret = -1): ApiResult {
        return new ApiResult($ret, $msg);
    }
}