# Contributing to PHP MVC Admin Starter

Thank you for considering contributing to PHP MVC Admin Starter! This document outlines the guidelines and standards for contributing to this PHP MVC authentication starter project.

## Table of Contents

- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Code Standards](#code-standards)
- [Documentation Standards](#documentation-standards)
- [Commit Guidelines](#commit-guidelines)
- [Pull Request Process](#pull-request-process)
- [Version Management](#version-management)

## Getting Started

1. Fork the repository on GitHub
2. Clone your fork locally
3. Set up the development environment following the README instructions
4. Create a new branch for your feature or bugfix

## Development Setup

```bash
# Clone your fork
git clone https://github.com/yourusername/php-mvc-admin-starter.git
cd php-mvc-admin-starter

# Install dependencies
composer install

# Set up upstream remote
git remote add upstream https://github.com/Jandres25/php-mvc-admin-starter.git

# Create and switch to a new branch
git checkout -b feature/your-feature-name
```

## Code Standards

### PHP Code Standards

- Follow PSR-4 autoloading standards
- Use meaningful class and method names
- Include proper error handling
- Maintain consistent indentation (4 spaces)
- Ensure all PHP files have proper PHPDoc documentation
- **Fat model / thin controller** — validation logic, data formatting, and cache invalidation belong in the model. Controllers only read input, call model methods, set flash messages, and redirect or return JSON.
- **Model base class** — all models extend `App\Core\Model` and set `protected $table = 'table_name'`. Never redeclare `getLastInsertId()` or `trimInput()` in a concrete model — inherit them from the base.
- **Model traits** — when a model exceeds ~400 lines, split concerns into PHP traits under `app/Models/Traits/`. Traits access `$this->connection` and `$this->table` directly. Group by responsibility (auth queries, password lifecycle, statistics).
- **CSRF on destructive non-AJAX routes** — logout and any other destructive action that is not an AJAX endpoint must use a `POST` route with a CSRF token, never `GET`. Call `$this->csrfCheck()` at the top of the controller method. See `AuthController::logout()` and `views/layouts/header.php` as reference.
- **CSRF token rotation** — call `regenerateCSRFToken()` unconditionally (before the success/failure branch) on sensitive endpoints so the token rotates even when the model write fails.
- **Remember-me invalidation** — call `$userModel->clearRememberToken($userId)` after any successful password change, regardless of who triggered it (self-service, admin edit, invitation acceptance, or forgot-password reset). `User::updatePassword()` and `UserPasswordTrait::resetPassword()` do this automatically — never bypass either with a raw SQL UPDATE on the `password` column.
- **File uploads** — always route through `ImageService`. MIME type must be validated server-side via `finfo` on `tmp_name`; never trust `$_FILES['type']` (client-controlled). Extension must be whitelisted. `public/uploads/users/.htaccess` blocks PHP execution and must not be removed.
- **JS string interpolation** — use `json_encode()` when passing PHP values into `<script>` blocks. `addslashes()` does not escape `</script>` and is unsafe for JS context.

### JavaScript Code Standards

- Use ES6+ features where appropriate
- Follow consistent naming conventions (camelCase)
- Include JSDoc documentation for functions
- Maintain proper code organization in module files

### Database Standards

- Use prepared statements for all database queries
- Follow consistent naming conventions for tables and columns
- Include proper foreign key relationships
- Document schema changes

## Documentation Standards

### PHPDoc Requirements

All PHP classes, methods, and files must include PHPDoc documentation:

```php
/**
 * Brief description of the class/method/file
 *
 * Detailed description if necessary
 *
 * @package PhpMvcAdminStarter
 * @subpackage [Module Name] (e.g., Controllers\Users, Models, Services)
 * @author Jandres25
 * @version 1.0
 *
 * @param type $parameter Description of parameter (for methods)
 * @return type Description of return value (for methods)
 * @throws ExceptionType Description of when exception is thrown
 */
```

### JSDoc Requirements

JavaScript functions and modules should include JSDoc documentation:

```javascript
/**
 * filename.js - Brief description
 *
 * Detailed description of the module's purpose
 *
 * @package PhpMvcAdminStarter
 * @subpackage JavaScript\[Module]
 * @author Jandres25
 * @version 1.0
 */

/**
 * Brief description of function
 * @param {type} parameter - Description of parameter
 * @returns {type} Description of return value
 */
function myFunction(parameter) {
  // Implementation
}
```

## Commit Guidelines

We follow [Conventional Commits](https://conventionalcommits.org/) for commit messages:

### Format

```
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]
```

### Types

- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation only changes
- `style`: Code style changes (formatting, semicolons, etc)
- `refactor`: Code refactoring without feature changes
- `perf`: Performance improvements
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

### Examples

```bash
feat: add user profile image upload functionality
fix: resolve session timeout issue in admin panel
docs: update installation instructions in README
refactor: reorganize user controller methods
```

## Testing

Run the test suite before submitting a PR:

```bash
vendor/bin/phpunit --testsuite=Unit         # always required
vendor/bin/phpunit --testsuite=Integration  # required when touching models or Auth
```

**Unit tests** live in `tests/Unit/` and require no DB. **Integration tests** live in `tests/Integration/` and need a local test DB — see the _Testing_ section in `README.md` for one-time setup.

When adding a new feature or fixing a bug:

- Add or update tests in the matching suite.
- Unit tests for logic in `app/Core/` or `app/Services/`.
- Integration tests for anything that touches a model or `Auth` cross-model flows.
- Do not write tests for controllers — those are covered by manual browser testing.

## Pull Request Process

1. **Update Documentation**: Ensure all new code is properly documented
2. **Update CHANGELOG**: Add your changes to the `[Unreleased]` section
3. **Run tests**: `vendor/bin/phpunit` must pass locally before opening the PR
4. **Code Review**: Request review from maintainers
5. **Address Feedback**: Make necessary changes based on review comments

When your changes affect session/permissions flow, AJAX endpoint patterns, local seed data, role/permission model, or AI/MCP tooling, update the corresponding docs under `docs/` (`ACCESS_CONTROL.md`, `AJAX_AND_MODULES.md`, `SEEDING.md`, `TESTING.md`, `AI_SETUP.md`) in the same PR.

When adding a new standalone auth page (one that does not use `layouts/footer.php`), manually include: `dark-mode.css`, `login-dark.css`, the anti-FOUC inline IIFE in `<head>`, the `#theme-toggle` button with class `auth-theme-toggle`, and `theme-toggle.js` at the bottom of `<body>` — see `views/auth/login.php` as reference. Existing standalone auth pages: `login.php`, `forgot_password.php`, `reset_password.php`, `accept_invitation.php`.

When adding a controller action that mutates state, call `AuditLogger::log()` after the model write succeeds. Never log inside model methods — logging belongs in the controller layer.

### Pull Request Template

```markdown
## Description

Brief description of what this PR does.

## Type of Change

- [ ] Bug fix
- [ ] New feature
- [ ] Documentation update
- [ ] Code refactoring

## Testing

- [ ] `vendor/bin/phpunit --testsuite=Unit` passes
- [ ] `vendor/bin/phpunit --testsuite=Integration` passes (if models/Auth touched)
- [ ] New tests added for the changed logic
- [ ] Documentation updated

## Checklist

- [ ] PHPDoc/JSDoc documentation added
- [ ] CHANGELOG.md updated
- [ ] Code follows project standards
- [ ] No breaking changes (or properly documented)
```

## Version Management

### Semantic Versioning

We follow [Semantic Versioning](https://semver.org/):

- **MAJOR** version for incompatible API changes
- **MINOR** version for backwards-compatible functionality additions
- **PATCH** version for backwards-compatible bug fixes

### Release Process

1. Update version numbers in relevant files
2. Update CHANGELOG.md with release notes
3. Create a git tag: `git tag -a 3.1.0 -m "Release 3.1.0"`
4. Push tags: `git push origin --tags`
5. Create GitHub release with release notes

## Project Structure Guidelines

When adding new features, follow the existing project structure:

```
├── public/               # Front controller (index.php) + static assets
│   ├── js/
│   │   ├── core/         # Core JavaScript utilities
│   │   └── modules/      # Feature-specific JS
│   └── css/
│       ├── core/         # Core styles
│       └── modules/      # Feature-specific CSS
├── app/
│   ├── Core/             # Controller.php, Model.php, Router.php, Auth.php, AssetRegistry.php, ErrorHandler.php, helpers.php
│   ├── Controllers/      # Feature controllers (flat — no subdirectories)
│   ├── Middleware/       # AuthMiddleware, GuestMiddleware, PermissionMiddleware
│   ├── Models/           # App\Models; Traits/ holds UserAuthTrait, UserPasswordTrait, UserStatsTrait
│   ├── Services/         # App\Services (ImageService, MailService, DashboardCache, LoginThrottleService, AuditLogger)
│   └── Config/           # Bootstrap: config.php, Connection.php (PDO singleton), phpdotenv init
├── routes/               # web.php — all route definitions
├── views/                # PHP templates
│   ├── layouts/          # Layout components (header, sidebar, footer, messages)
│   ├── users/            # User views
│   ├── permissions/      # Permission views
│   ├── auth/             # Login, forgot password, reset password (standalone — include dark mode assets manually)
│   ├── roles/            # Role views
│   ├── audit-log/        # Audit log views (read-only)
│   └── errors/           # 403, 404 error pages
├── database/             # schema.sql and seeder.sql
├── vendor/               # Composer dependencies (not committed — run composer install)
├── docs/                 # Project documentation for developers and AI
└── .claude/              # AI assistant configurations and custom skills
```

## Questions or Issues?

If you have questions about contributing, please:

1. Check existing [Issues](https://github.com/Jandres25/php-mvc-admin-starter/issues)
2. Create a new issue for bugs or feature requests
3. Start a [Discussion](https://github.com/Jandres25/php-mvc-admin-starter/discussions) for questions

## Code of Conduct

This project adheres to a code of conduct. By participating, you are expected to uphold this code:

- Be respectful and inclusive
- Focus on constructive feedback
- Help others learn and grow
- Maintain professional communication

Thank you for contributing to PHP MVC Admin Starter! 🚀
