# What The Quiz

A subscription-based quiz creation and playing platform built with PHP and Stripe integration.

## Features

- Create and manage quizzes
- Play quizzes created by yourself or others
- Monthly subscription model for quiz creators
- Secure payment processing with Stripe
- User authentication and authorization
- Modern and responsive UI

## Requirements

- PHP 8.0 or higher
- MySQL 5.7 or higher
- Composer
- Stripe API key
- Web server (Apache/Nginx)

## Installation

1. Clone the repository
2. Run `composer install` to install dependencies
3. Copy `.env.example` to `.env` and configure your environment variables
4. Set up your database and run migrations
5. Configure your web server to point to the `public` directory

## Configuration

Make sure to set up the following environment variables in your `.env` file:
- `DB_HOST`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`
- `STRIPE_SECRET_KEY`
- `STRIPE_PUBLISHABLE_KEY`

## License

MIT License