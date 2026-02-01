# E-commerce API

![Tests](https://github.com/alaminmishu/ecommerce-api/workflows/CI/badge.svg)
![PHP Version](https://img.shields.io/badge/PHP-8.4-blue)
![Laravel](https://img.shields.io/badge/Laravel-12-red)
![Tests](https://img.shields.io/badge/tests-23%20passing-brightgreen)

Production-grade RESTful API for e-commerce built with Laravel 12.

## 🚀 Features

- **Products**: CRUD, variants, image upload (WebP optimization)
- **Shopping Cart**: Guest + authenticated user support
- **Orders**: Complete checkout flow with order tracking
- **Payments**: Stripe integration with payment intents
- **Stock Management**: Automatic inventory reduction
- **API Documentation**: Interactive docs via Postman
- **Tests**: 23 tests, 95 assertions, 100% passing ✅
- **CI/CD**: Automated testing on every push

## 🛠️ Tech Stack

- **Backend**: Laravel 12, PHP 8.4
- **Database**: MariaDB/MySQL
- **Authentication**: Laravel Sanctum
- **Payments**: Stripe
- **Testing**: PHPUnit (23 tests, 95 assertions)
- **CI/CD**: GitHub Actions
- **Docs**: Postman, Scribe

## 📖 API Documentation

- **Live Docs**: [View on Postman](https://documenter.postman.com/view/YOUR-LINK)
- **Source Code**: [GitHub Repository](https://github.com/YOUR-USERNAME/ecommerce-api)

## 🧪 Testing
```bash
php artisan test
```

**Results:**
- 23 tests
- 95 assertions
- 100% passing ✅

## 🚀 Installation
```bash
# Clone repository
git clone https://github.com/YOUR-USERNAME/ecommerce-api.git

# Install dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed

# Run tests
php artisan test
```

## 📝 Environment Variables

Required `.env` variables:
```env
DB_CONNECTION=mysql
DB_DATABASE=ecommerce
STRIPE_KEY=your_stripe_key
STRIPE_SECRET=your_stripe_secret
```

## 🔐 Authentication

Uses Laravel Sanctum for API authentication.

Generate token:
```bash
POST /api/login
```

Use in requests:
```
Authorization: Bearer YOUR_TOKEN
```

## 📊 Project Stats

- **Code**: 4000+ lines
- **Commits**: 50+
- **Tests**: 23 (100% passing)
- **Endpoints**: 18 documented

## 👤 Author

**Al Amin Mishu**
- LinkedIn: [Your Profile]
- GitHub: [@alaminmishu](https://github.com/alaminmishu)


## 📝 License

MIT License
