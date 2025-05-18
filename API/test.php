<?php
// Test file to demonstrate API usage

// Function to make API requests
function makeRequest($endpoint, $method = 'GET', $data = null) {
    $url = "http://localhost/puss'n_till_kiosk/API/" . $endpoint;
    
    $options = [
        'http' => [
            'method' => $method,
            'header' => 'Content-Type: application/json',
            'content' => $data ? json_encode($data) : null
        ]
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    return json_decode($result, true);
}

// Test getting menu items
echo "Testing GET /menu.php\n";
$menu = makeRequest('menu.php');
print_r($menu);
echo "\n";

// Test creating a new menu item
echo "Testing POST /menu.php\n";
$newItem = [
    'name' => 'Test Item',
    'price' => 9.99,
    'category' => 'Test Category',
    'description' => 'This is a test item'
];
$result = makeRequest('menu.php', 'POST', $newItem);
print_r($result);
echo "\n";

// Test creating a new order
echo "Testing POST /orders.php\n";
$newOrder = [
    'items' => [
        [
            'item_id' => 1,
            'quantity' => 2,
            'price' => 9.99
        ]
    ]
];
$result = makeRequest('orders.php', 'POST', $newOrder);
print_r($result);
echo "\n";

// Test getting orders
echo "Testing GET /orders.php\n";
$orders = makeRequest('orders.php');
print_r($orders);
echo "\n";
?> 