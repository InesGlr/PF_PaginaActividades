CREATE DATABASE Actividades;

USE Actividades;

CREATE TABLE categoria (
  id int(11) AUTO_INCREMENT PRIMARY KEY,
  nombre varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  descripcion varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
);


CREATE TABLE direccion (
  id int(11) AUTO_INCREMENT PRIMARY KEY,
  pais varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  provincia varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  ciudad varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  codPostal int(11) NOT NULL,
  calle varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  numero int(11) NOT NULL,
  piso varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL
);


CREATE TABLE usuario (
  id int(11) AUTO_INCREMENT PRIMARY KEY,
  nombre varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  correo varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  clave varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  rol longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  UNIQUE KEY nombre (nombre),
  UNIQUE KEY correo (correo)
);


CREATE TABLE seguidor (
  id int(11) AUTO_INCREMENT PRIMARY KEY,
  usuario int(11) NOT NULL,
  follower int(11) NOT NULL,
  CONSTRAINT fk_seguidor FOREIGN KEY (follower) REFERENCES usuario (id),
  CONSTRAINT fk_usuario FOREIGN KEY (usuario) REFERENCES usuario (id),
  KEY fk_usuario (usuario),
  KEY fk_seguidor (follower)
);


CREATE TABLE actividad (
  id int(11) AUTO_INCREMENT PRIMARY KEY,
  nombre varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  descripcion text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  direccion int(11) NULL,
  coordinador int(11) NOT NULL,
  tipo varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  estado varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  categoria int(11) NULL,
  CONSTRAINT fk_categoria FOREIGN KEY (categoria) REFERENCES categoria (id),
  CONSTRAINT fk_coordinador FOREIGN KEY (coordinador) REFERENCES usuario (id),
  CONSTRAINT fk_direccion FOREIGN KEY (direccion) REFERENCES direccion (id) ON DELETE CASCADE ON UPDATE CASCADE,
  KEY fk_categoria (categoria),
  KEY fk_direccion (direccion),
  KEY fk_coordinador (coordinador)
);


CREATE TABLE sesion (
  id int(11) AUTO_INCREMENT PRIMARY KEY,
  fecha date NOT NULL,
  hora time NOT NULL,
  duracion time NULL,
  entradas int(50) NOT NULL,
  actividad int(1) NOT NULL,
  CONSTRAINT fk_actividad FOREIGN KEY (actividad) REFERENCES actividad (id) ON DELETE CASCADE ON UPDATE CASCADE,
  KEY fk_actividad (actividad)
);


CREATE TABLE pedido (
  id int(11) AUTO_INCREMENT PRIMARY KEY,
  usuario int(11) NULL,
  sesion int(11) NULL,
  entradas int(100) NOT NULL,
  fecha datetime NOT NULL,
  CONSTRAINT fk_pedido_sesion FOREIGN KEY (sesion) REFERENCES sesion (id),
  CONSTRAINT fk_pedido_usuario FOREIGN KEY (usuario) REFERENCES usuario (id),
  KEY fk_pedido_usuario (usuario),
  KEY fk_pedido_sesion (sesion)
);

CREATE TABLE devolucion (
  id int(11) AUTO_INCREMENT PRIMARY KEY,
  pedido int(11) NOT NULL,
  entradas int(50) NOT NULL,
  fecha datetime NOT NULL,
  motivo varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  CONSTRAINT fk_pedido FOREIGN KEY (pedido) REFERENCES pedido (id),
  KEY fk_pedido (pedido)
);


CREATE TABLE doctrine_migration_versions (
  version varchar(191) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL PRIMARY KEY,
  executed_at datetime  NULL,
  execution_time int(11) NULL
);


CREATE TABLE reset_password_request (
  id int(11) AUTO_INCREMENT PRIMARY KEY,
  user_id int(11) NOT NULL,
  selector varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  hashed_token varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  requested_at datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  expires_at datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  KEY IDX_7CE748AA76ED395 (user_id),
  CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES usuario (id)
);

INSERT INTO categoria (id, nombre, descripcion) VALUES
(1, 'Taller', 'Los talleres son espacios de aprendizaje práctico donde los participantes pueden adquirir nuevas habilidades y conocimientos en áreas específicas, como arte, tecnología, cocina, carpintería, y más. Estos eventos fomentan la creatividad y el desarrollo personal, permitiendo a los asistentes practicar'),
(2, 'Música', 'Clases de instrumentos, canto, interpretación, composición musical, y talleres de producción musical');

INSERT INTO direccion (id, pais, provincia, ciudad, codPostal, calle, numero, piso) VALUES
(1, 'España', 'Toledo', 'Olias del rey', 45280, 'Roma', 55, NULL);

INSERT INTO usuario (id, nombre, correo, clave, rol) VALUES
(1, 'ana', 'ana@gmail.com', '123456', '[\"ROLE_USER\"]'),
(2, 'ines', 'ines@gmail.com', '123456', '[\"ROLE_USER\",\"ROLE_ADMIN\"]'),
(5, 'superAdminActIVAT', 'superAdminActIVAT@gmail.com', '123456', '[\"ROLE_USER\", \"ROLE_ADMIN\", \"ROLE_SUPER_ADMIN\"]');

INSERT INTO seguidor (id, usuario, follower) VALUES
(1, 5, 2);

INSERT INTO actividad (id, direccion, categoria, coordinador, nombre, descripcion, tipo, estado) VALUES
(1, NULL, 1, 2, 'Clase de algebra', '<p>&iexcl;Bienvenidos a la oportunidad de sumergirse en el fascinante mundo del &aacute;lgebra! En nuestras clases, te guiaremos a trav&eacute;s de conceptos fundamentales y avanzados para desarrollar una comprensi&oacute;n s&oacute;lida de las matem&aacu', 'online', 'cancelado'),
(2, 1, 2, 2, 'Clase de guitarra', '<h3><strong>&iquest;Qu&eacute; aprender&aacute;s?</strong></h3>\r\n\r\n<ol>\r\n	<li>\r\n	<p><strong>Nivel Principiante</strong>:</p>\r\n\r\n	<ul>\r\n		<li>C&oacute;mo afinar tu guitarra y aprender los acordes b&aacute;sicos.</li>\r\n		<li>T&eacute;cnicas de rasgueo y punteo.</li>\r\n		<li>Tocar tus primeras canciones y ritmos populares.</li>\r\n		<li>Introducci&oacute;n a la lectura de tablaturas.</li>\r\n	</ul>\r\n	</li>\r\n	<li>\r\n	<p><strong>Nivel Intermedio</strong>:</p>\r\n\r\n	<ul>\r\n		<li>Progresiones de acordes m&aacute;s avanzadas.</li>\r\n		<li>T&eacute;cnicas como hammer-on, pull-off y slides.</li>\r\n		<li>Escalas y c&oacute;mo usarlas para improvisar.</li>\r\n		<li>Introducci&oacute;n a estilos como blues, rock o fingerpicking.</li>\r\n	</ul>\r\n	</li>\r\n	<li>\r\n	<p><strong>Nivel Avanzado</strong>:</p>\r\n\r\n	<ul>\r\n		<li>Construcci&oacute;n de solos y melod&iacute;as.</li>\r\n		<li>Composici&oacute;n propia y arreglos.</li>\r\n		<li>T&eacute;cnicas avanzadas como sweep picking y tapping.</li>\r\n		<li>Perfecci&oacute;n en interpretaci&oacute;n y teor&iacute;a musical aplicada.</li>\r\n	</ul>\r\n	</li>\r\n</ol>\r\n\r\n<h3><strong>&iquest;Por qu&eacute; elegir mis clases?</strong></h3>\r\n\r\n<ul>\r\n	<li><strong>Enfoque personalizado</strong>: Me adapto a tu nivel, estilo y objetivos.</li>\r\n	<li><strong>Clases din&aacute;micas</strong>: Cada sesi&oacute;n est&aacute; dise&ntilde;ada para ser pr&aacute;ctica y divertida.</li>\r\n	<li><strong>Flexibilidad horaria</strong>: Nos acomodamos a tus horarios, con la posibilidad de clases presenciales o en l&iacute;nea.</li>\r\n	<li><strong>M&eacute;todo pr&aacute;ctico</strong>: Aprender&aacute;s a tocar canciones que te gusten desde las primeras clases.</li>\r\n</ul>', 'presencial', 'publicado');

INSERT INTO sesion (id, actividad, fecha, hora, duracion, entradas) VALUES
(1, 1, '2024-12-12', '02:00:00', '02:00:00', 10),
(2, 2, '2024-12-13', '12:00:00', '02:00:00', 10),
(4, 2, '2024-12-10', '12:00:00', '03:00:00', 12);

INSERT INTO pedido (id, sesion, usuario, entradas, fecha) VALUES
(1, 1, 2, 0, '2024-12-01 19:10:44');

INSERT INTO devolucion (id, pedido, entradas, fecha, motivo) VALUES
(1, 1, 2, '2024-12-01 19:10:58', 'sin motivo'),
(2, 1, 2, '2024-12-01 19:11:25', 'Cancelacion por problemas con la actividad');

INSERT INTO doctrine_migration_versions (version, executed_at, execution_time) VALUES
('DoctrineMigrations\\Version20241201161036', '2024-12-01 17:17:22', 935);


INSERT INTO reset_password_request (id, user_id, selector, hashed_token, requested_at, expires_at) VALUES
(3, 2, 'mNcccsMvGdlHwpJjqV0O', 'kwebs3om+28To9eHdfNkE13II5ORoX/sJav50fePmYY=', '2024-11-03 13:06:09', '2024-11-03 14:06:09');
