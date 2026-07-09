# ESP001A - Document Engine Architecture

## Overview
The Document Engine is a core component of MESIGO ERP that manages the complete lifecycle of export documents from inquiry to payment receipt.

## Architecture Components

### 1. Entity Classes (classes/)
- **DocumentType.php** - Document type definitions and configurations
- **DocumentHeader.php** - Main document entity with header-level data
- **DocumentItem.php** - Document line items with product details
- **Buyer.php** - Buyer/Customer entity
- **NumberGenerator.php** - Auto-numbering for document series
- **DocumentStatusEngine.php** - Status workflow management
- **DocumentConversionEngine.php** - Document-to-document conversion
- **AttachmentManager.php** - File attachment management
- **RevisionManager.php** - Document revision tracking

### 2. Database Schema (database/schema/schema.sql)
Extended with the following tables:
- `document_types` - Document type definitions
- `document_headers` - Main document records
- `document_items` - Document line items
- `number_series` - Auto-numbering configuration
- `document_status_history` - Status change tracking
- `document_attachments` - File attachments
- `document_revisions` - Document revision history

### 3. Document Types
1. **Inquiry** (INQ) - Initial customer inquiry
2. **Quotation** (QTN) - Price quotation
3. **Proforma Invoice** (PI) - Proforma invoice
4. **Commercial Invoice** (CI) - Final commercial invoice
5. **Packing List** (PL) - Packing list
6. **Shipping Bill** (SB) - Shipping bill
7. **Bill of Lading** (BL) - Bill of lading
8. **Certificate of Origin** (CO) - Certificate of origin
9. **Phytosanitary** (PS) - Phytosanitary certificate
10. **Insurance** (INS) - Insurance certificate
11. **Inspection** (INSP) - Inspection certificate
12. **Payment Receipt** (PR) - Payment receipt

### 4. Status Workflow
- **Draft** (0) - Document in creation
- **Pending** (1) - Submitted for approval
- **Approved** (2) - Approved for processing
- **Rejected** (3) - Rejected, needs revision
- **Cancelled** (4) - Cancelled
- **Converted** (5) - Converted to another document
- **Closed** (6) - Finalized/closed

### 5. Conversion Flow
```
Inquiry → Quotation → Proforma Invoice → Commercial Invoice → Packing List → Shipping Bill
                                                                                   ↓
                              Bill of Lading, Certificate of Origin, Phytosanitary, Insurance, Inspection
                                                                                   ↓
                                                                                             Payment Receipt
```

### 6. Key Features
- **Auto-numbering** - Sequential numbering with prefix/suffix support
- **Status tracking** - Full audit trail of status changes
- **Document conversion** - One-click conversion between document types
- **Attachment management** - File upload and management
- **Revision control** - Track document revisions
- **Timeline view** - Complete document history

## File Structure
```
classes/
├── DocumentType.php
├── DocumentHeader.php
├── DocumentItem.php
├── Buyer.php
├── NumberGenerator.php
├── DocumentStatusEngine.php
├── DocumentConversionEngine.php
├── AttachmentManager.php
└── RevisionManager.php

database/
└── schema/
    └── schema.sql
```

## Integration Points
- Uses `Database.php` for PDO connection
- Uses `Auth.php` for user context
- Uses `Response.php` for API responses
- Uses `Validator.php` for data validation