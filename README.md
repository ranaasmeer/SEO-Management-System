# 🚀 SEO App – Client, Order & Business Management System

![PHP](https://img.shields.io/badge/PHP-8+-777BB4?style=for-the-badge&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6-F7DF1E?style=for-the-badge&logo=javascript)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3)

A complete **SEO Agency Management System** developed with **PHP**, **MySQL**, **JavaScript**, **HTML**, and **CSS**.

The application helps SEO agencies manage clients, orders, payments, expenses, outreach campaigns, reports, invoices, link insertions, and overall business operations from one centralized dashboard.

---

# ✨ Features

## 🔐 Authentication

- User Login
- User Registration
- Google OAuth Login
- Secure Session Management
- Logout
- Role-based Authentication

---

# 📊 Dashboard

Interactive dashboard displaying business statistics including:

- Total Orders
- Total Revenue
- Total Expenses
- Total Profit
- Payment Overview
- Recent Activities
- Business Summary Cards
- Charts & Analytics

---

# 👥 User Management

Administrators can:

- Add Users
- View Users
- Manage User Accounts
- Assign User Roles
- Client Management

---

# 📦 Order Management

Complete order management system.

Features include:

- Create Orders
- Edit Orders
- Delete Orders
- View Orders
- Track Order Status
- Assign Clients
- Order Details
- Generate Professional Invoice

---

# 🧾 Invoice Generator

Each order can generate a professional invoice.

Features

- Invoice PDF
- Order Details
- Client Details
- Pricing Summary
- Printable Invoice

---

# 💳 Payment Management

Manage complete payment workflow.

Features

- Add Payments
- Payment History
- Payment Clearance Date
- Automatic Pending → Cleared Update
- Withdraw Payments
- Delete Payments
- Payment Statistics
- Payment Summary Cards

### Payment Status Workflow

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

# 💰 Expense Management

Track business expenses.

Features

- Add Expenses
- Expense History
- Expense Statistics
- Order-wise Expenses
- Expense Validation
- Expense Tracking

---

# 🔗 Link Insertions

Manage backlink and guest post records.

Features

- Add Link Insertions
- Edit Records
- Delete Records
- Upload Images
- View Link Insertions

---

# 📢 Outreach Management

Manage outreach campaigns.

Features

- Add Outreach Records
- View Outreach History
- Campaign Tracking

---

# 📈 Reports & Analytics

Generate business reports including:

- Revenue Reports
- Expense Reports
- Profit Reports
- Payment Reports
- Business Analytics
- Dashboard Charts
- Financial Summary

---

# 📄 PDF Export

Integrated **FPDF** support for generating printable documents.

Used for:

- Invoices
- Reports
- Business Documents

---

# 📂 File Upload System

Supports uploading images and related project files.

Features

- Image Upload
- File Validation
- Secure Storage

---

# 🔎 Search & Data Management

The system provides organized management for

- Orders
- Payments
- Expenses
- Users
- Link Insertions
- Outreach Records

---

# 👤 User Roles

## 👑 Admin

Administrator has full access.

Permissions include

- Dashboard
- Manage Users
- Manage Orders
- Manage Payments
- Manage Expenses
- Manage Outreach
- Manage Link Insertions
- Generate Reports
- Generate Invoices

---

## 👤 Client

Client can

- Login
- View Dashboard
- View Assigned Orders
- View Payment Information

---

# 📊 Business Workflow

## Order Workflow

```
Create Order
      │
      ▼
Assign Client
      │
      ▼
Complete Work
      │
      ▼
Generate Invoice
      │
      ▼
Receive Payment
```

---

## Payment Workflow

```
Payment Added
      │
      ▼
Pending
      │
Clearance Date
      ▼
Cleared
      │
Withdraw
      ▼
Withdrawn
```

---

## Expense Workflow

```
Select Order
      │
      ▼
Enter Expense
      │
      ▼
Save Expense
      │
      ▼
Update Profit
```

---

# 📁 Project Structure

```
seo_app/

├── admin/
│   ├── add.php
│   └── users.php
│
├── assets/
│
├── auth/
│   ├── login.php
│   ├── register.php
│   ├── google_callback.php
│   └── logout.php
│
├── client/
│   └── dashboard.php
│
├── config/
│   └── db.php
│
├── dashboard/
│   └── index.php
│
├── expenses/
│   ├── add.php
│   ├── index.php
│   └── check_expense.php
│
├── includes/
│
├── link_insertions/
│   ├── add.php
│   ├── edit.php
│   └── index.php
│
├── orders/
│   ├── add.php
│   ├── edit.php
│   ├── delete.php
│   ├── invoice.php
│   └── index.php
│
├── outreach/
│   ├── add.php
│   └── index.php
│
├── payments/
│   ├── add.php
│   ├── check_payment.php
│   └── index.php
│
├── uploads/
│
├── vendor/
│
├── fpdf/
│
├── composer.json
│
└── index.php
```

---

# 🛠 Technologies Used

## Frontend

- HTML5
- CSS3
- JavaScript
- Font Awesome
- SweetAlert2

---

## Backend

- PHP 8+
- MySQL
- MySQLi

---

## Libraries

- Google OAuth
- PHPMailer
- FPDF
- Composer

---

# ⚙ Installation

## 1. Clone Repository

```bash
git clone https://github.com/yourusername/seo-app.git
```

or download the ZIP.

---

## 2. Move Project

Copy project into

```
xampp/htdocs/
```

Rename folder

```
seo_app
```

---

## 3. Start Server

Start

- Apache
- MySQL

using XAMPP.

---

## 4. Create Database

Open

```
http://localhost/phpmyadmin
```

Create database

```
seo_app
```

Import the provided SQL file.

---

## 5. Configure Database

Edit

```
config/db.php
```

```php
$host = "localhost";
$user = "root";
$password = "";
$database = "seo_app";
```

---

## 6. Install Dependencies

```bash
composer install
```

---

## 7. Run Application

```
http://localhost/seo_app
```

---

# 🔒 Security Features

- Session Authentication
- Login Protection
- Role-based Authorization
- SQL Injection Prevention
- Form Validation
- File Upload Validation
- Secure Database Connection

---

# 📦 Dependencies

- PHP 8+
- Composer
- MySQL
- Apache
- Google OAuth
- PHPMailer
- FPDF

---

# 💻 Requirements

- PHP 8+
- Apache Server
- MySQL 5.7+
- Composer
- XAMPP / WAMP / LAMP

---

# 🚀 Future Enhancements

Potential future improvements include:

- Email Notifications
- Advanced Search & Filters
- REST API
- Multi-language Support
- Dark Mode
- Backup & Restore
- Excel Export
- Email Invoice Delivery
- Advanced Role Permissions
- Activity Logs

---

# 👨‍💻 Developer

Developed using

- PHP
- MySQL
- JavaScript
- HTML5
- CSS3

---

# 📄 License

This project is developed for professional business management purposes.

You are free to modify, extend, and customize it according to your business requirements.

---

# ⭐ Support

If you found this project useful, consider giving it a ⭐ on GitHub.