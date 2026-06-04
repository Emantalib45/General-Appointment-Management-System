# 🏥 CareSync Hospital Management & Patient Portal

CareSync is a dynamic, fully-responsive PHP and MySQL-powered clinic/hospital operations platform. It features a modern user interface and bridges public patient appointment scheduling with a secured multi-role healthcare administration portal (Admin and Doctor views).

---

## 🌟 Key Features

### 🌐 Patient Facing Portal
* **Smart Availability Calendar:** Integrates `Flatpickr` dynamically with custom JavaScript to highlight and restrict calendar input selections solely to the specific doctor's pre-configured active dates.
* **Real-time Digital Ticket Generation:** Generates on-demand printable invoices/tickets equipped with mock barcodes, specialized printing media layouts (`@media print`), reporting windows, and auto-generated ticket sequences.

### 👔 Admin Dashboard & Controls
* **Live Operational Analytics:** Track key metrics such as Total Scheduled Doctors, Total Overall Bookings, Today's Patient Count, and Real-time Completed Status Earnings directly on the main control panel.
* **Automated Account Provisioning:** When adding a new availability slot for a practitioner, the system automatically checks for an active user login profile and provisions one dynamically if missing.
* **Comprehensive Scheduling & Logging:** Maintain multi-layered parameters including specializations, sliding-scale consultation fee setups, and explicit operating windows.

### 👨‍⚕️ Doctor Dashboard & Portal
* **Isolated Medical Records:** Doctors get automatic conditional route isolation to display exclusively their designated patient pipeline updates rather than the whole system records.
* **Status Lifecycles:** Allows authorized professionals to securely check off, finalize, or cancel pending clinic bookings down the line.

---

## 🛠️ Tech Stack & Dependencies

* **Backend Core:** PHP (Session management, dynamic parameterized routing, relational tracking).
* **Database Platform:** MySQL / MariaDB (`mysqli` engine extension layer).
* **Frontend Design:** Vanilla HTML5, CSS3 Grid Flexbox Layout architecture, Responsive design architecture.
* **External CDN Libraries:** `Flatpickr JS` (Modern Datepicker utility).

---

## 📁 File Structure

```text
├── db.php         # Global Database credentials & establishing context connection
├── login.php      # Secured central user gateway with default credential fallback seeding
├── logout.php     # Session un-setting and secure system lifecycle teardown script
├── index.php      # Public-facing Patient scheduling application & booking engine
├── admin.php      # Master administrator operational metrics dashboard
├── doctors.php    # Medical staff record, shift provisioning, & availability router
└── patients.php   # Internal operational grid for booking lifecycle updates