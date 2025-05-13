# Portal Staff Himalkom

Nama belum fix, but Portal Staff Himalkom looks good tho

### What is This?

Management Information System — a centralized platform for managing all aspects of departmental initiatives and work programs. Supervisors and higher ups can easily access detailed information, descriptions, and related documents for each work program — all in one unified platform.

### User Roles & Access Control Overview

-   The system supports three primary user roles: Managing Director, BPH, and Supervisor, each with distinct levels of access and responsibility. Additionally, a Superadmin, represented by the Research and Technology Department, oversees user management and system configuration.

-   Superadmin (Research and Technology Department):
    Responsible for creating user accounts, assigning appropriate departmental affiliations, and defining user roles within the system.

-   Managing Director:
    Authorized to create, read, update, and delete work programs specific to their department.

-   BPH:
    Functions as both a department and a governance role and visibility into all departments and their respective work programs.

-   Supervisor:
    Granted read-only access to view all departments and their work programs for monitoring and evaluation purposes.

### Tech Stack

![Laravel](https://img.shields.io/badge/laravel-%23FF2D20.svg?style=for-the-badge&logo=laravel&logoColor=white)

[![TailwindCSS](https://img.shields.io/badge/Tailwind%20CSS-%2338B2AC.svg?logo=tailwind-css&logoColor=white)](#)
[![jQuery](https://img.shields.io/badge/jQuery-0769AD?logo=jquery&logoColor=fff)](#)
[![Alpine.js](https://img.shields.io/badge/Alpine.js-8BC0D0?logo=alpinedotjs&logoColor=fff)](#)

### Installation

```bash
# Clone the repository
git clone https://github.com/AghnatHs/web-staff-himalkom.git

cd web-staff-himalkom

# Install PHP dependencies
composer install

# Install JS dependencies
npm install && npm run dev

# Copy .env and set your config
cp .env.example .env
# BE SURE TO SET YOUR ENV CORRECTLY

# Generate app key
php artisan key:generate

# Configure your DB in .env then run:
php artisan migrate

# Seed the db
php artisan db:seed

# Link the storage
php artisan link or "ln -s $(pwd)/storage/app/public $(pwd)/public/storage"

# Run in development
php artisan serve
npm run dev

# access
http://127.0.0.1:8000/superadmin for creating Users and Department
http://127.0.0.1:8000 for main website
```
