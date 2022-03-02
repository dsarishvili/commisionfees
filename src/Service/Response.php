<?php

namespace App\CommissionTask\Service;

class Response
{
    public const SUCCESS = 200;
    public const ERROR = 500;

    public static function success(array $data, string $message = '') : string
    {
        return self::json(self::SUCCESS, $data, $message);
    }

    public static function error(array $data, string $message = '') : string
    {
        return self::json(self::ERROR, $data, $message);
    }

    protected static function json(int $status, array $data, string $message = '') : string
    {
        return json_encode([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ]);
    }
}
