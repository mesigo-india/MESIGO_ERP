# AI Development Rules for MESIGO ERP

## Version: 1.0
## Last Updated: 2026-07-07
## Project: MESIGO ERP Enterprise Edition

---

## 1. CORE PRINCIPLES

### 1.0 AI Workflow Rules
- **Never overwrite existing files without approval** from the user or project owner
- **Never change project architecture unless explicitly instructed** - the current MESIGO ERP structure is the official architecture
- **Read related files before editing** to understand existing patterns, dependencies, and business rules
- **Keep edits minimal and focused** on the approved task scope
- **Report every modified file** after completing any change
- **Never delete files without approval**
- **Ask before making breaking changes** including schema changes, route changes, authentication changes, public API changes, or architecture changes

### 1.1 Security First
- **All input must be validated and sanitized** before processing
- **All output must be escaped** to prevent XSS attacks
- **SQL injection prevention** is mandatory - use PDO prepared statements exclusively
- **CSRF protection** must be implemented on all forms
- **Session management** must follow security best practices
- **Password hashing** must use PHP's `password_hash()` with PASSWORD_DEFAULT
- **File uploads** must be validated for type, size, and content
- **API endpoints** must require authentication and authorization

### 1.2 Code Quality Standards
- **No deprecated functions** - PHP 8.3 features only
- **Type declarations** required for all function parameters and return types
- **Strict typing** enabled (`declare(strict_types=1)`)
- **Error handling** must be comprehensive and user-friendly
- **Logging** must be implemented for all critical operations
- **Code comments** must explain business logic, not just what the code does

### 1.3 Performance Requirements
- **Database queries** must be optimized with proper indexing
- **Caching** must be implemented for frequently accessed data
- **Pagination** required for all list views with more than 50 records
- **AJAX requests** must have loading indicators and proper error handling
- **Assets** must be minified and combined for production

---

## 2. FILE AND DIRECTORY STRUCTURE

### 2.1 Root Directory Structure
```
/mesigo_erp/
├── classes/                # Core classes, domain models, engines, and shared utilities
├── modules/                # Feature modules and module-specific screens/actions
├── middleware/             # Authentication and authorization middleware
├── templates/              # View templates and page content
├── includes/               # Shared layout/includes such as header, sidebar, navbar, footer, loader
├── config/                 # Configuration files and routes
├── database/               # Schema, migrations, and seed data
├── helpers/                # Helper functions
├── assets/                 # CSS, JS, fonts, icons, and images
├── uploads/                # User uploaded files
├── logs/                   # Application logs
├── ajax/                   # AJAX handlers where applicable
├── api/                    # API files/endpoints where applicable
├── index.php               # Application entry point
└── vendor/                # Composer dependencies, if installed
```

The structure above is the official MESIGO ERP architecture. Do **not** migrate the project to an `app/`, `Controllers/`, `Services/`, or `public/` architecture unless explicitly instructed and approved.

### 2.2 File Naming Conventions
- **Core/domain classes**: `ClassName.php` inside `classes/` (e.g., `Buyer.php`, `Database.php`)
- **Modules**: module folders use lowercase names inside `modules/` (e.g., `users/`, `dashboard/`)
- **Templates**: view files use `snake_case.php` where applicable inside `templates/`
- **Helpers**: `helper_name.php` (e.g., `date_helper.php`)
- **Middleware**: `ActionMiddleware.php` (e.g., `AuthMiddleware.php`)
- **Includes**: shared include files use lowercase names (e.g., `header.php`, `sidebar.php`)

### 2.3 Class Naming Conventions
- **Core/domain classes**: `ClassName` (e.g., `Buyer`, `Database`, `DocumentHeader`)
- **Middleware**: `ActionMiddleware` (e.g., `AuthMiddleware`)
- **Base classes**: descriptive names matching their role (e.g., `Controller`, `MasterDataModel`)

---

## 3. DATABASE RULES

### 3.1 Connection Management
- Use **PDO exclusively** for all database operations
- **Connection pooling** must be implemented
- **Transactions** required for multi-step operations
- **Error mode** must be set to `PDO::ERRMODE_EXCEPTION`

### 3.2 Query Standards
- All queries must use **prepared statements**
- **No raw SQL** in route handlers or module actions - use core/domain class methods
- **Soft deletes** for all master data tables
- **Audit trails** for all critical operations
- **Timestamps** (`created_at`, `updated_at`, `deleted_at`) on all tables

### 3.3 Table Naming
- **Tables**: `module_name` (plural, snake_case)
- **Primary keys**: `id` (auto-increment)
- **Foreign keys**: `{table_name}_id` (e.g., `buyer_id`)
- **Pivot tables**: `{table1}_{table2}` (alphabetical order)

---

## 4. SECURITY IMPLEMENTATION

### 4.1 Authentication Rules
- **Session-based authentication** with secure session handling
- **Password requirements**:
  - Minimum 12 characters
  - Must include uppercase, lowercase, numbers, and special characters
  - Bcrypt hashing with cost factor 12
- **Login attempts** must be rate-limited (5 attempts per 15 minutes)
- **Password reset** must use secure tokens with expiration

### 4.2 Authorization Rules
- **Role-Based Access Control (RBAC)** mandatory
- **Permission checks** before every protected route or module action
- **Menu visibility** based on user permissions
- **API access** must validate permissions for each endpoint

### 4.3 Data Protection
- **Sensitive data** must be encrypted at rest
- **PII data** must have additional protection
- **Export operations** must be logged
- **Data retention** policies must be implemented

---

## 5. AJAX AND API STANDARDS

### 5.1 AJAX Response Format
```json
{
    "status": "success|error",
    "message": "Human-readable message",
    "data": {},
    "errors": []
}
```

### 5.2 API Endpoint Standards
- **RESTful design** for all API endpoints
- **Versioning** in URL: `/api/v1/resource`
- **Rate limiting** for all API endpoints
- **Request validation** before processing
- **Response caching** where appropriate

### 5.3 Error Handling
- **User-friendly error messages** - never expose system errors
- **Log all errors** with full context
- **HTTP status codes** must be appropriate
- **Validation errors** must return field-specific messages

---

## 6. LOGGING AND AUDIT

### 6.1 Log Categories
- **Authentication logs**: Login attempts, password changes
- **Authorization logs**: Permission denials, access attempts
- **Data modification logs**: Create, update, delete operations
- **System logs**: Errors, warnings, critical events
- **Business logs**: Invoice generation, order processing

### 6.2 Log Format
```
[YYYY-MM-DD HH:MM:SS] [LEVEL] [USER_ID] [IP_ADDRESS] [MESSAGE]
```

### 6.3 Audit Trail Requirements
- **Who** performed the action
- **What** was changed
- **When** it was changed
- **From where** (IP address)
- **Why** (if applicable - user reason)

---

## 7. MODULE DEVELOPMENT RULES

### 7.1 Module Structure
Each module must include:
- **Module folder** under `modules/` where applicable
- **Related class/model** under `classes/` where database or domain logic is required
- **Templates** for user interactions where applicable
- **Routes** in the routing configuration
- **Permissions** in the system
- **Audit trail** integration

### 7.2 Module Dependencies
- **Explicit dependency declaration** required
- **Use existing core classes and middleware patterns**
- **Do not introduce a new architecture layer** unless explicitly approved
- **Keep module communication simple and consistent with current project structure**

### 7.3 Testing Requirements
- **Unit tests** for all critical core/domain/module methods
- **Integration tests** for all API endpoints
- **UI tests** for critical user flows
- **Security tests** for all input points

---

## 8. DEPLOYMENT AND MAINTENANCE

### 8.1 Version Control
- **Git** for version control
- **Feature branches** for all new development
- **Pull requests** required for code review
- **Semantic versioning** (MAJOR.MINOR.PATCH)

### 8.2 Environment Configuration
- **Environment variables** for sensitive configuration
- **Separate config files** for each environment
- **No hardcoded credentials** in code
- **Configuration validation** on application start

### 8.3 Backup and Recovery
- **Daily database backups** required
- **File backup** for uploads and documents
- **Disaster recovery plan** documented
- **Rollback procedures** for each deployment

---

## 9. COMPLIANCE AND STANDARDS

### 9.1 Agricultural Export Compliance
- **GST compliance** for all financial documents
- **Export documentation** standards (DGFT, customs)
- **Quality certificates** integration
- **Traceability** for all products

### 9.2 Data Privacy
- **GDPR compliance** for international data
- **Indian data protection** laws followed
- **Data encryption** for sensitive information
- **User consent** for data processing

### 9.3 Industry Standards
- **ISO 22000** food safety management
- **HACCP** compliance for food products
- **Export quality standards** adherence
- **Documentation standards** for audit purposes

---

## 10. FUTURE CONSIDERATIONS

### 10.1 Scalability
- **Horizontal scaling** support
- **Database sharding** strategy
- **Microservices** architecture preparation
- **API gateway** for external integrations

### 10.2 Integration Points
- **Email service** integration
- **WhatsApp business API**
- **Banking API** for payments
- **Shipping carrier APIs**
- **Government portals** (GST, customs)

### 10.3 Technology Evolution
- **PHP version upgrades** path
- **Database migration** strategies
- **Frontend framework** upgrade path
- **Mobile app** development preparation

---

## 11. VIOLATION CONSEQUENCES

Any violation of these rules will result in:
1. **Code rejection** during review
2. **Security audit** of the entire module
3. **Mandatory refactoring** before merge
4. **Documentation** of the violation
5. **Training requirement** for the developer

---

## 12. APPROVAL AND REVIEW

### 12.1 Code Review Checklist
- [ ] Security rules followed
- [ ] Database rules followed
- [ ] Naming conventions followed
- [ ] Error handling implemented
- [ ] Logging implemented
- [ ] Tests written
- [ ] Documentation updated

### 12.2 Approval Authority
- **Lead Architect**: Final approval for all modules
- **Security Officer**: Security review approval
- **Business Analyst**: Business logic approval
- **QA Lead**: Testing approval

---

*This document is the foundation for all development in MESIGO ERP. Every line of code must comply with these rules.*