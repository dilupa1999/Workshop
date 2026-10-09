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



---

## 🌟 Operational Tools & Productivity Enhancements

Beyond the core assignment specifications, the following operational and enterprise-grade features were implemented to streamline day-to-day administrative workflows across the training centre:

### 1. 📥 Attendee List CSV Export (Front Desk Tool)
- **Instant Attendance Sheet Generation:** Front-desk staff and managers can download a complete attendee roster directly from any workshop's details page with a single click.
- **Export Specifications:**
  - Dynamic file naming: `attendees-[workshop-code]-[timestamp].csv`.
  - Comprehensive field export: Workshop Code, Title, Attendee Name, Attendee Email, Registration Status (`Active`, `Waitlisted`, `Cancelled`), Registration Timestamp, Enrolling Staff Member, and detailed Cancellation metadata (who cancelled and when).
  - Streamed response handling (`response()->stream()`) ensures minimal server memory footprint during large exports.

### 2. 📊 Live Dashboard Analytics & Metric Cards
- **Executive Summary at a Glance:** The authenticated dashboard renders four real-time analytical summary cards:
  - **Total Workshops:** Aggregate count of all workshops registered across the 3 campuses.
  - **Scheduled Active:** Number of workshops currently open for incoming bookings.
  - **Confirmed Attendees:** Real-time seat occupancy count across all active sessions.
  - **Waitlist Queue:** Total number of queued participants currently awaiting seat cancellations for automatic promotion.
- Built with zero-exception fallback logic ensuring graceful rendering even without explicit controller payload injections.

### 3. 🔍 Multi-Field Catalogue Keyword Search
- **Instant Search Bar:** Integrated into the workshop catalogue filter bar.
- Supports simultaneous partial matching (`LIKE %term%`) across:
  - Workshop Title (e.g., *Pottery*, *Welding*)
  - Unique Workshop Code (e.g., *WS-POT-01*)
  - Instructor Name (e.g., *Sarah Connor*)
- Seamlessly combines with existing date-range, status, campus location, and available-seat filters.

### 4. 👤 Administrative Role & Account Management
- **Role Re-assignment & User Updates:** System Administrators can edit existing staff account details and switch operational roles (`Admin`, `Manager`, `Staff`) on demand.
- **Audited Role Changes:** Any modification to staff roles or email accounts is captured with an atomic audit entry (`USER_ROLE_UPDATED`), recording the administrator's identity, timestamp, and a before/after diff of the affected role.

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