CREATE DATABASE IF NOT EXISTS publicis_groupe_backend;
USE publicis_groupe_backend;

CREATE TABLE users(
id                int(255) auto_increment not null,
nombre            varchar(50) NOT NULL,
apaterno          varchar(100) NOT NULL,
amaterno          varchar(100) NOT NULL,
telefono          int(255) NOT NULL,
fecha_nacimiento  date NOT NULL,,
genero            varchar(100) NOT NULL,
rol               varchar(20),
email             varchar(255) NOT NULL,
password          varchar(255) NOT NULL,
usuario           varchar(255) NOT NULL,
created_at        datetime DEFAULT NULL,
updated_at        datetime DEFAULT NULL,
token             varchar(255),
token_reco        varchar(255),
CONSTRAINT pk_users PRIMARY KEY(id)
)ENGINE=InnoDb;

CREATE TABLE categories(
id              int(255) auto_increment not null,
name            varchar(100),
created_at      datetime DEFAULT NULL,
updated_at      datetime DEFAULT NULL,
CONSTRAINT pk_categories PRIMARY KEY(id)
)ENGINE=InnoDb;

CREATE TABLE mimusicas(
id              int(255) auto_increment not null,
user_id         int(255) not null,
category_id     int(255) not null,
name           varchar(255) not null,
author           varchar(255) not null,
created_at      datetime DEFAULT NULL,
updated_at      datetime DEFAULT NULL,
CONSTRAINT pk_mimusicas PRIMARY KEY(id),
CONSTRAINT fk_mimusica_user FOREIGN KEY(user_id) REFERENCES users(id),
CONSTRAINT fk_mimusica_category FOREIGN KEY(category_id) REFERENCES categories(id)
)ENGINE=InnoDb;