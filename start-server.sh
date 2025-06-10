#!/bin/bash

# Start PHP development server
echo "Starting PHP development server on port 8000..."
php -S localhost:8000 -t public/ &
SERVER_PID=$!

# Wait for server to start
sleep 2

# Run API tests
echo "Running API tests..."
php tests/api_test.php

# Cleanup
echo "Stopping server..."
kill $SERVER_PID

echo "Done!"
