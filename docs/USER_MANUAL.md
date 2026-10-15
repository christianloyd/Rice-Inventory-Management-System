# RICE INVENTORY AND SALES MANAGEMENT SYSTEM (RISMS)
## USER MANUAL & OPERATING GUIDE
**Store Operations & Point of Sale (POS) Reference**
**Version:** 2.0 (Official Release)  
**System Name:** RISMS / MAE CONSUMER Rice Trading  
**Applicability:** All System Users (Administrator, Cashier / Staff, Viewer)

---

## TABLE OF CONTENTS

1. **System Introduction & Overview**
   - 1.1 Purpose of the System
   - 1.2 Core System Capabilities
   - 1.3 User Roles & Permission Matrix (Admin, Staff, Viewer)
   - 1.4 Technical Requirements & Access URL

2. **Getting Started & Authentication**
   - 2.1 Accessing the Application
   - 2.2 Logging In
   - 2.3 Session Security & Automatic Timeout
   - 2.4 Signing Out Safely

3. **User Interface & Navigation Guide**
   - 3.1 Overview of the Navigation Sidebar
   - 3.2 Visual Icons & Active Page Indicators
   - 3.3 Top Header, User Profile & System Confirmations

4. **Module 1: Dashboard**
   - 4.1 Real-Time KPI Metric Cards (Active Variants, Low Stock, Today's Sales, Sacks Sold, MTD Sales)
   - 4.2 Low Stock Alert Panel
   - 4.3 Recent Sales Feed & Live Status

5. **Module 2: Sales (POS & Rice Variant Exchange)**
   - 5.1 Sales Register & Transaction History
   - 5.2 Searching & Filtering Sales (Completed, Cancelled, Exchanged)
   - 5.3 Point of Sale (POS) — Step-by-Step New Sale Guide
     - 5.3.1 Adding Cart Items & Variant Selection
     - 5.3.2 Quantity Ordering & Live Stock Checking
     - 5.3.3 Line Item Fulfillment Types (Release Now, TBT, Utang)
     - 5.3.4 The "Taken Now" (Partial Pickup) Field & Logic
     - 5.3.5 Automatic TBT Queueing for Sacks Left at the Store
     - 5.3.6 Automatic Wholesale Discount (₱10/sack on 10+ sacks for eligible variants)
     - 5.3.7 Manual Custom Peso Discount
     - 5.3.8 Payment Methods (Cash Tendered/Change vs Cheque Credit)
     - 5.3.9 After-Hours / Backdated Sale Entry & Mandatory Remarks
     - 5.3.10 Completing Checkout & Official Receipt / Control Sheet Printing
   - 5.4 Rice Variant Exchange ("The Other Brought Rice" / Customer Returns)
     - 5.4.1 Understanding Exchange Business Rules (Strict No Cash Refund Policy)
     - 5.4.2 Step 1: Locating the Original Completed Sale
     - 5.4.3 Step 2: Reviewing Original Purchase Details & Items
     - 5.4.4 Step 3: Selecting Replacement Variant & Quantity
     - 5.4.5 Step 4: Value Balancing & Auto-Adjusted Quantities
     - 5.4.6 Step 5: Handling the Difference to Collect & Cash Tendered
     - 5.4.7 Step 6: Atomic Stock Adjustment (SALE_REVERSAL of Returned Rice & SALE of New Rice)
     - 5.4.8 Step 7: Auto-Releasing Old Pending TBT Records
     - 5.4.9 Step 8: Generating the ★ EXCHANGE TRANSACTION ★ Receipt
   - 5.5 Voiding / Cancelling Completed Sales (Admin Authorization Required)

6. **Module 3: Deliveries**
   - 6.1 Deliveries Register & Delivery Receipts (DR)
   - 6.2 Filtering Deliveries (Cash, Utang, Branch / Owner Transfer)
   - 6.3 Creating a New Customer Delivery
     - 6.3.1 Customer Details & Delivery Date
     - 6.3.2 Multi-Item Entry, Quantities & Per-Sack Discount (Disc/Sk)
     - 6.3.3 Stock Availability Verification
   - 6.4 Handling Delivery Types & Payment Methods
     - 6.4.1 Mode 1: Cash Delivery (Paid on Delivery, Recorded in Daily Sales)
     - 6.4.2 Mode 2: Utang Delivery (Credit / Receivable, Monitored in Utang Monitor)
     - 6.4.3 Mode 3: Branch / Owner Transfer (Internal Transfer, ₱0.00 Non-Revenue, OWNER_TRANSFER Stock Out)
   - 6.5 Delivery Receipts Auto-Printing (`DR-YYYYMMDD-XXXX`)
   - 6.6 Recording Installment Payments on Utang Deliveries

7. **Module 4: Inventory**
   - 7.1 Stock Overview & Current Balance by Rice Variety
   - 7.2 Low Stock Badges & Minimum Threshold Alerts
   - 7.3 Stock Movement Ledger (Audit Trail of all In/Out/Adjustments)

8. **Module 5: Stock-In**
   - 8.1 Inbound Supplier Deliveries Register
   - 8.2 Recording New Stock-In (Supplier, Reference #, Varieties, Sacks, Cost)
   - 8.3 Stock-In Override / Absolute Inventory Adjustment (Audit Discrepancy Correction)
   - 8.4 Cancelling a Stock-In Entry

9. **Module 6: TBT Monitor (To Be Taken)**
   - 9.1 What is TBT? (Reserving Purchased Sacks for Customer Later Pickup)
   - 9.2 Immediate Inventory Deduction & Stock Reservation Mechanics
   - 9.3 TBT Records Table & Status Filtering (Pending Pickup vs Released)
   - 9.4 Releasing Sacks to Customer (Full Release vs Partial Pickup)
   - 9.5 Accountability & Release Audit Log

10. **Module 7: Utang Monitor (Accounts Receivable / Credit)**
    - 10.1 Monitoring Outstanding Debts from POS Sales & Deliveries
    - 10.2 Searching Debtors & Viewing Transaction Breakdowns
    - 10.3 Recording Customer Payments (Installments & Full Settlement)
    - 10.4 Auto-Settlement when Balance Reaches ₱0.00
    - 10.5 Printing Utang Statement of Account Reports

11. **Module 8: Rice & Prices (Catalog Management)**
    - 11.1 Managing Rice Varieties (Active vs Inactive)
    - 11.2 Adding a New Rice Variety (Name, Unit, Initial Price, Min Stock, Wholesale Toggle)
    - 11.3 Editing Variant Metadata & Thresholds
    - 11.4 Updating Selling Prices in Real-Time
    - 11.5 Viewing Price Change Audit History

12. **Module 9: Reports**
    - 12.1 Sales Summary Report (Date Range Filtering: Daily, Weekly, Monthly, Custom)
    - 12.2 Delivery Breakdown Report (Cash vs Utang vs Owner Transfer)
    - 12.3 Comprehensive Stock & Sales Reconciliation Report (Beginning Stock, Inbound, Sales, Delivery, Exchanges, Konsumo, Ending Stock)
    - 12.4 Daily Stock Summary Report
    - 12.5 Utang / Receivables Report
    - 12.6 Cash Float & Coin Denomination Tracking (Store Drawer Reconciliation)
    - 12.7 Printing & PDF Exporting Guidelines

13. **Module 10: Expenses**
    - 13.1 Recording Cash Operating Expenses (Utilities, Rent, Labor, Freight)
    - 13.2 Recording "Konsumo" (Internal Rice Consumption & Stock Deduction)
    - 13.3 Expense History, Filtering & Summaries

14. **Module 11: Audit Logs**
    - 14.1 Security & Compliance Overview
    - 14.2 Detailed Event Tracking (Logins, Sales, Stock Changes, Voids, Deletions)
    - 14.3 Reviewing User, IP Address, Timestamp, and Change Diffs

15. **Module 12: Users**
    - 15.1 Managing System User Accounts
    - 15.2 Adding a New User & Assigning Role
    - 15.3 Editing User Details & Resetting Passwords
    - 15.4 Deactivating & Activating User Accounts

16. **Module 13: Settings**
    - 16.1 Store Identity & Receipt Header Setup
    - 16.2 Store Address & Contact Numbers
    - 16.3 Receipt Footer Message Customization
    - 16.4 Low Stock Alert Threshold
    - 16.5 Next Control Number Sequencing

17. **Troubleshooting & FAQs**
    - 17.1 Frequently Asked Operational Questions
    - 17.2 Printer & Pop-up Blocker Troubleshooting
    - 17.3 Handling Physical Stock vs System Stock Discrepancies
    - 17.4 Common Error Messages & Solutions

18. **Appendix & Glossary**
    - 18.1 Glossary of Terms & Abbreviations

---

## 1. SYSTEM INTRODUCTION & OVERVIEW

### 1.1 Purpose of the System
The **Rice Inventory and Sales Management System (RISMS)** is a comprehensive, web-based business management platform engineered specifically for wholesale and retail rice trading operations (such as **MAE CONSUMER Rice Trading**).

In traditional retail rice operations, stores face major challenges:
- Difficulty tracking sacks that customers buy today but pick up in installments (**To Be Taken / TBT**).
- Complicated variant exchanges when customers bring back sacks to swap for another variety.
- Complex bulk deliveries with mixed payment terms (Cash, Utang, and Owner/Branch Transfers).
- Unclear debt monitoring for customer credit accounts.
- Stock discrepancies due to unrecorded internal rice consumption (**Konsumo**).

RISMS solves these challenges with an integrated digital workflow featuring strict inventory controls, row-level concurrency locks, immutable audit logging, and automated receipt generation.

### 1.2 Core System Capabilities
- **High-Speed Point of Sale (POS):** Fast multi-line transaction entry with real-time stock deduction, dynamic wholesale pricing, cash/cheque tenders, and auto-printing control sheet receipts.
- **TBT (To Be Taken) Module:** Complete reservation and release tracking for purchased rice left in store custody.
- **Rice Variant Exchange Module:** Built-in workflow for customer-brought returns with strict "no cash refund" value protection.
- **Triple-Mode Delivery Handling:** Dispatches categorized into Cash, Utang (Receivable), and Branch/Owner Transfer (₱0 non-revenue inventory transfer).
- **Accounts Receivable (Utang Monitor):** Real-time customer balance monitoring and partial installment payment recording.
- **Multi-Movement Inventory Ledger:** Complete audit trail tracking every single bag of rice entering or leaving the warehouse.
- **Konsumo Expense Tracking:** Automatic inventory deduction for rice consumed by store staff or owners.
- **Comprehensive Financial & Stock Reports:** Full reconciliation reports displaying Beginning Stock, Stock-In, Sales, Deliveries, Exchanges, Konsumo, and Ending Stock.

### 1.3 User Roles & Permission Matrix
RISMS uses Role-Based Access Control (RBAC) to ensure security and operational accountability:

| Module / Action | Administrator | Staff / Cashier | Viewer |
| :--- | :---: | :---: | :---: |
| **Dashboard** | Full Access | Full Access | Read-Only |
| **Point of Sale (POS) New Sale** | Yes | Yes | No |
| **Rice Variant Exchange** | Yes | Yes | No |
| **Sales History & Receipt Reprint** | Yes | Yes | Read-Only |
| **Void / Cancel Sale** | **Yes (Password)** | No | No |
| **Deliveries (Cash & Utang)** | Yes | Yes | Read-Only |
| **Deliveries (Branch/Owner Transfer)** | Yes | Yes | Read-Only |
| **Record Delivery Payment** | Yes | Yes | No |
| **Inventory Overview & Movements** | Full Access | Full Access | Read-Only |
| **Stock-In (New Supplier Delivery)** | Yes | Yes | No |
| **Stock-In Override (Adjustment)** | Yes | Yes | No |
| **Cancel Stock-In Record** | **Yes** | No | No |
| **TBT Monitor & Sack Release** | Full Access | Full Access | Read-Only |
| **Utang Monitor & Payment Record** | Full Access | Full Access | Read-Only |
| **Rice Catalog (Add/Edit Variant)** | **Yes** | No | Read-Only |
| **Price Updates & History** | **Yes** | No | Read-Only |
| **Reports (View, Print, Export)** | Full Access | Full Access | Read-Only |
| **Coin Remittance / Float Entry** | Yes | Yes | No |
| **Expenses (Cash & Konsumo)** | Full Access | Full Access | No |
| **Audit Logs** | **Full Access** | No Access | No Access |
| **User Account Management** | **Full Access** | No Access | No Access |
| **System Settings** | **Full Access** | No Access | No Access |

### 1.4 Technical Requirements & Access URL
- **Client Devices:** Any standard PC, laptop, or tablet with Google Chrome, Microsoft Edge, or Mozilla Firefox.
- **Printers Supported:** Standard desktop ink/laser printers (Letter/A4 size) and thermal receipt printers for control sheets and delivery receipts.
- **Access URL:** Open your web browser and navigate to the store server address (e.g., `http://localhost/risms` or the configured network domain).

---

## 2. GETTING STARTED & AUTHENTICATION

### 2.1 Accessing the Application
Launch your web browser and open the RISMS URL. If you are not currently logged in, the system will automatically redirect you to the secure login gateway (`/login`).

### 2.2 Logging In
1. On the Login Screen, enter your designated **Username** and **Password**.
2. Click the **Sign In** button.
3. Upon successful credential validation, the system creates an encrypted session, updates your last login timestamp, logs a `LOGIN` event in the Audit Trail, and directs you to the Dashboard.

> **Security Note:** If three consecutive invalid login attempts occur, verify your credentials with the store Administrator. Never share your credentials with other personnel.

### 2.3 Session Security & Automatic Timeout
To protect financial transactions and inventory integrity, RISMS incorporates security safeguards:
- Session cookies are strictly configured with `HttpOnly` and `SameSite=Lax` protection.
- Every state-changing form submission is guarded by a dynamic **CSRF (Cross-Site Request Forgery) Token**.
- If a user workstation is left idle beyond the session inactivity timeout, the user is automatically logged out.

### 2.4 Signing Out Safely
1. At the bottom of the left sidebar, click the **Log Out** icon button next to your name.
2. A confirmation dialog will prompt: *"Are you sure you want to sign out of the application?"*
3. Click **Confirm / Sign Out**. The system will terminate your session and return you to the login screen.

---

## 3. USER INTERFACE & NAVIGATION GUIDE

### 3.1 Overview of the Navigation Sidebar
The left sidebar is your persistent command center. It displays the store logo (**MAE CONSUMER**) and all navigation menus accessible to your assigned role:

```
[Store Logo] MAE CONSUMER
---------------------------------------
  [⊞] Dashboard         (/dashboard)
  [🛒] Sales             (/sales)
  [🚚] Deliveries        (/deliveries)
  [📦] Inventory         (/inventory)
  [📥] Stock-In          (/stock-ins)
  [🕒] TBT Monitor       (/tbt)
  [📅] Utang Monitor     (/utang)
  [🌾] Rice & Prices     (/catalog)
  [📊] Reports           (/reports)
  [💵] Expenses          (/expenses)
  [📜] Audit Logs        (/audit-logs)
  [👥] Users             (/users)
  [⚙] Settings          (/settings)
---------------------------------------
[Logged User Name] | [ROLE] | [Log Out]
```

### 3.2 Visual Icons & Active Page Indicators
- The menu item matching your current screen is highlighted with an active background and brand accent color.
- Role filtering ensures staff cashiers only see operational menus, while administrative settings and audit logs are visible solely to Administrators.

### 3.3 Top Header, User Profile & System Confirmations
- The top header provides breadcrumbs showing your current section.
- Flash messages appear in green (Success) or red (Error) at the top of pages after actions like saving a sale or recording a payment.
- Destructive actions (like cancelling a sale or signing out) always present a double-confirmation modal to prevent accidental data entry.

---

## 4. MODULE 1: DASHBOARD

The Dashboard (`/dashboard`) provides management and staff with an immediate, bird's-eye overview of the day's operations.

### 4.1 Real-Time KPI Metric Cards
At the top of the dashboard are key operational indicators:
1. **Active Variants:** Total number of rice varieties currently active in the store catalog.
2. **Low Stock Alert:** Total varieties whose current warehouse balance has fallen below their minimum threshold.
3. **Today's Sales Revenue:** Net cash/paid revenue collected today across all completed POS sales and cash deliveries.
4. **Today's Sacks Sold:** Total physical quantity of rice sacks sold today.
5. **Month-to-Date (MTD) Sales:** Cumulative revenue generated from the first day of the current month up to the current date.

### 4.2 Low Stock Alert Panel
Located on the right side of the dashboard, this panel highlights varieties urgently requiring replenishment from suppliers. It lists:
- Variety Name
- Current Stock Level
- Minimum Safe Stock Threshold
- A visual alert badge prompting staff to issue a supplier Stock-In.

### 4.3 Recent Sales Feed & Live Status
Displays the latest 5 customer sales transactions, showing Transaction Number, Customer Name, Date, Sacks Purchased, Total Amount, and Status Badge (`Completed`, `Exchanged`, or `Cancelled`).

---

## 5. MODULE 2: SALES (POS & RICE VARIANT EXCHANGE)

The Sales module is the heartbeat of retail operations. It handles walk-in point-of-sale transactions, partial order pickups, credit sales, customer variant exchanges, and official receipt control sheets.

### 5.1 Sales Register & Transaction History
Navigating to `/sales` displays the complete Sales Register:
- **Transaction Number:** Unique invoice code (e.g., `INV-20261006-143022-819`).
- **Customer Name:** Name of the buyer or "Walk-in Customer".
- **Date:** Date of transaction.
- **Items Summary:** Varieties and quantities purchased.
- **Total Amount (₱):** Net total collected.
- **Cashier:** Staff member who processed the sale.
- **Status:** `Completed` (Green), `Exchanged` (Blue), or `Cancelled` (Red).
- **Actions:**
  - **View Details:** View full itemized breakdown and audit details.
  - **Print Receipt:** Reprint the Official Control Sheet Receipt.
  - **Void / Cancel:** Admin action to cancel the sale and restore inventory.

### 5.2 Point of Sale (POS) — Step-by-Step New Sale Guide
Click the **+ New Sale** button (or navigate to `/sales/create`) to launch the POS terminal.

```
+-------------------------------------------------------------------------------+
| POINT OF SALE (POS)                                                           |
+------------------------------------------------------+------------------------+
| CART ITEMS                                           | CHECKOUT SUMMARY       |
| [Variant] [Qty] [Type] [Taken Now] [Price] [Subtotal]| Customer: [          ] |
| ---------------------------------------------------  | Discount: ₱[   0.00  ] |
| Sinandomeng  | 10 | Release Now | -- | ₱2,100 | 21,000| ---------------------- |
| Dinorado 50k |  5 | Utang (Debt)|  2 | ₱2,400 | 12,000| Total Due: ₱33,000.00  |
|                                                      | Payment: (o)Cash ( )Chk|
| [+ Add Item Row]                                     | Tendered: ₱[33,000.00] |
|                                                      | Change:   ₱     0.00   |
| Notes / Remarks: [                                 ] | Sale Date: [2026-10-06]|
|                                                      | [  COMPLETE SALE  ]    |
+------------------------------------------------------+------------------------+
```

#### 5.2.1 Adding Cart Items & Variant Selection
1. Click **+ Add Item** to insert additional rows for customers buying multiple varieties.
2. In the **Variant** dropdown, select the rice variety. The system displays the variety name, unit, current price, and current stock in real-time (e.g., *Dinorado (50kg) — ₱2,450 (185 left)*).
3. The system automatically populates the **Unit Price (₱)** based on active catalog pricing.

#### 5.2.2 Quantity Ordering & Live Stock Checking
1. In the **Quantity** field, enter the number of sacks the customer wants to purchase.
2. The POS automatically validates the quantity against the warehouse stock balance. If the quantity entered exceeds current available stock, the system will prevent checkout to protect against overselling.

#### 5.2.3 Line Item Fulfillment Types (Release Now, TBT, Utang)
Each line item includes a **Type** selector with three vital options:
1. **Release Now:** Used when the customer takes all purchased sacks immediately upon payment.
2. **To Be Taken (TBT):** Used when the customer pays for the rice now, but leaves some or all sacks at the store to be claimed later.
3. **Utang (Credit):** Used when the customer is purchasing the rice on credit/debt terms without paying in full today. Customer name is mandatory.

#### 5.2.4 The "Taken Now" (`quantities_taken`) Feature & Partial Fulfillment
In retail rice operations, customers frequently buy in bulk (e.g., 10 sacks) but can only carry a few sacks home on their motorcycle or tricycle today (e.g., 3 sacks).

**How "Taken Now" Works:**
- When **Utang** or **TBT** is selected, a special highlighted field labeled **Taken Now** appears next to the quantity.
- Enter the exact number of sacks the customer is physically carrying away today.
- *Example:* Customer orders **10 sacks**. You enter **3** in the "Taken Now" field.
- The 3 sacks are released immediately to the customer.
- The system computes: `Unclaimed Sacks = 10 - 3 = 7 sacks`.
- The system automatically sends the 7 unclaimed sacks into the **TBT Monitor** under the customer's name!

#### 5.2.5 Automatic TBT Queueing for Sacks Left at the Store
- Any sacks where `Quantity Taken < Quantity Ordered` are automatically flagged with status `pending_pickup` in the **TBT Monitor**.
- **Crucial Inventory Rule:** The full 10 sacks are deducted from the warehouse stock immediately! This guarantees that the remaining 7 sacks are digitally reserved and cannot be accidentally sold to another walk-in customer.
- When the customer returns days later to collect their 7 sacks, staff simply look up their name in the TBT Monitor and click **Release**.

#### 5.2.6 Automatic Wholesale Discount (₱10/sack on 10+ sacks)
- The system includes a built-in wholesale incentive: If a customer purchases **10 or more sacks** of an eligible rice variety, the system automatically deducts **₱10.00 per sack**.
- *Example:* 10 sacks at ₱2,200 = ₱22,000 gross. An automatic discount of ₱100 (10 × ₱10) is applied, making the net total ₱21,900.
- **Strict Store Policy:** Wholesale discounts are applied **ONLY to Cash purchases**. The system automatically suppresses wholesale discounts if the transaction type is **Utang (Credit)**.

#### 5.2.7 Manual Custom Peso Discount
- Cashiers can grant additional special discounts by entering an amount into the **Manual Discount (₱)** field in the Checkout Summary.
- The grand total due recalculates in real-time.

#### 5.2.8 Payment Methods (Cash vs Cheque)
- **Cash:**
  - Select the **Cash** radio button.
  - Enter the customer's payment into **Amount Tendered (₱)**.
  - The system computes **Change Due** in real-time. The checkout button remains disabled if tendered amount is less than the net amount due.
- **Cheque (Credit / Cash-Equivalent):**
  - Select the **Cheque (Credit)** radio button.
  - Enter the **Cheque Number** (e.g., `CHK-9812456`).
  - Enter the **Expected Clearance Date**.
  - Cheques from verified business customers are treated as cash-equivalents for immediate inventory release.

#### 5.2.9 After-Hours / Backdated Sale Entry & Mandatory Remarks
- If a sale occurred late in the evening after normal closing hours or during a power outage, cashiers can adjust the **Sale Date** field.
- An alert banner warns: *"After-hours sale. This transaction will be recorded under the selected date."*
- Staff must enter an explanation in the **Sale Notes / Remarks** field for administrative auditing.

#### 5.2.10 Completing Checkout & Official Receipt / Control Sheet Printing
1. Click **Complete Sale**.
2. The system executes an atomic transaction: locks inventory rows, deducts stock, writes stock movements, creates TBT records (if applicable), logs an audit event, and assigns an official **Control Number**.
3. The browser immediately loads the **Official Receipt / Control Sheet** and opens the system print dialog (`?autoprint=1`).
4. Print the control sheet and provide a copy to the customer.

---

### 5.3 Rice Variant Exchange ("The Other Brought Rice" / Customer Returns)

In daily rice retailing, customers sometimes return bags of rice they previously purchased (the **"brought rice"**) because their family preferred a softer texture, or they want a different grade (e.g., swapping Sinandomeng for Dinorado).

RISMS features a dedicated **Rice Variant Exchange Module** (`/sales/exchange`) designed to handle these transactions with 100% financial and inventory accuracy.

#### 5.3.1 Understanding Exchange Business Rules (Strict No Cash Refund Policy)
Store policy enforces strict rules on rice variant exchanges:
1. **No Cash Refunds Ever:** Under no circumstances does the store return cash to a customer during an exchange.
2. **Cheaper Replacement Variant:** If the customer chooses a cheaper replacement variety, the system **automatically increments the quantity** of the new variety until the new total meets or exceeds the original amount paid!
3. **More Expensive Replacement Variant:** If the replacement variety is more expensive, the customer must pay the exact cash difference (**"Difference to Collect"**).
4. **Returned Sacks Are Restocked:** The returned rice is immediately added back to the warehouse inventory.
5. **Pending TBT Cleared:** If the original purchase had unclaimed sacks in the TBT Monitor, they are automatically released/resolved.

#### 5.3.2 Step 1: Locating the Original Completed Sale
1. From the Sales list, click the **Exchange Item** button (or navigate to `/sales/exchange`).
2. In the search box, enter the original **Receipt / Transaction Number** (e.g., `INV-20261004-1102`) or the **Customer Name**.
3. Optionally filter by date and click **Search**.
4. In the search results table, locate the matching transaction and click **Select →**.

#### 5.3.3 Step 2: Reviewing Original Purchase Details & Items
The screen displays a verified summary of the original sale:
- Transaction Number and Control Number
- Customer Name and Original Purchase Date
- Original Total Amount Paid (₱)
- Itemized table showing the exact varieties, quantities, and prices previously purchased.

#### 5.3.4 Step 3: Selecting Replacement Variant & Quantity
1. In the **New Rice Variant** dropdown, select the variety the customer wishes to take home.
2. In the **Quantity** field, enter the initial quantity the customer requested.

#### 5.3.5 Step 4: Value Balancing & Auto-Adjusted Quantities
The live **Exchange Preview** panel instantly calculates the financial balance:
- **Old Amount:** The total money originally paid by the customer.
- **New Amount:** The subtotal of the replacement variety.
- **Final Quantity (Adjusted):** If the replacement rice is cheaper than the old total, RISMS automatically increases the quantity of sacks so the customer receives full value for their money without receiving a cash refund.
- **Difference to Collect:** The additional amount the customer must pay if the new items exceed the original payment.

#### 5.3.6 Step 5: Handling the Difference to Collect & Cash Tendered
- If the difference is **₱0.00**, no cash is exchanged.
- If there is a **Difference to Collect** (e.g., ₱350.00), the **Amount Tendered** box appears.
- Enter the cash amount handed over by the customer.
- The system verifies the payment and displays the **Customer Change**.

#### 5.3.7 Step 6: Atomic Stock Adjustment
When you click **Confirm Exchange**, the system atomically:
1. Adds the returned variety back into stock with movement type `SALE_REVERSAL`.
2. Deducts the newly selected variety from stock with movement type `SALE`.
3. Marks the original sale status as `Exchanged` and links it to the new exchange invoice (`exchanged_to_sale_id`).

#### 5.3.8 Step 7: Auto-Releasing Old Pending TBT Records
If the original sale had uncollected sacks waiting in the TBT Monitor, the exchange system automatically marks those TBT records as `released` to prevent orphan reservations.

#### 5.3.9 Step 8: Generating the ★ EXCHANGE TRANSACTION ★ Receipt
The system automatically generates and prints a specialized **Exchange Receipt**:
- Header clearly displays: `★ EXCHANGE TRANSACTION ★`
- Shows the original transaction reference number and control number.
- Itemizes the replacement rice issued.
- Displays the old credit applied, additional cash paid, and change returned.

---

### 5.4 Voiding / Cancelling Completed Sales (Admin Authorization Required)
If an erroneous sale was completed in error:
1. Navigate to the sale details page (`/sales/{id}`).
2. In the cancellation panel, an **Administrator Password** is strictly required. Staff cashiers cannot void sales.
3. Enter the Admin password and click **Cancel Sale**.
4. The system validates the password, sets the sale status to `Cancelled`, restocks all items via `SALE_REVERSAL`, logs an audit entry, and locks the invoice against further edits.

---

## 6. MODULE 3: DELIVERIES

The Deliveries module (`/deliveries`) is designed for customer bulk dispatch and warehouse logistics. It handles wholesale customer deliveries with Delivery Receipts (`DR-YYYYMMDD-XXXX`).

### 6.1 Deliveries Register & Delivery Receipts (DR)
The Deliveries dashboard lists all delivery dispatches:
- **Receipt No:** Unique code formatted as `DR-YYYYMMDD-XXXX` (e.g., `DR-20261006-0004`).
- **Customer Name:** Store, canteen, or individual buyer.
- **Delivery Date:** Dispatch date.
- **Items Summary:** Varieties and total sacks dispatched.
- **Payment Type:** `Cash`, `Utang`, or `Branch / Owner Transfer`.
- **Payment Status:** `Paid` (Green) or `Outstanding` (Red badge showing remaining balance).
- **Actions:** View, Reprint DR Receipt, or Record Payment.

### 6.2 Filtering Deliveries
Quickly filter the delivery register using the top tabs:
- **All Deliveries:** Comprehensive list of all dispatches.
- **Cash:** Paid deliveries contributing to daily store revenue.
- **Utang:** Deliveries with unpaid receivables.

---

### 6.3 Creating a New Customer Delivery
Click **+ New Delivery** (or open `/deliveries/create`).

```
+-------------------------------------------------------------------------------+
| NEW CUSTOMER DELIVERY                                                         |
+------------------------------------------------------+------------------------+
| Customer Name: [Aling Nena General Store           ] | PAYMENT TYPE           |
| Delivery Date: [2026-10-06                         ] | [ Cash ]  [ Utang ]    |
|                                                      | [Branch / Owner Trans] |
| ITEM DETAILS                                         | ---------------------- |
| [Rice Variety]       [Qty] [Disc/Sk] [Subtotal]      | Total Due: ₱48,000.00  |
| Maharlika (50kg)  |   20  |  ₱20.00 | ₱47,600.00     | ---------------------- |
|                                                      | Amount Tendered:       |
| [+ Add another item]                                 | ₱[48,000.00]           |
|                                                      | Change: ₱0.00          |
| Notes: [Deliver to gate 2 by 3:00 PM               ] | [  SAVE & PRINT DR  ]  |
+------------------------------------------------------+------------------------+
```

#### 6.3.1 Customer Details & Delivery Date
Enter the business or customer name and select the scheduled delivery date.

#### 6.3.2 Multi-Item Entry & Per-Sack Discount (`discount_per_sack`)
- Add one or multiple varieties.
- For each row, select the rice variety and quantity in sacks.
- Enter **Disc/Sk (Discount per Sack)**: Bulk deliveries often have negotiated delivery discounts (e.g., ₱20 off per sack). The row subtotal calculates: `Subtotal = Quantity × (Unit Price - Disc/Sk)`.

#### 6.3.3 Stock Availability Verification
The system validates stock balances. If requested quantities exceed available inventory, saving is blocked to prevent warehouse shortages.

---

### 6.4 Handling Delivery Types & Payment Methods
Deliveries are divided into **three essential operational modes**:

```
+-------------------------------------------------------------------------------+
| DELIVERY OPERATIONAL MODES                                                    |
+-------------------+-----------------------------+-----------------------------+
| 1. CASH DELIVERY  | 2. UTANG DELIVERY           | 3. BRANCH / OWNER TRANSFER  |
+-------------------+-----------------------------+-----------------------------+
| • Customer pays   | • Delivered on credit       | • Internal transfer to      |
|   upon delivery   | • Balance = Total Due       |   another store branch or   |
| • Recorded into   | • Automatically tracked     |   personal owner supply     |
|   daily sales     |   in Utang Monitor          | • Unit Price = ₱0.00        |
| • Stock Movement: | • Deducts warehouse stock   | • Non-revenue (₱0 sales)    |
|   'SALE'          | • Subsequent payments       | • Stock Movement:           |
|                   |   recorded in installments  |   'OWNER_TRANSFER'          |
+-------------------+-----------------------------+-----------------------------+
```

#### 6.4.1 Mode 1: Cash Delivery (Paid on Delivery)
- Click the **Cash** button.
- The delivery is marked as fully paid upon dispatch or cash collection.
- Enter the **Amount Tendered** (change is calculated).
- **Accounting Impact:** The delivery is recorded directly into the store's **Daily Sales** ledger, contributing to daily revenue figures.
- **Inventory Impact:** Stock is deducted immediately with movement type `SALE`.

#### 6.4.2 Mode 2: Utang Delivery (Credit / Debt Delivery)
- Click the **Utang (Debt)** button.
- A warning notice appears: *"This delivery will be recorded as unpaid debt in the Utang Monitor."*
- Amount Paid is set to ₱0.00 (or partial downpayment), and the remainder is flagged as an outstanding balance.
- **Accounting Impact:** Synced directly to the **Utang Monitor** with status `outstanding`. It is monitored until payments are logged.
- **Inventory Impact:** Stock is deducted immediately with movement type `SALE`.
- Customers can pay this balance in installments over time.

#### 6.4.3 Mode 3: Branch / Owner Transfer (Internal Inventory Movement)
- Click the **Branch / Owner Transfer** button.
- A notice highlights: *"Internal transfer. This will NOT be recorded as revenue or a sale."*
- All item unit prices, discounts, and subtotals are automatically forced to **₱0.00**.
- **Total Due** displays **₱0.00**.
- **Accounting Impact:** Does **NOT** generate sales revenue, does **NOT** count as earnings, and will **NOT** artificially inflate the financial ledger.
- **Inventory Impact:** Stock is accurately deducted from warehouse inventory with dedicated movement type `OWNER_TRANSFER`.
- The printed Delivery Receipt clearly states **BRANCH / OWNER TRANSFER** with ₱0 totals, serving as an official gate pass for transport drivers.

---

### 6.5 Delivery Receipts Auto-Printing (`DR-YYYYMMDD-XXXX`)
Upon clicking **Save & Print DR**, the system creates the records and opens the official Delivery Receipt print window. The receipt features:
- Store header and contact numbers.
- DR Number and dispatch date.
- Recipient customer name and delivery address notes.
- Itemized quantity, rice variety, and net subtotal (or Transfer note).
- Sign-off lines for **Dispatched By**, **Driver / Delivered By**, and **Received By (Customer Signature)**.

### 6.6 Recording Installment Payments on Utang Deliveries
When a delivery debtor makes a payment:
1. Locate the delivery in the Deliveries list or the Utang Monitor.
2. Click **Record Payment**.
3. In the payment modal, enter the amount paid (e.g., ₱10,000 against a ₱48,000 balance).
4. The system reduces the balance, creates an audit record in `customer_delivery_payments`, and updates the status.
5. When the balance reaches ₱0.00, the system marks the delivery as `Paid` and automatically writes a final cash settlement entry in the sales ledger!

---

## 7. MODULE 4: INVENTORY

The Inventory module (`/inventory`) provides real-time warehouse visibility and an immutable stock movement ledger.

### 7.1 Stock Overview & Current Balance
The main inventory table displays:
- **Variety Name & Unit:** (e.g., *Sinandomeng - 50kg Sack*).
- **Current Stock:** Live sack balance in the store/warehouse.
- **Minimum Stock:** Safety threshold before alerts trigger.
- **Stock Status Badge:**
  - `In Stock` (Green): Healthy stock levels.
  - `Low Stock` (Yellow): At or below minimum threshold.
  - `Out of Stock` (Red): Zero sacks available.

### 7.2 Stock Movement Ledger (Audit Trail)
Click **View Movements** to open the complete inventory audit log (`/inventory/movements`). Every addition or subtraction is permanently logged with:
- Timestamp
- Rice Variety
- Movement Type:
  - `STOCK_IN`: Sacks added from supplier deliveries.
  - `SALE`: Sacks deducted from customer sales or deliveries.
  - `SALE_REVERSAL`: Sacks restored from cancelled sales or exchanged returns.
  - `ADJUSTMENT`: Manual stock correction via absolute override.
  - `OWNER_TRANSFER`: Sacks deducted for branch transfers.
  - `EXPENSE_CONSUMPTION`: Sacks deducted for store "Konsumo".
- Quantity Moved (+ / -)
- Balance After
- Staff Member Who Authorized the Transaction

---

## 8. MODULE 5: STOCK-IN

The Stock-In module (`/stock-ins`) records new inventory received from rice millers, distributors, or suppliers.

### 8.1 Inbound Supplier Deliveries Register
Displays all recorded supplier shipments: Reference Number / Waybill, Supplier Name, Stock-In Date, Total Sacks Received, and Recorded By.

### 8.2 Recording New Stock-In (Supplier Delivery)
1. Navigate to `/stock-ins/create`.
2. Enter the **Supplier Name** (e.g., *Bulacan Rice Mill Corporation*).
3. Enter the **Supplier Reference / Invoice #** (e.g., *SI-88219*).
4. Select the **Stock-In Date**.
5. Add line items: Select Rice Variety, Quantity of sacks received, and Cost Price per sack.
6. Click **Save Stock-In**.
7. The system updates `current_stock_cache` for each variety and logs `STOCK_IN` movements.

### 8.3 Viewing Stock-In Details & Packaging (kg) Visibility
When clicking **View** on any stock-in record (e.g., `/stock-ins/4`):
- Displays the header details: Supplier Name, Received By Staff, Date, Reference / Receipt Number, Total Cost, and Status badge (`Completed` / `Cancelled`).
- In the **Items Received** table:
  - **Variant Name:** Shows the rice variety name with its unit in parentheses (e.g., `Palma 160 (25kg)`).
  - **Unit / Packaging:** Features a dedicated, high-visibility badge displaying the exact packaging weight (e.g., `25kg`, `50kg`, `10kg`, `5kg`) so staff can immediately identify the sack size without confusion.
  - **Quantity:** Displays total physical sacks received (e.g., `147 sacks`).
  - **Unit Cost:** Displays supplier cost per sack (₱).
  - **Line Total:** Displays the calculated subtotal per variety.
  - **Total Computed:** Bottom summary row calculating grand shipment cost.

### 8.4 Stock-In Override / Absolute Inventory Adjustment
If physical inventory differs from digital stock due to physical counts, broken sacks, or unrecorded adjustments:
1. Navigate to `/stock-ins/override`.
2. Select the **Rice Variety**.
3. Enter the exact **Absolute Quantity** physically counted in the warehouse.
4. Enter a mandatory **Reason** (e.g., *"End-of-month physical warehouse count"*).
5. Click **Apply Stock Adjustment**.
6. The system recalculates the delta and updates the stock level with an `ADJUSTMENT` movement.

### 8.5 Cancelling a Stock-In Entry
Administrators can cancel an erroneous stock-in record from its view page. The system will reverse the added stock and mark the shipment as cancelled.

---

## 9. MODULE 6: TBT MONITOR (TO BE TAKEN)

The **TBT Monitor** (`/tbt`) manages one of the most critical aspects of rice retail operations: **customer rice storage and delayed pickup**.

### 9.1 What is TBT?
When customers buy multiple sacks of rice, they frequently request to leave some or all sacks at the store for later retrieval. RISMS tracks these sacks so customers are never short-changed and inventory is never double-sold.

### 9.2 Immediate Inventory Deduction & Reservation
- When a sale line is set to **TBT**, or when **Taken Now < Total Ordered**, the uncollected sacks enter the TBT Monitor.
- **Stock is deducted immediately** at sale time. This reserves the sacks so other staff cannot sell them.

### 9.3 TBT Records Table & Status Filtering
- Filter by:
  - **Pending Pickup:** Active reservations waiting for the customer.
  - **Released:** Sacks that have already been collected.
- Table columns:
  - Customer Name
  - Invoice / Transaction Number
  - Rice Variety
  - Reserved Sacks
  - Sacks Already Released
  - Remaining Sacks to Claim
  - Purchase Date

### 9.4 Releasing Sacks to Customer (Full vs Partial Pickup)
When a customer arrives with their receipt to pick up sacks:
1. Search the customer name or receipt number in the TBT Monitor.
2. Click the **Release** button on their record.
3. A pickup modal appears:
   - **Full Pickup:** To collect all remaining sacks, leave the quantity as maximum and click **Confirm Release**. The record status updates to `Released`.
   - **Partial Pickup:** If the customer only takes 2 of 5 remaining sacks, enter `2` in the quantity field and confirm. The system updates sacks released to 2, remaining to 3, and keeps the status as `Pending Pickup`!

### 9.5 Accountability & Release Audit Log
Every TBT release records the timestamp and the identity of the staff member who handed over the sacks.

---

## 10. MODULE 7: UTANG MONITOR (ACCOUNTS RECEIVABLE / CREDIT)

The **Utang Monitor** (`/utang`) tracks customer debts originating from credit POS sales and credit Deliveries.

### 10.1 Monitoring Outstanding Debts
The monitor displays:
- Customer Name
- Reference Transaction Number (INV or DR)
- Date Incurred
- Total Order Amount (₱)
- Total Amount Paid to Date (₱)
- Remaining Balance (₱)
- Status (`Outstanding` vs `Settled`)

### 10.2 Searching Debtors
Quickly filter by status or search by customer name to retrieve an account balance when a debtor visits the store.

### 10.3 Recording Customer Payments (Installments & Full Settlement)
1. Click **Record Payment** next to an outstanding debtor.
2. Enter the cash amount paid (e.g., ₱5,000).
3. The system subtracts the payment from the balance and records a timestamped entry.
4. When the balance hits ₱0.00, status automatically changes to `Settled`.

### 10.4 Printing Utang Statement of Account Reports
Click **Print Utang Report** to generate a clean, professional summary of receivables for collection follow-ups.

---

## 11. MODULE 8: RICE & PRICES (CATALOG MANAGEMENT)

Accessible at `/catalog`, this module controls what rice varieties the store sells and their active prices.

### 11.1 Viewing & Managing Rice Varieties
Lists all varieties with Name, Packaging Unit (e.g., *50kg Sack*, *25kg Sack*), Current Selling Price, Minimum Stock Threshold, Wholesale Discount eligibility, and Status (Active/Inactive).

### 11.2 Adding a New Rice Variety (Admin Only)
1. Click **+ Add Variety**.
2. Enter:
   - **Variety Name:** (e.g., *Super Angelica*).
   - **Unit:** (e.g., *50kg*).
   - **Initial Selling Price:** (e.g., *₱2,350.00*).
   - **Minimum Stock Threshold:** (e.g., *20* sacks).
   - **Allow Wholesale Discount:** Check box to enable automatic ₱10 discount for 10+ sacks.
3. Click **Save Variety**.

### 11.3 Updating Selling Prices & Price History
1. To change a selling price, click **Update Price** on any variety.
2. Enter the new selling price and submit.
3. The new price takes effect immediately across POS and Deliveries.
4. The system logs the change in the **Price History** table, recording the old price, new price, effective date, and user who changed it.

### 11.4 Activating & Deactivating Varieties
If a seasonal variety is unavailable, toggle it to **Inactive**. Inactive varieties are hidden from POS dropdowns but preserved in historical reports.

---

## 12. MODULE 9: REPORTS

The Reports module (`/reports`) delivers financial and operational intelligence for daily reconciliations and audits.

### 12.1 Sales Summary Report
- Filter by date range: **Daily (Today)**, **Weekly (This Week)**, **Monthly (This Month)**, or **Custom Date Range**.
- Displays:
  - Total Revenue (₱)
  - Total Transactions Completed
  - Sacks Sold per Variety
  - Cash vs Cheque Breakdown
  - Wholesale & Manual Discounts Granted

### 12.2 Delivery Breakdown Report
Navigate to `/reports/deliveries` to view dispatch volumes, revenue collected from cash deliveries, and debt incurred from credit deliveries.

### 12.3 Comprehensive Stock & Sales Reconciliation Report
This master audit table reconciles inventory movements across the selected period:
- **Beginning Stock:** Sacks on hand at start of period.
- **Stock-In:** Inbound supplier deliveries.
- **Cash Sales:** Retail sacks sold via cash.
- **Cheque Sales:** Sacks sold via cheque.
- **Delivery Sacks:** Sacks dispatched via deliveries.
- **Exchange Rice:** Sacks returned or issued in exchanges.
- **Konsumo:** Sacks consumed internally.
- **Ending Stock:** Calculated balance matching physical inventory.

### 12.4 Daily Stock Summary Report
Generates end-of-day stock summaries for evening store closure sign-offs.

### 12.5 Cash Float & Coin Denomination Tracking (Drawer Reconciliation)
Store owners need to reconcile loose coins and drawer change floats.
1. In the Reports drawer section, enter physical coin counts.
2. Enter date and notes (e.g., *₱500 coins for cashier drawer float*).
3. The system includes drawer coins in the total cash remittance calculation.

### 12.6 Printing & Exporting Guidelines
All reports feature **Print** and **Export to CSV / Excel** buttons formatted for clean, professional hard-copy archives.

---

## 13. MODULE 10: EXPENSES

The Expenses module (`/expenses`) tracks store operating costs and internal rice consumption.

### 13.1 Recording Cash Operating Expenses
1. Navigate to `/expenses`.
2. Enter **Description** (e.g., *Electric Bill - Davao Light*).
3. Select **Category**: `Utilities`, `Rent`, `Transportation`, `Labor / Salary`, `Supplies`, `Maintenance`, or `Miscellaneous`.
4. Enter **Amount (₱)** and **Date**.
5. Click **Save Expense**.

### 13.2 Recording "Konsumo" (Internal Rice Consumption)
Store owners and stay-in staff frequently consume rice from inventory for meals.
1. When recording an expense, select Category: **Konsumo**.
2. The Cash Amount field is automatically locked to **₱0.00** (no cash left the drawer).
3. Select the **Rice Variety** consumed and enter the **Quantity (Sacks or Kilos)**.
4. Click **Save Expense**.
5. **Inventory Impact:** The system automatically deducts the rice from warehouse inventory with movement type `EXPENSE_CONSUMPTION`. This ensures physical warehouse counts match digital balances without distorting cash flow!

---

## 14. MODULE 11: AUDIT LOGS

Accessible at `/audit-logs` (strictly restricted to Administrators), this module provides enterprise security compliance.

### 14.1 Security & Compliance Overview
Every critical action within RISMS is logged permanently in an append-only audit ledger. Audit logs cannot be modified or deleted.

### 14.2 Detailed Event Tracking
Logged events include:
- `LOGIN` and `LOGOUT`
- `CREATE` (Sales, Deliveries, Stock-Ins, Users, Variants)
- `UPDATE` (Prices, System Settings, User Profiles)
- `CANCEL` (Sale cancellations, Stock-In voids)
- `EXCHANGE` (Rice variant returns)
- `RELEASE` (TBT pickups)
- `PAYMENT` (Utang settlements)

### 14.3 Reviewing User, IP Address, Timestamp, and Change Diffs
Each log entry records:
- Exact Date and Timestamp
- User Account Full Name and Role
- IP Address of the terminal
- Target Table and Record ID
- Detailed JSON diff displaying old values vs new values.

---

## 15. MODULE 12: USERS

Accessible at `/users` (Administrator Only), this module manages staff user accounts.

### 15.1 Managing System User Accounts
View all accounts with Username, Full Name, Assigned Role, Status, and Creation Date.

### 15.2 Adding a New User
1. Click **+ Add User**.
2. Enter Username, Full Name, Email (optional), and Temporary Password.
3. Select Role:
   - **Administrator:** Full system authority.
   - **Staff / Cashier:** Day-to-day sales, deliveries, stock-in, and TBT operations.
   - **Viewer:** Read-only access for auditors or business partners.
4. Click **Create User**.

### 15.3 Password Resets & Account Status
- Administrators can reset passwords for staff who forget their credentials.
- When an employee leaves the company, toggle their status to **Inactive** to immediately terminate access without deleting historical sales records.

---

## 16. MODULE 13: SETTINGS

Accessible at `/settings` (Administrator Only), this module configures store branding and receipt formatting.

### 16.1 Store Identity & Receipt Customization
Configure parameters printed on all official control sheets and delivery receipts:
- **Application Name:** (e.g., `RISMS`).
- **Store Name:** (e.g., `MAE CONSUMER RICE TRADING`).
- **Store Address:** Street address and city.
- **Store Contact Number:** Mobile / telephone number.
- **Receipt Footer Message:** Custom customer courtesy message (e.g., *"Thank you for your purchase! Quality Rice for Every Filipino Home."*).
- **Low Stock Notification Threshold:** Default alert limit.
- **Next Control Number:** Starting number for sequential control sheets.

---

## 17. TROUBLESHOOTING & FAQS

### 17.1 Frequently Asked Questions
**Q: Can a customer take half their sacks now and leave the rest?**  
A: Yes! Use the **Taken Now** field in the POS. Enter what they take today, and the remaining sacks automatically move to the TBT Monitor under their name.

**Q: What happens if a customer returns rice to exchange for a cheaper variety?**  
A: RISMS enforces a strict **No Cash Refund** policy. The system will automatically increase the quantity of the cheaper variety so the customer receives full value in rice instead of cash.

**Q: Does a Branch / Owner Transfer count as store sales revenue?**  
A: No. Branch / Owner Transfers are non-revenue dispatches with ₱0 totals. They deduct inventory under `OWNER_TRANSFER` without inflating daily sales figures.

**Q: How do we account for rice cooked and eaten by store staff?**  
A: Record an expense under the category **Konsumo**. Select the variety and quantity. The system deducts the stock with zero cash impact.

### 17.2 Printer & Pop-Up Blocker Troubleshooting
If receipt windows do not appear after clicking Complete Sale:
1. Look at the right side of the browser address bar for a **Pop-up Blocked** icon.
2. Click the icon and select **"Always allow pop-ups and redirects from this site"**.
3. Click Done and reprint the receipt from the Sales list.

### 17.3 Physical Stock vs System Discrepancy
If an unexpected stock difference occurs:
1. Review the **Stock Movement Ledger** (`/inventory/movements`) for the variety.
2. If unrecorded physical loss or spillage occurred, perform an **Absolute Stock Override** under `/stock-ins/override` with an explanatory note.

---

## 18. APPENDIX & GLOSSARY

- **RISMS:** Rice Inventory and Sales Management System.
- **POS:** Point of Sale terminal.
- **TBT (To Be Taken):** Sacks purchased and reserved in store custody for later pickup.
- **Taken Now:** The portion of ordered sacks physically carried away at checkout.
- **Utang:** Local term for credit sales / accounts receivable.
- **DR (Delivery Receipt):** Official dispatch receipt for customer bulk deliveries.
- **Branch / Owner Transfer:** Internal transfer of rice to another retail branch or owner's household.
- **Konsumo:** Internal consumption of store rice by employees or owner.
- **Wholesale Discount:** Automatic ₱10/sack discount applied on cash purchases of 10+ eligible sacks.
- **SALE_REVERSAL:** Inventory ledger movement that restores stock from cancelled sales or returned exchange rice.
- **RBAC:** Role-Based Access Control restricting system menus according to user roles.
- **Control Sheet:** Official numbered receipt generated for completed transactions.

---
*End of User Manual — Rice Inventory and Sales Management System (RISMS)*