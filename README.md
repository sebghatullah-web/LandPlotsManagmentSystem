# 🏗️ Land Plot Management System

A simple **Land Plot Management System** developed for a construction company to manage projects, land plots, customers, and sales efficiently.

This system helps prevent duplicate sales of plots and allows the company to manage customer and sales data in an organized database.

---

## 📌 Project Overview

This project is designed for a **construction company managing land plots in development projects**.

The system allows administrators to:

- Manage development **projects**
- Register and manage **customers**
- Store and manage **land plots**
- Record **plot sales**
- Control **system users**

The goal is to simplify the management of land plots and ensure accurate tracking of sales and customers.

---

## ⚙️ Technologies Used

- HTML5
- CSS3
- JavaScript
- Bootstrap
- PHP
- MySQL

---

## 🗄️ Database Structure

The system database contains the following tables:

### 1️⃣ tblProjects
Stores information about development projects.

Fields:
- id
- project_name
- location
- description
- created_at

---

### 2️⃣ tblCustomers
Stores customer information.

Fields:
- id
- fullName
- fatherName
- tazkira
- phone
- address
- created_at

---

### 3️⃣ tblPlots
Stores land plot information.

Fields:
- id
- plot_no
- project_id
- area
- price
- status (Available / Sold / Reserved)
- map_file
- owner_id
- description
- created_at

Each plot belongs to a project.

---

### 4️⃣ tblSales
Stores information about plot sales.

Fields:
- id
- plot_id
- customer_id
- sale_date
- total_amount
- contract_file
- note

---

### 5️⃣ tblUsers
Stores system user accounts.

Fields:
- id
- username
- password
- role (Admin / Sales / Viewer)
- created_at

---

## 🔗 Database Relationships

- One **Project** can have multiple **Plots**
- One **Customer** can buy multiple **Plots**
- Each **Sale** links a **Customer** with a **Plot**

---

## 🚀 Features

- Project management
- Plot management
- Customer registration
- Plot sales tracking
- File storage for contracts and maps
- User role management

---

## 📂 Installation

1. Clone the repository

2. Move the project to your local server directory

Example (XAMPP):

3. Import the database

- Open **phpMyAdmin**
- Create a new database
- Import the provided SQL file

4. Configure database connection

Update database credentials in:

---

## 🔐 User Roles

The system supports different user roles:

- **Admin** – Full access
- **Sales** – Manage sales and customers
- **Viewer** – Read-only access

---

## 📊 Future Improvements

- Payment installment management
- Advanced reports
- Customer online portal
- Map visualization for plots
- Notification system

---

## 👨‍💻 Author

Developed by **Sebghatullah**

---

## 📄 License

This project is open-source and available for educational and business use.
