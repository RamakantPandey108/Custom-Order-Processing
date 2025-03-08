# 📘 Order Status Update API

## 🔹 Overview
This API allows external systems to update the status of an order in Magento 2.

---

## 1️⃣ Installation Steps

### Move the Module Folder
Move the module folder to the Magento `app/code` directory:
```
app/code/SmartWork/CustomOrderProcessing
```

### Run Magento Commands
Navigate to your Magento installation folder and run the following commands:
```bash
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento indexer:reindex
php bin/magento cache:flush
php bin/magento cache:clean
```

### Verify Module Installation
Check if the module is enabled:
```bash
php bin/magento module:status
```

### Set Permissions (If Needed)
If you encounter permission issues, run:
```bash
chmod -R 777 var/ pub/ generated/
```

---

## 2️⃣ API Endpoint
```
POST http://yourmagentodomain/rest/V1/orderstatus/update
```

---

## 3️⃣ Request Headers
| Header           | Value                      |
|-----------------|--------------------------|
| Content-Type    | application/json         |
| Authorization   | Bearer {Access_Token}    |

---

## 4️⃣ Request Body (JSON Format)
```json
{
    "increment_id": "100000001",
    "status": "processing"
}
```

| Parameter      | Type   | Description                           |
|---------------|--------|---------------------------------------|
| `increment_id` | string | Order Increment ID (Order Number)   |
| `status`       | string | New Order Status (e.g., shipped, processing, complete) |

---

## 5️⃣ Authentication
This API requires authentication via a Bearer Token.

### Get Admin Token (POST request)
```
POST http://mage2rock.magento.com/rest/V1/integration/admin/token
```
#### Request Body
```json
{
    "username": "admin",
    "password": "your_admin_password"
}
```
#### Response Example
```json
"your_generated_token_here"
```
Use this token in the `Authorization` header for all API requests.

---

## 6️⃣ Response Example
### ✅ Success Response (200 OK)
```json
{
    "status": true,
    "message": "Order status updated successfully."
}
```
### ❌ Error Response
```json
{
    "status": false,
    "message": "Order does not exist or status transition not allowed."
}
```

---

## 7️⃣ How to Use in Postman
1. Open **Postman**.
2. Select **POST** request.
3. Enter the API URL: `http://mage2rock.magento.com/rest/V1/orderstatus/update`
4. Go to the **Headers** tab and add:
   - **Key:** `Content-Type`, **Value:** `application/json`
   - **Key:** `Authorization`, **Value:** `Bearer your_generated_token`
5. Go to the **Body** tab and select **raw**, then paste the JSON request body.
6. Click **Send**.

---

## 8️⃣ Magento CLI Commands (After API Updates)
To ensure the changes reflect correctly in Magento, run:
```bash
php bin/magento cache:flush
php bin/magento cache:clean
php bin/magento indexer:reindex
```

---

📘 Architectural Decisions
Module Structure & Best Practices

The module follows Magento 2’s standard structure (app/code/Vendor/CustomOrderProcessing).
Uses dependency injection (DI) to avoid direct ObjectManager calls.
Follows PSR-4 autoloading for class structures.
Order Status Update API

A REST API endpoint (POST /V1/orderstatus/update) allows external systems to update the order status.
Uses Magento API authentication (Bearer Token) for security.
Validates order existence and status transition before updating.
Event Observer for Order Status Change

Listens to sales_order_save_after event.
Stores order status changes in a custom database table (custom_order_status_log).
If an order is marked as shipped, it triggers an email notification using Magento's email system.
Performance Optimization

Uses Magento repositories instead of direct SQL queries to interact with orders.
Optimized database operations (indexed queries, bulk inserts when needed).
Uses logging and error handling to debug issues effectively.
Scalability & Maintainability

The module can be extended easily to add more order processing logic.
Separate service classes ensure the code remains clean and maintainable.

---

🔹 Important Notes About Order Status Updates
1️⃣ Order Status Transition Rules

Every order in Magento has a state and a status.
If you try to update an order's status via API, the new status must be mapped to the order’s current state.
If the status does not belong to the order’s state, the transition will not happen.

2️⃣ Custom Order Status

If you need a custom order status, you must first create it in the Magento admin panel.
You also need to map this new status to an appropriate order state.
Without mapping, Magento won’t recognize the custom status for transitions.

![alt text](image.png)

3️⃣ Forcing Status Updates

While it is possible to bypass these rules(1,2) and force a status update programmatically, it is not recommended.
Magento maintains strict internal mappings between states and statuses, and forcing a status change may cause unexpected issues in future processing.

4️⃣ Email Notifications

To send order-related emails (e.g., order shipped), SMTP configuration and email server credentials are required.
In this module, the email function is implemented but commented out to avoid errors if SMTP is not configured.
Instead, a log entry is created to confirm when the email function is triggered upon status change(shipped one).

5️⃣ Observer Handling

When the API updates an order status, an observer listens for changes and logs(database entry) them in a custom table.
This observer runs only when the API is called, not when an order is placed via the frontend.
This ensures that only API-triggered status changes are logged, as per the module’s requirements.

