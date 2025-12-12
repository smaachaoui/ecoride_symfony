-- MySQL dump 10.13  Distrib 8.0.44, for Linux (x86_64)
--
-- Host: localhost    Database: ecoride
-- ------------------------------------------------------
-- Server version	8.0.44-0ubuntu0.24.04.1

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
-- Dumping data for table `avis`
--

LOCK TABLES `avis` WRITE;
/*!40000 ALTER TABLE `avis` DISABLE KEYS */;
/*!40000 ALTER TABLE `avis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `covoiturage`
--

LOCK TABLES `covoiturage` WRITE;
/*!40000 ALTER TABLE `covoiturage` DISABLE KEYS */;
INSERT INTO `covoiturage` VALUES (1,'Marseille','Toulouse','2025-12-12 09:00:00',15.00,4,1,2,1,'2025-12-12 13:30:00'),(2,'Marseille','Aix-En-Provence','2025-12-13 09:00:00',5.00,3,1,2,2,'2025-12-13 09:45:00');
/*!40000 ALTER TABLE `covoiturage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `doctrine_migration_versions`
--

LOCK TABLES `doctrine_migration_versions` WRITE;
/*!40000 ALTER TABLE `doctrine_migration_versions` DISABLE KEYS */;
INSERT INTO `doctrine_migration_versions` VALUES ('DoctrineMigrations\\Version20251209195507','2025-12-09 19:55:35',120),('DoctrineMigrations\\Version20251209202043','2025-12-09 20:20:50',133),('DoctrineMigrations\\Version20251209202602','2025-12-09 20:26:06',146),('DoctrineMigrations\\Version20251209202900','2025-12-09 20:29:05',190),('DoctrineMigrations\\Version20251209205012','2025-12-09 20:50:20',95),('DoctrineMigrations\\Version20251209205325','2025-12-09 20:53:30',111),('DoctrineMigrations\\Version20251209205723','2025-12-09 20:57:30',97),('DoctrineMigrations\\Version20251209205942','2025-12-09 20:59:50',71),('DoctrineMigrations\\Version20251209210340','2025-12-09 21:03:46',175),('DoctrineMigrations\\Version20251210021352','2025-12-10 02:14:15',154),('DoctrineMigrations\\Version20251210030043','2025-12-10 03:01:02',53),('DoctrineMigrations\\Version20251210032133','2025-12-10 03:21:39',20),('DoctrineMigrations\\Version20251210104647','2025-12-10 10:47:00',26),('DoctrineMigrations\\Version20251211092807','2025-12-11 09:28:21',41),('DoctrineMigrations\\Version20251211101852','2025-12-11 10:19:03',59),('DoctrineMigrations\\Version20251211112631','2025-12-11 11:26:41',93),('DoctrineMigrations\\Version20251211114025','2025-12-11 11:40:29',38),('DoctrineMigrations\\Version20251211210638','2025-12-11 21:06:48',20);
/*!40000 ALTER TABLE `doctrine_migration_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `participation`
--

LOCK TABLES `participation` WRITE;
/*!40000 ALTER TABLE `participation` DISABLE KEYS */;
INSERT INTO `participation` VALUES (1,0,5,3,2,'accepte');
/*!40000 ALTER TABLE `participation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `preferences`
--

LOCK TABLES `preferences` WRITE;
/*!40000 ALTER TABLE `preferences` DISABLE KEYS */;
INSERT INTO `preferences` VALUES (1,1,1,2,'J\'adore les chats !');
/*!40000 ALTER TABLE `preferences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `reset_password_request`
--

LOCK TABLES `reset_password_request` WRITE;
/*!40000 ALTER TABLE `reset_password_request` DISABLE KEYS */;
/*!40000 ALTER TABLE `reset_password_request` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `utilisateur`
--

LOCK TABLES `utilisateur` WRITE;
/*!40000 ALTER TABLE `utilisateur` DISABLE KEYS */;
INSERT INTO `utilisateur` VALUES (1,'test','test@ecoride.fr','$2y$13$apIZKRQcK9CgKwocxp/nJuKdvyxBkZiHj4aowGxaDJduKs6WwU6Wa',120,'PASSAGER',0,'2025-12-10 02:16:44','[\"ROLE_USER\"]',0,'6938df0a7c508.jpg'),(2,'Chauffeur','chauffeur@test.fr','$2y$13$1OZLN97XZD6LjNPVfObeEeTVNxlAgI8oENTcmC5aBpNYQZ8BeURjC',43,'CHAUFFEUR',0,'2025-12-10 10:39:30','[\"ROLE_USER\"]',0,'69394e1b8b4af.jpg'),(3,'Passager','passager@test.fr','$2y$13$9zluEfiE4Cq//P/mpYYx0uphnb0dCX52WZxDTkz2T77KswTg5MG2W',120,'PASSAGER',0,'2025-12-11 15:32:09','[\"ROLE_USER\"]',0,'693ae8366e75d.jpg'),(4,'Employe','employe@test.fr','$2y$13$.Y2eBP8XV.gjG352cGVFS.9orFrMmhjJIOwut/y8wiJpfTGxhc1cq',40,'PASSAGER',0,'2025-12-11 15:39:17','[\"EMPLOYE\"]',0,NULL),(5,'Administrateur','admin@test.fr','$2y$13$5VCG3nAmAD3V6lfAOAlY/eH4v71aK5GYbP0WY6vPX47zFl0FiRORy',40,'PASSAGER',0,'2025-12-11 21:24:24','[\"ROLE_ADMIN\"]',0,'693bdbb1b060c.jpg');
/*!40000 ALTER TABLE `utilisateur` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `vehicule`
--

LOCK TABLES `vehicule` WRITE;
/*!40000 ALTER TABLE `vehicule` DISABLE KEYS */;
INSERT INTO `vehicule` VALUES (1,'Model 3','Tesla','AB-123-CD','Electrique','2022-06-15',4,2,'Blanc'),(2,'Yaris Hybride','Toyota','UV-678-WX','Hybride','2021-02-08',5,2,'Blanc');
/*!40000 ALTER TABLE `vehicule` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-12-12 11:20:36
