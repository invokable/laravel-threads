# Laravel Threads Package Onboarding Guide

## Overview

The `revolution/laravel-threads` package is a Laravel library that provides comprehensive integration with Meta's Threads platform. It serves Laravel developers who need to:

- **Authenticate users via Threads OAuth** using Laravel Socialite
- **Publish content to Threads** (text posts, images, videos, carousels, polls)
- **Manage Threads content** (delete, repost, search, status checking)
- **Send notifications through Threads** via Laravel's notification system
- **Retrieve user profiles and posts** from the Threads API
- **Monitor publishing quotas** and API limits

The package abstracts the complexity of the Threads API behind familiar Laravel patterns (facades, service providers, notifications), enabling developers to integrate Threads functionality without dealing with raw HTTP requests or OAuth flows. It supports both direct API usage and Laravel's notification system for automated posting, with extensibility through Laravel's macro system.

**Target Users**: Laravel application developers building social media integrations, content management systems, or marketing automation tools that need to interact with Threads.

## Token Management

The package handles two types of Threads API tokens:

- **Short-lived tokens**: Obtained through Socialite OAuth flow, valid for limited time
- **Long-lived tokens**: Valid for 60-90 days, required for API operations

Key token management features:
- Automatic conversion from short-lived to long-lived tokens via `exchangeToken()`
- Token refresh capability via `refreshToken()` for continuous access
- Support for Threads tester tokens (pre-generated as long-lived)
- Database storage recommended (tokens cannot be stored in `.env` permanently)

## Project Organization

### Core Systems

The package is organized into four main subsystems:

1. **Core API Client System** (`src/ThreadsClient.php`, `src/Contracts/Factory.php`)
    - Handles direct HTTP communication with Threads API
    - Implements two-phase content creation (create → publish)
    - Manages token authentication, refresh, and quota monitoring
    - Supports extensibility through Laravel's macro system

2. **Laravel Integration Layer** (`src/ThreadsServiceProvider.php`, `src/Facades/Threads.php`, `src/Traits/WithThreads.php`)
    - Registers services with Laravel's container
    - Provides static facade interface
    - Enables Eloquent model integration via traits

3. **OAuth Authentication System** (`src/Socialite/ThreadsProvider.php`)
    - Extends Laravel Socialite for Threads OAuth
    - Handles authorization flow and token exchange
    - Maps Threads user data to Laravel user objects

4. **Notification System** (`src/Notifications/ThreadsChannel.php`, `src/Notifications/ThreadsMessage.php`)
    - Custom Laravel notification channel for Threads
    - Supports text, image, video, and poll content
    - Integrates with Laravel's notification infrastructure

### Main Files and Directories

```
src/
├── Contracts/
│   └── Factory.php              # Interface defining API client contract
├── Enums/
│   ├── MediaType.php           # TEXT, IMAGE, VIDEO, CAROUSEL types
│   ├── ReplyControl.php        # EVERYONE, FOLLOW, MENTIONED permissions
│   └── SearchType.php          # TOP, RECENT search ordering
├── Facades/
│   └── Threads.php             # Static facade for ThreadsClient
├── Notifications/
│   ├── ThreadsChannel.php      # Custom notification channel
│   └── ThreadsMessage.php      # Message structure for notifications
├── Socialite/
│   └── ThreadsProvider.php     # OAuth provider for Laravel Socialite
├── Traits/
│   └── WithThreads.php         # Trait for Eloquent model integration
├── ThreadsClient.php           # Core API client implementation
└── ThreadsServiceProvider.php  # Laravel service provider

tests/Feature/
├── Client/ClientTest.php       # Core client functionality tests
├── Notifications/NotificationTest.php # Notification system tests
└── Socialite/SocialiteTest.php # OAuth integration tests

docs/
├── basic-client.md            # Facade usage examples
├── socialite.md              # OAuth setup and usage
└── notification.md           # Notification system guide
```

### Main Classes and Functions

**Core API Client:**
- `ThreadsClient`: Main HTTP client with methods like `createText()`, `createImage()`, `createVideo()`, `createCarousel()`, `publish()`, `delete()`, `repost()`, `search()`, `profiles()`, `posts()`, `single()`, `replies()`, `status()`, `quota()`
- `Factory`: Interface defining the client contract for dependency injection

**Laravel Integration:**
- `ThreadsServiceProvider::register()`: Binds Factory to ThreadsClient as scoped service in container
- `ThreadsServiceProvider::boot()`: Extends Socialite with Threads driver using service configuration
- `Threads` (Facade): Provides static access like `Threads::createText()` with macro and conditional support
- `WithThreads::threads()`: Trait method returning configured client instance

**OAuth System:**
- `ThreadsProvider::redirect()`: Initiates OAuth authorization flow with default scopes
- `ThreadsProvider::user()`: Handles callback and retrieves user data
- `ThreadsProvider::exchangeToken()`: Converts short-lived to long-lived tokens
- Default scopes: `['threads_basic', 'threads_content_publish', 'threads_delete', 'threads_keyword_search']`

**Notification System:**
- `ThreadsChannel::send()`: Processes notifications and dispatches to Threads
- `ThreadsMessage::create()`: Factory method for message construction
- `ThreadsMessage::withImage()`, `ThreadsMessage::withVideo()`: Fluent media attachment methods

### CI/CD Pipeline

- **`.github/workflows/test.yml`**: Runs PHPUnit tests across PHP 8.2-8.4, generates coverage with Qlty integration
- **`.github/workflows/lint.yml`**: Enforces code style using Laravel Pint (PHP 8.4)
- **`.github/workflows/copilot-setup-steps.yml`**: Automated setup workflow for GitHub Copilot integration
- **`phpunit.xml`**: Configures test discovery and coverage reporting to `build/logs/clover.xml`
- **`pint.json`**: Laravel coding standards with disabled unused imports rule
