-- ============================================================
--  TruckerConnect — Full Improved Database Schema
--  Version 3.0 — All improvements added
-- ============================================================

-- ── 1. USERS ────────────────────────────────────────────────
CREATE TABLE users (
    id                          BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    role                        ENUM(
                                    'driver',
                                    'broker',
                                    'admin'
                                )                   NOT NULL,

    -- Basic Info
    full_name                   VARCHAR(255)        NOT NULL,
    email                       VARCHAR(255)        NOT NULL UNIQUE,
    password                    VARCHAR(255)        NOT NULL,
    phone                       VARCHAR(20)         NOT NULL,
    profile_photo_url           TEXT                NULL,
    company_name                VARCHAR(255)        NULL        COMMENT 'Broker only',

    -- Driver Info
    license_number              VARCHAR(100)        NULL        COMMENT 'Driver only',
    truck_type                  VARCHAR(100)        NULL        COMMENT 'Driver only',
    truck_plate                 VARCHAR(50)         NULL        COMMENT 'Driver only',

    -- Driver Availability
    is_available                TINYINT(1)          NOT NULL    DEFAULT 1
                                                    COMMENT 'Fix #5 — auto toggled by system',
    active_requests_count       TINYINT UNSIGNED    NOT NULL    DEFAULT 0
                                                    COMMENT 'Fix #5 — max 3 allowed',

    -- Driver Rating
    average_rating              DECIMAL(3,2)        NULL        DEFAULT NULL
                                                    COMMENT 'Fix #8 — recalculated after every review',
    total_reviews               INT UNSIGNED        NOT NULL    DEFAULT 0,

    -- Driver Stripe
    stripe_account_id           VARCHAR(255)        NULL        COMMENT 'Driver only — payout account',
    stripe_onboarding_status    ENUM(
                                    'pending',
                                    'verified',
                                    'restricted'
                                )                   NULL        DEFAULT NULL
                                                    COMMENT 'Fix #6 — checked before every transfer',
    stripe_onboarding_url       TEXT                NULL        COMMENT 'Driver only — resume onboarding',

    -- Broker Stripe
    stripe_customer_id          VARCHAR(255)        NULL        COMMENT 'Broker only — charge card',

    -- Push Notifications
    fcm_token                   TEXT                NULL        COMMENT 'Updated on every app open',

    -- ✅ NEW — Login tracking
    last_login_at               TIMESTAMP           NULL        COMMENT 'NEW — updated on every successful login | admin uses this to detect inactive accounts',

    -- Admin Control
    is_active                   TINYINT(1)          NOT NULL    DEFAULT 1,
    deactivated_at              TIMESTAMP           NULL,
    deactivation_reason         TEXT                NULL,

    created_at                  TIMESTAMP           NULL,
    updated_at                  TIMESTAMP           NULL,

    PRIMARY KEY (id),
    UNIQUE  KEY users_email_unique              (email),
    INDEX        users_role_index               (role),
    INDEX        users_is_active_index          (is_active),
    INDEX        users_is_available_index       (is_available),
    INDEX        users_average_rating_index     (average_rating),
    INDEX        users_stripe_account_index     (stripe_account_id),
    INDEX        users_stripe_customer_index    (stripe_customer_id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='All users — drivers, brokers, admins in one table';


-- ── 2. JOBS ─────────────────────────────────────────────────
CREATE TABLE jobs (
    id                          BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    broker_id                   BIGINT UNSIGNED     NOT NULL,
    driver_id                   BIGINT UNSIGNED     NULL,

    -- Job Details
    title                       VARCHAR(255)        NOT NULL,
    pickup_address              TEXT                NOT NULL,
    delivery_address            TEXT                NOT NULL,
    pickup_lat                  DECIMAL(10,7)       NULL,
    pickup_lng                  DECIMAL(10,7)       NULL,
    delivery_lat                DECIMAL(10,7)       NULL,
    delivery_lng                DECIMAL(10,7)       NULL,
    scheduled_at                TIMESTAMP           NOT NULL,
    load_type                   VARCHAR(100)        NOT NULL,
    load_weight                 DECIMAL(10,2)       NULL,
    payment_rate                DECIMAL(10,2)       NOT NULL,
    min_rating                  DECIMAL(3,2)        NULL        DEFAULT 0.00
                                                    COMMENT 'Fix #8 — drivers below this cannot request',
    instructions                TEXT                NULL,

    -- Job Status
    status                      ENUM(
                                    'open',
                                    'assigned',
                                    'in_progress',
                                    'completed',
                                    'cancelled'
                                )                   NOT NULL    DEFAULT 'open',

    -- ✅ NEW — Upfront Payment Tracking (Fix #1)
    upfront_payment_status      ENUM(
                                    'pending',
                                    'paid',
                                    'refunded'
                                )                   NOT NULL    DEFAULT 'pending'
                                                    COMMENT 'NEW Fix #1 — broker must pay before drivers can be assigned',
    upfront_paid_at             TIMESTAMP           NULL        COMMENT 'NEW — when broker completed upfront payment',

    -- Cancellation
    cancellation_reason         TEXT                NULL,
    cancelled_by                BIGINT UNSIGNED     NULL,
    cancelled_at                TIMESTAMP           NULL,

    created_at                  TIMESTAMP           NULL,
    updated_at                  TIMESTAMP           NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (broker_id)    REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id)    REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (cancelled_by) REFERENCES users(id) ON DELETE SET NULL,

    INDEX jobs_broker_id_index              (broker_id),
    INDEX jobs_driver_id_index              (driver_id),
    INDEX jobs_status_index                 (status),
    INDEX jobs_upfront_payment_status_index (upfront_payment_status)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='All freight jobs posted by brokers';


-- ── 3. JOB REQUESTS ─────────────────────────────────────────
CREATE TABLE job_requests (
    id                          BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    job_id                      BIGINT UNSIGNED     NOT NULL,
    driver_id                   BIGINT UNSIGNED     NOT NULL,
    status                      ENUM(
                                    'pending',
                                    'assigned',
                                    'rejected',
                                    'expired',
                                    'cancelled'
                                )                   NOT NULL    DEFAULT 'pending',
    note                        TEXT                NULL        COMMENT 'Optional message from driver to broker',
    expires_at                  TIMESTAMP           NOT NULL    COMMENT 'Fix #4 — 2 hours from created_at',
    responded_at                TIMESTAMP           NULL,

    -- ✅ NEW — Rejection reason (Fix #9)
    rejection_reason            VARCHAR(255)        NULL        COMMENT 'NEW Fix #9 — stored here and included in FCM message to driver',

    created_at                  TIMESTAMP           NULL,
    updated_at                  TIMESTAMP           NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (job_id)    REFERENCES jobs(id)  ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX job_requests_job_id_index    (job_id),
    INDEX job_requests_driver_id_index (driver_id),
    INDEX job_requests_status_index    (status),
    INDEX job_requests_expires_at_index(expires_at)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Driver requests for jobs — max 3 active per driver';


-- ── 4. DOCUMENTS ────────────────────────────────────────────
CREATE TABLE documents (
    id                          BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    job_id                      BIGINT UNSIGNED     NOT NULL,
    uploaded_by                 BIGINT UNSIGNED     NOT NULL,
    file_url                    TEXT                NOT NULL    COMMENT 'Firebase Storage URL',
    file_type                   ENUM(
                                    'photo',
                                    'pdf',
                                    'signature'
                                )                   NOT NULL    DEFAULT 'photo',
    file_name                   VARCHAR(255)        NULL,

    -- ✅ NEW — Document Verification
    verified_by                 BIGINT UNSIGNED     NULL        COMMENT 'NEW — broker_id who reviewed this document',
    verified_at                 TIMESTAMP           NULL        COMMENT 'NEW — when broker confirmed this document | used as proof in disputes',

    created_at                  TIMESTAMP           NULL,
    updated_at                  TIMESTAMP           NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (job_id)      REFERENCES jobs(id)  ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL,

    INDEX documents_job_id_index      (job_id),
    INDEX documents_uploaded_by_index (uploaded_by)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Delivery proof files — URL only stored, file lives on Firebase';


-- ── 5. INVOICES ─────────────────────────────────────────────
CREATE TABLE invoices (
    id                          BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    job_id                      BIGINT UNSIGNED     NOT NULL,
    driver_id                   BIGINT UNSIGNED     NOT NULL,
    broker_id                   BIGINT UNSIGNED     NOT NULL,
    invoice_number              VARCHAR(50)         NOT NULL    UNIQUE,
    amount                      DECIMAL(10,2)       NOT NULL    COMMENT 'Full job amount — snapshot of jobs.payment_rate',
    platform_fee                DECIMAL(10,2)       NOT NULL    COMMENT 'Fee kept by platform',
    driver_amount               DECIMAL(10,2)       NOT NULL    COMMENT 'Net amount driver receives',
    status                      ENUM(
                                    'pending',
                                    'paid',
                                    'refunded',
                                    'cancelled'
                                )                   NOT NULL    DEFAULT 'pending',
    due_date                    TIMESTAMP           NULL,
    paid_at                     TIMESTAMP           NULL,

    created_at                  TIMESTAMP           NULL,
    updated_at                  TIMESTAMP           NULL,

    PRIMARY KEY (id),
    UNIQUE  KEY invoices_invoice_number_unique (invoice_number),
    FOREIGN KEY (job_id)    REFERENCES jobs(id)  ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (broker_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX invoices_job_id_index    (job_id),
    INDEX invoices_driver_id_index (driver_id),
    INDEX invoices_broker_id_index (broker_id),
    INDEX invoices_status_index    (status)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Auto-generated invoices — one per completed job';


-- ── 6. PAYMENTS ─────────────────────────────────────────────
CREATE TABLE payments (
    id                          BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    invoice_id                  BIGINT UNSIGNED     NOT NULL,
    job_id                      BIGINT UNSIGNED     NOT NULL,
    driver_id                   BIGINT UNSIGNED     NOT NULL,
    broker_id                   BIGINT UNSIGNED     NOT NULL,
    released_by                 BIGINT UNSIGNED     NULL        COMMENT 'Admin who released hold',

    -- Amounts
    amount                      DECIMAL(10,2)       NOT NULL    COMMENT 'Full amount broker paid',
    platform_fee                DECIMAL(10,2)       NOT NULL    COMMENT 'Fee kept by platform',
    driver_amount               DECIMAL(10,2)       NOT NULL    COMMENT 'Net payout to driver',
    currency                    VARCHAR(10)         NOT NULL    DEFAULT 'usd',

    -- Status
    status                      ENUM(
                                    'processing',
                                    'paid',
                                    'refunded',
                                    'failed',
                                    'on_hold'
                                )                   NOT NULL    DEFAULT 'processing',

    -- Stripe IDs
    stripe_payment_intent_id    VARCHAR(255)        NULL,
    stripe_transfer_id          VARCHAR(255)        NULL,
    stripe_refund_id            VARCHAR(255)        NULL,

    -- Hold System
    admin_hold                  TINYINT(1)          NOT NULL    DEFAULT 0
                                                    COMMENT 'Fix #3 — admin sets true to freeze payment',
    held_at                     TIMESTAMP           NULL,
    released_at                 TIMESTAMP           NULL,
    failure_reason              TEXT                NULL,
    refund_reason               TEXT                NULL,

    -- ✅ NEW — Cancellation Fee Tracking (Fix #2)
    cancellation_fee            DECIMAL(10,2)       NULL        COMMENT 'NEW Fix #2 — penalty fee on cancellation',
    cancellation_fee_to         ENUM(
                                    'driver',
                                    'platform',
                                    'broker'
                                )                   NULL        COMMENT 'NEW Fix #2 — who receives the cancellation fee',

    created_at                  TIMESTAMP           NULL,
    updated_at                  TIMESTAMP           NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (invoice_id)  REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (job_id)      REFERENCES jobs(id)     ON DELETE CASCADE,
    FOREIGN KEY (driver_id)   REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (broker_id)   REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (released_by) REFERENCES users(id)    ON DELETE SET NULL,

    INDEX payments_invoice_id_index (invoice_id),
    INDEX payments_job_id_index     (job_id),
    INDEX payments_driver_id_index  (driver_id),
    INDEX payments_broker_id_index  (broker_id),
    INDEX payments_status_index     (status),
    INDEX payments_admin_hold_index (admin_hold)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Full payment audit trail — Stripe IDs + hold system';


-- ── 7. REVIEWS ──────────────────────────────────────────────
CREATE TABLE reviews (
    id                          BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    job_id                      BIGINT UNSIGNED     NOT NULL,
    driver_id                   BIGINT UNSIGNED     NOT NULL,
    broker_id                   BIGINT UNSIGNED     NOT NULL,

    -- 4 Rating Categories (1-5)
    punctuality                 TINYINT UNSIGNED    NOT NULL    COMMENT '1-5 stars',
    safety                      TINYINT UNSIGNED    NOT NULL    COMMENT '1-5 stars',
    compliance                  TINYINT UNSIGNED    NOT NULL    COMMENT '1-5 stars',
    professionalism             TINYINT UNSIGNED    NOT NULL    COMMENT '1-5 stars',
    average_score               DECIMAL(3,2)        NOT NULL    COMMENT 'Calculated average of 4 categories',
    comment                     TEXT                NULL,

    -- ✅ NEW — Admin Moderation
    is_hidden                   TINYINT(1)          NOT NULL    DEFAULT 0
                                                    COMMENT 'NEW — admin can hide abusive or unfair reviews',
    hidden_by                   BIGINT UNSIGNED     NULL        COMMENT 'NEW — admin who hid this review',

    created_at                  TIMESTAMP           NULL,
    updated_at                  TIMESTAMP           NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (job_id)    REFERENCES jobs(id)  ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (broker_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (hidden_by) REFERENCES users(id) ON DELETE SET NULL,

    INDEX reviews_driver_id_index (driver_id),
    INDEX reviews_broker_id_index (broker_id),
    INDEX reviews_is_hidden_index (is_hidden)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Driver ratings by brokers — 4 categories';


-- ── 8. NOTIFICATIONS ────────────────────────────────────────
CREATE TABLE notifications (
    id                          BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    user_id                     BIGINT UNSIGNED     NOT NULL,
    job_id                      BIGINT UNSIGNED     NULL,
    type                        VARCHAR(100)        NOT NULL    COMMENT 'e.g. job_assigned, payment_released, job_rejected',
    title                       VARCHAR(255)        NOT NULL,
    body                        TEXT                NOT NULL,
    data                        JSON                NULL        COMMENT 'Flutter uses this for deep linking e.g. {"job_id": "5"}',
    read_at                     TIMESTAMP           NULL,

    created_at                  TIMESTAMP           NULL,
    updated_at                  TIMESTAMP           NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (job_id)  REFERENCES jobs(id)  ON DELETE SET NULL,

    INDEX notifications_user_id_index (user_id),
    INDEX notifications_read_at_index (read_at)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='In-app notification history — all roles';


-- ── 9. CONFIG ───────────────────────────────────────────────
CREATE TABLE config (
    id                          BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    platform_fee_percent        DECIMAL(5,2)        NOT NULL    DEFAULT 5.00
                                                    COMMENT 'Platform service fee — editable from admin dashboard',
    maintenance_mode            TINYINT(1)          NOT NULL    DEFAULT 0,
    maintenance_message         TEXT                NULL,
    support_email               VARCHAR(255)        NULL,
    max_active_requests         TINYINT UNSIGNED    NOT NULL    DEFAULT 3
                                                    COMMENT 'Fix #5 — max requests a driver can have at once',
    request_expiry_hours        TINYINT UNSIGNED    NOT NULL    DEFAULT 2
                                                    COMMENT 'Fix #4 — hours before job request expires',
    payment_hold_hours          TINYINT UNSIGNED    NOT NULL    DEFAULT 24
                                                    COMMENT 'Hours before payment auto releases to driver',

    created_at                  TIMESTAMP           NULL,
    updated_at                  TIMESTAMP           NULL,

    PRIMARY KEY (id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Platform settings — always one row — all editable from admin';


-- ── 10. DRIVER BLOCKS (NEW TABLE) ───────────────────────────
CREATE TABLE driver_blocks (
    id                          BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    broker_id                   BIGINT UNSIGNED     NOT NULL    COMMENT 'Broker who blocked',
    driver_id                   BIGINT UNSIGNED     NOT NULL    COMMENT 'Driver who is blocked',
    reason                      TEXT                NULL        COMMENT 'Why broker blocked this driver',

    created_at                  TIMESTAMP           NULL,
    updated_at                  TIMESTAMP           NULL,

    PRIMARY KEY (id),
    UNIQUE  KEY unique_block (broker_id, driver_id)
                                                    COMMENT 'Same broker cannot block same driver twice',
    FOREIGN KEY (broker_id) REFERENCES users(id)   ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES users(id)   ON DELETE CASCADE,

    INDEX driver_blocks_broker_id_index (broker_id),
    INDEX driver_blocks_driver_id_index (driver_id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='NEW — brokers can block specific drivers from their jobs';


