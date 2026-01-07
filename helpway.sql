-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Янв 07 2026 г., 17:05
-- Версия сервера: 10.4.32-MariaDB
-- Версия PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `helpway`
--

-- --------------------------------------------------------

--
-- Структура таблицы `application`
--

CREATE TABLE `application` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `datetime` datetime NOT NULL DEFAULT current_timestamp(),
  `type` varchar(100) NOT NULL,
  `comment` text DEFAULT NULL,
  `start_address` varchar(255) NOT NULL,
  `end_id` bigint(20) NOT NULL,
  `go_date` date NOT NULL,
  `end_type` enum('mfc','polyclinic','uprava') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `images`
--

CREATE TABLE `images` (
  `id` bigint(20) NOT NULL,
  `photo` longblob NOT NULL,
  `disc` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `mfc_availability_elements`
--

CREATE TABLE `mfc_availability_elements` (
  `id` bigint(20) NOT NULL,
  `global_id` bigint(20) NOT NULL,
  `is_on_balance` varchar(50) DEFAULT NULL,
  `area_mgn` text DEFAULT NULL,
  `available_degree` varchar(50) DEFAULT NULL,
  `available_index` varchar(100) DEFAULT NULL,
  `element_mgn` text DEFAULT NULL,
  `group_mgn` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `mfc_availability_summary`
--

CREATE TABLE `mfc_availability_summary` (
  `id` bigint(20) NOT NULL,
  `global_id` bigint(20) NOT NULL,
  `available_g` varchar(50) DEFAULT NULL,
  `available_k` varchar(50) DEFAULT NULL,
  `available_o` varchar(50) DEFAULT NULL,
  `available_s` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `mfc_centers`
--

CREATE TABLE `mfc_centers` (
  `global_id` bigint(20) NOT NULL,
  `common_name` text DEFAULT NULL,
  `full_name` text DEFAULT NULL,
  `short_name` text DEFAULT NULL,
  `adm_area` varchar(255) DEFAULT NULL,
  `district` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `chief_name` text DEFAULT NULL,
  `chief_position` text DEFAULT NULL,
  `clarification_working_hours` text DEFAULT NULL,
  `sign_of_activity` text DEFAULT NULL,
  `open_date` date DEFAULT NULL,
  `close_date` date DEFAULT NULL,
  `reopen_date` date DEFAULT NULL,
  `center_area` decimal(10,2) DEFAULT NULL,
  `window_count` int(11) DEFAULT NULL,
  `website` text DEFAULT NULL,
  `diameter_name` varchar(255) DEFAULT NULL,
  `railway_lines` varchar(255) DEFAULT NULL,
  `is_on_balance` varchar(50) DEFAULT NULL,
  `lon` double DEFAULT NULL,
  `lat` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `mfc_metro_lines`
--

CREATE TABLE `mfc_metro_lines` (
  `id` bigint(20) NOT NULL,
  `global_id` bigint(20) NOT NULL,
  `line_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `mfc_working_hours`
--

CREATE TABLE `mfc_working_hours` (
  `id` bigint(20) NOT NULL,
  `global_id` bigint(20) NOT NULL,
  `day_of_week` varchar(64) DEFAULT NULL,
  `hours` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `password_hashes`
--

CREATE TABLE `password_hashes` (
  `user_id` bigint(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `polyclinics`
--

CREATE TABLE `polyclinics` (
  `global_id` bigint(20) NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `full_name` text DEFAULT NULL,
  `short_name` text DEFAULT NULL,
  `specialization` text DEFAULT NULL,
  `age_restriction` varchar(100) DEFAULT NULL,
  `paid_service_info` text DEFAULT NULL,
  `beneficial_drug_prescription` text DEFAULT NULL,
  `drug_store` varchar(50) DEFAULT NULL,
  `drug_store_type` varchar(255) DEFAULT NULL,
  `ambulance_station` varchar(50) DEFAULT NULL,
  `close_flag` varchar(50) DEFAULT NULL,
  `close_reason` text DEFAULT NULL,
  `close_date` varchar(50) DEFAULT NULL,
  `reopen_date` varchar(50) DEFAULT NULL,
  `bed_space` varchar(255) DEFAULT NULL,
  `extrainfo` text DEFAULT NULL,
  `clarification_working_hours` text DEFAULT NULL,
  `org_full_name` text DEFAULT NULL,
  `inn` varchar(32) DEFAULT NULL,
  `kpp` varchar(32) DEFAULT NULL,
  `ogrn` varchar(32) DEFAULT NULL,
  `legal_address` text DEFAULT NULL,
  `org_chief_name` text DEFAULT NULL,
  `org_chief_position` text DEFAULT NULL,
  `org_chief_gender` varchar(32) DEFAULT NULL,
  `chief_name` text DEFAULT NULL,
  `chief_position` text DEFAULT NULL,
  `chief_gender` varchar(32) DEFAULT NULL,
  `adm_area` varchar(255) DEFAULT NULL,
  `district` varchar(255) DEFAULT NULL,
  `postal_code` varchar(32) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `available_k` varchar(50) DEFAULT NULL,
  `available_o` varchar(50) DEFAULT NULL,
  `available_z` varchar(50) DEFAULT NULL,
  `available_s` varchar(50) DEFAULT NULL,
  `lon` double DEFAULT NULL,
  `lat` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `polyclinic_availability_elements`
--

CREATE TABLE `polyclinic_availability_elements` (
  `id` bigint(20) NOT NULL,
  `global_id` bigint(20) NOT NULL,
  `group_mgn` varchar(255) DEFAULT NULL,
  `area_mgn` text DEFAULT NULL,
  `element_mgn` text DEFAULT NULL,
  `available_degree` varchar(50) DEFAULT NULL,
  `available_index` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `polyclinic_chief_phones`
--

CREATE TABLE `polyclinic_chief_phones` (
  `id` bigint(20) NOT NULL,
  `global_id` bigint(20) NOT NULL,
  `phone` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `polyclinic_doctors_specialties`
--

CREATE TABLE `polyclinic_doctors_specialties` (
  `id` bigint(20) NOT NULL,
  `global_id` bigint(20) NOT NULL,
  `specialty` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `polyclinic_emails`
--

CREATE TABLE `polyclinic_emails` (
  `id` bigint(20) NOT NULL,
  `global_id` bigint(20) NOT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `polyclinic_org_chief_phones`
--

CREATE TABLE `polyclinic_org_chief_phones` (
  `id` bigint(20) NOT NULL,
  `global_id` bigint(20) NOT NULL,
  `phone` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `polyclinic_public_phones`
--

CREATE TABLE `polyclinic_public_phones` (
  `id` bigint(20) NOT NULL,
  `global_id` bigint(20) NOT NULL,
  `phone` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `polyclinic_working_hours`
--

CREATE TABLE `polyclinic_working_hours` (
  `id` bigint(20) NOT NULL,
  `global_id` bigint(20) NOT NULL,
  `day_week` varchar(64) DEFAULT NULL,
  `work_hours` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `upravas`
--

CREATE TABLE `upravas` (
  `global_id` bigint(20) NOT NULL,
  `name` text DEFAULT NULL,
  `authority_head` text DEFAULT NULL,
  `authority_area` varchar(255) DEFAULT NULL,
  `adm_area` varchar(255) DEFAULT NULL,
  `district` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `clarification_working_hours` text DEFAULT NULL,
  `website` text DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `departmental_affiliation` text DEFAULT NULL,
  `lon` double DEFAULT NULL,
  `lat` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `uprava_phones`
--

CREATE TABLE `uprava_phones` (
  `id` bigint(20) NOT NULL,
  `global_id` bigint(20) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `phone_comment` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `uprava_working_hours`
--

CREATE TABLE `uprava_working_hours` (
  `id` bigint(20) NOT NULL,
  `global_id` bigint(20) NOT NULL,
  `day_of_week` varchar(64) DEFAULT NULL,
  `hours` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` bigint(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `l_name` varchar(100) NOT NULL,
  `f_name` varchar(100) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `role` varchar(10) NOT NULL DEFAULT 'client'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `application`
--
ALTER TABLE `application`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_application_user` (`user_id`);

--
-- Индексы таблицы `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `mfc_availability_elements`
--
ALTER TABLE `mfc_availability_elements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ae_global` (`global_id`),
  ADD KEY `idx_ae_group` (`group_mgn`);

--
-- Индексы таблицы `mfc_availability_summary`
--
ALTER TABLE `mfc_availability_summary`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_as_global` (`global_id`);

--
-- Индексы таблицы `mfc_centers`
--
ALTER TABLE `mfc_centers`
  ADD PRIMARY KEY (`global_id`);

--
-- Индексы таблицы `mfc_metro_lines`
--
ALTER TABLE `mfc_metro_lines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ml_global` (`global_id`),
  ADD KEY `idx_ml_line` (`line_name`);

--
-- Индексы таблицы `mfc_working_hours`
--
ALTER TABLE `mfc_working_hours`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_wh_global` (`global_id`);

--
-- Индексы таблицы `password_hashes`
--
ALTER TABLE `password_hashes`
  ADD PRIMARY KEY (`user_id`);

--
-- Индексы таблицы `polyclinics`
--
ALTER TABLE `polyclinics`
  ADD PRIMARY KEY (`global_id`);

--
-- Индексы таблицы `polyclinic_availability_elements`
--
ALTER TABLE `polyclinic_availability_elements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pae_gid` (`global_id`),
  ADD KEY `idx_pae_group` (`group_mgn`(190));

--
-- Индексы таблицы `polyclinic_chief_phones`
--
ALTER TABLE `polyclinic_chief_phones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pcp_gid` (`global_id`);

--
-- Индексы таблицы `polyclinic_doctors_specialties`
--
ALTER TABLE `polyclinic_doctors_specialties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pda_gid` (`global_id`),
  ADD KEY `idx_pda_spec` (`specialty`);

--
-- Индексы таблицы `polyclinic_emails`
--
ALTER TABLE `polyclinic_emails`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pem_gid` (`global_id`);

--
-- Индексы таблицы `polyclinic_org_chief_phones`
--
ALTER TABLE `polyclinic_org_chief_phones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pocp_gid` (`global_id`);

--
-- Индексы таблицы `polyclinic_public_phones`
--
ALTER TABLE `polyclinic_public_phones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ppp_gid` (`global_id`);

--
-- Индексы таблицы `polyclinic_working_hours`
--
ALTER TABLE `polyclinic_working_hours`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pwh_gid` (`global_id`);

--
-- Индексы таблицы `upravas`
--
ALTER TABLE `upravas`
  ADD PRIMARY KEY (`global_id`);

--
-- Индексы таблицы `uprava_phones`
--
ALTER TABLE `uprava_phones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_uprava_phone_global` (`global_id`);

--
-- Индексы таблицы `uprava_working_hours`
--
ALTER TABLE `uprava_working_hours`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_uprava_wh_global` (`global_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `application`
--
ALTER TABLE `application`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `images`
--
ALTER TABLE `images`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `mfc_availability_elements`
--
ALTER TABLE `mfc_availability_elements`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `mfc_availability_summary`
--
ALTER TABLE `mfc_availability_summary`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `mfc_metro_lines`
--
ALTER TABLE `mfc_metro_lines`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `mfc_working_hours`
--
ALTER TABLE `mfc_working_hours`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `polyclinic_availability_elements`
--
ALTER TABLE `polyclinic_availability_elements`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `polyclinic_chief_phones`
--
ALTER TABLE `polyclinic_chief_phones`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `polyclinic_doctors_specialties`
--
ALTER TABLE `polyclinic_doctors_specialties`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `polyclinic_emails`
--
ALTER TABLE `polyclinic_emails`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `polyclinic_org_chief_phones`
--
ALTER TABLE `polyclinic_org_chief_phones`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `polyclinic_public_phones`
--
ALTER TABLE `polyclinic_public_phones`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `polyclinic_working_hours`
--
ALTER TABLE `polyclinic_working_hours`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `uprava_phones`
--
ALTER TABLE `uprava_phones`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `uprava_working_hours`
--
ALTER TABLE `uprava_working_hours`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `application`
--
ALTER TABLE `application`
  ADD CONSTRAINT `fk_application_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `mfc_availability_elements`
--
ALTER TABLE `mfc_availability_elements`
  ADD CONSTRAINT `fk_ae_center` FOREIGN KEY (`global_id`) REFERENCES `mfc_centers` (`global_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `mfc_availability_summary`
--
ALTER TABLE `mfc_availability_summary`
  ADD CONSTRAINT `fk_as_center` FOREIGN KEY (`global_id`) REFERENCES `mfc_centers` (`global_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `mfc_metro_lines`
--
ALTER TABLE `mfc_metro_lines`
  ADD CONSTRAINT `fk_ml_center` FOREIGN KEY (`global_id`) REFERENCES `mfc_centers` (`global_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `mfc_working_hours`
--
ALTER TABLE `mfc_working_hours`
  ADD CONSTRAINT `fk_wh_center` FOREIGN KEY (`global_id`) REFERENCES `mfc_centers` (`global_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `password_hashes`
--
ALTER TABLE `password_hashes`
  ADD CONSTRAINT `fk_password_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `polyclinic_availability_elements`
--
ALTER TABLE `polyclinic_availability_elements`
  ADD CONSTRAINT `polyclinic_availability_elements_ibfk_1` FOREIGN KEY (`global_id`) REFERENCES `polyclinics` (`global_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `polyclinic_chief_phones`
--
ALTER TABLE `polyclinic_chief_phones`
  ADD CONSTRAINT `polyclinic_chief_phones_ibfk_1` FOREIGN KEY (`global_id`) REFERENCES `polyclinics` (`global_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `polyclinic_doctors_specialties`
--
ALTER TABLE `polyclinic_doctors_specialties`
  ADD CONSTRAINT `polyclinic_doctors_specialties_ibfk_1` FOREIGN KEY (`global_id`) REFERENCES `polyclinics` (`global_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `polyclinic_emails`
--
ALTER TABLE `polyclinic_emails`
  ADD CONSTRAINT `polyclinic_emails_ibfk_1` FOREIGN KEY (`global_id`) REFERENCES `polyclinics` (`global_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `polyclinic_org_chief_phones`
--
ALTER TABLE `polyclinic_org_chief_phones`
  ADD CONSTRAINT `polyclinic_org_chief_phones_ibfk_1` FOREIGN KEY (`global_id`) REFERENCES `polyclinics` (`global_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `polyclinic_public_phones`
--
ALTER TABLE `polyclinic_public_phones`
  ADD CONSTRAINT `polyclinic_public_phones_ibfk_1` FOREIGN KEY (`global_id`) REFERENCES `polyclinics` (`global_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `polyclinic_working_hours`
--
ALTER TABLE `polyclinic_working_hours`
  ADD CONSTRAINT `polyclinic_working_hours_ibfk_1` FOREIGN KEY (`global_id`) REFERENCES `polyclinics` (`global_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `uprava_phones`
--
ALTER TABLE `uprava_phones`
  ADD CONSTRAINT `fk_uprava_phone` FOREIGN KEY (`global_id`) REFERENCES `upravas` (`global_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `uprava_working_hours`
--
ALTER TABLE `uprava_working_hours`
  ADD CONSTRAINT `fk_uprava_wh` FOREIGN KEY (`global_id`) REFERENCES `upravas` (`global_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
