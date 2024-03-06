CREATE TABLE IF NOT EXISTS event_store (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stream VARCHAR(128) NOT NULL,
    aggregate_id CHAR(36) NOT NULL,
    version INT NOT NULL,
    event VARCHAR(255) NOT NULL,
    payload TEXT NOT NULL,
    created_at VARCHAR(128) NOT NULL
);
