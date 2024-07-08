<?php

namespace App\Response;

class ResponseArray
{
    public function returnArray($status, $message, $data=null)
    {
        return (object)[
            'status' => $status,
            'message' => $message,
            'data' => $data
        ];

    }
}
