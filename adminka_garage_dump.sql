-- MySQL dump 10.13  Distrib 9.5.0, for macos15.7 (arm64)
--
-- Host: localhost    Database: adminka_garage
-- ------------------------------------------------------
-- Server version	9.5.0

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
SET @MYSQLDUMP_TEMP_LOG_BIN = @@SESSION.SQL_LOG_BIN;
SET @@SESSION.SQL_LOG_BIN= 0;

--
-- GTID state at the beginning of the backup 
--

SET @@GLOBAL.GTID_PURGED=/*!80000 '+'*/ '9a2b06b4-c671-11f0-bff5-4250c9fea129:1-18012';

--
-- Table structure for table `barcode_results`
--

DROP TABLE IF EXISTS `barcode_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `barcode_results` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `photo_id` bigint unsigned NOT NULL,
  `symbology` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `barcode_results_photo_id_symbology_data_unique` (`photo_id`,`symbology`,`data`),
  CONSTRAINT `barcode_results_photo_id_foreign` FOREIGN KEY (`photo_id`) REFERENCES `photos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `barcode_results`
--

LOCK TABLES `barcode_results` WRITE;
/*!40000 ALTER TABLE `barcode_results` DISABLE KEYS */;
INSERT INTO `barcode_results` VALUES (3,4,'MANUAL-AI','GG112','gg-label','2025-11-23 09:02:43','2025-11-23 20:16:44'),(4,4,'MANUAL-AI','035481116063','manual','2025-11-23 09:02:43','2025-11-23 20:16:44'),(5,22,'UPC-A','035481116063','gemini','2025-11-23 19:35:44','2025-11-23 19:35:44'),(6,22,'CODE128','GG112','gg-label','2025-11-23 19:35:44','2025-11-23 19:35:44'),(7,30,'MANUAL-AI','GG129','gg-label','2025-11-23 22:56:12','2025-11-23 22:56:12'),(8,30,'MANUAL-AI','840241311936','manual','2025-11-23 22:56:12','2025-11-23 22:56:12'),(9,30,'MANUAL-AI','840241311912','manual','2025-11-23 22:56:12','2025-11-23 22:56:12'),(10,36,'MANUAL-AI','GG115','gg-label','2025-11-25 01:59:35','2025-11-25 01:59:35'),(11,36,'MANUAL-AI','190104201447','manual','2025-11-25 01:59:35','2025-11-25 01:59:35'),(12,43,'MANUAL-AI','GG119','gg-label','2025-11-25 02:05:00','2025-11-25 02:05:00'),(13,48,'MANUAL-AI','GG120','gg-label','2025-11-25 02:08:05','2025-11-25 02:08:05'),(14,48,'MANUAL-AI','840241311905','manual','2025-11-25 02:08:05','2025-11-25 02:08:05'),(15,48,'MANUAL-AI','00609461800015','manual','2025-11-25 02:08:05','2025-11-25 02:08:05'),(16,48,'MANUAL-AI','B09F9BJSWV','manual','2025-11-25 02:08:05','2025-11-25 02:08:05'),(17,48,'MANUAL-AI','B00F9BJSWV','gg-label','2025-11-25 02:12:05','2025-11-25 02:12:05'),(18,48,'MANUAL-AI','840241311935','manual','2025-11-25 02:12:05','2025-11-25 02:12:05'),(19,48,'MANUAL-AI','840241311936','manual','2025-11-25 02:12:05','2025-11-25 02:12:05'),(20,55,'MANUAL-AI','GG126','gg-label','2025-11-25 02:15:48','2025-11-25 02:15:48'),(21,55,'MANUAL-AI','190107458269','manual','2025-11-25 02:15:48','2025-11-25 02:15:48');
/*!40000 ALTER TABLE `barcode_results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('laravel-cache-ebay_access_token','s:1944:\"v^1.1#i^1#r^0#p^1#I^3#f^0#t^H4sIAAAAAAAA/+VYfWwTZRhv125mwjAKAQUD5QYiH3e9u37euVa6dguVbR1r6dgSZde79+jB9e68925b8assgcSvGIgRQiQZxoBfMUZDxI+YiOICkgAhSoyJ+IGGoHwqEVTEu66MbhKGrMYm9p/mfd7nfd7n93ue533ee/FcVfW8dYvW/VpjvamiP4fnKqxWYhxeXVU5f4KtYmqlBS9SsPbnZuXsfbZjdZDJiArdBqAiSxA4ejOiBOm8MIDoqkTLDBQgLTEZAGmNpeOh5iaaxHBaUWVNZmURcUQjAcTPkwBQftbPehiexw2hdNlkQg4gnA9QjI/jeB/p9wDWZ8xDqIOoBDVG0gIIiZMelCBQ0p0gcdpD0S4vRlCuTsSRBCoUZMlQwXAkmPeWzq9Vi1y9tqcMhEDVDCNIMBpqjMdC0UhDS6LOWWQrWKAhrjGaDoePwjIHHElG1MG1t4F5bTqusyyAEHEGB3cYbpQOXXbmBtzPM02SPm/Kn+IpnPdSZMpbEiobZTXDaNf2w5QIHMrnVWkgaYKWHY1Rg43USsBqhVGLYSIacZh/S3RGFHgBqAGkoT7UEWptRYKR1QIjCbAZjadlADUA0da2CMp7gZviXG4C5XEvz3t4T2GjQWsFmkfsFJYlTjBJg44WWasHhtdgJDfuIm4MpZgUU0O8ZnpUrEde5tDv7TSDOhhFXUtLZlxBxiDCkR+OHoGh1ZqmCildA0MWRk7kKQogjKIIHDJyMp+LhfTphQEkrWkK7XT29PRgPS5MVlc4SRwnnMuam+JsGmQYxNQ1az2vL4y+ABXyUFhgrIQCrWUVw5deI1cNB6QVSNDj9hJuV4H34W4FR0r/JijC7BxeEaWqEBdBeVnC5fOwHCBZ3lOKCgkWktRp+gFSTBbNMOoqoCkiwwKUNfJMzwBV4GiXhyddfh6gnJfiUTfF82jKw3lRggcAByCVYin//6lQrjfV44BVgVaaXC9VnvvbG9w9zTAbVsjexiVLPVlncrGs4Il4dwNojPFyIuxTYpR7abTJHbjeargq+LAoGMwkjP1LQoBZ6yUjYZFsJBg3JnhxVlZAqywKbLa8AuxSuVZG1bJxIIqGYEwgQ4oSLdFZXSp4//CYuDHcJexR/01/uioqaKZseaEy10PDAKMImNmBMFbOOGWz1hnj+mGKl+e9HhNuwbi5lhVqA+QgWoEbvHJisgkXg90spgIo66px28Zi5g0sIa8CktHPNFUWRaAmiTHXcyaja0xKBOVW2CVIcIEps2ZL+LxunPBRPu+YcLH5Vrq83I6kkhzF9j6rYzT8bYARM+WFXVFlTmfNO+a/8MngHP5+EbTkf0SfdRfeZ/2gwmrF6/DZRC0+s8q21G4bPxUKGsAEhsegsEIyvstVgK0CWYUR1IqJlr2ffdEy/b37Xnr86JTc2lnODZYJRc8n/ffjtw89oFTbiHFFryn4nVdmKolbptSQHoIg3STuoVzeTrz2yqydmGyf9G3HC2vOz93K7zu2aaW6c2OH9QeUwGuGlKzWSosRa0sV+smpE7/ffBclt2/Ztu7QnIUrnzh5Ysf6p77b8dvsadyZ5IHDubYz01u6up+Npg4u2vLuzwv0vW+dfj3Uu2fTgls3dMuB6nMLWXj2YsT28Z/IpXuRpktdjx2/pxbT53S21cy8cDj9Ts+sQ7m5v+yK737ujTUP/Hiw//2H0yeP983v/+bR5JuxqTP++HzT+MUfNZ945qHXrA2JrtqJrlcmpwe+uvQ9/VP9xZdvezJ16kt7R92x7Ln000cGBmZuX23ffaArHNjmX3y+ftm+9ZY54KiIt8s7796/f/fZw75Pp2zedqB94PlT6IfhGUc2zt5+2vb12Uem3zFtT+RId+BV4sG3zx+ctKWz/UJy67wXN689NBjLvwAef0JF2BIAAA==\";',1764021476),('laravel-cache-ebay_barcode_0511951c6d5a017029bc7e12ff63cb8a','a:1:{i:0;a:6:{s:6:\"itemId\";s:17:\"v1|297785118843|0\";s:5:\"title\";s:81:\"UFC Men’s Socks Black Cotton No Show Size 10–13 3 Pair Wicking/ Antimicrobial\";s:5:\"price\";a:2:{s:5:\"value\";s:4:\"9.99\";s:8:\"currency\";s:3:\"USD\";}s:5:\"image\";s:58:\"https://i.ebayimg.com/images/g/X9MAAeSwMuxpInTH/s-l225.jpg\";s:3:\"url\";s:94:\"https://www.ebay.com/itm/297785118843?_skw=840241311912&hash=item455560507b:g:X9MAAeSwMuxpInTH\";s:9:\"condition\";s:13:\"New with tags\";}}',1764079891),('laravel-cache-ebay_barcode_854bf1c74a6a2a3090666b1a25a8ba43','a:1:{i:0;a:6:{s:6:\"itemId\";s:17:\"v1|205599020085|0\";s:5:\"title\";s:62:\"NWT Stance Icon Casual No Show Socks S (W:5-7.5, M:3-5.5) Pink\";s:5:\"price\";a:2:{s:5:\"value\";s:4:\"7.99\";s:8:\"currency\";s:3:\"USD\";}s:5:\"image\";s:58:\"https://i.ebayimg.com/images/g/YagAAeSwCpFoaMrA/s-l225.jpg\";s:3:\"url\";s:434:\"https://www.ebay.com/itm/205599020085?_skw=190107458269&hash=item2fdea81435:g:YagAAeSwCpFoaMrA&amdata=enc%3AAQAKAAAA4PeG5RIuIyokJHJy903%2F5UYujXt7fnP6NMC7t6habumhRwk8lcgTLJE4czl9tD%2BdoI8rAblHmygOZRhNuaOzx%2FKsYMRm4t%2FYgMxyz%2FXk4v1vW0NPkcTzNKb%2Fc%2F0hlDZenx2E21d%2FkzNTuzLDvbYcta8aSO4WAxPlVtEFLRywBHtJmJekCxI2HAnUlISrYetNUJdPXo3ECsxWRdLwjZNuuca464d1T1%2BXd23NTCY2YCpuZpvGAbaGkZ9mbjLP7pkOFbu10QqhTKnlAO2pB8WIo5BuSTiz9GDDh7KAFnM37j5Y\";s:9:\"condition\";s:13:\"New with tags\";}}',1764105354),('laravel-cache-ebay_barcode_9a4d4d0d6abdcf15653b44ed893476e7','a:0:{}',1764104376),('laravel-cache-ebay_barcode_d712ca61333db4cb16553708507bcafd','a:0:{}',1764104935),('laravel-cache-ebay_keyword_2f01df614a003cc48ce513f8d809ae25','a:0:{}',1764104377),('laravel-cache-ebay_keyword_453770b33099b8558a586cf7d024d5b5','a:0:{}',1764104742),('laravel-cache-ebay_keyword_afa4ec6d8e4aeb6af69b7d6f103f9f49','a:20:{i:0;a:6:{s:6:\"itemId\";s:28:\"v1|167643010176|467257719466\";s:5:\"title\";s:67:\"Dan Post Men’s Milwaukee 13” Round Toe Cherry Black Cowboy Boot\";s:5:\"price\";a:2:{s:5:\"value\";s:6:\"199.95\";s:8:\"currency\";s:3:\"USD\";}s:5:\"image\";s:58:\"https://i.ebayimg.com/images/g/320AAeSwWUdoxSSp/s-l225.jpg\";s:3:\"url\";s:928:\"https://www.ebay.com/itm/167643010176?_skw=DAN+POST+Dan+Post+Work+%26+Outdoor+%D0%92%D1%8B%D1%81%D0%BE%D0%BA%D0%BE%D1%8D%D1%84%D1%84%D0%B5%D0%BA%D1%82%D0%B8%D0%B2%D0%BD%D1%8B%D0%B5+%D0%A0%D0%B0%D0%B1%D0%BE%D1%87%D0%B8%D0%B5+%D0%9D%D0%BE%D1%81%D0%BA%D0%B8+Mid-Calf%2C+2+%D0%9F%D0%B0%D1%80%D1%8B%2C+%D0%A0%D0%B0%D0%B7%D0%BC%D0%B5%D1%80+10.5-13&hash=item27084d5480:g:320AAeSwWUdoxSSp&amdata=enc%3AAQAKAAABcPeG5RIuIyokJHJy903%2F5Uagogfvg1EhHkOzGKHrtX9%2BNQbc9ixJU%2FltLvJ2nAKigU7DuOczYZJrBNTpm2DK6yaSBf4PXxMCVzkAC1PgJfV4anWU5wL3kHhvCZ1ObARu8Av50%2FpcUO3w09CzJOkTtDx1lHIdSN%2B3s%2BHbamwQxbQQ2uXkjqhsCX%2BipgXng63xB%2FQEMr%2FQJJtO5Uy%2FZQjP7ZzdRJOxQgYQYrWca4BMMI6QsIu0vIgeoECFzW%2FMlRTjSNfzDHuAvOb0l8cDautPYyBYpOsT28OE%2FJ9elVFqc4aufTkEQxei51K1cDtoz%2FtxLOytrb39SzRN8UeVojGBDKXa3Bt4YgFlzQchhpZVXvD7zolx0UBsrT%2BuPnUW80zEDxGeH4HkwrZbmy4%2F1Q3ZlIxBpBM6lvui%2BYdmcCnb5X5NBzvPjBATNJ2euSc%2F9Ivvx84xfg5LN7BsxzW8ogIn0z8RFGuDzspQxkDO8y4loCN5\";s:9:\"condition\";s:12:\"New with box\";}i:1;a:6:{s:6:\"itemId\";s:28:\"v1|167643024062|467258018625\";s:5:\"title\";s:72:\"Dan Post Men’s Renegade Bay Apache 13” Round Toe Leather Cowboy Boot\";s:5:\"price\";a:2:{s:5:\"value\";s:6:\"199.95\";s:8:\"currency\";s:3:\"USD\";}s:5:\"image\";s:58:\"https://i.ebayimg.com/images/g/sWoAAeSw8PVoxSRu/s-l225.jpg\";s:3:\"url\";s:918:\"https://www.ebay.com/itm/167643024062?_skw=DAN+POST+Dan+Post+Work+%26+Outdoor+%D0%92%D1%8B%D1%81%D0%BE%D0%BA%D0%BE%D1%8D%D1%84%D1%84%D0%B5%D0%BA%D1%82%D0%B8%D0%B2%D0%BD%D1%8B%D0%B5+%D0%A0%D0%B0%D0%B1%D0%BE%D1%87%D0%B8%D0%B5+%D0%9D%D0%BE%D1%81%D0%BA%D0%B8+Mid-Calf%2C+2+%D0%9F%D0%B0%D1%80%D1%8B%2C+%D0%A0%D0%B0%D0%B7%D0%BC%D0%B5%D1%80+10.5-13&hash=item27084d8abe:g:sWoAAeSw8PVoxSRu&amdata=enc%3AAQAKAAABcPeG5RIuIyokJHJy903%2F5UYOIsDJPJXWdj3FNCV0c8WevViHdatHVbND%2Ba8BafOx3drtQ83bTQ6oRm6OTkw00jQB7vD48ThlYFho%2B213aaVLZc%2FNKTcdL3Y7kozdI1vk%2BnqGT4Z7agAlC%2BsqjjpUJlzyNCDTPhD1xAiy9zf7OrGI%2FovjLaRmN72beWvgUp10yVG2KOQgNwt4axxewBthQehpj4yPUMRMZAhEYtxV5g0yVPzyuvhQJAAMGMVvgxtEVPkTaarj5Y%2FdTbpvBhIjiLN67KHCj83rRcYvjGF7KmosG14V8ISkYn%2F1bC5z0zWxIZ7tBAREc4nrFEZBr%2B%2B7Q2ublwYBI3ePkEmRrmwJ5GAcOwmvwCLA55hSYtyPrizhXbROX3BqxHX9w1cbdzXW6TnNHBrAjoj2l1McuSJRrfMMCMAwt7KmQmP2Opccju1jl308qVODyFtiKrAy0dfpaYr1d2gkZXf%2BFq1TjyzoJFx3\";s:9:\"condition\";s:12:\"New with box\";}i:2;a:6:{s:6:\"itemId\";s:28:\"v1|204116036490|504652315925\";s:5:\"title\";s:62:\"DAN POST STANLEY 12\" TAN COWBOY BOOTS DP4903 * ALL SIZES - NEW\";s:5:\"price\";a:2:{s:5:\"value\";s:6:\"319.95\";s:8:\"currency\";s:3:\"USD\";}s:5:\"image\";s:58:\"https://i.ebayimg.com/images/g/1s0AAOSwuhNjQ~DH/s-l225.jpg\";s:3:\"url\";s:910:\"https://www.ebay.com/itm/204116036490?_skw=DAN+POST+Dan+Post+Work+%26+Outdoor+%D0%92%D1%8B%D1%81%D0%BE%D0%BA%D0%BE%D1%8D%D1%84%D1%84%D0%B5%D0%BA%D1%82%D0%B8%D0%B2%D0%BD%D1%8B%D0%B5+%D0%A0%D0%B0%D0%B1%D0%BE%D1%87%D0%B8%D0%B5+%D0%9D%D0%BE%D1%81%D0%BA%D0%B8+Mid-Calf%2C+2+%D0%9F%D0%B0%D1%80%D1%8B%2C+%D0%A0%D0%B0%D0%B7%D0%BC%D0%B5%D1%80+10.5-13&hash=item2f86438b8a:g:1s0AAOSwuhNjQ~DH&amdata=enc%3AAQAKAAABYPeG5RIuIyokJHJy903%2F5UaOWIMUGeV0xZ3E9lOe4thlr3w2Q3OuAzkQrwk8s1im1Q4QmMSFpBmZ8D%2BGurvCTCVC5OtRQyyIAmiql5bgdaENQFGzLO6gElXULBdvlLYaQ5wGe5%2FGIUNoFrfEYwUWjrm5Eoir0KNrz5iP3FPjbMgRs19%2BSctopwMe%2FvUpvkvHCbfuXOMH8%2F8zVOMFwzFx%2BQ6KrGWNd%2BciuE9zavqR1TF3Xf8fmg%2FVtAUt6zigW62yDeLbB68TungmuCGEZ6UR5saY%2F8%2BArUdt8eZIXJqq8GsIj0FJ9a80TsIqgSXtlbB5bw7mNs%2BbXbpl%2BycGPR622grsmBNl9L4I24eJ8gcbYvJuMw%2Fx7Qf59EBOFUD3sdqO5AiqEDbkMNrEiJFPym7%2FUFZzcOjggTnzjDphec5Xl8EfKARGPAZHZfI%2Bfy0fVvgyxq3GCqpGPIpc%2FNvT04JnWoY%3D\";s:9:\"condition\";s:12:\"New with box\";}i:3;a:6:{s:6:\"itemId\";s:17:\"v1|366005214992|0\";s:5:\"title\";s:79:\"Dan Post Men\'s Storms Eye Waterproof Western Boot Composite Toe DP59414 Sz  13M\";s:5:\"price\";a:2:{s:5:\"value\";s:6:\"129.89\";s:8:\"currency\";s:3:\"USD\";}s:5:\"image\";s:58:\"https://i.ebayimg.com/images/g/sDoAAeSwrxppH0aN/s-l225.jpg\";s:3:\"url\";s:920:\"https://www.ebay.com/itm/366005214992?_skw=DAN+POST+Dan+Post+Work+%26+Outdoor+%D0%92%D1%8B%D1%81%D0%BE%D0%BA%D0%BE%D1%8D%D1%84%D1%84%D0%B5%D0%BA%D1%82%D0%B8%D0%B2%D0%BD%D1%8B%D0%B5+%D0%A0%D0%B0%D0%B1%D0%BE%D1%87%D0%B8%D0%B5+%D0%9D%D0%BE%D1%81%D0%BA%D0%B8+Mid-Calf%2C+2+%D0%9F%D0%B0%D1%80%D1%8B%2C+%D0%A0%D0%B0%D0%B7%D0%BC%D0%B5%D1%80+10.5-13&hash=item55379c5f10:g:sDoAAeSwrxppH0aN&amdata=enc%3AAQAKAAABcPeG5RIuIyokJHJy903%2F5UZ4ymvfrrNJk6RiZ0obY7roVJNTP2B0nA8n2XvXRm538F2Zec5lRSvF96j2%2BazcMUhfQppHAymkpBOoh4VIIHfxLpOS2OfMGsMTzmveyCDoQ2SXuGY2yHUiXyV0WahcWyPNm%2FXWuF%2FvuXKD4af0pz21sym2QGM6zXdkRxSe33sxz%2BwL14k%2FROiVpFHVqjzBoOQUeFyWpWQ1UlSlK7O0MrMMGMfm38BvCky8Xz1DLZ--RznpGRAuOPmeqTpUdX%2B%2B%2BleWGAtD74IHvnghABA3JRgLcFDbSRTQoX97H1Z%2BAt57%2B81NjpzTedoy15ULgtAnpX59rHhL%2FjkJpne0EonOZPaxdSQbzOjbZSnwjygMSF64rl1IjaKi6HsMuHWTmeRqIlAigPZi9BkuDUDFRbKPLQSXeRvzhOz3E9LRaFBi8%2BBfYWXEDgiH60aMzxdjeDvXaWT5Ddwv7my5K76GO8Vzddqc\";s:9:\"condition\";s:12:\"New with box\";}i:4;a:6:{s:6:\"itemId\";s:17:\"v1|146975087565|0\";s:5:\"title\";s:25:\"Dan Post Renegade S 10.5D\";s:5:\"price\";a:2:{s:5:\"value\";s:5:\"90.00\";s:8:\"currency\";s:3:\"USD\";}s:5:\"image\";s:58:\"https://i.ebayimg.com/images/g/cBIAAeSwe7ZpHkKy/s-l225.jpg\";s:3:\"url\";s:380:\"https://www.ebay.com/itm/146975087565?_skw=DAN+POST+Dan+Post+Work+%26+Outdoor+%D0%92%D1%8B%D1%81%D0%BE%D0%BA%D0%BE%D1%8D%D1%84%D1%84%D0%B5%D0%BA%D1%82%D0%B8%D0%B2%D0%BD%D1%8B%D0%B5+%D0%A0%D0%B0%D0%B1%D0%BE%D1%87%D0%B8%D0%B5+%D0%9D%D0%BE%D1%81%D0%BA%D0%B8+Mid-Calf%2C+2+%D0%9F%D0%B0%D1%80%D1%8B%2C+%D0%A0%D0%B0%D0%B7%D0%BC%D0%B5%D1%80+10.5-13&hash=item223865dbcd:g:cBIAAeSwe7ZpHkKy\";s:9:\"condition\";s:16:\"Pre-owned - Fair\";}i:5;a:6:{s:6:\"itemId\";s:17:\"v1|317507598957|0\";s:5:\"title\";s:15:\"Dan Post Boots \";s:5:\"price\";a:2:{s:5:\"value\";s:5:\"50.00\";s:8:\"currency\";s:3:\"USD\";}s:5:\"image\";s:58:\"https://i.ebayimg.com/images/g/eJ8AAeSwZklpC-aE/s-l225.jpg\";s:3:\"url\";s:380:\"https://www.ebay.com/itm/317507598957?_skw=DAN+POST+Dan+Post+Work+%26+Outdoor+%D0%92%D1%8B%D1%81%D0%BE%D0%BA%D0%BE%D1%8D%D1%84%D1%84%D0%B5%D0%BA%D1%82%D0%B8%D0%B2%D0%BD%D1%8B%D0%B5+%D0%A0%D0%B0%D0%B1%D0%BE%D1%87%D0%B8%D0%B5+%D0%9D%D0%BE%D1%81%D0%BA%D0%B8+Mid-Calf%2C+2+%D0%9F%D0%B0%D1%80%D1%8B%2C+%D0%A0%D0%B0%D0%B7%D0%BC%D0%B5%D1%80+10.5-13&hash=item49eced7a6d:g:eJ8AAeSwZklpC-aE\";s:9:\"condition\";s:16:\"Pre-owned - Fair\";}i:6;a:6:{s:6:\"itemId\";s:17:\"v1|297446883849|0\";s:5:\"title\";s:15:\"DAN POST MAKARA\";s:5:\"price\";a:2:{s:5:\"value\";s:6:\"480.00\";s:8:\"currency\";s:3:\"USD\";}s:5:\"image\";s:58:\"https://i.ebayimg.com/images/g/TS4AAeSwbANoZTko/s-l225.jpg\";s:3:\"url\";s:380:\"https://www.ebay.com/itm/297446883849?_skw=DAN+POST+Dan+Post+Work+%26+Outdoor+%D0%92%D1%8B%D1%81%D0%BE%D0%BA%D0%BE%D1%8D%D1%84%D1%84%D0%B5%D0%BA%D1%82%D0%B8%D0%B2%D0%BD%D1%8B%D0%B5+%D0%A0%D0%B0%D0%B1%D0%BE%D1%87%D0%B8%D0%B5+%D0%9D%D0%BE%D1%81%D0%BA%D0%B8+Mid-Calf%2C+2+%D0%9F%D0%B0%D1%80%D1%8B%2C+%D0%A0%D0%B0%D0%B7%D0%BC%D0%B5%D1%80+10.5-13&hash=item4541374209:g:TS4AAeSwbANoZTko\";s:9:\"condition\";s:15:\"New without box\";}i:7;a:6:{s:6:\"itemId\";s:28:\"v1|153629820816|453651842093\";s:5:\"title\";s:58:\"Dan Post Men\'s Albuquerque Waterproof Leather Boot DP69681\";s:5:\"price\";a:2:{s:5:\"value\";s:6:\"184.95\";s:8:\"currency\";s:3:\"USD\";}s:5:\"image\";s:58:\"https://i.ebayimg.com/images/g/wVMAAOSwtkNdbt7L/s-l225.jpg\";s:3:\"url\";s:380:\"https://www.ebay.com/itm/153629820816?_skw=DAN+POST+Dan+Post+Work+%26+Outdoor+%D0%92%D1%8B%D1%81%D0%BE%D0%BA%D0%BE%D1%8D%D1%84%D1%84%D0%B5%D0%BA%D1%82%D0%B8%D0%B2%D0%BD%D1%8B%D0%B5+%D0%A0%D0%B0%D0%B1%D0%BE%D1%87%D0%B8%D0%B5+%D0%9D%D0%BE%D1%81%D0%BA%D0%B8+Mid-Calf%2C+2+%D0%9F%D0%B0%D1%80%D1%8B%2C+%D0%A0%D0%B0%D0%B7%D0%BC%D0%B5%D1%80+10.5-13&hash=item23c50d0790:g:wVMAAOSwtkNdbt7L\";s:9:\"condition\";s:12:\"New with box\";}i:8;a:6:{s:6:\"itemId\";s:17:\"v1|297203857085|0\";s:5:\"title\";s:15:\"DAN POST MAKARA\";s:5:\"price\";a:2:{s:5:\"value\";s:6:\"480.00\";s:8:\"currency\";s:3:\"USD\";}s:5:\"image\";s:58:\"https://i.ebayimg.com/images/g/PxcAAeSwFxBn-IAt/s-l225.jpg\";s:3:\"url\";s:380:\"https://www.ebay.com/itm/297203857085?_skw=DAN+POST+Dan+Post+Work+%26+Outdoor+%D0%92%D1%8B%D1%81%D0%BE%D0%BA%D0%BE%D1%8D%D1%84%D1%84%D0%B5%D0%BA%D1%82%D0%B8%D0%B2%D0%BD%D1%8B%D0%B5+%D0%A0%D0%B0%D0%B1%D0%BE%D1%87%D0%B8%D0%B5+%D0%9D%D0%BE%D1%81%D0%BA%D0%B8+Mid-Calf%2C+2+%D0%9F%D0%B0%D1%80%D1%8B%2C+%D0%A0%D0%B0%D0%B7%D0%BC%D0%B5%D1%80+10.5-13&hash=item4532baf6bd:g:PxcAAeSwFxBn-IAt\";s:9:\"condition\";s:15:\"New without box\";}i:9;a:6:{s:6:\"itemId\";s:28:\"v1|365437418495|635235351060\";s:5:\"title\";s:74:\"Dan Post Men\'s Mauney The Dirt Show Square Toe Pull On Casual Boots, Brown\";s:5:\"price\";a:2:{s:5:\"value\";s:6:\"299.95\";s:8:\"currency\";s:3:\"USD\";}s:5:\"image\";s:58:\"https://i.ebayimg.com/images/g/-3UAAeSwA7JnxhwD/s-l225.jpg\";s:3:\"url\";s:380:\"https://www.ebay.com/itm/365437418495?_skw=DAN+POST+Dan+Post+Work+%26+Outdoor+%D0%92%D1%8B%D1%81%D0%BE%D0%BA%D0%BE%D1%8D%D1%84%D1%84%D0%B5%D0%BA%D1%82%D0%B8%D0%B2%D0%BD%D1%8B%D0%B5+%D0%A0%D0%B0%D0%B1%D0%BE%D1%87%D0%B8%D0%B5+%D0%9D%D0%BE%D1%81%D0%BA%D0%B8+Mid-Calf%2C+2+%D0%9F%D0%B0%D1%80%D1%8B%2C+%D0%A0%D0%B0%D0%B7%D0%BC%D0%B5%D1%80+10.5-13&hash=item5515c47bff:g:-3UAAeSwA7JnxhwD\";s:9:\"condition\";s:12:\"New with box\";}i:10;a:6:{s:6:\"itemId\";s:17:\"v1|296329805938|0\";s:5:\"title\";s:15:\"Dan Post Boots \";s:5:\"price\";a:2:{s:5:\"value\";s:6:\"500.00\";s:8:\"currency\";s:3:\"USD\";}s:5:\"image\";s:58:\"https://i.ebayimg.com/images/g/-cEAAeSwncJn-rHU/s-l225.jpg\";s:3:\"url\";s:380:\"https://www.ebay.com/itm/296329805938?_skw=DAN+POST+Dan+Post+Work+%26+Outdoor+%D0%92%D1%8B%D1%81%D0%BE%D0%BA%D0%BE%D1%8D%D1%84%D1%84%D0%B5%D0%BA%D1%82%D0%B8%D0%B2%D0%BD%D1%8B%D0%B5+%D0%A0%D0%B0%D0%B1%D0%BE%D1%87%D0%B8%D0%B5+%D0%9D%D0%BE%D1%81%D0%BA%D0%B8+Mid-Calf%2C+2+%D0%9F%D0%B0%D1%80%D1%8B%2C+%D0%A0%D0%B0%D0%B7%D0%BC%D0%B5%D1%80+10.5-13&hash=item44fea20072:g:-cEAAeSwncJn-rHU\";s:9:\"condition\";s:12:\"New with box\";}i:11;a:6:{s:6:\"itemId\";s:17:\"v1|296600157963|0\";s:5:\"title\";s:14:\"Dan post Boots\";s:5:\"price\";a:2:{s:5:\"value\";s:6:\"450.00\";s:8:\"currency\";s:3:\"USD\";}s:5:\"image\";s:58:\"https://i.ebayimg.com/images/g/bSsAAOSwJrpmqGKu/s-l225.jpg\";s:3:\"url\";s:380:\"https://www.ebay.com/itm/296600157963?_skw=DAN+POST+Dan+Post+Work+%26+Outdoor+%D0%92%D1%8B%D1%81%D0%BE%D0%BA%D0%BE%D1%8D%D1%84%D1%84%D0%B5%D0%BA%D1%82%D0%B8%D0%B2%D0%BD%D1%8B%D0%B5+%D0%A0%D0%B0%D0%B1%D0%BE%D1%87%D0%B8%D0%B5+%D0%9D%D0%BE%D1%81%D0%BA%D0%B8+Mid-Calf%2C+2+%D0%9F%D0%B0%D1%80%D1%8B%2C+%D0%A0%D0%B0%D0%B7%D0%BC%D0%B5%D1%80+10.5-13&hash=item450ebf3f0b:g:bSsAAOSwJrpmqGKu\";s:9:\"condition\";s:15:\"New without box\";}i:12;a:6:{s:6:\"itemId\";s:17:\"v1|297446888809|0\";s:5:\"title\";s:15:\"DAN POST MAKARA\";s:5:\"price\";a:2:{s:5:\"value\";s:6:\"480.00\";s:8:\"currency\";s:3:\"USD\";}s:5:\"image\";s:58:\"https://i.ebayimg.com/images/g/Z68AAeSwGlRoZTmf/s-l225.jpg\";s:3:\"url\";s:380:\"https://www.ebay.com/itm/297446888809?_skw=DAN+POST+Dan+Post+Work+%26+Outdoor+%D0%92%D1%8B%D1%81%D0%BE%D0%BA%D0%BE%D1%8D%D1%84%D1%84%D0%B5%D0%BA%D1%82%D0%B8%D0%B2%D0%BD%D1%8B%D0%B5+%D0%A0%D0%B0%D0%B1%D0%BE%D1%87%D0%B8%D0%B5+%D0%9D%D0%BE%D1%81%D0%BA%D0%B8+Mid-Calf%2C+2+%D0%9F%D0%B0%D1%80%D1%8B%2C+%D0%A0%D0%B0%D0%B7%D0%BC%D0%B5%D1%80+10.5-13&hash=item4541375569:g:Z68AAeSwGlRoZTmf\";s:9:\"condition\";s:15:\"New without box\";}i:13;a:6:{s:6:\"itemId\";s:28:\"v1|167643031768|467257866628\";s:5:\"title\";s:54:\"Dan Post Cummins Waterproof Tan Round Toe Leather Boot\";s:5:\"price\";a:2:{s:5:\"value\";s:6:\"189.95\";s:8:\"currency\";s:3:\"USD\";}s:5:\"image\";s:58:\"https://i.ebayimg.com/images/g/rKQAAeSwhK9ocpRm/s-l225.jpg\";s:3:\"url\";s:910:\"https://www.ebay.com/itm/167643031768?_skw=DAN+POST+Dan+Post+Work+%26+Outdoor+%D0%92%D1%8B%D1%81%D0%BE%D0%BA%D0%BE%D1%8D%D1%84%D1%84%D0%B5%D0%BA%D1%82%D0%B8%D0%B2%D0%BD%D1%8B%D0%B5+%D0%A0%D0%B0%D0%B1%D0%BE%D1%87%D0%B8%D0%B5+%D0%9D%D0%BE%D1%81%D0%BA%D0%B8+Mid-Calf%2C+2+%D0%9F%D0%B0%D1%80%D1%8B%2C+%D0%A0%D0%B0%D0%B7%D0%BC%D0%B5%D1%80+10.5-13&hash=item27084da8d8:g:rKQAAeSwhK9ocpRm&amdata=enc%3AAQAKAAABcPeG5RIuIyokJHJy903%2F5UYr7GvZo5vbDyVZZRVpD%2FaT7f4rHJraG1uWQRhqXNvy7NP%2BZTcq6VHBGnfgJm6l0X1le2m%2FRzKvcfU9zePhuaJqHKOQS8q2hNAc0Ek6jCETjfYGf0ihyj0kouP5rgeivtnT7inyKVsf0EIwKimGHqQCHT6T0yHQAjs3t3LA1nGdfdT9xBi8JAYHwG5YOhPDmA842sMp9QLJxTsGLRBW32aJXiIOUBY1Cr9iKMWt14rWLfT4P35VyHDDXxAVVVJgmZpsWvixhlFbBx7NfFniL2EuIXgWjdgWX424yTk1F6PrsBgN4iNHeGsvOLRXzlHv6NUyanW2pM6gd7ui%2FxQ0VcYMjjMBzkY4Q3w2fuwYaxD%2FQgzy7hxZJ1%2Br35m0ZfO2T%2BmktMtNiaRyPSyQ7SLewzQazSRgkAZAXYYlu3CsrJWArqmIc0MffBYhlcQ9DiJJTBwbIIBCNGqfhYcotZTPclgQ\";s:9:\"condition\";s:12:\"New with box\";}i:14;a:6:{s:6:\"itemId\";s:17:\"v1|316123885247|0\";s:5:\"title\";s:75:\"Dan Post Men\'s 13m Waterproof Leather Work Boot DP59456 W/COMPTOE. NEW.  da\";s:5:\"price\";a:2:{s:5:\"value\";s:5:\"97.00\";s:8:\"currency\";s:3:\"USD\";}s:5:\"image\";s:58:\"https://i.ebayimg.com/images/g/L2MAAOSwLiVngW3z/s-l225.jpg\";s:3:\"url\";s:908:\"https://www.ebay.com/itm/316123885247?_skw=DAN+POST+Dan+Post+Work+%26+Outdoor+%D0%92%D1%8B%D1%81%D0%BE%D0%BA%D0%BE%D1%8D%D1%84%D1%84%D0%B5%D0%BA%D1%82%D0%B8%D0%B2%D0%BD%D1%8B%D0%B5+%D0%A0%D0%B0%D0%B1%D0%BE%D1%87%D0%B8%D0%B5+%D0%9D%D0%BE%D1%81%D0%BA%D0%B8+Mid-Calf%2C+2+%D0%9F%D0%B0%D1%80%D1%8B%2C+%D0%A0%D0%B0%D0%B7%D0%BC%D0%B5%D1%80+10.5-13&hash=item499a73aebf:g:L2MAAOSwLiVngW3z&amdata=enc%3AAQAKAAABYPeG5RIuIyokJHJy903%2F5UYuPf%2BFlsB772flVF7iXbtuTDU5JNDoVlxsdwgikL45b%2FiyMFINjgW2fAf6KwJjUd5jKooIyIRnp3d9Q%2BR4cChdvro0PLiF7ufm5Eaf11ihqlGKilZA0In%2FdCpwTNy0NcB8vq87MTueJEs9SS%2FlFIRB%2BLHB%2FIEwAd7rYy3vhMeJtYQuvBFIxrO%2FXHjSVarL7Z8NWYXXNyP6vwr8XoR8J7RhaX6ZqJhchqihn%2FRF1czC0kU4yEgdtjMqbDyZlodN7xzHoPlTS0%2FNUFCzfEbSDQcABzwGA%2Fit%2BCH0AHabNuJ4mvQtE3Mb%2FLRbH9y36hOjgFR%2B0lPiJqcg0hdkUkg9Woqs6uE8JUMiFBaYGNPZX3JDBs5m4UPIqHONbh6YpnQhc%2BvY5wRwvKli7b4d39RirCy7unZOF5508Y7TZY8M6ARO3ok6SmyvkSjdQZXczPnfHWA%3D\";s:9:\"condition\";s:15:\"New without box\";}i:15;a:6:{s:6:\"itemId\";s:17:\"v1|146968424362|0\";s:5:\"title\";s:62:\"Dan Post 6154 Leather Boots Size 13 Men\'s Brown Good Condition\";s:5:\"price\";a:2:{s:5:\"value\";s:6:\"230.00\";s:8:\"currency\";s:3:\"USD\";}s:5:\"image\";s:58:\"https://i.ebayimg.com/images/g/ZQQAAeSw9n5pGmDd/s-l225.jpg\";s:3:\"url\";s:914:\"https://www.ebay.com/itm/146968424362?_skw=DAN+POST+Dan+Post+Work+%26+Outdoor+%D0%92%D1%8B%D1%81%D0%BE%D0%BA%D0%BE%D1%8D%D1%84%D1%84%D0%B5%D0%BA%D1%82%D0%B8%D0%B2%D0%BD%D1%8B%D0%B5+%D0%A0%D0%B0%D0%B1%D0%BE%D1%87%D0%B8%D0%B5+%D0%9D%D0%BE%D1%81%D0%BA%D0%B8+Mid-Calf%2C+2+%D0%9F%D0%B0%D1%80%D1%8B%2C+%D0%A0%D0%B0%D0%B7%D0%BC%D0%B5%D1%80+10.5-13&hash=item2238002faa:g:ZQQAAeSw9n5pGmDd&amdata=enc%3AAQAKAAABYPeG5RIuIyokJHJy903%2F5UbeRnvuUA3Q3MFkTRM8rV3xMlh%2BBPQ7leAPQ5r51SQ45uqzYnGYCrN%2BL1Og1ZegO8xpm38Z%2FFqe7NZO3w76eJ%2BCx7c2mJ4zuClzDXP6oPlTYeGxLAwRezM92sXstmnfOnXz3G%2BM9RPkvWMQHlxW87x2WUeZxuNKX7odRR%2B9FqPrniGJLxK%2BonK%2FWNNGGMjgxXVu4PX9t7VGE8b0KIxy7Bi9zvM2rdUm1sQL2lViSI%2BLy%2BVCc77XCYhLHBftEkBzyfCRcu0ITfwhXFvYCtN8MvlngHY4BY8hUBgSzMQ1%2BGyeeEqHDXtFZuThfdKbtG47CgBLbAnQLdikAoZBviBomy8Hrfs384cAlSLhUUCeOE%2Beyi5K70Ifpryk09LzabAak%2Bsgb9n%2BakQ2miFXAWw%2FcGIqz0iy95%2B8uupB%2FLfbVyt75P4c7GJNqN8PcgLu0k%2BtmIk%3D\";s:9:\"condition\";s:16:\"Pre-owned - Good\";}i:16;a:6:{s:6:\"itemId\";s:17:\"v1|322900445167|0\";s:5:\"title\";s:38:\"New Dan Post #16811 7 D black (483B)  \";s:5:\"price\";a:2:{s:5:\"value\";s:5:\"89.99\";s:8:\"currency\";s:3:\"USD\";}s:5:\"image\";s:58:\"https://i.ebayimg.com/images/g/nekAAOSw89dg5iXT/s-l225.jpg\";s:3:\"url\";s:380:\"https://www.ebay.com/itm/322900445167?_skw=DAN+POST+Dan+Post+Work+%26+Outdoor+%D0%92%D1%8B%D1%81%D0%BE%D0%BA%D0%BE%D1%8D%D1%84%D1%84%D0%B5%D0%BA%D1%82%D0%B8%D0%B2%D0%BD%D1%8B%D0%B5+%D0%A0%D0%B0%D0%B1%D0%BE%D1%87%D0%B8%D0%B5+%D0%9D%D0%BE%D1%81%D0%BA%D0%B8+Mid-Calf%2C+2+%D0%9F%D0%B0%D1%80%D1%8B%2C+%D0%A0%D0%B0%D0%B7%D0%BC%D0%B5%D1%80+10.5-13&hash=item4b2e5dc7ef:g:nekAAOSw89dg5iXT\";s:9:\"condition\";s:15:\"New without box\";}i:17;a:6:{s:6:\"itemId\";s:17:\"v1|167420746263|0\";s:5:\"title\";s:22:\"Dan Post Style DP67621\";s:5:\"price\";a:2:{s:5:\"value\";s:5:\"90.00\";s:8:\"currency\";s:3:\"USD\";}s:5:\"image\";s:58:\"https://i.ebayimg.com/images/g/LV8AAOSwTsln7ChJ/s-l225.jpg\";s:3:\"url\";s:380:\"https://www.ebay.com/itm/167420746263?_skw=DAN+POST+Dan+Post+Work+%26+Outdoor+%D0%92%D1%8B%D1%81%D0%BE%D0%BA%D0%BE%D1%8D%D1%84%D1%84%D0%B5%D0%BA%D1%82%D0%B8%D0%B2%D0%BD%D1%8B%D0%B5+%D0%A0%D0%B0%D0%B1%D0%BE%D1%87%D0%B8%D0%B5+%D0%9D%D0%BE%D1%81%D0%BA%D0%B8+Mid-Calf%2C+2+%D0%9F%D0%B0%D1%80%D1%8B%2C+%D0%A0%D0%B0%D0%B7%D0%BC%D0%B5%D1%80+10.5-13&hash=item26fb0dda17:g:LV8AAOSwTsln7ChJ\";s:9:\"condition\";s:12:\"New with box\";}i:18;a:6:{s:6:\"itemId\";s:28:\"v1|195405558251|495230376707\";s:5:\"title\";s:62:\"DAN POST RENEGADE CS 13\" COWBOY BOOTS DP2163 - ALL SIZES - NEW\";s:5:\"price\";a:2:{s:5:\"value\";s:6:\"209.95\";s:8:\"currency\";s:3:\"USD\";}s:5:\"image\";s:58:\"https://i.ebayimg.com/images/g/sscAAOSw3ehjQ99m/s-l225.jpg\";s:3:\"url\";s:912:\"https://www.ebay.com/itm/195405558251?_skw=DAN+POST+Dan+Post+Work+%26+Outdoor+%D0%92%D1%8B%D1%81%D0%BE%D0%BA%D0%BE%D1%8D%D1%84%D1%84%D0%B5%D0%BA%D1%82%D0%B8%D0%B2%D0%BD%D1%8B%D0%B5+%D0%A0%D0%B0%D0%B1%D0%BE%D1%87%D0%B8%D0%B5+%D0%9D%D0%BE%D1%81%D0%BA%D0%B8+Mid-Calf%2C+2+%D0%9F%D0%B0%D1%80%D1%8B%2C+%D0%A0%D0%B0%D0%B7%D0%BC%D0%B5%D1%80+10.5-13&hash=item2d7f1431eb:g:sscAAOSw3ehjQ99m&amdata=enc%3AAQAKAAABYPeG5RIuIyokJHJy903%2F5UaP03rIYU7BxHIVkLTeCY2eooJ12giOuPzbitI8rXCZi4LwSY8gfWrtMMAKVE%2FuV%2FHMxi3Bq166e35y%2Ft7%2FVawOGChdLp6mQDtnpPsrNeN5xJ74q2r9MTCCqzZocrw6Beolc3vzh9VbgW5oY4BqU33VHwPSX%2FFC3W%2BQ4FmdCdwnr2qkcOEnxxUdnJwObc3B9BMFdhrAb9a65Lutx4qRmJ9rIXwZj9%2FbZoK69APz8DKDT8sso7mw30Cu6FbvCveZFYJ%2F7nOZsXai4%2Fm8X9L1B08LD8XNmxou0ZAAMrBcjr%2BE%2Fc91fgqIjvkJXgLimc8%2BAt5MlawXX7ygF8zhySysMd7H%2Ft1F5CAElHFZtB3p6m%2FfyH2h59cru8%2BNH95z0tTRwsttaU0RBpbG9x3moDJKHXrI3mb14R0eHmola7m%2FF%2BdHPWA8r1BxvjhRtI4APCJasG4%3D\";s:9:\"condition\";s:12:\"New with box\";}i:19;a:6:{s:6:\"itemId\";s:17:\"v1|397130001286|0\";s:5:\"title\";s:68:\"Dan Post Work Certified Boot Waterproof And Oil Resistant New In Box\";s:5:\"price\";a:2:{s:5:\"value\";s:5:\"90.44\";s:8:\"currency\";s:3:\"USD\";}s:5:\"image\";s:58:\"https://i.ebayimg.com/images/g/oGgAAeSwHp9oXDZC/s-l225.jpg\";s:3:\"url\";s:896:\"https://www.ebay.com/itm/397130001286?_skw=DAN+POST+Dan+Post+Work+%26+Outdoor+%D0%92%D1%8B%D1%81%D0%BE%D0%BA%D0%BE%D1%8D%D1%84%D1%84%D0%B5%D0%BA%D1%82%D0%B8%D0%B2%D0%BD%D1%8B%D0%B5+%D0%A0%D0%B0%D0%B1%D0%BE%D1%87%D0%B8%D0%B5+%D0%9D%D0%BE%D1%81%D0%BA%D0%B8+Mid-Calf%2C+2+%D0%9F%D0%B0%D1%80%D1%8B%2C+%D0%A0%D0%B0%D0%B7%D0%BC%D0%B5%D1%80+10.5-13&hash=item5c76caeb86:g:oGgAAeSwHp9oXDZC&amdata=enc%3AAQAKAAABYPeG5RIuIyokJHJy903%2F5Ub7uqLuCkMmTXgpl08NVe6Z801orO%2FEU0IazR2ZO4Uw2IUDbcGqq%2FnvWey2Eb6OY2ZlJlRkyrVNmofdmyyGfk4Rk1pCM2BnTP2FojaEjpeyv0kRoONChSfuq%2FicjlJV0B%2FarYdW3mMrMOrRpTsVwVzQn0YeT0lstM9fWSSoZccioLa%2FMQKge4XiNPoSxkYz%2FbXiHcYQh9U0vML9vrxqlzSh4XhiM9emoSgX0tX2FTng33wk7QvZzgyH7A1VrGallFeDHGtNdlt6EyGesUF0hyY4KykUmwy2J3N7eNujEyD0y6yEIgBVlFQtbj3JUvAcH2E1IW6TPS7pn89e%2Bcp%2BlTFkPrzbquoVX79uNFFM79ZCAogBwSR8l5KDwKz8iNV6vUCrnSMacfELMgn1%2FsBCVNbVybqBsTKzFrBx2alsAOQMb91xZI01iBFUygZQKe2HUhU%3D\";s:9:\"condition\";s:12:\"New with box\";}}',1764104803),('laravel-cache-ebay_keyword_c55fc6dff8df7d8d9e224bae072455f4','a:0:{}',1764104936),('laravel-cache-ebay_keyword_e6e65a6f84cb95a5bcae06eece8c197f','a:0:{}',1764105127),('laravel-cache-upc_lookup_00609461800015','N;',1764105262),('laravel-cache-upc_lookup_190104201447','N;',1764104449),('laravel-cache-upc_lookup_190107458269','a:8:{s:5:\"title\";s:107:\"Stance Icon No Show Crew Cut Socks Shoes Pink : SM (US Men\'s Shoe 3-5.5 - Women\'s Shoe 5-7.5), Nylon/Cotton\";s:5:\"brand\";s:6:\"Stance\";s:11:\"description\";s:573:\"Stance Icon No Show - Crew Cut Socks Shoes : Pink : Imagine kicking your feet up in the comfort of the Stance Icon No Show socks. ; A thin, close fitted sock that is lightweight and breathable. ; Light cushion is designed for lightweight bulk and excellent comfort. ; Deeper heel pockets work in tandem with the arch for an exceptional fit. ; Seamles toe design for a comfortable feel. ; 55% cotton, 42% nylon, 3% elastane. ; Machine wash, tumble dry. ; Imported. | Stance Icon No Show Crew Cut Socks Shoes Pink : SM (US Men\'s Shoe 3-5.5 - Women\'s Shoe 5-7.5), Nylon/Cotton\";s:12:\"lowest_price\";d:4.99;s:13:\"highest_price\";d:11.99;s:6:\"offers\";a:1:{i:0;a:4:{s:8:\"merchant\";s:10:\"Zappos.com\";s:5:\"price\";d:11.99;s:8:\"currency\";s:0:\"\";s:4:\"link\";s:118:\"https://www.upcitemdb.com/norob/alink/?id=z2q21323z2y2c484w2&tid=1&seq=1764018953&plt=c176fd46522407b44fefc031ebcca71a\";}}s:6:\"images\";a:1:{i:0;s:65:\"https://m.media-amazon.com/images/I/51Ti-TMR+0L._SR960%2C720_.jpg\";}s:8:\"category\";s:29:\"Apparel & Accessories > Shoes\";}',1764105353),('laravel-cache-upc_lookup_840241311905','a:8:{s:5:\"title\";s:82:\"UFC No Show Socks Men\'s Crew Cut Socks Shoes White/Black, Cotton/Polyester/Spandex\";s:5:\"brand\";s:3:\"UFC\";s:11:\"description\";s:455:\"UFC No Show Socks - Men\'s Crew Cut Socks Shoes : White/Black : The UFC No Show Socks are perfect for any day! The moisture wicking blend will have your feet comfortable all day no matter what you are taking on. ; Officially licensed by the UFC. ; High quality moisture wicking blend. ; No show cut. ; 69% Cotton, 29% Polyester, 2% Spandex ; Machine washable. ; Imported. | UFC No Show Socks Men\'s Crew Cut Socks Shoes White/Black, Cotton/Polyester/Spandex\";s:12:\"lowest_price\";d:1.99;s:13:\"highest_price\";d:9.99;s:6:\"offers\";a:1:{i:0;a:4:{s:8:\"merchant\";s:10:\"Zappos.com\";s:5:\"price\";d:9.99;s:8:\"currency\";s:0:\"\";s:4:\"link\";s:118:\"https://www.upcitemdb.com/norob/alink/?id=z2x2y21323y27444y2&tid=1&seq=1764018534&plt=983dce96f8d36030adedfeaae5e32ad9\";}}s:6:\"images\";a:1:{i:0;s:65:\"https://m.media-amazon.com/images/I/61NMWwyVOTL._SR960%2C720_.jpg\";}s:8:\"category\";s:29:\"Apparel & Accessories > Shoes\";}',1764104934),('laravel-cache-upc_lookup_840241311912','a:8:{s:5:\"title\";s:82:\"UFC No Show Socks Men\'s Crew Cut Socks Shoes Black/White, Spandex/Polyester/Cotton\";s:5:\"brand\";s:3:\"UFC\";s:11:\"description\";s:455:\"UFC No Show Socks - Men\'s Crew Cut Socks Shoes : Black/White : The UFC No Show Socks are perfect for any day! The moisture wicking blend will have your feet comfortable all day no matter what you are taking on. ; Officially licensed by the UFC. ; High quality moisture wicking blend. ; No show cut. ; 69% Cotton, 29% Polyester, 2% Spandex ; Machine washable. ; Imported. | UFC No Show Socks Men\'s Crew Cut Socks Shoes Black/White, Spandex/Polyester/Cotton\";s:12:\"lowest_price\";d:1.99;s:13:\"highest_price\";d:9.99;s:6:\"offers\";a:1:{i:0;a:4:{s:8:\"merchant\";s:10:\"Zappos.com\";s:5:\"price\";d:7.65;s:8:\"currency\";s:0:\"\";s:4:\"link\";s:118:\"https://www.upcitemdb.com/norob/alink/?id=z2x2y21323y27454q2&tid=1&seq=1763993489&plt=4beba4272c52e697cc5327f39eb166e1\";}}s:6:\"images\";a:1:{i:0;s:65:\"https://m.media-amazon.com/images/I/61Fm3Y9NADL._SR960%2C720_.jpg\";}s:8:\"category\";s:29:\"Apparel & Accessories > Shoes\";}',1764079889),('laravel-cache-upc_lookup_840241311935','N;',1764105263),('laravel-cache-upc_lookup_840241311936','a:8:{s:5:\"title\";s:79:\"UFC Crew Socks Men\'s Crew Cut Socks Shoes White/Black, Cotton/Polyester/Spandex\";s:5:\"brand\";s:3:\"UFC\";s:11:\"description\";s:443:\"UFC Crew Socks - Men\'s Crew Cut Socks Shoes : White/Black : The UFC Crew Socks are perfect for any day! The moisture wicking blend will have your feet comfortable all day no matter what you are taking on. ; Officially licensed by the UFC. ; High quality moisture wicking blend. ; Crew cut. ; 69% Cotton, 29% Polyester, 2% Spandex ; Machine washable. ; Imported. | UFC Crew Socks Men\'s Crew Cut Socks Shoes White/Black, Cotton/Polyester/Spandex\";s:12:\"lowest_price\";d:2.39;s:13:\"highest_price\";d:11.99;s:6:\"offers\";a:1:{i:0;a:4:{s:8:\"merchant\";s:10:\"Zappos.com\";s:5:\"price\";d:10.44;s:8:\"currency\";s:0:\"\";s:4:\"link\";s:118:\"https://www.upcitemdb.com/norob/alink/?id=z2x2y21323y2b4b4u2&tid=1&seq=1763993490&plt=68e894c84b1ee6ab3ebb950ccc1e9ea5\";}}s:6:\"images\";a:1:{i:0;s:65:\"https://m.media-amazon.com/images/I/518eLt3KyzL._SR960%2C720_.jpg\";}s:8:\"category\";s:29:\"Apparel & Accessories > Shoes\";}',1764079890),('laravel-cache-upc_lookup_B09F9BJSWV','N;',1764105264);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `photo_batches`
--

DROP TABLE IF EXISTS `photo_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `photo_batches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `correlation_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `chat_id` bigint NOT NULL,
  `message_ids` json DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `processed_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','processed','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `title` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) DEFAULT NULL,
  `condition` enum('new','used','refurbished') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `brand` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `size` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `color` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `ai_summary` text COLLATE utf8mb4_unicode_ci,
  `locations` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `photo_batches_correlation_id_unique` (`correlation_id`),
  KEY `photo_batches_chat_id_index` (`chat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `photo_batches`
--

LOCK TABLES `photo_batches` WRITE;
/*!40000 ALTER TABLE `photo_batches` DISABLE KEYS */;
INSERT INTO `photo_batches` VALUES (3,'BATCH-OFTYURZX',0,NULL,'2025-11-23 06:49:31',NULL,'pending','Мужская толстовка Carhartt K122 Loose Fit Midweight Full-Zip Hoodie Серая L Новая','Продается новая мужская толстовка Carhartt на молнии (модель K122-HGY).\n\nХарактеристики:\n- Бренд: Carhartt\n- Модель: Loose Fit Midweight Full-Zip Sweatshirt\n- Размер: L (Loose Fit - свободная посадка)\n- Цвет: Heather Grey (Серый меланж)\n- Код модели: K122-HGY\n- Состояние: Абсолютно новая с бирками (New with tags)\n\nОсобенности:\n- Прочная латунная молния во всю длину.\n- Регулируемый капюшон со шнурком.\n- Два передних кармана для рук.\n- Эластичные манжеты и пояс из ребристого трикотажа.\n- Нашивка с логотипом Carhartt на левом кармане.\n\nКлассическая, надежная толстовка средней плотности, идеально подходящая как для работы, так и для повседневного стиля.',45.00,'new','Мужская одежда > Толстовки и худи','Carhartt','L','Серый (Heather Grey)','',1,'{\n    \"internal_ids\": [\n        \"GG112\"\n    ],\n    \"barcodes\": [\n        \"035481116063\"\n    ],\n    \"title\": \"Мужская толстовка Carhartt K122 Loose Fit Midweight Full-Zip Hoodie Серая L Новая\",\n    \"description\": \"Продается новая мужская толстовка Carhartt на молнии (модель K122-HGY).\\n\\nХарактеристики:\\n- Бренд: Carhartt\\n- Модель: Loose Fit Midweight Full-Zip Sweatshirt\\n- Размер: L (Loose Fit - свободная посадка)\\n- Цвет: Heather Grey (Серый меланж)\\n- Код модели: K122-HGY\\n- Состояние: Абсолютно новая с бирками (New with tags)\\n\\nОсобенности:\\n- Прочная латунная молния во всю длину.\\n- Регулируемый капюшон со шнурком.\\n- Два передних кармана для рук.\\n- Эластичные манжеты и пояс из ребристого трикотажа.\\n- Нашивка с логотипом Carhartt на левом кармане.\\n\\nКлассическая, надежная толстовка средней плотности, идеально подходящая как для работы, так и для повседневного стиля.\",\n    \"brand\": \"Carhartt\",\n    \"category\": \"Мужская одежда > Толстовки и худи\",\n    \"color\": \"Серый (Heather Grey)\",\n    \"material\": \"Смесовый хлопок\",\n    \"size\": \"L\",\n    \"condition\": \"new\",\n    \"price_estimate\": 45,\n    \"price_min\": 35,\n    \"price_max\": 60\n}',NULL,'2025-11-23 06:49:31','2025-11-23 09:02:43'),(4,'BATCH-23R0I8QD',0,NULL,'2025-11-23 06:49:36',NULL,'pending','Женская фланелевая рубашка в клетку оранжевая черная Casual Grunge','Стильная и уютная фланелевая рубашка в классическую клетку. Основные цвета: оранжевый, черный и серый, создающие теплый осенний образ. Модель имеет прямой крой, классический отложной воротник и один накладной карман на груди. Застегивается на пуговицы по всей длине. Материал мягкий, приятный на ощупь (похож на хлопок или смесовую фланель), идеально подходит для прохладной погоды. Рубашка универсальна: отлично смотрится как самостоятельный элемент гардероба, так и в многослойных образах поверх футболки в стиле гранж или кэжуал.',24.00,'used','Женская одежда > Рубашки и блузки','Unknown','M','Оранжевый, Черный, Серый',NULL,1,'{\n    \"internal_ids\": [],\n    \"barcodes\": [],\n    \"title\": \"Женская фланелевая рубашка в клетку оранжевая черная Casual Grunge\",\n    \"description\": \"Стильная и уютная фланелевая рубашка в классическую клетку. Основные цвета: оранжевый, черный и серый, создающие теплый осенний образ. Модель имеет прямой крой, классический отложной воротник и один накладной карман на груди. Застегивается на пуговицы по всей длине. Материал мягкий, приятный на ощупь (похож на хлопок или смесовую фланель), идеально подходит для прохладной погоды. Рубашка универсальна: отлично смотрится как самостоятельный элемент гардероба, так и в многослойных образах поверх футболки в стиле гранж или кэжуал.\",\n    \"brand\": \"Unknown\",\n    \"category\": \"Женская одежда > Рубашки и блузки\",\n    \"color\": \"Оранжевый, Черный, Серый\",\n    \"material\": \"Фланель, Хлопок\",\n    \"size\": \"M\",\n    \"condition\": \"used\",\n    \"price_estimate\": 24,\n    \"price_min\": 18,\n    \"price_max\": 32\n}',NULL,'2025-11-23 06:49:36','2025-11-23 19:25:21'),(5,'BATCH-VBGAHSHC',0,NULL,'2025-11-23 19:35:12','2025-11-23 19:36:14','processed','Мужская Толстовка Carhartt K122 Loose Fit на Молнии Серый Размер L Новая','Продается новая мужская толстовка (худи) Carhartt Loose Fit Midweight Full-Zip Sweatshirt. \n\nХарактеристики:\n- Бренд: Carhartt\n- Модель: K122-HGY (TS0122-M)\n- Размер: L (Loose Fit - свободный крой, большемерит)\n- Цвет: Серый меланж (Heather Grey)\n- Состояние: Абсолютно новая с бирками\n- Материал: Средней плотности (Midweight), смесь хлопка и полиэстера\n\nОсобенности: Прочная латунная молния во всю длину, регулируемый капюшон на шнурке, два передних кармана для рук, эластичные трикотажные манжеты и пояс. Классическая, надежная и теплая кофта от легендарного бренда рабочей одежды.',45.00,'new','Мужская одежда / Толстовки и худи','Carhartt','L','Серый (Heather Grey)',NULL,1,'{\n    \"internal_ids\": [\n        \"GG112\"\n    ],\n    \"barcodes\": [\n        \"035481116063\"\n    ],\n    \"title\": \"Мужская Толстовка Carhartt K122 Loose Fit на Молнии Серый Размер L Новая\",\n    \"description\": \"Продается новая мужская толстовка (худи) Carhartt Loose Fit Midweight Full-Zip Sweatshirt. \\n\\nХарактеристики:\\n- Бренд: Carhartt\\n- Модель: K122-HGY (TS0122-M)\\n- Размер: L (Loose Fit - свободный крой, большемерит)\\n- Цвет: Серый меланж (Heather Grey)\\n- Состояние: Абсолютно новая с бирками\\n- Материал: Средней плотности (Midweight), смесь хлопка и полиэстера\\n\\nОсобенности: Прочная латунная молния во всю длину, регулируемый капюшон на шнурке, два передних кармана для рук, эластичные трикотажные манжеты и пояс. Классическая, надежная и теплая кофта от легендарного бренда рабочей одежды.\",\n    \"brand\": \"Carhartt\",\n    \"category\": \"Мужская одежда \\/ Толстовки и худи\",\n    \"color\": \"Серый (Heather Grey)\",\n    \"material\": \"Смесь хлопка и полиэстера\",\n    \"size\": \"L\",\n    \"condition\": \"new\",\n    \"price_estimate\": 45,\n    \"price_min\": 35,\n    \"price_max\": 55\n}',NULL,'2025-11-23 19:35:12','2025-11-23 19:36:14'),(6,'BATCH-5LJVRAQE',0,NULL,'2025-11-23 22:54:16','2025-11-23 22:55:00','processed','Набор спортивных носков UFC (3 пары), Черный/Белый, Единый размер, Хлопок/Полиэстер','Новый набор оригинальных спортивных носков от официального бренда UFC. Идеально подходят для интенсивных тренировок, занятий ММА или повседневной носки. Набор включает три пары: две пары в черно-белой расцветке (Style 12BLK2166) и одну пару в бело-черной расцветке (Style 12WHT2163). \n\nМатериал обеспечивает высокий уровень комфорта и долговечности: 69% хлопок, 29% полиэстер и 2% спандекс. Хлопок гарантирует мягкость и воздухопроницаемость, а полиэстер и спандекс — эластичность и быстрое высыхание. Размер универсальный (One Size). Товар новый, в заводской упаковке.',18.00,'new','Одежда и аксессуары, Носки','UFC','One Size','Ассорти (Черный и Белый)',NULL,1,'{\n    \"internal_ids\": [\n        \"GG129\"\n    ],\n    \"barcodes\": [\n        \"840241311936\",\n        \"840241311912\"\n    ],\n    \"title\": \"Набор спортивных носков UFC (3 пары), Черный\\/Белый, Единый размер, Хлопок\\/Полиэстер\",\n    \"description\": \"Новый набор оригинальных спортивных носков от официального бренда UFC. Идеально подходят для интенсивных тренировок, занятий ММА или повседневной носки. Набор включает три пары: две пары в черно-белой расцветке (Style 12BLK2166) и одну пару в бело-черной расцветке (Style 12WHT2163). \\n\\nМатериал обеспечивает высокий уровень комфорта и долговечности: 69% хлопок, 29% полиэстер и 2% спандекс. Хлопок гарантирует мягкость и воздухопроницаемость, а полиэстер и спандекс — эластичность и быстрое высыхание. Размер универсальный (One Size). Товар новый, в заводской упаковке.\",\n    \"brand\": \"UFC\",\n    \"category\": \"Одежда и аксессуары, Носки\",\n    \"color\": \"Ассорти (Черный и Белый)\",\n    \"material\": \"69% Хлопок, 29% Полиэстер, 2% Спандекс\",\n    \"size\": \"One Size\",\n    \"condition\": \"new\",\n    \"price_estimate\": 18,\n    \"price_min\": 15,\n    \"price_max\": 25\n}',NULL,'2025-11-23 22:54:16','2025-11-24 00:07:27'),(7,'BATCH-EHNTOZK0',0,NULL,'2025-11-25 01:58:45',NULL,'pending','Набор из 3 пар носков Stance L (M 9-12) — Серый, Синий, Белый | Хлопковый бленд','Отличный набор из трех пар высококачественных носков Stance, идеально подходящих для повседневной носки. В комплект входят три пары разных стилей и цветов: серые (Crew, Mid Cushion), темно-синие (No Show, Light Cushion) и белые (Crew/Casual). Все носки изготовлены из фирменного смесового чесаного хлопка Stance, обеспечивающего максимальный комфорт, долговечность и воздухопроницаемость. Размер L соответствует мужскому размеру US 9-12. Носки Stance известны своим качеством, поддержкой свода стопы и стильным дизайном. Товар новый, с оригинальными бирками.',30.00,'new','Одежда, Аксессуары, Носки','Stance','L (US M 9-12)','Ассорти (Серый, Темно-синий, Белый)',NULL,1,'{\n    \"internal_ids\": [\n        \"GG115\"\n    ],\n    \"barcodes\": [\n        \"190104201447\"\n    ],\n    \"title\": \"Набор из 3 пар носков Stance L (M 9-12) — Серый, Синий, Белый | Хлопковый бленд\",\n    \"description\": \"Отличный набор из трех пар высококачественных носков Stance, идеально подходящих для повседневной носки. В комплект входят три пары разных стилей и цветов: серые (Crew, Mid Cushion), темно-синие (No Show, Light Cushion) и белые (Crew\\/Casual). Все носки изготовлены из фирменного смесового чесаного хлопка Stance, обеспечивающего максимальный комфорт, долговечность и воздухопроницаемость. Размер L соответствует мужскому размеру US 9-12. Носки Stance известны своим качеством, поддержкой свода стопы и стильным дизайном. Товар новый, с оригинальными бирками.\",\n    \"brand\": \"Stance\",\n    \"category\": \"Одежда, Аксессуары, Носки\",\n    \"color\": \"Ассорти (Серый, Темно-синий, Белый)\",\n    \"material\": \"Смесь чесаного хлопка (Combed Cotton Blend)\",\n    \"size\": \"L (US M 9-12)\",\n    \"condition\": \"new\",\n    \"price_estimate\": 30,\n    \"price_min\": 25,\n    \"price_max\": 40\n}',NULL,'2025-11-25 01:58:45','2025-11-25 01:59:35'),(8,'BATCH-KLXYLKTC',0,NULL,'2025-11-25 02:01:15',NULL,'pending','',NULL,NULL,NULL,'','','','',NULL,1,NULL,NULL,'2025-11-25 02:01:15','2025-11-25 02:01:15'),(9,'BATCH-P3AIJGIX',0,NULL,'2025-11-25 02:04:11','2025-11-25 02:05:00','processed','Dan Post Work & Outdoor Высокоэффективные Рабочие Носки Mid-Calf, 2 Пары, Размер 10.5-13','Носки Dan Post Work & Outdoor High-Performance Socks средней плотности (Medium Weight) с продвинутым дизайном, превосходящим обычные рабочие носки. Идеально подходят для длительной работы и активного отдыха. Особенности включают: широкая ребристая резинка для надежной фиксации, превосходный контроль влажности, антимикробная защита и контроль запаха, усиленная поддержка свода стопы (Arch Support), гладкий шов на мыске (Lycra Toe Seam). Усиленная пятка и мысок обеспечивают долговечность и снижение трения. Сделано в США. В комплекте 2 пары. Размер подходит для обуви 10 1/2 - 13.',20.00,'new','Носки для работы и активного отдыха','DAN POST','10 1/2 - 13','Песочный/Бежевый',NULL,1,'{\n    \"internal_ids\": [\n        \"GG119\"\n    ],\n    \"barcodes\": [],\n    \"title\": \"Dan Post Work & Outdoor Высокоэффективные Рабочие Носки Mid-Calf, 2 Пары, Размер 10.5-13\",\n    \"description\": \"Носки Dan Post Work & Outdoor High-Performance Socks средней плотности (Medium Weight) с продвинутым дизайном, превосходящим обычные рабочие носки. Идеально подходят для длительной работы и активного отдыха. Особенности включают: широкая ребристая резинка для надежной фиксации, превосходный контроль влажности, антимикробная защита и контроль запаха, усиленная поддержка свода стопы (Arch Support), гладкий шов на мыске (Lycra Toe Seam). Усиленная пятка и мысок обеспечивают долговечность и снижение трения. Сделано в США. В комплекте 2 пары. Размер подходит для обуви 10 1\\/2 - 13.\",\n    \"brand\": \"DAN POST\",\n    \"category\": \"Носки для работы и активного отдыха\",\n    \"color\": \"Песочный\\/Бежевый\",\n    \"material\": \"Смесь хлопка (Хлопок, Полиэстер, Нейлон, Спандекс)\",\n    \"size\": \"10 1\\/2 - 13\",\n    \"condition\": \"new\",\n    \"price_estimate\": 20,\n    \"price_min\": 15,\n    \"price_max\": 25\n}',NULL,'2025-11-25 02:04:11','2025-11-25 02:06:42'),(10,'BATCH-DSKJVPQL',0,NULL,'2025-11-25 02:07:21','2025-11-25 02:08:05','processed','Большой Лот Носков (7+ пар): UFC, Jordan, Massimo Dutti. Новые, Универсальный Размер','Смешанный лот новых носков от известных спортивных и модных брендов, идеально подходящий для повседневной носки и тренировок. Лот включает несколько пар носков UFC (белые и черно-белые, высокие и укороченные модели), черные носки Massimo Dutti и черные носки Jordan с логотипом. Все товары новые, с оригинальными бирками. Носки UFC и Massimo Dutti изготовлены преимущественно из смеси хлопка, полиэстера/полиамида и спандекса (эластана), обеспечивая комфорт, воздухопроницаемость и эластичность. Отличный набор для пополнения гардероба или перепродажи.',30.00,'new','Одежда и Аксессуары / Носки','Смешанный лот (UFC, Jordan, Massimo Dutti)','Универсальный размер / Смешанные размеры','Белый, Черный (Смешанный)',NULL,1,'{\n    \"internal_ids\": [\n        \"GG120\",\n        \"B00F9BJSWV\"\n    ],\n    \"barcodes\": [\n        \"840241311935\",\n        \"840241311936\",\n        \"00609461800015\"\n    ],\n    \"title\": \"Большой Лот Носков (7+ пар): UFC, Jordan, Massimo Dutti. Новые, Универсальный Размер\",\n    \"description\": \"Смешанный лот новых носков от известных спортивных и модных брендов, идеально подходящий для повседневной носки и тренировок. Лот включает несколько пар носков UFC (белые и черно-белые, высокие и укороченные модели), черные носки Massimo Dutti и черные носки Jordan с логотипом. Все товары новые, с оригинальными бирками. Носки UFC и Massimo Dutti изготовлены преимущественно из смеси хлопка, полиэстера\\/полиамида и спандекса (эластана), обеспечивая комфорт, воздухопроницаемость и эластичность. Отличный набор для пополнения гардероба или перепродажи.\",\n    \"brand\": \"Смешанный лот (UFC, Jordan, Massimo Dutti)\",\n    \"category\": \"Одежда и Аксессуары \\/ Носки\",\n    \"color\": \"Белый, Черный (Смешанный)\",\n    \"material\": \"Хлопок\\/Полиэстер\\/Спандекс (Смешанный состав)\",\n    \"size\": \"Универсальный размер \\/ Смешанные размеры\",\n    \"condition\": \"new\",\n    \"price_estimate\": 30,\n    \"price_min\": 25,\n    \"price_max\": 45\n}',NULL,'2025-11-25 02:07:21','2025-11-25 02:12:05'),(11,'BATCH-985DEIXE',0,NULL,'2025-11-25 02:15:03','2025-11-25 02:15:48','processed','Лот 4 пары носков Stance Icon No Show - Розовые - Размеры S и L - Хлопок - Новые','Продается лот из 4 пар носков бренда Stance, модель Icon No Show. \n\nХарактеристики:\n- Бренд: Stance\n- Модель: Icon No Show (A145A21INS)\n- Цвет: Розовый (Pink / PNK) с синей полоской на мыске\n- Материал: 57% гребенной хлопок, 39% нейлон, 4% эластан\n- Тип: Следки (невидимки), легкая амортизация (Light Cushion)\n- Состояние: Новые с бирками\n\nВ комплекте 4 пары:\n- 3 пары размера S (US Men\'s 3-5.5 / Women\'s 5-7.5)\n- 1 пара размера L (US Men\'s 9-12)\n\nОтличные качественные носки для повседневной носки.',28.00,'new','Носки','Stance','S, L','Розовый',NULL,1,'{\n    \"internal_ids\": [\n        \"GG126\"\n    ],\n    \"barcodes\": [\n        \"190107458269\"\n    ],\n    \"title\": \"Лот 4 пары носков Stance Icon No Show - Розовые - Размеры S и L - Хлопок - Новые\",\n    \"description\": \"Продается лот из 4 пар носков бренда Stance, модель Icon No Show. \\n\\nХарактеристики:\\n- Бренд: Stance\\n- Модель: Icon No Show (A145A21INS)\\n- Цвет: Розовый (Pink \\/ PNK) с синей полоской на мыске\\n- Материал: 57% гребенной хлопок, 39% нейлон, 4% эластан\\n- Тип: Следки (невидимки), легкая амортизация (Light Cushion)\\n- Состояние: Новые с бирками\\n\\nВ комплекте 4 пары:\\n- 3 пары размера S (US Men\'s 3-5.5 \\/ Women\'s 5-7.5)\\n- 1 пара размера L (US Men\'s 9-12)\\n\\nОтличные качественные носки для повседневной носки.\",\n    \"brand\": \"Stance\",\n    \"category\": \"Носки\",\n    \"color\": \"Розовый\",\n    \"material\": \"57% хлопок, 39% нейлон, 4% эластан\",\n    \"size\": \"S, L\",\n    \"condition\": \"new\",\n    \"price_estimate\": 28,\n    \"price_min\": 22,\n    \"price_max\": 35\n}',NULL,'2025-11-25 02:15:03','2025-11-25 02:15:48');
/*!40000 ALTER TABLE `photo_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `photo_buffers`
--

DROP TABLE IF EXISTS `photo_buffers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `photo_buffers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `file_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message_id` bigint NOT NULL,
  `chat_id` bigint NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `taken_at` timestamp NULL DEFAULT NULL,
  `gg_label` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `barcode` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `group_id` int DEFAULT NULL,
  `group_order` int NOT NULL DEFAULT '0',
  `processed` tinyint(1) NOT NULL DEFAULT '0',
  `sent_to_bot` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `photo_buffers_file_id_unique` (`file_id`)
) ENGINE=InnoDB AUTO_INCREMENT=135 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `photo_buffers`
--

LOCK TABLES `photo_buffers` WRITE;
/*!40000 ALTER TABLE `photo_buffers` DISABLE KEYS */;
INSERT INTO `photo_buffers` VALUES (45,'AgACAgEAAxkBAAIBD2kiB-UHthRi61BdgHSlx-U0Ya2lAAKDC2sbVZwQRY3Gz4zp1Fi2AQADAgADeQADNgQ',459,6729176028,'buffer/2025/11/23/69231b0e6973a.jpg','2025-11-23 19:32:46',NULL,'','',NULL,0,0,0,'2025-11-23 19:32:46','2025-11-23 19:32:46'),(46,'AgACAgEAAxkBAAIBEGkiB-XPT0N1MiuoAcglNvULtoeXAAKCC2sbVZwQRUI5CZawnkeoAQADAgADeQADNgQ',460,6729176028,'buffer/2025/11/23/69231b0fe5dd9.jpg','2025-11-23 19:32:47',NULL,'','',NULL,0,0,0,'2025-11-23 19:32:47','2025-11-23 19:32:47'),(47,'AgACAgEAAxkBAAIBCmkiBP_teZv7i5nrw6kU9MbZ15VxAAKBC2sbVZwQRSncLyctGHvYAQADAgADeQADNgQ',461,6729176028,'buffer/2025/11/23/69231b1185cb5.jpg','2025-11-23 19:32:49',NULL,'','',NULL,0,0,0,'2025-11-23 19:32:49','2025-11-23 19:32:49'),(48,'AgACAgEAAxkBAAIBCWkiBP_souEmcI1Z8vKm-othzr5jAAKAC2sbVZwQRUrHOvpcYM27AQADAgADeQADNgQ',462,6729176028,'buffer/2025/11/23/69231b12da772.jpg','2025-11-23 19:32:50',NULL,'','',NULL,0,0,0,'2025-11-23 19:32:50','2025-11-23 19:32:50'),(49,'AgACAgEAAxkBAAIBFWkiB-XMbaemMT9yJEX9tOjgWsYDAAJ_C2sbVZwQRba7h2fv-xELAQADAgADeQADNgQ',463,6729176028,'buffer/2025/11/23/69231b143994b.jpg','2025-11-23 19:32:52',NULL,'','',NULL,0,0,0,'2025-11-23 19:32:52','2025-11-23 19:32:52'),(54,'AgACAgEAAxkBAAIBG2kiB-UrT50vZ0Pu1TmQfeCT4bBDAAJ5C2sbVZwQRYunSTJ243s7AQADAgADeQADNgQ',469,6729176028,'buffer/2025/11/23/69231b23adbf5.jpg','2025-11-23 19:33:07',NULL,'','',NULL,0,0,0,'2025-11-23 19:33:07','2025-11-23 19:33:07'),(55,'AgACAgEAAxkBAAIBGmkiB-UCAoJZSdicaYy0bXDB-1RlAAJ4C2sbVZwQRbtIUXRLDCE8AQADAgADeQADNgQ',470,6729176028,'buffer/2025/11/23/69231b259046c.jpg','2025-11-23 19:33:09',NULL,'','',NULL,0,0,0,'2025-11-23 19:33:09','2025-11-23 19:33:09'),(56,'AgACAgEAAxkBAAIBGWkiB-UDzEP_w988E0niD-DmK9UOAAJ3C2sbVZwQRRAdXYcufIwGAQADAgADeQADNgQ',471,6729176028,'buffer/2025/11/23/69231b270c262.jpg','2025-11-23 19:33:11',NULL,'','',NULL,0,0,0,'2025-11-23 19:33:11','2025-11-23 19:33:11'),(57,'AgACAgEAAxkBAAIBHGkiB-WIJXxBvrkEEAcch7zqdvSxAAJ2C2sbVZwQRRLOl2YmrmLqAQADAgADeQADNgQ',472,6729176028,'buffer/2025/11/23/69231b28b0099.jpg','2025-11-23 19:33:12',NULL,'','',NULL,0,0,0,'2025-11-23 19:33:12','2025-11-23 19:33:12'),(64,'AgACAgEAAxkBAAIB7WkjKL1p1f17mJWiVrludHCVHHsbAAIcC2sbyEEYRWj6P8emozP6AQADAgADeQADNgQ',493,6729176028,'buffer/2025/11/23/692328c95854a.jpg','2025-11-23 20:31:21',NULL,'','',NULL,0,0,0,'2025-11-23 20:31:21','2025-11-23 20:31:21'),(65,'AgACAgEAAxkBAAIB7mkjKL1hm-Em6t1kM1xd-xui7tbQAAIjC2sbVZwQRahWTNlIDakkAQADAgADeQADNgQ',494,6729176028,'buffer/2025/11/23/692328ce8bad9.jpg','2025-11-23 20:31:26',NULL,'','',NULL,0,0,0,'2025-11-23 20:31:26','2025-11-23 20:31:26'),(66,'AgACAgEAAxkBAAIB72kjKL3iECJFpurVWEu_9zMMWtVqAAIkC2sbVZwQRSC2Gk2iQ0CWAQADAgADeQADNgQ',495,6729176028,'buffer/2025/11/23/692328d0b33f6.jpg','2025-11-23 20:31:28',NULL,'','',NULL,0,0,0,'2025-11-23 20:31:28','2025-11-23 20:31:28'),(67,'AgACAgEAAxkBAAIB8GkjKL1W-_ikafdo8BJM0QV7xleAAAIlC2sbVZwQRRENxvmAs6NvAQADAgADeQADNgQ',496,6729176028,'buffer/2025/11/23/692328d2795b2.jpg','2025-11-23 20:31:30',NULL,'','',NULL,0,0,0,'2025-11-23 20:31:30','2025-11-23 20:31:30'),(68,'AgACAgEAAxkBAAIB8WkjKL0vZyv1nyi2EHV-ebVSoV-JAAI1C2sbVZwQRefffmwJ-RuyAQADAgADeQADNgQ',497,6729176028,'buffer/2025/11/23/692328d47f26a.jpg','2025-11-23 20:31:32',NULL,'','',NULL,0,0,0,'2025-11-23 20:31:32','2025-11-23 20:31:32'),(69,'AgACAgEAAxkBAAIB8mkjKL6jileuuyIas7jDM9oN6NODAAI2C2sbVZwQRanbUE-5pZ2UAQADAgADeQADNgQ',498,6729176028,'buffer/2025/11/23/692328d84d5e1.jpg','2025-11-23 20:31:36',NULL,'','',NULL,0,0,0,'2025-11-23 20:31:36','2025-11-23 20:31:36'),(70,'AgACAgEAAxkBAAIB82kjKL5HmKiDtQWUd6R30CVC57ZUAAI3C2sbVZwQRX1F9AfIQ0e4AQADAgADeQADNgQ',499,6729176028,'buffer/2025/11/23/692328dac392f.jpg','2025-11-23 20:31:38',NULL,'','',NULL,0,0,0,'2025-11-23 20:31:38','2025-11-23 20:31:38'),(71,'AgACAgEAAxkBAAIB9GkjKL7nx_ysFMHPyqwTWMWAT9NdAAI4C2sbVZwQRaQI9r524CnyAQADAgADeQADNgQ',500,6729176028,'buffer/2025/11/23/692328dcee160.jpg','2025-11-23 20:31:40',NULL,'','',NULL,0,0,0,'2025-11-23 20:31:40','2025-11-23 20:31:40'),(72,'AgACAgEAAxkBAAIB9WkjKL4VN5n-efRaKDTH1vL2sH62AAI5C2sbVZwQRZLZNU3MbZ2xAQADAgADeQADNgQ',501,6729176028,'buffer/2025/11/23/692328dfd673d.jpg','2025-11-23 20:31:43',NULL,'','',NULL,0,0,0,'2025-11-23 20:31:43','2025-11-23 20:31:43'),(73,'AgACAgEAAxkBAAIB9mkjKL46heRh0miu7zGBedvEl4txAAI6C2sbVZwQRUMqUP8z6oYiAQADAgADeQADNgQ',502,6729176028,'buffer/2025/11/23/692328e1e2ad4.jpg','2025-11-23 20:31:45',NULL,'','',NULL,0,0,0,'2025-11-23 20:31:45','2025-11-23 20:31:45'),(78,'AgACAgEAAxkBAAIB-2kjKL4rihZ8Pou-oLmM3ZZu0k0kAAIdC2sbyEEYReeds4cchxfSAQADAgADeQADNgQ',507,6729176028,'buffer/2025/11/23/692328ef1efa8.jpg','2025-11-23 20:31:59',NULL,'','',NULL,0,0,0,'2025-11-23 20:31:59','2025-11-23 20:31:59'),(79,'AgACAgEAAxkBAAIB_GkjKMCsHBizxu9bLct9k__4OHPxAAIeC2sbyEEYRV4a8Is2LDVJAQADAgADeQADNgQ',508,6729176028,'buffer/2025/11/23/692328f17859e.jpg','2025-11-23 20:32:01',NULL,'','',NULL,0,0,0,'2025-11-23 20:32:01','2025-11-23 20:32:01'),(80,'AgACAgEAAxkBAAIB_WkjKMCPGjTP6LxUTSUygXc7VGztAAI_C2sbVZwQRXOkDyKBeWx0AQADAgADeQADNgQ',509,6729176028,'buffer/2025/11/23/692328f3a6952.jpg','2025-11-23 20:32:03',NULL,'','',NULL,0,0,0,'2025-11-23 20:32:03','2025-11-23 20:32:03'),(81,'AgACAgEAAxkBAAIB_mkjKMBUTTiDsxMsMpTFGb6a2e6NAAJAC2sbVZwQRa-j6QdoMlmoAQADAgADeQADNgQ',510,6729176028,'buffer/2025/11/23/692328f58d05f.jpg','2025-11-23 20:32:05',NULL,'','',NULL,0,0,0,'2025-11-23 20:32:05','2025-11-23 20:32:05'),(82,'AgACAgEAAxkBAAIB_2kjKMDNmFBz3Lv8iPaIjFDmbYWCAAJBC2sbVZwQRcHXKzLxxXehAQADAgADeQADNgQ',511,6729176028,'buffer/2025/11/23/692328f7ecd02.jpg','2025-11-23 20:32:07',NULL,'','',NULL,0,0,0,'2025-11-23 20:32:07','2025-11-23 20:32:07'),(83,'AgACAgEAAxkBAAICAAFpIyjA8jZuj9RPJn4o4t7aMuc0bgACQgtrG1WcEEWL0MfMChWHmAEAAwIAA3kAAzYE',512,6729176028,'buffer/2025/11/23/692328fa192ba.jpg','2025-11-23 20:32:10',NULL,'','',NULL,0,0,0,'2025-11-23 20:32:10','2025-11-23 20:32:10'),(84,'AgACAgEAAxkBAAICAWkjKMAtZDY_z2k1zVN3Pk_DnahIAAJDC2sbVZwQRfFCahbv0pA4AQADAgADeQADNgQ',513,6729176028,'buffer/2025/11/23/692328fe32e2f.jpg','2025-11-23 20:32:14',NULL,'','',NULL,0,0,0,'2025-11-23 20:32:14','2025-11-23 20:32:14'),(85,'AgACAgEAAxkBAAICAmkjKMDOY9JWG6ODFDuSipttk09mAAJEC2sbVZwQRUkdi_yg9YSQAQADAgADeQADNgQ',514,6729176028,'buffer/2025/11/23/69232900f2189.jpg','2025-11-23 20:32:16',NULL,'','',NULL,0,0,0,'2025-11-23 20:32:16','2025-11-23 20:32:16'),(86,'AgACAgEAAxkBAAICA2kjKMB47msC_b5Mu7dkkz9qT18rAAJHC2sbVZwQRdq5_hLxPrSdAQADAgADeQADNgQ',515,6729176028,'buffer/2025/11/23/6923290327e3e.jpg','2025-11-23 20:32:19',NULL,'','',NULL,0,0,0,'2025-11-23 20:32:19','2025-11-23 20:32:19'),(87,'AgACAgEAAxkBAAICBGkjKMBFyGLMC6dh-Mmphez44hTYAAJIC2sbVZwQRXpUNBnA-ttBAQADAgADeQADNgQ',516,6729176028,'buffer/2025/11/23/692329061f1ea.jpg','2025-11-23 20:32:22',NULL,'','',NULL,0,0,0,'2025-11-23 20:32:22','2025-11-23 20:32:22'),(88,'AgACAgEAAxkBAAICBWkjKMADWvr-e3Fp52yGj6X4xC5OAAJJC2sbVZwQRbwWUAW6P_p2AQADAgADeQADNgQ',517,6729176028,'buffer/2025/11/23/692329088e545.jpg','2025-11-23 20:32:24',NULL,'','',NULL,0,0,0,'2025-11-23 20:32:24','2025-11-23 20:32:24'),(89,'AgACAgEAAxkBAAICBmkjKMHYJAJDvq4egPxXBgdASBVtAAJKC2sbVZwQRb44c2gY5_pXAQADAgADeQADNgQ',518,6729176028,'buffer/2025/11/23/6923290aa48cd.jpg','2025-11-23 20:32:26',NULL,'','',NULL,0,0,0,'2025-11-23 20:32:26','2025-11-23 20:32:26'),(90,'AgACAgEAAxkBAAICB2kjKMGDFOvMu-griCoOYh7-7C5rAAJLC2sbVZwQReNbDbsuqWkXAQADAgADeQADNgQ',519,6729176028,'buffer/2025/11/23/6923290cde989.jpg','2025-11-23 20:32:28',NULL,'','',NULL,0,0,0,'2025-11-23 20:32:28','2025-11-23 20:32:28'),(91,'AgACAgEAAxkBAAICCGkjKMHNlA4IHD3Bw3suyffn32PoAAJMC2sbVZwQRQ6oki7amlpQAQADAgADeQADNgQ',520,6729176028,'buffer/2025/11/23/6923290f1a7c1.jpg','2025-11-23 20:32:31',NULL,'','',NULL,0,0,0,'2025-11-23 20:32:31','2025-11-23 20:32:31'),(92,'AgACAgEAAxkBAAICCWkjKMGlVxY-kfLMOX4-0nMmVY5sAAJNC2sbVZwQRRo152KmNgIEAQADAgADeQADNgQ',521,6729176028,'buffer/2025/11/23/692329112020f.jpg','2025-11-23 20:32:33',NULL,'','',NULL,0,0,0,'2025-11-23 20:32:33','2025-11-23 20:32:33'),(93,'AgACAgEAAxkBAAICCmkjKMHcgk5LkpRo644kCzrM4Zc1AAJPC2sbVZwQRWIlrwI-eEGmAQADAgADeQADNgQ',522,6729176028,'buffer/2025/11/23/6923291447e98.jpg','2025-11-23 20:32:36',NULL,'','',NULL,0,0,0,'2025-11-23 20:32:36','2025-11-23 20:32:36'),(94,'AgACAgEAAxkBAAICC2kjKMHHQzO4YEFgVvtB4F5as8d1AAJQC2sbVZwQRRDQa-nv6etYAQADAgADeQADNgQ',523,6729176028,'buffer/2025/11/23/69232916d9bbc.jpg','2025-11-23 20:32:38',NULL,'','',NULL,0,0,0,'2025-11-23 20:32:38','2025-11-23 20:32:38'),(95,'AgACAgEAAxkBAAICDGkjKMGXkJ79oZE_tosu7_wwOIq9AAJRC2sbVZwQRX8n1NIGRgd0AQADAgADeQADNgQ',524,6729176028,'buffer/2025/11/23/69232919bc4e5.jpg','2025-11-23 20:32:41',NULL,'','',NULL,0,0,0,'2025-11-23 20:32:41','2025-11-23 20:32:41'),(96,'AgACAgEAAxkBAAICDWkjKMHYsp58RvuiDHfOrJYIYqJMAAJTC2sbVZwQRZOijz81lO5rAQADAgADeQADNgQ',525,6729176028,'buffer/2025/11/23/6923291dd651f.jpg','2025-11-23 20:32:45',NULL,'','',NULL,0,0,0,'2025-11-23 20:32:45','2025-11-23 20:32:45'),(97,'AgACAgEAAxkBAAICDmkjKMFkUis5JrA5kAROn6NuS_W-AAJUC2sbVZwQRXqtm_3YlmXoAQADAgADeQADNgQ',526,6729176028,'buffer/2025/11/23/692329208634a.jpg','2025-11-23 20:32:48',NULL,'','',NULL,0,0,0,'2025-11-23 20:32:48','2025-11-23 20:32:48'),(98,'AgACAgEAAxkBAAICD2kjKMF3fCzy49ySLtxdOtXruxegAAJVC2sbVZwQRVRspk8Mo7V-AQADAgADeQADNgQ',527,6729176028,'buffer/2025/11/23/6923292344965.jpg','2025-11-23 20:32:51',NULL,'','',NULL,0,0,0,'2025-11-23 20:32:51','2025-11-23 20:32:51'),(99,'AgACAgEAAxkBAAICEGkjKMJ9PCkJ4c3wN3reBt0-HbMsAAJWC2sbVZwQRR--pNknNj_3AQADAgADeQADNgQ',528,6729176028,'buffer/2025/11/23/692329256c899.jpg','2025-11-23 20:32:53',NULL,'','',NULL,0,0,0,'2025-11-23 20:32:53','2025-11-23 20:32:53'),(100,'AgACAgEAAxkBAAICEWkjKMKEno4fbNFqFJZPZRiba_ymAAJXC2sbVZwQRTHwb670VxauAQADAgADeQADNgQ',529,6729176028,'buffer/2025/11/23/69232927bd411.jpg','2025-11-23 20:32:55',NULL,'','',NULL,0,0,0,'2025-11-23 20:32:55','2025-11-23 20:32:55'),(101,'AgACAgEAAxkBAAICEmkjKMJay-cd2UFQyhBZC7VhGXg9AAJYC2sbVZwQRRU8vgERpbgnAQADAgADeQADNgQ',530,6729176028,'buffer/2025/11/23/6923292a15e05.jpg','2025-11-23 20:32:58',NULL,'','',NULL,0,0,0,'2025-11-23 20:32:58','2025-11-23 20:32:58'),(115,'AgACAgEAAxkBAAIBU2kiCFwtbCqbOs1UZiSWakROZ34qAAJmC2sbVZwQRfeusid3VQNhAQADAgADeQADNgQ',544,6729176028,'buffer/2025/11/23/692329478eaf9.jpg','2025-11-23 20:33:27',NULL,'','',NULL,0,0,0,'2025-11-23 20:33:27','2025-11-23 20:33:27'),(116,'AgACAgEAAxkBAAIBUGkiCFycAkCILCYU5auxK0tqs5SjAAJnC2sbVZwQRSGzqyp-io2gAQADAgADeQADNgQ',545,6729176028,'buffer/2025/11/23/69232949392f9.jpg','2025-11-23 20:33:29',NULL,'','',NULL,0,0,0,'2025-11-23 20:33:29','2025-11-23 20:33:29'),(117,'AgACAgEAAxkBAAIBTWkiCFxky2_2Hd0vI6LtUSnFYyR6AAJoC2sbVZwQRfFxexb_4OY-AQADAgADeQADNgQ',546,6729176028,'buffer/2025/11/23/6923294dc2e0d.jpg','2025-11-23 20:33:33',NULL,'','',NULL,0,0,0,'2025-11-23 20:33:33','2025-11-23 20:33:33'),(118,'AgACAgEAAxkBAAIBSmkiCFw1ce12ju1FTlG6I-mKONmaAAJpC2sbVZwQRcanjBFEfC6UAQADAgADeQADNgQ',547,6729176028,'buffer/2025/11/23/6923295086985.jpg','2025-11-23 20:33:36',NULL,'','',NULL,0,0,0,'2025-11-23 20:33:36','2025-11-23 20:33:36'),(119,'AgACAgEAAxkBAAIBSGkiCFzoUIO_TBLn7hgRPTpDkFrlAAJqC2sbVZwQRRU2jMGujcM2AQADAgADeQADNgQ',548,6729176028,'buffer/2025/11/23/692329523ab15.jpg','2025-11-23 20:33:38',NULL,'','',NULL,0,0,0,'2025-11-23 20:33:38','2025-11-23 20:33:38'),(120,'AgACAgEAAxkBAAIBHWkiB-VFNjz-NHOJWNoxuaiw6S08AAJ1C2sbVZwQRU8Mi2RcgDnSAQADAgADeQADNgQ',549,6729176028,'buffer/2025/11/23/69232953e7352.jpg','2025-11-23 20:33:39',NULL,'','',NULL,0,0,0,'2025-11-23 20:33:39','2025-11-23 20:33:39'),(121,'AgACAgEAAxkBAAIBRWkiCFweTzcDlcdiWegpmKPhOfJLAAJrC2sbVZwQRU_yRMdSIpffAQADAgADeQADNgQ',550,6729176028,'buffer/2025/11/23/69232955e3d05.jpg','2025-11-23 20:33:41',NULL,'','',NULL,0,0,0,'2025-11-23 20:33:41','2025-11-23 20:33:41'),(122,'AgACAgEAAxkBAAIBQmkiCFxb23pnQpyakrtdTl5XS22xAAJsC2sbVZwQRSqLIyyzxArUAQADAgADeQADNgQ',552,6729176028,'buffer/2025/11/23/69232958f1109.jpg','2025-11-23 20:33:44',NULL,'','',NULL,0,0,0,'2025-11-23 20:33:44','2025-11-23 20:33:44'),(123,'AgACAgEAAxkBAAIBQGkiCFz0Q0nNOFRp84-Fws1MUTqvAAJtC2sbVZwQRZ4o5sbqPdspAQADAgADeQADNgQ',555,6729176028,'buffer/2025/11/23/6923295c76854.jpg','2025-11-23 20:33:48',NULL,'','',NULL,0,0,0,'2025-11-23 20:33:48','2025-11-23 20:33:48'),(127,'AgACAgEAAxkBAAIBTGkiCFwQGuTAUlg9VWInW536RO3-AAJCC2sbyEEQRTRUjAovPgABbQEAAwIAA3kAAzYE',564,6729176028,'buffer/2025/11/23/6923296b7d2e9.jpg','2025-11-23 20:34:03',NULL,'','',NULL,0,0,0,'2025-11-23 20:34:03','2025-11-23 20:34:03'),(128,'AgACAgEAAxkBAAIBSWkiCFyHvMhEf5z2EzCiPdoe9hooAAJzC2sbVZwQRQW2pp1a4PUxAQADAgADeQADNgQ',566,6729176028,'buffer/2025/11/23/6923296f6f7e8.jpg','2025-11-23 20:34:07',NULL,'','',NULL,0,0,0,'2025-11-23 20:34:07','2025-11-23 20:34:07'),(129,'AgACAgEAAxkBAAIBGGkiB-WKFIYraxADkaO-QbrFew-LAAJ6C2sbVZwQRYuxICqGdfldAQADAgADeQADNgQ',567,6729176028,'buffer/2025/11/23/6923297190a5c.jpg','2025-11-23 20:34:09',NULL,'','',NULL,0,0,0,'2025-11-23 20:34:09','2025-11-23 20:34:09'),(130,'AgACAgEAAxkBAAIBF2kiB-VL548I8C_Equ7e7tTMHIBBAAJ7C2sbVZwQRSRRNfoZ7TCeAQADAgADeQADNgQ',568,6729176028,'buffer/2025/11/23/6923297302988.jpg','2025-11-23 20:34:11',NULL,'','',NULL,0,0,0,'2025-11-23 20:34:11','2025-11-23 20:34:11'),(131,'AgACAgEAAxkBAAIBHmkiB-VARAXjId8rk0s_KhQYDeVNAAJ0C2sbVZwQReNFTSsdLEfwAQADAgADeQADNgQ',569,6729176028,'buffer/2025/11/23/6923297515081.jpg','2025-11-23 20:34:13',NULL,'','',NULL,0,0,0,'2025-11-23 20:34:13','2025-11-23 20:34:13'),(132,'AgACAgEAAxkBAAIBFmkiB-WiMre3qP9zp-4BiKFZQX3LAAJ8C2sbVZwQRV2jtPZNefgGAQADAgADeQADNgQ',570,6729176028,'buffer/2025/11/23/69232977e97fb.jpg','2025-11-23 20:34:15',NULL,'','',NULL,0,0,0,'2025-11-23 20:34:15','2025-11-23 20:34:15'),(133,'AgACAgEAAxkBAAIBE2kiB-VIdm2vFSdptDN2WH0iy8WwAAJ9C2sbVZwQRd4trOGD0UHsAQADAgADeQADNgQ',571,6729176028,'buffer/2025/11/23/6923297963094.jpg','2025-11-23 20:34:17',NULL,'','',NULL,0,0,0,'2025-11-23 20:34:17','2025-11-23 20:34:17'),(134,'AgACAgEAAxkBAAIBCGkiBP82PgF0kl-aF9HchC8rkOl_AAJ-C2sbVZwQRYqJDFi_YhZ9AQADAgADeQADNgQ',572,6729176028,'buffer/2025/11/23/6923297b80e36.jpg','2025-11-23 20:34:19',NULL,'','',NULL,0,0,0,'2025-11-23 20:34:19','2025-11-23 20:34:19');
/*!40000 ALTER TABLE `photo_buffers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `photos`
--

DROP TABLE IF EXISTS `photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `photos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `photo_batch_id` bigint unsigned NOT NULL,
  `file_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message_id` bigint NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_main` tinyint(1) NOT NULL DEFAULT '0',
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `photos_photo_batch_id_foreign` (`photo_batch_id`),
  CONSTRAINT `photos_photo_batch_id_foreign` FOREIGN KEY (`photo_batch_id`) REFERENCES `photo_batches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `photos`
--

LOCK TABLES `photos` WRITE;
/*!40000 ALTER TABLE `photos` DISABLE KEYS */;
INSERT INTO `photos` VALUES (4,3,'AgACAgEAAxkBAAIBE2kiB-VIdm2vFSdptDN2WH0iy8WwAAJ9C2sbVZwQRd4trOGD0UHsAQADAgADeQADNgQ',440,'buffer/2025/11/23/6922676b69fce.jpg','2025-11-23 06:46:19',0,0,1,'2025-11-23 06:49:31','2025-11-23 06:49:31'),(5,3,'AgACAgEAAxkBAAIBFmkiB-WiMre3qP9zp-4BiKFZQX3LAAJ8C2sbVZwQRV2jtPZNefgGAQADAgADeQADNgQ',441,'buffer/2025/11/23/6922676cc636c.jpg','2025-11-23 06:46:20',0,0,2,'2025-11-23 06:49:31','2025-11-23 06:49:31'),(6,3,'AgACAgEAAxkBAAIBF2kiB-VL548I8C_Equ7e7tTMHIBBAAJ7C2sbVZwQRSRRNfoZ7TCeAQADAgADeQADNgQ',442,'buffer/2025/11/23/6922676e33582.jpg','2025-11-23 06:46:22',0,0,3,'2025-11-23 06:49:31','2025-11-23 06:49:31'),(7,3,'AgACAgEAAxkBAAIBCGkiBP82PgF0kl-aF9HchC8rkOl_AAJ-C2sbVZwQRYqJDFi_YhZ9AQADAgADeQADNgQ',443,'buffer/2025/11/23/6922676fa4461.jpg','2025-11-23 06:46:23',0,0,4,'2025-11-23 06:49:31','2025-11-23 06:49:31'),(12,3,'fashn_6922971ab93eb',0,'fashn_6922971ab9070.jpg','2025-11-23 05:09:46',0,0,0,'2025-11-23 10:09:46','2025-11-23 10:09:46'),(13,3,'fashn_6922972a1ba34',0,'fashn_6922972a1b72f.jpg','2025-11-23 05:10:02',0,0,0,'2025-11-23 10:10:02','2025-11-23 10:10:02'),(17,3,'fashn_6922983b1b7dd',0,'fashn_6922983b1b348.jpg','2025-11-23 05:14:35',0,0,0,'2025-11-23 10:14:35','2025-11-23 10:14:35'),(18,4,'magic_69229b561990a',0,'magic_69229b561970e.jpg','2025-11-23 05:27:50',0,0,0,'2025-11-23 10:27:50','2025-11-23 10:37:31'),(21,4,'fashn_69229d959fcdb',0,'fashn_69229d959f4b5.jpg','2025-11-23 05:37:25',1,0,0,'2025-11-23 10:37:25','2025-11-23 10:37:31'),(22,5,'AgACAgEAAxkBAAIBCGkiBP82PgF0kl-aF9HchC8rkOl_AAJ-C2sbVZwQRYqJDFi_YhZ9AQADAgADeQADNgQ',464,'buffer/2025/11/23/69231b15a5f3d.jpg','2025-11-23 19:32:53',1,0,0,'2025-11-23 19:35:12','2025-11-23 19:35:12'),(23,5,'AgACAgEAAxkBAAIBE2kiB-VIdm2vFSdptDN2WH0iy8WwAAJ9C2sbVZwQRd4trOGD0UHsAQADAgADeQADNgQ',465,'buffer/2025/11/23/69231b1734ca0.jpg','2025-11-23 19:32:55',0,0,1,'2025-11-23 19:35:12','2025-11-23 19:35:12'),(24,5,'AgACAgEAAxkBAAIBFmkiB-WiMre3qP9zp-4BiKFZQX3LAAJ8C2sbVZwQRV2jtPZNefgGAQADAgADeQADNgQ',466,'buffer/2025/11/23/69231b19903de.jpg','2025-11-23 19:32:57',0,0,2,'2025-11-23 19:35:12','2025-11-23 19:35:12'),(25,5,'AgACAgEAAxkBAAIBF2kiB-VL548I8C_Equ7e7tTMHIBBAAJ7C2sbVZwQRSRRNfoZ7TCeAQADAgADeQADNgQ',467,'buffer/2025/11/23/69231b1b2e408.jpg','2025-11-23 19:32:59',0,0,3,'2025-11-23 19:35:12','2025-11-23 19:35:12'),(26,5,'AgACAgEAAxkBAAIBGGkiB-WKFIYraxADkaO-QbrFew-LAAJ6C2sbVZwQRYuxICqGdfldAQADAgADeQADNgQ',482,'buffer/2025/11/23/69231b36d4f27.jpg','2025-11-23 19:33:26',0,0,4,'2025-11-23 19:35:12','2025-11-23 19:35:12'),(27,5,'magic_69231bdb7064a',0,'magic_69231bdb70408.jpg','2025-11-23 14:36:11',0,0,0,'2025-11-23 19:36:11','2025-11-23 19:36:11'),(28,5,'magic_69231c63b470e',0,'magic_69231c63b4494.jpg','2025-11-23 14:38:27',0,0,0,'2025-11-23 19:38:27','2025-11-23 19:38:27'),(29,5,'magic_69231da582c9c',0,'magic_69231da582a0d.jpg','2025-11-23 14:43:49',0,0,0,'2025-11-23 19:43:49','2025-11-23 19:43:49'),(30,6,'AgACAgEAAxkBAAIB6GkjKL1-PW-T1YeU08ezfythmHzKAAIcC2sbVZwQRSscqTux0gGYAQADAgADeQADNgQ',488,'buffer/2025/11/23/692328bf44760.jpg','2025-11-23 20:31:11',0,0,0,'2025-11-23 22:54:16','2025-11-23 23:07:40'),(31,6,'AgACAgEAAxkBAAIB6WkjKL3eZDVbmWDXIppY40HVO-2iAAIdC2sbVZwQRQF35rOl6T52AQADAgADeQADNgQ',489,'buffer/2025/11/23/692328c1765f8.jpg','2025-11-23 20:31:13',0,0,1,'2025-11-23 22:54:16','2025-11-23 23:07:40'),(32,6,'AgACAgEAAxkBAAIB6mkjKL1kaqetdLkCW-5xjw3Szb1VAAIeC2sbVZwQRYfrpYq2d79tAQADAgADeQADNgQ',490,'buffer/2025/11/23/692328c36d05c.jpg','2025-11-23 20:31:15',0,0,2,'2025-11-23 22:54:16','2025-11-23 23:07:40'),(33,6,'AgACAgEAAxkBAAIB62kjKL2S61O9AAGr3WGBub22ccDRZgACHwtrG1WcEEVrVuDLbAim4gEAAwIAA3kAAzYE',491,'buffer/2025/11/23/692328c5585ae.jpg','2025-11-23 20:31:17',0,0,3,'2025-11-23 22:54:16','2025-11-23 23:07:40'),(34,6,'AgACAgEAAxkBAAIB7GkjKL3y2Z8HuyFNYBejqScAARUbTQACIAtrG1WcEEXrvlGx_7eftAEAAwIAA3kAAzYE',492,'buffer/2025/11/23/692328c79221e.jpg','2025-11-23 20:31:19',0,0,4,'2025-11-23 22:54:16','2025-11-23 23:07:40'),(35,6,'magic_69234c7c236b8',0,'magic_69234c7c22eeb.jpg','2025-11-23 18:03:40',1,1,0,'2025-11-23 23:03:40','2025-11-24 00:06:48'),(36,7,'AgACAgEAAxkBAAIBPWkiCFxGa4J7SU79amW84eltgBWjAAJwC2sbVZwQRY7jb3uER0LNAQADAgADeQADNgQ',556,'buffer/2025/11/23/6923295e372e3.jpg','2025-11-23 20:33:50',1,0,0,'2025-11-25 01:58:45','2025-11-25 01:58:45'),(37,7,'AgACAgEAAxkBAAIBUWkiCFw5Mf5I3taKm5-KlqlZmglLAAJxC2sbVZwQReBHyCVjGhvzAQADAgADeQADNgQ',559,'buffer/2025/11/23/692329633c4a2.jpg','2025-11-23 20:33:55',0,0,1,'2025-11-25 01:58:45','2025-11-25 01:58:45'),(38,7,'AgACAgEAAxkBAAIBTmkiCFytlPRukaa0mA5Z2EDz4ndaAAJyC2sbVZwQRbxnXVim0bDIAQADAgADeQADNgQ',561,'buffer/2025/11/23/69232967ac9f9.jpg','2025-11-23 20:33:59',0,0,2,'2025-11-25 01:58:45','2025-11-25 01:58:45'),(39,7,'magic_6924c76a1c5d4',0,'magic_6924c76a1c05a.jpg','2025-11-24 21:00:26',0,1,0,'2025-11-25 02:00:26','2025-11-25 02:00:42'),(40,8,'AgACAgEAAxkBAAIBQWkiCFyl4Lecf3BbStG7hrVKjWuPAAJjC2sbVZwQRQdkQ-EDU2zGAQADAgADeQADNgQ',541,'buffer/2025/11/23/692329418fc83.jpg','2025-11-23 20:33:21',1,0,0,'2025-11-25 02:01:15','2025-11-25 02:01:15'),(41,8,'AgACAgEAAxkBAAIBPmkiCFy3pMQC_p36-qdo3p5zF_5IAAJkC2sbVZwQReD6mhxFBZldAQADAgADeQADNgQ',542,'buffer/2025/11/23/692329441ecc7.jpg','2025-11-23 20:33:24',0,0,1,'2025-11-25 02:01:15','2025-11-25 02:01:15'),(42,8,'AgACAgEAAxkBAAIBVGkiCFzoS2OS1TYks09f7aAUGUGXAAJlC2sbVZwQRWYCxq_i1D7mAQADAgADeQADNgQ',543,'buffer/2025/11/23/69232945a92a4.jpg','2025-11-23 20:33:25',0,0,2,'2025-11-25 02:01:15','2025-11-25 02:01:15'),(43,9,'AgACAgEAAxkBAAIBT2kiCFz7-gHVuzFkTXXpZzo7lgYnAAJfC2sbVZwQRfbxoTu-QarXAQADAgADeQADNgQ',537,'buffer/2025/11/23/6923293912423.jpg','2025-11-23 20:33:13',1,0,0,'2025-11-25 02:04:11','2025-11-25 02:04:11'),(44,9,'AgACAgEAAxkBAAIBS2kiCFyweWhR82g8khfCButMvE41AAJgC2sbVZwQRawSTPEq7fmTAQADAgADeQADNgQ',538,'buffer/2025/11/23/6923293b07f1c.jpg','2025-11-23 20:33:15',0,0,1,'2025-11-25 02:04:11','2025-11-25 02:04:11'),(45,9,'AgACAgEAAxkBAAIBR2kiCFx7Mgmo4BWeDSuudbwpwWEjAAJhC2sbVZwQRdB3VLZxDv02AQADAgADeQADNgQ',539,'buffer/2025/11/23/6923293da34d1.jpg','2025-11-23 20:33:17',0,0,2,'2025-11-25 02:04:11','2025-11-25 02:04:11'),(46,9,'AgACAgEAAxkBAAIBRGkiCFwcLIiSZ7hfoNS5N3en6uquAAJiC2sbVZwQRZa14k6UMyCnAQADAgADeQADNgQ',540,'buffer/2025/11/23/6923294034498.jpg','2025-11-23 20:33:20',0,0,3,'2025-11-25 02:04:11','2025-11-25 02:04:11'),(47,9,'magic_6924c8a56f66d',0,'magic_6924c8a56f0e0.jpg','2025-11-24 21:05:41',0,1,0,'2025-11-25 02:05:41','2025-11-25 02:06:31'),(48,10,'AgACAgEAAxkBAAICE2kjKMIJNwt0iQor-AU3ca5LG_OIAAJZC2sbVZwQRWV4-cnp9ugAAQEAAwIAA3kAAzYE',531,'buffer/2025/11/23/6923292c02e42.jpg','2025-11-23 20:33:00',1,0,0,'2025-11-25 02:07:21','2025-11-25 02:07:21'),(49,10,'AgACAgEAAxkBAAICFGkjKML1Q0dQd9v9zRbLYLsn8ukTAAJaC2sbVZwQRXPoGUfaynWVAQADAgADeQADNgQ',532,'buffer/2025/11/23/6923292eb1e3f.jpg','2025-11-23 20:33:02',0,0,1,'2025-11-25 02:07:21','2025-11-25 02:07:21'),(50,10,'AgACAgEAAxkBAAICFWkjKMLbdGxtowezXhViKEYeabTlAAJbC2sbVZwQRae3fmIQT0-EAQADAgADeQADNgQ',533,'buffer/2025/11/23/692329313ce67.jpg','2025-11-23 20:33:05',0,0,2,'2025-11-25 02:07:21','2025-11-25 02:07:21'),(51,10,'AgACAgEAAxkBAAICFmkjKMKrzqOf1NzxWGARJe1Vy211AAJcC2sbVZwQRW-dbnqJBcP6AQADAgADeQADNgQ',534,'buffer/2025/11/23/692329334d297.jpg','2025-11-23 20:33:07',0,0,3,'2025-11-25 02:07:21','2025-11-25 02:07:21'),(52,10,'AgACAgEAAxkBAAICF2kjKML-vCsUt1_qMOTptJXSpjueAAJdC2sbVZwQRcMEF2DWhIcPAQADAgADeQADNgQ',535,'buffer/2025/11/23/692329359a04e.jpg','2025-11-23 20:33:09',0,0,4,'2025-11-25 02:07:21','2025-11-25 02:07:21'),(53,10,'AgACAgEAAxkBAAIBUmkiCFwsKtfh4vtihwLN3yJaDcm3AAJeC2sbVZwQRaVC4iTWi478AQADAgADeQADNgQ',536,'buffer/2025/11/23/6923293795f82.jpg','2025-11-23 20:33:11',0,0,5,'2025-11-25 02:07:21','2025-11-25 02:07:21'),(54,10,'magic_6924c9c1dd3b5',0,'magic_6924c9c1dd0d2.jpg','2025-11-24 21:10:25',0,1,0,'2025-11-25 02:10:25','2025-11-25 02:10:58'),(55,11,'AgACAgEAAxkBAAIB92kjKL7bt2d_MfWFfIQOkkmDRdNgAAI7C2sbVZwQRcgyV2y-R5gVAQADAgADeQADNgQ',503,'buffer/2025/11/23/692328e49a82a.jpg','2025-11-23 20:31:48',1,0,0,'2025-11-25 02:15:03','2025-11-25 02:15:03'),(56,11,'AgACAgEAAxkBAAIB-GkjKL7PU66w_skSrkfmh3gWiFnPAAI8C2sbVZwQRYTcG29x-UeeAQADAgADeQADNgQ',504,'buffer/2025/11/23/692328e6852e6.jpg','2025-11-23 20:31:50',0,0,1,'2025-11-25 02:15:03','2025-11-25 02:15:03'),(57,11,'AgACAgEAAxkBAAIB-WkjKL7z9JPnR8iP_-oq_6-D_f4WAAI9C2sbVZwQRatUFFErYujOAQADAgADeQADNgQ',505,'buffer/2025/11/23/692328e9adfe8.jpg','2025-11-23 20:31:53',0,0,2,'2025-11-25 02:15:03','2025-11-25 02:15:03'),(58,11,'AgACAgEAAxkBAAIB-mkjKL5ZeXhda2zv0l2jAAF5q1GD6wACPgtrG1WcEEWT7IzDE3n9PAEAAwIAA3kAAzYE',506,'buffer/2025/11/23/692328ec85574.jpg','2025-11-23 20:31:56',0,0,3,'2025-11-25 02:15:03','2025-11-25 02:15:03'),(59,11,'magic_6924d2bab6b85',0,'magic_6924d2bab6928.jpg','2025-11-24 21:48:42',0,0,0,'2025-11-25 02:48:42','2025-11-25 02:48:42');
/*!40000 ALTER TABLE `photos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `processing_tasks`
--

DROP TABLE IF EXISTS `processing_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `processing_tasks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `photo_id` bigint unsigned NOT NULL,
  `api_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','processing','completed','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `request_data` json DEFAULT NULL,
  `response_data` json DEFAULT NULL,
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `processing_tasks_photo_id_foreign` (`photo_id`),
  CONSTRAINT `processing_tasks_photo_id_foreign` FOREIGN KEY (`photo_id`) REFERENCES `photos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `processing_tasks`
--

LOCK TABLES `processing_tasks` WRITE;
/*!40000 ALTER TABLE `processing_tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `processing_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_photos`
--

DROP TABLE IF EXISTS `product_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_photos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_photos`
--

LOCK TABLES `product_photos` WRITE;
/*!40000 ALTER TABLE `product_photos` DISABLE KEYS */;
INSERT INTO `product_photos` VALUES (1,1,'magic_69234c7c22eeb.jpg',0,'2025-11-24 00:07:44','2025-11-24 00:07:44'),(2,2,'magic_69234c7c22eeb.jpg',0,'2025-11-25 01:53:17','2025-11-25 01:53:17'),(3,3,'magic_6924c76a1c05a.jpg',0,'2025-11-25 02:00:47','2025-11-25 02:00:47'),(4,4,'buffer/2025/11/23/6923293912423.jpg',0,'2025-11-25 02:05:00','2025-11-25 02:05:00'),(5,4,'buffer/2025/11/23/6923293b07f1c.jpg',1,'2025-11-25 02:05:00','2025-11-25 02:05:00'),(6,4,'buffer/2025/11/23/6923293da34d1.jpg',2,'2025-11-25 02:05:00','2025-11-25 02:05:00'),(7,4,'buffer/2025/11/23/6923294034498.jpg',3,'2025-11-25 02:05:00','2025-11-25 02:05:00'),(8,5,'magic_6924c8a56f0e0.jpg',0,'2025-11-25 02:06:54','2025-11-25 02:06:54'),(9,6,'buffer/2025/11/23/6923292c02e42.jpg',0,'2025-11-25 02:08:05','2025-11-25 02:08:05'),(10,6,'buffer/2025/11/23/6923292eb1e3f.jpg',1,'2025-11-25 02:08:05','2025-11-25 02:08:05'),(11,6,'buffer/2025/11/23/692329313ce67.jpg',2,'2025-11-25 02:08:05','2025-11-25 02:08:05'),(12,6,'buffer/2025/11/23/692329334d297.jpg',3,'2025-11-25 02:08:05','2025-11-25 02:08:05'),(13,6,'buffer/2025/11/23/692329359a04e.jpg',4,'2025-11-25 02:08:05','2025-11-25 02:08:05'),(14,6,'buffer/2025/11/23/6923293795f82.jpg',5,'2025-11-25 02:08:05','2025-11-25 02:08:05'),(15,7,'magic_6924c9c1dd0d2.jpg',0,'2025-11-25 02:14:22','2025-11-25 02:14:22'),(16,8,'buffer/2025/11/23/692328e49a82a.jpg',0,'2025-11-25 02:15:48','2025-11-25 02:15:48'),(17,8,'buffer/2025/11/23/692328e6852e6.jpg',1,'2025-11-25 02:15:48','2025-11-25 02:15:48'),(18,8,'buffer/2025/11/23/692328e9adfe8.jpg',2,'2025-11-25 02:15:48','2025-11-25 02:15:48'),(19,8,'buffer/2025/11/23/692328ec85574.jpg',3,'2025-11-25 02:15:48','2025-11-25 02:15:48');
/*!40000 ALTER TABLE `product_photos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `photo_batch_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) DEFAULT NULL,
  `brand` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `material` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `condition` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'used',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `products_photo_batch_id_foreign` (`photo_batch_id`),
  CONSTRAINT `products_photo_batch_id_foreign` FOREIGN KEY (`photo_batch_id`) REFERENCES `photo_batches` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,6,'Набор спортивных носков UFC (3 пары), Черный/Белый, Единый размер, Хлопок/Полиэстер','Новый набор оригинальных спортивных носков от официального бренда UFC. Идеально подходят для интенсивных тренировок, занятий ММА или повседневной носки. Набор включает три пары: две пары в черно-белой расцветке (Style 12BLK2166) и одну пару в бело-черной расцветке (Style 12WHT2163). \n\nМатериал обеспечивает высокий уровень комфорта и долговечности: 69% хлопок, 29% полиэстер и 2% спандекс. Хлопок гарантирует мягкость и воздухопроницаемость, а полиэстер и спандекс — эластичность и быстрое высыхание. Размер универсальный (One Size). Товар новый, в заводской упаковке.',18.00,'UFC','Одежда и аксессуары, Носки','One Size','Ассорти (Черный и Белый)',NULL,'new','published','2025-11-24 00:07:44','2025-11-24 00:09:31'),(2,6,'Набор спортивных носков UFC (3 пары), Черный/Белый, Единый размер, Хлопок/Полиэстер','Новый набор оригинальных спортивных носков от официального бренда UFC. Идеально подходят для интенсивных тренировок, занятий ММА или повседневной носки. Набор включает три пары: две пары в черно-белой расцветке (Style 12BLK2166) и одну пару в бело-черной расцветке (Style 12WHT2163). \n\nМатериал обеспечивает высокий уровень комфорта и долговечности: 69% хлопок, 29% полиэстер и 2% спандекс. Хлопок гарантирует мягкость и воздухопроницаемость, а полиэстер и спандекс — эластичность и быстрое высыхание. Размер универсальный (One Size). Товар новый, в заводской упаковке.',18.00,'UFC','Одежда и аксессуары, Носки','One Size','Ассорти (Черный и Белый)',NULL,'new','published','2025-11-25 01:53:17','2025-11-25 01:53:17'),(3,7,'Набор из 3 пар носков Stance L (M 9-12) — Серый, Синий, Белый | Хлопковый бленд','Отличный набор из трех пар высококачественных носков Stance, идеально подходящих для повседневной носки. В комплект входят три пары разных стилей и цветов: серые (Crew, Mid Cushion), темно-синие (No Show, Light Cushion) и белые (Crew/Casual). Все носки изготовлены из фирменного смесового чесаного хлопка Stance, обеспечивающего максимальный комфорт, долговечность и воздухопроницаемость. Размер L соответствует мужскому размеру US 9-12. Носки Stance известны своим качеством, поддержкой свода стопы и стильным дизайном. Товар новый, с оригинальными бирками.',30.00,'Stance','Одежда, Аксессуары, Носки','L (US M 9-12)','Ассорти (Серый, Темно-синий, Белый)',NULL,'new','published','2025-11-25 02:00:47','2025-11-25 02:00:47'),(4,9,'Носки рабочие Dan Post Work & Outdoor, 2 пары, размер 10.5-13, Сделано в США, Бежевые','Продаются новые высококачественные рабочие носки Dan Post Work & Outdoor (упаковка из 2 пар). \n\nХарактеристики товара:\n- Бренд: Dan Post\n- Модель: Work & Outdoor High-Performance Socks\n- Размер: Подходит для размера ботинок (Boot Sizes) 10 1/2 - 13\n- Цвет: Бежевый (Natural/Sand)\n- Страна производства: Сделано в США (Made in USA)\n- Тип: Средняя плотность (Medium Weight), длина до середины икры (Mid-Calf)\n\nОсобенности:\n- Широкая резинка, носки не сползают.\n- Превосходный отвод влаги (Moisture Management).\n- Антимикробная защита от запаха и бактерий.\n- Ребристая поддержка свода стопы для комфорта.\n- Амортизирующая подошва.\n- Плоский шов на мыске (Low-Profile Toe Seam).\n- Усиленные пятка и мысок для долговечности.\n- Нить Endurall для снижения трения.\n\nСостав: Смесовая ткань (Хлопок Ring Spun, Полиэстер Sorbtek, Нейлон, Спандекс). Отличный выбор для тяжелой работы и активного отдыха.',22.00,'Dan Post','Носки','10 1/2 - 13','Бежевый',NULL,'new','draft','2025-11-25 02:05:00','2025-11-25 02:05:00'),(5,9,'Dan Post Work & Outdoor Высокоэффективные Рабочие Носки Mid-Calf, 2 Пары, Размер 10.5-13','Носки Dan Post Work & Outdoor High-Performance Socks средней плотности (Medium Weight) с продвинутым дизайном, превосходящим обычные рабочие носки. Идеально подходят для длительной работы и активного отдыха. Особенности включают: широкая ребристая резинка для надежной фиксации, превосходный контроль влажности, антимикробная защита и контроль запаха, усиленная поддержка свода стопы (Arch Support), гладкий шов на мыске (Lycra Toe Seam). Усиленная пятка и мысок обеспечивают долговечность и снижение трения. Сделано в США. В комплекте 2 пары. Размер подходит для обуви 10 1/2 - 13.',20.00,'DAN POST','Носки для работы и активного отдыха','10 1/2 - 13','Песочный/Бежевый',NULL,'new','published','2025-11-25 02:06:54','2025-11-25 02:06:54'),(7,10,'Большой Лот Носков (7+ пар): UFC, Jordan, Massimo Dutti. Новые, Универсальный Размер','Смешанный лот новых носков от известных спортивных и модных брендов, идеально подходящий для повседневной носки и тренировок. Лот включает несколько пар носков UFC (белые и черно-белые, высокие и укороченные модели), черные носки Massimo Dutti и черные носки Jordan с логотипом. Все товары новые, с оригинальными бирками. Носки UFC и Massimo Dutti изготовлены преимущественно из смеси хлопка, полиэстера/полиамида и спандекса (эластана), обеспечивая комфорт, воздухопроницаемость и эластичность. Отличный набор для пополнения гардероба или перепродажи.',30.00,'Смешанный лот (UFC, Jordan, Massimo Dutti)','Одежда и Аксессуары / Носки','Универсальный размер / Смешанные размеры','Белый, Черный (Смешанный)',NULL,'new','published','2025-11-25 02:14:22','2025-11-25 02:14:22'),(8,11,'Лот 4 пары носков Stance Icon No Show - Розовые - Размеры S и L - Хлопок - Новые','Продается лот из 4 пар носков бренда Stance, модель Icon No Show. \n\nХарактеристики:\n- Бренд: Stance\n- Модель: Icon No Show (A145A21INS)\n- Цвет: Розовый (Pink / PNK) с синей полоской на мыске\n- Материал: 57% гребенной хлопок, 39% нейлон, 4% эластан\n- Тип: Следки (невидимки), легкая амортизация (Light Cushion)\n- Состояние: Новые с бирками\n\nВ комплекте 4 пары:\n- 3 пары размера S (US Men\'s 3-5.5 / Women\'s 5-7.5)\n- 1 пара размера L (US Men\'s 9-12)\n\nОтличные качественные носки для повседневной носки.',28.00,'Stance','Носки','S, L','Розовый',NULL,'new','draft','2025-11-25 02:15:48','2025-11-25 02:15:48');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prompts`
--

DROP TABLE IF EXISTS `prompts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prompts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prompt` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'gpt-4o',
  `max_tokens` int NOT NULL DEFAULT '2000',
  `temperature` double NOT NULL DEFAULT '0.3',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prompts_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prompts`
--

LOCK TABLES `prompts` WRITE;
/*!40000 ALTER TABLE `prompts` DISABLE KEYS */;
INSERT INTO `prompts` VALUES (1,'generate_summary','Генерация описания товара','Analyze the product images and provide a detailed description for selling on eBay/marketplace.\n\nBarcodes found: {barcodes}\nGG Labels: {gg_labels}\n\nPlease provide:\n1. Product title (brand + model + key features)\n2. Detailed description\n3. Suggested category\n4. Condition assessment\n5. Key specifications (size, color, material if visible)\n6. Suggested price range based on product type\n\nFormat the response as JSON with keys: title, description, category, condition, brand, size, color, price_min, price_max','gpt-4o',2000,0.3,'2025-11-23 06:27:18','2025-11-23 06:27:18'),(2,'analyze_barcode','Анализ штрихкода','Look at this product barcode/label image and extract all text information including:\n- UPC/EAN barcode numbers\n- SKU codes\n- Model numbers\n- Size information\n- Any other identifying text\n\nReturn as JSON with keys: barcode, sku, model, size, other_text','gpt-4o',500,0.1,'2025-11-23 06:27:18','2025-11-23 06:27:18');
/*!40000 ALTER TABLE `prompts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin','admin@admin.com',NULL,'$2y$12$yna8GOO37cpOdmJHJfVlZ.uSWKE6CzrKN0uj55C55ZywYFraAADlS','CDOucCXcPoYNBCxWmpvvTHjPaSQLLgibrv5bw0oszqC1TrH4NwD6SDVeY93T','2025-11-23 04:29:55','2025-11-23 04:32:17');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'adminka_garage'
--
SET @@SESSION.SQL_LOG_BIN = @MYSQLDUMP_TEMP_LOG_BIN;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-24 17:06:24
