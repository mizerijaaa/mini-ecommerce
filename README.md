# Mini E-Commerce Platform (Laravel + Livewire Volt)

### 1) Project Overview
A small e-commerce application built with **Laravel** and **Livewire Volt** following a **Domain-Driven Design (DDD)** structure.

**Key features**
- **Marketplace**: browse products across vendors with filters + pagination
- **Cart**: add/remove items, update quantities, grouped by vendor with subtotals
- **Checkout (simulated payment)**: creates orders, handles success/failure rules
- **Buyer orders**: order history + order details
- **Vendor tools**: manage products (create/update/delete), view vendor order items, update item status
- **Admin dashboard**: high-level platform counters (users/vendors/products/orders)
- **Product status**: `draft`, `active`, `archived` (only active is sellable)

---

### 2) Tech Stack
- **Backend**: Laravel **13.x** (`laravel/framework:^13.0`)
- **PHP**: **>= 8.3** (`php:^8.3`)
- **Auth**: Laravel Breeze
- **UI / Interactivity**: Livewire **v4** + Volt (`livewire/livewire:^4.2`, `livewire/volt:^1.10`)
- **Styling**: Tailwind CSS (via Vite)
- **JS**: Alpine.js (small UI interactions), Axios
- **Database**: **MySQL** (recommended for local dev)
  - Note: `.env.example` defaults to SQLite, but this project is intended to run on MySQL.
- **Testing**: Pest + PHPUnit (`pestphp/pest`, `phpunit/phpunit`)

---

### 3) Architecture
This project uses a **Domain-Driven Design (DDD)** structure. Business logic is organized into bounded contexts under `app/Domain`.

**Main domains**
- **IdentityAndAccess**
  - Users, roles, registration/profile actions
- **ProductCatalog**
  - Vendors, products, marketplace search
- **Cart**
  - Cart + cart items, stock validation, cart actions
- **OrderManagement**
  - Checkout orchestration, orders + order items, order status transitions, payment simulation

---

### 4) Features

#### Buyer functionality
- Browse marketplace (`/marketplace`)
- Filter/search products (keyword/vendor/price range)
- Add items to cart, update quantities, remove items (`/cart`)
- Checkout with simulated payment (`/checkout`)
- View order history + details (`/buyer/orders`, `/buyer/orders/{orderId}`)

#### Vendor functionality
- Manage products (`/vendor/products`)
  - Create product
  - Update stock + status
  - Delete product
- View vendor order items (`/vendor/orders`)
- Update status of **their** order items (forward-only transitions)

#### Admin functionality (implemented)
- Role-based dashboard cards on `/dashboard` for admins:
  - total users, vendors, products, orders

#### Product status handling
- Status values: **draft / active / archived**
- Only **active** products appear in marketplace and can be purchased
- Draft/archived items in cart are treated as unavailable and block checkout

---

### 5) Business Logic

#### Cart stock validation
- Quantity changes validate stock via `CartStockValidationService`
- If requested qty exceeds stock, quantity is clamped and a warning is shown

#### Checkout flow (success/failure rules)
Implemented in `CheckoutService` within a `DB::transaction()`:
- Validates:
  - cart is not empty
  - each cart item has a product
  - **product is active**
  - stock is sufficient
- Creates `Order` + `OrderItem`s
- Simulates payment:
  - **fails if total > 999**
- On payment success:
  - decrements product stock
  - clears cart
  - sets order + order items status to **paid**
- On payment failure:
  - throws an error
  - cart remains intact

#### Order status transitions
Order item statuses follow:
- `pending → paid → shipped → delivered`
- Vendor updates are validated via `OrderStatus::canTransitionTo()` (forward-only)

#### Payment simulation
- `PaymentSimulatorService` returns a structured result
- Rule: **order totals over $999 are rejected**

---

### 6) Setup Instructions (macOS)

#### Prerequisites
- **PHP >= 8.3**
- **Composer**
- **Node.js + npm**
- **MySQL** (via Homebrew recommended)

Install MySQL (Homebrew):
```bash
brew update
brew install mysql
brew services start mysql
```

#### Clone repository
```bash
git clone https://github.com/mizerijaaa/mini-ecommerce.git
cd mini-ecommerce
```

#### Install backend dependencies
```bash
composer install
```

#### Install frontend dependencies
```bash
npm install
```

#### Create `.env` and configure MySQL
```bash
cp .env.example .env
```

Example MySQL config for `.env`:
```env
APP_ENV=local
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce
DB_USERNAME=root
DB_PASSWORD=
```

Create the database (one-time):
```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS ecommerce;"
```

#### Generate app key
```bash
php artisan key:generate
```

#### Run migrations + seeders
```bash
php artisan migrate:fresh --seed
```

#### Start the app
Run backend:
```bash
php artisan serve
```

Run frontend (Vite):
```bash
npm run dev
```

Optional (single command): the repository includes a `composer run dev` script which runs server, queue listener, logs, and Vite together:
```bash
composer run dev
```

#### Dev login accounts (seeded in local/testing)
After seeding, you can log in at `/login` using:
- **Admin**: `admin@example.com` / `password`
- **Vendor**: `vendor@example.com` / `password`
- **Buyer**: `buyer@example.com` / `password`

---

### 7) Running Tests
```bash
php artisan test
```

Pest is installed; tests run through Laravel’s test runner:
```bash
./vendor/bin/pest
```

---

### 8) Project Structure
Key folders:
- `app/Domain/`
  - Domain models, actions, DTOs, services, enums (DDD bounded contexts)
- `app/Http/`
  - Thin controllers (Breeze + profile), middleware
- `resources/views/livewire/`
  - Livewire Volt pages (marketplace, cart, checkout, dashboards, vendor/buyer pages)
- `database/migrations/`
  - Schema definitions (ULID primary keys, foreign keys, indexes, soft deletes)
- `database/seeders/` and `database/factories/`
  - Sample data generation
- `tests/Feature/`
  - Feature-level test coverage

---

### 9) Assumptions
- **Buyer access**: any authenticated user can shop (vendors can also buy).
- **Vendor access**: vendor workflows require a **Vendor profile**.
- **Product availability**:
  - only `active` products appear in marketplace and are purchasable
  - if a product becomes `draft`/`archived` while in the cart, cart/checkout block purchase with a clear message
- **Payment simulation**:
  - totals **> 999** fail; otherwise succeed
- **Status transitions** are forward-only for vendor order items.

---

### 10) Optional Improvements
- Dedicated **admin panel** (user/vendor management UI)
- Vendor product management enhancements:
  - edit name/description/price/image via UI
  - product image uploads (storage + validation)
- Better role model:
  - separate role assignments (many-to-many) instead of single `users.role`
- More granular authorization and policies (e.g., admin workflows)
- More exhaustive tests for UI flows and authorization edge cases

