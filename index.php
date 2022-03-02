<?php

date_default_timezone_set('Asia/Tbilisi');

require 'vendor/autoload.php';

use App\CommissionTask\Service\Response;
use App\CommissionTask\Service\CSV;
use App\CommissionTask\Service\DataManager;

if(!isset($argv[1])) {
    echo Response::error([], 'File input is not provided');
    die();
}

$csv = new CSV;
$csv->load($argv[1]);
$csv->toTransactions();
$transactions = $csv->get();

$manager = new DataManager;
$manager->loadTransactions($transactions);

echo Response::success($manager->transactionFees(), 'success');
die();

?>