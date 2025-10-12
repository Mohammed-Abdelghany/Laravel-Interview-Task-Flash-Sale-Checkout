
# 🧾 Flash Sale Checkout – Laravel 12 

### 🚀 Overview
This project implements a **Flash Sale Checkout System** designed to handle **high concurrency** safely.  
It ensures that:
- Stock is never oversold (even under parallel requests).
- Holds expire automatically, returning stock.
- Orders are created only for valid, unexpired holds.
- Payment webhooks are **idempotent** and **out-of-order safe**.
- All operations are **transactional and consistent**.
---

## ⚙️ Assumptions & Invariants

| Concept | Invariant |
|----------|------------|
| **Products** | Each product has a limited stock. |
| **Holds** | Temporarily reserve stock for a few minutes using `lockForUpdate()`. Each hold can only be used once. |
| **Orders** | Created only from valid holds → start as `pending` → move to `paid` or `cancelled`. |
| **Payments (Webhooks)** | Each webhook includes an `idempotency_key`. Same key = same effect → no double processing. |
| **Transactions** | DB transactions + pessimistic locks guarantee no race conditions. |
| **Caching** | Used for quick deduplication of processed webhooks and product reads. |
| **Expiry Worker** | Background job safely releases expired holds without double execution. |
| **Logging** | Every webhook and stock event logged for visibility and metrics. |

---

## 🧩 System Flow

1. **Hold Product**  
   `POST /api/holds`  
   Locks product stock and creates a temporary hold record.

2. **Create Order**  
   `POST /api/orders`  
   Converts a valid hold into an order (status: `pending`).

3. **Payment Webhook**  
   `POST /api/webhooks/payment`  
   Called by the payment provider to confirm or cancel payment.  
   - Handles duplicates safely.  
   - Works even if the order response hasn’t reached the client yet.  
   - Updates order → `paid` / `cancelled` accordingly.

---

## 🧱 Database Schema (Key Tables)

| Table | Description |
|--------|--------------|
| **products** | Contains stock and pricing. |
| **holds** | Temporary stock reservations (expire automatically). |
| **orders** | Created from holds. Status: `pending`, `paid`, `cancelled`. |
| **payment_webhooks** | Logs idempotent webhook events with `idempotency_key`. |

---

## 🧪 Tests & Verification

All tests are automated using PHPUnit:

| Test | Description |
|------|--------------|
| 🧍‍♂️ **Parallel Hold Attempts** | Multiple concurrent holds for last stock item → only one succeeds (no oversell). |
| ⏰ **Hold Expiry** | Expired hold releases stock → product available again. |
| 🔁 **Webhook Idempotency** | Same `idempotency_key` processed multiple times → state unchanged after first success. |
| ⏳ **Out-of-Order Webhook** | Webhook arrives before order creation → once order exists, correct final state applied. |

Run with:
```
vendor\bin\phpunit --colors=always
```
---

## ⚡ How to Run Locally

```bash
git clone https://github.com/Mohammed-Abdelghany/Laravel-Interview-Task-Flash-Sale-Checkout.git
cd Flash-Sale-Checkout
composer install
cp .env.example .env
php artisan key:generate
```
### Configure `.env`:
```
DB_CONNECTION=mysql
DB_DATABASE=flashsale
DB_USERNAME=root
DB_PASSWORD=
PAYMENT_WEBHOOK_SECRET=your_webhook_secret
```

### Then:
```bash
php artisan migrate --seed
php artisan serve
```
Now the API is live at:  
➡ `http://127.0.0.1:8000/api`

---

## 📊 Logs & Metrics

| Type | Location |
|------|-----------|
| **App Logs** | `storage/logs/laravel.log` |
| **Webhook Logs** | Table: `payment_webhooks` |
| **Stock Events** | Logged via `Log::info()` in `PaymentWebhookService`. |
| **Metrics Tools (Optional)** | Laravel Telescope or Horizon for real-time metrics. |

---
## 🧠 Key Design Highlights

- **Idempotent Webhooks** via cache + DB log  
- **Pessimistic Locking** (`lockForUpdate`) to avoid race conditions  
- **Transactional Consistency** around stock, orders, and payments  
- **Graceful Error Handling** → duplicate or out-of-order events are harmless  
- **Cache Invalidation** keeps product availability up-to-date  

---

## 🧭 Example API Flow

```
sequenceDiagram
User->>API: POST /api/holds (reserve stock)
API->>DB: Create hold (lock stock)
User->>API: POST /api/orders (use hold)
PaymentProvider->>API: POST /api/webhooks/payments
API->>DB: Update order → paid or cancelled
API->>Cache: Mark webhook as processed
```

---

**Author:**Muhammed Abdelghany 
**Framework:** Laravel 12 (PHP 8.3)  
**Authentication:** JWT  
**Database:** MySQL  
**Tests:** PHPUnit  

