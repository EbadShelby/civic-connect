#!/bin/bash

echo "Testing registration API endpoint..."
echo ""

# Test data
TEST_EMAIL="testuser_$(date +%s)@gmail.com"
REAL_EMAIL="anothershelby7@gmail.com"

echo "Test 1: Register with test email"
echo "=================================="
curl -X POST http://localhost/api/users/register \
  -H "Content-Type: application/json" \
  -d @- << EOF
{
  "email": "$TEST_EMAIL",
  "password": "TestPassword123",
  "first_name": "Test",
  "last_name": "User",
  "phone": "1234567890"
}
EOF

echo -e "\n\n"

echo "Test 2: Register with real Gmail address"
echo "========================================="
curl -X POST http://localhost/api/users/register \
  -H "Content-Type: application/json" \
  -d @- <<  EOF
{
  "email": "$REAL_EMAIL",
  "password": "TestPassword123",
  "first_name": "Real",
  "last_name": "User",
  "phone": "9876543210"
}
EOF

echo -e "\n\n"
echo "Check error logs for any \"CRITICAL\" messages:"
echo "==============================================="
sudo journalctl -u httpd -n 50 --no-pager | grep "CRITICAL" || echo "No critical errors found in httpd logs"

echo -e "\nCheck PHP error log:"
php -r "echo 'PHP error_log location: ' . ini_get('error_log') . PHP_EOL;"
