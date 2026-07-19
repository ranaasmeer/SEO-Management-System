# SEO App - Client & Order Management System

A PHP & MySQL based SEO Management System for handling clients, orders, payments, expenses, reports, and link insertions.

---

# Features

## Authentication
- User Login
- User Registration
- Google Login
- Role-based Access
  - Admin
  - Client

---

## Dashboard
- Total Orders
- Revenue
- Expenses
- Profit
- Payment Summary
- Recent Activity

---

## Order Management

- Create Orders
- View Orders
- Edit Orders
- Delete Orders
- Order Tracking

---

## Payments

- Add Payments
- Payment Clearance Date
- Automatic Pending → Cleared Update
- Withdraw Payments
- Delete Payments
- Payment Statistics

Payment Statuses

- Pending
- Cleared
- Withdrawn

---

## Expenses

- Add Expenses
- Expense History
- Expense Tracking
- Expense Statistics

---

## Link Insertions

- Add Link Insertions
- Upload Images
- Edit Records
- Delete Records

---

## Reports

Generate reports including

- Revenue
- Expenses
- Profit
- Payment Details

---

## User Management

Admin can

- View Users
- Add Users
- Manage User Roles

---

# Technologies Used

Frontend

- HTML5
- CSS3
- JavaScript
- Font Awesome
- SweetAlert2

Backend

- PHP 8+
- MySQL
- MySQLi

Libraries

- FPDF
- Google OAuth

---

# Folder Structure

```
seo_app/
│
├── admin/
├── assets/
├── auth/
├── client/
├── config/
├── dashboard/
├── expenses/
├── includes/
├── link_insertions/
├── orders/
├── payments/
├── reports/
├── uploads/
├── vendor/
├── composer.json
└── index.php
```

---

# Installation

## 1. Clone or Download

Copy the project into

```
xampp/htdocs/
```

Rename folder

```
seo_app
```

---

## 2. Start XAMPP

Start

- Apache
- MySQL

---

## 3. Create Database

Open phpMyAdmin

Create database

```
seo_app
```

Import the SQL file.

---

## 4. Configure Database

Open

```
config/db.php
```

Update

```php
$host = "localhost";
$user = "root";
$password = "";
$database = "seo_app";
```

---

## 5. Install Composer Packages

```
composer install
```

---

## 6. Run Project

Open

```
http://localhost/seo_app
```

---

# User Roles

## Admin

- Manage Users
- Manage Orders
- Manage Payments
- Manage Expenses
- View Reports
- Dashboard Access

---

## Client

- Login
- View Assigned Orders
- View Payment Information

---

# Payment Workflow

```
Pending
      │
      ▼
 Clearance Date Reached
      │
      ▼
 Cleared
      │
 Withdraw
      ▼
 Withdrawn
```

---

# Expense Workflow

```
Select Order
      │
      ▼
Enter Cost
      │
      ▼
Add Description
      │
      ▼
Expense Saved
```

---

# Requirements

- PHP 8.0+
- MySQL 5.7+
- Apache Server
- Composer
- XAMPP/WAMP/LAMP

---

# Security Features

- Session Authentication
- Role Based Authorization
- SQL Injection Protection (MySQLi Escaping)
- File Upload Validation
- Form Validation

---

# Future Improvements

- Email Notifications
- Invoice Generation
- AJAX Forms
- REST API
- Charts & Analytics
- Multi-language Support
- Dark Mode
- Backup & Restore
- Export to Excel
- Export to PDF

---

# Developer

Developed using

- PHP
- MySQL
- JavaScript
- HTML
- CSS

---

# License

This project is intended for educational and business management purposes.

Feel free to modify and extend it according to your requirements.