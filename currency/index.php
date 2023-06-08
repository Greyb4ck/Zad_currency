<?php
session_start();
require_once 'CurrencyConverter.php';
require_once 'CurrencyAPI.php';
require_once '../inc/dbcon.php';

$currencyAPI = new CurrencyAPI($conn);
$currencyAPI->getCurrencyTable();

$currencyConverter = new CurrencyConverter($conn);

// Handle form submission
if (isset($_POST['exchange'])) {
    $currencyConverter->processCurrencyForm();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Currency Converter</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Currency Converter</h1>

    <div id="currency-form">
        <?php
        $currencyConverter->displayCurrencyForm();
        ?>
    </div>

    <div id="currency-list">
        <h2>Currency</h2>
        <?php
        $currencyAPI->generateCurrencyList();
        ?>
    </div>

    <div id="exchange-history">
        <h2>Exchange History</h2>
        <?php
        $currencyConverter->generateExchangeHistoryList();
        ?>
    </div>
</body>
</html>
