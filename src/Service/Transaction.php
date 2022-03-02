<?php

namespace App\CommissionTask\Service;

class Transaction
{
    public $date;

    public $user_id;

    public $user_type;

    public $operation_type;

    public $operation_amount;

    public $operation_currency;

    private const ASSOCIATION = [
        0 => ['attribute' => 'date', 'type' => 'date'],
        1 => ['attribute' => 'user_id', 'type' => 'int'],
        2 => ['attribute' => 'user_type', 'type' => 'string'],
        3 => ['attribute' => 'operation_type', 'type' => 'string'],
        4 => ['attribute' => 'operation_amount', 'type' => 'float'],
        5 => ['attribute' => 'operation_currency', 'type' => 'string']
    ];

    public function set(array $input) : void
    {
        foreach($input as $i => $field) {
            if($this::ASSOCIATION[$i]['type'] == 'date') {
                $field = $this->date($field);
            } else {
                settype($field, $this::ASSOCIATION[$i]['type']);
            }

            $this->{$this::ASSOCIATION[$i]['attribute']} = $field;
        }
    }

    public function get() : Transaction
    {
        return $this;
    }

    private function date($date) : \DateTime 
    {
        return \DateTime::createFromFormat('Y-m-d', $date);
    }
}
