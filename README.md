

# PHP Website Assembler

A lightweight build system for multi-page PHP websites that compiles modular source files into deployable endpoint files.

This project assembles pages from structured backend/frontend components, automatically resolves partials and assets, generates final PHP endpoints, and prepares a `/dist` folder ready for deployment.

## Features

- Modular multi-page PHP architecture
- Automatic page assembly into deployable endpoint files
- Recursive partial inclusion system
- Automatic CSS and JS asset registration
- Shared core backend injection
- Support for nested feature folders
- Standalone script auto-detection
- API endpoint generation
- Automatic `/dist` structure creation
- Automatic `.htaccess` protection for logs
- Minimal dependencies — pure PHP

---

## Project Structure

```txt
dist/                         # Generated deployable output
├── api/
├── css/
├── js/
├── .htaccess
└── *.php

src/
├── api/                      # API endpoint source files
├── core/                     # Backend files included in every page
├── frontend/
│   ├── partials/             # Reusable frontend partials
│   └── scripts/              # Standalone JS scripts
├── pages/                    # Page source files
└── .htaccess
```

### Page Structure Example

```txt
pages/
└── auth/
    └── login/
        ├── auth-login.php
        ├── auth-login-checks.php
        ├── auth-login-post.php
        ├── auth-login-fn.php
        ├── view-auth-login.php
        ├── view-auth-login.css
        └── view-auth-login.js
```

---

## How It Works

The assembler:

1. Loads shared backend core files
2. Discovers pages recursively
3. Merges:
   - core backend
   - page functions
   - checks
   - POST handlers
   - main page logic
4. Resolves frontend partials recursively
5. Automatically registers required CSS and JS assets
6. Injects asset html tags into the layout
7. Generates final endpoint files inside `/dist`
8. Copies required static files and scripts
9. Generates API endpoints

---

## Partial System

Partials are inserted using special directives:

```html
__PARTIAL_BOTTOM_NAVBAR__
```

The assembler:

- Finds the matching partial folder
- Loads the partial view
- Automatically registers:
  - `partial-name.css`
  - `partial-name.js`
- Resolves nested partials recursively

### Partial Folder Example

```txt
partials/
└── bottom-navbar/
    ├── bottom-navbar.php
    ├── bottom-navbar.css
    └── bottom-navbar.js
```

---

## Standalone Script Injection

Inside views:

```html
__SCRIPT_VALIDATE_FORM__
```

The assembler:

- Searches recursively inside `frontend/scripts`
- Registers the matching JS file automatically

Example output:

```html
<script src="js/validate-form.js"></script>
```

---

## Naming Convention

The system relies heavily on naming conventions.

### Page Files

| File | Purpose |
|---|---|
| `feature-page.php` | Main backend logic |
| `feature-page-fn.php` | Optional helper functions |
| `feature-page-checks.php` | Optional validation/auth checks |
| `feature-page-post.php` | Optional POST handling |
| `view-feature-page.php` | Main frontend view |
| `view-feature-page.css` | Page stylesheet |
| `view-feature-page.js` | Page script |

---

## Build Output

Generated files are placed into:

```txt
dist/
├── api/
├── css/
├── js/
└── *.php
```

Each generated page becomes a standalone endpoint containing:

- merged backend PHP
- rendered frontend layout
- asset references
- resolved partials

---

## Running the Assembler

Run from CLI or browser:

```bash
php assemble.php
```

or open:

```txt
/assemble.php
```

---

## Generated Endpoint Structure

A generated endpoint typically contains:

```php
<?php

// CORE
// FUNCTIONS
// CHECKS
// POST
// MAIN

?>

<!-- VIEW -->
```

---

## Design Goals

- Keep PHP simple and procedural
- Avoid framework overhead
- Enable modular architecture
- Reduce repetitive includes
- Centralize asset handling
- Keep deploy output clean and portable

---

## Intended Use Cases

- Traditional multi-page PHP websites
- Server-rendered applications
- Small-to-medium projects
- Framework-free architectures
- Developers who prefer procedural PHP

---

## Requirements

- PHP 8+
- Apache recommended (`.htaccess` support)

---

## Current Capabilities

- Recursive page discovery
- Recursive partial resolution
- Automatic asset registration
- Shared layout injection
- API generation support
- Dist folder assembly
- Standalone script discovery
- Log protection setup

---

## Future Improvements

Potential additions:

- Asset minification
- Dependency graph caching
- Incremental builds
- Hot reload/watch mode
- Route manifest generation
- Source maps
- CLI arguments
- Build profiles (dev/prod)

