# Velix PHP Framework
Velix is a lightweight, flexible PHP micro-framework designed to make web development fast and intuitive. It simplifies routing, request handling, and response management with a clean and modern API. Whether you're building a small API or a dynamic web app, Velix offers a hassle-free experience with minimal setup.

## Features
- **Simple Routing**: Define routes for GET, POST, and other HTTP methods with ease.
- **Flexible Handlers**: Route handlers accept dynamic arguments, including `Request`, `Response`, or route parameters, in any order.
- **Request Handling**: Access query params, body data, JSON payloads, and headers effortlessly.
- **Response Control**: Set headers, cookies, status codes, and send JSON or raw responses with a fluent API.
- **Lightweight**: Minimal dependencies, perfect for small to medium projects.
- **UTF-8 Support**: Built-in support for multilingual URLs and data.
- **Static File Serving**: Automatically serves static files from the `public` directory.

## Installation
1. Clone or download the Velix framework.
```bash
git clone https://github.com/wetoon/Velix-Framework.git velix-app
```
2. Place the `Velix.php` file in your project root.
3. Set up a web server (e.g., Apache) with the provided `.htaccess` to route requests through `public/app.php`.
4. Ensure the `public` directory contains your `app.php` and any static files (e.g., `index.html`).
Example `.htaccess` (already included):
```apache
DirectoryIndex public/app.php
RewriteEngine On
RewriteCond %{REQUEST_URI} ^/public/(.*)$
RewriteCond %{DOCUMENT_ROOT}/public/%1 -f
RewriteCond %1 !\.php$ [NC]
RewriteRule ^public/(.*)$ /%1 [L,R=301]
RewriteCond %{DOCUMENT_ROOT}/public%{REQUEST_URI} -f
RewriteRule ^ public%{REQUEST_URI} [L]
RewriteRule ^ public/app.php [L]
```

## Project Structure
```
.
├── Velix.php
├── .htaccess
└── public/
    ├── app.php
    └── index.html
```

## Quick Start
Create `public/app.php` to define your routes and start the server.
```php
<?php
require_once __DIR__ . '/../Velix.php';

$velix = new Velix();

// Simple GET route
$velix->get('/', function () {
    return ['message' => 'Welcome to Velix!'];
});

// Dynamic route with parameters
$velix->get('/user/{id}', function ($id, Request $req, Response $res) {
    return ['user_id' => $id, 'query' => $req->query('name')];
});

// POST route with JSON handling
$velix->post('/data', function (Request $req, Response $res) {
    $data = $req->json; // Auto-parsed JSON body
    $res->status(201)->json(['received' => $data]);
});

$velix->dispatch();
```
Run your server, and Velix will handle the rest!

## Flexible Route Handlers
Velix's route handlers are designed for ultimate flexibility. You can define handlers with any combination of arguments, and Velix will intelligently inject the right values:
- `Request` or `req`: The `Request` object for accessing query, body, headers, or params.
- `Response` or `res`: The `Response` object for setting headers, cookies, or status codes.
- **Route Parameters**: Automatically injected if their names match the route's placeholders (e.g., `{id}`).
- **Optional Args**: Missing parameters are passed as `null`, so you can omit unnecessary arguments.
Example:
```php
$velix->get('/post/{id}', function ($id, Request $req) {
    return ['post_id' => $id, 'category' => $req->query('category', 'general')];
});
```
You can also use `Response` for custom responses:
```php
$velix->get('/cookie', function (Response $res) {
    $res->cookie('theme', 'dark', ['expire' => time() + 3600, 'path' => '/'])
        ->json(['message' => 'Cookie set!']);
});
```

## Request Object
The `Request` class makes it easy to access incoming data:
- `$req->method`: HTTP method (e.g., GET, POST).
- `$req->uri`: Request URI.
- `$req->params`: Route parameters (e.g., {id}).
- `$req->query($key, $default)`: Get query string values.
- `$req->input($key, $default)`: Get body or JSON data.
- `$req->header($key, $default)`: Get request headers.
- `$req->json`: Auto-parsed JSON payload.
Example:
```php
$velix->post('/submit', function (Request $req) {
    return [
        'name' => $req->input('name', 'Guest'),
        'token' => $req->header('Authorization'),
        'query' => $req->query('page', 1)
    ];
});
```

## Response Object
The `Response` class provides a chainable API for building responses:
- `$res->header($name, $value)`: Set a response header.
- `$res->cookie($name, $value, $options)`: Set a cookie with options (expire, path, secure, etc.).
- `$res->status($code)`: Set HTTP status code.
- `$res->json($data)`: Send JSON response.
- `$res->send($body)`: Send raw response.
Example:
```php
$velix->get('/api', function (Response $res) {
    $res->header('X-Version', '1.0')
        ->status(200)
        ->json(['status' => 'ok']);
});
```

## Static Files
Place static files (e.g., `index.html`, CSS, JS) in the `public` directory. The `.htaccess` configuration ensures they are served directly, while dynamic routes are handled by `app.php`.
Example:
- `public/index.html` → Accessible at `http://your-site/`
- `public/style.css` → Accessible at `http://your-site/style.css`

## Error Handling
If no route matches, Velix serves `public/index.html` (if it exists) or returns a `404 Not Found` response.

## Why Velix?
- `Minimal Setup`: Get started in minutes with a single PHP file.
- `Developer-Friendly`: Intuitive APIs and flexible handlers reduce boilerplate code.
- `Lightweight`: No heavy dependencies, ideal for small projects or APIs.
- `Customizable`: Easily extend with your own middleware or logic.

## License
Velix is open-source under the MIT License.
