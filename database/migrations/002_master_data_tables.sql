-- Upgrading Buyer table for Export CRM requirements
ALTER TABLE buyers 
ADD COLUMN company_website VARCHAR(255) NULL,
ADD COLUMN primary_contact_name VARCHAR(150) NULL,
ADD COLUMN primary_contact_email VARCHAR(150) NULL,
ADD COLUMN primary_contact_phone VARCHAR(50) NULL,
ADD COLUMN bank_name VARCHAR(255) NULL,
ADD COLUMN bank_branch VARCHAR(255) NULL,
ADD COLUMN bank_account_number VARCHAR(100) NULL,
ADD COLUMN bank_swift_code VARCHAR(50) NULL,
ADD COLUMN payment_terms VARCHAR(100) NULL,
ADD COLUMN export_region VARCHAR(100) NULL,
ADD COLUMN crm_notes TEXT NULL,
ADD COLUMN crm_last_contacted_at DATETIME NULL;

-- Create table for multiple contacts
CREATE TABLE IF NOT EXISTS buyer_contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    name VARCHAR(150),
    designation VARCHAR(100),
    email VARCHAR(150),
    phone VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES buyers(id) ON DELETE CASCADE
);

-- Create table for multiple addresses
CREATE TABLE IF NOT EXISTS buyer_addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    address_type ENUM('billing', 'shipping', 'other') DEFAULT 'billing',
    address_line1 VARCHAR(255),
    address_line2 VARCHAR(255),
    city VARCHAR(100),
    state VARCHAR(100),
    country VARCHAR(100),
    postal_code VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES buyers(id) ON DELETE CASCADE
);

-- Create table for documents
CREATE TABLE IF NOT EXISTS buyer_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    document_name VARCHAR(255),
    file_path VARCHAR(255),
    document_type VARCHAR(100),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES buyers(id) ON DELETE CASCADE
);
