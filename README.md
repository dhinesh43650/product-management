# Product Management — Laravel PHP Skills Test

A Laravel web application with a product form that saves data to a JSON file
and displays a live-updating table with AJAX — no page reload required.

---

## Features

- **Add products** via a clean Bootstrap 5 form (name, quantity, price)
- **Edit any row** in-place using the pencil button
- **AJAX-powered** — all adds and edits update the table without a full page reload
- **JSON storage** — data persisted to `storage/app/products.json`
- **Auto-calculated** Total Value (Quantity × Price) per row
- **Grand Total** row at the bottom of the table
- Rows sorted by datetime submitted (oldest → newest)
- Fully responsive — works on mobile and desktop

---

## Requirements

| Tool    | Version  |
|---------|----------|
| PHP     | ≥ 8.1    |
| Composer| ≥ 2.x    |
| Laravel | 10.x     |
| Node    | Optional (only if rebuilding assets) |

---

## Quick Start

```bash
# 1. Extract the zip and enter the project folder
cd product-management

# 2. Install PHP dependencies
composer install

# 3. Copy the environment file
cp .env.example .env

# 4. Generate the application key
php artisan key:generate

# 5. Make sure storage is writable
chmod -R 775 storage bootstrap/cache

# 6. (Optional) create a symlink for public storage
php artisan storage:link

# 7. Start the development server
php artisan serve
```

Then open **http://127.0.0.1:8000** in your browser.

---

## File Structure

```
product-management/
├── app/Http/Controllers/
│   └── ProductController.php   ← All business logic (store / update / list)
├── routes/
│   └── web.php                 ← GET /, POST /products, PUT /products/{id}
├── resources/views/
│   └── products.blade.php      ← Single-page Blade template
├── public/
│   ├── css/app.css             ← Custom styles (Bootstrap 5 override)
│   └── js/app.js               ← All AJAX logic (vanilla JS, no jQuery)
├── storage/app/
│   └── products.json           ← Flat-file data store
└── database/migrations/
    └── *_create_products_table.php  ← Optional if you want a real DB
```

---

## API Endpoints (AJAX)

| Method | URL               | Description              |
|--------|-------------------|--------------------------|
| GET    | `/`               | Main page (Blade view)   |
| POST   | `/products`       | Add a new product        |
| PUT    | `/products/{id}`  | Update an existing one   |
| GET    | `/products/list`  | Fetch all as JSON        |

All POST/PUT endpoints accept and return JSON.  
All mutating requests require the Laravel CSRF token (injected into `window.AppConfig.csrfToken`).

---

## Notes

- No database setup is required — data is stored in `storage/app/products.json`.
- The `.env` file only needs `APP_KEY` to be set (done automatically by `php artisan key:generate`).
- Bootstrap 5 and Bootstrap Icons are loaded from CDN; no `npm install` needed.
