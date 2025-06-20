#!/bin/bash

# Set script directory as current working directory
cd "$(dirname "$0")"

# Display header
echo "==============================================="
echo "    Natuna Jaya Payment Notification Tests    "
echo "==============================================="
echo ""

# Run the test script
php test_all_payment_notifications.php

# Check status
STATUS=$?
if [ $STATUS -eq 0 ]; then
    echo ""
    echo "Tests completed successfully!"
else
    echo ""
    echo "Tests failed with status code: $STATUS"
fi

echo ""
echo "Press Enter to exit..."
read
