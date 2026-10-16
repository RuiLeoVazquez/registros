CREATE DATABASE IF NOT EXISTS sae_escolar CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci;
USE sae_escolar;

CREATE TABLE alumnos (
 idalumn INT AUTO_INCREMENT PRIMARY KEY,
 matricula_al VARCHAR(20) NOT NULL UNIQUE,
 nombre_al VARCHAR(60) NOT NULL,
 apaterno_al VARCHAR(60) NOT NULL,
 amaterno_al VARCHAR(60) NOT NULL,
 dom_al VARCHAR(150) NOT NULL,
 mail_al VARCHAR(100) NOT NULL,
 tel_al VARCHAR(20) NOT NULL,
 estatus_al ENUM('ALTA','BAJA') NOT NULL DEFAULT 'ALTA'
) ENGINE=InnoDB;

CREATE TABLE profesores (
 idprof INT AUTO_INCREMENT PRIMARY KEY,
 nocontrol_prof VARCHAR(20) NOT NULL UNIQUE,
 nombre_prof VARCHAR(60) NOT NULL,
 apaterno_prof VARCHAR(60) NOT NULL,
 amaterno_prof VARCHAR(60) NOT NULL,
 dom_prof VARCHAR(150) NOT NULL,
 mail_prof VARCHAR(100) NOT NULL,
 tel_prof VARCHAR(20) NOT NULL,
 estatus_prof ENUM('ALTA','BAJA') NOT NULL DEFAULT 'ALTA'
) ENGINE=InnoDB;

CREATE TABLE materias (
 idmateria INT AUTO_INCREMENT PRIMARY KEY,
 descripcion_mat VARCHAR(100) NOT NULL,
 estatus_mat ENUM('ACTIVA','INACTIVA') NOT NULL DEFAULT 'ACTIVA'
) ENGINE=InnoDB;

CREATE TABLE grupos (
 idgrupo INT AUTO_INCREMENT PRIMARY KEY,
 descripcion_grupo VARCHAR(50) NOT NULL,
 estatus_grupo ENUM('ALTA','BAJA') NOT NULL DEFAULT 'ALTA'
) ENGINE=InnoDB;

CREATE TABLE asignar_grupo (
 idasignar_grupo INT AUTO_INCREMENT PRIMARY KEY,
 idgrupo INT NOT NULL,
 idalumn INT NOT NULL,
 idprof INT NOT NULL,
 UNIQUE KEY uq_alumno_grupo (idalumn,idgrupo),
 FOREIGN KEY (idgrupo) REFERENCES grupos(idgrupo),
 FOREIGN KEY (idalumn) REFERENCES alumnos(idalumn),
 FOREIGN KEY (idprof) REFERENCES profesores(idprof)
) ENGINE=InnoDB;

CREATE TABLE asignar_materia (
 idasignar_materia INT AUTO_INCREMENT PRIMARY KEY,
 idmateria INT NOT NULL,
 idalumn INT NOT NULL,
 idprof INT NOT NULL,
 calificacion DECIMAL(5,2) NULL,
 UNIQUE KEY uq_alumno_materia (idalumn,idmateria),
 FOREIGN KEY (idmateria) REFERENCES materias(idmateria),
 FOREIGN KEY (idalumn) REFERENCES alumnos(idalumn),
 FOREIGN KEY (idprof) REFERENCES profesores(idprof)
) ENGINE=InnoDB;
