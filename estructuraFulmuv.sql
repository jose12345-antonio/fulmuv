-- MySQL dump 10.13  Distrib 8.0.42, for Linux (x86_64)
--
-- Host: localhost    Database: fulmuv
-- ------------------------------------------------------
-- Server version	8.0.42-0ubuntu0.20.04.1

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
-- Table structure for table `agentes`
--

DROP TABLE IF EXISTS `agentes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `agentes` (
  `id_agente` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `correo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `codigo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `estado` varchar(1) DEFAULT 'A',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `tipo` varchar(100) DEFAULT 'general',
  PRIMARY KEY (`id_agente`)
) ENGINE=InnoDB AUTO_INCREMENT=123 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `archivos_empleos`
--

DROP TABLE IF EXISTS `archivos_empleos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `archivos_empleos` (
  `id_archivo_empleo` int unsigned NOT NULL AUTO_INCREMENT,
  `id_empleo` int DEFAULT NULL,
  `archivo` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `tipo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_archivo_empleo`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `archivos_empresa`
--

DROP TABLE IF EXISTS `archivos_empresa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `archivos_empresa` (
  `id_archivo_empresa` int unsigned NOT NULL AUTO_INCREMENT,
  `id_empresa` int DEFAULT NULL,
  `archivo` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `tipo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `titulo` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `descripcion` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_archivo_empresa`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `archivos_productos`
--

DROP TABLE IF EXISTS `archivos_productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `archivos_productos` (
  `id_archivo_producto` int unsigned NOT NULL AUTO_INCREMENT,
  `id_producto` int DEFAULT NULL,
  `archivo` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `tipo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_archivo_producto`)
) ENGINE=InnoDB AUTO_INCREMENT=1277 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `archivos_vehiculos`
--

DROP TABLE IF EXISTS `archivos_vehiculos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `archivos_vehiculos` (
  `id_archivo_vehiculo` int unsigned NOT NULL AUTO_INCREMENT,
  `id_vehiculo` int DEFAULT NULL,
  `archivo` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `tipo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_archivo_vehiculo`)
) ENGINE=InnoDB AUTO_INCREMENT=150 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `areas`
--

DROP TABLE IF EXISTS `areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `areas` (
  `id_area` int unsigned NOT NULL AUTO_INCREMENT,
  `id_sucursal` int NOT NULL,
  `nombre` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_area`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `atributos`
--

DROP TABLE IF EXISTS `atributos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `atributos` (
  `id_atributo` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `tipo_dato` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `opciones` json DEFAULT NULL,
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'A',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_atributo`)
) ENGINE=InnoDB AUTO_INCREMENT=187 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `banner`
--

DROP TABLE IF EXISTS `banner`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `banner` (
  `id_banner` int NOT NULL AUTO_INCREMENT,
  `imagen` varchar(500) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `estado` varchar(1) DEFAULT 'A',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_banner`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `catalogos`
--

DROP TABLE IF EXISTS `catalogos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `catalogos` (
  `id_catalogo` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `descripcion` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'A',
  `id_sucursal` int NOT NULL,
  `productos` json NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `creation_user` int DEFAULT '0',
  `default_empresa` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'true',
  `tipo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'G',
  PRIMARY KEY (`id_catalogo`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `categorias`
--

DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias` (
  `id_categoria` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `referencia` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `categoria_principal` json DEFAULT NULL,
  `tipo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `imagen` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `atributos` json DEFAULT NULL,
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `categorias_principales`
--

DROP TABLE IF EXISTS `categorias_principales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias_principales` (
  `id_categoria_principal` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `imagen` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_categoria_principal`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clientes` (
  `id_cliente` int NOT NULL AUTO_INCREMENT,
  `nombres` varchar(100) DEFAULT '',
  `cedula` varchar(100) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `telefono` varchar(100) DEFAULT NULL,
  `password` varchar(500) DEFAULT NULL,
  `estado` varchar(1) DEFAULT 'A',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `apellidos` varchar(100) DEFAULT '',
  PRIMARY KEY (`id_cliente`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `climatizacion`
--

DROP TABLE IF EXISTS `climatizacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `climatizacion` (
  `id_climatizacion` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'A',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_climatizacion`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `colores`
--

DROP TABLE IF EXISTS `colores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `colores` (
  `id_color` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'A',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_color`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contacto_fulmuv`
--

DROP TABLE IF EXISTS `contacto_fulmuv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contacto_fulmuv` (
  `id_contacto` int NOT NULL AUTO_INCREMENT,
  `motivo` varchar(200) DEFAULT '',
  `nombre_empresa` varchar(200) DEFAULT '',
  `titular` varchar(100) DEFAULT '',
  `telefono` varchar(100) DEFAULT '',
  `correo` varchar(100) DEFAULT '',
  `comentario` varchar(5000) DEFAULT '',
  `estado` varchar(1) DEFAULT 'A',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_contacto`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contenedor`
--

DROP TABLE IF EXISTS `contenedor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contenedor` (
  `id_contenedor` int NOT NULL AUTO_INCREMENT,
  `imagen` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `color` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `header` text CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci,
  `footer` text CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci,
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_contenedor`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cookies_consent`
--

DROP TABLE IF EXISTS `cookies_consent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cookies_consent` (
  `id_consent` int NOT NULL AUTO_INCREMENT,
  `consent_token` varchar(100) DEFAULT '',
  `decision` varchar(100) DEFAULT '',
  `ts_ms` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ip_addr` varchar(1000) DEFAULT NULL,
  `user_agent` varchar(1000) DEFAULT '',
  `cookie_essential` tinyint(1) DEFAULT '0',
  `cookie_analiticas` tinyint(1) DEFAULT '0',
  `cookie_publicidad` tinyint(1) DEFAULT '0',
  `country_code` varchar(100) DEFAULT '',
  `source_page` varchar(100) DEFAULT '',
  `version_texto` varchar(100) DEFAULT '',
  PRIMARY KEY (`id_consent`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_correo_control`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `correo_plantilla`
--

DROP TABLE IF EXISTS `correo_plantilla`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `correo_plantilla` (
  `id_correo` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `cuerpo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci,
  `descripcion` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `id_contenedor` int NOT NULL DEFAULT '1',
  `estado` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_correo`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `correos_default`
--

DROP TABLE IF EXISTS `correos_default`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `correos_default` (
  `id_correo_default` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `estado` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_correo_default`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `datos_domicilio`
--

DROP TABLE IF EXISTS `datos_domicilio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `datos_domicilio` (
  `id_domicilio` int NOT NULL AUTO_INCREMENT,
  `nombres` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `cedula` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `telefono` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `direccion_exacta` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `punto_referencial` varchar(500) DEFAULT '',
  `provincia` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `canton` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `parroquia` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `codigo_postal` varchar(100) DEFAULT '',
  `horario_entrega` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `observaciones` varchar(5000) DEFAULT '',
  `estado` varchar(1) DEFAULT 'A',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `id_orden` int DEFAULT NULL,
  `latitud` varchar(200) DEFAULT '',
  `longitud` varchar(200) DEFAULT '',
  `parroquia_id` varchar(100) DEFAULT '',
  `canton_id` varchar(100) DEFAULT '',
  PRIMARY KEY (`id_domicilio`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `datos_facturacion`
--

DROP TABLE IF EXISTS `datos_facturacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `datos_facturacion` (
  `id_facturacion` int NOT NULL AUTO_INCREMENT,
  `tipo_identificacion` varchar(100) DEFAULT NULL,
  `razon_social` varchar(500) DEFAULT NULL,
  `numero_identificacion` varchar(100) DEFAULT NULL,
  `correo` varchar(500) DEFAULT NULL,
  `telefono` varchar(500) DEFAULT NULL,
  `direccion` varchar(5000) DEFAULT NULL,
  `forma_pago` varchar(100) DEFAULT NULL,
  `id_orden` int DEFAULT NULL,
  `estado` varchar(1) DEFAULT 'A',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_facturacion`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `direccion`
--

DROP TABLE IF EXISTS `direccion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `direccion` (
  `id_direccion` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'A',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_direccion`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `empleos`
--

DROP TABLE IF EXISTS `empleos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empleos` (
  `id_empleo` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `provincia` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `canton` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `img_frontal` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `img_posterior` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci,
  `id_empresa` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `tipo_creador` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'empresa',
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'A',
  `tags` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  PRIMARY KEY (`id_empleo`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `empresas`
--

DROP TABLE IF EXISTS `empresas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empresas` (
  `id_empresa` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `nombre_titular` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `correo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `direccion` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `tipo_tienda` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `img_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `latitud` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `longitud` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `provincia` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `ciudad` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `tiempo_anos` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT '',
  `tiempo_meses` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT '',
  `guardia` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT '',
  `camaras` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT '',
  `parqueadero` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT '',
  `tipo_parqueadero` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `garantia_ofrecida` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `garantia_tiempo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `garantia_condiciones` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `garantia_terminos` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `instala_productos` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `instalacion_tipos` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `instalacion_valor` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT '',
  `horario_tipo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `horario_otro` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `telefono_contacto` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `whatsapp_contacto` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `categorias_referencia` json DEFAULT NULL,
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'A',
  `canton` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `calle_principal` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `calle_secundaria` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `bien_inmueble` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `razon_social` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `tipo_identificacion` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `cedula_ruc` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `sucursales` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'N',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_empresa`)
) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `establecimientos`
--

DROP TABLE IF EXISTS `establecimientos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `establecimientos` (
  `id_establecimiento` int unsigned NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_establecimiento`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eventos`
--

DROP TABLE IF EXISTS `eventos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eventos` (
  `id_evento` int NOT NULL AUTO_INCREMENT,
  `id_empresa` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `titulo` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `organizador` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `imagen` varchar(500) DEFAULT NULL,
  `fecha_hora_inicio` datetime DEFAULT NULL,
  `fecha_hora_fin` datetime DEFAULT NULL,
  `estado` varchar(1) DEFAULT 'A',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `tipo_evento` int DEFAULT NULL,
  `subtipo_evento` varchar(500) DEFAULT NULL,
  `enlace` varchar(500) DEFAULT '',
  `direccion` varchar(500) DEFAULT '',
  `provincia` varchar(500) DEFAULT '',
  `canton` varchar(500) DEFAULT '',
  `modalidad` varchar(100) DEFAULT '',
  `tipo_entrada` varchar(100) DEFAULT '',
  `precio_secciones` varchar(100) DEFAULT '',
  `enlace_compra` varchar(500) DEFAULT '',
  `portada_evento` varchar(500) DEFAULT '',
  `nombre_contacto` varchar(500) DEFAULT '',
  `telefono` varchar(500) DEFAULT '',
  `correo` varchar(500) DEFAULT '',
  `tipo_creador` varchar(100) DEFAULT 'empresa',
  `latitud` varchar(200) DEFAULT '',
  `longitud` varchar(200) DEFAULT '',
  PRIMARY KEY (`id_evento`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `facturas`
--

DROP TABLE IF EXISTS `facturas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `facturas` (
  `id_factura` int NOT NULL AUTO_INCREMENT,
  `id_factura_contifico` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `id_cliente` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `numero_factura` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `descripcion` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `tipo` varchar(1) DEFAULT '',
  `estado` varchar(1) DEFAULT 'A',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_factura`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `funcionamiento_motor`
--

DROP TABLE IF EXISTS `funcionamiento_motor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `funcionamiento_motor` (
  `id_funcionamiento_motor` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `referencia` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'AUTOS,CAMIONETAS,UTV',
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_funcionamiento_motor`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `galerias_eventos`
--

DROP TABLE IF EXISTS `galerias_eventos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `galerias_eventos` (
  `id_galeria` int NOT NULL AUTO_INCREMENT,
  `imagen` varchar(500) DEFAULT '',
  `id_evento` int DEFAULT NULL,
  `estado` varchar(1) DEFAULT '',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_galeria`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `marcas`
--

DROP TABLE IF EXISTS `marcas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `marcas` (
  `id_marca` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `referencia` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'AUTOS,CAMIONETAS,UTV',
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'A',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_marca`)
) ENGINE=InnoDB AUTO_INCREMENT=321 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `marcas_productos`
--

DROP TABLE IF EXISTS `marcas_productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `marcas_productos` (
  `id_marca_producto` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'A',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_marca_producto`)
) ENGINE=InnoDB AUTO_INCREMENT=1143 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `membresias`
--

DROP TABLE IF EXISTS `membresias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `membresias` (
  `id_membresia` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `tipo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `costo` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `numero` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `dias_permitidos` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_membresia`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `membresias_empresas`
--

DROP TABLE IF EXISTS `membresias_empresas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `membresias_empresas` (
  `id_membresia_empresa` int unsigned NOT NULL AUTO_INCREMENT,
  `id_empresa` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `id_membresia` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `fecha_inicio` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `fecha_fin` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `limite_articulos` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `mes_prueba` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'N',
  `valor_membresia` float DEFAULT '0',
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_membresia_empresa`)
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `modelos_autos`
--

DROP TABLE IF EXISTS `modelos_autos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `modelos_autos` (
  `id_modelos_autos` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `id_marca` int DEFAULT NULL,
  `id_tipo_auto` int DEFAULT NULL,
  `id_funcionamiento_motor` int DEFAULT NULL,
  `id_tipo_traccion` int DEFAULT NULL,
  `referencia` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'BICICLETAS,SCOOTERS',
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_modelos_autos`)
) ENGINE=InnoDB AUTO_INCREMENT=18378 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nombres_productos`
--

DROP TABLE IF EXISTS `nombres_productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nombres_productos` (
  `id_nombre_producto` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `categoria` int DEFAULT NULL,
  `sub_categoria` int DEFAULT NULL,
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_nombre_producto`)
) ENGINE=InnoDB AUTO_INCREMENT=4564 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nombres_servicios`
--

DROP TABLE IF EXISTS `nombres_servicios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nombres_servicios` (
  `id_nombre_servicio` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `categoria` int DEFAULT NULL,
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `referencia` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT '',
  PRIMARY KEY (`id_nombre_servicio`)
) ENGINE=InnoDB AUTO_INCREMENT=394 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ordenes`
--

DROP TABLE IF EXISTS `ordenes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ordenes` (
  `id_orden` int NOT NULL AUTO_INCREMENT,
  `numero_orden` varchar(100) DEFAULT NULL,
  `estado` varchar(1) DEFAULT 'A',
  `subtotal` varchar(100) DEFAULT NULL,
  `iva` varchar(100) DEFAULT NULL,
  `total` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status_orden` varchar(100) DEFAULT 'creado',
  `id_cliente` int DEFAULT '0',
  `envio_domicilio` tinyint(1) DEFAULT '0',
  `nombre_retiro` varchar(300) DEFAULT '',
  `latitud` varchar(200) DEFAULT '',
  `longitud` varchar(300) DEFAULT '',
  `agencia_cercana` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_orden`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ordenes_empresas`
--

DROP TABLE IF EXISTS `ordenes_empresas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ordenes_empresas` (
  `id_orden` int NOT NULL AUTO_INCREMENT,
  `id_empresa` int DEFAULT NULL,
  `id_sucursal` int DEFAULT '0',
  `id_area` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '0',
  `productos` json DEFAULT NULL,
  `orden_estado` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'creada',
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'A',
  `total` float DEFAULT '0',
  `creation_user` int DEFAULT '0',
  `id_ordenes` int DEFAULT NULL,
  `envio_domicilio` tinyint(1) DEFAULT '0',
  `estado_venta` tinyint(1) DEFAULT '0',
  `id_trayecto` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `total_envio` float DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `id_ruta` int DEFAULT '0',
  `peso_total` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `valor_base_trayecto` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `valor_adicional_kg` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `peso_real_total_kg` decimal(10,2) DEFAULT '0.00',
  `largo_cm` decimal(10,2) DEFAULT '0.00',
  `ancho_cm` decimal(10,2) DEFAULT '0.00',
  `alto_cm` decimal(10,2) DEFAULT '0.00',
  `fragil` tinyint(1) NOT NULL DEFAULT '0',
  `valor_producto_usd` decimal(12,2) DEFAULT '0.00',
  `peso_volumetrico_kg` decimal(10,2) DEFAULT '0.00',
  `peso_facturable_kg` decimal(10,2) DEFAULT '0.00',
  `seguro_base_usd` decimal(12,2) DEFAULT '0.00',
  `seguro_iva_usd` decimal(12,2) DEFAULT '0.00',
  `seguro_total_usd` decimal(12,2) DEFAULT '0.00',
  `alerta_mayor_50kg` tinyint(1) NOT NULL DEFAULT '0',
  `divisor_volumetrico` int DEFAULT '0',
  `iva_envio` decimal(5,4) DEFAULT '0.0000',
  `seguro_pct` decimal(7,6) DEFAULT '0.000000',
  PRIMARY KEY (`id_orden`)
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ordenes_iso`
--

DROP TABLE IF EXISTS `ordenes_iso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ordenes_iso` (
  `id_orden_iso` int NOT NULL AUTO_INCREMENT,
  `orden_estado` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'procesada',
  `id_orden_empresa` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `total` float NOT NULL DEFAULT '0',
  `total_peso` float DEFAULT '0',
  `id_trayecto` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `estado_envio` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_orden_iso`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'A',
  `tipo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `tipo_nota` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'E',
  PRIMARY KEY (`id_nota`)
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ordenes_servientrega`
--

DROP TABLE IF EXISTS `ordenes_servientrega`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ordenes_servientrega` (
  `id_orden_servientrega` int NOT NULL AUTO_INCREMENT,
  `id_orden_empresa` int DEFAULT NULL,
  `id_guia` varchar(100) DEFAULT '',
  `estado` varchar(1) DEFAULT 'A',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `url_grupoentrega` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `code` varchar(100) DEFAULT '',
  PRIMARY KEY (`id_orden_servientrega`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pagos_ordenes`
--

DROP TABLE IF EXISTS `pagos_ordenes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pagos_ordenes` (
  `id_pago` int NOT NULL AUTO_INCREMENT,
  `id_orden` int DEFAULT NULL,
  `imagen` varchar(300) DEFAULT NULL,
  `estado` varchar(1) DEFAULT 'A',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pago`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pagos_recurrentes`
--

DROP TABLE IF EXISTS `pagos_recurrentes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pagos_recurrentes` (
  `id_pago_recurrente` int NOT NULL AUTO_INCREMENT,
  `token` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `transaction_reference` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `id_usuario` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `id_empresa` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `estado` varchar(1) DEFAULT 'A',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pago_recurrente`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pagos_transaccion`
--

DROP TABLE IF EXISTS `pagos_transaccion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pagos_transaccion` (
  `id_pagos_transaccion` int NOT NULL AUTO_INCREMENT,
  `id_transaccion` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '',
  `codigo_autorizacion` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '',
  `metodo_pago` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'tarjeta',
  `id_membresia` int NOT NULL,
  `id_membresia_empresa` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT '',
  `pago_valor` double NOT NULL,
  `id_usuario` int DEFAULT NULL,
  `recurrente` varchar(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT 'Y',
  `array_transaction` json DEFAULT NULL,
  `id_empresa` int DEFAULT NULL,
  `tipo` varchar(100) DEFAULT 'empresa',
  `estado` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'A',
  `fecha_transaccion` datetime DEFAULT CURRENT_TIMESTAMP,
  `creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pagos_transaccion`)
) ENGINE=InnoDB AUTO_INCREMENT=1346 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `postulante_trabajo_empresa`
--

DROP TABLE IF EXISTS `postulante_trabajo_empresa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `postulante_trabajo_empresa` (
  `id_postulante` int NOT NULL AUTO_INCREMENT,
  `nombres_apellidos` varchar(200) DEFAULT '',
  `correo` varchar(100) DEFAULT NULL,
  `cedula` varchar(100) DEFAULT NULL,
  `telefono` varchar(100) DEFAULT NULL,
  `id_empleo` int DEFAULT NULL,
  `id_empresa` int DEFAULT NULL,
  `cv` varchar(500) DEFAULT NULL,
  `estado` varchar(1) DEFAULT 'A',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_postulante`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `productos`
--

DROP TABLE IF EXISTS `productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `productos` (
  `id_producto` int NOT NULL AUTO_INCREMENT,
  `titulo_producto` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `marca_producto` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `nombre` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci,
  `codigo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `categoria` json DEFAULT NULL,
  `sub_categoria` json DEFAULT NULL,
  `tags` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `img_frontal` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `img_posterior` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `precio_referencia` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '0',
  `id_empresa` int DEFAULT '0',
  `descuento` float DEFAULT '0',
  `peso` float DEFAULT '0',
  `tipo_creador` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'empresa',
  `detalle_producto` json DEFAULT NULL,
  `iva` tinyint DEFAULT '0',
  `negociable` tinyint DEFAULT '0',
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'A',
  `unidad` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT '',
  `id_marca` json DEFAULT NULL,
  `id_modelo` json DEFAULT NULL,
  `tipo_auto` json DEFAULT NULL,
  `tipo_traccion` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `referencias` json DEFAULT NULL,
  `emergencia_24_7` tinyint DEFAULT '0',
  `emergencia_carretera` tinyint DEFAULT '0',
  `emergencia_domicilio` tinyint DEFAULT '0',
  `tipo_producto` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT 'producto',
  `funcionamiento_motor` json DEFAULT NULL,
  PRIMARY KEY (`id_producto`)
) ENGINE=InnoDB AUTO_INCREMENT=208 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `publicidad`
--

DROP TABLE IF EXISTS `publicidad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `publicidad` (
  `id_publicidad` int NOT NULL AUTO_INCREMENT,
  `imagen` varchar(400) DEFAULT NULL,
  `estado` varchar(1) DEFAULT 'A',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `posicion` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_publicidad`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
-- Table structure for table `ruta_grupo`
--

DROP TABLE IF EXISTS `ruta_grupo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ruta_grupo` (
  `id_ruta` int NOT NULL AUTO_INCREMENT,
  `provincia` varchar(200) DEFAULT '',
  `canton` varchar(200) DEFAULT '',
  `parroquia` varchar(200) DEFAULT '',
  `tipo_cobertura` varchar(200) DEFAULT '',
  `dias_laborables` varchar(200) DEFAULT '',
  `fuera_cobertura` varchar(200) DEFAULT '',
  `valor_kg` varchar(100) DEFAULT '',
  `valor_kg_adicional` varchar(100) DEFAULT '',
  `estado` varchar(1) DEFAULT 'A',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_ruta`)
) ENGINE=InnoDB AUTO_INCREMENT=2233 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rutas`
--

DROP TABLE IF EXISTS `rutas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rutas` (
  `id_ruta` int NOT NULL AUTO_INCREMENT,
  `provincia_origen` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `canton_origen` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `provincia_destino` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `canton_destino` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `trayecto` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `aplica_domicilio` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `zona_peligrosa` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `agencia_1` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `direccion_1` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `agencia_2` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `direccion_2` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `estado` varchar(1) DEFAULT 'A',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_ruta`)
) ENGINE=InnoDB AUTO_INCREMENT=401644 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `servicios`
--

DROP TABLE IF EXISTS `servicios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `servicios` (
  `id_servicio` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci,
  `codigo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `categoria` int DEFAULT NULL,
  `tags` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `img_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `ficha_tecnica` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `precio_referencia` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '0',
  `id_empresa` int DEFAULT '0',
  `detalle_servicio` json DEFAULT NULL,
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_servicio`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sub_categorias`
--

DROP TABLE IF EXISTS `sub_categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sub_categorias` (
  `id_sub_categoria` int unsigned NOT NULL AUTO_INCREMENT,
  `id_categoria` int DEFAULT NULL,
  `nombre` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_sub_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=484 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `subtipo_eventos`
--

DROP TABLE IF EXISTS `subtipo_eventos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subtipo_eventos` (
  `id_subtipo_eventos` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(300) DEFAULT '',
  `id_tipo` int DEFAULT NULL,
  `estado` varchar(1) DEFAULT 'A',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_subtipo_eventos`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sucursales`
--

DROP TABLE IF EXISTS `sucursales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sucursales` (
  `id_sucursal` int unsigned NOT NULL AUTO_INCREMENT,
  `id_empresa` int NOT NULL,
  `nombre` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `direccion` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `numero_local` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'A',
  `img_path` varchar(500) COLLATE utf8mb4_spanish_ci DEFAULT '',
  `latitud` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT '',
  `longitud` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT '',
  `provincia` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT '',
  `ciudad` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `telefono_contacto` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT '',
  `whatsapp_contacto` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT '',
  `canton` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT '',
  `calle_principal` varchar(500) COLLATE utf8mb4_spanish_ci DEFAULT '',
  `calle_secundaria` varchar(500) COLLATE utf8mb4_spanish_ci DEFAULT '',
  `bien_inmueble` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `correo` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `imagen` varchar(200) COLLATE utf8mb4_spanish_ci DEFAULT '',
  PRIMARY KEY (`id_sucursal`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tapiceria`
--

DROP TABLE IF EXISTS `tapiceria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tapiceria` (
  `id_tapiceria` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'A',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_tapiceria`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `terminos_producto_accept`
--

DROP TABLE IF EXISTS `terminos_producto_accept`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `terminos_producto_accept` (
  `id_accept` int NOT NULL AUTO_INCREMENT,
  `ip_addr` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `version_terminos` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `ts_accept` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `user_agent` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `source_page` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `product_id` int DEFAULT NULL,
  PRIMARY KEY (`id_accept`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tipo_traccion`
--

DROP TABLE IF EXISTS `tipo_traccion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_traccion` (
  `id_tipo_traccion` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT '',
  `referencia` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'AUTOS,CAMIONETAS,UTV',
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_tipo_traccion`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tipo_vendedor`
--

DROP TABLE IF EXISTS `tipo_vendedor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_vendedor` (
  `id_tipo_vendedor` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'A',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_tipo_vendedor`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tipos_auto`
--

DROP TABLE IF EXISTS `tipos_auto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipos_auto` (
  `id_tipo_auto` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `referencia` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'AUTOS,CAMIONETAS,UTV',
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'A',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_tipo_auto`)
) ENGINE=InnoDB AUTO_INCREMENT=128 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tipos_eventos`
--

DROP TABLE IF EXISTS `tipos_eventos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipos_eventos` (
  `id_tipo_evento` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) DEFAULT '',
  `estado` varchar(1) DEFAULT 'A',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_tipo_evento`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `transmision`
--

DROP TABLE IF EXISTS `transmision`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transmision` (
  `id_transmision` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'A',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_transmision`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `trayecto`
--

DROP TABLE IF EXISTS `trayecto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trayecto` (
  `id_trayecto` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `valor` float DEFAULT '0',
  `adicional` float DEFAULT '0',
  `tipo` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'A',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_trayecto`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `modo_baja` varchar(400) DEFAULT '',
  `fecha_baja` varchar(100) DEFAULT '',
  PRIMARY KEY (`id_usuario`),
  KEY `rol_id` (`rol_id`),
  CONSTRAINT `rol_id_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `rol` (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=137 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vehiculos`
--

DROP TABLE IF EXISTS `vehiculos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehiculos` (
  `id_vehiculo` int NOT NULL AUTO_INCREMENT,
  `id_modelo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `anio` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `condicion` json DEFAULT NULL,
  `tipo_auto` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `id_marca` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `kilometraje` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `transmision` json DEFAULT NULL,
  `tipo_traccion` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `funcionamiento_motor` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `inicio_placa` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `fin_placa` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `tipo_vendedor` json DEFAULT NULL,
  `provincia` json DEFAULT NULL,
  `canton` json DEFAULT NULL,
  `img_frontal` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `img_posterior` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `color` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `cilindraje` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `tapiceria` json DEFAULT NULL,
  `tipo_dueno` json DEFAULT NULL,
  `direccion` json DEFAULT NULL,
  `climatizacion` json DEFAULT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci,
  `precio_referencia` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '0',
  `id_empresa` int DEFAULT '0',
  `descuento` float DEFAULT '0',
  `tipo_creador` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'empresa',
  `negociable` tinyint DEFAULT '0',
  `estado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'A',
  `tags` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `referencias` json DEFAULT NULL,
  PRIMARY KEY (`id_vehiculo`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `verificacion_empresa`
--

DROP TABLE IF EXISTS `verificacion_empresa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `verificacion_empresa` (
  `id_verificacion` int NOT NULL AUTO_INCREMENT,
  `id_empresa` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `nombre_comercial` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `ruc` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `cedula` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `nombramiento` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `patente` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `planilla` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `verificado` tinyint(1) DEFAULT '0',
  `estado` varchar(1) DEFAULT 'A',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `rechazo_verificacion_empresa` varchar(1) DEFAULT '0',
  `observacion_verificacion` varchar(10000) DEFAULT '',
  PRIMARY KEY (`id_verificacion`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-05 21:48:39
