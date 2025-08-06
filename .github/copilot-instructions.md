# GitHub Copilot Instructions for REDAXO

REDAXO is a flexible Content Management System (CMS) developed in PHP since 2004. This document provides context for GitHub Copilot to help with code suggestions that align with REDAXO's architecture, coding standards, and conventions.

## Project Overview

REDAXO is a mature PHP-based CMS that emphasizes flexibility and extensibility through its addon-based architecture. The system provides a solid core foundation with modular addons that extend functionality.

### Key Characteristics
- **Language**: PHP 8.1+ with strict typing and modern PHP features
- **Architecture**: Modular addon-based system with a core foundation
- **Database**: MySQL/MariaDB with custom ORM layer
- **Frontend**: Custom backend interface, flexible frontend templating
- **Extensibility**: Addon system for plugins and extensions

## Directory Structure

```
redaxo/
├── src/
│   ├── core/                    # Core system files
│   │   ├── lib/                 # Core classes and libraries
│   │   ├── boot.php            # System bootstrap
│   │   ├── pages/              # Backend pages
│   │   ├── fragments/          # Reusable UI components
│   │   └── lang/               # Language files
│   └── addons/                 # System and user addons
│       ├── backup/             # Backup functionality
│       ├── structure/          # Content structure management
│       ├── mediapool/          # Media management
│       ├── users/              # User management
│       └── [addon_name]/       # Individual addon directories
├── data/                       # Runtime data, cache, logs
├── media/                      # User uploaded media files
└── assets/                     # CSS, JS, and other assets
```

## Coding Standards and Conventions

### PHP Standards
- **PHP Version**: Minimum PHP 8.1, use modern PHP features
- **PSR Standards**: Follow PSR-1, PSR-2, PSR-4 for autoloading
- **Type Declarations**: Use strict typing, declare parameter and return types
- **Documentation**: PHPDoc blocks for all classes, methods, and properties
- **Error Handling**: Use exceptions, implement proper error handling

### Code Style
- **Formatting**: Use PHP CS Fixer with REDAXO's configuration (`.php-cs-fixer.dist.php`)
- **Naming Conventions**:
  - Classes: `PascalCase` (e.g., `rex_article`, `rex_media_manager`)
  - Methods: `camelCase` with descriptive names
  - Constants: `UPPER_SNAKE_CASE`
  - Variables: `$camelCase` or `$snake_case` for legacy compatibility
- **Indentation**: 4 spaces, no tabs
- **Line Length**: Prefer 120 characters maximum

### REDAXO-Specific Patterns
- **Class Prefixes**: Core classes use `rex_` prefix (e.g., `rex_sql`, `rex_config`)
- **Addon Classes**: Use addon name as prefix (e.g., `rex_media_manager_type`)
- **Static Methods**: Extensive use of static methods for utilities and core functionality
- **Configuration**: Use `rex_config` for persistent configuration storage
- **Database**: Use `rex_sql` class for database operations, not raw PDO

## Architecture Patterns

### Core System
- **Bootstrap Process**: System initialization through `boot.php`
- **Request Handling**: Separate frontend/backend request processing
- **Extension Points**: Hook system for extending core functionality
- **Package System**: Addons and plugins as installable packages

### Database Layer
- **rex_sql**: Primary database abstraction layer
- **Active Record Pattern**: Models extend base classes with database integration
- **Schema Management**: SQL schema files for addon installation/updates
- **Migration System**: Update scripts for version management

### Templating and Output
- **Fragments**: Reusable template components in `fragments/` directory
- **Backend Pages**: Modular backend interface through page system
- **Frontend**: Flexible output system, no enforced templating engine

## Development Workflow

### Quality Assurance Tools
1. **PHP CS Fixer**: Code style formatting (`composer cs-fixer`)
2. **PHPStan**: Static analysis level 6 (`composer phpstan`)
3. **Psalm**: Additional static analysis with taint checking (`composer psalm`)
4. **PHPUnit**: Unit testing (`composer phpunit`)
5. **Rector**: Code modernization and refactoring (`composer rector`)

### Commands
- `composer check`: Run all quality checks (style, analysis, tests)
- `composer cs`: Run code style fixers (rector + php-cs-fixer)
- `composer sa`: Run static analysis (phpstan + psalm)

### Testing
- **Unit Tests**: PHPUnit tests in `tests/` directories within addons
- **Coverage**: Aim for good test coverage of core functionality
- **Mocking**: Use PHPUnit mocking for dependencies

## Common Patterns

### Configuration Management
```php
// Set configuration
rex::setConfig('key', 'value');
rex_config::set('addon_name', 'key', 'value');

// Get configuration
$value = rex::getConfig('key', 'default');
$value = rex_config::get('addon_name', 'key', 'default');
```

### Database Operations
```php
// Query database
$sql = rex_sql::factory();
$sql->setQuery('SELECT * FROM rex_article WHERE id = ?', [$id]);
$articles = $sql->getArray();

// Insert/Update
$sql = rex_sql::factory();
$sql->setTable('rex_article');
$sql->setValue('name', $name);
$sql->insert(); // or $sql->update()
```

### Extension Points
```php
// Register extension
rex_extension::register('ARTICLE_UPDATED', function(rex_extension_point $ep) {
    $article = $ep->getSubject();
    // Handle the event
});
```

### Permission Checks
```php
// Check user permissions
if (rex::getUser()->hasPerm('structure[]')) {
    // User has structure permissions
}
```

### Language/Translation
```php
// Get translated text
$text = rex_i18n::msg('addon_name.message_key');
$text = rex_i18n::msg('addon_name.message_with_params', $param1, $param2);
```

## Addon Development

### Addon Structure
```
addon_name/
├── package.yml              # Addon metadata and dependencies
├── install.php             # Installation script
├── uninstall.php          # Uninstallation script
├── update.php             # Update scripts
├── lib/                   # PHP classes
├── pages/                 # Backend pages
├── fragments/             # UI fragments
├── lang/                  # Language files
├── assets/                # CSS, JS files
└── tests/                 # Unit tests
```

### package.yml Example
```yaml
package: addon_name
version: '1.0.0'
author: Author Name
supportpage: https://example.com

requires:
    redaxo: '^5.15.0'
    php:
        version: '^8.1'
```

## Security Considerations

- **Input Validation**: Always validate and sanitize user input
- **SQL Injection**: Use parameterized queries with `rex_sql`
- **XSS Prevention**: Escape output with `rex_escape::html()`
- **CSRF Protection**: Use `rex_csrf_token` for forms
- **File Uploads**: Validate file types and sanitize filenames
- **Permissions**: Check user permissions before sensitive operations

## Performance Guidelines

- **Caching**: Use REDAXO's caching system for expensive operations
- **Database**: Optimize queries, use indexes appropriately
- **Assets**: Minimize and compress CSS/JS files
- **Memory**: Be mindful of memory usage in loops and large datasets

## Debugging and Logging

- **Debug Addon**: Use the debug addon for development debugging
- **Error Handling**: Implement proper exception handling
- **Logging**: Use appropriate logging levels and structured logging
- **Whoops**: Error page handler for development environment

## Documentation Standards

- **PHPDoc**: Complete documentation for all public methods and classes
- **README Files**: Each addon should have clear README documentation
- **Changelog**: Maintain changelog following semantic versioning
- **Code Comments**: Explain complex business logic and algorithms

When suggesting code, prioritize:
1. **Type Safety**: Use proper type declarations and static analysis compliance
2. **REDAXO Patterns**: Follow established conventions and use core classes
3. **Security**: Implement proper validation and security measures
4. **Performance**: Consider caching and optimization opportunities
5. **Maintainability**: Write clean, well-documented, testable code