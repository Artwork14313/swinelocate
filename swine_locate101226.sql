-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 12, 2026 at 04:56 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `swine_locate`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `farmers`
--

CREATE TABLE `farmers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `farm_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `contact_number` varchar(30) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `farmers`
--

INSERT INTO `farmers` (`id`, `farm_id`, `name`, `contact_number`, `email`, `address`, `created_at`, `updated_at`) VALUES
(1, 2, 'Ben 10', '09656546', NULL, 'sagay', '2026-10-09 06:25:51', '2026-10-09 06:35:00'),
(2, 2, 'tests', '2133123', NULL, 'adad', '2026-10-12 00:30:04', '2026-10-12 00:30:04'),
(3, 2, 'benjo', '123213', NULL, 'asdasd', '2026-10-12 01:23:26', '2026-10-12 01:23:26');

-- --------------------------------------------------------

--
-- Table structure for table `farms`
--

CREATE TABLE `farms` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `farm_code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `municipality` varchar(255) DEFAULT NULL,
  `province` varchar(255) DEFAULT NULL,
  `region` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `contact_number` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `farms`
--

INSERT INTO `farms` (`id`, `farm_code`, `name`, `address`, `municipality`, `province`, `region`, `latitude`, `longitude`, `contact_number`, `email`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'FARM-001', 'Tope Backyard', 'sagay', 'Sagay', 'Negros Occidental', 'VI', 10.0000000, 123.0000000, NULL, NULL, 'active', '2026-09-25 14:06:03', '2026-09-25 14:06:03', NULL),
(2, 'FARM-002', 'Slaughter', 'Sagay', 'Sagay', 'Negros Occidental', 'VI', 12.0000000, 123.0000000, NULL, NULL, 'active', '2026-10-09 06:30:39', '2026-10-09 06:30:39', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `farm_locations`
--

CREATE TABLE `farm_locations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `farm_id` bigint(20) UNSIGNED NOT NULL,
  `location_code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `capacity` int(10) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `farm_locations`
--

INSERT INTO `farm_locations` (`id`, `farm_id`, `location_code`, `name`, `type`, `capacity`, `description`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'PEN-001', 'Grower Pen 1', 'Grower', 50, NULL, 'active', '2026-09-25 14:06:33', '2026-09-25 14:06:33', NULL),
(2, 1, 'PEN-002', 'Grower Pen 2', 'Grower', 50, NULL, 'active', '2026-09-26 10:23:49', '2026-09-26 10:23:49', NULL),
(3, 2, 'PEN-001', 'Grower Pen 1', 'Grower', 50, NULL, 'active', '2026-10-09 06:31:11', '2026-10-09 06:31:11', NULL),
(4, 2, 'PEN-002', 'Grower Pen 2', 'Grower', 50, NULL, 'active', '2026-10-12 01:47:13', '2026-10-12 01:47:13', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `health_records`
--

CREATE TABLE `health_records` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `swine_id` bigint(20) UNSIGNED NOT NULL,
  `recorded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `record_date` date NOT NULL,
  `record_type` varchar(255) NOT NULL,
  `vaccine_name` varchar(255) DEFAULT NULL,
  `dose` varchar(255) DEFAULT NULL,
  `batch_number` varchar(255) DEFAULT NULL,
  `next_due_date` date DEFAULT NULL,
  `symptoms` text DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `treatment` text DEFAULT NULL,
  `observations` text DEFAULT NULL,
  `veterinary_assessment` text DEFAULT NULL,
  `health_status` varchar(255) NOT NULL DEFAULT 'healthy',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `local_id` char(36) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `health_records`
--

INSERT INTO `health_records` (`id`, `swine_id`, `recorded_by`, `record_date`, `record_type`, `vaccine_name`, `dose`, `batch_number`, `next_due_date`, `symptoms`, `diagnosis`, `treatment`, `observations`, `veterinary_assessment`, `health_status`, `notes`, `created_at`, `updated_at`, `deleted_at`, `local_id`) VALUES
(1, 1, 2, '2026-09-25', 'Vaccination', 'Swine fever vaccine', '2 ml', NULL, '2026-10-10', NULL, NULL, NULL, NULL, NULL, 'healthy', NULL, '2026-09-25 14:22:43', '2026-09-25 14:22:43', NULL, NULL),
(2, 1, 1, '2026-09-26', 'Follow-up', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'recovering', NULL, '2026-09-26 10:08:02', '2026-09-26 10:08:02', NULL, NULL),
(3, 4, 1, '2026-10-12', 'Illness', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'sick', 'offline', '2026-10-12 01:40:55', '2026-10-12 02:03:38', NULL, '3f0cf9a5-c3e5-4a0c-aa25-77e93371db1c');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_08_18_063418_create_roles_table', 1),
(5, '2026_08_18_063442_create_permissions_table', 1),
(6, '2026_08_18_063506_create_role_permission_table', 1),
(7, '2026_08_18_063529_create_farms_table', 1),
(8, '2026_08_18_063559_create_farm_locations_table', 1),
(9, '2026_08_18_064027_add_swinelocate_fields_to_users_table', 1),
(10, '2026_08_18_064301_create_swine_table', 1),
(11, '2026_08_20_052551_add_qr_token_to_swine_table', 1),
(12, '2026_08_20_075111_create_swine_movements_table', 1),
(13, '2026_08_21_175938_create_health_records_table', 1),
(14, '2026_08_21_180000_create_vaccination_records_table', 1),
(15, '2026_08_24_145832_add_vaccination_fields_to_health_records_table', 1),
(16, '2026_08_26_114255_create_weight_records_table', 1),
(17, '2026_08_26_141435_add_deleted_at_to_swine_movements_table', 1),
(18, '2026_09_03_093540_add_status_to_swine_movements_table', 1),
(19, '2026_09_08_093353_add_local_id_to_weight_records_table', 1),
(20, '2026_10_09_140000_create_farmers_table', 2),
(21, '2026_10_12_000000_create_system_evaluations_table', 3),
(22, '2026_10_12_090000_add_local_id_to_health_records_table', 4),
(23, '2026_10_12_091000_add_destination_farmer_to_swine_movements', 4);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Manage Users', 'manage-users', 'Create, update, and manage users', '2026-09-25 13:37:59', '2026-10-09 06:24:16'),
(2, 'Manage Roles', 'manage-roles', 'Manage system roles and permissions', '2026-09-25 13:37:59', '2026-10-09 06:24:16'),
(3, 'Manage Farms', 'manage-farms', 'Create and manage farms', '2026-09-25 13:37:59', '2026-10-09 06:24:16'),
(4, 'Manage Locations', 'manage-locations', 'Manage farm locations and pens', '2026-09-25 13:37:59', '2026-10-09 06:24:17'),
(5, 'Manage Swine', 'manage-swine', 'Manage swine profiles', '2026-09-25 13:37:59', '2026-10-09 06:24:17'),
(7, 'Manage Vaccinations', 'manage-vaccinations', 'Manage vaccination records', '2026-09-25 13:37:59', '2026-09-25 13:37:59'),
(8, 'Manage Weight Records', 'manage-weight-records', 'Manage weight and growth records', '2026-09-25 13:37:59', '2026-09-25 13:37:59'),
(9, 'Manage Movements', 'manage-movements', 'Record swine movements', '2026-09-25 13:37:59', '2026-10-09 06:24:17'),
(10, 'View Reports', 'view-reports', 'View system reports', '2026-09-25 13:37:59', '2026-10-09 06:24:17'),
(11, 'Manage Health Records', 'manage-health', 'Manage veterinary and health records', '2026-09-25 13:44:27', '2026-10-09 06:24:17'),
(12, 'Record Weight', 'record-weight', 'Record swine weight', '2026-09-25 13:44:27', '2026-10-09 06:24:17'),
(13, 'Scan QR Codes', 'scan-qr', 'Scan swine QR codes', '2026-09-25 13:44:27', '2026-10-09 06:24:17'),
(14, 'View Traceability', 'view-traceability', 'View swine traceability information', '2026-09-25 13:44:27', '2026-09-25 13:50:42'),
(15, 'Manage Backups', 'manage-backups', 'Manage system backups', '2026-09-25 13:44:27', '2026-10-09 06:24:17'),
(28, 'Register Swine', 'register-swine', 'Register new swine', '2026-09-25 14:10:33', '2026-10-09 06:24:17'),
(29, 'Manage Farmers', 'manage-farmers', 'Register and manage farmer profiles', '2026-10-09 06:24:17', '2026-10-09 06:24:17'),
(30, 'Generate QR Codes', 'generate-qr', 'Generate QR codes for swine', '2026-10-09 06:24:17', '2026-10-09 06:24:17'),
(31, 'View Evaluation Results', 'view-evaluation-results', 'View aggregate system quality and usability evaluation results', '2026-10-12 00:04:53', '2026-10-12 00:04:53');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'administrator', 'Full system access', '2026-09-25 13:34:47', '2026-10-09 06:24:16'),
(2, 'Veterinarian', 'veterinarian', 'Manages swine health and veterinary records', '2026-09-25 13:48:18', '2026-10-09 06:24:16'),
(3, 'Farm Manager', 'farm-manager', 'Manages farm and swine operations', '2026-09-25 13:48:18', '2026-10-09 06:24:16'),
(4, 'Farm Staff', 'farm-staff', 'Performs farm-level recording and scanning', '2026-09-25 13:48:18', '2026-10-09 06:24:16');

-- --------------------------------------------------------

--
-- Table structure for table `role_permission`
--

CREATE TABLE `role_permission` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permission`
--

INSERT INTO `role_permission` (`role_id`, `permission_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 7),
(1, 8),
(1, 9),
(1, 10),
(1, 11),
(1, 12),
(1, 13),
(1, 14),
(1, 15),
(1, 28),
(1, 29),
(1, 30),
(1, 31),
(2, 5),
(2, 10),
(2, 11),
(2, 31),
(3, 3),
(3, 4),
(3, 5),
(3, 9),
(3, 10),
(3, 11),
(3, 12),
(3, 13),
(3, 14),
(3, 28),
(3, 29),
(3, 30),
(3, 31),
(4, 9),
(4, 12),
(4, 13),
(4, 14);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `swine`
--

CREATE TABLE `swine` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `farm_id` bigint(20) UNSIGNED NOT NULL,
  `farmer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `local_id` char(36) DEFAULT NULL,
  `current_location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tag_number` varchar(255) NOT NULL,
  `qr_token` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `sex` varchar(255) NOT NULL,
  `breed` varchar(255) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `acquisition_date` date DEFAULT NULL,
  `source` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `swine`
--

INSERT INTO `swine` (`id`, `farm_id`, `farmer_id`, `local_id`, `current_location_id`, `tag_number`, `qr_token`, `name`, `sex`, `breed`, `birth_date`, `acquisition_date`, `source`, `status`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, NULL, NULL, 1, 'SW-000001', 'SWL-YDUQGTZ29AS0', NULL, 'male', 'Large White', NULL, NULL, NULL, 'active', NULL, '2026-09-25 14:19:57', '2026-09-25 14:19:57', NULL),
(2, 1, NULL, NULL, 1, 'SW-000002', 'SWL-VLNKGOTK1WP0', NULL, 'male', 'Large White', NULL, NULL, NULL, 'active', NULL, '2026-09-26 10:17:47', '2026-09-26 10:17:47', NULL),
(3, 1, NULL, NULL, 2, 'SW-000003', 'SWL-ELNLKJWTDNIO', NULL, 'female', 'Small white', NULL, NULL, NULL, 'active', NULL, '2026-09-26 10:18:14', '2026-09-26 10:24:06', NULL),
(4, 2, 1, NULL, 3, 'SW-000005', 'SWL-KXHEV2DZSB13', NULL, 'male', 'Large white', NULL, NULL, NULL, 'active', NULL, '2026-10-09 06:35:51', '2026-10-09 06:35:51', NULL),
(5, 2, 3, '6b9c9de7-63fb-4686-8621-76f17c3e3803', 4, 'SW-000006', '98cbc261-e11b-43df-bebe-ef1b75c37af8', NULL, 'female', 'Small White', NULL, NULL, NULL, 'active', 'offline', '2026-10-12 01:37:48', '2026-10-12 01:57:54', NULL),
(6, 2, 2, 'fa9586e4-a96d-407e-8c62-91da99fa4f76', 3, 'SW-000007', 'b480769d-7f07-42ad-a4b1-461526b7dca4', NULL, 'male', NULL, NULL, NULL, NULL, 'active', NULL, '2026-10-12 02:15:01', '2026-10-12 02:15:01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `swine_movements`
--

CREATE TABLE `swine_movements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `swine_id` bigint(20) UNSIGNED NOT NULL,
  `from_location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `to_location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `to_farmer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `movement_date` datetime NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'completed',
  `conflict_resolution` varchar(255) DEFAULT NULL,
  `recorded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `swine_movements`
--

INSERT INTO `swine_movements` (`id`, `swine_id`, `from_location_id`, `to_location_id`, `to_farmer_id`, `movement_date`, `reason`, `notes`, `status`, `conflict_resolution`, `recorded_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 3, 1, 2, NULL, '2026-09-26 18:24:00', NULL, NULL, 'completed', NULL, 1, '2026-09-26 10:24:06', '2026-09-26 10:24:06', NULL),
(2, 5, 3, 4, 3, '2026-10-12 09:57:00', NULL, NULL, 'completed', NULL, 1, '2026-10-12 01:57:54', '2026-10-12 01:57:54', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED DEFAULT NULL,
  `farm_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `farm_id`, `name`, `email`, `phone`, `status`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, NULL, 'admin', 'admin@gmail.com', NULL, 'active', NULL, '$2y$12$cCNFqJIrgwcdTv7DBNoFu.JoGsqCvkOYJb42s6fPYMFuJVNkKwHuC', NULL, '2026-09-25 13:27:26', '2026-09-25 13:27:26', NULL),
(2, 2, NULL, 'test', 'test@gmail.com', NULL, 'active', NULL, '$2y$12$K7GF8xrzCb5dWFp0.Xnx5uHMRkJWndhzbLsg849SeljdN1Az5qysG', NULL, '2026-09-25 13:51:55', '2026-09-25 13:55:05', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `weight_records`
--

CREATE TABLE `weight_records` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `local_id` varchar(100) DEFAULT NULL,
  `swine_id` bigint(20) UNSIGNED NOT NULL,
  `recorded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `record_date` date NOT NULL,
  `weight` decimal(8,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `weight_records`
--

INSERT INTO `weight_records` (`id`, `local_id`, `swine_id`, `recorded_by`, `record_date`, `weight`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, NULL, 1, 1, '2026-09-26', 15.00, NULL, '2026-09-26 10:24:27', '2026-09-26 10:24:27', NULL),
(2, NULL, 1, 1, '2026-09-26', 20.00, NULL, '2026-09-26 10:24:39', '2026-09-26 10:24:39', NULL),
(3, NULL, 1, 1, '2026-09-26', 30.00, NULL, '2026-09-26 10:24:51', '2026-09-26 10:24:51', NULL),
(4, '0ff0387a-358a-4401-a756-b28ab783d57b', 1, 1, '2026-10-12', 40.00, NULL, '2026-10-12 00:30:32', '2026-10-12 00:30:32', NULL),
(5, '74a06c46-5b1a-4b7d-b91a-ff6585241111', 4, 1, '2026-10-12', 7.00, 'offline test', '2026-10-12 01:41:53', '2026-10-12 02:03:37', NULL),
(6, '15153e8c-c187-4cbc-9df0-1205dcf55ebf', 6, 1, '2026-10-12', 11.00, NULL, '2026-10-12 02:50:50', '2026-10-12 02:50:50', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `farmers`
--
ALTER TABLE `farmers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `farmers_farm_id_foreign` (`farm_id`);

--
-- Indexes for table `farms`
--
ALTER TABLE `farms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `farms_farm_code_unique` (`farm_code`);

--
-- Indexes for table `farm_locations`
--
ALTER TABLE `farm_locations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `farm_locations_farm_id_location_code_unique` (`farm_id`,`location_code`);

--
-- Indexes for table `health_records`
--
ALTER TABLE `health_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `health_records_local_id_unique` (`local_id`),
  ADD KEY `health_records_swine_id_foreign` (`swine_id`),
  ADD KEY `health_records_recorded_by_foreign` (`recorded_by`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_slug_unique` (`slug`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_slug_unique` (`slug`);

--
-- Indexes for table `role_permission`
--
ALTER TABLE `role_permission`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `role_permission_permission_id_foreign` (`permission_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `swine`
--
ALTER TABLE `swine`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `swine_tag_number_unique` (`tag_number`),
  ADD UNIQUE KEY `swine_qr_token_unique` (`qr_token`),
  ADD UNIQUE KEY `swine_local_id_unique` (`local_id`),
  ADD KEY `swine_farm_id_foreign` (`farm_id`),
  ADD KEY `swine_current_location_id_foreign` (`current_location_id`),
  ADD KEY `swine_farmer_id_foreign` (`farmer_id`);

--
-- Indexes for table `swine_movements`
--
ALTER TABLE `swine_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `swine_movements_swine_id_foreign` (`swine_id`),
  ADD KEY `swine_movements_from_location_id_foreign` (`from_location_id`),
  ADD KEY `swine_movements_to_location_id_foreign` (`to_location_id`),
  ADD KEY `swine_movements_recorded_by_foreign` (`recorded_by`),
  ADD KEY `swine_movements_to_farmer_id_foreign` (`to_farmer_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_role_id_foreign` (`role_id`),
  ADD KEY `users_farm_id_foreign` (`farm_id`);

--
-- Indexes for table `weight_records`
--
ALTER TABLE `weight_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `weight_records_local_id_unique` (`local_id`),
  ADD KEY `weight_records_swine_id_foreign` (`swine_id`),
  ADD KEY `weight_records_recorded_by_foreign` (`recorded_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `farmers`
--
ALTER TABLE `farmers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `farms`
--
ALTER TABLE `farms`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `farm_locations`
--
ALTER TABLE `farm_locations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `health_records`
--
ALTER TABLE `health_records`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `swine`
--
ALTER TABLE `swine`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `swine_movements`
--
ALTER TABLE `swine_movements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `weight_records`
--
ALTER TABLE `weight_records`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `farmers`
--
ALTER TABLE `farmers`
  ADD CONSTRAINT `farmers_farm_id_foreign` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`);

--
-- Constraints for table `farm_locations`
--
ALTER TABLE `farm_locations`
  ADD CONSTRAINT `farm_locations_farm_id_foreign` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `health_records`
--
ALTER TABLE `health_records`
  ADD CONSTRAINT `health_records_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `health_records_swine_id_foreign` FOREIGN KEY (`swine_id`) REFERENCES `swine` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_permission`
--
ALTER TABLE `role_permission`
  ADD CONSTRAINT `role_permission_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permission_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `swine`
--
ALTER TABLE `swine`
  ADD CONSTRAINT `swine_current_location_id_foreign` FOREIGN KEY (`current_location_id`) REFERENCES `farm_locations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `swine_farm_id_foreign` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `swine_farmer_id_foreign` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `swine_movements`
--
ALTER TABLE `swine_movements`
  ADD CONSTRAINT `swine_movements_from_location_id_foreign` FOREIGN KEY (`from_location_id`) REFERENCES `farm_locations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `swine_movements_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `swine_movements_swine_id_foreign` FOREIGN KEY (`swine_id`) REFERENCES `swine` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `swine_movements_to_farmer_id_foreign` FOREIGN KEY (`to_farmer_id`) REFERENCES `farmers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `swine_movements_to_location_id_foreign` FOREIGN KEY (`to_location_id`) REFERENCES `farm_locations` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_farm_id_foreign` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `weight_records`
--
ALTER TABLE `weight_records`
  ADD CONSTRAINT `weight_records_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `weight_records_swine_id_foreign` FOREIGN KEY (`swine_id`) REFERENCES `swine` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
