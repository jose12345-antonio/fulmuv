-- MySQL dump 10.13  Distrib 8.0.40, for Linux (x86_64)
--
-- Host: localhost    Database: grupo_iso
-- ------------------------------------------------------
-- Server version	8.0.40-0ubuntu0.20.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `areas`
--

DROP TABLE IF EXISTS `areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `areas` (
  `id_area` int unsigned NOT NULL AUTO_INCREMENT,
  `id_sucursal` int NOT NULL,
  `nombre` varchar(500) COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `estado` varchar(1) COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_area`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `areas`
--

LOCK TABLES `areas` WRITE;
/*!40000 ALTER TABLE `areas` DISABLE KEYS */;
INSERT INTO `areas` VALUES (1,1,'DESARROLLO','A','2025-01-09 19:40:02','2025-01-09 19:40:02');
/*!40000 ALTER TABLE `areas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalogos`
--

DROP TABLE IF EXISTS `catalogos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `catalogos` (
  `id_catalogo` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_spanish_ci NOT NULL,
  `descripcion` varchar(150) COLLATE utf8mb4_spanish_ci DEFAULT '',
  `estado` varchar(1) COLLATE utf8mb4_spanish_ci DEFAULT 'A',
  `id_sucursal` int NOT NULL,
  `productos` json NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `creation_user` int DEFAULT '0',
  `default_empresa` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT 'true',
  `tipo` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT 'G',
  PRIMARY KEY (`id_catalogo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalogos`
--

LOCK TABLES `catalogos` WRITE;
/*!40000 ALTER TABLE `catalogos` DISABLE KEYS */;
/*!40000 ALTER TABLE `catalogos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categorias`
--

DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias` (
  `id_categoria` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_spanish_ci NOT NULL,
  `estado` varchar(1) COLLATE utf8mb4_spanish_ci DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias`
--

LOCK TABLES `categorias` WRITE;
/*!40000 ALTER TABLE `categorias` DISABLE KEYS */;
/*!40000 ALTER TABLE `categorias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contenedor`
--

DROP TABLE IF EXISTS `contenedor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contenedor` (
  `id_contenedor` int NOT NULL AUTO_INCREMENT,
  `imagen` varchar(200) COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `color` varchar(10) COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `header` text CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci,
  `footer` text CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci,
  `estado` varchar(1) COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_contenedor`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contenedor`
--

LOCK TABLES `contenedor` WRITE;
/*!40000 ALTER TABLE `contenedor` DISABLE KEYS */;
INSERT INTO `contenedor` VALUES (1,'files/Grupo-ISO-blanco_20241029_223628.png','#00686f','','','A','2024-10-29 16:23:44','2024-10-29 22:36:29');
/*!40000 ALTER TABLE `contenedor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `correo_control`
--

DROP TABLE IF EXISTS `correo_control`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `correo_control` (
  `id_correo_control` int NOT NULL AUTO_INCREMENT,
  `id_correo_plantilla` int NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `estado` varchar(1) COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_correo_control`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `correo_control`
--

LOCK TABLES `correo_control` WRITE;
/*!40000 ALTER TABLE `correo_control` DISABLE KEYS */;
INSERT INTO `correo_control` VALUES (1,1,'orden_creada','A','2024-10-30 21:09:41','2024-11-21 20:40:50'),(2,2,'orden_enviada','A','2024-10-30 21:09:41','2024-11-21 20:40:52'),(3,3,'orden_procesada','A','2024-10-30 21:09:41','2024-11-21 20:40:54'),(4,4,'orden_aprobada','A','2024-10-30 21:09:41','2024-11-21 20:40:55'),(5,5,'orden_completada','A','2024-10-30 21:09:41','2024-11-21 20:40:57');
/*!40000 ALTER TABLE `correo_control` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `correo_plantilla`
--

DROP TABLE IF EXISTS `correo_plantilla`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `correo_plantilla` (
  `id_correo` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(300) COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `cuerpo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci,
  `descripcion` varchar(300) COLLATE utf8mb4_spanish_ci DEFAULT '',
  `id_contenedor` int NOT NULL DEFAULT '1',
  `estado` varchar(10) COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_correo`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `correo_plantilla`
--

LOCK TABLES `correo_plantilla` WRITE;
/*!40000 ALTER TABLE `correo_plantilla` DISABLE KEYS */;
INSERT INTO `correo_plantilla` VALUES (1,'Orden Creada','<h1><strong>Hola, tu orden ha sido creada.</strong></h1>','Enviar cuando se crea una orden',1,'A','2024-10-30 20:37:09','2025-01-09 20:59:26'),(2,'Orden Enviada','<h1><strong>Hola, tu orden ha sido enviada.</strong></h1>','Enviar cuando se manda una orden',1,'A','2024-10-30 20:37:09','2025-01-09 20:59:36'),(3,'Orden Procesada','<h1><strong>Hola, tu orden ha sido procesada.</strong></h1>','Enviar cuando se procesa una orden',1,'A','2024-10-30 20:37:09','2025-01-09 20:59:58'),(4,'Orden Aprobada','<h1><strong>Hola, tu orden ha sido aprobada.</strong></h1>','Enviar cuando se aprueba una orden',1,'A','2024-10-30 20:37:09','2025-01-09 20:58:48'),(5,'Orden Completada','<h1><strong>Hola, tu orden ha sido completada.</strong></h1>','Enviar cuando se completa una orden',1,'A','2024-10-30 20:37:09','2025-01-09 20:59:02');
/*!40000 ALTER TABLE `correo_plantilla` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `correos_default`
--

DROP TABLE IF EXISTS `correos_default`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `correos_default` (
  `id_correo_default` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `estado` varchar(10) COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_correo_default`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `correos_default`
--

LOCK TABLES `correos_default` WRITE;
/*!40000 ALTER TABLE `correos_default` DISABLE KEYS */;
/*!40000 ALTER TABLE `correos_default` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empresas`
--

DROP TABLE IF EXISTS `empresas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empresas` (
  `id_empresa` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(1000) COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `direccion` varchar(1000) COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `tipo_establecimiento` int NOT NULL,
  `estado` varchar(1) COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `razon_social` varchar(1000) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `img_path` varchar(255) COLLATE utf8mb4_spanish_ci DEFAULT '',
  PRIMARY KEY (`id_empresa`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empresas`
--

LOCK TABLES `empresas` WRITE;
/*!40000 ALTER TABLE `empresas` DISABLE KEYS */;
INSERT INTO `empresas` VALUES (1,'BONSAI','AURORA',1,'A','2025-01-09 19:36:35','2025-01-09 19:36:35','BONSAI SA','');
/*!40000 ALTER TABLE `empresas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `establecimientos`
--

DROP TABLE IF EXISTS `establecimientos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `establecimientos` (
  `id_establecimiento` int unsigned NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(500) COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `estado` varchar(1) COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_establecimiento`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `establecimientos`
--

LOCK TABLES `establecimientos` WRITE;
/*!40000 ALTER TABLE `establecimientos` DISABLE KEYS */;
INSERT INTO `establecimientos` VALUES (1,'Alimenticio','A','2024-07-30 16:47:15'),(2,'Industrial / Comercial','A','2024-07-30 19:47:28');
/*!40000 ALTER TABLE `establecimientos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ordenes_empresas`
--

DROP TABLE IF EXISTS `ordenes_empresas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ordenes_empresas` (
  `id_orden` int NOT NULL AUTO_INCREMENT,
  `id_empresa` int NOT NULL,
  `id_sucursal` int NOT NULL,
  `id_area` varchar(45) COLLATE utf8mb4_spanish_ci NOT NULL,
  `productos` json NOT NULL,
  `orden_estado` varchar(50) COLLATE utf8mb4_spanish_ci DEFAULT 'creada',
  `estado` varchar(1) COLLATE utf8mb4_spanish_ci DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `total` float NOT NULL DEFAULT '0',
  `creation_user` int DEFAULT '0',
  PRIMARY KEY (`id_orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ordenes_empresas`
--

LOCK TABLES `ordenes_empresas` WRITE;
/*!40000 ALTER TABLE `ordenes_empresas` DISABLE KEYS */;
/*!40000 ALTER TABLE `ordenes_empresas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ordenes_iso`
--

DROP TABLE IF EXISTS `ordenes_iso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ordenes_iso` (
  `id_orden_iso` int NOT NULL AUTO_INCREMENT,
  `ordenes` json NOT NULL,
  `orden_estado` varchar(50) COLLATE utf8mb4_spanish_ci DEFAULT 'procesada',
  `total` float NOT NULL DEFAULT '0',
  `estado` varchar(1) COLLATE utf8mb4_spanish_ci DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_orden_iso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ordenes_iso`
--

LOCK TABLES `ordenes_iso` WRITE;
/*!40000 ALTER TABLE `ordenes_iso` DISABLE KEYS */;
/*!40000 ALTER TABLE `ordenes_iso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ordenes_notas`
--

DROP TABLE IF EXISTS `ordenes_notas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ordenes_notas` (
  `id_nota` int unsigned NOT NULL AUTO_INCREMENT,
  `id_orden` int NOT NULL,
  `id_usuario` int NOT NULL,
  `accion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` varchar(1) COLLATE utf8mb4_spanish_ci DEFAULT 'A',
  `tipo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `tipo_nota` varchar(1) COLLATE utf8mb4_spanish_ci DEFAULT 'E',
  PRIMARY KEY (`id_nota`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ordenes_notas`
--

LOCK TABLES `ordenes_notas` WRITE;
/*!40000 ALTER TABLE `ordenes_notas` DISABLE KEYS */;
/*!40000 ALTER TABLE `ordenes_notas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permisos`
--

DROP TABLE IF EXISTS `permisos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permisos` (
  `id_permisos` int unsigned NOT NULL AUTO_INCREMENT,
  `permiso` varchar(500) NOT NULL DEFAULT '',
  `valor` varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `levels` varchar(500) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT 'A',
  `id_rol` int unsigned NOT NULL,
  PRIMARY KEY (`id_permisos`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permisos`
--

LOCK TABLES `permisos` WRITE;
/*!40000 ALTER TABLE `permisos` DISABLE KEYS */;
INSERT INTO `permisos` VALUES (1,'Empresas','true','GrupoIso','2024-08-06 15:41:40','2024-08-06 15:41:40','A',1),(2,'Usuarios','true','GrupoIso','2024-08-06 15:41:40','2024-08-06 15:41:40','A',1),(3,'Dashboard','true','GrupoIso','2024-08-06 15:41:40','2024-08-06 15:41:40','A',1),(4,'Roles','true','GrupoIso','2024-08-06 15:41:40','2024-08-06 15:41:40','A',1),(5,'Empresas','false','Empresa','2024-08-06 15:41:40','2024-08-06 15:41:40','A',2),(6,'Usuarios','true','Empresa','2024-08-06 15:41:40','2024-08-06 15:41:40','A',2),(7,'Dashboard','false','Empresa','2024-08-06 15:41:40','2024-08-06 15:41:40','A',2),(8,'Roles','false','GrupoIso','2024-08-06 15:41:40','2024-08-06 15:41:40','A',2),(9,'Empresas','false','Empresa','2024-08-06 15:41:40','2024-08-06 15:41:40','A',3),(10,'Usuarios','true','Sucursal','2024-08-06 15:41:40','2024-08-06 15:41:40','A',3),(11,'Dashboard','false','GrupoIso','2024-08-06 15:41:40','2024-08-06 15:41:40','A',3),(12,'Roles','false','GrupoIso','2024-08-06 15:41:40','2024-08-06 15:41:40','A',3),(17,'Ordenes','true','GrupoIso','2024-08-06 15:41:40','2024-08-06 15:41:40','A',1),(18,'Ordenes','true','Empresa','2024-08-06 15:41:40','2024-08-06 15:41:40','A',2),(19,'Ordenes','true','Sucursal','2024-08-06 15:41:40','2024-08-06 15:41:40','A',3),(20,'Productos','true','GrupoIso','2024-08-06 15:41:40','2024-08-06 15:41:40','A',1),(21,'Productos','false','Empresa','2024-08-06 15:41:40','2024-08-06 15:41:40','A',2),(22,'Productos','false','Sucursal','2024-08-06 15:41:40','2024-08-06 15:41:40','A',3),(23,'Catalogos','true','GrupoIso','2024-08-06 15:41:40','2024-08-06 15:41:40','A',1),(24,'Catalogos','false','Empresa','2024-08-06 15:41:40','2024-08-06 15:41:40','A',2),(25,'Catalogos','false','Sucursal','2024-08-06 15:41:40','2024-08-06 15:41:40','A',3),(26,'E-mail','true','GrupoIso','2024-08-06 15:41:40','2024-08-06 15:41:40','A',1),(27,'E-mail','false','GrupoIso','2024-08-06 15:41:40','2024-08-06 15:41:40','A',2),(28,'E-mail','false','GrupoIso','2024-08-06 15:41:40','2024-08-06 15:41:40','A',3),(29,'Empresas','false','GrupoIso','2024-11-13 16:23:51','2024-11-13 16:23:51','A',5),(30,'Usuarios','false','GrupoIso','2024-11-13 16:23:51','2024-11-13 16:23:51','A',5),(31,'Productos','false','GrupoIso','2024-11-13 16:23:51','2024-11-13 16:23:51','A',5),(32,'Catalogos','true','Vendedor','2024-11-13 16:23:51','2024-11-13 16:23:51','A',5),(33,'E-mail','false','GrupoIso','2024-11-13 16:23:51','2024-11-13 16:23:51','A',5),(34,'Ordenes','true','Vendedor','2024-11-13 16:23:51','2024-11-13 16:23:51','A',5),(35,'Dashboard','true','Vendedor','2024-11-13 16:23:51','2024-11-13 16:23:51','A',5),(36,'Roles','false','GrupoIso','2024-11-13 16:23:51','2024-11-13 16:23:51','A',5);
/*!40000 ALTER TABLE `permisos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos`
--

DROP TABLE IF EXISTS `productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `productos` (
  `id_producto` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_spanish_ci NOT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci,
  `codigo` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT '',
  `categoria` int NOT NULL,
  `sub_categoria` int NOT NULL,
  `tags` varchar(300) COLLATE utf8mb4_spanish_ci DEFAULT '',
  `img_path` varchar(255) COLLATE utf8mb4_spanish_ci DEFAULT '',
  `estado` varchar(1) COLLATE utf8mb4_spanish_ci DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ficha_tecnica` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `precio_referencia` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_producto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos`
--

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rol`
--

DROP TABLE IF EXISTS `rol`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rol` (
  `id_rol` int unsigned NOT NULL AUTO_INCREMENT,
  `rol` varchar(500) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'A',
  PRIMARY KEY (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rol`
--

LOCK TABLES `rol` WRITE;
/*!40000 ALTER TABLE `rol` DISABLE KEYS */;
INSERT INTO `rol` VALUES (1,'Owner','2023-03-23 16:27:12','A'),(2,'Admin','2024-02-20 19:02:01','A'),(3,'Manager','2024-02-20 19:02:04','A'),(5,'Vendedor Iso','2024-11-13 16:23:51','A');
/*!40000 ALTER TABLE `rol` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sub_categorias`
--

DROP TABLE IF EXISTS `sub_categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sub_categorias` (
  `id_sub_categoria` int unsigned NOT NULL AUTO_INCREMENT,
  `id_categoria` int NOT NULL,
  `nombre` varchar(500) COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `estado` varchar(1) COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_sub_categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sub_categorias`
--

LOCK TABLES `sub_categorias` WRITE;
/*!40000 ALTER TABLE `sub_categorias` DISABLE KEYS */;
/*!40000 ALTER TABLE `sub_categorias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sucursales`
--

DROP TABLE IF EXISTS `sucursales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sucursales` (
  `id_sucursal` int unsigned NOT NULL AUTO_INCREMENT,
  `id_empresa` int NOT NULL,
  `nombre` varchar(500) COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `direccion` varchar(1000) COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `estado` varchar(1) COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_sucursal`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sucursales`
--

LOCK TABLES `sucursales` WRITE;
/*!40000 ALTER TABLE `sucursales` DISABLE KEYS */;
INSERT INTO `sucursales` VALUES (1,1,'BONSAI SUCURSAL','PLATINUM','A','2025-01-09 19:38:39','2025-01-09 19:38:39');
/*!40000 ALTER TABLE `sucursales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id_usuario` int NOT NULL AUTO_INCREMENT,
  `nombre_usuario` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `pass` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `rol_id` int unsigned NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `estado` varchar(1) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT 'A',
  `nombres` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `correo` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `imagen` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `id` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  PRIMARY KEY (`id_usuario`),
  KEY `rol_id` (`rol_id`),
  CONSTRAINT `rol_id_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `rol` (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'admin','$2a$10$098b72b21a867db4f9e62OIQxuhOQSSNa1.mIUlY/zhtGXLTY2PXq',1,'2023-02-13 19:05:21','A','Admin Bonsai','admin@bonsai.com.ec','../theme/public/assets/img/team/avatar.png',''),(2,'admin_grupoiso','$2a$10$e580b10a0e939a59adc0buCcerDHy4b07ThezeGgqzkYetnPcwgfq',1,'2023-02-13 19:05:21','A','Admin GrupoIso','denisse.bayas@isolatot.com','../theme/public/assets/img/team/avatar.png','');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-01-13 17:42:18
