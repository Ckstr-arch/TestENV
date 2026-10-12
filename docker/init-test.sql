-- Dedicated disposable development test database, created only on first volume initialization.
CREATE DATABASE IF NOT EXISTS sportshub_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON sportshub_test.* TO 'sportshub'@'%';
