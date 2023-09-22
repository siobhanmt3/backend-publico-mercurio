

CREATE TABLE `dato`(
	`id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`socialfb` varchar(255),
	`socialig` varchar(255),
	`descripcion` varchar(255),
	`estado` varchar(75),
	`foto` longblob
);


CREATE TABLE `usuario`(
	`id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`usuario` varchar(255) NOT NULL,
	`correo` varchar(255) NOT NULL,
	`contrasena` varchar(60) NOT NULL,
	`id_dato` int NOT NULL, FOREIGN KEY (id_dato) REFERENCES dato(id)
);


CREATE TABLE `producto`(
	`id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`id_usuario` int NOT NULL, FOREIGN KEY (id_usuario) REFERENCES usuario(id),
	`nombre` varchar(90) NOT NULL,
	`descripcion` varchar(255) NOT NULL,
	`categoria` varchar(255) NOT NULL,
	`existencia` boolean NOT NULL,
	`cantidad` int(15) NOT NULL,
	`foto` longblob
);