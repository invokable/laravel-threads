Laravel Threads
====

https://developers.facebook.com/docs/threads

**Work in progress**

The Threads API does not provide a full-featured SNS client API, so it is recommended to only use Socialite for OAuth authentication and the notification function.

## Requirements
- PHP >= 8.2
- Laravel >= 11.0

## Installation

```shell
composer require revolution/laravel-threads
```

### Uninstall
```shell
composer remove revolution/laravel-threads
```

## Getting Started
To use the Threads API, you need to create an "App" on Meta for Developers.
https://developers.facebook.com/

- A Facebook account is required
- "Create App"
- "Do not link to a business portfolio"
- Select Threads API as the use case

## Customizing the Use Case
Once you reach the app dashboard, customize the Threads API settings from the use case.

### Permissions
`threads_basic` is required and enabled by default. Enable `threads_content_publish` for posting, `threads_delete` for deleting, and `threads_keyword_search` for searching.

### Settings
Add your Threads account as a Threads tester. Approve it on the Threads website. Generate a token. If you only want to post to your own account, this token is enough.

If you want to get a token via Socialite/OAuth authentication, you also need to set the callback URL.

## Usage
- [Basic Client](./docs/basic-client.md)
- [Socialite](./docs/socialite.md)
- [Laravel Notifications](./docs/notification.md)

## LICENCE
MIT
