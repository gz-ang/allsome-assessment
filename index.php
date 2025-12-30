<?php
// csv filename
$filename = "allsome_interview_test_orders.csv";

// get orders from csv
$orders = getOrdersFromCSV($filename);

if ($orders && count($orders)) {

    $output = $orders;
    // Unset orders key to achieve the desired output
    unset($output['orders']);

    // Output to file
    $output_filename = "output.json";
    file_put_contents($output_filename, json_encode($output, JSON_PRETTY_PRINT));

    // Print output
    echo json_encode($output, JSON_PRETTY_PRINT);

} else {
    die("Error: No orders found in the file.");
}

/**
 * Get orders from CSV file
 * @param string $filename
 * 
 * @return array{best_selling_sku: array, orders: array, total_revenue: float}
 */
function getOrdersFromCSV(string $filename): array {
    $orders = $sku_stats = $best_seller = [];
    $max_qty = $total_revenue = 0;

    // Check if the file exists and is readable
    if (file_exists($filename) && false !== $file_handler = fopen($filename, "r")) {
        // Reads header row
        $header = fgetcsv($file_handler);
        // Reads data rows
        while ($row = fgetcsv($file_handler)) {
            // Check if order data is valid
            if (count($header) !== count($row) || $row[0] == "") {
                die("Error: Incomplete data, order " . ($row[0] ?? '?') . " is not valid.");
            }

            // Combine header and row into an array regardless of column order
            $order = array_combine($header, $row);

            // Check if data types are valid
            if (!is_numeric($order['quantity']) || !is_numeric($order['price'])) {
                die("Error: Invalid data, order " . ($row[0] ?? '?') . " is not valid.");
            }

            // Add up SKU sold quantity
            $sku = $order['sku'];
            $sku_stats[$sku] =  ($sku_stats[$sku] ?? 0) + $order['quantity'];

            // Compare and update best seller
            if ($sku_stats[$sku] > $max_qty) {
                $max_qty = $sku_stats[$sku];
                $best_seller = [
                    'sku' => $sku,
                    'total_quantity' => $max_qty
                ];
            }

            // Calculate total revenue
            $total_revenue += floatval($order['price']) * intval($order['quantity']);

            // Add order to orders array
            $orders[] = $order;
        }

        fclose($file_handler);
    } else {
        die("Error: File \"$filename\" does not exist or is not readable.");
    }
    
    // Returns $orders also assuming real life scenarios will further process it
    return [
        'orders' => $orders,
        'total_revenue' => round($total_revenue, 2),
        'best_selling_sku' => $best_seller
    ];
}