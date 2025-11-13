# Velix PHP Micro Framework

Velix is a lightweight PHP router designed to work seamlessly with frontend frameworks like **React**, **Vue**, **Svelte**, or any static frontend project (HTML/JS/CSS).  
It automatically handles API routes and frontend fallback without extra configuration.

---

## 📁 Project Structure

```
.
├── Velix.php
├── .htaccess
└── public/
    ├── app.php
    └── index.html
```

---

## ⚙️ .htaccess Configuration

The `.htaccess` file handles routing automatically.

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

# php_flag display_errors on
```

🧩 **Explanation:**
- Requests for existing files are served directly (e.g., `/main.js`, `/style.css`).
- All other routes are redirected to `public/app.php`.
- No need to manually serve `index.html` — Apache and `.htaccess` handle this automatically.

---

## 🚀 Usage

### Step 1 — Include Velix
```php
require_once __DIR__ . '/../Velix.php';

$velix = new Velix();
```

### Step 2 — Define Routes
```php
$velix->get('/api/hello', function(Request $req, Response $res) {
    return $res->json(['message' => 'Hello from Velix!']);
});

$velix->post('/api/users/{id}', function(Request $req, Response $res, $id) {
    $data = $req->json;
    return $res->json(['userId' => $id, 'data' => $data]);
});

$velix->dispatch();
```

---

## 🍪 Cookies Example

```php
$res->cookie('username', 'Smith', [
    'expire' => time() + 3600,
    'path' => '/',
    'secure' => true,
    'httpOnly' => true,
    'sameSite' => 'Strict'
]);
```

### 🔹 Notes
- `domain` is automatically set by the browser.
- `expire` defines the lifetime of the cookie (0 = session cookie).

---

## 🧠 Request Helpers

| Method | Description | Example |
|--------|--------------|----------|
| `$req->input('name')` | Get POST/JSON data | `$_POST['name']` or JSON |
| `$req->query('page')` | Get query string | `/api/users?page=2` |
| `$req->param('id')` | Get route parameter | `/api/users/{id}` |
| `$req->header('Authorization')` | Get HTTP header | `Bearer token` |

---

## 🧾 Response Helpers

| Method | Description | Example |
|--------|--------------|----------|
| `$res->json($data)` | Return JSON response | `$res->json(['ok' => true])` |
| `$res->status(404)` | Set status code | `$res->status(404)->json(['error'=>'Not found'])` |
| `$res->header('X-Test', 'Hello')` | Add header |  |

---

## 🌐 Frontend Integration

Velix is designed for **single-page applications** (SPA).  
It automatically works with any frontend build placed in `/public` — such as React, Vue, or Svelte.

- Build your frontend → output files into `/public`
- Deploy as-is — `.htaccess` ensures all non-file requests are routed to `public/app.php`
- No need to manually serve `index.html`

Example React deployment:
```
npm run build
# Copy dist or build output into /public
```

---

## 🧩 Example API Call (Frontend)

```js
fetch('/api/hello')
  .then(res => res.json())
  .then(data => console.log(data))
```

---

## 🧱 Summary

✅ Automatic static file handling  
✅ Works with any frontend framework  
✅ Simple routing syntax  
✅ UTF-8 and Thai URL support  
✅ Cookie and header management  
✅ JSON body parsing  

---

© 2025 Velix — MIT Licensed

