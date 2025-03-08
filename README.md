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
## 9️⃣ Architectural Decisions

**Module Structure & Best Practices**

* Follows Magento 2’s standard structure (app/code/SmartWork/CustomOrderProcessing).

* Uses dependency injection (DI) to avoid direct ObjectManager calls.

* Follows PSR-4 autoloading for class structures.

**Order Status Update API**

* Implements a REST API endpoint (POST /V1/orderstatus/update).

* Uses Magento API authentication (Bearer Token) for security.

* Validates order existence and status transition before updating.

**Event Observer for Order Status Change**

* Listens to the sales_order_save_after event.

* Stores order status changes in a custom database table (custom_order_status_log).

* If an order is marked as shipped, it triggers an email notification to the customer.

**Performance Optimization**

* Uses Magento repositories instead of direct SQL queries for order data.

* Optimized database operations (indexed queries, bulk inserts when needed).

* Implements logging and error handling to debug issues effectively.

**Scalability & Maintainability**

* The module is easily extendable to add more order processing logic.

* Uses separate service classes to keep the code clean and maintainable.

## 🔹 Important Notes About Order Status Updates

1️⃣ **Order Status Transition Rules**

* Every order in Magento has a state and a status.

* When updating an order’s status via API, the new status must be mapped to the order’s current state.

* If the status does not belong to the order’s state, the transition will not happen.

2️⃣ **Custom Order Status**

* To create a custom order status, you must first add it in the Magento admin panel.

* You also need to map this new status to an appropriate order state.

* Without mapping, Magento won’t recognize the custom status for transitions.
  
![image](https://github.com/user-attachments/assets/0979cca9-a4ff-44ec-a91f-a9d4b613ebf0)

3️⃣ **Forcing Status Updates**

* Although you can bypass transition rules and force a status update programmatically, it is not recommended.

* Magento maintains strict internal mappings between states and statuses, and forcing a status change may cause unexpected issues.

4️⃣ **Email Notifications**

* SMTP configuration and email server credentials are required to send order-related emails (e.g., when an order is shipped).

* The email function is implemented but commented out to avoid errors if SMTP is not configured.

* Instead, a log entry confirms when the email function is triggered upon status change.

5️⃣ **Observer Handling**

* When the API updates an order status, an observer listens for changes and logs them in a custom database table.

* This observer only runs when the API is called, not when an order is placed via the frontend.

* This ensures that only API-triggered status changes are logged, as per the module’s requirements.
* 
## 🔹 Few Important module flow understanding from images
* **After the Order save via API, the order status updates the data that goes into the custom log table**
![download (2)](https://github.com/user-attachments/assets/ca9b60d6-e58c-4e25-9cde-4fdc9ddb58d4)

* **If you assign some new status for an order and for that order's state, if it does not have the mapping of that status, the transition is not allowed. For that you have to create a new status from the admin panel**
![Screenshot 2025-03-08 145543](https://github.com/user-attachments/assets/5bc59aee-5f26-4fa7-91da-9fa408af00f4)

* **You have to get the Bearer token for admin as this api requires admin access authorization**
![Screenshot 2025-03-08 145508](https://github.com/user-attachments/assets/733c8bba-9f79-4d2d-8b9c-b9f5f7558a57)

* **If you access the API short of token then you get the below error.**
![Screenshot 2025-03-08 144213](https://github.com/user-attachments/assets/e2148c68-0b79-4337-87fd-bcf38a1e281a)
