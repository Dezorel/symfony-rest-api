/*
 Navicat Premium Data Transfer

 Source Server         : notebook-server
 Source Server Type    : MySQL
 Source Server Version : 100521
 Source Host           : localhost:3306
 Source Schema         : API_DB

 Target Server Type    : MySQL
 Target Server Version : 100521
 File Encoding         : 65001

 Date: 12/12/2023 22:00:05
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for Authors
-- ----------------------------
DROP TABLE IF EXISTS `Authors`;
CREATE TABLE `Authors`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `create_date` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 36362 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for Books
-- ----------------------------
DROP TABLE IF EXISTS `Books`;
CREATE TABLE `Books`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `author_id` bigint UNSIGNED NOT NULL,
  `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `price` float NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `book_author_id`(`author_id`) USING BTREE,
  CONSTRAINT `book_author_id` FOREIGN KEY (`author_id`) REFERENCES `Authors` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1000001 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
