# Community Training Centre — Workshop Registration Service

An enterprise-ready Workshop Registration and Scheduling Management platform developed for a Community Training Centre with three campuses. The solution implements strict Role-Based Access Control (RBAC), database-level concurrency protection to prevent workshop overbooking, an automated waitlist queue, and a comprehensive polymorphic audit trail.

---

## 🛠 Tech Stack & Dependencies

- **Framework:** Laravel 11 / 12 (PHP 8.2+)
- **Authentication & UI Scaffold:** Laravel Jetstream (Blade / Livewire Stack)
- **Styling:** Tailwind CSS & Vite
- **Database Engine:** MySQL 8.0+ (**InnoDB** required for row-level locking)
- **RBAC Library:** `spatie/laravel-permission` (^6.0)
- **Testing Framework:** PHPUnit / Laravel Feature Testing Suite

---

## 📌 Business Roles & Permissions (RBAC)

Access boundaries are enforced strictly at the **HTTP Middleware** level. Unauthorized URL access immediately returns an **HTTP 403 Forbidden** response.

| Feature / Action | Admin | Manager | Staff |
| :--- | :---: | :---: | :---: |
| **User Account Management** (Create staff accounts & assign roles) | ✅ | ❌ (403) | ❌ (403) |
| **Workshop Lifecycle** (Create, edit, change status & capacity) | ❌ (403) | ✅ | ❌ (403) |
| **Catalogue Search & Filters** (Date range, status, branch, seats) | ❌ (403) | ✅ | ✅ |
| **Attendee Registration** (Direct booking or waitlist placement) | ❌ (403) | ✅ | ✅ |
| **Cancellations & Audit Viewing** (Cancel seats, review changes) | ❌ (403) | ✅ | ✅ |

---

## 💡 Key Architectural Features

### 1. Concurrency Control & Overbooking Prevention
To prevent Time-of-Check to Time-of-Use (TOCTOU) race conditions when concurrent requests book the final seat:
- All registration and capacity validation operations run inside **`DB::transaction()`**.
- Uses **Pessimistic Write Locking (`lockForUpdate()`)** on the target workshop record.
- Concurrent requests queue sequentially at the database engine level, guaranteeing capacity limits are never breached.

### 2. Bonus Feature: Automated Waitlist Queue
- When workshop capacity hits 100%, registrations automatically divert to a prioritized FIFO waitlist queue.
- If an active registration is cancelled, the system automatically elevates the earliest waitlisted attendee (`ORDER BY created_at ASC`) to an active seat inside an atomic transaction.

### 3. Bonus Feature: Comprehensive Audit Trail
- A dedicated polymorphic `audit_logs` table logs administrative actions.
- Tracks user account provisioning events by Admins.
- Inspects and records model mutations (`before` vs `after` attribute diffs) whenever Managers edit workshop configurations (dates, capacities, titles, instructors, locations).

### 4. Bonus Feature: Multi-Campus Support & Filter Bar
- Full multi-location support across three centres:
  - `Central Campus`
  - `North Branch`
  - `South Centre`
- Integrated catalogue filter allows filtering by Date Range, Status, Campus Location, and "Available Seats Only".

---

## 🚀 Local Installation & Setup

### 1. Clone the Repository
```bash
git clone [https://github.com/](https://github.com/)<your-username>/workshop-registration-service.git
cd workshop-registration-service

composer install
npm install

cp .env.example .env
php artisan key:generate

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=workshop_service_db
DB_USERNAME=root
DB_PASSWORD=

php artisan migrate --seed

npm run build
php artisan serve


Role,Login Email,Password,Permissions & System Scope
System Admin,admin@workshop.com,Admin@1234,"Create staff accounts and assign roles only. Blocked from workshop routes (HTTP 403 Forbidden)."
Workshop Manager,manager@workshop.com,Manager@1234,"Create and edit workshops, manage attendee registrations, process cancellations, and view audit logs."
Front Desk Staff,staff@workshop.com,Staff@1234,"Browse workshop catalogue, register attendees (including waitlist queue), and process seat cancellations."