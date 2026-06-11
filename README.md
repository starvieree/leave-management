# Leave Management System API

## Overview

Leave Management System API merupakan RESTful API yang dibangun menggunakan Laravel 11 untuk mengelola proses pengajuan cuti karyawan. Sistem ini menerapkan autentikasi menggunakan Laravel Sanctum serta mendukung OAuth Login melalui Google OAuth. API dirancang menggunakan pendekatan Clean Architecture dengan pemisahan tanggung jawab melalui Controller, Service Layer, Repository Pattern, Request Validation, Policy Authorization, Resource Response, dan Middleware.

Sistem ini memungkinkan karyawan mengajukan cuti dengan melampirkan dokumen pendukung, sedangkan administrator dapat melakukan proses persetujuan atau penolakan terhadap pengajuan cuti tersebut.

---

## Tech Stack

### Backend

* Laravel 11
* PHP 8.3

### Database

* MySQL

### Authentication

* Laravel Sanctum
* Google OAuth (Laravel Socialite)

### Documentation

* Postman

### Additional Package

* Laravel Sanctum
* Laravel Socialite
* L5 Swagger (Setup tersedia namun belum digunakan penuh)
* Laravel Storage

---

# Fitur Utama

## Authentication

### Register

Pengguna dapat membuat akun baru sebagai Employee.

### Login

Pengguna dapat melakukan login menggunakan email dan password.

### Logout

Pengguna dapat mengakhiri sesi login dan menghapus token autentikasi.

### Google OAuth Login

Pengguna dapat login menggunakan akun Google.

---

## Role Management

Sistem memiliki dua role:

### Employee

Hak akses:

* Login
* Logout
* Melihat data profil sendiri
* Mengajukan cuti
* Melihat seluruh riwayat cuti miliknya sendiri

### Admin

Hak akses:

* Login
* Logout
* Melihat seluruh data pengajuan cuti
* Menyetujui pengajuan cuti
* Menolak pengajuan cuti

---

## Leave Management

### Pengajuan Cuti

Employee wajib mengisi:

* Start Date
* End Date
* Reason
* Attachment

Status awal pengajuan:

Pending

---

### Approval Workflow

Alur pengajuan cuti:

Pending → Approved

atau

Pending → Rejected

---

### Annual Leave Quota

Setiap karyawan memiliki kuota cuti:

12 Hari / Tahun

Saat pengajuan dilakukan:

1. Sistem menghitung jumlah hari cuti.
2. Sistem mengecek kuota tersisa.
3. Sistem menolak jika kuota tidak mencukupi.
4. Sistem menolak jika tanggal cuti bertabrakan dengan cuti lain yang masih Pending atau Approved.

---

# Arsitektur Sistem

Sistem dibangun menggunakan pendekatan Layered Architecture.

## 1. Controller Layer

Controller hanya bertanggung jawab menerima request dan mengembalikan response.

Contoh:

AuthController

* Register
* Login
* Logout
* OAuth Login

LeaveController

* Create Leave Request
* View Own Leave Request

AdminLeaveController

* View All Leave Requests
* Approve Leave
* Reject Leave

---

## 2. Request Validation Layer

Setiap request divalidasi menggunakan Form Request.

Contoh:

RegisterRequest

* name wajib
* email wajib
* email unik
* password minimal 8 karakter

StoreLeaveRequest

* start_date wajib
* end_date wajib
* reason wajib
* attachment wajib

Tujuan:

* Menjaga kebersihan controller
* Memastikan data valid sebelum masuk ke business logic

---

## 3. Service Layer

Business logic dipisahkan dari controller.

Contoh:

LeaveService

Tanggung jawab:

* Menghitung durasi cuti
* Mengecek kuota cuti
* Mengecek overlap tanggal
* Membuat pengajuan cuti

Keuntungan:

* Controller lebih bersih
* Logic mudah diuji
* Mudah digunakan ulang

---

## 4. Repository Layer

Repository bertanggung jawab mengakses database.

Contoh:

LeaveRepository

Method:

* create()
* findById()
* getEmployeeLeaves()
* getAllLeaves()

Keuntungan:

* Database abstraction
* Mudah mengganti database tanpa mengubah service

---

## 5. Policy Authorization

Policy digunakan untuk memastikan user hanya dapat mengakses data yang berhak diakses.

Contoh:

Employee hanya dapat melihat pengajuan miliknya sendiri.

Admin dapat melihat seluruh pengajuan.

---

## 6. Resource Layer

API Response menggunakan Laravel API Resource.

Tujuan:

* Konsistensi response
* Mempermudah transformasi data

---

# Database Design

## users

| Field    | Type                 |
| -------- | -------------------- |
| id       | bigint               |
| name     | varchar              |
| email    | varchar              |
| password | varchar              |
| role     | enum(admin,employee) |

---

## leave_requests

| Field       | Type    |
| ----------- | ------- |
| id          | bigint  |
| employee_id | bigint  |
| start_date  | date    |
| end_date    | date    |
| total_days  | integer |
| reason      | text    |
| attachment  | varchar |
| status      | enum    |

---

## leave_quotas

| Field   | Type    |
| ------- | ------- |
| id      | bigint  |
| user_id | bigint  |
| year    | integer |
| quota   | integer |
| used    | integer |

---

## leave_histories

| Field            | Type    |
| ---------------- | ------- |
| id               | bigint  |
| leave_request_id | bigint  |
| action           | varchar |
| description      | text    |

---

# Flow Sistem

## Employee

Login/Register

↓

Submit Leave Request

↓

Status Pending

↓

Menunggu Review Admin

↓

Approved / Rejected

---

## Admin

Login

↓

Melihat Seluruh Pengajuan

↓

Approve atau Reject

↓

Status Berubah

↓

Kuota Cuti Terupdate

---

# Instalasi Project

## Clone Repository

```bash
git clone <repository-url>
cd leave-management
```

## Install Dependency

```bash
composer install
```

## Setup Environment

```bash
cp .env.example .env
```

## Generate Key

```bash
php artisan key:generate
```

---

# Setup .env

## Database

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=leave_management
DB_USERNAME=root
DB_PASSWORD=
```

---

## Sanctum

Tidak memerlukan konfigurasi tambahan.

---

## Google OAuth

```env
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://127.0.0.1:8000/api/auth/google/callback
```

---

## Mail Configuration

```env
MAIL_MAILER=log

MAIL_HOST=127.0.0.1
MAIL_PORT=1025

MAIL_USERNAME=null
MAIL_PASSWORD=null

MAIL_ENCRYPTION=null

MAIL_FROM_ADDRESS=test@mail.com
MAIL_FROM_NAME="Leave Management"
```

---

# Migrasi Database

```bash
php artisan migrate
```

---

# Menjalankan Aplikasi

```bash
php artisan serve
```

Aplikasi berjalan pada:

```text
http://127.0.0.1:8000
```

---

# Pengujian Menggunakan Postman

## 1. Register Employee

POST

```http
/api/register
```

Body:

```json
{
    "name": "Ainur Reza",
    "email": "reza@gmail.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

---

## 2. Login

POST

```http
/api/login
```

Body:

```json
{
    "email": "reza@gmail.com",
    "password": "password123"
}
```

Response menghasilkan Bearer Token.

---

## 3. Create Leave Request

POST

```http
/api/leave-requests
```

Authorization:

Bearer Token

Body Form Data:

* start_date
* end_date
* reason
* attachment

---

## 4. View Own Leave Request

GET

```http
/api/leave-requests
```

Authorization:

Bearer Token

---

## 5. Login Admin

POST

```http
/api/login
```

Body:

```json
{
    "email": "admin@mail.com",
    "password": "password"
}
```

---

## 6. View All Leave Request

GET

```http
/api/admin/leave-requests
```

Authorization:

Bearer Admin Token

---

## 7. Approve Leave

PATCH

```http
/api/admin/leave-requests/{id}/approve
```

Authorization:

Bearer Admin Token

---

## 8. Reject Leave

PATCH

```http
/api/admin/leave-requests/{id}/reject
```

Authorization:

Bearer Admin Token

Body:

```json
{
    "reason": "Dokumen tidak lengkap"
}
```

---

# Postman Documentation

Postman Collection:

https://solar-spaceship-805312.postman.co/workspace/My-Workspace~756cf2e8-3712-48bf-8903-23253cc7c126/collection/37147770-3e648765-7783-4bde-be7a-19bf84a2a35f?action=share&creator=37147770

---

# Catatan Implementasi

Implementasi yang telah diselesaikan:

✓ Conventional Authentication

✓ Google OAuth Authentication

✓ Role Based Access Control

✓ Leave Quota Management

✓ Leave Approval Workflow

✓ File Attachment Upload

✓ Leave History Tracking

✓ Repository Pattern

✓ Service Layer

✓ Form Request Validation

✓ API Resource Response

✓ Policy Authorization

✓ MySQL Database Integration

Fitur lanjutan seperti Automated Testing, Queue Worker, Docker, CI/CD, dan Swagger Documentation telah dipersiapkan pada tahap pengembangan namun belum diimplementasikan secara penuh pada versi submission ini.
