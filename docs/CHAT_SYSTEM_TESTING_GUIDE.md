# CHAT SYSTEM - COMPLETE TESTING GUIDE

## Overview
This guide ensures all chat system features work correctly:
1. Real-time notifications with badge count
2. Seller inbox with customer/product filtering
3. Data synchronization
4. Message persistence

---

## PART 1: BADGE COUNT & REAL-TIME NOTIFICATION

### Test 1.1: Badge Count Displays in Header
1. Login as customer
2. Go to any page
3. Check header - "Messages" menu item
4. **Expected:** No badge if 0 unread, red badge shows count if >0

### Test 1.2: Badge Updates Real-Time  
1. Open 2 browser windows:
   - Window A: Logged in as Customer
   - Window B: Logged in as Seller
2. In Window A: Go to product page and wait 5 seconds
3. In Window B: Send message to customer
4. In Window A: Wait 3-5 seconds - badge should update WITHOUT refreshing
5. **Expected:** Badge count increases automatically

### Test 1.3: Badge Disappears When Marked as Read
1. Login as customer
2. See red badge in header
3. Click "Messages" menu
4. Open a conversation
5. **Expected:** 
   - Badge disappears automatically
   - Message marked as read in database

### Test 1.4: Multiple Products - Separate Counts
1. Login as seller
2. Have customer send 2 messages:
   - Message 1 to Product A
   - Message 2 to Product B
3. Check badge should show total count (2)
4. **Expected:** Badge shows total unread across ALL products

---

## PART 2: SELLER INBOX - NEW LAYOUT

### Test 2.1: Seller Inbox Layout
1. Login as seller
2. Go to seller dashboard → Messages
3. **Expected Layout:**
   - Left panel: Customer list
   - Right panel: "Select a customer" message
   - Clean split view layout

### Test 2.2: Customer List Display
1. In seller inbox, see left panel
2. Should show:
   - Customer names
   - Last message preview
   - Time of last message
   - Unread badge (if any)
3. **Expected:** List sorted by most recent first

### Test 2.3: Click Customer - Show Products
1. Click on any customer in left list
2. Right panel should show:
   - "Products with Messages" heading
   - List of products with messages from that customer
   - Product image
   - Last message preview
   - Unread count per product
3. **Expected:** Products sorted by most recent first

### Test 2.4: Click Product - Show Conversation
1. From products list, click on a product
2. Should show conversation panel with:
   - Product name at top
   - Customer name at top
   - Messages in chronological order
   - Message textarea at bottom
   - "Back to Products" button
3. **Expected:** 
   - Messages load correctly
   - Old messages at top, new at bottom
   - Own messages (seller) = blue
   - Customer messages = gray

### Test 2.5: User Flow - Multiple Customers
**Setup:**
- Seller has 3 products: A, B, C
- 2 customers have messages:
  - Customer 1: sent to Product A and B
  - Customer 2: sent to Product C

**Test Flow:**
1. Login as seller
2. See 2 customers in left list
3. Click Customer 1:
   - Should see Product A and B
   - NOT Product C
4. Click Product A:
   - Show only messages with Customer 1 about Product A
   - NOT messages from Customer 2
5. Click back to products
6. Click Product B:
   - Show messages between Seller and Customer 1 about Product B
7. Click back to customers
8. Click Customer 2:
   - Should see ONLY Product C
9. Click Product C:
   - Show only messages with Customer 2 about Product C

**Expected:** No data mixing - each conversation is isolated by customer_id + product_id

---

## PART 3: DATA SYNCHRONIZATION

### Test 3.1: Message Query - Correct Product
1. Setup:
   - Customer sends message about Product A
   - Customer sends message about Product B
2. Seller views messages for Product A
3. **Expected:** Only messages for Product A shown, NOT Product B

### Test 3.2: Message Query - Correct Customer
1. Setup:
   - Customer 1 sends to Seller
   - Customer 2 sends to Seller
   - Both about same Product
2. Seller clicks Customer 1 → Product
3. **Expected:** Only messages from Customer 1 shown, NOT Customer 2

### Test 3.3: Message Query - Correct Seller Product
1. Setup:
   - Seller A and Seller B both have Product X
   - Customer messages Seller A about Product X
2. Login as Seller B
3. Go to messages
4. **Expected:** Seller B does NOT see Seller A's conversations

### Test 3.4: Unread Count - Accurate
1. Setup:
   - Customer sends 3 messages to Product A
   - Customer sends 2 messages to Product B
2. Seller marks Product A messages as read
3. Check Customer list:
   - Overall unread should still show 2 (from Product B)
4. Check Product B unread:
   - Should show 2
5. **Expected:** Counts are accurate, not double-counted

### Test 3.5: Auto-Refresh - Gets Latest Messages
1. Open seller conversation for Product A, Customer 1
2. In another window, login as customer
3. Send a new message
4. Wait 2-3 seconds in seller window
5. **Expected:** New message appears WITHOUT manual refresh

---

## PART 4: MESSAGE PERSISTENCE

### Test 4.1: Messages Persist After Reload
1. Seller views conversation with Customer about Product A
2. Refresh page (F5)
3. Navigate back to same conversation
4. **Expected:** All messages still there, nothing lost

### Test 4.2: Messages Persist Across Customers
1. Seller views Customer 1 conversations
2. Switch to Customer 2
3. Switch back to Customer 1
4. **Expected:** Customer 1's messages still visible, not lost

### Test 4.3: Conversation History Preserved
1. Seller and Customer exchange 10 messages (back and forth)
2. Close browser
3. Reopen and login as seller
4. Navigate to same conversation
5. **Expected:** All 10 messages still there in order

---

## PART 5: EDGE CASES

### Test 5.1: Empty Inbox
1. Clear all messages for seller (admin can do this)
2. Login as seller, go to messages
3. **Expected:** "You have no conversations yet" or empty customer list

### Test 5.2: Very Long Messages
1. Seller sends message with 1000+ characters
2. Show should be truncated in preview
3. Full message shows in conversation
4. **Expected:** Layout doesn't break

### Test 5.3: Special Characters in Messages
1. Send message with: "Hi! @#$%^&*()_+-=[]{}|;':\"<>,.?/"
2. View in conversation
3. **Expected:** Characters display correctly, not corrupted

### Test 5.4: Rapid Message Sending
1. Send 5 messages rapidly in succession
2. **Expected:** All appear in correct order, no duplicates

### Test 5.5: Network Delay Simulation
1. Open DevTools Network tab
2. Set throttling to "Slow 3G"
3. Send message
4. **Expected:** Message still sends, error if needed

---

## QUICK TEST CHECKLIST

Run this before deployment:

```
[ ] Badge shows correct number
[ ] Badge updates without page refresh
[ ] Badge disappears when messages read
[ ] Seller inbox shows customers list
[ ] Customers list sorted by recent
[ ] Click customer shows products
[ ] Products filtered by customer
[ ] Click product shows conversation
[ ] Messages load for correct product
[ ] Messages load for correct customer
[ ] Own messages are blue
[ ] Customer messages are gray
[ ] Messages auto-refresh every 2 seconds
[ ] Can send message
[ ] Message saves to database
[ ] Multiple customers don't mix
[ ] Multiple products don't mix
[ ] Unread count is accurate
[ ] Messages persist after reload
[ ] Long messages don't break layout
[ ] Special characters display
[ ] No data leaks between customers
```

---

## TESTING WITH MULTIPLE ACCOUNTS

### Setup Test Accounts:
```
Seller:
- Email: seller@test.com
- Name: Test Seller

Customer 1:
- Email: customer1@test.com
- Name: Customer One

Customer 2:
- Email: customer2@test.com
- Name: Customer Two
```

### Create Test Products:
Seller creates:
- Product A (price: $10)
- Product B (price: $20)
- Product C (price: $30)

### Run Test Sequences:
1. Customer 1 messages Seller about Product A
2. Customer 1 messages Seller about Product B  
3. Customer 2 messages Seller about Product A
4. Seller replies to all messages
5. Verify seller inbox shows both customers
6. Verify clicking each customer shows correct products
7. Verify no message mixing

---

## API ENDPOINTS TO TEST

### Get Unread Count (Public):
```
GET /messages/unread/count
Response: {"unread_count": 5}
```

### Seller - Get Customers List:
```
GET /seller/messages/api/customers
Response: [
  {
    "id": 1,
    "name": "Customer Name",
    "last_message": "Hi, ...",
    "last_message_at": "2024-03-03T10:30:00",
    "unread_count": 2
  }
]
```

### Seller - Get Customer's Products:
```
GET /seller/messages/api/customers/{customerId}/products
Response: [
  {
    "id": 1,
    "name": "Product Name",
    "last_message": "Hi, ...",
    "last_message_at": "2024-03-03T10:30:00",
    "unread_count": 1
  }
]
```

### Get Messages for Product:
```
GET /products/{productId}/messages?user_id={customerId}
Response: [
  {
    "id": 1,
    "sender_id": 2,
    "receiver_id": 3,
    "message": "Hi, ...",
    "read": false,
    "created_at": "2024-03-03T10:30:00"
  }
]
```

---

## KNOWN ISSUES & SOLUTIONS

### Issue: Badge not updating
**Solution:** Check browser console for errors, ensure /messages/unread/count endpoint works

### Issue: Products not showing
**Solution:** Ensure customer has messages for that product with this seller

### Issue: Old messages mixed with new
**Solution:** Check message query filters customer_id + product_id

### Issue: Page slow on large conversations
**Solution:** Consider pagination or limit initial messages to 50

---

## DEPLOYMENT CHECKLIST

Before going live:
- [ ] All tests pass
- [ ] No JavaScript console errors
- [ ] Badge updates smooth
- [ ] No data leaks visible
- [ ] Performance acceptable (<2s load)
- [ ] Mobile responsive layout
- [ ] All edge cases handled

---

## Important Notes

✅ **What Works:**
- Real-time message updates (2-second polling)
- Badge count updates every 3 seconds
- Customer/Product filtering works
- Data isolation is correct
- Message persistence is reliable

🔄 **Auto-Refresh Intervals:**
- Badge count: every 3 seconds
- Messages in conversation: every 2 seconds
- Can be tuned in code if needed

🔒 **Security:**
- Only seller can see their messages
- Only customer can see their conversation
- Admin cannot access private messages
- All queries filtered by user_id and product_id
