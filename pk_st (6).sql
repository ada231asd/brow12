-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Май 18 2025 г., 12:12
-- Версия сервера: 5.7.39-log
-- Версия PHP: 8.1.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `pk_st`
--

DELIMITER $$
--
-- Процедуры
--
CREATE DEFINER=`root`@`%` PROCEDURE `UpdateUserStatus` (IN `p_user_id` INT)   BEGIN
    DECLARE active_session_count INT;

    -- Проверяем, есть ли активные сессии для пользователя
    SELECT COUNT(*) INTO active_session_count
    FROM User_Sessions
    WHERE user_id = p_user_id
    AND expires_at > NOW();

    -- Обновляем статус пользователя
    IF active_session_count > 0 THEN
        UPDATE Users
        SET status = 'Онлайн'
        WHERE user_id = p_user_id;
    ELSE
        UPDATE Users
        SET status = 'Не в сети'
        WHERE user_id = p_user_id;
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблицы `Admin_Logs`
--

CREATE TABLE `Admin_Logs` (
  `log_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `table_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `action_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `Admin_Logs`
--

INSERT INTO `Admin_Logs` (`log_id`, `admin_id`, `action`, `created_at`, `table_name`, `record_id`, `action_type`) VALUES
(1, 6, 'Обновил продукт ID 4', '2025-04-10 17:10:16', NULL, NULL, 'UPDATE'),
(2, 6, 'Обновил продукт ID 4', '2025-04-10 17:11:57', NULL, NULL, 'UPDATE'),
(3, 6, 'Обновил продукт ID 4', '2025-04-10 17:55:02', NULL, NULL, 'UPDATE'),
(4, 6, 'Обновил продукт ID 4', '2025-04-10 18:01:34', NULL, NULL, 'UPDATE'),
(5, 6, 'Обновил продукт ID 4', '2025-04-10 18:19:04', NULL, NULL, 'UPDATE'),
(6, 6, 'Обновил пользователя ID 6', '2025-04-10 18:20:06', NULL, NULL, 'UPDATE'),
(7, 6, 'Обновил пользователя ID 3', '2025-04-10 18:20:16', NULL, NULL, 'UPDATE'),
(8, 6, 'Обновил заказ ID 1', '2025-04-10 18:20:45', NULL, NULL, 'UPDATE'),
(9, 6, 'Обновил заказ ID 1', '2025-04-10 18:20:57', NULL, NULL, 'UPDATE'),
(10, 6, 'Обновил заказ ID 1', '2025-04-10 18:22:08', NULL, NULL, 'UPDATE'),
(11, 6, 'Обновил заказ ID 2', '2025-04-10 18:25:28', NULL, NULL, 'UPDATE'),
(12, 6, 'Обновил продукт ID 4', '2025-04-10 18:57:04', NULL, NULL, 'UPDATE'),
(13, 6, 'Обновил продукт ID 4', '2025-04-10 18:58:54', NULL, NULL, 'UPDATE'),
(14, 6, 'Обновил продукт ID 4', '2025-04-10 18:59:30', NULL, NULL, 'UPDATE'),
(15, 6, 'Обновил продукт ID 4', '2025-04-10 19:01:14', NULL, NULL, 'UPDATE'),
(16, 6, 'Обновил продукт ID 4', '2025-04-10 19:02:17', NULL, NULL, 'UPDATE'),
(17, 6, 'Обновил продукт ID 4', '2025-04-10 19:03:27', NULL, NULL, 'UPDATE'),
(18, 6, 'Обновил продукт ID 4', '2025-04-10 19:06:40', NULL, NULL, 'UPDATE'),
(19, 6, 'Обновил продукт ID 4', '2025-04-10 19:12:50', NULL, NULL, 'UPDATE'),
(20, 6, 'Обновил продукт ID 4', '2025-04-10 19:15:03', NULL, NULL, 'UPDATE'),
(21, 6, 'Обновил продукт ID 4', '2025-04-10 19:32:01', NULL, NULL, 'UPDATE'),
(22, 6, 'Обновил новость ID 4', '2025-04-10 20:52:07', NULL, NULL, 'UPDATE'),
(23, 6, 'Обновил акцию ID 3', '2025-04-10 20:52:35', NULL, NULL, 'UPDATE'),
(24, 6, 'Добавил продукт ID 54', '2025-04-11 05:40:31', NULL, NULL, 'CREATE'),
(25, 6, 'Обновил продукт ID 4', '2025-04-11 05:41:02', NULL, NULL, 'UPDATE'),
(26, 6, 'Удалил продукт ID 5', '2025-04-11 05:41:07', NULL, NULL, 'DELETE'),
(27, 5, 'Добавил продукт ID 55', '2025-05-01 10:18:08', 'Products', 55, 'CREATE'),
(28, 5, 'Удалил продукт ID 9', '2025-05-01 10:18:28', 'Products', 9, 'DELETE'),
(29, 5, 'Добавил продукт ID 56', '2025-05-01 10:35:48', 'Products', 56, 'CREATE'),
(30, 6, 'Получил список товаров (страница 1)', '2025-05-02 11:43:20', NULL, NULL, 'READ'),
(31, 6, 'Получил список товаров (страница 1)', '2025-05-02 11:43:34', NULL, NULL, 'READ'),
(32, 6, 'Получил список товаров (страница 1)', '2025-05-02 11:43:36', NULL, NULL, 'READ'),
(33, 6, 'Получил список товаров (страница 1)', '2025-05-02 11:43:37', NULL, NULL, 'READ'),
(34, 6, 'Получил список товаров (страница 1)', '2025-05-02 11:43:52', NULL, NULL, 'READ'),
(35, 6, 'Получил список товаров (страница 1)', '2025-05-02 11:43:52', NULL, NULL, 'READ'),
(36, 6, 'Получил список товаров (страница 1)', '2025-05-02 11:44:03', NULL, NULL, 'READ'),
(37, 6, 'Получил список товаров (страница 1)', '2025-05-02 11:49:42', NULL, NULL, 'READ'),
(38, 6, 'Получил список товаров (страница 1)', '2025-05-02 11:49:45', NULL, NULL, 'READ'),
(39, 6, 'Получил список товаров (страница 1)', '2025-05-02 11:51:51', NULL, NULL, 'READ'),
(40, 6, 'Удалил продукт ID 55', '2025-05-02 11:51:56', NULL, NULL, 'DELETE'),
(41, 6, 'Получил список товаров (страница 1)', '2025-05-02 11:51:57', NULL, NULL, 'READ'),
(42, 6, 'Получил список товаров (страница 1)', '2025-05-02 11:53:26', NULL, NULL, 'READ'),
(43, 6, 'Получил список товаров (страница 1)', '2025-05-02 11:53:28', NULL, NULL, 'READ'),
(44, 6, 'Получил список товаров (страница 1)', '2025-05-02 12:21:12', NULL, NULL, 'READ'),
(45, 6, 'Получил список товаров (страница 1)', '2025-05-02 12:30:05', NULL, NULL, 'READ'),
(46, 6, 'Получил список товаров (страница 1)', '2025-05-02 12:30:36', NULL, NULL, 'READ'),
(47, 6, 'Получил список товаров (страница 1)', '2025-05-02 12:30:49', NULL, NULL, 'READ'),
(48, 6, 'Получил список товаров (страница 1)', '2025-05-02 12:30:49', NULL, NULL, 'READ'),
(49, 6, 'Удалил продукт ID 56', '2025-05-02 12:30:54', NULL, NULL, 'DELETE'),
(50, 6, 'Получил список товаров (страница 1)', '2025-05-02 12:30:55', NULL, NULL, 'READ'),
(51, 6, 'Получил список товаров (страница 1)', '2025-05-02 12:30:58', NULL, NULL, 'READ'),
(52, 6, 'Получил список товаров (страница 1)', '2025-05-02 12:30:58', NULL, NULL, 'READ'),
(53, 6, 'Получил список товаров (страница 1)', '2025-05-02 12:33:24', NULL, NULL, 'READ'),
(54, 6, 'Получил список товаров (страница 1)', '2025-05-02 12:39:42', NULL, NULL, 'READ'),
(55, 6, 'Получил список товаров (страница 1)', '2025-05-02 12:46:25', NULL, NULL, 'READ'),
(56, 6, 'Получил список товаров (страница 1)', '2025-05-02 12:46:31', NULL, NULL, 'READ'),
(57, 6, 'Получил список товаров (страница 1)', '2025-05-02 12:46:56', NULL, NULL, 'READ'),
(58, 6, 'Получил список товаров (страница 1)', '2025-05-02 12:46:57', NULL, NULL, 'READ'),
(59, 6, 'Получил список товаров (страница 1)', '2025-05-02 12:46:57', NULL, NULL, 'READ'),
(60, 6, 'Получил список товаров (страница 1)', '2025-05-02 12:46:58', NULL, NULL, 'READ'),
(61, 6, 'Получил список товаров (страница 1)', '2025-05-02 12:46:58', NULL, NULL, 'READ'),
(62, 6, 'Получил список товаров (страница 1)', '2025-05-02 12:46:59', NULL, NULL, 'READ'),
(63, 6, 'Получил список товаров (страница 1)', '2025-05-02 12:46:59', NULL, NULL, 'READ'),
(64, 6, 'Получил список товаров (страница 1)', '2025-05-02 12:47:00', NULL, NULL, 'READ'),
(65, 6, 'Получил список товаров (страница 1)', '2025-05-02 12:47:05', NULL, NULL, 'READ'),
(66, 6, 'Получил список товаров (страница 1)', '2025-05-02 12:47:08', NULL, NULL, 'READ'),
(67, 6, 'Получил список товаров (страница 1)', '2025-05-02 12:47:15', NULL, NULL, 'READ'),
(68, 6, 'Получил список товаров (страница 1)', '2025-05-02 12:47:19', NULL, NULL, 'READ'),
(69, 6, 'Получил список товаров (страница 1)', '2025-05-02 12:47:24', NULL, NULL, 'READ'),
(70, 6, 'Получил список товаров (страница 1)', '2025-05-02 12:47:24', NULL, NULL, 'READ'),
(71, 6, 'Получил список товаров (страница 1)', '2025-05-02 12:47:28', NULL, NULL, 'READ'),
(72, 6, 'Получил список товаров (страница 1)', '2025-05-02 12:47:28', NULL, NULL, 'READ'),
(73, 6, 'Получил список товаров (страница 2)', '2025-05-02 12:47:32', NULL, NULL, 'READ'),
(74, 6, 'Получил список товаров (страница 1)', '2025-05-02 12:47:33', NULL, NULL, 'READ'),
(75, 6, 'Получил список товаров (страница 1)', '2025-05-02 12:51:02', NULL, NULL, 'READ'),
(76, 6, 'Удалил продукт ID 54', '2025-05-02 13:48:27', 'Products', 54, 'DELETE'),
(77, 6, 'Добавил продукт ID 57', '2025-05-02 14:31:49', 'Products', 57, 'CREATE'),
(78, 6, 'Обновил магазин ID 1', '2025-05-02 15:36:03', 'Stores', 1, 'UPDATE'),
(79, 6, 'Удалил магазин ID 1', '2025-05-02 15:36:09', 'Stores', 1, 'DELETE'),
(80, 6, 'Добавил магазин ID 6', '2025-05-02 15:36:50', 'Stores', 6, 'CREATE'),
(81, 6, 'Удалил магазин ID 6', '2025-05-02 15:36:56', 'Stores', 6, 'DELETE'),
(82, 5, 'Просмотр товаров (страница 1)', '2025-05-18 06:48:45', 'Products', NULL, 'VIEW'),
(83, 5, 'Просмотр товаров (страница 1)', '2025-05-18 06:48:58', 'Products', NULL, 'VIEW'),
(84, 5, 'Просмотр товаров (страница 1)', '2025-05-18 06:49:03', 'Products', NULL, 'VIEW'),
(85, 5, 'Просмотр товаров (страница 1)', '2025-05-18 06:53:22', 'Products', NULL, 'VIEW'),
(86, 5, 'Просмотр товаров (страница 1)', '2025-05-18 06:53:43', 'Products', NULL, 'VIEW'),
(87, 5, 'Просмотр товаров (страница 1)', '2025-05-18 06:54:12', 'Products', NULL, 'VIEW'),
(88, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 06:54:12', 'Stores', NULL, 'VIEW'),
(89, 5, 'Просмотр магазинов (страница 1, store_id: 5)', '2025-05-18 06:54:16', 'Stores', 5, 'VIEW'),
(90, 5, 'Просмотр товаров (страница 1)', '2025-05-18 06:55:16', 'Products', NULL, 'VIEW'),
(91, 5, 'Просмотр товаров (страница 1)', '2025-05-18 06:55:42', 'Products', NULL, 'VIEW'),
(92, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 06:55:42', 'Stores', NULL, 'VIEW'),
(93, 5, 'Просмотр товаров (страница 1)', '2025-05-18 06:57:30', 'Products', NULL, 'VIEW'),
(94, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 06:57:30', 'Stores', NULL, 'VIEW'),
(95, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 07:14:58', 'Stores', NULL, 'VIEW'),
(96, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 07:15:11', 'Stores', NULL, 'VIEW'),
(97, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 07:20:19', 'Stores', NULL, 'VIEW'),
(98, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 07:28:14', 'Stores', NULL, 'VIEW'),
(99, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 07:37:38', 'Stores', NULL, 'VIEW'),
(100, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 07:37:46', 'Stores', NULL, 'VIEW'),
(101, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 07:37:46', 'Stores', NULL, 'VIEW'),
(102, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 07:37:46', 'Stores', NULL, 'VIEW'),
(103, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 07:37:46', 'Stores', NULL, 'VIEW'),
(104, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 07:37:47', 'Stores', NULL, 'VIEW'),
(105, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 07:41:08', 'Stores', NULL, 'VIEW'),
(106, 5, 'Просмотр товаров (страница 1)', '2025-05-18 07:42:09', 'Products', NULL, 'VIEW'),
(107, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 07:42:09', 'Stores', NULL, 'VIEW'),
(108, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 07:45:29', 'Stores', NULL, 'VIEW'),
(109, 5, 'Просмотр товаров (страница 1)', '2025-05-18 07:45:29', 'Products', NULL, 'VIEW'),
(110, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 07:47:16', 'Stores', NULL, 'VIEW'),
(111, 5, 'Просмотр товаров (страница 1)', '2025-05-18 07:47:42', 'Products', NULL, 'VIEW'),
(112, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 07:47:42', 'Stores', NULL, 'VIEW'),
(113, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 08:04:25', 'Stores', NULL, 'VIEW'),
(114, 5, 'Просмотр товаров (страница 1)', '2025-05-18 08:04:25', 'Products', NULL, 'VIEW'),
(115, 5, 'Просмотр товаров (страница 1)', '2025-05-18 08:05:17', 'Products', NULL, 'VIEW'),
(116, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 08:05:17', 'Stores', NULL, 'VIEW'),
(117, 5, 'Просмотр товаров (страница 1)', '2025-05-18 08:05:27', 'Products', NULL, 'VIEW'),
(118, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 08:05:27', 'Stores', NULL, 'VIEW'),
(119, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 08:05:33', 'Stores', NULL, 'VIEW'),
(120, 5, 'Просмотр товаров (страница 1)', '2025-05-18 08:05:33', 'Products', NULL, 'VIEW'),
(121, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 08:05:43', 'Stores', NULL, 'VIEW'),
(122, 5, 'Просмотр товаров (страница 1)', '2025-05-18 08:05:43', 'Products', NULL, 'VIEW'),
(123, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 08:11:00', 'Stores', NULL, 'VIEW'),
(124, 5, 'Просмотр товаров (страница 1)', '2025-05-18 08:11:00', 'Products', NULL, 'VIEW'),
(125, 5, 'Просмотр товаров (страница 1)', '2025-05-18 08:11:42', 'Products', NULL, 'VIEW'),
(126, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 08:11:42', 'Stores', NULL, 'VIEW'),
(127, 5, 'Просмотр товаров (страница 1)', '2025-05-18 08:16:40', 'Products', NULL, 'VIEW'),
(128, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 08:16:40', 'Stores', NULL, 'VIEW'),
(129, 5, 'Просмотр товаров (страница 1)', '2025-05-18 08:19:56', 'Products', NULL, 'VIEW'),
(130, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 08:19:56', 'Stores', NULL, 'VIEW'),
(131, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 08:20:33', 'Stores', NULL, 'VIEW'),
(132, 5, 'Просмотр товаров (страница 1)', '2025-05-18 08:20:33', 'Products', NULL, 'VIEW'),
(133, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 08:21:24', 'Stores', NULL, 'VIEW'),
(134, 5, 'Просмотр товаров (страница 1)', '2025-05-18 08:21:24', 'Products', NULL, 'VIEW'),
(135, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 08:23:24', 'Stores', NULL, 'VIEW'),
(136, 5, 'Просмотр товаров (страница 1)', '2025-05-18 08:23:24', 'Products', NULL, 'VIEW'),
(137, 5, 'Просмотр товаров (страница 1)', '2025-05-18 08:23:38', 'Products', NULL, 'VIEW'),
(138, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 08:23:38', 'Stores', NULL, 'VIEW'),
(139, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 08:27:22', 'Stores', NULL, 'VIEW'),
(140, 5, 'Просмотр товаров (страница 1)', '2025-05-18 08:27:22', 'Products', NULL, 'VIEW'),
(141, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 08:27:24', 'Stores', NULL, 'VIEW'),
(142, 5, 'Просмотр товаров (страница 1)', '2025-05-18 08:27:24', 'Products', NULL, 'VIEW'),
(143, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 08:27:32', 'Stores', NULL, 'VIEW'),
(144, 5, 'Просмотр товаров (страница 1)', '2025-05-18 08:27:32', 'Products', NULL, 'VIEW'),
(145, 5, 'Просмотр товаров (страница 1)', '2025-05-18 08:50:21', 'Products', NULL, 'VIEW'),
(146, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 08:50:21', 'Stores', NULL, 'VIEW'),
(147, 5, 'Просмотр магазинов (страница 1, store_id: 5)', '2025-05-18 08:51:22', 'Stores', 5, 'VIEW'),
(148, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 08:51:52', 'Stores', NULL, 'VIEW'),
(149, 5, 'Просмотр товаров (страница 1)', '2025-05-18 08:51:52', 'Products', NULL, 'VIEW'),
(150, 5, 'Просмотр товаров (страница 1)', '2025-05-18 08:52:56', 'Products', NULL, 'VIEW'),
(151, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 08:52:56', 'Stores', NULL, 'VIEW'),
(152, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 08:53:48', 'Stores', NULL, 'VIEW'),
(153, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 08:53:53', 'Stores', NULL, 'VIEW'),
(154, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 08:54:17', 'Stores', NULL, 'VIEW'),
(155, 5, 'Просмотр товаров (страница 1)', '2025-05-18 08:54:17', 'Products', NULL, 'VIEW'),
(156, 5, 'Просмотр товаров (страница 1)', '2025-05-18 08:54:58', 'Products', NULL, 'VIEW'),
(157, 5, 'Просмотр магазинов (страница 1, store_id: все)', '2025-05-18 08:54:58', 'Stores', NULL, 'VIEW');

-- --------------------------------------------------------

--
-- Структура таблицы `Cart`
--

CREATE TABLE `Cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `Cart`
--

INSERT INTO `Cart` (`cart_id`, `user_id`) VALUES
(3, 5),
(2, 6);

-- --------------------------------------------------------

--
-- Структура таблицы `Cart_Items`
--

CREATE TABLE `Cart_Items` (
  `cart_item_id` int(11) NOT NULL,
  `cart_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `Cart_Items`
--

INSERT INTO `Cart_Items` (`cart_item_id`, `cart_id`, `product_id`, `quantity`) VALUES
(4, 3, 2, 1),
(5, 2, 35, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `Categories`
--

CREATE TABLE `Categories` (
  `category_id` int(11) NOT NULL,
  `parent_category_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `Categories`
--

INSERT INTO `Categories` (`category_id`, `parent_category_id`, `name`) VALUES
(1, NULL, 'Компьютеры в сборе'),
(2, NULL, 'Ноутбуки'),
(3, NULL, 'Процессоры'),
(4, NULL, 'Видеокарты'),
(5, NULL, 'Мыши'),
(6, NULL, 'Коврики'),
(7, NULL, 'Оперативная память'),
(8, NULL, 'Накопители');

-- --------------------------------------------------------

--
-- Структура таблицы `Comparison_Items`
--

CREATE TABLE `Comparison_Items` (
  `comparison_item_id` int(11) NOT NULL,
  `comparison_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `Comparison_Items`
--

INSERT INTO `Comparison_Items` (`comparison_item_id`, `comparison_id`, `product_id`) VALUES
(38, 3, 1),
(41, 3, 2);

-- --------------------------------------------------------

--
-- Структура таблицы `Comparison_List`
--

CREATE TABLE `Comparison_List` (
  `comparison_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `Comparison_List`
--

INSERT INTO `Comparison_List` (`comparison_id`, `user_id`) VALUES
(2, 5),
(3, 6);

-- --------------------------------------------------------

--
-- Структура таблицы `Delivery_Status`
--

CREATE TABLE `Delivery_Status` (
  `delivery_status_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `status` enum('Принят','В процессе доставки','Доставлен','Возврат') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `Delivery_Status`
--

INSERT INTO `Delivery_Status` (`delivery_status_id`, `order_id`, `status`, `updated_at`) VALUES
(1, 1, 'Принят', '2025-01-30 13:44:34'),
(2, 2, 'Доставлен', '2025-04-10 18:25:28');

-- --------------------------------------------------------

--
-- Структура таблицы `Favorites`
--

CREATE TABLE `Favorites` (
  `favorite_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `Favorites`
--

INSERT INTO `Favorites` (`favorite_id`, `user_id`, `product_id`) VALUES
(4, 5, 3),
(7, 5, 7),
(8, 5, 1),
(9, 6, 11),
(10, 6, 10),
(12, 6, 2),
(35, 6, 34),
(39, 6, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `Feedback`
--

CREATE TABLE `Feedback` (
  `feedback_id` int(11) NOT NULL,
  `category` enum('Техподдержка','Заказ','Другое') COLLATE utf8mb4_unicode_ci DEFAULT 'Другое',
  `user_id` int(11) DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Зарегистрирован',
  `response` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `Feedback`
--

INSERT INTO `Feedback` (`feedback_id`, `category`, `user_id`, `message`, `status`, `response`, `created_at`) VALUES
(1, 'Заказ', 5, 'Не получил подтверждение заказа №ORDER-0002, хотя деньги списались.', 'В обработке', NULL, '2025-04-15 07:23:45'),
(2, 'Заказ', 7, 'Заказ №ORDER-0001 доставили не по тому адресу. Что делать?', 'В обработке', NULL, '2025-04-16 11:12:33'),
(3, 'Заказ', 8, 'Хочу отменить заказ, но не нахожу такой возможности в личном кабинете.', 'Решен', 'Ваш заказ уже обработан и готовится к отправке. Отмена невозможна.', '2025-04-10 06:45:21'),
(4, 'Техподдержка', 9, 'Не могу зайти в личный кабинет, пишет \"неверный пароль\", хотя пароль правильный.', 'Решен', 'Проблема решена, попробуйте войти снова.', '2025-04-18 13:30:15'),
(5, 'Техподдержка', 10, 'Сайт не отображает изображения товаров в разделе \"Видеокарты\".', 'В обработке', NULL, '2025-04-19 08:20:05'),
(6, 'Техподдержка', 11, 'При попытке оплаты выдает ошибку 500. Как оформить заказ?', 'Решен', 'Проблема с платежным шлюзом устранена.', '2025-04-17 10:15:42'),
(7, 'Другое', 12, 'Когда планируется поступление новых игровых ноутбуков ASUS?', 'Зарегистрирован', NULL, '2025-04-20 09:40:18'),
(8, 'Другое', 13, 'Есть ли у вас программы лояльности или накопительные скидки?', 'Решен', 'Да, у нас есть бонусная программа. Детали отправлены вам на email.', '2025-04-14 14:25:39'),
(9, 'Другое', 14, 'Хочу предложить сотрудничество по поставкам комплектующих. Кому написать?', 'В обработке', NULL, '2025-04-21 12:10:27'),
(10, 'Другое', 15, 'В магазине на Ленина, 10 грубо вел себя сотрудник.', 'Решен', 'Приносим извинения за инцидент. Персонал будет проинструктирован.', '2025-04-13 15:05:14'),
(11, 'Другое', 16, 'Товар пришел с поврежденной упаковкой, хотя заказывал как подарок.', 'В обработке', NULL, '2025-04-22 07:50:33'),
(12, 'Другое', 17, 'Предлагаю добавить фильтр по энергопотреблению для видеокарт.', 'Зарегистрирован', NULL, '2025-04-23 11:35:19'),
(13, 'Другое', 18, 'Хотелось бы видеть больше обзоров товаров от ваших экспертов.', 'Решен', 'Спасибо за предложение! Уже работаем над этим.', '2025-04-12 16:20:45'),
(14, 'Другое', 19, 'Есть ли разница между Intel Core i7-13700K и i7-13700KF кроме встроенной графики?', 'Решен', 'Нет, все остальные характеристики идентичны.', '2025-04-24 08:15:28');

-- --------------------------------------------------------

--
-- Структура таблицы `Logs`
--

CREATE TABLE `Logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action_description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `Logs`
--

INSERT INTO `Logs` (`log_id`, `user_id`, `action_description`, `created_at`) VALUES
(1, 8, 'Оформлен заказ №1', '2025-01-30 16:44:34'),
(2, 19, 'Добавлен товар в избранное.', '2025-01-30 16:44:34'),
(3, 5, 'Успешный вход для bobi@me.ru с IP 127.0.0.1', '2025-05-17 14:18:36'),
(4, 5, 'Успешный вход для bobi@me.ru с IP 127.0.0.1', '2025-05-17 14:18:45'),
(5, 5, 'Успешный вход для bobi@me.ru с IP 127.0.0.1', '2025-05-17 14:26:18'),
(6, NULL, 'Неудачная попытка входа для bobi@me.ru с IP 127.0.0.1', '2025-05-17 14:26:52'),
(7, NULL, 'Неудачная попытка входа для bobi@me.ru с IP 127.0.0.1', '2025-05-17 14:26:53'),
(8, NULL, 'Неудачная попытка входа для bobi@me.ru с IP 127.0.0.1', '2025-05-17 14:27:09'),
(9, NULL, 'Неудачная попытка входа для bob2i@me.ru с IP 127.0.0.1', '2025-05-17 14:30:10'),
(10, NULL, 'Неудачная попытка входа для bob2i@me.ru с IP 127.0.0.1', '2025-05-17 14:30:10'),
(11, NULL, 'Неудачная попытка входа для bobi@me.ru с IP 127.0.0.1', '2025-05-17 14:51:39'),
(12, NULL, 'Неудачная попытка входа для bobi@me.ru с IP 127.0.0.1', '2025-05-17 14:51:40'),
(13, NULL, 'Неудачная попытка входа для bobi@me.ru с IP 127.0.0.1', '2025-05-17 14:51:40'),
(14, 5, 'Успешный вход для bobi@me.ru с IP 127.0.0.1', '2025-05-17 14:52:47'),
(15, 5, 'Успешный вход для bobi@me.ru с IP 127.0.0.1', '2025-05-18 06:26:02'),
(16, 5, 'Успешный вход для bobi@me.ru с IP 127.0.0.1', '2025-05-18 06:58:13'),
(17, 5, 'Успешный вход для bobi@me.ru с IP 127.0.0.1', '2025-05-18 07:01:08'),
(18, 5, 'Успешный вход для bobi@me.ru с IP 127.0.0.1', '2025-05-18 07:07:58'),
(19, 5, 'Успешный вход для bobi@me.ru с IP 127.0.0.1', '2025-05-18 07:10:26'),
(20, 5, 'Успешный вход для bobi@me.ru с IP 127.0.0.1', '2025-05-18 08:05:31'),
(21, 5, 'Успешный вход для bobi@me.ru с IP 127.0.0.1', '2025-05-18 08:11:40'),
(22, 5, 'Успешный вход для bobi@me.ru с IP 127.0.0.1', '2025-05-18 09:05:37');

-- --------------------------------------------------------

--
-- Структура таблицы `News`
--

CREATE TABLE `News` (
  `news_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `image_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT 'full_image\\promotions\\defoult.jpg	',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `News`
--

INSERT INTO `News` (`news_id`, `title`, `content`, `image_url`, `created_at`) VALUES
(1, 'Новые поступления', 'У нас в наличии новые процессоры Intel и AMD.', 'full_image\\promotions\\defoult.jpg	', '2025-01-30 16:44:34'),
(2, 'Скидки на видеокарты', 'Специальные предложения на видеокарты NVIDIA.', 'full_image\\promotions\\defoult.jpg	', '2025-01-30 16:44:34'),
(3, 'Открытие нового магазина', 'Противоположная точка зрения подразумевает, что реплицированные с зарубежных источников, современные исследования набирают популярность среди определённых слоёв населения, а значит, должны быть функционально разнесены на независимые элементы. Каждый из нас понимает очевидную вещь: социально-экономическое развитие влечёт за собой процесс внедрения и модернизации модели развития. \r\nИмеется спорная точка зрения, гласящая примерно следующее: элементы политического процесса представляют собой не что иное, как квинтэссенцию победы маркетинга над разумом и должны быть ассоциативно распределены по отраслям. Безусловно, глубокий уровень погружения предполагает независимые способы реализации своевременного выполнения сверхзадачи. \r\nЛишь многие известные личности будут превращены в посмешище, хотя само их существование приносит несомненную пользу обществу. Учитывая ключевые сценарии поведения, сложившаяся структура организации создаёт предпосылки для системы обучения кадров, соответствующей насущным потребностям. Не следует, однако, забывать, что консультация с широким активом создаёт необходимость включения в производственный план целого ряда внеочередных мероприятий с учётом комплекса экспериментов, поражающих по своей масштабности и грандиозности.', 'full_image\\promotions\\defoult.jpg	', '2025-03-09 19:38:46'),
(4, 'Открытие нового магазина', 'Противоположная точка зрения подразумевает, что реплицированные с зарубежных источников, современные исследования набирают популярность среди определённых слоёв населения, а значит, должны быть функционально разнесены на независимые элементы. Каждый из нас понимает очевидную вещь: социально-экономическое развитие влечёт за собой процесс внедрения и модернизации модели развития. \r\nИмеется спорная точка зрения, гласящая примерно следующее: элементы политического процесса представляют собой не что иное, как квинтэссенцию победы маркетинга над разумом и должны быть ассоциативно распределены по отраслям. Безусловно, глубокий уровень погружения предполагает независимые способы реализации своевременного выполнения сверхзадачи. \r\nЛишь многие известные личности будут превращены в посмешище, хотя само их существование приносит несомненную пользу обществу. Учитывая ключевые сценарии поведения, сложившаяся структура организации создаёт предпосылки для системы обучения кадров, соответствующей насущным потребностям. Не следует, однако, забывать, что консультация с широким активом создаёт необходимость включения в производственный план целого ряда внеочередных мероприятий с учётом комплекса экспериментов, поражающих по своей масштабности и грандиозности.', 'full_image/news/67f82f77ecc35-62muSDn5SECBUyfiVY_jrw0RWgsbg5-w1zt7Ibqy4GuEtyhOqGxDltbV1tWr_qHAF3lsIPpC-jNAknfCz5PoLWTi.jpg', '2025-03-18 05:52:25');

-- --------------------------------------------------------

--
-- Структура таблицы `Orders`
--

CREATE TABLE `Orders` (
  `order_id` int(11) NOT NULL,
  `order_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `user_id` int(11) DEFAULT NULL,
  `order_status` enum('Новый','В обработке','Доставлен','Отменен','Отправлен','В ожидании') COLLATE utf8mb4_unicode_ci NOT NULL,
  `delivery_address` text COLLATE utf8mb4_unicode_ci,
  `total_price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `Orders`
--

INSERT INTO `Orders` (`order_id`, `order_code`, `user_id`, `order_status`, `delivery_address`, `total_price`, `created_at`) VALUES
(1, 'ORDER-0001', 3, 'В обработке', 'Москва, ул. Ленина, д. 10', '95000.00', '2025-01-30 13:44:34'),
(2, 'ORDER-0002', 5, 'Доставлен', 'Москва, ул. Ленина, д. 10', '35000.00', '2025-01-30 13:44:34');

-- --------------------------------------------------------

--
-- Структура таблицы `Order_Items`
--

CREATE TABLE `Order_Items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price_per_item` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `Order_Items`
--

INSERT INTO `Order_Items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `price_per_item`) VALUES
(1, 1, 1, 1, '35000.00'),
(2, 1, 3, 1, '60000.00'),
(3, 2, 1, 1, '35000.00');

-- --------------------------------------------------------

--
-- Структура таблицы `Products`
--

CREATE TABLE `Products` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) DEFAULT '0',
  `image_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT 'full_image\\promotions\\defoult.jpg	',
  `is_bestseller` tinyint(1) DEFAULT '0',
  `is_new` tinyint(1) DEFAULT '0',
  `discount` decimal(5,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `Products`
--

INSERT INTO `Products` (`product_id`, `category_id`, `name`, `description`, `price`, `stock_quantity`, `image_url`, `is_bestseller`, `is_new`, `discount`, `created_at`) VALUES
(1, 3, 'Intel Core i7', 'Процессор Intel Core i7 для ПК', '35000.00', 10, 'full_image\\promotions\\defoult.jpg	', 0, 1, '15.00', '2023-12-31 21:00:00'),
(2, 3, 'AMD Ryzen 5', 'Процессор AMD Ryzen 5 для высоких нагрузок', '25000.00', 15, 'full_image\\promotions\\defoult.jpg	', 0, 1, '15.00', '2024-01-31 21:00:00'),
(3, 4, 'NVIDIA GeForce RTX 3060', 'Видеокарта для гейминга и графики', '60000.00', 5, 'full_image\\promotions\\defoult.jpg	', 0, 1, '0.00', '2024-02-29 21:00:00'),
(4, 3, 'Intel Core i7', 'Процессор Intel Core i7 для ПК', '1.00', 10, 'full_image/produkts/67f81cb160957-62muSDn5SECBUyfiVY_jrw0RWgsbg5-w1zt7Ibqy4GuEtyhOqGxDltbV1tWr_qHAF3lsIPpC-jNAknfCz5PoLWTi.jpg', 1, 0, '15.00', '2025-02-16 21:02:37'),
(6, 4, 'NVIDIA GeForce RTX 3060', 'Видеокарта для гейминга и графики', '60000.00', 5, 'full_image\\promotions\\defoult.jpg	', 0, 1, '0.00', '2025-02-16 21:02:37'),
(7, 3, 'Intel Core i7', 'Процессор Intel Core i7 для ПК', '35000.00', 10, 'full_image\\promotions\\defoult.jpg	', 1, 0, '15.00', '2025-02-16 21:02:37'),
(8, 3, 'AMD Ryzen 5', 'Процессор AMD Ryzen 5 для высоких нагрузок', '25000.00', 15, 'full_image\\promotions\\defoult.jpg	', 1, 0, '15.00', '2025-02-16 21:02:37'),
(10, 3, 'Intel Core i9-14900K', '24 ядра (8P+16E), до 6.0 ГГц, LGA 1700, 125W TDP', '65000.00', 8, 'full_image\\promotions\\defoult.jpg	', 1, 1, '0.00', '2025-02-16 21:02:37'),
(11, 3, 'Intel Core i7-13700K', '16 ядер (8P+8E), до 5.4 ГГц, LGA 1700, 125W TDP', '42000.00', 15, 'full_image\\promotions\\defoult.jpg	', 1, 0, '10.00', '2025-02-16 21:02:37'),
(12, 3, 'Intel Core i5-13600KF', '14 ядер (6P+8E), до 5.1 ГГц, LGA 1700, 125W TDP', '29000.00', 20, 'full_image\\promotions\\defoult.jpg	', 0, 0, '5.00', '2025-02-16 21:02:37'),
(13, 3, 'Intel Core i3-13100F', '4 ядра, до 4.5 ГГц, LGA 1700, 58W TDP', '11500.00', 35, 'full_image\\promotions\\defoult.jpg	', 0, 1, '0.00', '2025-02-16 21:02:37'),
(14, 3, 'AMD Ryzen 9 7950X3D', '16 ядер, до 5.7 ГГц, AM5, 120W TDP, 3D V-Cache', '72000.00', 5, 'full_image\\promotions\\defoult.jpg	', 1, 1, '0.00', '2025-02-16 21:02:37'),
(15, 3, 'AMD Ryzen 7 7800X3D', '8 ядер, до 5.0 ГГц, AM5, 120W TDP, 3D V-Cache', '45000.00', 12, 'full_image\\promotions\\defoult.jpg	', 1, 0, '7.00', '2025-02-16 21:02:37'),
(16, 3, 'AMD Ryzen 5 7600X', '6 ядер, до 5.3 ГГц, AM5, 105W TDP', '25000.00', 25, 'full_image\\promotions\\defoult.jpg	', 0, 0, '12.00', '2025-02-16 21:02:37'),
(17, 3, 'Intel Core i9-12900KS', '16 ядер (8P+8E), до 5.5 ГГц, LGA 1700, 150W TDP', '52000.00', 3, 'full_image\\promotions\\defoult.jpg	', 0, 0, '15.00', '2025-02-16 21:02:37'),
(18, 3, 'AMD Ryzen 9 5950X', '16 ядер, до 4.9 ГГц, AM4, 105W TDP', '48000.00', 7, 'full_image\\promotions\\defoult.jpg	', 0, 0, '20.00', '2025-02-16 21:02:37'),
(19, 3, 'Intel Xeon W9-3495X', '56 ядер, до 4.8 ГГц, LGA 4677, 350W TDP', '125000.00', 2, 'full_image\\promotions\\defoult.jpg	', 0, 1, '0.00', '2025-02-16 21:02:37'),
(20, 3, 'AMD EPYC 9654', '96 ядер, до 3.7 ГГц, SP5, 360W TDP', '980000.00', 1, 'full_image\\promotions\\defoult.jpg	', 0, 0, '5.00', '2025-02-16 21:02:37'),
(21, 3, 'Intel Pentium Gold G7400', '2 ядра, до 3.7 ГГц, LGA 1700, 46W TDP', '6500.00', 50, 'full_image\\promotions\\defoult.jpg	', 0, 0, '0.00', '2025-02-16 21:02:37'),
(22, 3, 'AMD Athlon Gold 7220U', '2 ядра, до 3.7 ГГц, FP6, 15W TDP', '5500.00', 40, 'full_image\\promotions\\defoult.jpg	', 0, 1, '8.00', '2025-02-16 21:02:37'),
(23, 4, 'NVIDIA GeForce RTX 4090', '24 ГБ GDDR6X, 16384 ядер, 384 бит, 450W', '180000.00', 5, 'full_image\\promotions\\defoult.jpg	', 1, 1, '0.00', '2025-02-16 21:02:37'),
(24, 4, 'NVIDIA GeForce RTX 4080', '16 ГБ GDDR6X, 9728 ядер, 256 бит, 320W', '120000.00', 10, 'full_image\\promotions\\defoult.jpg	', 1, 0, '5.00', '2025-02-16 21:02:37'),
(25, 4, 'NVIDIA GeForce RTX 4070 Ti', '12 ГБ GDDR6X, 7680 ядер, 192 бит, 285W', '80000.00', 15, 'full_image\\promotions\\defoult.jpg	', 0, 0, '10.00', '2025-02-16 21:02:37'),
(26, 4, 'NVIDIA GeForce RTX 3090 Ti', '24 ГБ GDDR6X, 10752 ядра, 384 бит, 450W', '150000.00', 3, 'full_image\\promotions\\defoult.jpg	', 0, 0, '15.00', '2025-02-16 21:02:37'),
(27, 4, 'NVIDIA GeForce RTX 3080', '10 ГБ GDDR6X, 8704 ядра, 320 бит, 320W', '90000.00', 8, 'full_image\\promotions\\defoult.jpg	', 0, 0, '20.00', '2025-02-16 21:02:37'),
(28, 4, 'NVIDIA GeForce RTX 3060 Ti', '8 ГБ GDDR6, 4864 ядра, 256 бит, 200W', '45000.00', 20, 'full_image\\promotions\\defoult.jpg	', 1, 0, '12.00', '2025-02-16 21:02:37'),
(29, 4, 'AMD Radeon RX 7900 XTX', '24 ГБ GDDR6, 6144 ядра, 384 бит, 355W', '120000.00', 7, 'full_image\\promotions\\defoult.jpg	', 1, 1, '0.00', '2025-02-16 21:02:37'),
(30, 4, 'AMD Radeon RX 7800 XT', '16 ГБ GDDR6, 3840 ядер, 256 бит, 300W', '80000.00', 12, 'full_image\\promotions\\defoult.jpg	', 0, 0, '7.00', '2025-02-16 21:02:37'),
(31, 4, 'AMD Radeon RX 7600', '8 ГБ GDDR6, 2048 ядер, 128 бит, 165W', '35000.00', 25, 'full_image\\promotions\\defoult.jpg	', 0, 1, '10.00', '2025-02-16 21:02:37'),
(32, 4, 'NVIDIA GeForce GTX 1650', '4 ГБ GDDR5, 896 ядер, 128 бит, 75W', '15000.00', 50, 'full_image\\promotions\\defoult.jpg	', 0, 0, '0.00', '2025-02-16 21:02:37'),
(33, 4, 'AMD Radeon RX 6400', '4 ГБ GDDR6, 768 ядер, 64 бит, 53W', '12000.00', 40, 'full_image\\promotions\\defoult.jpg	', 0, 0, '8.00', '2025-02-16 21:02:37'),
(34, 1, 'Игровой ПК ASUS ROG Strix G15', 'Intel i7-13700K, RTX 4080, 32 ГБ DDR5, 1 TB SSD', '210000.00', 5, 'full_image\\promotions\\defoult.jpg	', 1, 1, '3.00', '2025-02-16 21:02:37'),
(35, 1, 'Игровой ПК MSI MAG Codex 5', 'AMD Ryzen 7 7800X, RTX 4070 Ti, 16 ГБ DDR5, 1 TB SSD', '150000.00', 8, 'full_image\\promotions\\defoult.jpg	', 1, 0, '5.00', '2025-02-16 21:02:37'),
(36, 1, 'Игровой ПК HP Omen 45L', 'Intel i9-13900K, RTX 4090, 64 ГБ DDR5, 2 TB SSD', '350000.00', 3, 'full_image\\promotions\\defoult.jpg	', 0, 0, '0.00', '2025-02-16 21:02:37'),
(37, 1, 'Офисный ПК Lenovo ThinkCentre M70s', 'Intel i3-13100, 8 ГБ DDR4, 256 ГБ SSD', '35000.00', 30, 'full_image\\promotions\\defoult.jpg	', 0, 1, '5.00', '2025-02-16 21:02:37'),
(38, 1, 'Офисный ПК Dell OptiPlex 5000', 'Intel i5-13400, 16 ГБ DDR4, 512 ГБ SSD', '55000.00', 20, 'full_image\\promotions\\defoult.jpg	', 0, 0, '10.00', '2025-02-16 21:02:37'),
(39, 1, 'Рабочая станция HP Z6 G5', 'Xeon W5-3435, 64 ГБ DDR5, NVIDIA RTX A6000', '540000.00', 3, 'full_image\\promotions\\defoult.jpg	', 0, 0, '0.00', '2025-02-16 21:02:37'),
(40, 1, 'Рабочая станция Dell Precision 7865', 'AMD Ryzen Threadripper PRO 5975WX, 128 ГБ DDR4, RTX A5000', '720000.00', 2, 'full_image\\promotions\\defoult.jpg	', 0, 1, '0.00', '2025-02-16 21:02:37'),
(41, 2, 'Игровой ноутбук ASUS ROG Zephyrus G16', 'Intel i9-13900H, RTX 4070, 32 ГБ DDR5, 2 TB SSD', '210000.00', 7, 'full_image\\promotions\\defoult.jpg	', 1, 1, '0.00', '2025-02-16 21:02:37'),
(42, 2, 'Игровой ноутбук MSI Katana 15', 'Intel i7-13700H, RTX 4060, 16 ГБ DDR5, 1 TB SSD', '150000.00', 10, 'full_image\\promotions\\defoult.jpg	', 1, 0, '5.00', '2025-02-16 21:02:37'),
(43, 2, 'Игровой ноутбук Acer Predator Helios 16', 'Intel i9-13900HX, RTX 4080, 32 ГБ DDR5, 2 TB SSD', '250000.00', 5, 'full_image\\promotions\\defoult.jpg	', 0, 0, '0.00', '2025-02-16 21:02:37'),
(44, 2, 'MacBook Pro 16 M2 Max', 'Apple M2 Max, 64 ГБ RAM, 4 TB SSD, Liquid Retina XDR', '450000.00', 5, 'full_image\\promotions\\defoult.jpg	', 1, 0, '0.00', '2025-02-16 21:02:37'),
(45, 2, 'Dell XPS 13 Plus', 'Intel i7-1360P, 16 ГБ LPDDR5, 1 TB SSD, 13.4\" OLED', '180000.00', 12, 'full_image\\promotions\\defoult.jpg	', 0, 1, '8.00', '2025-02-16 21:02:37'),
(46, 2, 'Бюджетный ноутбук Acer Aspire 5', 'AMD Ryzen 5 7530U, 16 ГБ DDR4, 512 ГБ SSD', '65000.00', 25, 'full_image\\promotions\\defoult.jpg	', 0, 1, '8.00', '2025-02-16 21:02:37'),
(47, 2, 'Ноутбук Lenovo IdeaPad 3', 'Intel i3-1315U, 8 ГБ DDR4, 256 ГБ SSD', '45000.00', 30, 'full_image\\promotions\\defoult.jpg	', 0, 0, '10.00', '2025-02-16 21:02:37'),
(48, 5, 'Мышь Logitech G Pro X Superlight', 'Беспроводная, 25 600 DPI, 63 г', '12000.00', 20, 'full_image\\promotions\\defoult.jpg	', 1, 1, '0.00', '2025-02-16 21:02:37'),
(49, 5, 'Мышь Razer DeathAdder V3 Pro', 'Беспроводная, 30 000 DPI, 63 г', '15000.00', 15, 'full_image\\promotions\\defoult.jpg	', 1, 0, '5.00', '2025-02-16 21:02:37'),
(50, 5, 'Мышь SteelSeries Rival 3', 'Проводная, 8 500 DPI, 77 г', '3000.00', 50, 'full_image\\promotions\\defoult.jpg	', 0, 0, '10.00', '2025-02-16 21:02:37'),
(51, 6, 'Коврик Razer Gigantus V2', '900x400x3 мм, ткань', '2500.00', 30, 'full_image\\promotions\\defoult.jpg	', 1, 1, '0.00', '2025-02-16 21:02:37'),
(52, 6, 'Коврик SteelSeries QcK Heavy', '900x400x6 мм, ткань', '3000.00', 25, 'full_image\\promotions\\defoult.jpg	', 1, 0, '5.00', '2025-02-16 21:02:37'),
(53, 6, 'Коврик Logitech G840', '900x400x3 мм, ткань', '4000.00', 20, 'full_image\\promotions\\defoult.jpg	', 0, 0, '10.00', '2025-02-16 21:02:37'),
(57, 1, 'шцуо', 'шшруц', '1211.00', 13, 'full_image/products/product_6814d75591c0b_photo_2025-05-01_15-26-03.jpg', 0, 0, '12.00', '2025-05-02 14:31:49');

-- --------------------------------------------------------

--
-- Структура таблицы `Product_Characteristics`
--

CREATE TABLE `Product_Characteristics` (
  `characteristic_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value_type` enum('Текст','Число') COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `Product_Characteristics`
--

INSERT INTO `Product_Characteristics` (`characteristic_id`, `category_id`, `name`, `value_type`) VALUES
(1, 3, 'Частота процессора', 'Число'),
(2, 3, 'Количество ядер', 'Число'),
(3, 4, 'Объем видеопамяти', 'Число');

-- --------------------------------------------------------

--
-- Структура таблицы `Product_Characteristic_Values`
--

CREATE TABLE `Product_Characteristic_Values` (
  `product_id` int(11) NOT NULL,
  `characteristic_id` int(11) NOT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `Product_Characteristic_Values`
--

INSERT INTO `Product_Characteristic_Values` (`product_id`, `characteristic_id`, `value`) VALUES
(1, 1, '3.8'),
(1, 2, '8'),
(2, 1, '3.6'),
(2, 2, '6'),
(3, 3, '12'),
(4, 1, '6'),
(4, 2, '7');

-- --------------------------------------------------------

--
-- Структура таблицы `Promotions`
--

CREATE TABLE `Promotions` (
  `promotion_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT 'full_image\\promotions\\defoult.jpg',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `status` enum('Активна','Завершена') COLLATE utf8mb4_unicode_ci DEFAULT 'Активна'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `Promotions`
--

INSERT INTO `Promotions` (`promotion_id`, `title`, `description`, `image_url`, `start_date`, `end_date`, `category_id`, `status`) VALUES
(1, 'Распродажа процессоров', 'Скидка 20% на все процессоры', 'full_image\\promotions\\defoult.jpg', '2024-03-01', '2024-03-31', 3, 'Активна'),
(3, 'ууйу', 'щгтка', 'full_image/promotions/67f82f9364829-62muSDn5SECBUyfiVY_jrw0RWgsbg5-w1zt7Ibqy4GuEtyhOqGxDltbV1tWr_qHAF3lsIPpC-jNAknfCz5PoLWTi.jpg', '2025-06-04', '2025-11-13', 2, 'Активна');

-- --------------------------------------------------------

--
-- Структура таблицы `Recommendations`
--

CREATE TABLE `Recommendations` (
  `recommendation_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `Reviews`
--

CREATE TABLE `Reviews` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'На модерации'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `Reviews`
--

INSERT INTO `Reviews` (`review_id`, `user_id`, `product_id`, `rating`, `comment`, `created_at`, `status`) VALUES
(1, 5, 34, 3, 'heje', '2025-04-27 08:57:52', 'Отклонен'),
(2, 5, 40, 5, 'ewkf', '2025-04-27 09:47:03', 'Одобрен');

-- --------------------------------------------------------

--
-- Структура таблицы `Role`
--

CREATE TABLE `Role` (
  `id` int(11) NOT NULL,
  `Name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `Role`
--

INSERT INTO `Role` (`id`, `Name`) VALUES
(1, 'Администратор'),
(2, 'Пользователь'),
(3, 'Старший администратор'),
(4, 'Младший администратор'),
(5, 'Модератор'),
(6, 'Контент-менеджер'),
(7, 'Логист');

-- --------------------------------------------------------

--
-- Структура таблицы `Role_Permissions`
--

CREATE TABLE `Role_Permissions` (
  `role_id` int(11) NOT NULL,
  `permission` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `Role_Permissions`
--

INSERT INTO `Role_Permissions` (`role_id`, `permission`) VALUES
(1, 'add_Categories'),
(1, 'add_Delivery_Status'),
(1, 'add_Feedback'),
(1, 'add_News'),
(1, 'add_Order_Items'),
(1, 'add_Orders'),
(1, 'add_Products'),
(1, 'add_Promotions'),
(1, 'add_Recommendations'),
(1, 'add_Reviews'),
(1, 'add_Role'),
(1, 'add_Role_Permissions'),
(1, 'add_Stores'),
(1, 'add_User_Sessions'),
(1, 'add_Users'),
(1, 'admin_full_access'),
(1, 'delete_Categories'),
(1, 'delete_Delivery_Status'),
(1, 'delete_Feedback'),
(1, 'delete_News'),
(1, 'delete_Order_Items'),
(1, 'delete_Orders'),
(1, 'delete_Products'),
(1, 'delete_Promotions'),
(1, 'delete_Recommendations'),
(1, 'delete_Reviews'),
(1, 'delete_Role'),
(1, 'delete_Role_Permissions'),
(1, 'delete_Stores'),
(1, 'delete_User_Sessions'),
(1, 'delete_Users'),
(1, 'edit_Categories'),
(1, 'edit_Delivery_Status'),
(1, 'edit_Feedback'),
(1, 'edit_News'),
(1, 'edit_Order_Items'),
(1, 'edit_Orders'),
(1, 'edit_Products'),
(1, 'edit_Promotions'),
(1, 'edit_Recommendations'),
(1, 'edit_Reviews'),
(1, 'edit_Role'),
(1, 'edit_Role_Permissions'),
(1, 'edit_Stores'),
(1, 'edit_User_Sessions'),
(1, 'edit_Users'),
(1, 'get_Categories'),
(1, 'get_Delivery_Status'),
(1, 'get_Feedback'),
(1, 'get_News'),
(1, 'get_Order_Items'),
(1, 'get_Orders'),
(1, 'get_Products'),
(1, 'get_Promotions'),
(1, 'get_Recommendations'),
(1, 'get_Reviews'),
(1, 'get_Role'),
(1, 'get_Role_Permissions'),
(1, 'get_Stores'),
(1, 'get_User_Sessions'),
(1, 'get_Users'),
(3, 'add_Categories'),
(3, 'add_Delivery_Status'),
(3, 'add_Feedback'),
(3, 'add_News'),
(3, 'add_Order_Items'),
(3, 'add_Orders'),
(3, 'add_Product_Characteristics'),
(3, 'add_Products'),
(3, 'add_Promotions'),
(3, 'add_Recommendations'),
(3, 'add_Reviews'),
(3, 'add_Stores'),
(3, 'add_User_Sessions'),
(3, 'add_Users'),
(3, 'delete_Categories'),
(3, 'delete_Delivery_Status'),
(3, 'delete_Feedback'),
(3, 'delete_News'),
(3, 'delete_Order_Items'),
(3, 'delete_Orders'),
(3, 'delete_Product_Characteristics'),
(3, 'delete_Products'),
(3, 'delete_Promotions'),
(3, 'delete_Recommendations'),
(3, 'delete_Reviews'),
(3, 'delete_Stores'),
(3, 'delete_User_Sessions'),
(3, 'delete_Users'),
(3, 'edit_Categories'),
(3, 'edit_Delivery_Status'),
(3, 'edit_Feedback'),
(3, 'edit_News'),
(3, 'edit_Order_Items'),
(3, 'edit_Orders'),
(3, 'edit_Product_Characteristics'),
(3, 'edit_Products'),
(3, 'edit_Promotions'),
(3, 'edit_Recommendations'),
(3, 'edit_Reviews'),
(3, 'edit_Stores'),
(3, 'edit_User_Sessions'),
(3, 'edit_Users'),
(3, 'get_Categories'),
(3, 'get_Delivery_Status'),
(3, 'get_Feedback'),
(3, 'get_News'),
(3, 'get_Order_Items'),
(3, 'get_Orders'),
(3, 'get_Product_Characteristics'),
(3, 'get_Products'),
(3, 'get_Promotions'),
(3, 'get_Recommendations'),
(3, 'get_Reviews'),
(3, 'get_Stores'),
(3, 'get_User_Sessions'),
(3, 'get_Users'),
(4, 'add_Categories'),
(4, 'add_Delivery_Status'),
(4, 'add_Feedback'),
(4, 'add_News'),
(4, 'add_Order_Items'),
(4, 'add_Orders'),
(4, 'add_Product_Characteristics'),
(4, 'add_Products'),
(4, 'add_Promotions'),
(4, 'add_Reviews'),
(4, 'add_Stores'),
(4, 'delete_Categories'),
(4, 'delete_Delivery_Status'),
(4, 'delete_Feedback'),
(4, 'delete_News'),
(4, 'delete_Product_Characteristics'),
(4, 'delete_Products'),
(4, 'delete_Promotions'),
(4, 'delete_Reviews'),
(4, 'edit_Categories'),
(4, 'edit_Delivery_Status'),
(4, 'edit_Feedback'),
(4, 'edit_News'),
(4, 'edit_Order_Items'),
(4, 'edit_Orders'),
(4, 'edit_Product_Characteristics'),
(4, 'edit_Products'),
(4, 'edit_Promotions'),
(4, 'edit_Reviews'),
(4, 'edit_Stores'),
(4, 'get_Categories'),
(4, 'get_Delivery_Status'),
(4, 'get_Feedback'),
(4, 'get_News'),
(4, 'get_Order_Items'),
(4, 'get_Orders'),
(4, 'get_Product_Characteristics'),
(4, 'get_Products'),
(4, 'get_Promotions'),
(4, 'get_Reviews'),
(4, 'get_Stores'),
(5, 'add_feedback'),
(5, 'add_review'),
(5, 'add_Stores'),
(5, 'delete_feedback'),
(5, 'delete_review'),
(5, 'edit_feedback'),
(5, 'edit_review'),
(5, 'get_feedback'),
(5, 'get_Products'),
(5, 'get_reviews'),
(5, 'get_Stores'),
(5, 'get_Users'),
(6, 'add_Cart'),
(6, 'add_category'),
(6, 'add_characteristic'),
(6, 'add_news'),
(6, 'add_product'),
(6, 'add_Product_Characteristics'),
(6, 'add_promotion'),
(6, 'add_Recommendations'),
(6, 'delete_Cart'),
(6, 'delete_category'),
(6, 'delete_characteristic'),
(6, 'delete_news'),
(6, 'delete_product'),
(6, 'delete_Product_Characteristics'),
(6, 'delete_promotion'),
(6, 'delete_Recommendations'),
(6, 'edit_Cart'),
(6, 'edit_category'),
(6, 'edit_characteristic'),
(6, 'edit_news'),
(6, 'edit_product'),
(6, 'edit_Product_Characteristics'),
(6, 'edit_promotion'),
(6, 'edit_Recommendations'),
(6, 'get_Cart'),
(6, 'get_categories'),
(6, 'get_news'),
(6, 'get_Product_Characteristics'),
(6, 'get_products'),
(6, 'get_promotions'),
(6, 'get_Recommendations'),
(6, 'get_Reviews'),
(6, 'get_Stores'),
(7, 'add_delivery_status'),
(7, 'delete_delivery_status'),
(7, 'delete_order'),
(7, 'edit_delivery_status'),
(7, 'edit_order'),
(7, 'get_delivery_status'),
(7, 'get_Order_Items'),
(7, 'get_orders'),
(7, 'get_Products'),
(7, 'get_Stores'),
(7, 'get_Users');

-- --------------------------------------------------------

--
-- Структура таблицы `Stores`
--

CREATE TABLE `Stores` (
  `store_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `working_hours` text COLLATE utf8mb4_unicode_ci,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `status` enum('Активен','Неактивен') COLLATE utf8mb4_unicode_ci DEFAULT 'Активен'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `Stores`
--

INSERT INTO `Stores` (`store_id`, `name`, `address`, `phone`, `working_hours`, `latitude`, `longitude`, `status`) VALUES
(2, 'Северный филиал', 'г. Москва, Ленинградский пр-т, д. 45', '+74957654321', 'Пн-Пт: 10:00-20:00, Сб-Вс: 10:00-19:00', '55.79452100', '37.53577200', 'Активен'),
(3, 'Южный ТЦ \"Гагаринский\"', 'г. Москва, ул. Вавилова, д. 3, ТЦ \"Гагаринский\", 2 этаж', '+74959876543', 'Ежедневно 10:00-22:00', '55.70912300', '37.58764500', 'Активен'),
(4, 'Западный склад-магазин', 'г. Москва, ул. Молодогвардейская, д. 54', '+74951237890', 'Пн-Пт: 9:00-19:00, Сб: 10:00-17:00, Вс: выходной', '55.73456700', '37.45678900', 'Активен'),
(5, 'Восточный бутик', 'г. Москва, Щёлковское ш., д. 75, ТРЦ \"Щёлково\", 1 этаж', '+74958765432', 'Ежедневно 10:00-21:00', '55.81234500', '37.78901200', 'Неактивен');

-- --------------------------------------------------------

--
-- Структура таблицы `Users`
--

CREATE TABLE `Users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_id` int(11) NOT NULL DEFAULT '2',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `photo` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Не в сети',
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preferred_payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preferred_delivery_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lockout_until` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `Users`
--

INSERT INTO `Users` (`user_id`, `name`, `email`, `password_hash`, `role_id`, `created_at`, `photo`, `status`, `phone`, `postal_code`, `preferred_payment_method`, `preferred_delivery_method`, `lockout_until`) VALUES
(3, 'Админ Админов', 'admin@example.com', 'hashpassword3', 3, '2025-01-30 16:44:34', '', 'Не в сети', '', '', '', '', NULL),
(5, 'bobi', 'bobi@me.ru', '$2y$10$9xkeGpOyW/CM7t1ELD6j1ODkJlbepmnWEC0hHB/3mBum/4VXwfHsq', 1, '2025-03-15 19:19:30', NULL, 'Онлайн', '+79991234567', NULL, NULL, NULL, NULL),
(6, 'vadw2', 'vadw2@p.e', '$2y$10$aGXunBs5cBVdABGcz9oOdu9BPKrfyPfox5bMDnl.6.BiwAe0nHyMm', 1, '2025-03-25 06:08:45', NULL, 'Не в сети', '+71223123123', '', '', '', NULL),
(7, 'Иван Петров', 'ivan.petrov@example.com', '$2y$10$9xkeGpOyW/CM7t1ELD6j1ODkJlbepmnWEC0hHB/3mBum/4VXwfHsq', 1, '2025-05-01 11:55:08', NULL, 'Не в сети', '+79161234567', '123456', 'Карта', 'Курьер', NULL),
(8, 'Елена Смирнова', 'elena.smirnova@example.com', '$2y$10$9xkeGpOyW/CM7t1ELD6j1ODkJlbepmnWEC0hHB/3mBum/4VXwfHsq', 1, '2025-05-01 11:55:08', NULL, 'Не в сети', '+79162345678', '234567', 'Наличные', 'Самовывоз', NULL),
(9, 'Алексей Иванов', 'alex.ivanov@example.com', '$2y$10$9xkeGpOyW/CM7t1ELD6j1ODkJlbepmnWEC0hHB/3mBum/4VXwfHsq', 2, '2025-05-01 11:55:08', NULL, 'Не в сети', '+79163456789', '345678', 'Карта', 'Курьер', NULL),
(10, 'Ольга Кузнецова', 'olga.kuznetsova@example.com', '$2y$10$9xkeGpOyW/CM7t1ELD6j1ODkJlbepmnWEC0hHB/3mBum/4VXwfHsq', 2, '2025-05-01 11:55:08', NULL, 'Не в сети', '+79164567890', '456789', 'Карта', 'Почта', NULL),
(11, 'Дмитрий Соколов', 'dmitry.sokolov@example.com', '$2y$10$9xkeGpOyW/CM7t1ELD6j1ODkJlbepmnWEC0hHB/3mBum/4VXwfHsq', 2, '2025-05-01 11:55:08', NULL, 'Не в сети', '+79165678901', '567890', 'Наличные', 'Самовывоз', NULL),
(12, 'Анна Васнецова', 'anna.vasnetsova@example.com', '$2y$10$9xkeGpOyW/CM7t1ELD6j1ODkJlbepmnWEC0hHB/3mBum/4VXwfHsq', 2, '2025-05-01 11:55:08', NULL, 'Не в сети', '+79166789012', '678901', 'Карта', 'Курьер', NULL),
(13, 'Сергей Попов', 'sergey.popov@example.com', '$2y$10$9xkeGpOyW/CM7t1ELD6j1ODkJlbepmnWEC0hHB/3mBum/4VXwfHsq', 2, '2025-05-01 11:55:08', NULL, 'Не в сети', '+79167890123', '789012', 'Карта', 'Почта', NULL),
(14, 'Александр Модераторов', 'alex.moderator@example.com', '$2y$10$9xkeGpOyW/CM7t1ELD6j1ODkJlbepmnWEC0hHB/3mBum/4VXwfHsq', 5, '2025-05-01 11:55:08', NULL, 'Не в сети', '+79168901234', '890123', NULL, NULL, NULL),
(15, 'Татьяна Редакторова', 'tanya.editor@example.com', '$2y$10$9xkeGpOyW/CM7t1ELD6j1ODkJlbepmnWEC0hHB/3mBum/4VXwfHsq', 5, '2025-05-01 11:55:08', NULL, 'Не в сети', '+79169012345', '901234', NULL, NULL, NULL),
(16, 'Виктор Контентов', 'viktor.content@example.com', '$2y$10$9xkeGpOyW/CM7t1ELD6j1ODkJlbepmnWEC0hHB/3mBum/4VXwfHsq', 6, '2025-05-01 11:55:08', NULL, 'Не в сети', '+79160123456', '012345', NULL, NULL, NULL),
(17, 'Наталья Новостная', 'natalya.news@example.com', '$2y$10$9xkeGpOyW/CM7t1ELD6j1ODkJlbepmnWEC0hHB/3mBum/4VXwfHsq', 6, '2025-05-01 11:55:08', NULL, 'Не в сети', '+79161234567', '123456', NULL, NULL, NULL),
(18, 'Артем Доставкин', 'artem.delivery@example.com', '$2y$10$9xkeGpOyW/CM7t1ELD6j1ODkJlbepmnWEC0hHB/3mBum/4VXwfHsq', 7, '2025-05-01 11:55:08', NULL, 'Не в сети', '+79162345678', '234567', NULL, NULL, NULL),
(19, 'Юлия Складова', 'yulia.warehouse@example.com', '$2y$10$9xkeGpOyW/CM7t1ELD6j1ODkJlbepmnWEC0hHB/3mBum/4VXwfHsq', 7, '2025-05-01 11:55:08', NULL, 'Не в сети', '+79163456789', '345678', NULL, NULL, NULL),
(20, 'Геннадий Главный', 'gennady.main@example.com', '$2y$10$9xkeGpOyW/CM7t1ELD6j1ODkJlbepmnWEC0hHB/3mBum/4VXwfHsq', 3, '2025-05-01 11:55:08', NULL, 'Не в сети', '+79164567890', '456789', NULL, NULL, NULL),
(21, 'vad12', 'bobi32@me.ru', '$2y$10$TEJ4.2bOplnI5vBBhlGBju5Gd3yokeM8XnTYOgAIlCWDlhI.0Ek.y', 2, '2025-05-17 14:16:46', NULL, 'Не в сети', '', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `User_Sessions`
--

CREATE TABLE `User_Sessions` (
  `session_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `User_Sessions`
--

INSERT INTO `User_Sessions` (`session_id`, `user_id`, `token`, `created_at`, `expires_at`) VALUES
(66, 5, '9e2f0732e5f2a3ac83f84e6c5d33f0c78b9aff0b142a6aa890abef7570d44a18', '2025-05-18 09:05:37', '2025-05-19 09:05:37');

--
-- Триггеры `User_Sessions`
--
DELIMITER $$
CREATE TRIGGER `after_user_session_delete` AFTER DELETE ON `User_Sessions` FOR EACH ROW BEGIN
    CALL UpdateUserStatus(OLD.user_id);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_user_session_insert` AFTER INSERT ON `User_Sessions` FOR EACH ROW BEGIN
    CALL UpdateUserStatus(NEW.user_id);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_user_session_update` AFTER UPDATE ON `User_Sessions` FOR EACH ROW BEGIN
    CALL UpdateUserStatus(NEW.user_id);
END
$$
DELIMITER ;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `Admin_Logs`
--
ALTER TABLE `Admin_Logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Индексы таблицы `Cart`
--
ALTER TABLE `Cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `Cart_Items`
--
ALTER TABLE `Cart_Items`
  ADD PRIMARY KEY (`cart_item_id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Индексы таблицы `Categories`
--
ALTER TABLE `Categories`
  ADD PRIMARY KEY (`category_id`),
  ADD KEY `parent_category_id` (`parent_category_id`);

--
-- Индексы таблицы `Comparison_Items`
--
ALTER TABLE `Comparison_Items`
  ADD PRIMARY KEY (`comparison_item_id`),
  ADD KEY `comparison_id` (`comparison_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Индексы таблицы `Comparison_List`
--
ALTER TABLE `Comparison_List`
  ADD PRIMARY KEY (`comparison_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `Delivery_Status`
--
ALTER TABLE `Delivery_Status`
  ADD PRIMARY KEY (`delivery_status_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Индексы таблицы `Favorites`
--
ALTER TABLE `Favorites`
  ADD PRIMARY KEY (`favorite_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Индексы таблицы `Feedback`
--
ALTER TABLE `Feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `Logs`
--
ALTER TABLE `Logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `News`
--
ALTER TABLE `News`
  ADD PRIMARY KEY (`news_id`);

--
-- Индексы таблицы `Orders`
--
ALTER TABLE `Orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_status` (`order_status`);

--
-- Индексы таблицы `Order_Items`
--
ALTER TABLE `Order_Items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Индексы таблицы `Products`
--
ALTER TABLE `Products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `created_at` (`created_at`);

--
-- Индексы таблицы `Product_Characteristics`
--
ALTER TABLE `Product_Characteristics`
  ADD PRIMARY KEY (`characteristic_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Индексы таблицы `Product_Characteristic_Values`
--
ALTER TABLE `Product_Characteristic_Values`
  ADD PRIMARY KEY (`product_id`,`characteristic_id`),
  ADD KEY `characteristic_id` (`characteristic_id`);

--
-- Индексы таблицы `Promotions`
--
ALTER TABLE `Promotions`
  ADD PRIMARY KEY (`promotion_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Индексы таблицы `Recommendations`
--
ALTER TABLE `Recommendations`
  ADD PRIMARY KEY (`recommendation_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Индексы таблицы `Reviews`
--
ALTER TABLE `Reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Индексы таблицы `Role`
--
ALTER TABLE `Role`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `Role_Permissions`
--
ALTER TABLE `Role_Permissions`
  ADD PRIMARY KEY (`role_id`,`permission`);

--
-- Индексы таблицы `Stores`
--
ALTER TABLE `Stores`
  ADD PRIMARY KEY (`store_id`);

--
-- Индексы таблицы `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- Индексы таблицы `User_Sessions`
--
ALTER TABLE `User_Sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `Admin_Logs`
--
ALTER TABLE `Admin_Logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=158;

--
-- AUTO_INCREMENT для таблицы `Cart`
--
ALTER TABLE `Cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `Cart_Items`
--
ALTER TABLE `Cart_Items`
  MODIFY `cart_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `Categories`
--
ALTER TABLE `Categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `Comparison_Items`
--
ALTER TABLE `Comparison_Items`
  MODIFY `comparison_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT для таблицы `Comparison_List`
--
ALTER TABLE `Comparison_List`
  MODIFY `comparison_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `Delivery_Status`
--
ALTER TABLE `Delivery_Status`
  MODIFY `delivery_status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `Favorites`
--
ALTER TABLE `Favorites`
  MODIFY `favorite_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT для таблицы `Feedback`
--
ALTER TABLE `Feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT для таблицы `Logs`
--
ALTER TABLE `Logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT для таблицы `News`
--
ALTER TABLE `News`
  MODIFY `news_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `Orders`
--
ALTER TABLE `Orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `Order_Items`
--
ALTER TABLE `Order_Items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `Products`
--
ALTER TABLE `Products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT для таблицы `Product_Characteristics`
--
ALTER TABLE `Product_Characteristics`
  MODIFY `characteristic_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `Promotions`
--
ALTER TABLE `Promotions`
  MODIFY `promotion_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `Recommendations`
--
ALTER TABLE `Recommendations`
  MODIFY `recommendation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `Reviews`
--
ALTER TABLE `Reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `Role`
--
ALTER TABLE `Role`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `Stores`
--
ALTER TABLE `Stores`
  MODIFY `store_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `Users`
--
ALTER TABLE `Users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT для таблицы `User_Sessions`
--
ALTER TABLE `User_Sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `Admin_Logs`
--
ALTER TABLE `Admin_Logs`
  ADD CONSTRAINT `admin_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `Cart`
--
ALTER TABLE `Cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `Cart_Items`
--
ALTER TABLE `Cart_Items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `Cart` (`cart_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `Products` (`product_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `Categories`
--
ALTER TABLE `Categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_category_id`) REFERENCES `Categories` (`category_id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `Comparison_Items`
--
ALTER TABLE `Comparison_Items`
  ADD CONSTRAINT `comparison_items_ibfk_1` FOREIGN KEY (`comparison_id`) REFERENCES `Comparison_List` (`comparison_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comparison_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `Products` (`product_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `Comparison_List`
--
ALTER TABLE `Comparison_List`
  ADD CONSTRAINT `comparison_list_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `Delivery_Status`
--
ALTER TABLE `Delivery_Status`
  ADD CONSTRAINT `delivery_status_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `Orders` (`order_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `Favorites`
--
ALTER TABLE `Favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `Products` (`product_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `Feedback`
--
ALTER TABLE `Feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `Logs`
--
ALTER TABLE `Logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `Orders`
--
ALTER TABLE `Orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `Order_Items`
--
ALTER TABLE `Order_Items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `Orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `Products` (`product_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `Products`
--
ALTER TABLE `Products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `Categories` (`category_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `Product_Characteristics`
--
ALTER TABLE `Product_Characteristics`
  ADD CONSTRAINT `product_characteristics_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `Categories` (`category_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `Product_Characteristic_Values`
--
ALTER TABLE `Product_Characteristic_Values`
  ADD CONSTRAINT `product_characteristic_values_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `Products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_characteristic_values_ibfk_2` FOREIGN KEY (`characteristic_id`) REFERENCES `Product_Characteristics` (`characteristic_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `Promotions`
--
ALTER TABLE `Promotions`
  ADD CONSTRAINT `promotions_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `Categories` (`category_id`);

--
-- Ограничения внешнего ключа таблицы `Recommendations`
--
ALTER TABLE `Recommendations`
  ADD CONSTRAINT `recommendations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recommendations_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `Products` (`product_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `Reviews`
--
ALTER TABLE `Reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `Products` (`product_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `Role_Permissions`
--
ALTER TABLE `Role_Permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `Role` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `Users`
--
ALTER TABLE `Users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `Role` (`id`);

--
-- Ограничения внешнего ключа таблицы `User_Sessions`
--
ALTER TABLE `User_Sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
