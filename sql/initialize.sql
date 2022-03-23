/*
mysql [dbname] < initialize.sql.
*/

/* INDENTIFICATION TYPE */
INSERT INTO `identification_type` (`id`,`name`) VALUES (1,'IFK / CIF');
INSERT INTO `identification_type` (`id`,`name`) VALUES (2,'IFZ-AIZ / NIF-NIE');
INSERT INTO `identification_type` (`id`,`name`) VALUES (3,'Atzerritarra / Extranjero');

/* DURATION TYPE */
INSERT INTO `duration_type` (`id`,`name`) VALUES (2,'Egunak / Días');
INSERT INTO `duration_type` (`id`,`name`) VALUES (3,'Asteak / Semanas');
INSERT INTO `duration_type` (`id`,`name`) VALUES (4,'Hilabeteak / Meses');
INSERT INTO `duration_type` (`id`,`name`) VALUES (5,'Urteak / Años');

/* CONTRACT TYPE */
INSERT INTO `contract_type` (`id`,`name`,`max_amount`) VALUES (1,'Obrak / Obras',48400);
INSERT INTO `contract_type` (`id`,`name`,`max_amount`) VALUES (2,'Zerbitzuak / Servicios',48400);
INSERT INTO `contract_type` (`id`,`name`,`max_amount`) VALUES (3,'Hornikuntza / Suministros',48400);
INSERT INTO `contract_type` (`id`,`name`,`max_amount`) VALUES (8,'Harpidetzak / Suscripciones',48400);