-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Дек 10 2024 г., 15:43
-- Версия сервера: 10.4.28-MariaDB
-- Версия PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `machines_db`
--

DELIMITER $$
--
-- Процедуры
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `update_order_status` (IN `p_order_id` INT, IN `p_new_status` VARCHAR(20), IN `p_changed_by` VARCHAR(100))   BEGIN
    DECLARE v_current_status VARCHAR(20);
    
    -- Начало транзакции
    START TRANSACTION;
    
    -- Получаем текущий статус
    SELECT status INTO v_current_status 
    FROM orders 
    WHERE order_id = p_order_id FOR UPDATE;
    
    -- Обновляем статус заказа
    UPDATE orders 
    SET status = p_new_status 
    WHERE order_id = p_order_id;
    
    -- Добавляем запись в журнал статусов
    INSERT INTO order_status_log 
    (order_id, old_status, new_status, changed_by) 
    VALUES 
    (p_order_id, v_current_status, p_new_status, p_changed_by);
    
    -- Фиксируем транзакцию
    COMMIT;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблицы `characteristics`
--

CREATE TABLE `characteristics` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL COMMENT 'Название характеристики'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Дамп данных таблицы `characteristics`
--

INSERT INTO `characteristics` (`id`, `name`) VALUES
(1, 'Длина'),
(2, 'Ширина');

-- --------------------------------------------------------

--
-- Структура таблицы `machines`
--

CREATE TABLE `machines` (
  `machine_id` int(11) NOT NULL,
  `model_name` varchar(255) NOT NULL COMMENT 'Модель станка',
  `manufacturer` varchar(255) NOT NULL COMMENT 'Производитель',
  `price` decimal(10,2) NOT NULL COMMENT 'Цена',
  `stock_quantity` int(11) NOT NULL COMMENT 'Количество на складе',
  `specification_id` int(11) DEFAULT NULL COMMENT 'ID спецификации',
  `sort` int(11) NOT NULL DEFAULT 500 COMMENT 'Порядок сортировки',
  `image` varchar(255) DEFAULT NULL COMMENT 'Путь к изображению'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Дамп данных таблицы `machines`
--

INSERT INTO `machines` (`machine_id`, `model_name`, `manufacturer`, `price`, `stock_quantity`, `specification_id`, `sort`, `image`) VALUES
(1, 'Model A', 'Manufacturer X', 10000.00, 0, NULL, 500, NULL),
(2, 'Model B', 'Manufacturer Y', 15000.00, 1, NULL, 500, NULL),
(3, 'Taisun multi mill 500', 'Taisun', 15000.00, 12, NULL, 500, NULL),
(4, 'Taisun multi mill 600', 'Taisun', 14999.99, 9, NULL, 500, NULL),
(5, 'Taisun multi mill 700', 'Taisun', 14999.99, 0, NULL, 500, 'images/674666bf9e331_Trenazher-stanka-chpu-Taisun-Machine-Trainer_transparent_web.png'),
(6, 'Taisun multi mill 900', 'Taisun', 12000000.00, 17, NULL, 500, 'images/6748aef2ebfab_Trenazher-stanka-chpu-Taisun-Machine-TrainerTrenazher-stanka-chpu-Taisun-Machine-Trainer_web.png');

-- --------------------------------------------------------

--
-- Структура таблицы `machine_characteristics`
--

CREATE TABLE `machine_characteristics` (
  `id` int(11) NOT NULL,
  `machine_id` int(11) NOT NULL COMMENT 'ID станка',
  `characteristic_id` int(11) NOT NULL COMMENT 'ID характеристики',
  `value` varchar(255) NOT NULL COMMENT 'Значение характеристики'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Дамп данных таблицы `machine_characteristics`
--

INSERT INTO `machine_characteristics` (`id`, `machine_id`, `characteristic_id`, `value`) VALUES
(14, 1, 1, '5000мм'),
(15, 1, 2, '4000мм'),
(3, 2, 1, '3000мм'),
(4, 2, 2, '2500мм'),
(5, 3, 1, '5000мм'),
(6, 3, 2, '4000мм'),
(16, 5, 1, '2000мм'),
(8, 6, 1, '3000мм'),
(9, 6, 2, '2000мм');

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `machine_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_phone` varchar(50) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('new','processed','completed','cancelled') DEFAULT 'new'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`order_id`, `machine_id`, `customer_name`, `customer_phone`, `customer_email`, `quantity`, `total_price`, `order_date`, `status`) VALUES
(1, 5, 'Алексей ', '89287697591', 'Lesha0410@yandex.ru', 3, 44999.97, '2024-11-27 14:21:33', 'new'),
(4, 5, 'Алексей', '89287697591', 'Lesha0410@yandex.ru', 2, 29999.98, '2024-11-28 17:46:25', 'new'),
(5, 1, 'Алексей', '89287697591', 'Lesha0410@yandex.ru', 1, 10000.00, '2024-11-28 17:52:35', 'new'),
(6, 2, 'Алексей', '89287697591', 'Lesha0410@yandex.ru', 1, 15000.00, '2024-11-28 17:53:31', 'new'),
(7, 2, 'Алексей', '89287697591', 'Lesha0410@yandex.ru', 1, 15000.00, '2024-11-28 17:56:10', 'new'),
(8, 5, 'Алексей', '89287697591', 'Lesha0410@yandex.ru', 2, 29999.98, '2024-11-29 14:02:01', 'completed'),
(9, 6, 'Alex', '89287697591', 'Lesha0410@yandex.ru', 1, 12000000.00, '2024-12-07 11:27:42', 'new'),
(10, 6, 'Alex', '89287697591', 'Lesha041004@yandex.ru', 2, 24000000.00, '2024-12-07 11:30:47', 'new');

-- --------------------------------------------------------

--
-- Структура таблицы `order_status_log`
--

CREATE TABLE `order_status_log` (
  `log_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `old_status` enum('new','processed','completed','cancelled') NOT NULL,
  `new_status` enum('new','processed','completed','cancelled') NOT NULL,
  `changed_by` varchar(100) NOT NULL,
  `change_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Дамп данных таблицы `order_status_log`
--

INSERT INTO `order_status_log` (`log_id`, `order_id`, `old_status`, `new_status`, `changed_by`, `change_time`) VALUES
(1, 8, 'new', 'processed', 'Администратор', '2024-11-30 12:04:24'),
(2, 8, 'processed', 'completed', 'Администратор', '2024-11-30 12:08:10'),
(3, 8, 'completed', 'processed', 'Администратор', '2024-11-30 12:21:05'),
(4, 8, 'processed', 'completed', 'Администратор', '2024-12-04 11:47:31');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `characteristics`
--
ALTER TABLE `characteristics`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `machines`
--
ALTER TABLE `machines`
  ADD PRIMARY KEY (`machine_id`),
  ADD KEY `idx_machines_model` (`model_name`),
  ADD KEY `idx_machines_manufacturer` (`manufacturer`),
  ADD KEY `idx_machines_price` (`price`);

--
-- Индексы таблицы `machine_characteristics`
--
ALTER TABLE `machine_characteristics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `machine_id` (`machine_id`),
  ADD KEY `characteristic_id` (`characteristic_id`),
  ADD KEY `idx_machine_char` (`machine_id`,`characteristic_id`,`value`);

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `idx_order_date` (`order_date`),
  ADD KEY `idx_customer_email` (`customer_email`),
  ADD KEY `idx_orders_status` (`status`),
  ADD KEY `idx_orders_date` (`order_date`),
  ADD KEY `idx_orders_customer` (`customer_email`),
  ADD KEY `idx_orders_machine_status` (`machine_id`,`status`);

--
-- Индексы таблицы `order_status_log`
--
ALTER TABLE `order_status_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `order_id` (`order_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `characteristics`
--
ALTER TABLE `characteristics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `machines`
--
ALTER TABLE `machines`
  MODIFY `machine_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `machine_characteristics`
--
ALTER TABLE `machine_characteristics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT для таблицы `order_status_log`
--
ALTER TABLE `order_status_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `machine_characteristics`
--
ALTER TABLE `machine_characteristics`
  ADD CONSTRAINT `fk_characteristic` FOREIGN KEY (`characteristic_id`) REFERENCES `characteristics` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_machine` FOREIGN KEY (`machine_id`) REFERENCES `machines` (`machine_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`machine_id`) REFERENCES `machines` (`machine_id`);

--
-- Ограничения внешнего ключа таблицы `order_status_log`
--
ALTER TABLE `order_status_log`
  ADD CONSTRAINT `order_status_log_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
