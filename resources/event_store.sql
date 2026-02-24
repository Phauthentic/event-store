CREATE TABLE IF NOT EXISTS event_store (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stream VARCHAR(128) NULL,
    aggregate_id CHAR(36) NOT NULL,
    version INT NOT NULL,
    event VARCHAR(255) NOT NULL,
    payload TEXT NOT NULL,
    created_at VARCHAR(128) NOT NULL,
    correlation_id VARCHAR(255) NULL,
    meta_data TEXT NULL,
    INDEX idx_aggregate_id (aggregate_id),
    UNIQUE KEY unique_aggregate_version (aggregate_id, version)
);
