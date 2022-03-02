<?php

namespace App\CommissionTask\Service;

use App\CommissionTask\Service\Transaction;

class DataManager
{
    private $transactions = [];

    private const DEPOSIT_FEE = 0.03;
    private const WITHDRAW_PRIVATE_FEE = 0.3;
    private const WITHDRAW_BUSINESS_FEE = 0.5;
    private const CHARGE_FREE_LIMIT = 1000;


    public function loadTransactions(array $transactions) : void
    {
        $this->transactions = $transactions;
    }

    public function getTransactions() : array
    {
        return $this->transactions;
    }

    private function toUserTransactionsByWeek() : void
    {
        $this->toUserTransactions();

        $data = [];
        foreach($this->transactions as $user_id => $transactions) {
            $data[$user_id] = $this->transactionsByWeek($transactions);
        }

        $this->transactions = $data;
    }

    private function toUserTransactions() : void
    {
        $data = [];
        foreach ($this->transactions as $i => $transaction) {
            if(!isset($data[$transaction->user_id])){
                $data[$transaction->user_id] = [];
            }

            // set temporary id
            $transaction->id = $i+1;
            $data[$transaction->user_id][] = $transaction;
        }

        $this->transactions = $data;
    }

    private function transactionsByWeek(array $transactions) : array
    {
        $data = []; 
        foreach ($transactions as $transaction) {
            $identifier = $transaction->date->format('oW');
        
            if(!isset($data[$identifier])){
                $data[$identifier] = [];
            }
        
            $data[$identifier][] = $transaction;
            usort($data[$identifier], fn ($a, $b) => strtotime($a->date->format('Y-m-d')) - strtotime($b->date->format('Y-m-d')));
        }
        
        return $data;
    }

    public function transactionFees() : array
    {
        $this->toUserTransactionsByWeek();
        $fees = [];

        foreach($this->transactions as $user_id => $weekly) {
            foreach($weekly as $week => $transactions) {

                $weeklyChargeFreeLeft = $this::CHARGE_FREE_LIMIT;
                $weeklyChargeFreeLeftCounter = 0;
                foreach($transactions as $transaction) {
    
                    $operation_currency = $transaction->operation_currency;
    
                    if($transaction->operation_type == 'deposit') {
    
                        $fee = ceil(($transaction->operation_amount * $this::DEPOSIT_FEE / 100) * 100) / 100;
                    
                    } elseif($transaction->operation_type == 'withdraw') {
    
                        $operation_amount = $transaction->operation_amount;
    
                        if($transaction->user_type == 'private') {
                            
                            if(strtoupper($transaction->operation_currency) !== 'EUR') {
                                $currencyRate = $this->currencyRate($transaction->operation_currency);
                                $operation_amount = $operation_amount / $currencyRate;
                                $operation_currency = 'EUR';
                            }
    
                            if($weeklyChargeFreeLeftCounter < 3) {
    
                                if($operation_amount <= $weeklyChargeFreeLeft) {
                                    $fee = 0;
                                    $weeklyChargeFreeLeft -= (float) $operation_amount;
                                } else {
                                    $fee = ceil((($operation_amount - $weeklyChargeFreeLeft) * $this::WITHDRAW_PRIVATE_FEE / 100) * 100) / 100;
                                    $weeklyChargeFreeLeft = 0;
                                }
        
                            } else {
                                $fee = ceil(($operation_amount * $this::WITHDRAW_PRIVATE_FEE / 100) * 100) / 100;
                            }
    
                            $weeklyChargeFreeLeftCounter++;
                        } elseif($transaction->user_type == 'business') {
                            $fee = ceil(($transaction->operation_amount * $this::WITHDRAW_BUSINESS_FEE / 100) * 100) / 100;
                        }
    
                    }
                
                    $fees[$transaction->id] = number_format($fee, 2) . ' ' . $operation_currency;
                }
    
            }
        }

        return $fees;
    }

    private function currencyRate($currency) : float
    {
        $ch = curl_init();
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, 'https://developers.paysera.com/tasks/api/currency-exchange-rates');
        $result = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($result);

        return $result->rates->{strtoupper($currency)};
    }
}
