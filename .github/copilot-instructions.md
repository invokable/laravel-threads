# Laravel Threads Package Onboarding Guide

## Overview

The `revolution/laravel-threads` package is a Laravel library that provides comprehensive integration with Meta's Threads platform. It serves Laravel developers who need to:

- **Authenticate users via Threads OAuth** using Laravel Socialite
- **Publish content to Threads** (text posts, images, videos, carousels)
- **Manage Threads content** (delete, repost, search)
- **Send notifications through Threads** via Laravel's notification system
- **Retrieve user profiles and posts** from the Threads API

The package abstracts the complexity of the Threads API behind familiar Laravel patterns (facades, service providers, notifications), enabling developers to integrate Threads functionality without dealing with raw HTTP requests or OAuth flows. It supports both direct API usage and Laravel's notification system for automated posting.

**Target Users**: Laravel application developers building social media integrations, content management systems, or marketing automation tools that need to interact with Threads.

## Project Organization

### Core Systems

The package is organized into four main subsystems:

1. **Core API Client System** (`src/ThreadsClient.php`, `src/Contracts/Factory.php`)
    - Handles direct HTTP communication with Threads API
    - Implements two-phase content creation (create → publish)
    - Manages token authentication and refresh

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
    - Supports text, image, and video content
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
- `ThreadsClient`: Main HTTP client with methods like `createText()`, `createImage()`, `publish()`, `delete()`
- `Factory`: Interface defining the client contract for dependency injection

**Laravel Integration:**
- `ThreadsServiceProvider::register()`: Binds Factory to ThreadsClient in service container
- `ThreadsServiceProvider::boot()`: Extends Socialite with Threads driver
- `Threads` (Facade): Provides static access like `Threads::createText()`
- `WithThreads::threads()`: Trait method returning configured client instance

**OAuth System:**
- `ThreadsProvider::redirect()`: Initiates OAuth authorization flow
- `ThreadsProvider::user()`: Handles callback and retrieves user data
- `ThreadsProvider::exchangeToken()`: Converts short-lived to long-lived tokens

**Notification System:**
- `ThreadsChannel::send()`: Processes notifications and dispatches to Threads
- `ThreadsMessage::create()`: Factory method for message construction
- `ThreadsMessage::withImage()`, `ThreadsMessage::withVideo()`: Fluent media attachment

### CI/CD Pipeline

- **`.github/workflows/test.yml`**: Runs PHPUnit tests across PHP 8.2-8.4, generates coverage
- **`.github/workflows/lint.yml`**: Enforces code style using Laravel Pint
- **`phpunit.xml`**: Configures test discovery and coverage reporting
- **`pint.json`**: Laravel coding standards with disabled unused imports rule

## Glossary of Codebase-Specific Terms

**ThreadsClient** - Core HTTP client class implementing Factory contract. Located: `src/ThreadsClient.php`

**Factory** - Interface defining API client methods like `createText()`, `publish()`. Located: `src/Contracts/Factory.php`

**ThreadsProvider** - Socialite driver for Threads OAuth authentication. Located: `src/Socialite/ThreadsProvider.php`

**ThreadsChannel** - Custom Laravel notification channel for sending to Threads. Located: `src/Notifications/ThreadsChannel.php`

**ThreadsMessage** - Data structure for notification content with text/image/video. Located: `src/Notifications/ThreadsMessage.php`

**WithThreads** - Trait providing `threads()` method for Eloquent models. Located: `src/Traits/WithThreads.php`

**ThreadsServiceProvider** - Laravel service provider registering package services. Located: `src/ThreadsServiceProvider.php`

**exchangeToken** - Method converting short-lived OAuth tokens to long-lived ones. Found in: `ThreadsClient::exchangeToken()`

**refreshToken** - Method renewing expired long-lived access tokens. Found in: `ThreadsClient::refreshToken()`

**MediaType** - Enum defining content types: TEXT, IMAGE, VIDEO, CAROUSEL. Located: `src/Enums/MediaType.php`

**ReplyControl** - Enum for reply permissions: EVERYONE, FOLLOW, MENTIONED. Located: `src/Enums/ReplyControl.php`

**SearchType** - Enum for search ordering: TOP, RECENT. Located: `src/Enums/SearchType.php`

**publish** - Two-phase API method completing content creation. Found in: `ThreadsClient::publish()`

**createText** - Method creating text-only posts on Threads. Found in: `ThreadsClient::createText()`

**createImage** - Method creating image posts with optional text. Found in: `ThreadsClient::createImage()`

**createVideo** - Method creating video posts with optional text. Found in: `ThreadsClient::createVideo()`

**createCarousel** - Method creating multi-media carousel posts. Found in: `ThreadsClient::createCarousel()`

**toThreads** - Notification method returning ThreadsMessage instance. Found in: custom notification classes

**routeNotificationForThreads** - Model method providing Threads token for notifications. Found in: Notifiable models

**threads** - Trait method returning configured ThreadsClient instance. Found in: `WithThreads::threads()`

**tokenForThreads** - Abstract method models must implement for token retrieval. Found in: `WithThreads` trait

**POST_DEFAULT_FIELDS** - Constant array of default API response fields. Found in: `ThreadsClient::POST_DEFAULT_FIELDS`

**withImage** - Fluent method adding image URL to ThreadsMessage. Found in: `ThreadsMessage::withImage()`

**withVideo** - Fluent method adding video URL to ThreadsMessage. Found in: `ThreadsMessage::withVideo()`

**auto_publish_text** - Option for immediate text post publishing. Found in: `createText()` options

**is_carousel** - Boolean flag marking media for carousel inclusion. Found in: media creation methods

**sleep** - Parameter controlling publish timing/delays. Found in: `publish()` and `ThreadsMessage`
