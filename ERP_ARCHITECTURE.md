# MESIGO ERP Enterprise Edition - Architecture Document

## Version: 1.0
## Last Updated: 2026-07-07
## Project: MESIGO ERP Enterprise Edition

---

## 1. EXECUTIVE SUMMARY

MESIGO ERP is a comprehensive Enterprise Resource Planning system designed specifically for agricultural export businesses. The architecture follows modern PHP development practices with a focus on security, scalability, and maintainability.

---

## 2. SYSTEM OVERVIEW

### 2.1 Business Context
MESIGO INDIA PRIVATE LIMITED operates in the agricultural export sector, dealing with:
- Multiple product categories (spices, grains, fruits, vegetables)
- International buyers across different countries
- Complex export documentation requirements
- Multi-currency transactions
- Quality certification and compliance
- Seasonal business cycles

### 2.2 Technical Stack
- **Backend**: PHP 8.3 (Core PHP, no framework)
- **Database**: MySQL 8.0
- **Frontend**: Bootstrap 5, jQuery, AJAX
- **Server**: Apache/Nginx with PHP-FPM
- **Authentication**: Session-based with RBAC
- **API**: RESTful JSON API

---

## 3. ARCHITECTURAL PATTERNS

### 3.1 MVC (Model-View-Controller) Pattern
```
Request → Router → Controller → Model → Database
                           ↓
                    Service Layer
                           ↓
                    View Template
                           ↓
Response ← ← ← ← ← ← ← ← ← ← ← ← ← ← ← ← ← ←
```

### 3.2 Layered Architecture
```
┌─────────────────────────────────────────┐
│           PRESENTATION LAYER            │
│  (Views, Bootstrap 5, jQuery, AJAX)    │
├─────────────────────────────────────────┤
│           APPLICATION LAYER             │
│  (Controllers, Middleware, Services)     │
├─────────────────────────────────────────┤
│           BUSINESS LAYER                │
│  (Models, Validation, Business Logic)    │
├─────────────────────────────────────────┤
│           DATA ACCESS LAYER             │
│  (PDO, Query Builder, Repositories)      │
├─────────────────────────────────────────┤
│           INFRASTRUCTURE              │
│  (Database, File System, External APIs)  │
└─────────────────────────────────────────┘
```

### 3.3 Service-Oriented Design
Each module has a dedicated service class that encapsulates business logic:
- **Single Responsibility Principle** - One service per module
- **Dependency Injection** - Services receive dependencies via constructor
- **Interface Contracts** - Services implement defined interfaces
- **Event-Driven Communication** - Modules communicate via events

---

## 4. DIRECTORY STRUCTURE

### 4.1 Application Structure
```
/mesigo_erp/
├── app/
│   ├── Core/
│   │   ├── Router.php           # URL routing
│   │   ├── Database.php         # PDO connection manager
│   │   ├── Session.php          # Session management
│   │   ├── Auth.php             # Authentication handler
│   │   ├── Validator.php        # Input validation
│   │   ├── Logger.php           # Logging service
│   │   └── EventDispatcher.php  # Event system
│   │
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── BuyerCrmController.php
│   │   ├── SupplierCrmController.php
│   │   ├── ProductController.php
│   │   ├── InquiryController.php
│   │   ├── QuotationController.php
│   │   ├── InvoiceController.php
│   │   ├── ShippingController.php
│   │   ├── CertificateController.php
│   │   ├── InsuranceController.php
│   │   ├── PaymentController.php
│   │   ├── CostingController.php
│   │   ├── ReportController.php
│   │   ├── DocumentController.php
│   │   ├── EmailController.php
│   │   ├── WhatsAppController.php
│   │   ├── AuditController.php
│   │   └── SettingsController.php
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── Role.php
│   │   ├── Buyer.php
│   │   ├── Supplier.php
│   │   ├── Product.php
│   │   ├── Inquiry.php
│   │   ├── Quotation.php
│   │   ├── Invoice.php
│   │   ├── Shipping.php
│   │   ├── Certificate.php
│   │   ├── Insurance.php
│   │   ├── Payment.php
│   │   ├── Costing.php
│   │   ├── Document.php
│   │   ├── Email.php
│   │   ├── WhatsApp.php
│   │   ├── AuditLog.php
│   │   └── Setting.php
│   │
│   ├── Views/
│   │   ├── layouts/
│   │   │   ├── header.php
│   │   │   ├── footer.php
│   │   │   ├── sidebar.php
│   │   │   └── navbar.php
│   │   ├── auth/
│   │   ├── dashboard/
│   │   ├── buyer_crm/
│   │   ├── supplier_crm/
│   │   ├── products/
│   │   ├── inquiry/
│   │   ├── quotation/
│   │   ├── invoice/
│   │   ├── shipping/
│   │   ├── certificate/
│   │   ├── insurance/
│   │   ├── payment/
│   │   ├── costing/
│   │   ├── reports/
│   │   ├── documents/
│   │   ├── email/
│   │   ├── whatsapp/
│   │   ├── audit/
│   │   └── settings/
│   │
│   ├── Services/
│   │   ├── BuyerService.php
│   │   ├── SupplierService.php
│   │   ├── ProductService.php
│   │   ├── InquiryService.php
│   │   ├── QuotationService.php
│   │   ├── InvoiceService.php
│   │   ├── ShippingService.php
│   │   ├── CertificateService.php
│   │   ├── InsuranceService.php
│   │   ├── PaymentService.php
│   │   ├── CostingService.php
│   │   ├── ReportService.php
│   │   ├── DocumentService.php
│   │   ├── EmailService.php
│   │   ├── WhatsAppService.php
│   │   └── AuditService.php
│   │
│   ├── Middleware/
│   │   ├── AuthMiddleware.php
│   │   ├── CsrfMiddleware.php
│   │   ├── PermissionMiddleware.php
│   │   └── LogMiddleware.php
│   │
│   ├── Helpers/
│   │   ├── DateHelper.php
│   │   ├── NumberHelper.php
│   │   ├── CurrencyHelper.php
│   │   ├── ExportHelper.php
│   │   └── ValidationHelper.php
│   │
│   └── Interfaces/
│       ├── ServiceInterface.php
│       ├── RepositoryInterface.php
│       └── EventInterface.php
│
├── public/
│   ├── assets/
│   │   ├── css/
│   │   │   ├── bootstrap.min.css
│   │   │   ├── custom.css
│   │   │   └── print.css
│   │   ├── js/
│   │   │   ├── jquery.min.js
│   │   │   ├── bootstrap.bundle.min.js
│   │   │   ├── custom.js
│   │   │   └── ajax-handler.js
│   │   └── img/
│   │       ├── logo.png
│   │       ├── favicon.ico
│   │       └── icons/
│   ├── uploads/
│   │   ├── documents/
│   │   ├── certificates/
│   │   ├── invoices/
│   │   └── temp/
│   └── index.php
│
├── config/
│   ├── app.php
│   ├── database.php
│   ├── auth.php
│   ├── mail.php
│   └── whatsapp.php
│
├── logs/
│   ├── auth.log
│   ├── error.log
│   ├── audit.log
│   └── business.log
│
├── vendor/
└── docs/
```

---

## 5. DATABASE ARCHITECTURE

### 5.1 Database Design Principles
- **Normalized to 3NF** for data integrity
- **Soft deletes** for all master tables
- **Audit trails** for all transactions
- **Indexing strategy** for performance
- **Partitioning** for large tables

### 5.2 Core Tables
```
users
├── id (PK)
├── role_id (FK)
├── username
├── email
├── password
├── first_name, last_name
├── phone
├── status
├── created_at, updated_at, deleted_at

roles
├── id (PK)
├── name
├── permissions (JSON)
├── created_at, updated_at

buyers
├── id (PK)
├── buyer_code (unique)
├── company_name
├── contact_person
├── email, phone
├── address (JSON)
├── country, state, city
├── gst_number
├── status
├── created_at, updated_at, deleted_at

suppliers
├── id (PK)
├── supplier_code (unique)
├── company_name
├── contact_person
├── email, phone
├── address (JSON)
├── country, state, city
├── gst_number
├── status
├── created_at, updated_at, deleted_at

products
├── id (PK)
├── product_code (unique)
├── name
├── category
├── hsn_code
├── unit_of_measure
├── description
├── status
├── created_at, updated_at, deleted_at

inquiries
├── id (PK)
├── inquiry_number (unique)
├── buyer_id (FK)
├── product_id (FK)
├── quantity
├── unit_price
├── currency
├── status
├── created_at, updated_at

quotations
├── id (PK)
├── quotation_number (unique)
├── inquiry_id (FK)
├── buyer_id (FK)
├── valid_until
├── terms_and_conditions
├── status
├── created_at, updated_at

invoices
├── id (PK)
├── invoice_number (unique)
├── buyer_id (FK)
├── quotation_id (FK)
├── invoice_date
├── due_date
├── subtotal
├── tax_amount
├── total_amount
├── currency
├── status
├── created_at, updated_at

shipping
├── id (PK)
├── invoice_id (FK)
├── shipping_bill_number
├── bill_of_lading_number
├── vessel_name
├── port_of_loading
├── port_of_discharge
├── etd, eta
├── status
├── created_at, updated_at

certificates
├── id (PK)
├── invoice_id (FK)
├── certificate_type
├── certificate_number
├── issue_date
├── expiry_date
├── file_path
├── status
├── created_at, updated_at

audit_logs
├── id (PK)
├── user_id (FK)
├── action
├── table_name
├── record_id
├── old_values (JSON)
├── new_values (JSON)
├── ip_address
├── user_agent
├── created_at
```

---

## 6. MODULE INTERACTIONS

### 6.1 Core Module Flow
```
Authentication → Authorization → Dashboard
     ↓              ↓              ↓
   Users           Roles           Main Menu
     ↓              ↓              ↓
                     ↘           ↙
                       All Modules
```

### 6.2 Business Process Flow
```
Buyer CRM → Inquiry → Quotation → Proforma Invoice
     ↓         ↓         ↓              ↓
                           ↓
                    Order Costing → Commercial Invoice
                           ↓
                    Packing List → Shipping Bill
                           ↓
                    Bill of Lading → Certificate of Origin
                           ↓
                    Phytosanitary → Insurance
                           ↓
                    Payment Receipt
```

### 6.3 Document Generation Flow
```
Invoice Data → PDF Generation → Document Vault
     ↓              ↓              ↓
                     ↘           ↙
                   Email/WhatsApp
```

---

## 7. SECURITY ARCHITECTURE

### 7.1 Authentication Flow
```
1. User enters credentials
2. CSRF token validation
3. Rate limit check
4. Password verification (bcrypt)
5. Session creation
6. Permission loading
7. Audit log entry
8. Redirect to dashboard
```

### 7.2 Authorization Flow
```
1. Request received
2. Session validation
3. Permission check
4. Role-based access
5. Module access check
6. Action permission check
7. Allow/Deny response
```

### 7.3 Data Protection Layers
```
┌─────────────────────────────────────┐
│  Application Level Encryption         │
│  (Sensitive fields)                 │
├─────────────────────────────────────┤
│  Database Level Security              │
│  (User permissions, views)          │
├─────────────────────────────────────┤
│  Server Level Security                │
│  (Firewall, SSL, backups)           │
└─────────────────────────────────────┘
```

---

## 8. API ARCHITECTURE

### 8.1 API Endpoints Structure
```
/api/v1/
├── auth/
│   ├── POST /login
│   ├── POST /logout
│   ├── POST /refresh
│   └── POST /reset-password
│
├── buyers/
│   ├── GET /buyers
│   ├── GET /buyers/{id}
│   ├── POST /buyers
│   ├── PUT /buyers/{id}
│   └── DELETE /buyers/{id}
│
├── suppliers/
│   ├── GET /suppliers
│   ├── GET /suppliers/{id}
│   ├── POST /suppliers
│   ├── PUT /suppliers/{id}
│   └── DELETE /suppliers/{id}
│
├── products/
│   ├── GET /products
│   ├── GET /products/{id}
│   ├── POST /products
│   ├── PUT /products/{id}
│   └── DELETE /products/{id}
│
├── inquiry/
│   ├── GET /inquiry
│   ├── GET /inquiry/{id}
│   ├── POST /inquiry
│   ├── PUT /inquiry/{id}
│   └── DELETE /inquiry/{id}
│
├── quotation/
│   ├── GET /quotation
│   ├── GET /quotation/{id}
│   ├── POST /quotation
│   ├── PUT /quotation/{id}
│   └── DELETE /quotation/{id}
│
├── invoices/
│   ├── GET /invoices
│   ├── GET /invoices/{id}
│   ├── POST /invoices
│   ├── PUT /invoices/{id}
│   └── DELETE /invoices/{id}
│
├── shipping/
│   ├── GET /shipping
│   ├── GET /shipping/{id}
│   ├── POST /shipping
│   ├── PUT /shipping/{id}
│   └── DELETE /shipping/{id}
│
├── certificates/
│   ├── GET /certificates
│   ├── GET /certificates/{id}
│   ├── POST /certificates
│   ├── PUT /certificates/{id}
│   └── DELETE /certificates/{id}
│
├── payments/
│   ├── GET /payments
│   ├── GET /payments/{id}
│   ├── POST /payments
│   ├── PUT /payments/{id}
│   └── DELETE /payments/{id}
│
├── reports/
│   ├── GET /reports/sales
│   ├── GET /reports/export
│   ├── GET /reports/buyers
│   └── GET /reports/products
│
├── documents/
│   ├── GET /documents
│   ├── GET /documents/{id}
│   ├── POST /documents
│   └── DELETE /documents/{id}
│
├── email/
│   ├── POST /send
│   └── GET /templates
│
├── whatsapp/
│   ├── POST /send
│   └── GET /templates
│
└── audit/
    ├── GET /audit
    └── GET /audit/{id}
```

### 8.2 API Response Standards
```json
{
    "status": "success",
    "message": "Operation completed successfully",
    "data": {
        "id": 1,
        "name": "Record Name"
    },
    "meta": {
        "pagination": {
            "current_page": 1,
            "per_page": 15,
            "total": 100
        }
    },
    "errors": []
}
```

---

## 9. EVENT SYSTEM

### 9.1 Event Types
- **User Events**: login, logout, password_change
- **Data Events**: create, update, delete, restore
- **Business Events**: inquiry_created, quotation_sent, invoice_generated
- **System Events**: error_occurred, backup_completed

### 9.2 Event Listeners
- **Email Notifications**: Send emails on business events
- **WhatsApp Notifications**: Send WhatsApp messages
- **Audit Logging**: Log all data changes
- **Cache Invalidation**: Clear cache on data updates

---

## 10. CACHING STRATEGY

### 10.1 Cache Layers
- **Application Cache**: Frequently accessed configuration
- **Database Cache**: Query result caching
- **Session Cache**: User session data
- **View Cache**: Compiled templates

### 10.2 Cache Keys
- `config:*` - Configuration settings
- `user:{id}:*` - User-specific data
- `module:{name}:list` - Module list data
- `report:{type}:{date}` - Report data

---

## 11. ERROR HANDLING

### 11.1 Error Types
- **Validation Errors**: User input issues
- **Authentication Errors**: Login/permission issues
- **Database Errors**: Query/connection issues
- **System Errors**: Server/application issues

### 11.2 Error Response
```json
{
    "status": "error",
    "message": "User-friendly error message",
    "errors": [
        {
            "field": "email",
            "message": "Invalid email format"
        }
    ],
    "code": "VALIDATION_ERROR"
}
```

---

## 12. DEPLOYMENT ARCHITECTURE

### 12.1 Server Requirements
- **PHP**: 8.3+
- **MySQL**: 8.0+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Memory**: 4GB minimum
- **Storage**: SSD with daily backups

### 12.2 Environment Configuration
```
Development → Testing → Staging → Production
     ↓         ↓         ↓         ↓
  Debug On   Debug Off   Debug Off   Debug Off
  Local DB   Test DB     Mirror DB   Live DB
```

---

## 13. MONITORING AND LOGGING

### 13.1 Log Types
- **Application Logs**: Business operations
- **Error Logs**: System errors
- **Security Logs**: Authentication/authorization
- **Audit Logs**: Data changes
- **Performance Logs**: Query performance

### 13.2 Monitoring Points
- **Database connections**
- **API response times**
- **Error rates**
- **User activity**
- **File storage**

---

## 14. SCALABILITY CONSIDERATIONS

### 14.1 Horizontal Scaling
- **Load balancer** for multiple servers
- **Database replication** for read scaling
- **Redis cache** for session sharing
- **CDN** for static assets

### 14.2 Vertical Scaling
- **Database optimization**
- **Query optimization**
- **Index optimization**
- **Memory optimization**

---

## 15. BACKUP AND RECOVERY

### 15.1 Backup Strategy
- **Daily**: Full database backup
- **Hourly**: Transaction log backup
- **Weekly**: Full system backup
- **Monthly**: Archive backup

### 15.2 Recovery Plan
- **RTO**: 4 hours
- **RPO**: 1 hour
- **Disaster Recovery**: 24 hours
- **Data Retention**: 7 years

---

## 16. COMPLIANCE REQUIREMENTS

### 16.1 Export Compliance
- **GST invoicing** standards
- **Export documentation** (Shipping Bill, B/L)
- **Quality certificates** (Phytosanitary, COO)
- **Bank documentation** (LUT, ARE)

### 16.2 Data Compliance
- **GDPR** for international data
- **Indian IT Act** compliance
- **Financial audit** trail
- **Document retention** policies

---

*This architecture document defines the technical foundation for MESIGO ERP. All development must align with these architectural principles.*