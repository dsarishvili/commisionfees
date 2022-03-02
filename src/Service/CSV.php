<?php

namespace App\CommissionTask\Service;

use App\CommissionTask\Service\Response;
use App\CommissionTask\Service\Transaction;

class CSV
{
    private $file;
    private $data = [];

    public function load(string $input) : void
    {
        $this->validate($input);

        $this->file = $input;

        $this->parse();
    }

    public function toTransactions() : void
    {
        $data = [];

        foreach($this->data as $item) {
            $transaction = new Transaction;
            $transaction->set($item);
            $data[] = $transaction->get();
        }

        $this->data = $data;
    }

    public function get() : array
    {
        return $this->data;
    }

    private function parse() : void
    {
        $data = [];
        $i = 0;
        if (($handle = fopen($this->file, "r")) !== FALSE) {
            while (($csv = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $num = count($csv);
                for ($c = 0; $c < $num; $c++) {
                    $data[$i][$c] = $csv[$c];
                }
                $i++;
            }
            fclose($handle);
        }

        $this->data = $data;
    }

    private function validate(string $input) : void
    {
        try {
            if(!file_exists($input)) {
                $errorMessage = 'File does not exist';
            }
    
            if(isset(pathinfo($input)['extension']) && pathinfo($input)['extension'] !== 'csv') {
                $errorMessage = 'File is not CSV';
            }

            if(isset($errorMessage)) {
                throw new \Exception($errorMessage);
            }
        }  catch (\Exception $e) {
            echo Response::error([], $e->getMessage());
            die();
        }
    }
}
