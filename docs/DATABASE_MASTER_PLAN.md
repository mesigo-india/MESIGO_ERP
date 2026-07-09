# MESIGO ERP - Database Master Plan

## Source of Truth

This document is based on the existing MESIGO ERP documentation and current project state, especially:

- `AI_RULES.md`
- `docs/PROJECT_AUDIT.md`
- `docs/ERP_ROADMAP.md`
- `DATABASE_RULES.md`
- `ERP_ARCHITECTURE.md`

This is a documentation-only plan. It does not generate SQL and does not modify the existing schema or migration files.

---

# 1. Database Architecture

## Database Name

- Current configured/default database name: `mesigo_erp`
- Source observed:
  - `.env`: `DB_NAME=mesigo_erp`
  - `config/config.php`: default database name `mesigo_erp`
  - `config/database.php`: default database name `mesigo_erp`

## Character Set

- Required character set: `utf8mb4`
- Purpose: full Unicode support, including international buyer/supplier names, addresses, export documentation text, and symbols.

## Collation

- Required collation: `utf8mb4_unicode_ci`
- Purpose: case-insensitive comparisons with broad Unicode compatibility.

## Storage Engine

- Required storage engine: `InnoDB`
- Purpose: transactions, row-level locking, foreign key constraints, and safe multi-step ERP operations.

## Naming Convention

### Tables

- Use plural `snake_case` table names.
- Examples already existing:
  - `users`
  - `roles`
  - `buyers`
  - `document_headers`
  - `document_items`
  - `audit_logs`

### Primary Keys

- Use `id` as the primary key column.
- Use `BIGINT UNSIGNED AUTO_INCREMENT` for primary IDs where possible.

### Foreign Keys

- Use `{referenced_table_singular_or_context}_id` naming.
- Existing examples:
  - `role_id`
  - `buyer_id`
  - `document_type_id`
  - `document_header_id`
  - `country_id`

### Timestamps

- Standard audit timestamp fields:
  - `created_at`
  - `updated_at`
  - `deleted_at` where soft delete applies

### Status Fields

- Use `status` for active/inactive/workflow state where applicable.
- Existing standard values from documentation:
  - `0` = Inactive/Deleted
  - `1` = Active
  - `2` = Draft
  - `3` = Pending
  - `4` = Approved
  - `5` = Rejected
  - `6` = Completed
  - `7` = Cancelled

## Primary Key Strategy

- Every business table should have a single-column primary key named `id`.
- Pivot tables may use composite primary keys where appropriate.
- Existing pivot examples:
  - `role_permissions`: composite `role_id`, `permission_id`
  - `user_roles`: composite `user_id`, `role_id`

## Foreign Key Strategy

- Use foreign keys for all required relationships.
- Use `ON DELETE RESTRICT` where deleting a parent should be blocked.
- Use `ON DELETE SET NULL` where historical records should remain but optional references may be removed.
- Use `ON DELETE CASCADE` only for dependent child records that should not exist without the parent, such as document line details.
- All foreign key columns must be indexed.

## Index Strategy

- Primary keys are required on every table.
- Foreign keys must be indexed.
- Frequently filtered columns should be indexed.
- Common status/date indexes:
  - `status`
  - `created_at`
  - business dates such as `document_date`, `shipment_date`, `payment_date`
- Business unique codes/numbers should have unique indexes.
- Existing examples:
  - `uk_users_email`
  - `uk_buyers_code`
  - `uk_document_headers_number`
  - `idx_document_headers_status`
  - `idx_document_headers_date`

---

# 2. Complete Table List

Status definitions:

- **Existing**: Table exists in current `database/schema/schema.sql` or `database/migrations/002_master_data_tables.sql`.
- **Partial**: A general-purpose existing table covers the concept, but module-specific final structure is incomplete or specialized tables are missing.
- **Missing**: Table is required by the ERP roadmap/module plan but was not observed in existing schema or migrations.

## Authentication, Users, Roles, Permissions

| Table Name | Purpose | Estimated Columns | Relationships | Status |
|---|---|---:|---|---|
| `users` | Stores user accounts, login identity, password hash, role reference, profile fields, and status. | 12-16 | Belongs to `roles` through `role_id`; related to `user_roles`, `login_logs`, `audit_logs`, document creator/updater fields. | Existing |
| `roles` | Stores role definitions and permission JSON. | 4-6 | Related to `users`, `role_permissions`, `user_roles`. | Existing |
| `permissions` | Stores individual permission definitions. | 5-7 | Related to `roles` through `role_permissions`. | Existing |
| `role_permissions` | Pivot table assigning permissions to roles. | 3-4 | Belongs to `roles` and `permissions`. | Existing |
| `user_roles` | Pivot table assigning roles to users. | 3-4 | Belongs to `users` and `roles`. | Existing |
| `login_logs` | Stores successful and failed login attempts. | 5-7 | Belongs optionally to `users`. | Existing |
| `password_resets` | Stores password reset tokens and expiry data. | 6-8 | Belongs to `users`. | Missing |
| `user_sessions` | Optional table for server-side session/device tracking. | 8-12 | Belongs to `users`. | Missing |

## Company and Organization

| Table Name | Purpose | Estimated Columns | Relationships | Status |
|---|---|---:|---|---|
| `company` | Stores company profile, GST/IEC, contact, and address data. | 10-14 | Parent for `branches`. | Existing |
| `branches` | Stores company branches, GST, address, contact, and branch code. | 18-24 | Belongs to `company`; created/updated/deleted by `users`. | Existing |
| `settings` | Stores system-wide configuration values. | 7-10 | May be used globally by all modules. | Existing |
| `financial_years` | Stores financial year definitions and current year marker. | 6-8 | Used by finance, number series, reports. | Existing |

## Geography, Currency, Ports, Logistics Masters

| Table Name | Purpose | Estimated Columns | Relationships | Status |
|---|---|---:|---|---|
| `countries` | Country master for buyers, suppliers, ports, origins, and export data. | 7-9 | Parent of `states`, `ports`, buyer/supplier country references. | Existing |
| `states` | State/province master. | 7-9 | Belongs to `countries`; parent of `cities`. | Existing |
| `cities` | City master. | 6-8 | Belongs to `states`; referenced by buyers/suppliers. | Existing |
| `currencies` | Currency master with code, symbol, exchange rate, default flag. | 8-10 | Referenced by documents and finance. | Existing |
| `ports` | Air/sea/land ports used in export documentation and shipping. | 8-10 | Belongs optionally to `countries`; referenced by document headers and shipments. | Existing |
| `shipping_lines` | Shipping line master. | 7-10 | Referenced by shipments and bill of lading flows. | Existing |
| `containers` | Container master/registry. | 7-9 | Referenced by inventory/shipping workflows. | Existing |
| `freight_forwarders` | Freight forwarder master. | 18-24 | Belongs optionally to `countries`; used by shipping/logistics. | Existing |
| `inspection_agencies` | Inspection/certification agency master. | 18-24 | Belongs optionally to `countries`; used by inspection certificate workflows. | Existing |

## Product and Packaging Masters

| Table Name | Purpose | Estimated Columns | Relationships | Status |
|---|---|---:|---|---|
| `product_categories` | Product category master. | 6-8 | Parent of `product_varieties`; referenced by products. | Existing |
| `product_varieties` | Product variety master under category. | 7-9 | Belongs to `product_categories`; referenced by products and document items. | Existing |
| `units` | Unit of measure master. | 6-8 | Referenced by products and document items. | Existing |
| `packing_types` | Packaging type master. | 6-8 | Referenced by products and document items. | Existing |
| `hs_codes` | HS/HSN code master for export/GST classification. | 12-16 | Used by product and document line items. | Existing |
| `product_grades` | Product grade/quality master. | 12-16 | Used by products or document item quality workflows. | Existing |
| `product_origins` | Product origin master. | 12-16 | Used by product/export origin data. | Existing |
| `products` | Product master with category, variety, HSN, unit, packing, and status. | 16-22 | Belongs to categories, varieties, units, packing types; referenced by document items, inventory, purchase/sales. | Existing |
| `product_packaging` | Detailed product packaging configurations where one product has multiple packaging options. | 8-12 | Belongs to `products`, `packing_types`, `units`. | Missing |

## CRM: Buyers, Suppliers, Contacts

| Table Name | Purpose | Estimated Columns | Relationships | Status |
|---|---|---:|---|---|
| `buyers` | Buyer/customer master. | 18-24 | Belongs optionally to `countries`, `states`, `cities`; referenced by documents, CRM, sales. | Existing |
| `suppliers` | Supplier/vendor master. | 16-22 | Belongs optionally to `countries`, `states`, `cities`; referenced by purchase and documents. | Existing |
| `buyer_contacts` | Stores multiple buyer contact persons. | 10-14 | Belongs to `buyers`. | Missing |
| `supplier_contacts` | Stores multiple supplier contact persons. | 10-14 | Belongs to `suppliers`. | Missing |
| `buyer_addresses` | Stores multiple buyer billing/shipping/document addresses. | 12-18 | Belongs to `buyers`, geography masters. | Missing |
| `supplier_addresses` | Stores multiple supplier addresses. | 12-18 | Belongs to `suppliers`, geography masters. | Missing |
| `crm_communications` | Stores buyer/supplier communication history. | 12-18 | Belongs to buyer or supplier; created by `users`. | Missing |
| `crm_activities` | Stores CRM tasks, follow-ups, calls, meetings. | 12-18 | Belongs to buyer/supplier and assigned user. | Missing |

## Sales and Quotations

The current schema uses a generic document engine (`document_headers`, `document_items`, etc.) rather than separate quotation/invoice tables. Specialized business document tables are therefore marked missing unless the generic document table covers the concept partially.

| Table Name | Purpose | Estimated Columns | Relationships | Status |
|---|---|---:|---|---|
| `document_types` | Defines document types such as inquiry, quotation, proforma invoice, commercial invoice, packing list, shipping bill, certificates, insurance, inspection, payment receipt. | 5-7 | Parent of `document_headers`. | Existing |
| `document_headers` | Generic header for ERP/export documents. | 25-35 | Belongs to `document_types`, buyers, suppliers, currencies, incoterms, ports, payment terms, users. | Existing |
| `document_items` | Generic line items for documents. | 20-28 | Belongs to `document_headers`, products, varieties, units, packing types, origin countries. | Existing |
| `document_terms` | Terms and conditions per document. | 5-7 | Belongs to `document_headers`. | Existing |
| `document_charges` | Additional charges/taxes per document. | 8-12 | Belongs to `document_headers`. | Existing |
| `document_status_history` | Tracks document workflow status changes. | 7-10 | Belongs to `document_headers`; changed by `users`. | Existing |
| `quotations` | Dedicated quotation header if separated from document engine. | 18-28 | Buyer, currency, inquiry/source document, user. | Partial |
| `quotation_items` | Dedicated quotation line items if separated from document engine. | 14-24 | Quotation, products, units, packing. | Partial |
| `proforma_invoices` | Dedicated proforma invoice header if separated from document engine. | 18-30 | Buyer, quotation/source document, currency. | Partial |
| `proforma_invoice_items` | Dedicated proforma invoice line items if separated from document engine. | 14-24 | Proforma invoice, products. | Partial |
| `commercial_invoices` | Dedicated commercial invoice header if separated from document engine. | 20-34 | Buyer, proforma/source document, currency, finance. | Partial |
| `commercial_invoice_items` | Dedicated commercial invoice line items if separated from document engine. | 14-24 | Commercial invoice, products. | Partial |
| `sales_orders` | Sales order tracking after confirmation. | 18-30 | Buyer, quotation/proforma/commercial invoice, users. | Missing |
| `sales_order_items` | Sales order line items. | 14-24 | Sales order, products, units. | Missing |

## Purchase

| Table Name | Purpose | Estimated Columns | Relationships | Status |
|---|---|---:|---|---|
| `purchase_requests` | Purchase/procurement request header. | 14-22 | Supplier optional, requested by user, branch/company. | Missing |
| `purchase_request_items` | Purchase request line items. | 10-18 | Purchase request, products, units. | Missing |
| `purchase_orders` | Purchase order header. | 18-30 | Supplier, currency, payment terms, branch/company, users. | Missing |
| `purchase_order_items` | Purchase order line items. | 12-22 | Purchase order, products, units, packing types. | Missing |
| `goods_receipts` | Goods receipt/inward header. | 14-24 | Purchase order, supplier, branch/warehouse, user. | Missing |
| `goods_receipt_items` | Goods receipt line items. | 12-22 | Goods receipt, purchase order item, product. | Missing |

## Inventory

| Table Name | Purpose | Estimated Columns | Relationships | Status |
|---|---|---:|---|---|
| `stock_ledgers` | Complete stock movement ledger. | 16-26 | Product, branch/warehouse, source document, user. | Missing |
| `stock_batches` | Batch/lot level stock tracking. | 12-20 | Product, branch/warehouse, purchase/goods receipt. | Missing |
| `stock_adjustments` | Stock adjustment header. | 12-18 | Branch/warehouse, user approval. | Missing |
| `stock_adjustment_items` | Stock adjustment line items. | 10-18 | Adjustment, product, batch. | Missing |
| `warehouses` | Warehouse/storage location master if separate from branches. | 10-16 | Company/branch. | Missing |
| `warehouse_locations` | Bin/rack/location master. | 8-14 | Warehouse. | Missing |

## Export Documentation, Shipping, Certificates

| Table Name | Purpose | Estimated Columns | Relationships | Status |
|---|---|---:|---|---|
| `packing_lists` | Dedicated packing list header if separated from document engine. | 18-30 | Commercial invoice/source document, buyer, shipment. | Partial |
| `packing_list_items` | Dedicated packing list line details. | 14-24 | Packing list, products, containers. | Partial |
| `shipments` | Shipment planning and execution header. | 20-34 | Buyer, supplier, ports, shipping line, freight forwarder, source documents. | Missing |
| `shipment_items` | Shipment product/item allocation. | 12-22 | Shipment, products, document items, stock batches. | Missing |
| `shipment_containers` | Links containers to shipments. | 12-20 | Shipment, containers, packing list. | Missing |
| `shipment_milestones` | Shipment timeline/milestone tracking. | 10-16 | Shipment, user. | Missing |
| `shipping_bills` | Dedicated shipping bill header if separated from document engine. | 18-30 | Shipment, ports, buyer, document header. | Partial |
| `bills_of_lading` | Bill of lading data. | 18-32 | Shipment, shipping line, ports, buyer. | Partial |
| `certificates` | Generic certificate header if separated from document engine. | 16-28 | Shipment/document, certificate type, agency, user. | Partial |
| `certificate_items` | Certificate line/commodity details if needed. | 10-18 | Certificate, products/document items. | Missing |
| `document_attachments` | Stores document attachment metadata. | 9-12 | Belongs to `document_headers`, uploaded by `users`. | Existing |
| `document_revisions` | Stores document revision snapshots. | 7-10 | Belongs to `document_headers`, created by `users`. | Existing |

## Finance, Payments, Expenses

| Table Name | Purpose | Estimated Columns | Relationships | Status |
|---|---|---:|---|---|
| `banks` | Bank master. | 8-12 | Used by company, payments, finance. | Existing |
| `payments` | Payment records for receivables/payables. | 16-26 | Buyer/supplier, document/invoice, bank, currency, user. | Missing |
| `payment_allocations` | Allocates payments against invoices/documents. | 8-14 | Payment, document/invoice. | Missing |
| `payment_receipts` | Dedicated payment receipt document if separated from document engine. | 14-24 | Payment, buyer, bank, commercial invoice. | Partial |
| `expenses` | Expense header/record. | 14-24 | Supplier/vendor optional, shipment/order/document, category, user. | Missing |
| `expense_categories` | Expense category master. | 6-10 | Parent of `expenses`. | Missing |
| `order_costings` | Costing header for order/export job. | 14-24 | Sales order/shipment/commercial invoice. | Missing |
| `order_costing_items` | Costing line items. | 10-18 | Costing, expense category/charge. | Missing |
| `receivables` | Receivable ledger summary if separated from document/payment allocation. | 12-20 | Buyer, commercial invoice/document, payments. | Missing |
| `payables` | Payable ledger summary if separated from purchase/payment allocation. | 12-20 | Supplier, purchase order/bill, payments. | Missing |

## Tasks, Notifications, Automation

| Table Name | Purpose | Estimated Columns | Relationships | Status |
|---|---|---:|---|---|
| `tasks` | User tasks, follow-ups, approvals, reminders. | 14-22 | Assigned user, created user, related module/record. | Missing |
| `task_comments` | Task discussion/comments. | 6-10 | Task, user. | Missing |
| `notifications` | In-app notification records. | 10-16 | Recipient user, source module/record. | Missing |
| `notification_templates` | Templates for system notifications. | 8-14 | Used by automation. | Missing |
| `email_templates` | Email template definitions. | 8-14 | Used by email automation. | Missing |
| `email_logs` | Email send attempts and status. | 12-20 | Template, user, related module/record. | Missing |
| `whatsapp_templates` | WhatsApp message template definitions. | 8-14 | Used by WhatsApp automation. | Missing |
| `whatsapp_logs` | WhatsApp send attempts and status. | 12-20 | Template, user, related module/record. | Missing |
| `scheduled_jobs` | Scheduled job definitions/status if implemented inside app. | 10-16 | Optional user/system owner. | Missing |

## Audit, Reports, Dashboard, AI

| Table Name | Purpose | Estimated Columns | Relationships | Status |
|---|---|---:|---|---|
| `audit_logs` | Generic audit trail for create/update/delete/status changes. | 9-12 | Belongs to `users`; references table/record by name and ID. | Existing |
| `report_definitions` | Saved report definitions/filters. | 10-16 | Created by `users`. | Missing |
| `report_exports` | Tracks report export jobs/files. | 10-16 | Report definition, user. | Missing |
| `dashboard_widgets` | Dashboard widget configuration. | 10-16 | User/role/global configuration. | Missing |
| `dashboard_metrics_cache` | Optional cached dashboard metric values. | 8-14 | Widget/report reference. | Missing |
| `ai_conversations` | AI assistant conversation sessions. | 10-16 | User, module context. | Missing |
| `ai_messages` | AI conversation messages. | 8-14 | Conversation, user/system role. | Missing |
| `ai_action_logs` | Audit trail of AI-suggested or AI-assisted actions. | 10-18 | User, related module/record. | Missing |

---

# 3. ER Relationships

## Authentication and Authorization

- `roles` has many `users` through `users.role_id`.
- `users` and `roles` may also relate many-to-many through `user_roles`.
- `roles` and `permissions` relate many-to-many through `role_permissions`.
- `users` has many `login_logs`.
- `users` has many `audit_logs`.
- `users` is referenced by `created_by`, `updated_by`, `deleted_by`, `approved_by`, `changed_by`, and `uploaded_by` fields across master and document tables.

## Company and Branches

- `company` has many `branches`.
- `branches` are expected to be used by inventory, warehouse, user access, and shipment workflows.

## Geography

- `countries` has many `states`.
- `states` has many `cities`.
- `countries`, `states`, and `cities` are referenced by `buyers` and `suppliers`.
- `countries` is referenced by `ports`, `freight_forwarders`, and `inspection_agencies`.
- `countries` is also referenced by `document_items.origin_country_id`.

## Product Master

- `product_categories` has many `product_varieties`.
- `products` belongs to `product_categories`.
- `products` belongs to `product_varieties`.
- `products` belongs to `units`.
- `products` belongs to `packing_types`.
- `document_items` belongs to `products`.
- `document_items` may reference `product_varieties`, `units`, `packing_types`, and origin countries.

## CRM

- `buyers` has many sales/export documents through `document_headers.buyer_id`.
- `suppliers` has many documents through `document_headers.seller_id`.
- Future `buyer_contacts` and `buyer_addresses` belong to `buyers`.
- Future `supplier_contacts` and `supplier_addresses` belong to `suppliers`.
- Future `crm_communications` and `crm_activities` should reference buyer or supplier plus the responsible user.

## Document Engine

- `document_types` has many `document_headers`.
- `document_headers` has many `document_items`.
- `document_headers` has many `document_terms`.
- `document_headers` has many `document_charges`.
- `document_headers` has many `document_status_history`.
- `document_headers` has many `document_attachments`.
- `document_headers` has many `document_revisions`.
- `document_headers` references:
  - `buyers`
  - `suppliers`
  - `currencies`
  - `incoterms`
  - loading `ports`
  - destination `ports`
  - `payment_terms`
  - `users` for created/approved references
- `document_headers.converted_from_id` and `document_headers.converted_to_id` represent self-referencing document conversion relationships.

## Sales

- Current implementation is expected to use `document_headers` and `document_items` for inquiry, quotation, proforma invoice, and commercial invoice.
- If future dedicated sales tables are approved:
  - `sales_orders` should reference buyers and source documents.
  - `sales_order_items` should reference `sales_orders`, products, units, and packing types.

## Purchase

- Future `purchase_orders` should reference suppliers, currencies, payment terms, branches/company, and users.
- Future `purchase_order_items` should reference `purchase_orders`, products, units, and packing types.
- Future goods receipt tables should reference purchase orders and update inventory ledgers.

## Inventory

- Future `stock_ledgers` should reference products, branches/warehouses, source document/order records, and users.
- Future `stock_batches` should reference products and stock locations.
- Future stock adjustment tables should reference users and products.
- Containers may be linked to stock, packing, and shipment workflows.

## Shipping and Export Logistics

- Future `shipments` should reference buyers, source sales/export documents, ports, shipping lines, freight forwarders, inspection agencies, and users.
- Future `shipment_containers` should connect shipments to `containers`.
- Future `shipment_milestones` should belong to shipments.
- Existing document engine can store shipping bill, bill of lading, certificates, insurance, and inspection documents through document type records.

## Finance

- `banks` should be referenced by future payments and company banking details.
- Future `payments` should reference buyers/suppliers, banks, currencies, and related documents/invoices.
- Future `payment_allocations` should connect payments to payable/receivable documents.
- Future costing tables should reference sales orders, shipments, and expense categories.

## Tasks and Notifications

- Future `tasks` should reference assigned users, creator users, and related module records.
- Future `notifications` should reference recipient users and source records.
- Email/WhatsApp logs should reference templates, users, and related module records.

## Audit and Reports

- `audit_logs` references users and stores table/record identity generically.
- Future reports may read from all transactional tables.
- Future report definitions and exports should reference users.
- Dashboard metrics may be computed from sales, purchase, inventory, finance, documents, and audit tables.

---

# 4. Development Order

The database table creation order should respect dependency rules: parent/reference tables first, child/transaction tables later.

## Stage 1 - Core Security and Configuration

1. `roles`
2. `permissions`
3. `users`
4. `role_permissions`
5. `user_roles`
6. `login_logs`
7. `password_resets`
8. `settings`
9. `audit_logs`

## Stage 2 - Company and Global Masters

10. `company`
11. `branches`
12. `financial_years`
13. `currencies`
14. `number_series`

## Stage 3 - Geography and Logistics Masters

15. `countries`
16. `states`
17. `cities`
18. `ports`
19. `shipping_lines`
20. `containers`
21. `freight_forwarders`
22. `inspection_agencies`

## Stage 4 - Product and Packaging Masters

23. `product_categories`
24. `product_varieties`
25. `units`
26. `packing_types`
27. `hs_codes`
28. `product_grades`
29. `product_origins`
30. `products`
31. `product_packaging`

## Stage 5 - CRM Masters

32. `buyers`
33. `suppliers`
34. `buyer_contacts`
35. `supplier_contacts`
36. `buyer_addresses`
37. `supplier_addresses`
38. `crm_communications`
39. `crm_activities`

## Stage 6 - Document Engine

40. `document_types`
41. `document_headers`
42. `document_items`
43. `document_terms`
44. `document_charges`
45. `document_status_history`
46. `document_attachments`
47. `document_revisions`

## Stage 7 - Sales Extensions

48. `sales_orders`
49. `sales_order_items`
50. `quotations` if dedicated tables are approved
51. `quotation_items` if dedicated tables are approved
52. `proforma_invoices` if dedicated tables are approved
53. `proforma_invoice_items` if dedicated tables are approved
54. `commercial_invoices` if dedicated tables are approved
55. `commercial_invoice_items` if dedicated tables are approved

## Stage 8 - Purchase

56. `purchase_requests`
57. `purchase_request_items`
58. `purchase_orders`
59. `purchase_order_items`
60. `goods_receipts`
61. `goods_receipt_items`

## Stage 9 - Inventory

62. `warehouses`
63. `warehouse_locations`
64. `stock_batches`
65. `stock_ledgers`
66. `stock_adjustments`
67. `stock_adjustment_items`

## Stage 10 - Export Documentation and Shipping

68. `shipments`
69. `shipment_items`
70. `shipment_containers`
71. `shipment_milestones`
72. `packing_lists` if dedicated tables are approved
73. `packing_list_items` if dedicated tables are approved
74. `shipping_bills` if dedicated tables are approved
75. `bills_of_lading` if dedicated tables are approved
76. `certificates` if dedicated tables are approved
77. `certificate_items` if dedicated tables are approved

## Stage 11 - Finance

78. `banks`
79. `expense_categories`
80. `expenses`
81. `payments`
82. `payment_allocations`
83. `payment_receipts` if dedicated tables are approved
84. `receivables`
85. `payables`
86. `order_costings`
87. `order_costing_items`

Note: `banks` already exists in the current schema. It is listed in the finance stage because finance workflows depend on it, but actual creation should happen earlier if needed by master data.

## Stage 12 - Tasks, Notifications, Automation

88. `tasks`
89. `task_comments`
90. `notification_templates`
91. `notifications`
92. `email_templates`
93. `email_logs`
94. `whatsapp_templates`
95. `whatsapp_logs`
96. `scheduled_jobs`

## Stage 13 - Reports, Dashboard, AI

97. `report_definitions`
98. `report_exports`
99. `dashboard_widgets`
100. `dashboard_metrics_cache`
101. `ai_conversations`
102. `ai_messages`
103. `ai_action_logs`

---

# 5. Future Expansion

The following reserved tables should be considered for future modules after the core ERP is stable.

## Advanced CRM

- `crm_leads`
- `crm_lead_sources`
- `crm_campaigns`
- `crm_opportunities`
- `crm_pipeline_stages`

## Advanced Inventory and Warehouse

- `stock_transfers`
- `stock_transfer_items`
- `quality_checks`
- `quality_check_items`
- `warehouse_zones`
- `barcode_labels`

## Production or Processing

- `production_batches`
- `production_batch_items`
- `processing_orders`
- `processing_order_inputs`
- `processing_order_outputs`

## Compliance and Export

- `compliance_rules`
- `compliance_documents`
- `country_export_requirements`
- `gst_return_periods`
- `gst_return_lines`
- `dgft_documents`
- `customs_events`

## Finance Expansion

- `general_ledger_accounts`
- `journal_entries`
- `journal_entry_lines`
- `tax_ledgers`
- `bank_reconciliations`
- `bank_reconciliation_lines`

## Integrations

- `api_clients`
- `api_tokens`
- `api_request_logs`
- `webhook_subscriptions`
- `webhook_logs`
- `external_sync_jobs`
- `external_sync_logs`

## Automation and AI

- `workflow_definitions`
- `workflow_steps`
- `workflow_instances`
- `workflow_action_logs`
- `ai_prompts`
- `ai_knowledge_sources`
- `ai_document_drafts`
- `ai_review_queue`

## Archival and Performance

- `audit_logs_archive`
- `login_logs_archive`
- `document_revisions_archive`
- `report_cache`
- `dashboard_cache`

---

# Master Plan Notes

1. Existing generic document engine tables should remain the primary document architecture unless a dedicated table split is explicitly approved.
2. Dedicated tables such as `quotations`, `commercial_invoices`, `packing_lists`, and `certificates` are marked Partial because the current document engine can represent those document types, but separate physical tables do not currently exist.
3. All future schema work should be done through migration files under `database/migrations/`.
4. Existing schema and migration files should not be manually edited for production changes without approval.
5. Every new transactional table should include audit/user references where required.
6. Every future table should follow `utf8mb4`, `utf8mb4_unicode_ci`, InnoDB, indexed foreign keys, and status/timestamp conventions.
7. SQL is intentionally not included in this document.
