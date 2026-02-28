# Contributing to MailCamp

Thank you for your interest in contributing to MailCamp! This document provides guidelines and instructions for contributing.

## Code of Conduct

Please be respectful and constructive in all interactions. We are committed to providing a welcoming and inclusive experience for everyone.

## Getting Started

1. **Fork the repository** and clone it locally
2. **Install dependencies**: `composer install`
3. **Copy environment file**: `cp .env.example .env`
4. **Configure your database** in `.env`
5. **Run migrations**: `php bin/larafony migrate`
6. **Seed demo data**: `php bin/larafony app:seed`

## Development Workflow

### Branch Naming

- `feature/` – New features (e.g., `feature/recipient-import`)
- `fix/` – Bug fixes (e.g., `fix/template-duplicate-query`)
- `docs/` – Documentation changes
- `refactor/` – Code refactoring without behavior changes
- `test/` – Adding or updating tests

### Making Changes

1. Create a new branch from `main`
2. Make your changes following the coding standards below
3. Write or update tests for your changes
4. Run the test suite: `composer test`
5. Run the linter: `composer lint`
6. Commit your changes with clear, descriptive messages
7. Push your branch and open a pull request

### Commit Messages

Use clear, descriptive commit messages:

```
feat: add recipient CSV import endpoint
fix: remove duplicate template query in edit action
docs: update API documentation for campaigns
test: add unit tests for Organization model
refactor: extract auth check into middleware
```

## Coding Standards

### PHP Style

- Use `declare(strict_types=1)` in all PHP files
- Follow PSR-12 coding standard
- Use PHP 8.5+ features (property hooks, attributes, asymmetric visibility)
- Use type declarations for all parameters and return types

### Architecture

- **Controllers** use attribute-based routing (`#[Route(...)]`)
- **Models** extend `Larafony\Framework\Database\ORM\Model`
- **DTOs** extend `Larafony\Framework\Validation\FormRequest` with validation attributes
- **Multi-tenancy**: Always filter queries by `organization_id`
- **Authentication**: Check `Auth::check()` in all protected routes

### Security

- Never commit credentials or secrets
- Use parameterized queries (the ORM handles this)
- Escape output in views with `htmlspecialchars()`
- Validate all user input through DTOs

## Pull Request Process

### Before Submitting

- [ ] Code follows the project coding standards
- [ ] Tests pass locally (`composer test`)
- [ ] Linting passes (`composer lint`)
- [ ] New features include appropriate tests
- [ ] Documentation is updated if needed
- [ ] No debug statements (`var_dump`, `dd()`, `print_r`) left in code

### PR Review Checklist

Reviewers will check for:

- **Correctness**: Does the code do what it claims?
- **Security**: Are there any vulnerabilities introduced?
- **Multi-tenancy**: Are queries properly scoped to the organization?
- **Tests**: Are changes adequately tested?
- **Performance**: Are there any N+1 queries or unnecessary database calls?
- **Code style**: Does the code follow project conventions?

### Review Process

1. Submit your pull request against the `main` branch
2. Ensure all CI checks pass (linting, tests, security scan)
3. Request a review from a maintainer
4. Address review feedback
5. Once approved, a maintainer will merge your PR

## Running Tests

```bash
# Run all tests
composer test

# Run with coverage report
composer test-coverage

# Lint PHP files
composer lint
```

## Project Structure

```
src/
├── Controllers/    # Route handlers with #[Route] attributes
├── Models/         # ORM models with property hooks
├── DTOs/           # Form request DTOs with validation
├── Middleware/      # PSR-15 middleware
├── Console/        # CLI commands
└── View/           # View components

tests/
└── Unit/
    ├── Models/     # Model unit tests
    └── DTOs/       # DTO unit tests
```

## Reporting Issues

When reporting bugs, please include:

- Steps to reproduce the issue
- Expected behavior
- Actual behavior
- PHP version and environment details
- Relevant error messages or logs

## Questions?

If you have questions about contributing, please open an issue for discussion.
