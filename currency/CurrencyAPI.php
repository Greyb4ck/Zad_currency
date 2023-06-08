<?php
class CurrencyAPI {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }
    // Function to drop the currency table from the database
    private function dropCurrencyTable() {
        global $conn;

        $sql = "DROP TABLE Currency";

        // Drop the 'Currency' table
        if (!mysqli_query($conn, $sql)) {
            die('Error deleting Currency table: ' . mysqli_error($conn));
        }
    }

    // Function to create the currency table in the database
    private function createCurrencyTable() {
        global $conn;

        // If currency table already exists, return
        if ($this->checkIfCurrencyTableExists()) {
            return;
        }

        $sql = "CREATE TABLE Currency (
            ID INT AUTO_INCREMENT PRIMARY KEY,
            name_currency VARCHAR(255) CHARACTER SET utf16 COLLATE utf16_polish_ci,
            code_currency VARCHAR(255) CHARACTER SET utf16 COLLATE utf16_polish_ci,
            mid_currency DECIMAL(10,4),
            date_currency DATE
        )";

        // Create the 'Currency' table
        if (!mysqli_query($conn, $sql)) {
            die('Error creating table: ' . mysqli_error($conn));
        }
    }

    // Function to insert currency data into the database
    private function insertCurrencyData($rates, $effectiveDate) {
        global $conn;

        foreach ($rates as $rate) {
            $currency = mysqli_real_escape_string($conn, $rate['currency']);
            $code = mysqli_real_escape_string($conn, $rate['code']);
            $mid = $rate['mid'];

            $insertSql = "INSERT INTO Currency (name_currency, code_currency, mid_currency, date_currency) 
                          VALUES ('$currency', '$code', $mid, '$effectiveDate')";

            // Insert the currency data into the 'Currency' table
            if (mysqli_query($conn, $insertSql)) {
                //echo "Inserted currency: $currency\n";
            } else {
                echo "Error inserting currency: " . mysqli_error($conn) . "\n";
            }
        }
    }

    // Function to fetch currency data from the API and insert it into the database
    public function getCurrencyTable() {
        $val = $this->checkIfCurrencyTableExists();

        // If currency table already exists, drop it
        if ($val !== FALSE) {
            $this->dropCurrencyTable();
        }

        // Fetch data from the API
        $jsonData = $this->fetchDataFromAPI();

        // Decode the JSON data
        $data = json_decode($jsonData, true);

        // Get the first item from the data array
        $item = $data[0];

        // Extract the effective date and rates from the item
        $effectiveDate = $item['effectiveDate'];
        $rates = $item['rates'];

        // Create the currency table in the database
        $this->createCurrencyTable();

        // Insert the currency data into the table
        $this->insertCurrencyData($rates, $effectiveDate);
    }
    // Fetches currency data from the database.
    public function fetchCurrencyData() {
        global $conn; 

        $sql = "SELECT * FROM Currency";
        $result = $conn->query($sql);

        $currencyData = array();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $currencyData[] = $row;
            }
        }

        return $currencyData;
    }
    // Generates a currency list in HTML format.
    public function generateCurrencyList() {
        $currencyData = $this->fetchCurrencyData();

        $html = '<table>';
        $html .= '<tr><th>ID</th><th>Name</th><th>Code</th><th>Mid</th><th>Date</th></tr>';

        foreach ($currencyData as $currency) {
            $id = $currency['ID'];
            $name = $currency['name_currency'];
            $code = $currency['code_currency'];
            $mid = $currency['mid_currency'];
            $date = $currency['date_currency'];

            $html .= "<tr><td>$id</td><td>$name</td><td>$code</td><td>$mid</td><td>$date</td></tr>";
        }

        $html .= '</table>';

        echo $html;
    }

    // Function to check if the currency table exists in the database
    public function checkIfCurrencyTableExists() {
        // Check if the table 'Currency' exists
        $result = mysqli_query($this->conn, "SHOW TABLES LIKE 'Currency'");
        return mysqli_num_rows($result) > 0;
    }



    // Function to fetch data from the API
    private function fetchDataFromAPI() {
        $url = 'https://api.nbp.pl/api/exchangerates/tables/a/?format=json';

        // Fetch JSON data from the API
        $jsonData = file_get_contents($url);

        if ($jsonData === false) {
            die('Error retrieving data from the API.');
        }

        return $jsonData;
    }

    


}
