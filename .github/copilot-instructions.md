# REDAXO CMS Development Instructions

REDAXO is a PHP-based Content Management System (CMS) that has been actively developed since 2004. It features a modular addon architecture, extensive console interface, and comprehensive development toolchain. Always reference these instructions first and fallback to search or bash commands only when you encounter unexpected information that does not match the info here.

## Working Effectively

### Prerequisites and Bootstrap
- PHP 8.1+ required (tested with 8.3.6)
- MySQL 8.0+ or MariaDB 10.1+ required  
- Composer for dependency management
- Docker optional but recommended for isolated development

### Essential Setup Commands
Run these commands in sequence for a fresh REDAXO development environment:

1. **Install development dependencies**:
   ```bash
   composer install --no-interaction --ignore-platform-reqs
   ```
   - NEVER CANCEL: Can take 10-15 minutes on slow networks. Set timeout to 30+ minutes.
   - Use `--ignore-platform-reqs` flag to bypass PHP version conflicts with static analysis tools

2. **Database setup** (if using local MySQL):
   ```bash
   # Set root password and create database
   mysql -u debian-sys-maint -p[password] -e "CREATE DATABASE IF NOT EXISTS redaxo5; CREATE USER IF NOT EXISTS 'root'@'127.0.0.1' IDENTIFIED BY 'root'; GRANT ALL PRIVILEGES ON redaxo5.* TO 'root'@'127.0.0.1'; FLUSH PRIVILEGES;"
   ```

3. **REDAXO installation** (takes ~0.6 seconds):
   ```bash
   php redaxo/bin/console setup:run -n --lang=de_de --db-host=127.0.0.1 --db-name=redaxo5 --db-password=root --db-createdb=no --db-setup=normal --admin-username=admin --admin-password=adminpassword --error-email=test@redaxo.invalid --ansi -v
   ```

4. **Configure debug mode**:
   ```bash
   php redaxo/bin/console config:set --type boolean debug.enabled true -v
   php redaxo/bin/console config:set --type boolean debug.throw_always_exception true -v
   ```

5. **Verify installation**:
   ```bash
   php redaxo/bin/console system:report -v
   ```

### Alternative: Docker Development Environment
For isolated development without local PHP/MySQL setup:

```bash
docker compose up -d
```
- NEVER CANCEL: Initial image pull takes 15+ minutes. Set timeout to 30+ minutes.
- Uses `friendsofredaxo/redaxo:5` image with MySQL 8
- Web interface available on http://localhost:80 (or custom port via `REDAXO_PORT=8080`)
- Database credentials: user=redaxo, password=redaxo, database=redaxo

## Console Interface (Primary Development Tool)

REDAXO provides extensive console commands for all major operations:

### Core Commands (Always Available)
```bash
php redaxo/bin/console list                    # List all commands
php redaxo/bin/console cache:clear            # Clear REDAXO cache
php redaxo/bin/console config:get             # Get configuration values  
php redaxo/bin/console config:set             # Set configuration values
php redaxo/bin/console system:report          # Show system information
```

### Post-Setup Commands (Available After Installation)
```bash
# Package Management
php redaxo/bin/console package:list           # List installed packages/addons
php redaxo/bin/console package:activate       # Activate addon
php redaxo/bin/console package:install        # Install addon

# User Management  
php redaxo/bin/console user:list             # List users
php redaxo/bin/console user:create           # Create user
php redaxo/bin/console user:set-password     # Reset password

# Database Operations
php redaxo/bin/console db:dump-schema        # Export database schema
php redaxo/bin/console db:connection-options # Show MySQL connection details

# Asset Management
php redaxo/bin/console assets:sync           # Sync core assets
php redaxo/bin/console be_style:compile     # Compile backend styles
```

## Code Quality and Testing

### Static Analysis
REDAXO uses comprehensive static analysis with strict configuration:

```bash
# PHPStan analysis - NEVER CANCEL: Takes 3-5 minutes
composer phpstan                              # Run analysis 
composer phpstan:no-cache                     # Clear cache and run
composer phpstan:baseline                     # Update baseline (for maintainers)

# Psalm analysis - NEVER CANCEL: Takes 2-3 minutes  
composer psalm                                # Run psalm
composer psalm:no-cache                       # Clear cache and run
composer taint                                # Run taint analysis - takes 5+ minutes
```

### Code Style
```bash
# PHP-CS-Fixer - NEVER CANCEL: Takes 2-3 minutes
composer cs-fixer                             # Fix code style
composer cs-fixer:no-cache                    # Fix without cache

# Rector - NEVER CANCEL: Takes 3-5 minutes
composer rector                               # Apply automated refactoring
composer rector:no-cache                      # Rector without cache

# Combined code style
composer cs                                   # Run both rector and php-cs-fixer
```

### Unit Testing  
```bash
# PHPUnit tests - NEVER CANCEL: Takes 5-10 minutes
composer phpunit                              # Run all tests
```

### Complete Quality Check
```bash
# Run everything - NEVER CANCEL: Takes 15-20 minutes total
composer check                               # Runs cs + sa + phpunit + taint
```

## Validation Scenarios

### Essential Validation After Changes
Always run these validation steps before committing:

1. **Console functionality test**:
   ```bash
   php redaxo/bin/console list
   php redaxo/bin/console system:report -v
   ```

2. **Code quality validation**:
   ```bash
   composer cs-fixer                         # Auto-fix style issues
   composer phpstan                          # Static analysis check
   ```

3. **Database connectivity test**:
   ```bash
   php redaxo/bin/console config:get setup  # Should return 'false' after setup
   ```

### Manual Testing Scenarios
- **Admin login**: Use username=admin, password=adminpassword to access `/redaxo/` admin interface
- **Console verification**: Ensure all expected commands appear in `php redaxo/bin/console list`
- **Asset compilation**: Run `php redaxo/bin/console be_style:compile` to verify SCSS compilation works

## Repository Structure and Key Locations

### Core Structure
```
/redaxo/                     # Core REDAXO system
  /bin/console              # Main console application
  /src/core/                # Core system classes and functions
  /src/addons/              # System addons (backup, mediapool, etc.)
  /data/                    # Runtime data and configuration
  /cache/                   # System cache files

/.tools/                    # Development tools and configuration  
  /bin/                     # Custom development scripts
  /phpstan/                 # PHPStan baselines and config
  /psalm/                   # Psalm baselines and config

/assets/                    # Public web assets
/media/                     # Media files storage
```

### Key System Addons
Located in `/redaxo/src/addons/`:
- **backup**: Database and file backup functionality
- **be_style**: Backend styling and SCSS compilation  
- **install**: Package installation from redaxo.org
- **mediapool**: Media file management
- **structure**: Page structure and content management
- **users**: User and permission management

### Configuration Files
- `composer.json`: Dependencies and development scripts
- `phpstan.dist.neon`: Static analysis configuration
- `psalm.xml`: Psalm static analysis configuration  
- `.php-cs-fixer.dist.php`: Code style configuration
- `phpunit.dist.xml`: PHPUnit testing configuration
- `rector.php`: Automated refactoring rules

## Development Workflow Tips

### Before Making Changes
1. Always run `composer install --no-interaction --ignore-platform-reqs` first
2. Ensure REDAXO is properly set up with database connection
3. Run `php redaxo/bin/console system:report` to verify system health

### During Development  
1. Use console commands rather than web interface for most operations
2. Clear cache with `php redaxo/bin/console cache:clear` after core changes
3. Run `composer cs-fixer` frequently to maintain code style

### Before Committing
1. **ALWAYS** run `composer cs-fixer` - the CI will fail without proper formatting
2. **ALWAYS** run `composer phpstan` to catch static analysis issues  
3. Test affected console commands to ensure functionality
4. For addon development, test activation/deactivation cycle

### CI Pipeline Compatibility
The GitHub Actions CI runs tests on:
- PHP versions: 8.1, 8.2, 8.3, 8.4 
- Database: MySQL 5.6-8.4, MariaDB 10.1-latest
- Timeout expectations: 30 minutes for most jobs
- Requires passing: code style, static analysis, unit tests

## Common Tasks Reference

### Quick Command Reference
```bash
# Setup and installation
composer install --no-interaction --ignore-platform-reqs
php redaxo/bin/console setup:run [options]

# Daily development
php redaxo/bin/console cache:clear
composer cs-fixer
composer phpstan  

# Package management
php redaxo/bin/console package:list
php redaxo/bin/console install:list

# Addon development
.tools/bin/clone-addon [github-url]    # Clone addon for development
.tools/bin/refresh                     # Refresh installation after changes

# Release preparation  
composer check                        # Full quality verification
```

### Troubleshooting Common Issues
- **"Setup not completed" errors**: Run the setup:run command first
- **Permission errors**: Check file permissions on redaxo/data/ and redaxo/cache/
- **Memory errors**: Increase PHP memory_limit for composer and static analysis tools
- **Network timeouts**: Use `--ignore-platform-reqs` flag and longer timeouts