# MESIGO ERP - Complete Implementation Roadmap

## Source of Truth

This roadmap is based on the current project state documented in `docs/PROJECT_AUDIT.md`.

Rules applied:

- Existing modules/classes/tables are marked only when they were observed in `PROJECT_AUDIT.md`.
- Missing modules are marked as missing when no implemented module/controller/template/API files were observed.
- This roadmap does not require changing the approved project architecture.
- Current official structure remains: `classes/`, `modules/`, `middleware/`, `templates/`, `includes/`, `config/`, `database/`, `helpers/`, `assets/`, `ajax/`, `api/`, `uploads/`, and `logs/`.

---

# 1. Project Vision

MESIGO ERP should become a complete agricultural export ERP for managing company setup, users, master data, CRM, sales, purchase, inventory, export documentation, shipping, finance, reports, automation, and AI-assisted operations.

The current project is a partially completed foundation and document-engine prototype. The roadmap goal is to convert the current foundation into a working ERP by:

1. Stabilizing the existing foundation.
2. Completing missing controllers, modules, templates, and API endpoints.
3. Building master data before transactional flows.
4. Building CRM before sales and purchase flows.
5. Building sales, purchase, inventory, export documentation, shipping, and finance in dependency order.
6. Adding reporting, automation, and AI assistant features only after core ERP workflows are functional.

Current audit-based ERP completion estimate from `PROJECT_AUDIT.md`: **30%**.

---

# 2. ERP Development Phases

## Phase 1 - Foundation

### Objective

Stabilize the existing ERP foundation so all routes, core classes, sessions, authentication, validation, logging, middleware, configuration, and layout systems can support real modules.

### Modules

- Core bootstrap and loader
- Router
- Base controller
- Authentication foundation
- Authorization/RBAC foundation
- Session and CSRF foundation
- Response handling
- Validation foundation
- Logging foundation
- Configuration foundation
- Layout/includes foundation
- Error pages
- Migration/seed foundation
- Test/syntax verification foundation

### Dependencies

- Existing `classes/Database.php`
- Existing `classes/Session.php`
- Existing `classes/Auth.php`
- Existing `classes/Response.php`
- Existing `classes/Validator.php`
- Existing `classes/Logger.php`
- Existing `classes/Router.php`
- Existing `classes/Controller.php`
- Existing `middleware/AuthMiddleware.php`
- Existing `middleware/PermissionMiddleware.php`
- Existing `config/*.php`
- Existing `includes/*.php`
- Existing `database/schema/schema.sql`

### Priority

Critical

### Estimated Complexity

High, because the audit found blocking runtime issues:

- `classes/Branch.php` parse error.
- Routes reference missing controllers.
- `vendor/autoload.php` is required but Composer files were not observed.
- Middleware exists but is not integrated into routes.
- CSRF support exists but enforcement was not observed.

### Completion Criteria

- All PHP files pass syntax check.
- `classes/Branch.php` is valid or excluded until implemented.
- Entry point runs without missing `vendor/autoload.php` failure.
- Existing routes resolve to real executable controllers/module handlers.
- Authentication login/logout works end-to-end.
- User session is regenerated on login.
- CSRF validation is enforced on state-changing web requests.
- Middleware is integrated or explicitly invoked in protected flows.
- Logging writes successfully to `logs/`.
- Basic module layout renders using `includes/header.php`, `sidebar.php`, `navbar.php`, and `footer.php`.

---

## Phase 2 - Master Data

### Objective

Implement complete master data management for all existing master tables so CRM, sales, purchase, inventory, documents, logistics, and finance can reuse consistent reference data.

### Modules

- Company
- Branches
- Countries, states, cities
- Currencies
- Financial years
- Number series
- Buyers master foundation
- Suppliers master foundation
- Products
- Product categories
- Product varieties
- Units
- Packing types
- Incoterms
- Payment terms
- Ports
- Shipping lines
- Banks
- Containers
- HS codes
- Product grades
- Product origins
- Freight forwarders
- Inspection agencies

### Dependencies

- Phase 1 foundation
- Existing `MasterDataModel.php`
- Existing `Buyer.php`
- Existing `HSCode.php`
- Existing `ProductGrade.php`
- Existing but broken `Branch.php`
- Existing schema and migration tables
- Auth/RBAC for protected CRUD
- Audit logging foundation

### Priority

Critical

### Estimated Complexity

High

### Completion Criteria

- Every existing master table has a working class/model or reusable master-data implementation.
- Each master module has list, create, edit, view, delete/soft-delete screens.
- Search and pagination work for each list.
- Validation rules exist for each master module.
- Permission checks protect all master-data actions.
- Audit logging records create/update/delete operations.
- Master data can be used by downstream sales/document/shipping/finance modules.

---

## Phase 3 - CRM

### Objective

Build buyer and supplier CRM workflows on top of master data, including contacts, addresses, documents, communication history, and activity tracking.

### Modules

- Buyer CRM
- Supplier CRM
- Buyer contacts
- Supplier contacts
- Buyer/supplier addresses
- Buyer/supplier document storage
- Buyer/supplier communication history
- CRM activity timeline

### Dependencies

- Phase 1 foundation
- Phase 2 master data
- Existing `buyers` table and `Buyer.php`
- Existing `suppliers` table
- Upload validation/storage controls
- Audit logging
- User/RBAC module

### Priority

High

### Estimated Complexity

Medium to High

### Completion Criteria

- Buyer CRM is usable from UI.
- Supplier CRM is usable from UI.
- Buyers and suppliers can be searched, filtered, created, edited, viewed, and soft-deleted.
- Contact, address, and document attachment workflows are implemented where required.
- CRM records can be selected from sales, purchase, and document modules.

---

## Phase 4 - Sales

### Objective

Implement sales transaction flow from inquiry through quotation, proforma invoice, commercial invoice, and sales order tracking.

### Modules

- Inquiry
- Quotation
- Proforma invoice
- Commercial invoice
- Sales order dashboard
- Sales terms and charges
- Sales item pricing
- Sales document conversion

### Dependencies

- Phase 1 foundation
- Phase 2 master data
- Phase 3 CRM
- Existing document engine classes:
  - `DocumentType.php`
  - `DocumentHeader.php`
  - `DocumentItem.php`
  - `NumberGenerator.php`
  - `DocumentStatusEngine.php`
  - `DocumentConversionEngine.php`
- Existing document tables
- Products, buyers, currencies, incoterms, payment terms, ports

### Priority

High

### Estimated Complexity

High

### Completion Criteria

- Inquiry, quotation, proforma invoice, and commercial invoice screens exist.
- Documents can be created with multiple line items.
- Number series generates document numbers.
- Document status workflow works.
- Document conversion works in approved sequence.
- Sales documents can be viewed, printed/exported when document generation is added.

---

## Phase 5 - Purchase

### Objective

Implement purchase and procurement workflows connected to supplier CRM, products, inventory, and costing.

### Modules

- Purchase inquiry/request
- Supplier quotation
- Purchase order
- Goods receipt reference
- Purchase costing
- Supplier document attachments

### Dependencies

- Phase 1 foundation
- Phase 2 master data
- Phase 3 CRM supplier data
- Products
- Units and packing types
- Inventory module foundation for goods receipt integration
- Finance module for payable tracking

### Priority

Medium

### Estimated Complexity

High

### Completion Criteria

- Purchase request/order flow exists.
- Supplier and product references work.
- Purchase item quantities and rates are tracked.
- Purchase records can feed inventory receipts and finance/payables.

---

## Phase 6 - Inventory

### Objective

Track product stock, packing, containers, warehouse/branch movement, and availability for sales/export operations.

### Modules

- Stock ledger
- Stock inward
- Stock outward
- Stock adjustment
- Batch/lot tracking
- Warehouse/branch stock
- Container allocation
- Product availability

### Dependencies

- Phase 1 foundation
- Phase 2 master data
- Product master
- Branches
- Units
- Packing types
- Containers
- Purchase flow for inward stock
- Sales/export flow for outward stock

### Priority

Medium

### Estimated Complexity

High

### Completion Criteria

- Product stock can be increased, reduced, adjusted, and audited.
- Stock ledger reflects all inventory movements.
- Stock can be linked to sales/export documents.
- Container allocation can be used in shipping/export workflows.

---

## Phase 7 - Export Documentation

### Objective

Complete the export document lifecycle using the existing document engine classes and tables.

### Modules

- Commercial invoice
- Packing list
- Shipping bill
- Bill of lading
- Certificate of origin
- Phytosanitary certificate
- Insurance certificate
- Inspection certificate
- Payment receipt document
- Document attachments
- Document revisions
- Document status timeline
- Document print/PDF templates

### Dependencies

- Phase 1 foundation
- Phase 2 master data
- Phase 3 CRM
- Phase 4 sales
- Phase 6 inventory for packing/container data
- Existing document engine classes and tables
- Number series
- Document type seed data

### Priority

High

### Estimated Complexity

Very High

### Completion Criteria

- All document types listed in `ESP001A_ARCHITECTURE.md` are usable from UI.
- Document conversion flow works from inquiry to payment receipt where applicable.
- Document items, charges, terms, attachments, revisions, and status history are available.
- Export documents can be generated in compliant formats.

---

## Phase 8 - Shipping & Logistics

### Objective

Manage shipment planning, freight forwarders, inspection agencies, ports, shipping lines, containers, and export logistics milestones.

### Modules

- Shipment planning
- Freight forwarders
- Inspection agencies
- Shipping lines
- Ports
- Containers
- Vessel/shipping details
- Logistics milestone tracking
- Shipping document coordination

### Dependencies

- Phase 2 master data
- Phase 4 sales
- Phase 6 inventory
- Phase 7 export documentation
- Existing tables: `ports`, `shipping_lines`, `containers`, `freight_forwarders`, `inspection_agencies`

### Priority

Medium to High

### Estimated Complexity

High

### Completion Criteria

- Shipments can be linked to export documents.
- Ports, shipping lines, containers, forwarders, and inspection agencies are selectable from master data.
- Shipment milestones can be tracked.
- Shipping-related documents can use logistics data.

---

## Phase 9 - Finance

### Objective

Implement financial tracking for invoices, payments, banks, costing, GST/tax values, receivables, payables, and payment receipts.

### Modules

- Payment receipts
- Receivables
- Payables
- Bank master usage
- Order costing
- Charges/taxes
- Currency conversion
- Financial year handling
- GST/export finance reports foundation

### Dependencies

- Phase 1 foundation
- Phase 2 master data
- Phase 4 sales
- Phase 5 purchase
- Phase 7 export documentation
- Existing tables: `banks`, `currencies`, `financial_years`, `document_charges`, `document_headers`, `document_items`

### Priority

High

### Estimated Complexity

High

### Completion Criteria

- Payment receipts can be recorded and linked to invoices/documents.
- Receivable status can be tracked.
- Costing can include freight, commission, charges, and taxes.
- Currency and exchange rate values are correctly applied.
- Financial reports have reliable source data.

---

## Phase 10 - Reports & Analytics

### Objective

Provide operational, sales, purchase, inventory, finance, export, audit, and management reports.

### Modules

- Dashboard analytics
- Sales reports
- Buyer reports
- Supplier reports
- Product reports
- Inventory reports
- Export documentation reports
- Shipment reports
- Finance reports
- GST/tax reports
- Audit reports
- Custom filters/export

### Dependencies

- Completed transactional modules
- Audit logging
- Stable database relationships
- Pagination and filtering
- Export/PDF/Excel capability when implemented

### Priority

Medium

### Estimated Complexity

Medium to High

### Completion Criteria

- Reports use real transactional data.
- Reports include date/status/entity filters.
- Critical reports can be exported or printed.
- Dashboard widgets reflect live system data.

---

## Phase 11 - Automation

### Objective

Automate repetitive ERP tasks such as notifications, document conversions, status updates, reminders, email/WhatsApp communication, and scheduled jobs.

### Modules

- Email integration
- WhatsApp integration
- Notification engine
- Reminder engine
- Scheduled jobs
- Auto document numbering
- Auto status transitions where approved
- Email/WhatsApp templates
- Queue/log tracking

### Dependencies

- Phase 1 foundation
- Phase 4 sales
- Phase 7 export documentation
- Phase 9 finance
- Phase 10 reports
- Existing `.env` mail/WhatsApp settings
- Logging foundation

### Priority

Medium

### Estimated Complexity

High

### Completion Criteria

- Email and WhatsApp configuration works.
- Templates exist for key business events.
- Notifications are logged.
- Scheduled reminders can be run safely.
- Automation does not bypass permissions or audit requirements.

---

## Phase 12 - AI Assistant

### Objective

Add an AI assistant layer after ERP workflows are stable, focused on safe guidance, document drafting, data lookup, workflow suggestions, and report explanation.

### Modules

- AI help assistant
- AI document drafting assistant
- AI report explainer
- AI workflow guidance
- AI search over ERP data
- AI rule/compliance checker
- AI audit summarizer

### Dependencies

- Stable ERP modules from Phases 1-11
- Strong access control
- Audit logging
- Data privacy rules
- Approved AI workflow rules in `AI_RULES.md`
- API/service boundary for AI operations

### Priority

Low until core ERP is operational

### Estimated Complexity

Very High

### Completion Criteria

- AI assistant only accesses data allowed by user permissions.
- AI actions are logged.
- AI cannot modify records without explicit user approval.
- AI follows `AI_RULES.md`.
- AI outputs are reviewable before business use.

---

# Complete Module List

Status definitions:

- **Completed**: End-to-end module implementation observed.
- **Partial**: Some classes/tables/folders exist, but full module implementation is incomplete.
- **Missing**: No implemented module/controller/template/API files observed, even if planned in documents.

No module is marked Completed because `PROJECT_AUDIT.md` did not identify any end-to-end completed module.

| # | Module Name | Status | Dependencies | Estimated Files | Estimated Database Tables | Estimated Development Order |
|---:|---|---|---|---:|---:|---:|
| 1 | Foundation/Core Bootstrap | Partial | None | 8-12 | 0 | 1 |
| 2 | Configuration | Partial | Foundation | 4-6 | 0 | 2 |
| 3 | Database Connection | Partial | Configuration | 1-2 | 0 | 3 |
| 4 | Migration/Seed System | Missing | Database Connection | 4-8 | 1 | 4 |
| 5 | Routing | Partial | Foundation | 2-4 | 0 | 5 |
| 6 | Base Controller/Layout Rendering | Partial | Routing, Includes | 4-8 | 0 | 6 |
| 7 | Error Handling | Partial | Foundation, Logger | 4-6 | 0 | 7 |
| 8 | Logging | Partial | Foundation | 2-4 | 2 observed audit/login tables | 8 |
| 9 | Session Management | Partial | Configuration | 1-2 | 0 | 9 |
| 10 | Authentication | Partial | Session, Users, Roles | 8-12 | `users`, `roles`, `login_logs` | 10 |
| 11 | Authorization/RBAC | Partial | Authentication | 6-10 | `permissions`, `role_permissions`, `user_roles` | 11 |
| 12 | User Management | Missing | Authentication, RBAC | 8-14 | `users`, `user_roles` | 12 |
| 13 | Role & Permission Management | Missing | Authentication, RBAC | 8-14 | `roles`, `permissions`, `role_permissions` | 13 |
| 14 | Dashboard | Missing | Authentication, Layout | 5-10 | 0-2 | 14 |
| 15 | Company Setup | Missing | Authentication, Master Data | 5-10 | `company` | 15 |
| 16 | Settings | Missing | Authentication, RBAC | 5-10 | `settings` | 16 |
| 17 | Master Data Base Engine | Partial | Foundation, Database | 2-4 | 0 | 17 |
| 18 | Branch Master | Partial | Master Data Base, Company | 5-8 | `branches` | 18 |
| 19 | Country/State/City Master | Missing | Master Data Base | 8-12 | `countries`, `states`, `cities` | 19 |
| 20 | Currency Master | Missing | Master Data Base | 4-7 | `currencies` | 20 |
| 21 | Financial Year Master | Missing | Master Data Base | 4-7 | `financial_years` | 21 |
| 22 | Number Series Master | Partial | Master Data Base | 4-7 | `number_series` | 22 |
| 23 | Buyer Master | Partial | Master Data Base, Geography | 8-12 | `buyers` | 23 |
| 24 | Supplier Master | Missing | Master Data Base, Geography | 8-12 | `suppliers` | 24 |
| 25 | Product Category Master | Missing | Master Data Base | 4-7 | `product_categories` | 25 |
| 26 | Product Variety Master | Missing | Product Category | 4-7 | `product_varieties` | 26 |
| 27 | Unit Master | Missing | Master Data Base | 4-7 | `units` | 27 |
| 28 | Packing Type Master | Missing | Master Data Base | 4-7 | `packing_types` | 28 |
| 29 | HS Code Master | Partial | Master Data Base | 4-7 | `hs_codes` | 29 |
| 30 | Product Grade Master | Partial | Master Data Base | 4-7 | `product_grades` | 30 |
| 31 | Product Origin Master | Missing | Master Data Base | 4-7 | `product_origins` | 31 |
| 32 | Product Master | Missing | Product Category, Variety, Unit, Packing, HS Code | 8-14 | `products` | 32 |
| 33 | Incoterm Master | Missing | Master Data Base | 4-7 | `incoterms` | 33 |
| 34 | Payment Term Master | Missing | Master Data Base | 4-7 | `payment_terms` | 34 |
| 35 | Port Master | Missing | Geography | 4-7 | `ports` | 35 |
| 36 | Shipping Line Master | Missing | Master Data Base | 4-7 | `shipping_lines` | 36 |
| 37 | Bank Master | Missing | Master Data Base | 4-7 | `banks` | 37 |
| 38 | Container Master | Missing | Master Data Base | 4-7 | `containers` | 38 |
| 39 | Freight Forwarder Master | Missing | Geography | 5-8 | `freight_forwarders` | 39 |
| 40 | Inspection Agency Master | Missing | Geography | 5-8 | `inspection_agencies` | 40 |
| 41 | Buyer CRM | Missing | Buyer Master, Uploads, Audit | 10-18 | `buyers` plus future contact/history tables if added | 41 |
| 42 | Supplier CRM | Missing | Supplier Master, Uploads, Audit | 10-18 | `suppliers` plus future contact/history tables if added | 42 |
| 43 | Document Type Management | Partial | Document Engine, Number Series | 4-8 | `document_types` | 43 |
| 44 | Document Header Engine | Partial | Document Type, Buyer/Supplier, Currency | 4-8 | `document_headers` | 44 |
| 45 | Document Item Engine | Partial | Product Master, Document Header | 4-8 | `document_items` | 45 |
| 46 | Document Terms | Missing | Document Header | 4-7 | `document_terms` | 46 |
| 47 | Document Charges | Missing | Document Header, Finance | 4-7 | `document_charges` | 47 |
| 48 | Document Status Workflow | Partial | Document Header, Users | 4-8 | `document_status_history` | 48 |
| 49 | Document Attachments | Partial | Document Header, Upload Security | 4-8 | `document_attachments` | 49 |
| 50 | Document Revisions | Partial | Document Header, Users | 4-8 | `document_revisions` | 50 |
| 51 | Inquiry | Missing | Buyer CRM, Product Master, Document Engine | 8-14 | document tables | 51 |
| 52 | Quotation | Missing | Inquiry, Document Engine | 8-14 | document tables | 52 |
| 53 | Proforma Invoice | Missing | Quotation, Document Engine | 8-14 | document tables | 53 |
| 54 | Commercial Invoice | Missing | Proforma Invoice, Finance, Document Engine | 8-14 | document tables | 54 |
| 55 | Purchase Request | Missing | Supplier CRM, Product Master | 8-14 | future purchase tables | 55 |
| 56 | Purchase Order | Missing | Purchase Request, Supplier CRM, Product Master | 8-14 | future purchase tables | 56 |
| 57 | Stock Ledger | Missing | Product Master, Branch Master | 8-14 | future inventory tables | 57 |
| 58 | Stock Inward | Missing | Purchase, Stock Ledger | 6-10 | future inventory tables | 58 |
| 59 | Stock Outward | Missing | Sales/Documents, Stock Ledger | 6-10 | future inventory tables | 59 |
| 60 | Packing List | Missing | Commercial Invoice, Inventory, Document Engine | 8-14 | document tables | 60 |
| 61 | Shipping Bill | Missing | Packing List, Shipping Master Data | 8-14 | document tables | 61 |
| 62 | Bill of Lading | Missing | Shipping Bill, Shipping Line, Ports | 8-14 | document tables | 62 |
| 63 | Certificate of Origin | Missing | Shipping Bill, Export Documentation | 8-14 | document tables | 63 |
| 64 | Phytosanitary Certificate | Missing | Shipping Bill, Inspection/Export Data | 8-14 | document tables | 64 |
| 65 | Insurance Certificate | Missing | Shipping Bill, Finance/Logistics | 8-14 | document tables | 65 |
| 66 | Inspection Certificate | Missing | Inspection Agencies, Shipping Bill | 8-14 | document tables | 66 |
| 67 | Shipment Planning | Missing | Export Docs, Ports, Containers, Forwarders | 10-18 | future shipment tables | 67 |
| 68 | Logistics Milestones | Missing | Shipment Planning | 6-12 | future shipment tables | 68 |
| 69 | Payment Receipt | Missing | Commercial Invoice, Banks, Finance | 8-14 | document tables/future payment tables | 69 |
| 70 | Receivables | Missing | Commercial Invoice, Payment Receipt | 8-14 | future finance tables | 70 |
| 71 | Payables | Missing | Purchase Order, Suppliers | 8-14 | future finance tables | 71 |
| 72 | Order Costing | Missing | Sales, Purchase, Shipping, Finance | 8-14 | future costing tables | 72 |
| 73 | Reports | Missing | Completed business modules | 10-20 | mostly existing transactional tables | 73 |
| 74 | Audit Log UI | Missing | Audit Logging | 5-10 | `audit_logs` | 74 |
| 75 | Document Vault UI | Missing | Attachments, Documents, Uploads | 8-14 | `document_attachments`, `document_revisions` | 75 |
| 76 | Email Integration | Missing | Settings, Automation | 8-14 | future email tables/logs | 76 |
| 77 | WhatsApp Integration | Missing | Settings, Automation | 8-14 | future WhatsApp tables/logs | 77 |
| 78 | Notification/Reminder Engine | Missing | Email/WhatsApp, User, Documents | 8-14 | future notification tables | 78 |
| 79 | API Foundation | Partial | Router, Auth, Response | 6-12 | 0 | 79 |
| 80 | Public API Endpoints | Missing | API Foundation, RBAC | 12-30 | existing module tables | 80 |
| 81 | AI Assistant | Missing | Stable ERP, RBAC, Audit, API | 12-30 | future AI logs/settings tables | 81 |

---

# ERP Development Sequence

This is the recommended step-by-step build order from the current audited state to a complete ERP.

## Stabilization Sequence

1. Confirm the approved architecture remains the current structure.
2. Fix the blocking PHP parse error in `classes/Branch.php`.
3. Resolve `vendor/autoload.php` dependency by installing Composer dependencies or adjusting bootstrap according to project policy.
4. Run syntax check across all PHP files.
5. Add or wire the missing controllers referenced by `config/routes.php`.
6. Decide and implement how `modules/` are executed by the router.
7. Integrate authentication middleware and permission middleware with routes or module actions.
8. Enforce CSRF on POST/PUT/DELETE web actions.
9. Add login session regeneration.
10. Add login rate limiting.
11. Add `.env.example` and protect real `.env` usage.
12. Add seed data for roles, permissions, admin user, document types, and number series.

## Foundation Build Sequence

13. Complete user login/logout screens.
14. Complete password reset only after token storage/validation is defined.
15. Complete user management.
16. Complete role and permission management.
17. Complete dashboard shell.
18. Complete settings module.
19. Complete company setup.
20. Add audit logging service and use it in create/update/delete actions.

## Master Data Build Sequence

21. Complete `MasterDataModel` safety improvements, including table/field whitelisting.
22. Complete branch master.
23. Complete geography masters: countries, states, cities.
24. Complete currency and financial year masters.
25. Complete number series UI/management.
26. Complete buyer master.
27. Complete supplier master.
28. Complete product category master.
29. Complete product variety master.
30. Complete units master.
31. Complete packing type master.
32. Complete HS code master.
33. Complete product grade master.
34. Complete product origin master.
35. Complete product master.
36. Complete incoterms master.
37. Complete payment terms master.
38. Complete ports master.
39. Complete shipping lines master.
40. Complete banks master.
41. Complete containers master.
42. Complete freight forwarders master.
43. Complete inspection agencies master.

## CRM Build Sequence

44. Build Buyer CRM screens using buyer master.
45. Build Supplier CRM screens using supplier master.
46. Add CRM contact/address/document sections if database tables are approved.
47. Add CRM communication history if database tables are approved.

## Sales Build Sequence

48. Complete document type seed/configuration.
49. Complete document header UI foundation.
50. Complete document item UI foundation.
51. Complete document terms UI.
52. Complete document charges UI.
53. Build inquiry module.
54. Build quotation module.
55. Build quotation conversion from inquiry.
56. Build proforma invoice module.
57. Build proforma invoice conversion from quotation.
58. Build commercial invoice module.
59. Build commercial invoice conversion from proforma invoice.

## Purchase Build Sequence

60. Define required purchase tables through migration.
61. Build purchase request module.
62. Build supplier quotation module if required.
63. Build purchase order module.
64. Connect purchase order to supplier and product masters.

## Inventory Build Sequence

65. Define required inventory tables through migration.
66. Build stock ledger.
67. Build stock inward.
68. Build stock outward.
69. Build stock adjustment.
70. Add batch/lot tracking if approved.
71. Connect stock to branch/product/container data.

## Export Documentation Build Sequence

72. Build packing list module.
73. Build packing list conversion from commercial invoice.
74. Build shipping bill module.
75. Build shipping bill conversion from packing list.
76. Build bill of lading module.
77. Build certificate of origin module.
78. Build phytosanitary certificate module.
79. Build insurance certificate module.
80. Build inspection certificate module.
81. Complete document attachments UI.
82. Complete document revisions UI.
83. Complete document status timeline UI.
84. Add document print/PDF templates.

## Shipping & Logistics Build Sequence

85. Define shipment tables if required.
86. Build shipment planning module.
87. Build container allocation workflow.
88. Build freight forwarder linkage.
89. Build inspection agency linkage.
90. Build logistics milestone tracking.
91. Connect shipping data to export documents.

## Finance Build Sequence

92. Build payment receipt module.
93. Build receivables tracking.
94. Build payables tracking.
95. Build order costing.
96. Connect finance to banks, currencies, commercial invoices, purchases, and shipments.
97. Add GST/export finance calculations where required.

## Reports & Analytics Build Sequence

98. Build dashboard analytics from real data.
99. Build sales reports.
100. Build CRM reports.
101. Build product reports.
102. Build inventory reports.
103. Build export documentation reports.
104. Build shipping reports.
105. Build finance reports.
106. Build audit reports.
107. Add export/print options.

## Automation Build Sequence

108. Build email configuration and templates.
109. Build WhatsApp configuration and templates.
110. Build notification logs.
111. Build reminders for pending approvals, shipments, and payments.
112. Build queue/scheduled-job mechanism if approved.
113. Add safe automation audit logging.

## API Build Sequence

114. Complete API authentication/authorization strategy.
115. Build API response standards around `Response.php`.
116. Add API endpoints module-by-module after each module stabilizes.
117. Add API validation, rate limiting, and audit logging.

## AI Assistant Build Sequence

118. Define AI permissions and data access rules.
119. Build AI audit/log table if approved.
120. Build read-only AI help/search assistant.
121. Build AI report explanation assistant.
122. Build AI document drafting assistant with human approval.
123. Build AI workflow guidance.
124. Add AI safety review for business-critical outputs.

---

# Roadmap Completion Target

The roadmap should be considered complete when:

- Foundation is stable and syntax-clean.
- All existing database tables have corresponding working modules or documented intentionally internal usage.
- All routes resolve to implemented handlers.
- All protected actions enforce authentication, authorization, CSRF where applicable, validation, and audit logging.
- All ERP phases from master data through finance are operational.
- Reports use real data.
- Automation and AI assistant features are added only after the core ERP is stable.
