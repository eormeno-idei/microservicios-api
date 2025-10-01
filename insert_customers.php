<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Customer;

$customers = [
    [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'jdoe@hotmail.com',
    ],
    [
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'email' => 'jsmith@hotmail.com',
    ],
    [
        'first_name' => 'Alice',
        'last_name' => 'Johnson',
        'email' => 'ajohnson@hotmail.com',
    ],
    [
        'first_name' => 'Maru',
        'last_name' => 'Scheffer',
        'email' => 'mscheffer@hotmail.com',
    ],
    [
        'first_name' => 'Martín',
        'last_name' => 'Varela Ochoa',
        'email' => 'mvarelochoa@hotmail.com',
    ]
];

foreach ($customers as $customerData) {
    Customer::updateOrCreate(
        ['email' => $customerData['email']],
        $customerData
    );
}

echo "Customers inserted or updated successfully.\n";

$listado = Customer::all();
foreach ($listado as $customer) {
    echo "$customer\n";
}

echo "Total customers: " . $listado->count() . "\n";
