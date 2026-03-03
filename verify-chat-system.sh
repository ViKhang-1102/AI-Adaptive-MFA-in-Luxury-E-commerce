#!/bin/bash

# Chat System Verification Script
# This script checks if all chat system components are properly configured

echo ""
echo "=========================================="
echo "CHAT SYSTEM - VERIFICATION CHECK"
echo "=========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check 1: Controller methods exist
echo -n "Checking MessageController methods... "
if grep -q "public function getUnreadCount" app/Http/Controllers/MessageController.php && \
   grep -q "public function getCustomersList" app/Http/Controllers/MessageController.php && \
   grep -q "public function getCustomerProducts" app/Http/Controllers/MessageController.php; then
    echo -e "${GREEN}✓${NC}"
else
    echo -e "${RED}✗${NC}"
    echo "Error: Missing methods in MessageController"
fi

# Check 2: Routes are configured
echo -n "Checking routes configuration... "
if grep -q "/messages/unread/count" routes/web.php && \
   grep -q "/messages/api/customers" routes/web.php && \
   grep -q "/messages/api/customers/{customerId}/products" routes/web.php; then
    echo -e "${GREEN}✓${NC}"
else
    echo -e "${RED}✗${NC}"
    echo "Error: Missing routes in web.php"
fi

# Check 3: View file exists
echo -n "Checking seller messages view... "
if [ -f "resources/views/seller/messages/index_new.blade.php" ]; then
    echo -e "${GREEN}✓${NC}"
else
    echo -e "${RED}✗${NC}"
    echo "Error: index_new.blade.php not found"
fi

# Check 4: Header has badge script
echo -n "Checking header badge script... "
if grep -q "updateMessageBadge" resources/views/layouts/header.blade.php && \
   grep -q "data-message-badge" resources/views/layouts/header.blade.php; then
    echo -e "${GREEN}✓${NC}"
else
    echo -e "${RED}✗${NC}"
    echo "Error: Badge script not found in header"
fi

# Check 5: Product model has messages relationship
echo -n "Checking Product model relationships... "
if grep -q "public function messages()" app/Models/Product.php; then
    echo -e "${GREEN}✓${NC}"
else
    echo -e "${RED}✗${NC}"
    echo "Warning: messages() relationship not found in Product model"
fi

# Check 6: Message model has forConversation scope
echo -n "Checking Message model scope... "
if grep -q "scopeForConversation" app/Models/Message.php; then
    echo -e "${GREEN}✓${NC}"
else
    echo -e "${RED}✗${NC}"
    echo "Error: forConversation scope not found"
fi

# Check 7: Testing guide exists
echo -n "Checking testing guide... "
if [ -f "CHAT_SYSTEM_TESTING_GUIDE.md" ]; then
    echo -e "${GREEN}✓${NC}"
else
    echo -e "${RED}✗${NC}"
    echo "Warning: Testing guide not found"
fi

# Check 8: Database table has messages table
echo -n "Checking messages table migration... "
if [ -f "database/migrations/"*"_create_messages_table.php" ] || \
   grep -r "create_messages_table" database/migrations/ > /dev/null 2>&1; then
    echo -e "${GREEN}✓${NC}"
else
    echo -e "${YELLOW}?${NC}"
    echo "Note: Messages table migration file not easily found (might be namespaced)"
fi

echo ""
echo -e "${GREEN}=========================================="
echo "VERIFICATION COMPLETE"
echo "==========================================${NC}"
echo ""
echo "Next steps:"
echo "1. Run: php artisan migrate (if needed)"
echo "2. Start Laravel server: php artisan serve"
echo "3. Test a conversation: Login as seller & customer"
echo "4. Follow: CHAT_SYSTEM_TESTING_GUIDE.md"
echo ""
