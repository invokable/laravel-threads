# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

This is a Laravel package (`revolution/laravel-threads`) that provides comprehensive integration with Meta's Threads platform. The package enables Laravel developers to authenticate users via OAuth, publish content, manage posts, and send notifications through Threads' API.

## Common Development Commands

### Testing
```bash
# Run all tests
vendor/bin/phpunit

# Run specific test file
vendor/bin/phpunit tests/Feature/Client/ClientTest.php
```

### Code Quality
```bash
# Check code style (lint check)
vendor/bin/pint --test

# Fix code style issues
vendor/bin/pint
```

### Dependencies
```bash
# Install dependencies
composer install

# Update dependencies  
composer update
```

## Architecture Overview

The package is organized into four main subsystems:

### 1. Core API Client System
- **`ThreadsClient`** (`src/ThreadsClient.php`): Main HTTP client implementing the Factory contract
- **`Factory`** (`src/Contracts/Factory.php`): Interface defining all API client methods
- Handles two-phase content creation (create → publish)
- Manages token authentication, refresh, and quota monitoring
- Uses Laravel's Macroable trait for extensibility

### 2. Laravel Integration Layer
- **`ThreadsServiceProvider`** (`src/ThreadsServiceProvider.php`): Registers services and extends Socialite
- **`Threads`** facade (`src/Facades/Threads.php`): Provides static access to ThreadsClient
- **`WithThreads`** trait (`src/Traits/WithThreads.php`): Enables Eloquent model integration

### 3. OAuth Authentication System
- **`ThreadsProvider`** (`src/Socialite/ThreadsProvider.php`): Custom Socialite driver for Threads OAuth
- Handles authorization flow and token exchange
- Default scopes: `threads_basic`, `threads_content_publish`, `threads_delete`, `threads_keyword_search`

### 4. Notification System
- **`ThreadsChannel`** (`src/Notifications/ThreadsChannel.php`): Custom Laravel notification channel
- **`ThreadsMessage`** (`src/Notifications/ThreadsMessage.php`): Message structure for notifications
- Supports text, image, video, and poll content

## Key Components

### Enums
- **`MediaType`**: TEXT, IMAGE, VIDEO, CAROUSEL
- **`ReplyControl`**: EVERYONE, FOLLOW, MENTIONED permissions  
- **`SearchType`**: TOP, RECENT search ordering

### Core Methods in ThreadsClient
- Content creation: `createText()`, `createImage()`, `createVideo()`, `createCarousel()`
- Publishing: `publish()` (completes two-phase creation)
- Management: `delete()`, `repost()`, `search()`, `status()`, `quota()`
- Data retrieval: `profiles()`, `posts()`, `single()`, `replies()`
- Token management: `exchangeToken()`, `refreshToken()`

## Token Management

The package handles two types of Threads API tokens:
- **Short-lived tokens**: From OAuth flow, limited validity
- **Long-lived tokens**: 60-90 days validity, required for API operations
- Use `exchangeToken()` to convert short-lived to long-lived tokens
- Use `refreshToken()` to renew expired long-lived tokens

## Testing Structure

Tests are organized in `tests/Feature/`:
- `Client/ClientTest.php`: Core client functionality
- `Notifications/NotificationTest.php`: Notification system
- `Socialite/SocialiteTest.php`: OAuth integration

## Configuration

The package integrates with Laravel's service configuration for OAuth settings and uses dependency injection through the Factory contract. Service provider automatically registers ThreadsClient as a scoped service and extends Socialite with the Threads driver.