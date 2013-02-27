-- Sally Database Dump Version 0.9.*
-- Prefix sly_

CREATE TABLE sly_article (id INT UNSIGNED NOT NULL, clang INT UNSIGNED NOT NULL, revision INT UNSIGNED DEFAULT 0 NOT NULL, re_id INT UNSIGNED NOT NULL, name VARCHAR(255) NOT NULL, catname VARCHAR(255) NOT NULL, catpos INT UNSIGNED NOT NULL, attributes TEXT NOT NULL, startpage TINYINT(1) NOT NULL, pos INT UNSIGNED NOT NULL, path VARCHAR(255) NOT NULL, type VARCHAR(64) NOT NULL, deleted TINYINT(1) NOT NULL, createdate DATETIME NOT NULL, updatedate DATETIME NOT NULL, createuser VARCHAR(255) NOT NULL, updateuser VARCHAR(255) NOT NULL, PRIMARY KEY(id, clang, revision)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE sly_article_slice (id INT UNSIGNED AUTO_INCREMENT NOT NULL, clang INT UNSIGNED NOT NULL, slot VARCHAR(64) NOT NULL, pos INT UNSIGNED NOT NULL, slice_id INT UNSIGNED DEFAULT 0 NOT NULL, article_id INT UNSIGNED NOT NULL, createdate DATETIME NOT NULL, updatedate DATETIME NOT NULL, createuser VARCHAR(255) NOT NULL, updateuser VARCHAR(255) NOT NULL, revision INT UNSIGNED DEFAULT 0 NOT NULL, INDEX find_article (article_id, clang, revision), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE sly_clang (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, locale VARCHAR(5) NOT NULL, revision INT UNSIGNED DEFAULT 0 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE sly_file (id INT UNSIGNED AUTO_INCREMENT NOT NULL, re_file_id INT UNSIGNED NOT NULL, category_id INT UNSIGNED NOT NULL, attributes TEXT NULL, filetype VARCHAR(255) NOT NULL, filename VARCHAR(255) NOT NULL, originalname VARCHAR(255) NOT NULL, filesize INT UNSIGNED NOT NULL, width INT UNSIGNED NOT NULL, height INT UNSIGNED NOT NULL, title VARCHAR(255) NOT NULL, createdate DATETIME NOT NULL, updatedate DATETIME NOT NULL, createuser VARCHAR(255) NOT NULL, updateuser VARCHAR(255) NOT NULL, revision INT UNSIGNED DEFAULT 0 NOT NULL, INDEX filename (filename), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE sly_file_category (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, re_id INT UNSIGNED NOT NULL, path VARCHAR(255) NOT NULL, attributes TEXT NULL, createdate DATETIME NOT NULL, updatedate DATETIME NOT NULL, createuser VARCHAR(255) NOT NULL, updateuser VARCHAR(255) NOT NULL, revision INT UNSIGNED DEFAULT 0 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE sly_user (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(255) NULL, description VARCHAR(255) NULL, login VARCHAR(128) NOT NULL, password CHAR(128), status TINYINT(1) NOT NULL, attributes TEXT NULL, lasttrydate DATETIME NULL, timezone VARCHAR(64) NULL, createdate DATETIME NOT NULL, updatedate DATETIME NOT NULL, createuser VARCHAR(255) NOT NULL, updateuser VARCHAR(255) NOT NULL, revision INT UNSIGNED DEFAULT 0 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE sly_slice (id INT UNSIGNED AUTO_INCREMENT NOT NULL, module VARCHAR(64) NOT NULL, serialized_values LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE sly_registry (name VARCHAR(255) NOT NULL, value BLOB NOT NULL, PRIMARY KEY(name)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;

-- populate database with some initial data
INSERT INTO sly_clang (name, locale) VALUES ('deutsch', 'de_DE');
