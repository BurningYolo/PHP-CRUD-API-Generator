<?php
// URL of the API
$url = 'http://localhost/hishuanigami/tbl_hishuanigami_crud_api.php?action=create';

// Parameters to send
$data = [
    'username' => 'testuser',
    'email' => 'testuser@example.com',
];

// Initialize cURL
$ch = curl_init($url);

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

// Execute the request and capture the response
$response = curl_exec($ch);

// Check for errors
if (curl_errno($ch)) {
    echo 'cURL Error: ' . curl_error($ch);
} else {
    // Print the response from the server
    echo 'Response: ' . $response;
}

// Close cURL session
curl_close($ch);
?>