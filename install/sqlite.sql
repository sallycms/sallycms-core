-- Sally Database Dump Version 0.9.*
-- Prefix sly_

CREATE TABLE sly_article (id INTEGER NOT NULL, clang INTEGER NOT NULL, revision INTEGER DEFAULT 0 NOT NULL, online BOOLEAN DEFAULT '0' NOT NULL, deleted BOOLEAN DEFAULT '0' NOT NULL, type VARCHAR(64) NOT NULL, re_id INTEGER NOT NULL, path VARCHAR(255) NOT NULL, pos INTEGER NOT NULL, name VARCHAR(255) NOT NULL, catpos INTEGER NOT NULL, catname VARCHAR(255) NOT NULL, startpage BOOLEAN DEFAULT '0' NOT NULL, createdate DATETIME NOT NULL, updatedate DATETIME NOT NULL, createuser DATETIME NOT NULL, updateuser DATETIME NOT NULL, attributes CLOB NOT NULL, PRIMARY KEY(id, clang, revision));
CREATE INDEX parents ON sly_article (re_id);
CREATE INDEX types ON sly_article (type);
CREATE TABLE sly_article_slice (id INTEGER NOT NULL, article_id INTEGER NOT NULL, clang INTEGER NOT NULL, revision INTEGER DEFAULT 0 NOT NULL, pos INTEGER NOT NULL, slot VARCHAR(64) NOT NULL, slice_id INTEGER NOT NULL, createdate DATETIME NOT NULL, updatedate DATETIME NOT NULL, createuser DATETIME NOT NULL, updateuser DATETIME NOT NULL, PRIMARY KEY(id));
CREATE INDEX find_article ON sly_article_slice (article_id, clang, revision);
CREATE TABLE sly_clang (id INTEGER NOT NULL, revision INTEGER DEFAULT 0 NOT NULL, name VARCHAR(255) NOT NULL, locale VARCHAR(5) NOT NULL, PRIMARY KEY(id));
CREATE TABLE sly_config (id VARCHAR(255) NOT NULL, value CLOB NOT NULL, PRIMARY KEY(id));
CREATE TABLE sly_file (id INTEGER NOT NULL, revision INTEGER DEFAULT 0 NOT NULL, category_id INTEGER NOT NULL, title VARCHAR(255) NOT NULL, filename VARCHAR(255) NOT NULL, originalname VARCHAR(255) NOT NULL, filetype VARCHAR(255) NOT NULL, filesize INTEGER NOT NULL, width INTEGER NOT NULL, height INTEGER NOT NULL, createdate DATETIME NOT NULL, updatedate DATETIME NOT NULL, createuser DATETIME NOT NULL, updateuser DATETIME NOT NULL, attributes CLOB DEFAULT NULL, PRIMARY KEY(id));
CREATE INDEX filename ON sly_file (filename);
CREATE TABLE sly_file_category (id INTEGER NOT NULL, revision INTEGER DEFAULT 0 NOT NULL, name VARCHAR(255) NOT NULL, re_id INTEGER NOT NULL, path VARCHAR(255) NOT NULL, createdate DATETIME NOT NULL, updatedate DATETIME NOT NULL, createuser DATETIME NOT NULL, updateuser DATETIME NOT NULL, attributes CLOB DEFAULT NULL, PRIMARY KEY(id));
CREATE TABLE sly_registry (name VARCHAR(255) NOT NULL, value BLOB NOT NULL, PRIMARY KEY(name));
CREATE TABLE sly_slice (id INTEGER NOT NULL, module VARCHAR(64) NOT NULL, serialized_values CLOB NOT NULL, PRIMARY KEY(id));
CREATE TABLE sly_user (id INTEGER NOT NULL, revision INTEGER DEFAULT 0 NOT NULL, login VARCHAR(128) NOT NULL, password VARCHAR(128) NOT NULL, status BOOLEAN DEFAULT '0' NOT NULL, name VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, timezone VARCHAR(64) DEFAULT NULL, lasttrydate DATETIME DEFAULT NULL, createdate DATETIME NOT NULL, updatedate DATETIME NOT NULL, createuser DATETIME NOT NULL, updateuser DATETIME NOT NULL, attributes CLOB DEFAULT NULL, PRIMARY KEY(id));
CREATE INDEX logins ON sly_user (login);

-- populate database with some initial data
INSERT INTO sly_clang (name, locale) VALUES ('deutsch', 'de_DE');
INSERT INTO sly_config (id, value) VALUES ('start_article_id', '1');
INSERT INTO sly_config (id, value) VALUES ('notfound_article_id', '1');
INSERT INTO sly_config (id, value) VALUES ('default_clang_id', '1');
INSERT INTO sly_config (id, value) VALUES ('default_article_type', '""');
INSERT INTO sly_config (id, value) VALUES ('projectname', '"SallyCMS-Projekt"');
INSERT INTO sly_config (id, value) VALUES ('timezone', '"Europe/Berlin"');
INSERT INTO sly_config (id, value) VALUES ('default_locale', '"de_de"');
INSERT INTO sly_config (id, value) VALUES ('addons', '[]');
