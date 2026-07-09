<?php
declare(strict_types=1);

/**
 * MESIGO ERP - Constants
 */

// Status constants
define('STATUS_INACTIVE', 0);
define('STATUS_ACTIVE', 1);
define('STATUS_DRAFT', 2);
define('STATUS_PENDING', 3);
define('STATUS_APPROVED', 4);
define('STATUS_REJECTED', 5);
define('STATUS_COMPLETED', 6);
define('STATUS_CANCELLED', 7);

// User types
define('USER_TYPE_ADMIN', 1);
define('USER_TYPE_MANAGER', 2);
define('USER_TYPE_STAFF', 3);
define('USER_TYPE_VIEWER', 4);

// Permission constants
define('PERM_CREATE', 'create');
define('PERM_READ', 'read');
define('PERM_UPDATE', 'update');
define('PERM_DELETE', 'delete');

// Document types
define('DOC_TYPE_PDF', 'pdf');
define('DOC_TYPE_IMAGE', 'image');
define('DOC_TYPE_SPREADSHEET', 'spreadsheet');

// Date formats
define('DATE_FORMAT', 'd-m-Y');
define('DATETIME_FORMAT', 'd-m-Y H:i:s');
define('DB_DATE_FORMAT', 'Y-m-d');
define('DB_DATETIME_FORMAT', 'Y-m-d H:i:s');