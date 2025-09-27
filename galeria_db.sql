-- Crear la base de datos
CREATE DATABASE galeria_db;
USE galeria_db;
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `image_id` int(11) NOT NULL,
  `added_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `images`
--

CREATE TABLE `images` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `filename` varchar(255) NOT NULL,
  `theme_id` int(11) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `images`
--

INSERT INTO `images` (`id`, `title`, `description`, `filename`, `theme_id`, `upload_date`) VALUES
(2, 'Topper', NULL, '68b1b1cc8b37b', 1, '2025-08-29 13:57:33'),
(3, 'Articulos', NULL, '68b1b5c0f2b61', 1, '2025-08-29 14:14:26'),
(4, 'Articulos', NULL, '68b1b5c2420a2', 1, '2025-08-29 14:14:27'),
(5, 'Articulos', NULL, '68b1b5c3693be', 1, '2025-08-29 14:14:29'),
(6, 'Articulos', NULL, '68b1b88c728d4', 2, '2025-08-29 14:26:22'),
(7, 'Articulos', NULL, '68b1b88e5e3cb', 2, '2025-08-29 14:26:24'),
(8, 'Articulos', NULL, '68b22ceb7b6dc', 16, '2025-08-29 22:42:52'),
(9, 'Articulos', NULL, '68b22cec479ac', 16, '2025-08-29 22:42:52'),
(10, 'Articulos', NULL, '68b22cec9b220', 16, '2025-08-29 22:42:52'),
(11, 'Articulos', NULL, '68b22ced02683', 16, '2025-08-29 22:42:53'),
(12, 'Articulos', NULL, '68b22ced59a6c', 16, '2025-08-29 22:42:53'),
(13, 'Articulos', NULL, '68b22ced943dc', 16, '2025-08-29 22:42:53'),
(16, 'Articulos', NULL, '68b22d59d42d0', 10, '2025-08-29 22:44:42'),
(17, 'Articulos', NULL, '68b22e724b1b9', 15, '2025-08-29 22:49:22'),
(18, 'Articulos', NULL, '68b22e72a1bb8', 15, '2025-08-29 22:49:23'),
(19, 'Articulos', NULL, '68b22e73d4cef', 15, '2025-08-29 22:49:24'),
(20, 'Articulos', NULL, '68b22e74386cb', 15, '2025-08-29 22:49:25'),
(21, 'Articulos', NULL, '68b22e759a378', 15, '2025-08-29 22:49:26'),
(23, 'Articulos', NULL, '68b22e7829d1e', 15, '2025-08-29 22:49:29'),
(25, 'Articulos', NULL, '68b22e79c8aa2', 15, '2025-08-29 22:49:30'),
(26, 'Articulos', NULL, '68b22e7a0824d', 15, '2025-08-29 22:49:30'),
(27, 'Articulos', NULL, '68b22e7a35344', 15, '2025-08-29 22:49:30'),
(28, 'Articulos', NULL, '68b2322ce4a59', 7, '2025-08-29 23:05:17'),
(29, 'Articulos', NULL, '68b2322d4e537', 7, '2025-08-29 23:05:17'),
(30, 'Articulos', NULL, '68b2322da0664', 7, '2025-08-29 23:05:17'),
(31, 'Articulos', NULL, '68b2322dd8e07', 7, '2025-08-29 23:05:18'),
(32, 'Articulos', NULL, '68b2322e1a36c', 7, '2025-08-29 23:05:18'),
(33, 'Articulos', NULL, '68b2322e51510', 7, '2025-08-29 23:05:19'),
(34, 'Articulos', NULL, '68b2322f793e1', 7, '2025-08-29 23:05:20'),
(35, 'Articulos', NULL, '68b23230a783f', 7, '2025-08-29 23:05:20'),
(36, 'Articulos', NULL, '68b23230e488f', 7, '2025-08-29 23:05:21'),
(37, 'Articulos', NULL, '68b232314f68d', 7, '2025-08-29 23:05:21'),
(38, 'Articulos', NULL, '68b23502aa432', 16, '2025-08-29 23:17:24'),
(39, 'Articulos', NULL, '68b2350468ec8', 16, '2025-08-29 23:17:26'),
(40, 'Articulos', NULL, '68b2350615844', 16, '2025-08-29 23:17:27'),
(41, 'Articulos', NULL, '68b23507e34a6', 16, '2025-08-29 23:17:29'),
(43, 'Articulos', NULL, '68b2350b4dbfd', 16, '2025-08-29 23:17:32'),
(44, 'Articulos', NULL, '68b235cf41aff', 10, '2025-08-29 23:20:48'),
(45, 'Articulos', NULL, '68b235d0dc858', 10, '2025-08-29 23:20:50'),
(48, 'Articulos', NULL, '68b237a216e12', 3, '2025-08-29 23:28:35'),
(49, 'Articulos', NULL, '68b237a31c980', 3, '2025-08-29 23:28:36'),
(50, 'Articulos', NULL, '68b237a4c9c9a', 3, '2025-08-29 23:28:38'),
(51, 'Articulos', NULL, '68b237a685a56', 3, '2025-08-29 23:28:40'),
(52, 'Articulos', NULL, '68b237a842fa7', 3, '2025-08-29 23:28:41'),
(53, 'Articulos', NULL, '68b237aa0072f', 3, '2025-08-29 23:28:43'),
(54, 'Articulos', NULL, '68b237abcf15d', 3, '2025-08-29 23:28:45'),
(55, 'Articulos', NULL, '68b237ad86243', 3, '2025-08-29 23:28:47'),
(57, 'Articulos', NULL, '68b237b0cfcb6', 3, '2025-08-29 23:28:50'),
(58, 'Articulos', NULL, '68b237b274763', 3, '2025-08-29 23:28:51'),
(59, 'Articulos', NULL, '68b237b393814', 3, '2025-08-29 23:28:53'),
(60, 'Articulos', NULL, '68b237b530a02', 3, '2025-08-29 23:28:54'),
(61, 'Articulos', NULL, '68b237b6c92bd', 3, '2025-08-29 23:28:56'),
(62, 'Articulos', NULL, '68b237b86869c', 3, '2025-08-29 23:28:58'),
(64, 'Articulos', NULL, '68b237bba3fb3', 3, '2025-08-29 23:29:01'),
(65, 'Articulos', NULL, '68b237bd105cc', 3, '2025-08-29 23:29:02'),
(66, 'Articulos', NULL, '68baadabc37f9', 22, '2025-09-05 09:30:21'),
(67, 'Articulos', NULL, '68baadad93479', 22, '2025-09-05 09:30:22'),
(68, 'Articulos', NULL, '68baadaea7e27', 22, '2025-09-05 09:30:24'),
(69, 'Articulos', NULL, '68baadb06e31e', 22, '2025-09-05 09:30:26'),
(71, 'Articulos', NULL, '68baadb41f416', 22, '2025-09-05 09:30:29'),
(72, 'Artículo', NULL, '68c877ecce3c9', 8, '2025-09-15 20:32:45'),
(73, 'Artículo', NULL, '68c8786333842', 8, '2025-09-15 20:34:43'),
(74, 'Artículo', NULL, '68c8786367faa', 8, '2025-09-15 20:34:43'),
(75, 'Artículo', NULL, '68c87863a91a2', 8, '2025-09-15 20:34:43'),
(76, 'Artículo', NULL, '68c87863d20f7', 8, '2025-09-15 20:34:44'),
(82, 'Artículo', NULL, '68cc0777dec08', 12, '2025-09-18 13:22:01'),
(84, 'Artículo', NULL, '68cc970154e13', 8, '2025-09-18 23:34:27'),
(89, 'Artículo', NULL, '68d2b54860a79', 11, '2025-09-23 14:57:13'),
(91, 'Artículo', NULL, '68d2b5d144706', 11, '2025-09-23 14:59:29'),
(92, 'Artículo', NULL, '68d2b68e3d26b', 12, '2025-09-23 15:02:38'),
(94, 'Artículo', NULL, '68d2bb75aa25e', 19, '2025-09-23 15:23:34'),
(98, 'Artículo', NULL, '68d41697bc3ee', 4, '2025-09-24 16:04:41'),
(102, 'Artículo', NULL, '68d49b54dc89a', 19, '2025-09-25 01:31:02'),
(103, 'Artículo', NULL, '68d49d0298d68', 19, '2025-09-25 01:38:11'),
(105, 'Artículos', NULL, '68d5d6a84d221', 11, '2025-09-25 23:56:26'),
(106, 'Artículos', NULL, '68d5d6aa5e37f', 11, '2025-09-25 23:56:28'),
(107, 'Artículos', NULL, '68d5d6ac45325', 11, '2025-09-25 23:56:30'),
(108, 'Artículos', NULL, '68d5d6ae3a1d5', 11, '2025-09-25 23:56:31'),
(109, 'Artículos', NULL, '68d5d6afbb009', 11, '2025-09-25 23:56:33'),
(110, 'Artículos', NULL, '68d5d7542dcaf', 7, '2025-09-25 23:59:17'),
(111, 'Artículos', NULL, '68d5d755f0cd0', 7, '2025-09-25 23:59:19'),
(112, 'Artículos', NULL, '68d5d7f919d9c', 15, '2025-09-26 00:02:02'),
(113, 'Artículos', NULL, '68d5d7fae9ccf', 15, '2025-09-26 00:02:04'),
(114, 'Artículos', NULL, '68d5d7fcd8444', 15, '2025-09-26 00:02:06'),
(115, 'Artículos', NULL, '68d5d7febe55b', 15, '2025-09-26 00:02:08'),
(116, 'Artículo', NULL, '68d5d8959f299', 3, '2025-09-26 00:04:39'),
(117, 'Artículo', NULL, '68d5e2a9ac1bc', 35, '2025-09-26 00:47:39'),
(118, 'Artículo', NULL, '68d5e2ab77e45', 35, '2025-09-26 00:47:41'),
(119, 'Artículo', NULL, '68d5e2ad36585', 35, '2025-09-26 00:47:42'),
(120, 'Artículo', NULL, '68d5e2aeeb94c', 35, '2025-09-26 00:47:44'),
(121, 'Artículo', NULL, '68d5e2b0a91a4', 35, '2025-09-26 00:47:46'),
(122, 'Artículo', NULL, '68d5e2b257d0c', 35, '2025-09-26 00:47:48'),
(123, 'Artículo', NULL, '68d5e2b41562a', 35, '2025-09-26 00:47:49'),
(124, 'Artículo', NULL, '68d5e2b56df08', 35, '2025-09-26 00:47:51'),
(125, 'Artículo', NULL, '68d5e2b7317a5', 35, '2025-09-26 00:47:52'),
(126, 'Artículo', NULL, '68d5e2b8eec12', 35, '2025-09-26 00:47:54'),
(127, 'Artículo', NULL, '68d5e2baad1fa', 35, '2025-09-26 00:47:56'),
(128, 'Artículo', NULL, '68d5e2bc668cc', 35, '2025-09-26 00:47:58'),
(129, 'Artículo', NULL, '68d5e2be28171', 35, '2025-09-26 00:47:59'),
(130, 'Artículo', NULL, '68d5e44277e39', 8, '2025-09-26 00:54:28'),
(131, 'Artículo', NULL, '68d5e4443d260', 8, '2025-09-26 00:54:29'),
(132, 'Artículo', NULL, '68d5e51431908', 3, '2025-09-26 00:57:57'),
(133, 'Artículo', NULL, '68d5e5bfbd089', 11, '2025-09-26 01:00:49'),
(134, 'Atículo', NULL, '68d5e5f260326', 11, '2025-09-26 01:01:41'),
(135, 'Artículo', NULL, '68d5e66d078bd', 36, '2025-09-26 01:03:42'),
(136, 'Artículo', NULL, '68d5e66ec2d74', 36, '2025-09-26 01:03:44'),
(137, 'Artículo', NULL, '68d5e670839bf', 36, '2025-09-26 01:03:46'),
(139, 'Artículo', NULL, '68d5e72fe835a', 18, '2025-09-26 01:06:56'),
(140, 'Artículo', NULL, '68d5e730e4056', 18, '2025-09-26 01:06:58'),
(141, 'Artículo', NULL, '68d5e89734999', 39, '2025-09-26 01:12:57'),
(142, 'Artículo', NULL, '68d5e899024a4', 39, '2025-09-26 01:12:58'),
(143, 'Artículo', NULL, '68d5e89a74386', 39, '2025-09-26 01:13:00'),
(144, 'Artículo', NULL, '68d5e94635404', 4, '2025-09-26 01:15:52'),
(145, 'Artículo', NULL, '68d5e9480915b', 4, '2025-09-26 01:15:53'),
(146, 'Artículo', NULL, '68d5e949dae8b', 4, '2025-09-26 01:15:55'),
(147, 'Artículo', NULL, '68d5e94b2145c', 4, '2025-09-26 01:15:56'),
(148, 'Artículo', NULL, '68d5e94c9a0f8', 4, '2025-09-26 01:15:57'),
(149, 'Artículo', NULL, '68d5e94ddc4f6', 4, '2025-09-26 01:15:58'),
(150, 'Artículo', NULL, '68d5e94ed74df', 4, '2025-09-26 01:15:59'),
(151, 'Artículo', NULL, '68d5ea2ce5142', 4, '2025-09-26 01:19:45'),
(152, 'Artículo', NULL, '68d5ea31cdd56', 4, '2025-09-26 01:19:47'),
(153, 'Artículo', NULL, '68d5eaf2d7c9b', 32, '2025-09-26 01:23:00'),
(154, 'Artículo', NULL, '68d5eb23e2308', 16, '2025-09-26 01:23:49'),
(155, 'Artículo', NULL, '68d5eb539d56d', 13, '2025-09-26 01:24:37'),
(156, 'Artículo', NULL, '68d5eb81d0b23', 10, '2025-09-26 01:25:23'),
(157, 'Artículo', NULL, '68d5eb836c13e', 10, '2025-09-26 01:25:24'),
(158, 'Artículo', NULL, '68d5eb950b982', 35, '2025-09-26 01:25:42'),
(159, 'Artículo', NULL, '68d5ec01cab2a', 14, '2025-09-26 01:27:31'),
(160, 'Artículo', NULL, '68d5ec03905c3', 14, '2025-09-26 01:27:33'),
(161, 'Artículo', NULL, '68d5ec055152f', 14, '2025-09-26 01:27:35'),
(163, 'Artículo', NULL, '68d5ecabb96ab', 40, '2025-09-26 01:30:21'),
(164, 'Artículo', NULL, '68d5ed3d0a2e6', 1, '2025-09-26 01:32:46'),
(165, 'Artículo', NULL, '68d5ed9713d6c', 2, '2025-09-26 01:34:16'),
(166, 'Artículo', NULL, '68d5edb679635', 15, '2025-09-26 01:34:48'),
(167, 'Artículo', NULL, '68d5ede4bc546', 9, '2025-09-26 01:35:34'),
(168, 'Artículo', NULL, '68d5ede634125', 9, '2025-09-26 01:35:36'),
(170, 'Artículo', NULL, '68d5edf48efc5', 9, '2025-09-26 01:35:54'),
(171, 'Artículo', NULL, '68d5edfab036c', 9, '2025-09-26 01:35:56'),
(173, 'Artículo', NULL, '68d5ee8e70436', 33, '2025-09-26 01:38:24'),
(174, 'Artículo', NULL, '68d5ee9061b2b', 33, '2025-09-26 01:38:26'),
(175, 'Artículo', NULL, '68d5ef0e317ec', 19, '2025-09-26 01:40:31'),
(176, 'Artículo', NULL, '68d5ef0fb093b', 19, '2025-09-26 01:40:33'),
(177, 'Artículo', NULL, '68d5efb811462', 41, '2025-09-26 01:43:22'),
(178, 'Artículo', NULL, '68d5efba55417', 41, '2025-09-26 01:43:23'),
(182, 'Artículo', NULL, '68d6165a05262', 42, '2025-09-26 04:28:11'),
(183, 'Artículo', NULL, '68d6165bd6d1c', 42, '2025-09-26 04:28:13'),
(184, 'Artículo', NULL, '68d6165dba5a5', 42, '2025-09-26 04:28:14'),
(185, 'Artículo', NULL, '68d6165ee7034', 42, '2025-09-26 04:28:16'),
(186, 'Artículo', NULL, '68d6188d20b81', 37, '2025-09-26 04:37:35'),
(187, 'Artículo', NULL, '68d6188f12d62', 37, '2025-09-26 04:37:36'),
(188, 'Artículo', NULL, '68d61890b92c1', 37, '2025-09-26 04:37:38'),
(189, 'Artículo', NULL, '68d619111c01f', 17, '2025-09-26 04:39:46'),
(190, 'Artículo', NULL, '68d619126e12f', 17, '2025-09-26 04:39:47'),
(191, 'Artículo', NULL, '68d61969bd8e9', 5, '2025-09-26 04:41:15'),
(192, 'Artículo', NULL, '68d619ee8e261', 7, '2025-09-26 04:43:28'),
(193, 'Artículo', NULL, '68d619f06ba9e', 7, '2025-09-26 04:43:30'),
(194, 'Artículo', NULL, '68d619f22e3f8', 7, '2025-09-26 04:43:32'),
(195, 'Artículo', NULL, '68d619f421aa2', 7, '2025-09-26 04:43:34'),
(196, 'Artículo', NULL, '68d61a85b7801', 17, '2025-09-26 04:45:59'),
(197, 'Artículo', NULL, '68d61a8790b28', 17, '2025-09-26 04:46:01'),
(198, 'Artículo', NULL, '68d61a891ced4', 17, '2025-09-26 04:46:02'),
(199, 'Artículo', NULL, '68d61a8ad834a', 17, '2025-09-26 04:46:04'),
(200, 'Artículo', NULL, '68d61a8c26f40', 17, '2025-09-26 04:46:05'),
(201, 'Artículo', NULL, '68d61b7be9dd2', 6, '2025-09-26 04:50:05'),
(202, 'Artículo', NULL, '68d61b7dadb86', 6, '2025-09-26 04:50:07'),
(203, 'Artículo', NULL, '68d61b7f780e5', 6, '2025-09-26 04:50:09'),
(204, 'Artículo', NULL, '68d61b815c056', 6, '2025-09-26 04:50:11'),
(205, 'Artículo', NULL, '68d61c249c389', 35, '2025-09-26 04:52:54'),
(208, 'Artículo', NULL, '68d61cde4a5ef', 24, '2025-09-26 04:56:00'),
(209, 'Artículo', NULL, '68d61ce01c613', 24, '2025-09-26 04:56:01'),
(211, 'Artículo', NULL, '68d61ce354873', 24, '2025-09-26 04:56:04'),
(212, 'Artículo', NULL, '68d61ce487790', 24, '2025-09-26 04:56:06'),
(213, 'Articulo', NULL, '68d61d28dfa29', 30, '2025-09-26 04:57:14'),
(214, 'Artículo', NULL, '68d61d8d21040', 4, '2025-09-26 04:58:54'),
(215, 'Artículo', NULL, '68d61d8e6ff91', 4, '2025-09-26 04:58:56'),
(216, 'Artículo', NULL, '68d61d9017514', 4, '2025-09-26 04:58:57'),
(217, 'Artículo', NULL, '68d61d91837ff', 4, '2025-09-26 04:58:59'),
(218, 'Artículo', NULL, '68d61d93744f2', 4, '2025-09-26 04:59:00'),
(219, 'Artículo', NULL, '68d61d94be9b4', 4, '2025-09-26 04:59:01'),
(220, 'Artículo', NULL, '68d61df048464', 11, '2025-09-26 05:00:33'),
(221, 'Artículo', NULL, '68d61df1c2082', 11, '2025-09-26 05:00:35'),
(222, 'Articulo', NULL, '68d61e3784667', 32, '2025-09-26 05:01:45'),
(223, 'Artículo', NULL, '68d61e81a52fb', 18, '2025-09-26 05:02:59'),
(224, 'Artículo', NULL, '68d61e8308ee4', 18, '2025-09-26 05:03:00'),
(225, 'Artículo', NULL, '68d61ebc59d86', 32, '2025-09-26 05:03:58'),
(226, 'Artículo', NULL, '68d61ebe455c1', 32, '2025-09-26 05:04:00'),
(227, 'Artículo', NULL, '68d61ec0318f7', 32, '2025-09-26 05:04:02'),
(229, 'artículo', NULL, '68d61f783bd22', 7, '2025-09-26 05:07:06'),
(230, 'artículo', NULL, '68d61f7a1362e', 7, '2025-09-26 05:07:08'),
(231, 'artículo', NULL, '68d61f7c229fa', 7, '2025-09-26 05:07:10'),
(232, 'artículo', NULL, '68d61f7e11302', 7, '2025-09-26 05:07:11'),
(233, 'Artículo', NULL, '68d61feb24d70', 19, '2025-09-26 05:09:00'),
(234, 'Artículo', NULL, '68d61fecbb106', 19, '2025-09-26 05:09:02'),
(235, 'Artículo', NULL, '68d61fee1a4cd', 19, '2025-09-26 05:09:03'),
(236, 'Artículo', NULL, '68d6204830049', 32, '2025-09-26 05:10:33'),
(237, 'Artículo', NULL, '68d620498b651', 32, '2025-09-26 05:10:35'),
(238, 'Artículo', NULL, '68d620d8551cf', 5, '2025-09-26 05:12:57'),
(239, 'Artículo', NULL, '68d620d9bc22b', 5, '2025-09-26 05:12:59'),
(240, 'Artículo', NULL, '68d620db8e0f0', 5, '2025-09-26 05:13:01'),
(241, 'Artículo', NULL, '68d6210b1a0bc', 34, '2025-09-26 05:13:48'),
(242, 'Artículo', NULL, '68d6210c5a7b5', 34, '2025-09-26 05:13:49'),
(243, 'Articulo', NULL, '68d621806b3c1', 33, '2025-09-26 05:15:45'),
(244, 'Articulo', NULL, '68d62181b7212', 33, '2025-09-26 05:15:47'),
(245, 'Articulo', NULL, '68d621d878f71', 16, '2025-09-26 05:17:13'),
(247, 'Articulo', NULL, '68d621db1f33d', 16, '2025-09-26 05:17:16'),
(248, 'Articulo', NULL, '68d621dc75a42', 16, '2025-09-26 05:17:18'),
(249, 'Artículo', NULL, '68d6237e7a4f5', 18, '2025-09-26 05:24:15'),
(250, 'Artículo', NULL, '68d6237fc14e6', 18, '2025-09-26 05:24:16'),
(251, 'Artículo', NULL, '68d62380edb22', 18, '2025-09-26 05:24:18'),
(252, 'Artículo', NULL, '68d62382666bb', 18, '2025-09-26 05:24:20'),
(253, 'Artículo', NULL, '68d623843313f', 18, '2025-09-26 05:24:21'),
(254, 'Artículo', NULL, '68d623858034d', 18, '2025-09-26 05:24:22'),
(255, 'Artículo', NULL, '68d6238679b29', 18, '2025-09-26 05:24:23'),
(256, 'Artículo', NULL, '68d62387a848f', 18, '2025-09-26 05:24:24'),
(257, 'Artículo', NULL, '68d62388f28d8', 18, '2025-09-26 05:24:26'),
(258, 'Artículo', NULL, '68d6238a58110', 18, '2025-09-26 05:24:27'),
(259, 'Articulo', NULL, '68d624e8b772b', 11, '2025-09-26 05:30:18'),
(260, 'Articulo', NULL, '68d624ea77b99', 11, '2025-09-26 05:30:20'),
(261, 'Articulo', NULL, '68d624ec4cec1', 11, '2025-09-26 05:30:22'),
(262, 'Articulo', NULL, '68d624ee13f48', 11, '2025-09-26 05:30:23'),
(263, 'Articulo', NULL, '68d624efe208b', 11, '2025-09-26 05:30:25'),
(264, 'Articulo', NULL, '68d624f1a3ceb', 11, '2025-09-26 05:30:26'),
(265, 'Articulo', NULL, '68d624f2ee755', 11, '2025-09-26 05:30:28'),
(266, 'Artículo', NULL, '68d6259f8630f', 17, '2025-09-26 05:33:21'),
(268, 'Artículo', NULL, '68d625a283db6', 17, '2025-09-26 05:33:23'),
(269, 'Artículo', NULL, '68d625a3efa33', 17, '2025-09-26 05:33:25'),
(271, 'Artículo', NULL, '68d693677c36c', 16, '2025-09-26 13:21:44'),
(272, 'Artículo', NULL, '68d69368e8da5', 16, '2025-09-26 13:21:46'),
(273, 'Artículo', NULL, '68d6936a3014d', 16, '2025-09-26 13:21:48'),
(274, 'Artículo', NULL, '68d693f754bb3', 21, '2025-09-26 13:24:09'),
(275, 'Artículo', NULL, '68d693f90ece7', 21, '2025-09-26 13:24:10'),
(276, 'Artículo', NULL, '68d693fab344c', 21, '2025-09-26 13:24:12'),
(277, 'Artículo', NULL, '68d69478d8745', 19, '2025-09-26 13:26:18'),
(279, 'Artículo', NULL, '68d696a45bfe8', 15, '2025-09-26 13:35:34'),
(280, 'Artículo', NULL, '68d696a612d12', 15, '2025-09-26 13:35:35'),
(281, 'Artículo', NULL, '68d696f65819c', 12, '2025-09-26 13:36:56'),
(282, 'Artículo', NULL, '68d696f80b50f', 12, '2025-09-26 13:36:57'),
(283, 'Artículo', NULL, '68d697bb33228', 2, '2025-09-26 13:40:12'),
(284, 'Artículo', NULL, '68d697bce4fd7', 2, '2025-09-26 13:40:14'),
(285, 'Artículo', NULL, '68d698817e45d', 7, '2025-09-26 13:43:31'),
(286, 'Artículo', NULL, '68d698832f21b', 7, '2025-09-26 13:43:32'),
(287, 'Artículo', NULL, '68d698b1284c2', 27, '2025-09-26 13:44:18'),
(288, 'Artículo', NULL, '68d698b2e3868', 27, '2025-09-26 13:44:20'),
(289, 'Artículo', NULL, '68d699038a119', 17, '2025-09-26 13:45:41'),
(290, 'Artículo', NULL, '68d699054ace0', 17, '2025-09-26 13:45:42'),
(291, 'Artículo', NULL, '68d6990688e95', 17, '2025-09-26 13:45:43'),
(292, 'Artículo', NULL, '68d6990792dd7', 17, '2025-09-26 13:45:45'),
(293, 'Artículo', NULL, '68d699092d40f', 17, '2025-09-26 13:45:46'),
(294, 'Artículo', NULL, '68d6996c60047', 11, '2025-09-26 13:47:26'),
(295, 'Artículo', NULL, '68d6996e25cab', 11, '2025-09-26 13:47:27'),
(296, 'Artículo', NULL, '68d6996f4b1c9', 11, '2025-09-26 13:47:28'),
(297, 'Artículo', NULL, '68d699703f701', 11, '2025-09-26 13:47:29'),
(298, 'Artículo', NULL, '68d69971d76b6', 11, '2025-09-26 13:47:31'),
(299, 'Artículo', NULL, '68d6997308cba', 11, '2025-09-26 13:47:32'),
(300, 'Artículo', NULL, '68d699748930b', 11, '2025-09-26 13:47:33'),
(301, 'Artículo', NULL, '68d69975dcfba', 11, '2025-09-26 13:47:35'),
(302, 'Artículo', NULL, '68d69b04392ee', 5, '2025-09-26 13:54:13'),
(304, 'Artículo', NULL, '68d69bd93f050', 24, '2025-09-26 13:57:46'),
(305, 'Artículo', NULL, '68d69bda6773b', 24, '2025-09-26 13:57:47'),
(306, 'Artículo', NULL, '68d69bdb65815', 24, '2025-09-26 13:57:48'),
(307, 'Artículo', NULL, '68d69bdcc8ae0', 24, '2025-09-26 13:57:50'),
(308, 'Artículo', NULL, '68d69bde8399e', 24, '2025-09-26 13:57:52'),
(309, 'Artículo', NULL, '68d69be03b215', 24, '2025-09-26 13:57:53'),
(310, 'Articulo', NULL, '68d6a0e5653e5', 43, '2025-09-26 14:19:18'),
(311, 'Articulo', NULL, '68d6a0e706733', 43, '2025-09-26 14:19:20'),
(312, 'Articulo', NULL, '68d6a0e80cbdc', 43, '2025-09-26 14:19:21'),
(313, 'Articulo', NULL, '68d6a0e951eec', 43, '2025-09-26 14:19:23'),
(314, 'Articulo', NULL, '68d6a0eb39357', 43, '2025-09-26 14:19:24'),
(315, 'Articulo', NULL, '68d6a279949c1', 44, '2025-09-26 14:26:03'),
(316, 'Articulo', NULL, '68d6a27b52b47', 44, '2025-09-26 14:26:05'),
(317, 'Articulo', NULL, '68d6a27d2646e', 44, '2025-09-26 14:26:06'),
(318, 'Articulo', NULL, '68d6a27ed52ac', 44, '2025-09-26 14:26:08'),
(319, 'Articulo', NULL, '68d6a2809745e', 44, '2025-09-26 14:26:10'),
(320, 'Articulo', NULL, '68d6a2824cd48', 44, '2025-09-26 14:26:12'),
(321, 'Articulo', NULL, '68d6a28412b38', 44, '2025-09-26 14:26:13'),
(322, 'Articulo', NULL, '68d6a285b3bc9', 44, '2025-09-26 14:26:15'),
(323, 'Artículo', NULL, '68d6a2d96fe63', 35, '2025-09-26 14:27:38'),
(324, 'Artículo', NULL, '68d6a2dadf88b', 35, '2025-09-26 14:27:40'),
(325, 'Artículo', NULL, '68d6a2dc51037', 35, '2025-09-26 14:27:42'),
(326, 'Artículo', NULL, '68d6a2de0425b', 35, '2025-09-26 14:27:43'),
(327, 'Artículo', NULL, '68d6a2df85e15', 35, '2025-09-26 14:27:44'),
(328, 'Artículo', NULL, '68d6a2e07e189', 35, '2025-09-26 14:27:46'),
(329, 'Articulo', NULL, '68d6a30ff3a57', 20, '2025-09-26 14:28:33'),
(330, 'Artículo', NULL, '68d6a339e303d', 41, '2025-09-26 14:29:15'),
(331, 'Artículo', NULL, '68d6a4b25ca6f', 6, '2025-09-26 14:35:31'),
(332, 'Artículo', NULL, '68d6a554a4506', 14, '2025-09-26 14:38:14'),
(333, 'Artículo', NULL, '68d6a5565e629', 14, '2025-09-26 14:38:16'),
(334, 'articulo', NULL, '68d6a601c1c22', 3, '2025-09-26 14:41:07'),
(335, 'articulo', NULL, '68d6a6037dbf3', 3, '2025-09-26 14:41:09'),
(336, 'articulo', NULL, '68d6a60553224', 3, '2025-09-26 14:41:11'),
(337, 'articulo', NULL, '68d6a60761c05', 3, '2025-09-26 14:41:12'),
(338, 'Artículo', NULL, '68d6a67942c15', 21, '2025-09-26 14:43:06'),
(339, 'Articulo', NULL, '68d6a702dba22', 6, '2025-09-26 14:45:24'),
(340, 'Articulo', NULL, '68d6a72e3adfe', 25, '2025-09-26 14:46:07'),
(341, 'Artículo', NULL, '68d6a78622b84', 1, '2025-09-26 14:47:35'),
(342, 'Artículo', NULL, '68d6a78738002', 1, '2025-09-26 14:47:36'),
(343, 'Artículo', NULL, '68d6a7885881c', 1, '2025-09-26 14:47:37'),
(344, 'Artículo', NULL, '68d6a7dd9f705', 6, '2025-09-26 14:49:02'),
(345, 'Artículo', NULL, '68d6a7de5c89b', 6, '2025-09-26 14:49:03'),
(346, 'Artículo', NULL, '68d6a7df2a898', 6, '2025-09-26 14:49:03'),
(347, 'Artículo', NULL, '68d6a8b09f294', 30, '2025-09-26 14:52:33'),
(348, 'Artículo', NULL, '68d6a8b165dfe', 30, '2025-09-26 14:52:34'),
(349, 'Artículo', NULL, '68d6a8b222362', 30, '2025-09-26 14:52:35'),
(350, 'Artículo', NULL, '68d6a94e4e1f1', 5, '2025-09-26 14:55:11'),
(351, 'Artículo', NULL, '68d6a94f27777', 5, '2025-09-26 14:55:11'),
(352, 'Artículo', NULL, '68d6a94fe5a5d', 5, '2025-09-26 14:55:13'),
(353, 'Artículo', NULL, '68d6a9515a64f', 5, '2025-09-26 14:55:14'),
(354, 'Articulo', NULL, '68d71d9bf20a3', 36, '2025-09-26 23:11:25'),
(355, 'Articulo', NULL, '68d71d9d16330', 36, '2025-09-26 23:11:26'),
(356, 'Artículo', NULL, '68d71f4adb8e0', 14, '2025-09-26 23:18:36'),
(357, 'Artículo', NULL, '68d71f4cc6079', 14, '2025-09-26 23:18:38'),
(358, 'Artículo', NULL, '68d71f4e645a4', 14, '2025-09-26 23:18:40'),
(359, 'Artículo', NULL, '68d71fa336f12', 41, '2025-09-26 23:20:03'),
(360, 'Artículo', NULL, '68d720a3b60a7', 17, '2025-09-26 23:24:20'),
(361, 'Artículo', NULL, '68d720a4b381e', 17, '2025-09-26 23:24:21'),
(362, 'Articulo', NULL, '68d72146851af', 23, '2025-09-26 23:27:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `invoice_id` varchar(50) DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `order_date` date NOT NULL,
  `delivery_date` date NOT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('Pendiente de pago','Pagado en espera','Pagado en proceso','Terminado','Entregado') DEFAULT 'Pendiente de pago',
  `delivery` enum('Si','No') DEFAULT 'No',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `client_name` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `invoices`
--

INSERT INTO `invoices` (`id`, `invoice_id`, `client_id`, `phone`, `address`, `order_date`, `delivery_date`, `total_amount`, `paid_amount`, `status`, `delivery`, `created_at`, `client_name`) VALUES
(7, 'F_7_2025', NULL, '50250136', '5 de diciembre casa número 30 calle c sí que estoy viendo ahí cuando', '2025-08-20', '2025-09-07', '2150.00', '2150.00', 'Entregado', 'No', '2025-08-31 01:35:38', 'Laritza Céspedes Toirac'),
(41, 'F_41_2025', NULL, '+53 5 8373146', 'Edif 10 apto 8', '2025-09-04', '2025-09-06', '900.00', '900.00', 'Entregado', 'No', '2025-09-04 23:16:27', 'Yanet Roman'),
(43, 'F_43_2025', NULL, '+53 5 8373146', 'Edif 10 apto 8', '2025-09-04', '2025-09-14', '600.00', '600.00', 'Entregado', 'No', '2025-09-04 23:22:59', 'Yanet Roman'),
(44, 'F_44_2025', 44, '054665815', 'Edificio 43 Apto 12 Miraflores', '2025-09-04', '2025-09-05', '0.00', '0.00', 'Entregado', 'No', '2025-09-04 23:26:40', 'Wilian Proenza Matos'),
(45, 'F_45_2025', 44, '054665815', 'Edificio 43 Apto 12 Miraflores', '2025-09-04', '2025-09-05', '225.00', '225.00', 'Entregado', 'No', '2025-09-04 23:30:17', 'Wilian Proenza Matos'),
(46, 'F_46_2025', 44, '054665815', 'Edificio 43 Apto 12 Miraflores', '2025-09-04', '2025-09-05', '200.00', '200.00', 'Entregado', 'No', '2025-09-04 23:31:51', 'Wilian Proenza Matos'),
(48, 'F_48_2025', NULL, '+53 5 6549516', 'Las Coloradas', '2025-09-04', '2025-09-07', '5000.00', '5000.00', 'Entregado', 'No', '2025-09-04 23:58:59', 'Claridey Dulcera'),
(49, 'F_49_2025', NULL, '55225152', 'Calle segunda casa 12 pueblo nuevo', '2025-09-02', '2025-09-10', '900.00', '900.00', 'Entregado', 'No', '2025-09-05 00:04:59', 'Neimi Casas'),
(53, 'F_53_2025', 44, '054665815', 'Edificio 43 Apto 12 Miraflores', '2025-09-05', '2025-09-05', '150.00', '150.00', 'Entregado', 'No', '2025-09-05 08:05:49', 'Wilian Proenza Matos'),
(54, 'F_54_2025', NULL, '53825902', 'Edif 4 Apto 10 Coloradas', '2025-09-06', '2025-09-19', '1200.00', '1200.00', 'Entregado', 'No', '2025-09-06 19:31:58', 'Yolaidis Gómez Mejía'),
(55, 'F_55_2025', NULL, '59672412', 'Calle 6ta Vista Alegre #9a', '2025-09-09', '2025-09-24', '3900.00', '3900.00', 'Entregado', 'No', '2025-09-09 22:55:06', 'Franklin García Silot'),
(56, 'F_56_2025', NULL, '58105475', 'Coloradas detrás de la policia', '2025-09-10', '2025-09-12', '550.00', '550.00', 'Entregado', 'Si', '2025-09-10 17:09:36', 'Yolema dulcera'),
(57, 'F_57_2025', NULL, '55012921', 'Referida de elizandra', '2025-09-11', '2025-09-15', '2500.00', '2500.00', 'Entregado', 'No', '2025-09-12 00:32:49', 'Leya'),
(58, 'F_58_2025', NULL, '+53 5 5683296', 'Edif B Apto 1 Atlántico', '2025-09-15', '2025-09-25', '2500.00', '2500.00', 'Entregado', 'No', '2025-09-15 17:21:59', 'Liyanet Pérez Rodríguez'),
(60, 'F_60_2025', NULL, '51426378', 'Edif 38 apto 5 caribe', '2025-09-19', '2025-09-29', '400.00', '400.00', 'Entregado', 'No', '2025-09-19 22:05:54', 'Carlos Tol'),
(62, 'F_62_2025', NULL, '+53 5 9168104', 'Edificio 23 Apto 45 Miraflores', '2025-09-20', '2025-09-20', '3000.00', '3000.00', 'Entregado', 'No', '2025-09-27 01:38:57', 'José M Cruz');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `invoice_details`
--

CREATE TABLE `invoice_details` (
  `id` int(11) NOT NULL,
  `invoice_id` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `discount` decimal(10,2) DEFAULT 0.00,
  `amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `invoice_details`
--

INSERT INTO `invoice_details` (`id`, `invoice_id`, `description`, `price`, `quantity`, `discount`, `amount`) VALUES
(71, 'F_44_2025', 'Impresión de hoja de ruta', '0.00', 1, '0.00', '0.00'),
(76, 'F_41_2025', 'Topper principal temática Barbie con 3d y foami', '600.00', 1, '0.00', '600.00'),
(77, 'F_41_2025', 'Topper A x 10', '300.00', 1, '0.00', '300.00'),
(78, 'F_45_2025', 'Impresión de certificación de notas', '75.00', 3, '0.00', '225.00'),
(82, 'F_7_2025', 'Topper Cartulina y Foami (Aron Daniel 3 Sonic)', '600.00', 1, '0.00', '600.00'),
(83, 'F_7_2025', 'Topper Accesorios 7 x 8.5 alt', '250.00', 1, '0.00', '250.00'),
(84, 'F_7_2025', 'Topper 4 estrellas de foami', '25.00', 4, '0.00', '100.00'),
(85, 'F_7_2025', 'Torre cupcake 2 pisos', '600.00', 2, '0.00', '1200.00'),
(86, 'F_46_2025', 'Carne de vacunación infantil temática niña', '200.00', 1, '0.00', '200.00'),
(87, 'F_49_2025', 'bases ponques 1 piso', '450.00', 2, '0.00', '900.00'),
(101, 'F_53_2025', 'Impresión de QR y plastificando ', '75.00', 2, '0.00', '150.00'),
(102, 'F_48_2025', 'Fotos y Video ', '5000.00', 1, '0.00', '5000.00'),
(109, 'F_56_2025', 'Topper principal simple', '100.00', 1, '0.00', '100.00'),
(110, 'F_56_2025', 'Topper accesorios  ', '350.00', 1, '0.00', '350.00'),
(111, 'F_56_2025', 'Domicilio', '100.00', 1, '0.00', '100.00'),
(112, 'F_43_2025', 'Topper castillo', '600.00', 1, '0.00', '600.00'),
(116, 'F_57_2025', 'Topper P Foami Kristall 9 Mariposas corona', '600.00', 1, '0.00', '600.00'),
(117, 'F_57_2025', 'Rosas y mariposas de foami', '1900.00', 1, '0.00', '1900.00'),
(130, 'F_54_2025', 'torre temática 15 colores malva plateado', '600.00', 2, '0.00', '1200.00'),
(132, 'F_55_2025', 'Topper dinosaurio, Mateo 3', '2700.00', 1, '0.00', '2700.00'),
(133, 'F_55_2025', 'Torre 2 pisos', '600.00', 2, '0.00', '1200.00'),
(134, 'F_58_2025', 'Topper mis 15 Aliena Dorado, Flores Rosadas Pastel y Mariposas Doradas de Foami', '2500.00', 1, '0.00', '2500.00'),
(135, 'F_60_2025', 'Topper bebe y mariposas Alaia 2', '400.00', 1, '0.00', '400.00'),
(136, 'F_62_2025', 'Diseño de Logo y Tarjeta de Presentación ', '1000.00', 1, '500.00', '500.00'),
(137, 'F_62_2025', 'Impresión Tarjetas de Presentación ', '25.00', 100, '0.00', '2500.00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `invoice_id` varchar(50) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `invoice_id`, `is_read`, `created_at`) VALUES
(2, 2, 'Se ha creado un nuevo pedido para usted. Número: F_2_2025', 'F_2_2025', 1, '2025-08-29 16:11:15'),
(3, 2, 'Su pedido F_2_2025 ha sido actualizado. Estado: Pagado en espera', 'F_2_2025', 1, '2025-08-29 16:12:31'),
(6, 2, 'Su pedido F_2_2025 ha sido actualizado. Estado: Pagado en proceso', 'F_2_2025', 1, '2025-08-30 17:46:40'),
(7, 2, 'Se ha creado un nuevo pedido para usted. Número: F_9_2025', 'F_9_2025', 1, '2025-08-31 03:23:50'),
(11, 2, 'Se ha creado un nuevo pedido para usted. Número: F_14_2025', 'F_14_2025', 1, '2025-08-31 04:22:59'),
(17, 2, 'Se ha creado un nuevo pedido para usted. Número: F_20_2025', 'F_20_2025', 1, '2025-08-31 05:27:49'),
(19, 1, 'Se ha creado un nuevo pedido para usted. Número: F_22_2025', 'F_22_2025', 1, '2025-08-31 05:56:06'),
(22, 1, 'Se ha creado un nuevo pedido para usted. Número: F_25_2025', 'F_25_2025', 1, '2025-08-31 06:02:30'),
(24, 2, 'Se ha creado un nuevo pedido para usted. Número: F_27_2025', 'F_27_2025', 1, '2025-09-04 00:52:43'),
(27, 2, 'Se ha creado un nuevo pedido para usted. Número: F_30_2025', 'F_30_2025', 1, '2025-09-04 05:57:22'),
(33, 1, 'Se ha creado un nuevo pedido para usted. Número: F_40_2025', 'F_40_2025', 1, '2025-09-04 15:51:07'),
(34, 44, 'Se ha creado un nuevo pedido para usted. Número: F_44_2025', 'F_44_2025', 0, '2025-09-04 23:26:40'),
(35, 44, 'Se ha creado un nuevo pedido para usted. Número: F_45_2025', 'F_45_2025', 0, '2025-09-04 23:30:17'),
(36, 44, 'Se ha creado un nuevo pedido para usted. Número: F_46_2025', 'F_46_2025', 0, '2025-09-04 23:31:51'),
(37, 44, 'Su pedido F_46_2025 ha sido actualizado. Estado: Pagado en espera', 'F_46_2025', 0, '2025-09-04 23:32:16'),
(38, 44, 'Su pedido F_46_2025 ha sido actualizado. Estado: Pagado en espera', 'F_46_2025', 0, '2025-09-04 23:53:00'),
(39, 44, 'Se ha creado un nuevo pedido para usted. Número: F_47_2025', 'F_47_2025', 0, '2025-09-04 23:56:44'),
(40, 44, 'Su pedido F_47_2025 ha sido actualizado. Estado: Pendiente de pago', 'F_47_2025', 0, '2025-09-04 23:57:44'),
(41, 44, 'Se ha creado un nuevo pedido para usted. Número: F_50_2025', 'F_50_2025', 0, '2025-09-05 00:58:35'),
(42, 2, 'Se ha creado un nuevo pedido para usted. Número: F_51_2025', 'F_51_2025', 1, '2025-09-05 07:37:59'),
(43, 44, 'Su pedido F_50_2025 ha sido actualizado. Estado: Pendiente de pago', 'F_50_2025', 0, '2025-09-05 07:44:53'),
(44, 44, 'Su pedido F_50_2025 ha sido actualizado. Estado: Pendiente de pago', 'F_50_2025', 0, '2025-09-05 07:45:14'),
(45, 44, 'Se ha creado un nuevo pedido para usted. Número: F_52_2025', 'F_52_2025', 0, '2025-09-05 07:45:49'),
(46, 44, 'Su pedido F_44_2025 ha sido actualizado. Estado: Entregado', 'F_44_2025', 0, '2025-09-05 08:00:01'),
(47, 44, 'Su pedido F_45_2025 ha sido actualizado. Estado: Terminado', 'F_45_2025', 0, '2025-09-05 08:00:38'),
(48, 44, 'Su pedido F_46_2025 ha sido actualizado. Estado: Terminado', 'F_46_2025', 0, '2025-09-05 08:01:16'),
(49, 44, 'Se ha creado un nuevo pedido para usted. Número: F_53_2025', 'F_53_2025', 0, '2025-09-05 08:05:49'),
(50, 44, 'Su pedido F_53_2025 ha sido actualizado. Estado: Terminado', 'F_53_2025', 0, '2025-09-05 08:06:51'),
(51, 44, 'Su pedido F_45_2025 ha sido actualizado. Estado: Entregado', 'F_45_2025', 0, '2025-09-06 14:19:46'),
(52, 44, 'Su pedido F_46_2025 ha sido actualizado. Estado: Entregado', 'F_46_2025', 0, '2025-09-10 16:46:48'),
(53, 44, 'Su pedido F_53_2025 ha sido actualizado. Estado: Terminado', 'F_53_2025', 0, '2025-09-11 23:10:23'),
(54, 44, 'Su pedido F_53_2025 ha sido actualizado. Estado: Entregado', 'F_53_2025', 0, '2025-09-11 23:11:05'),
(55, 2, 'Se ha creado un nuevo pedido para usted. Número: F_61_2025', 'F_61_2025', 0, '2025-09-25 03:55:58'),
(56, 2, 'Su pedido F_61_2025 ha sido actualizado. Estado: Pendiente de pago', 'F_61_2025', 0, '2025-09-25 03:56:49');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `system_name` varchar(100) NOT NULL DEFAULT 'Sistema de Galería',
  `logo` varchar(255) DEFAULT NULL,
  `whatsapp_number` varchar(20) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `settings`
--

INSERT INTO `settings` (`id`, `system_name`, `logo`, `whatsapp_number`, `created_at`) VALUES
(1, 'Digital-Print-Fiesta', 'logo.png', '+5354665814', '2025-08-29 13:29:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `themes`
--

CREATE TABLE `themes` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `themes`
--

INSERT INTO `themes` (`id`, `name`, `image`, `created_at`) VALUES
(1, 'Plim Plim', '68d54d27a9848.webp', '2025-08-29 13:46:56'),
(2, 'Rayo Macqueen', '68d54e08df692.webp', '2025-08-29 14:24:23'),
(3, 'Sofia', '68d54f33ae7de.webp', '2025-08-29 22:25:03'),
(4, 'Safari', '68d54e6bca612.webp', '2025-08-29 22:25:21'),
(5, 'Minni', '68d54cb4be1db.webp', '2025-08-29 22:25:56'),
(6, 'Mickey', '68d54c86e16bb.webp', '2025-08-29 22:26:03'),
(7, 'Frozen', '68d54c071e71c.webp', '2025-08-29 22:26:11'),
(8, 'Quinceañeras', '68d54dab42ffe.webp', '2025-08-29 22:26:25'),
(9, 'Soni', '68d54f93d8603.webp', '2025-08-29 22:27:40'),
(10, 'Abejas', '68d54b4b03b04.webp', '2025-08-29 22:32:29'),
(11, 'Princesas', '68d552b8db484.webp', '2025-08-29 22:33:18'),
(12, 'Moana', '68d54cda8ae5e.webp', '2025-08-29 22:33:23'),
(13, 'Campanita Tinquervel', '68d54b76d10e8.webp', '2025-08-29 22:33:50'),
(14, 'Rosita Fresita', '68d54e327194d.webp', '2025-08-29 22:33:59'),
(15, 'Barbie', '68d54ac2c359f.webp', '2025-08-29 22:34:09'),
(16, 'Paw Patrol', '68d54d013ab78.webp', '2025-08-29 22:34:26'),
(17, 'Unicornio', '68d5502d911e0.webp', '2025-08-29 22:37:56'),
(18, 'Sirenita Ariel', '68d54ead41171.webp', '2025-08-29 22:38:10'),
(19, 'Dino', '68d551f0a814e.webp', '2025-08-29 23:10:03'),
(20, 'Masha y el Oso', '68d54c5574d5d.webp', '2025-08-29 23:10:16'),
(21, 'Dora la Exploradora', '68d54bdd278aa.webp', '2025-08-29 23:10:30'),
(22, 'Mario', '68d54c302beb7.webp', '2025-09-05 09:28:56'),
(23, 'Baby Shower', '68d5cbaf6b86b.webp', '2025-09-25 23:09:35'),
(24, 'Bebe Jefazo', '68d5cd1b97983.webp', '2025-09-25 23:15:39'),
(25, 'Ben 10', '68d5cd9956cd5.webp', '2025-09-25 23:17:45'),
(26, 'Beybe Shark', '68d5cdd030fb9.webp', '2025-09-25 23:18:40'),
(27, 'Blancanieves', '68d5cdf87a3d0.webp', '2025-09-25 23:19:20'),
(28, 'Blaze', '68d5ce2a156d3.webp', '2025-09-25 23:20:10'),
(29, 'Bob Esponja', '68d5ce51702ce.webp', '2025-09-25 23:20:49'),
(30, 'Caballos', '68d5cef183e45.webp', '2025-09-25 23:23:29'),
(31, 'Doctora Juguetes', '68d5cf7861920.webp', '2025-09-25 23:25:44'),
(32, 'Football', '68d5cffb199aa.webp', '2025-09-25 23:27:55'),
(33, 'Granja de Zenon', '68d5d03edf082.webp', '2025-09-25 23:29:03'),
(34, 'Little Pony', '68d5d0712d3f8.webp', '2025-09-25 23:29:53'),
(35, 'Mariposas y Flores', '68d5d0fb71349.webp', '2025-09-25 23:32:11'),
(36, 'Minecraft', '68d5d1716639d.webp', '2025-09-25 23:34:09'),
(37, 'Ositos', '68d5d1bb58083.webp', '2025-09-25 23:35:23'),
(38, 'Pocoyo', '68d5d2353ec4f.webp', '2025-09-25 23:37:25'),
(39, 'Oceano Marino', '68d5e818068c7.webp', '2025-09-26 01:10:48'),
(40, 'Coco Melon', '68d5ec8de5e50.webp', '2025-09-26 01:29:50'),
(41, 'Bodas', '68d5ef882b216.webp', '2025-09-26 01:42:32'),
(42, 'Toy Story', '68d6179ea147b.webp', '2025-09-26 04:26:51'),
(43, 'Lazos', '68d69db2e71c2.webp', '2025-09-26 14:05:39'),
(44, 'Cenicienta', '68d69dc0ee6f5.webp', '2025-09-26 14:05:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','client') DEFAULT 'client',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `phone`, `address`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Wiliam', '54665814', 'Edificio 43 apto 12 miraflores', 'mailiwkk@gmail.com', '$2y$10$C.699RIuwkNjwDhLYiBJBuMeicvPRXnecdQnRXhS4oXkO57Izq2J.', 'admin', '2025-08-29 13:29:46'),
(2, 'Elianis Zuñiga', '58601741', 'Edificio 43 apto 12 miraflores', 'elianiszr@gmail.com', '$2y$10$tkz3NWoLgRh0Cy4qAIGKZOEdjKrVi0GUxItHmCwJ9g/lIMKgtjIo.', 'client', '2025-08-29 14:34:19'),
(44, 'Wilian Proenza Matos', '054665815', 'Edificio 43 Apto 12 Miraflores', 'mailiwkk1@gmail.com', '$2y$10$FiSqx1dyUVUwK5RiyCQuS.10vQ0GKQRjAAinj99sv8X1L.Aw05kVG', 'client', '2025-09-04 08:40:00');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_favorite` (`user_id`,`image_id`),
  ADD KEY `image_id` (`image_id`);

--
-- Indices de la tabla `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `theme_id` (`theme_id`);

--
-- Indices de la tabla `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_id` (`invoice_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indices de la tabla `invoice_details`
--
ALTER TABLE `invoice_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indices de la tabla `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `themes`
--
ALTER TABLE `themes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `images`
--
ALTER TABLE `images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=363;

--
-- AUTO_INCREMENT de la tabla `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT de la tabla `invoice_details`
--
ALTER TABLE `invoice_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=138;

--
-- AUTO_INCREMENT de la tabla `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT de la tabla `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `themes`
--
ALTER TABLE `themes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`image_id`) REFERENCES `images` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `images`
--
ALTER TABLE `images`
  ADD CONSTRAINT `images_ibfk_1` FOREIGN KEY (`theme_id`) REFERENCES `themes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `invoice_details`
--
ALTER TABLE `invoice_details`
  ADD CONSTRAINT `invoice_details_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`invoice_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

