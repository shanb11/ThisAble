-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 16, 2025 at 08:36 AM
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
-- Database: `jobportal_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `accessibility_settings`
--

CREATE TABLE `accessibility_settings` (
  `accessibility_id` int(11) NOT NULL,
  `seeker_id` int(11) NOT NULL,
  `high_contrast` tinyint(1) DEFAULT 0,
  `text_size` enum('small','medium','large') DEFAULT 'medium',
  `screen_reader_support` tinyint(1) DEFAULT 1,
  `keyboard_navigation` tinyint(1) DEFAULT 1,
  `motion_reduction` tinyint(1) DEFAULT 0,
  `assistive_tools` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`assistive_tools`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `api_tokens`
--

CREATE TABLE `api_tokens` (
  `token_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` enum('candidate','employer') NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_used` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `api_tokens`
--

INSERT INTO `api_tokens` (`token_id`, `user_id`, `user_type`, `token`, `expires_at`, `created_at`, `last_used`, `is_active`) VALUES
(81, 4, 'candidate', '494cb2a2913fdba78290df753e90ddff1c5c5b0c0a41b568ec5a93bbd88d86ea', '2025-07-22 15:00:34', '2025-06-22 21:00:34', NULL, 1),
(82, 4, 'candidate', '1e5bce90ae08575ab36030f65f878b239ec196ef81b42ad6977aceabf80b607b', '2025-07-22 15:00:43', '2025-06-22 21:00:43', '2025-06-22 21:01:02', 1),
(83, 4, 'candidate', '9c7b2ec3ade80967bd5f43d80665cd8879414f10b533e51496f3ad93174e09e8', '2025-07-22 15:25:19', '2025-06-22 21:25:19', '2025-06-22 21:28:50', 1),
(84, 4, 'candidate', 'f9916110ef42e03fdd9817dac67b0e679ce72153a7ad108098abdf20263538a1', '2025-07-22 15:32:09', '2025-06-22 21:32:09', '2025-06-22 21:32:31', 1),
(85, 4, 'candidate', 'e4b3efb0e4f81e1b4c8037e909727fb80cfd8204f4738efb6df39ac005dbf234', '2025-07-24 18:15:47', '2025-06-25 00:15:47', NULL, 1),
(86, 4, 'candidate', '4415c27f7f6a1cb75c563d3dcab1c834dedd335e62ef649438d6839d362dd10a', '2025-07-24 21:01:43', '2025-06-25 03:01:43', NULL, 1),
(87, 4, 'candidate', '92a7b5ec3bf8cbe8b7c73cd5a39d953400be0d6708e91788699cc425bef8f678', '2025-07-25 02:15:52', '2025-06-25 08:15:52', NULL, 1),
(88, 4, 'candidate', '9b1c9517040305fe63b7ca31324637aa4f94449244e328105f703e0504ef86e1', '2025-07-25 18:30:52', '2025-06-26 00:30:52', NULL, 1),
(89, 4, 'candidate', '1726c7e171b8b4f33822ab80eff04eeff243af4599c2403a8e0280da03e80f4f', '2025-07-26 01:59:37', '2025-06-26 07:59:37', '2025-06-26 07:59:46', 1),
(90, 4, 'candidate', '2e1d050952fdf70633359dece21f8531439efae655a704cfe693759a584670ec', '2025-07-27 11:15:00', '2025-06-27 17:15:00', '2025-06-27 17:15:04', 1),
(91, 4, 'candidate', 'c6c74b4f280dba0ca2c68eef4c454ee7a319148be473699bab6ff20bc0afe830', '2025-07-27 11:17:39', '2025-06-27 17:17:39', '2025-06-27 17:17:40', 1),
(92, 4, 'candidate', '74beb946c0440a399ac1c4c64f1996d60ea27eef971175e4b4cb7174e37171be', '2025-07-27 11:30:21', '2025-06-27 17:30:21', '2025-06-27 17:30:22', 1),
(93, 4, 'candidate', 'aa6d672ebc9c31183a0e865cebcec4a9811ffabb893b711ef57c7a55d822f5e8', '2025-07-28 10:18:20', '2025-06-28 16:18:20', '2025-06-28 16:18:22', 1),
(94, 4, 'candidate', '2866f0b754458e125207a6ce639644a5bfab537d9a65c92736a69cb95f5735f0', '2025-07-28 13:57:33', '2025-06-28 19:57:33', '2025-06-28 19:57:35', 1),
(95, 4, 'candidate', '3335799d8bbc96707beb23f4d1295a16ecfd0965f25e6ee89ec0ba725b22faf1', '2025-07-28 15:53:29', '2025-06-28 21:53:29', '2025-06-28 21:53:32', 1),
(96, 4, 'candidate', '34f95d555907124ee7d5a5aa6a35fb602d90d7768f042bd7db45d0b82c36828e', '2025-07-28 16:53:17', '2025-06-28 22:53:17', '2025-06-28 23:00:02', 1),
(97, 4, 'candidate', 'd6056a343a2865accfef8453999262290879798dff5c7c1ff6ea793cd13a07f4', '2025-07-28 17:01:57', '2025-06-28 23:01:57', '2025-06-28 23:02:00', 1),
(98, 4, 'candidate', 'c5fcc1950ef4898099d01a0e669527aa7c7e16085d6602a9c3ada8352b75ccc2', '2025-07-29 08:44:13', '2025-06-29 14:44:13', '2025-06-29 14:44:16', 1),
(99, 4, 'candidate', '65349792a05bcaa20805e66a0539402a10097a3535725d321af58f4fadf21eff', '2025-07-29 10:00:04', '2025-06-29 16:00:04', '2025-06-29 16:00:07', 1),
(100, 4, 'candidate', '3b9342d5198b7585a19dd0a90a2094e5b7e1d762e324d73adf2c02d8e01702a8', '2025-08-02 04:13:02', '2025-07-03 10:13:02', '2025-07-03 10:14:47', 1),
(101, 4, 'candidate', 'b8c0a0e9c8259642f82d21adcf7bad4e0dda79df322fcb6c7bf3c87035d5479a', '2025-08-04 21:56:55', '2025-07-06 03:56:55', '2025-07-06 03:56:57', 1),
(102, 4, 'candidate', '3fa7c86ac5475ce71df4b0045d2990a06e04953d34df70ad4826a40a6e1f413f', '2025-08-04 22:07:14', '2025-07-06 04:07:14', NULL, 1),
(103, 4, 'candidate', '03f275e9f0b35c7e41089a94ec0861e17ec8af06223a8b1da6eb301a51e03695', '2025-08-04 22:12:50', '2025-07-06 04:12:50', '2025-07-06 04:12:53', 1),
(104, 4, 'candidate', '9ff8c88ec305a26acfb26babf37c70a381f06ec136950fd17e5569866c75c8ca', '2025-08-04 22:21:30', '2025-07-06 04:21:30', '2025-07-06 04:31:26', 1),
(105, 4, 'candidate', '0798c056905efa786df05239b616826d46706bd40b650eee2aa9196138c63008', '2025-08-04 23:49:54', '2025-07-06 05:49:54', '2025-07-06 05:50:12', 1),
(106, 4, 'candidate', '4d91c0eea5766ccafefcae6048238f2ca7eb7032765d17d7c10ddad1ab48a57d', '2025-08-05 04:07:30', '2025-07-06 10:07:30', '2025-07-06 10:07:33', 1),
(107, 4, 'candidate', 'b773dc2f69acd05c00867a9f32925282d7348fbcfc2168c24fec66f67331b06c', '2025-08-05 04:12:23', '2025-07-06 10:12:23', '2025-07-06 10:45:51', 1),
(108, 4, 'candidate', 'a4e321d28d8874d6a1531c2be16ddb4e8e31683594f0ddb030fe3b10200e4e16', '2025-08-05 14:14:33', '2025-07-06 20:14:33', '2025-07-06 20:14:36', 1),
(109, 4, 'candidate', '562234a13205fffb4772f6975cbe9673ef9d87ec506d723db12a719f32a5ea14', '2025-08-05 16:27:34', '2025-07-06 22:27:34', '2025-07-06 22:41:57', 1),
(110, 4, 'candidate', '888d4bd4fc4e268b1abc12b41e0a5c03a45f952bd6180915c418d1fce9bfeaa0', '2025-08-05 16:42:32', '2025-07-06 22:42:32', '2025-07-06 22:45:04', 1),
(111, 4, 'candidate', '3d61219f8a1b903a8fde95d86c939b8b357f60dab157df25c2dc61234d489326', '2025-08-05 17:02:46', '2025-07-06 23:02:46', '2025-07-06 23:02:49', 1),
(112, 4, 'candidate', '2ee689ee54cb2bfe43fafecb010737ea6da2c6fd768a04f4feed429108e4cd12', '2025-08-05 17:09:24', '2025-07-06 23:09:24', '2025-07-06 23:09:26', 1),
(113, 4, 'candidate', 'dd7024fa706495c97b91664a7669cccb05f06086c98faf625b9bd56ac479244d', '2025-08-05 17:25:12', '2025-07-06 23:25:12', '2025-07-06 23:25:15', 1),
(114, 4, 'candidate', 'ee710d90a9d82b4888ffadadfa5bdaaf6a00a7a74ac36b627ffb1c0001a8a4cd', '2025-08-05 17:27:55', '2025-07-06 23:27:55', '2025-07-06 23:27:57', 1),
(115, 4, 'candidate', 'e77789ffc10ac8561f8736046397f14f5dff645af04b1572dfdb01aba46f1bac', '2025-08-05 18:09:25', '2025-07-07 00:09:25', '2025-07-07 00:09:28', 1),
(116, 4, 'candidate', '981c81f1e6ea9a6f225ae5df2bc7b4ae8038626f5f4b19f40e91826f1f09f65b', '2025-08-05 18:16:38', '2025-07-07 00:16:38', '2025-07-07 00:16:41', 1),
(117, 4, 'candidate', 'c08eadfb9a80be56419c07203c800a0e0a26ed685c40bb2ac79c70d470166b90', '2025-08-05 18:24:53', '2025-07-07 00:24:53', '2025-07-07 00:24:56', 1),
(118, 4, 'candidate', '8f4185c65c21ae022cd7c2eb392fe0bb31984429496cee88557c944cc55b1fb3', '2025-08-05 18:26:37', '2025-07-07 00:26:37', '2025-07-07 00:26:52', 1),
(119, 4, 'candidate', 'cedd2abc5e02b691c45b649b9b4d7337ae0c5006e94e80c6326c1bd7ca7b8f65', '2025-08-06 13:56:26', '2025-07-07 19:56:26', '2025-07-07 19:56:29', 1),
(120, 4, 'candidate', '57d88e469d39f3cf6383ae9b1fe0339f19663470a2ec8b3d456b92bee119b285', '2025-08-06 21:03:38', '2025-07-08 03:03:38', '2025-07-08 03:03:41', 1),
(121, 4, 'candidate', '5b5466469d6b03ce0cbe715c6c6905f0f99c91ee24d16dcadcb20c2a53dcca61', '2025-08-06 21:42:38', '2025-07-08 03:42:38', '2025-07-08 03:42:41', 1),
(122, 4, 'candidate', '8c09f1d7ef7c6469b714e5db227be6afca7ed716f4df6e6ac08b79246d480d22', '2025-08-06 22:11:23', '2025-07-08 04:11:23', '2025-07-08 04:11:26', 1),
(123, 4, 'candidate', 'c880a54bc984440eff46d4f5861f7bc7442389a2ead2bcc6b6a8b4e4a99b4f2d', '2025-08-06 22:12:18', '2025-07-08 04:12:18', '2025-07-08 04:12:21', 1),
(124, 4, 'candidate', 'c286b4606694e2d3038fd8b380e4d5e839505224796b67dc2a9a49b6293bdfdf', '2025-08-07 01:20:34', '2025-07-08 07:20:34', '2025-07-08 07:20:37', 1),
(125, 4, 'candidate', '2b1325ea01175c956bcb03691d5329af0cbc3000298dc7c93f45ec3a82b20edb', '2025-08-07 01:22:46', '2025-07-08 07:22:46', '2025-07-08 07:22:49', 1),
(126, 4, 'candidate', '6bea2db929629b4344f84f88cc2c68501b26f756d31f8457d20c21886df58aa9', '2025-08-07 01:25:19', '2025-07-08 07:25:19', '2025-07-08 07:25:22', 1),
(127, 4, 'candidate', '1bb4ebd8552d95ab975a81166c464dd29f15384a4e73be8dd8e3e560121055fc', '2025-08-07 01:53:25', '2025-07-08 07:53:25', '2025-07-08 07:53:27', 1),
(128, 4, 'candidate', '0f1f09eae78e0dbc2d2f58f7b9351dc480940d7ebc339aae1f16095831279b1a', '2025-08-07 01:54:54', '2025-07-08 07:54:54', '2025-07-08 07:54:56', 1),
(129, 4, 'candidate', 'a09f5e30f28003a385cd40f0b15ad0cba951348ada4211182556cdf165ced5ad', '2025-08-07 01:56:09', '2025-07-08 07:56:09', '2025-07-08 07:56:22', 1),
(130, 4, 'candidate', '910e598c3aad6145a72cf9675948f30992474da3ab03c9780bf4d51c97764219', '2025-08-07 01:59:19', '2025-07-08 07:59:19', '2025-07-08 08:00:41', 1),
(131, 4, 'candidate', '35a4ea9fd51896fb37d5158a3f3e7d447b27f34fa98054f0d5a320fd02035af2', '2025-08-07 03:33:29', '2025-07-08 09:33:29', '2025-07-08 09:40:42', 1),
(132, 4, 'candidate', '90ea1d212d06ffbc7e3b25e8174b95703098db9dd9a65da7000b6bf1f910cabf', '2025-08-09 18:01:41', '2025-07-11 00:01:41', '2025-07-11 00:01:45', 1),
(133, 4, 'candidate', '5b58b8a23aad97057cedbbf276c26df4e37af5f9661ede708df189b443e7c80e', '2025-08-09 18:02:15', '2025-07-11 00:02:15', '2025-07-11 01:41:40', 1),
(134, 4, 'candidate', '0a2cf61803c5f04b4155058802c46f691a410e463ecc9274b792f12ce0776b26', '2025-10-02 09:10:10', '2025-09-02 15:10:10', NULL, 1),
(135, 4, 'candidate', '7ffafdec7539a482e6ad88bf486b75bf2a2328b47575bc8e9a012dfefecb6d16', '2025-10-02 09:10:38', '2025-09-02 15:10:38', NULL, 1),
(136, 4, 'candidate', '52a63f9a0460e0719809cf82a0a5f8fa7f1bee4593da52d2c9f60af33987cde1', '2025-10-02 10:38:32', '2025-09-02 16:38:32', NULL, 1),
(137, 4, 'candidate', '7f3653f49f29279061b98f39e0b5b62063237902a0793c1c28595bc0e4243218', '2025-10-02 10:38:34', '2025-09-02 16:38:34', NULL, 1),
(138, 4, 'candidate', '12fea93f7b13db6bbef41406dc274f78338543b1f44304bea1d791d9d918fe98', '2025-10-02 10:39:00', '2025-09-02 16:39:00', NULL, 1),
(139, 4, 'candidate', 'a31fa702f65872d4e33a4112b4e15013fa9dd2cde07baa815a8041c8ee2c7e0f', '2025-10-03 01:00:35', '2025-09-03 07:00:35', NULL, 1),
(140, 4, 'candidate', '3276451b7014fddfbf523debca52c9c54b045e4e24dd21c09b513e8d17d5a0a8', '2025-10-03 04:04:25', '2025-09-03 10:04:25', NULL, 1),
(141, 4, 'candidate', '5d975cc915119a6ab4e0c2b1590a124681ecf67b0672e225804a97a7a1f9b279', '2025-10-03 04:09:26', '2025-09-03 10:09:26', NULL, 1),
(142, 4, 'candidate', '08b70b83a936b372a476bf736b7217882d81642bb537f3c9fb47582b759a9dcb', '2025-10-03 04:17:06', '2025-09-03 10:17:06', '2025-09-03 16:25:05', 1),
(143, 4, 'candidate', 'a58697715357247fbb7c6f468a6286a92e09d82e0eedb6367e343ea053ceda90', '2025-10-03 10:29:58', '2025-09-03 16:29:58', '2025-09-03 16:30:04', 1),
(144, 4, 'candidate', 'cb35eb7f223e189fc7f5746093fc158080a10fdd12267bb4021d0570adabfd51', '2025-10-04 21:42:42', '2025-09-05 03:42:42', '2025-09-05 03:55:03', 1),
(145, 4, 'candidate', 'f40c9568e80a1feaf271ecb17e37eaf8511a5a8d27165f2b5aa0e3c59cedc520', '2025-10-06 02:29:36', '2025-09-06 08:29:36', '2025-09-06 15:47:10', 1),
(146, 4, 'candidate', '77a9f4d8a4f37c00f06624f5ce6395458929aff4d2c320169c6fa78bd59bae7c', '2025-10-06 09:52:18', '2025-09-06 15:52:18', '2025-09-06 17:18:26', 1),
(147, 4, 'candidate', '70194d69e8d58934a39918978ada62b3f9ce2f7874cf33bfd678b5773db1c155', '2025-10-06 23:41:29', '2025-09-07 05:41:29', '2025-09-07 06:56:41', 1),
(148, 4, 'candidate', '6878f22fa1227bafc545b177491340a59fcc8975e732bae41eaa3084ebcf7b2a', '2025-10-07 01:01:55', '2025-09-07 07:01:55', '2025-09-07 11:01:30', 1),
(149, 4, 'candidate', '42e3a6056f0f1edb83c1181d6926ca129422463403672d23f34c6a9266dd3a1f', '2025-10-07 10:15:45', '2025-09-07 16:15:45', '2025-09-07 16:48:34', 1),
(150, 4, 'candidate', 'bf00547b44894a5d3eaa64865ea8423ade717df728d2119f88f26950daa349f5', '2025-10-07 10:58:18', '2025-09-07 16:58:18', '2025-09-07 17:15:27', 1),
(151, 4, 'candidate', 'd7c186af8dbbe2eb1fde31f41383c879891bca82bca0c9592f8b6d8b7a089eda', '2025-10-07 11:19:38', '2025-09-07 17:19:38', '2025-09-07 17:25:27', 1),
(152, 4, 'candidate', 'e0c8aaa68359998047a9ad3b413473cb7abef4ceafba06cc243322658e68c880', '2025-10-07 11:28:54', '2025-09-07 17:28:54', '2025-09-07 19:31:12', 1),
(153, 4, 'candidate', '935b337d7b6c79da7b436ad5a1f34fa369efdde456337129b34ea98bda44b56e', '2025-10-07 13:36:33', '2025-09-07 19:36:33', '2025-09-07 19:37:57', 1),
(154, 4, 'candidate', 'e2733b82ff2c642ad4c6ed6239e9c894f64378e767314e35748ab3f5565c0f4f', '2025-10-07 22:34:08', '2025-09-08 04:34:08', '2025-09-08 04:36:12', 1),
(155, 4, 'candidate', '1a458eb7ef73d9f2a0aed9a17bff2016e520dc1145a64bdcc129c7ff8b8fc3bd', '2025-10-09 01:03:25', '2025-09-09 07:03:25', '2025-09-09 07:32:48', 1),
(156, 4, 'candidate', '0ccc01f2adbb96bca4fd79bebc763587520abd274e7c889d2af516573f31f03c', '2025-10-09 01:36:23', '2025-09-09 07:36:23', '2025-09-09 07:37:01', 1),
(157, 4, 'candidate', 'd5fcfbae36a56e1cf7a6ca315084ae142afd28dedc881709a64cd655679c1cba', '2025-10-11 04:43:10', '2025-09-11 10:43:10', '2025-09-11 10:43:15', 1),
(158, 4, 'candidate', 'c6da4ea9e653d4d74a1192f9995f5410de2bf5bbfc5f53a1f72b1fd38b3920da', '2025-10-11 07:43:31', '2025-09-11 13:43:31', '2025-09-11 14:37:28', 1),
(159, 4, 'candidate', '840fc3a422ed9326f2ac33eee931cdb3c3a5130107c1e622b8343fc2700d4d37', '2025-10-11 12:25:20', '2025-09-11 18:25:20', '2025-09-11 18:25:27', 1),
(160, 4, 'candidate', '8496db7a99660c2fe6ade19273fe8e5dc4733db938536c712a085f750cf0a18f', '2025-10-12 00:42:59', '2025-09-12 06:42:59', '2025-09-12 06:44:53', 1),
(161, 4, 'candidate', 'fc081631af6728902b50fe72b603747a801d360d2554ff7352b7e6a9d1897630', '2025-10-12 00:51:42', '2025-09-12 06:51:42', '2025-09-12 07:12:37', 1),
(162, 4, 'candidate', '7d6421656a44e69ef09c1469dd788324d0182178effc058563d4dc6b57006ee3', '2025-10-12 01:32:26', '2025-09-12 07:32:26', '2025-09-12 07:37:05', 1),
(163, 4, 'candidate', 'c669a5d8b2bdec51e8803ffa4c395b2becee6cd6c360912c691cf3bc8b7cefbd', '2025-10-12 01:50:06', '2025-09-12 07:50:06', '2025-09-12 07:50:11', 1),
(164, 4, 'candidate', 'd6b3f879f044ca76d5a897cd9489634c6e5776a020b0d5e74dafb02a5a2dab7a', '2025-10-12 03:40:56', '2025-09-12 09:40:56', '2025-09-12 09:41:02', 1),
(165, 7, 'candidate', '556a78d52fcbb420f9b15ff226ee3b521005a86a49674f1f93eabc1530f28d36', '2025-10-12 19:13:36', '2025-09-13 01:13:36', NULL, 1),
(166, 7, 'candidate', 'cfec1f019dd1d6c265d0f25b4a382f9fba153e4adad033a2da354d1e989700f6', '2025-10-12 19:13:58', '2025-09-13 01:13:58', NULL, 1),
(167, 4, 'candidate', 'bcae2e8ca5624c592145d1b759f6416a61715851c60ba82680d65849ce619d5e', '2025-10-13 02:45:23', '2025-09-13 08:45:23', '2025-09-13 08:55:24', 1),
(168, 4, 'candidate', 'e4bb846b207a1c3060f800a433fb99ffcd2a481a25b14491eae58ad41435cf9d', '2025-10-13 02:58:51', '2025-09-13 08:58:51', '2025-09-13 08:58:56', 1),
(169, 4, 'candidate', '3fd9d70c1bfb68ac9c8aee5a2de8564e3dce4c848dd4da59dc70ca4b01a23005', '2025-10-14 01:18:30', '2025-09-14 07:18:30', '2025-09-14 10:39:06', 1),
(170, 4, 'candidate', '5c714ded7c03673f8747261cdb85cd20d68aa91484c8a59b29b4517ff24fe10c', '2025-10-14 06:17:35', '2025-09-14 12:17:35', '2025-09-14 12:33:05', 1),
(171, 4, 'candidate', '60d19daa11c93a4c36c0a0ff6f27d54d3313e2c4f529386dab8fc05692b29bc3', '2025-10-14 06:43:23', '2025-09-14 12:43:23', '2025-09-14 12:48:50', 1),
(172, 4, 'candidate', 'e4416decaf2d79b6279f257bdfb916fef8a1992f662db99851506719aa855fcc', '2025-10-14 06:53:42', '2025-09-14 12:53:42', '2025-09-14 12:57:57', 1),
(173, 4, 'candidate', 'b7c59b960dc99ece7819acc1fef9b097af5ad31d445f869fe480b71d0b90a8e4', '2025-10-14 07:03:50', '2025-09-14 13:03:50', '2025-09-14 14:45:36', 1),
(174, 4, 'candidate', '3947e4340402016d22d1a62ba1f42581e13594c273da74e2889525eb5d241177', '2025-10-14 12:24:17', '2025-09-14 18:24:17', '2025-09-14 18:28:02', 1),
(175, 4, 'candidate', '751807033f00ad7ac99a02a52c66f1a20ae501fd15dd8ecda15fca823613c87c', '2025-10-14 12:42:13', '2025-09-14 18:42:13', '2025-09-14 19:00:01', 1),
(176, 4, 'candidate', '20c66a1e91b74bfbaf57c888bfd28c68eee63405823d8c942a551a00ff8d9818', '2025-10-14 22:05:12', '2025-09-15 04:05:12', '2025-09-15 04:09:14', 1),
(177, 4, 'candidate', 'b4aa42c030ef183625b08a8dd6f251aff211066beac99f83927222e661946d29', '2025-10-14 22:31:06', '2025-09-15 04:31:06', '2025-09-15 04:33:30', 1),
(178, 4, 'candidate', '5db4bcf18d5b76b6ee901fc8d2fe40db84eacb02c82b66f29d2495dab42f1eeb', '2025-10-14 22:39:42', '2025-09-15 04:39:42', '2025-09-15 04:40:30', 1),
(179, 4, 'candidate', 'd919a4e53682311268876449a1d917c17965432f389240e022f3ab9bb24adf1b', '2025-10-14 23:08:30', '2025-09-15 05:08:30', '2025-09-15 05:08:34', 1),
(180, 4, 'candidate', '77523f4d377085c5eba28076a91c3985d48fdbe5f5afec5e0b0fb8159640e15c', '2025-10-14 23:11:27', '2025-09-15 05:11:27', '2025-09-15 05:25:12', 1),
(181, 4, 'candidate', 'fa35935b5fc0c9b3f626b3d80bf1acdc2e0f57fc93e51d0dead9c020ae97e770', '2025-10-15 01:54:30', '2025-09-15 07:54:30', '2025-09-15 07:54:35', 1),
(182, 4, 'candidate', '57aea349b517f9fc37346f7d8ee5bd02e21f8ec675a9a5d5caafc5eb636768d0', '2025-10-15 02:43:56', '2025-09-15 08:43:56', '2025-09-15 08:52:01', 1),
(183, 4, 'candidate', '190dfeb83ce0c2221041f698bbdf00ef37819af5bbfff1bb5f116e5d866fff2c', '2025-10-21 13:02:15', '2025-09-21 19:02:15', '2025-09-21 19:02:19', 1),
(184, 4, 'candidate', '42ddf59ad27420f22d935ce54a86b2e350467273cd2314737bfa98360aa63fa5', '2025-10-21 22:46:24', '2025-09-22 04:46:24', '2025-09-22 08:30:10', 1),
(185, 8, 'candidate', 'bb9f9d5d284cbe6eda3d2c68717d9f15c08abb4f7c5ca2b68132cc6b341526fd', '2025-10-25 07:19:19', '2025-09-25 13:19:19', NULL, 1),
(186, 8, 'candidate', 'a3bbaa98ec20eae195dca2aca052900d1147e9a089caabd3e7168d21193e0c24', '2025-10-25 07:19:41', '2025-09-25 13:19:41', NULL, 1),
(187, 4, 'candidate', '2458b9ddf16d0e4816c8b70f88d152fbc6aa1e4a323a5bcff9e27651ca6d475b', '2025-10-26 02:47:41', '2025-09-26 09:47:41', '2025-09-26 12:00:25', 1),
(188, 4, 'candidate', 'c2e4607321b68fcc8f7b5eac3cb7315e9ce94fd113bc035b68024be4bea53991', '2025-10-28 01:01:01', '2025-09-28 08:01:01', '2025-09-28 10:53:11', 1),
(189, 4, 'candidate', '728e13a5c74346f8f8cb6f8a85b41c2abdaac057e4462f3df927ab6d01b931dd', '2025-10-29 01:29:17', '2025-09-29 08:29:17', '2025-09-29 08:31:31', 1),
(190, 4, 'candidate', '343346e15d75a41b9c81f9ab0dff21bdae7b5346953d72291fbda97f5bf8dba4', '2025-10-30 03:44:10', '2025-09-30 10:44:10', '2025-09-30 10:44:21', 1),
(191, 4, 'candidate', 'e10258fd9ef838be1b679882136642cc28836a97ab0097e78375999377c7f6ca', '2025-10-31 17:53:21', '2025-10-02 00:53:21', '2025-10-02 00:53:25', 1),
(192, 4, 'candidate', '16ab16c516836438c4f8241abe290b15dcdf9971a9255577199827fc520264c5', '2025-11-01 00:37:25', '2025-10-02 07:37:25', '2025-10-02 07:37:37', 1),
(193, 4, 'candidate', '8edb697a2d0e3e31b97b6dba5e2ef4cf93d0f712c8400f9d05e55e64795c7df7', '2025-11-01 00:54:24', '2025-10-02 07:54:24', '2025-10-02 08:03:16', 1),
(194, 4, 'candidate', '1e4030317aaf9166c9ed3199cf5181712ea369c11d0dc95b8df392aa6eafd683', '2025-11-01 02:14:43', '2025-10-02 09:14:43', '2025-10-02 09:45:03', 1),
(195, 4, 'candidate', 'ad3346b92b09a434252132f47c86304009204a2f18bf9116bb51452d3984701e', '2025-11-02 22:33:30', '2025-10-04 05:33:30', '2025-10-04 07:46:51', 1),
(196, 4, 'candidate', '9a8d96094ad81d46b2aa0c19a3c1a932ba1b72295683e8b86844ba3542216434', '2025-11-06 08:34:52', '2025-10-07 15:34:52', '2025-10-07 15:34:56', 1),
(197, 4, 'candidate', 'c8c47a807d7f2f196f6e337f6553bc6b4641e4c1dcb17011452e334c558f65a3', '2025-11-07 00:50:00', '2025-10-08 07:50:00', '2025-10-08 11:25:24', 1),
(198, 4, 'candidate', 'c779be2ca0f80cd469374ff31e6bdec5a8efe78271b99241e9c6f98a3fc246e2', '2025-11-08 23:15:39', '2025-10-10 06:15:39', '2025-10-10 06:15:45', 1),
(199, 4, 'candidate', '73403fa140a0572a1f800fd04a16f8a5ed2bd73984fcd3581eb1e8a28b2e9c3b', '2025-11-13 04:12:52', '2025-10-14 11:12:52', '2025-10-14 11:13:27', 1),
(200, 4, 'candidate', '5097e2f187fb46256afe2a50aba15ddb9273e5aaddc4a21b56955c863cf5aefd', '2025-11-13 04:41:34', '2025-10-14 11:41:34', '2025-10-14 11:41:37', 1),
(201, 4, 'candidate', 'f7750233745b1acd86bb7c902db29c32978a6b1fe9a2adc066a76045db62971f', '2025-11-13 05:14:47', '2025-10-14 12:14:47', '2025-10-14 12:17:02', 1),
(202, 4, 'candidate', 'b024bc1aa23b3ab57431cf070b6af033140861a8c624bcf3c27e64a7b3985d64', '2025-11-13 05:59:34', '2025-10-14 12:59:34', '2025-10-14 12:59:37', 1),
(203, 4, 'candidate', '747c0058784b50f8781a011e08768f2c742575e2f9dfd31336ba5fa40b563ded', '2025-11-13 06:08:12', '2025-10-14 13:08:12', '2025-10-14 13:08:23', 1),
(204, 4, 'candidate', '5e68885453b68b699d0e5bbfd1268e2d9b6de3be25cad2ee661732367a8f3677', '2025-11-14 00:02:55', '2025-10-15 07:02:55', '2025-10-15 10:12:33', 1),
(205, 4, 'candidate', '7bceb97e6b21ab09823c4f36bbc94b21bd8828253369844b44a95432ce69061f', '2025-11-14 06:10:36', '2025-10-15 13:10:36', '2025-10-15 13:10:48', 1);

-- --------------------------------------------------------

--
-- Stand-in structure for view `applicant_overview`
-- (See below for the actual view)
--
CREATE TABLE `applicant_overview` (
`application_id` int(11)
,`job_id` int(11)
,`seeker_id` int(11)
,`application_status` enum('submitted','under_review','shortlisted','interview_scheduled','interviewed','hired','rejected','withdrawn')
,`applied_at` timestamp
,`cover_letter` text
,`employer_notes` text
,`last_activity` timestamp
,`resume_id` int(11)
,`job_title` varchar(255)
,`employer_id` int(11)
,`employment_type` enum('Full-time','Part-time','Contract','Internship','Freelance')
,`job_location` varchar(255)
,`salary_range` varchar(100)
,`first_name` varchar(255)
,`last_name` varchar(255)
,`contact_number` varchar(20)
,`city` varchar(100)
,`province` varchar(100)
,`disability_id` int(11)
,`disability_name` varchar(255)
,`disability_category` varchar(255)
,`headline` varchar(255)
,`bio` text
,`profile_photo_path` varchar(255)
,`preferred_location` varchar(255)
,`resume_filename` varchar(255)
,`resume_path` varchar(255)
,`resume_type` varchar(50)
,`resume_upload_date` timestamp
,`email` varchar(255)
);

-- --------------------------------------------------------

--
-- Table structure for table `application_settings`
--

CREATE TABLE `application_settings` (
  `app_setting_id` int(11) NOT NULL,
  `seeker_id` int(11) NOT NULL,
  `auto_fill` tinyint(1) DEFAULT 1,
  `include_cover_letter` tinyint(1) DEFAULT 1,
  `follow_companies` tinyint(1) DEFAULT 1,
  `default_cover_letter` text DEFAULT NULL,
  `save_application_history` tinyint(1) DEFAULT 1,
  `receive_application_feedback` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `application_status_history`
--

CREATE TABLE `application_status_history` (
  `history_id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `previous_status` enum('submitted','under_review','shortlisted','interview_scheduled','interviewed','hired','rejected','withdrawn') DEFAULT NULL,
  `new_status` enum('submitted','under_review','shortlisted','interview_scheduled','interviewed','hired','rejected','withdrawn') NOT NULL,
  `changed_by_employer` tinyint(1) DEFAULT 1,
  `notes` text DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `application_status_history`
--

INSERT INTO `application_status_history` (`history_id`, `application_id`, `previous_status`, `new_status`, `changed_by_employer`, `notes`, `changed_at`) VALUES
(5, 3, NULL, 'submitted', 0, 'Application submitted by candidate', '2025-06-29 22:39:46'),
(6, 4, NULL, 'submitted', 0, 'Application submitted by candidate', '2025-07-01 01:46:47'),
(7, 5, NULL, 'submitted', 0, 'Application submitted by candidate', '2025-07-01 01:47:49'),
(8, 6, NULL, 'submitted', 0, 'Application submitted by candidate', '2025-07-02 01:44:46'),
(9, 7, NULL, 'submitted', 0, 'Application submitted by candidate', '2025-07-02 01:49:05'),
(10, 6, 'submitted', 'under_review', 1, '', '2025-07-02 05:47:45'),
(11, 6, 'under_review', 'interview_scheduled', 1, '', '2025-07-04 01:14:35'),
(12, 6, 'interview_scheduled', 'under_review', 1, '', '2025-07-04 01:15:04'),
(13, 6, 'under_review', 'interview_scheduled', 1, '', '2025-07-04 01:15:20'),
(14, 6, 'interview_scheduled', 'hired', 1, '', '2025-07-04 01:15:26'),
(15, 6, 'hired', 'under_review', 1, '', '2025-07-04 01:15:42'),
(16, 6, 'under_review', 'interview_scheduled', 1, 'Interview scheduled for Jul 5, 2025 10:00 AM', '2025-07-04 01:16:41'),
(17, 6, 'interview_scheduled', 'under_review', 1, '', '2025-07-04 01:17:06'),
(18, 8, NULL, 'submitted', 0, 'Application submitted by candidate', '2025-07-04 15:06:47'),
(19, 8, 'submitted', 'under_review', 1, '', '2025-07-04 15:07:24'),
(20, 9, NULL, 'submitted', 0, 'Application submitted by candidate', '2025-07-04 15:13:14'),
(21, 6, 'under_review', 'interview_scheduled', 1, '', '2025-07-06 23:31:18'),
(22, 4, 'submitted', 'rejected', 1, '', '2025-07-06 23:56:16'),
(23, 6, 'interview_scheduled', 'under_review', 1, '', '2025-07-08 02:11:55'),
(24, 6, 'under_review', 'interview_scheduled', 1, '', '2025-07-08 02:37:43'),
(25, 6, 'interview_scheduled', 'hired', 1, '', '2025-07-08 03:14:12'),
(26, 6, 'hired', 'under_review', 1, '', '2025-07-08 03:14:14'),
(27, 6, 'under_review', 'interview_scheduled', 1, '', '2025-07-08 03:24:12'),
(28, 4, 'rejected', 'interview_scheduled', 1, 'Interview scheduled for Jul 9, 2025 10:00 AM', '2025-07-08 19:37:03'),
(29, 9, 'submitted', 'interview_scheduled', 1, 'Interview scheduled for Jul 9, 2025 10:00 AM', '2025-07-08 20:19:05'),
(30, 9, 'interview_scheduled', 'under_review', 1, '', '2025-07-08 20:19:17'),
(31, 10, NULL, 'submitted', 0, 'Application submitted by candidate', '2025-07-10 03:00:34'),
(32, 6, 'interview_scheduled', 'rejected', 1, '', '2025-07-10 23:52:09'),
(33, 8, 'under_review', 'rejected', 1, '', '2025-08-04 06:56:03'),
(34, 9, 'under_review', 'withdrawn', 0, 'Application withdrawn by candidate. Reason: Test', '2025-09-07 19:37:57');

-- --------------------------------------------------------

--
-- Table structure for table `candidate_documents`
--

CREATE TABLE `candidate_documents` (
  `document_id` int(11) NOT NULL,
  `seeker_id` int(11) NOT NULL,
  `document_type` enum('diploma','certificate','license','other') NOT NULL,
  `document_name` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_verified` tinyint(1) DEFAULT 0,
  `verification_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `candidate_documents`
--

INSERT INTO `candidate_documents` (`document_id`, `seeker_id`, `document_type`, `document_name`, `original_filename`, `file_path`, `file_size`, `mime_type`, `upload_date`, `is_verified`, `verification_notes`) VALUES
(3, 4, 'diploma', 'Diploma', 'CertificateOfRegistration-2022-172670-AY_2024_-_2025_3rd_Term.pdf', 'uploads/documents/diplomas/4_diploma_686f35ab577d8.pdf', 158264, 'application/pdf', '2025-07-10 03:38:19', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `company_values`
--

CREATE TABLE `company_values` (
  `value_id` int(11) NOT NULL,
  `employer_id` int(11) NOT NULL,
  `value_title` varchar(100) NOT NULL,
  `value_description` text NOT NULL,
  `display_order` int(11) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `connection_test`
--

CREATE TABLE `connection_test` (
  `id` int(11) NOT NULL,
  `test_value` varchar(50) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `connection_test`
--

INSERT INTO `connection_test` (`id`, `test_value`, `timestamp`) VALUES
(1, 'Test at 2025-05-20 20:56:01', '2025-05-20 10:56:01');

-- --------------------------------------------------------

--
-- Table structure for table `disability_categories`
--

CREATE TABLE `disability_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `disability_categories`
--

INSERT INTO `disability_categories` (`category_id`, `category_name`) VALUES
(1, 'Apparent'),
(2, 'Non-Apparent');

-- --------------------------------------------------------

--
-- Table structure for table `disability_types`
--

CREATE TABLE `disability_types` (
  `disability_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `disability_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `disability_types`
--

INSERT INTO `disability_types` (`disability_id`, `category_id`, `disability_name`) VALUES
(1, 1, 'Visual Impairment'),
(2, 1, 'Physical Impairment'),
(3, 2, 'Deaf/Hard of Hearing Disability'),
(4, 2, 'Intellectual Disability'),
(5, 2, 'Learning Disability'),
(6, 2, 'Mental Disability'),
(7, 2, 'Psychosocial Disability'),
(8, 2, 'Non-apparent Visual Disability'),
(9, 2, 'Non-apparent Speech and Language Impairment'),
(10, 2, 'Non-apparent cancer'),
(11, 2, 'Non-apparent rare disease');

-- --------------------------------------------------------

--
-- Table structure for table `document_categories`
--

CREATE TABLE `document_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `category_type` enum('degree_field','certification_type','license_type') NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_categories`
--

INSERT INTO `document_categories` (`category_id`, `category_name`, `category_type`, `display_order`, `is_active`) VALUES
(1, 'Computer Science', 'degree_field', 1, 1),
(2, 'Information Technology', 'degree_field', 2, 1),
(3, 'Engineering', 'degree_field', 3, 1),
(4, 'Business Administration', 'degree_field', 4, 1),
(5, 'Accounting', 'degree_field', 5, 1),
(6, 'Marketing', 'degree_field', 6, 1),
(7, 'Education', 'degree_field', 7, 1),
(8, 'Nursing', 'degree_field', 8, 1),
(9, 'Psychology', 'degree_field', 9, 1),
(10, 'Other', 'degree_field', 99, 1),
(11, 'AWS Certification', 'certification_type', 1, 1),
(12, 'Microsoft Certification', 'certification_type', 2, 1),
(13, 'Google Certification', 'certification_type', 3, 1),
(14, 'Cisco Certification', 'certification_type', 4, 1),
(15, 'PMP Certification', 'certification_type', 5, 1),
(16, 'Six Sigma', 'certification_type', 6, 1),
(17, 'CompTIA', 'certification_type', 7, 1),
(18, 'Adobe Certification', 'certification_type', 8, 1),
(19, 'Salesforce Certification', 'certification_type', 9, 1),
(20, 'Other', 'certification_type', 99, 1),
(21, 'Professional Engineer License', 'license_type', 1, 1),
(22, 'CPA License', 'license_type', 2, 1),
(23, 'Teaching License', 'license_type', 3, 1),
(24, 'Nursing License', 'license_type', 4, 1),
(25, 'Real Estate License', 'license_type', 5, 1),
(26, 'Driver\'s License', 'license_type', 6, 1),
(27, 'Security License', 'license_type', 7, 1),
(28, 'Trade License', 'license_type', 8, 1),
(29, 'Other', 'license_type', 99, 1);

-- --------------------------------------------------------

--
-- Table structure for table `education`
--

CREATE TABLE `education` (
  `education_id` int(11) NOT NULL,
  `seeker_id` int(11) NOT NULL,
  `degree` varchar(255) NOT NULL,
  `institution` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `is_current` tinyint(1) DEFAULT 0,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `education`
--

INSERT INTO `education` (`education_id`, `seeker_id`, `degree`, `institution`, `location`, `start_date`, `end_date`, `is_current`, `description`, `created_at`) VALUES
(1, 4, 'BSIT', 'NU Dasma', 'Dasma Ngani', '2022-08-01', NULL, 1, 'Sana madefend na', '2025-07-08 07:49:45');

-- --------------------------------------------------------

--
-- Table structure for table `employers`
--

CREATE TABLE `employers` (
  `employer_id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `industry` varchar(100) NOT NULL,
  `company_address` text NOT NULL,
  `company_size` enum('1-10','11-50','51-200','201-500','501-1000','1000+') DEFAULT NULL,
  `company_website` varchar(255) DEFAULT NULL,
  `company_description` text DEFAULT NULL,
  `mission_vision` text DEFAULT NULL,
  `why_join_us` text DEFAULT NULL,
  `company_logo_path` varchar(255) DEFAULT NULL,
  `verification_status` enum('pending','verified','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `industry_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employers`
--

INSERT INTO `employers` (`employer_id`, `company_name`, `industry`, `company_address`, `company_size`, `company_website`, `company_description`, `mission_vision`, `why_join_us`, `company_logo_path`, `verification_status`, `created_at`, `updated_at`, `industry_id`) VALUES
(1, 'Test Company', 'Business Process Outsourcing (BPO)', 'GenTri, Cavite', '1-10', '', 'TechForward is a leading software development company established in 2010. With over 200 employees globally, we create innovative solutions for healthcare and finance industries.', 'Our mission is to make technology accessible to everyone. We envision a world where digital solutions enhance daily life for people of all abilities.', 'We offer competitive benefits, flexible work arrangements, and continuous learning opportunities. Our inclusive culture encourages innovation and personal growth in a supportive environment.', NULL, 'verified', '2025-06-09 20:46:51', '2025-06-09 20:48:00', 2),
(3, 'ABC Co.', 'Business Process Outsourcing (BPO)', 'Gen. Trias, Cavite', '', '', '', NULL, NULL, NULL, 'verified', '2025-06-10 04:53:16', '2025-06-10 04:53:16', 2),
(6, 'Test Company', 'Business Process Outsourcing (BPO)', 'GenTri, Cavite', '', '', 'Test', 'Test', 'Test', 'uploads/company_logos/6_logo_684b6c9845135.png', 'verified', '2025-06-13 00:02:22', '2025-06-13 00:13:07', 2),
(7, 'ThisAble', 'Business Process Outsourcing (BPO)', 'Cavite', '', '', 'Test Company', 'Test Mission and Vision', 'Test Join Us', 'uploads/company_logos/7_logo_686d21447ed84.png', 'verified', '2025-07-08 13:46:19', '2025-07-08 13:47:13', 2);

-- --------------------------------------------------------

--
-- Table structure for table `employer_accounts`
--

CREATE TABLE `employer_accounts` (
  `account_id` int(11) NOT NULL,
  `employer_id` int(11) NOT NULL,
  `contact_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `email_verification_token` varchar(100) DEFAULT NULL,
  `email_verification_expires` datetime DEFAULT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `google_account` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employer_accounts`
--

INSERT INTO `employer_accounts` (`account_id`, `employer_id`, `contact_id`, `email`, `password_hash`, `email_verified`, `email_verification_token`, `email_verification_expires`, `reset_token`, `reset_token_expires`, `last_login`, `login_attempts`, `locked_until`, `created_at`, `updated_at`, `google_account`) VALUES
(2, 6, 6, 'smbacs03@gmail.com', '$2y$10$jCCcek4ftm8mQ36wEB9Dl.22OiIgjnLBxRAASoTPS.3Ug44xJNeQm', 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-06-13 00:02:22', '2025-06-13 00:02:22', 1),
(3, 7, 7, 'thisableee@gmail.com', '$2y$10$IIJyWm4lM3xsBNvOqbluUuxZkO5vzMHW1IpKzmEs8mjqlSKRaDtZO', 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-07-08 13:46:19', '2025-07-08 13:46:19', 1);

-- --------------------------------------------------------

--
-- Stand-in structure for view `employer_applicant_stats`
-- (See below for the actual view)
--
CREATE TABLE `employer_applicant_stats` (
`employer_id` int(11)
,`total_applications` bigint(21)
,`new_applications` decimal(22,0)
,`under_review` decimal(22,0)
,`shortlisted` decimal(22,0)
,`interviews_scheduled` decimal(22,0)
,`interviewed` decimal(22,0)
,`hired` decimal(22,0)
,`rejected` decimal(22,0)
,`jobs_with_applications` bigint(21)
,`latest_application` timestamp
);

-- --------------------------------------------------------

--
-- Table structure for table `employer_contacts`
--

CREATE TABLE `employer_contacts` (
  `contact_id` int(11) NOT NULL,
  `employer_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `position` varchar(100) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employer_contacts`
--

INSERT INTO `employer_contacts` (`contact_id`, `employer_id`, `first_name`, `last_name`, `position`, `contact_number`, `email`, `is_primary`, `created_at`, `updated_at`) VALUES
(6, 6, 'Shan', 'Pangatlo', 'HR Manager', '09664873735', 'smbacs03@gmail.com', 1, '2025-06-13 00:02:22', '2025-06-13 00:02:22'),
(7, 7, 'This', 'Able', 'HR Manager', '09123456789', 'thisableee@gmail.com', 1, '2025-07-08 13:46:19', '2025-07-08 13:46:19');

-- --------------------------------------------------------

--
-- Table structure for table `employer_display_settings`
--

CREATE TABLE `employer_display_settings` (
  `display_id` int(11) NOT NULL,
  `employer_id` int(11) NOT NULL,
  `theme` enum('light','dark','system') DEFAULT 'light',
  `font_size` enum('small','medium','large') DEFAULT 'medium',
  `color_scheme` enum('default','blue','purple','red','custom') DEFAULT 'default',
  `high_contrast` tinyint(1) DEFAULT 0,
  `reduce_motion` tinyint(1) DEFAULT 0,
  `screen_reader_support` tinyint(1) DEFAULT 1,
  `default_view` enum('dashboard','job-listings','applicants','company-profile') DEFAULT 'dashboard',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employer_hiring_preferences`
--

CREATE TABLE `employer_hiring_preferences` (
  `preference_id` int(11) NOT NULL,
  `employer_id` int(11) NOT NULL,
  `open_to_pwd` tinyint(1) DEFAULT 1,
  `disability_types` text DEFAULT NULL,
  `workplace_accommodations` text DEFAULT NULL,
  `additional_accommodations` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employer_hiring_preferences`
--

INSERT INTO `employer_hiring_preferences` (`preference_id`, `employer_id`, `open_to_pwd`, `disability_types`, `workplace_accommodations`, `additional_accommodations`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '[\"mobility\"]', '[\"ramps\"]', '', '2025-06-09 20:48:20', '2025-06-09 20:48:20'),
(2, 6, 1, '[\"mobility\",\"hearing\"]', '[\"ramps\"]', '', '2025-06-13 00:15:16', '2025-06-13 00:15:16'),
(3, 7, 1, '[\"mobility\",\"cognitive\"]', '[\"ramps\",\"software\"]', '', '2025-07-08 13:47:50', '2025-07-08 13:47:50');

-- --------------------------------------------------------

--
-- Table structure for table `employer_notification_settings`
--

CREATE TABLE `employer_notification_settings` (
  `notification_id` int(11) NOT NULL,
  `employer_id` int(11) NOT NULL,
  `email_notifications` tinyint(1) DEFAULT 1,
  `sms_notifications` tinyint(1) DEFAULT 0,
  `push_notifications` tinyint(1) DEFAULT 1,
  `new_applications` tinyint(1) DEFAULT 1,
  `application_status` tinyint(1) DEFAULT 1,
  `message_notifications` tinyint(1) DEFAULT 1,
  `system_updates` tinyint(1) DEFAULT 1,
  `marketing_notifications` tinyint(1) DEFAULT 0,
  `email_frequency` enum('immediate','daily','weekly') DEFAULT 'immediate',
  `enable_quiet_hours` tinyint(1) DEFAULT 0,
  `quiet_from` time DEFAULT '22:00:00',
  `quiet_to` time DEFAULT '08:00:00',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employer_privacy_settings`
--

CREATE TABLE `employer_privacy_settings` (
  `privacy_id` int(11) NOT NULL,
  `employer_id` int(11) NOT NULL,
  `profile_visibility` tinyint(1) DEFAULT 1,
  `share_company_info` tinyint(1) DEFAULT 1,
  `share_contact_info` tinyint(1) DEFAULT 0,
  `job_visibility` enum('public','limited','private') DEFAULT 'public',
  `allow_data_collection` tinyint(1) DEFAULT 1,
  `allow_marketing` tinyint(1) DEFAULT 0,
  `allow_third_party` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employer_setup_progress`
--

CREATE TABLE `employer_setup_progress` (
  `progress_id` int(11) NOT NULL,
  `employer_id` int(11) NOT NULL,
  `basic_info_complete` tinyint(1) DEFAULT 0,
  `company_description_complete` tinyint(1) DEFAULT 0,
  `description_complete` tinyint(1) DEFAULT 0,
  `hiring_preferences_complete` tinyint(1) DEFAULT 0,
  `preferences_complete` tinyint(1) DEFAULT 0,
  `social_links_complete` tinyint(1) DEFAULT 0,
  `social_complete` tinyint(1) DEFAULT 0,
  `logo_uploaded` tinyint(1) DEFAULT 0,
  `logo_upload_complete` tinyint(1) DEFAULT 0,
  `setup_complete` tinyint(1) DEFAULT 0,
  `completion_percentage` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employer_setup_progress`
--

INSERT INTO `employer_setup_progress` (`progress_id`, `employer_id`, `basic_info_complete`, `company_description_complete`, `description_complete`, `hiring_preferences_complete`, `preferences_complete`, `social_links_complete`, `social_complete`, `logo_uploaded`, `logo_upload_complete`, `setup_complete`, `completion_percentage`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 1, 1, 100, '2025-06-09 20:46:52', '2025-06-09 20:48:44'),
(2, 3, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 20, '2025-06-10 04:53:16', '2025-06-10 04:53:16'),
(3, 6, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 100, '2025-06-13 00:02:22', '2025-06-13 00:16:14'),
(4, 7, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 100, '2025-07-08 13:46:19', '2025-07-08 13:48:11');

-- --------------------------------------------------------

--
-- Table structure for table `employer_social_links`
--

CREATE TABLE `employer_social_links` (
  `social_id` int(11) NOT NULL,
  `employer_id` int(11) NOT NULL,
  `website_url` varchar(255) DEFAULT NULL,
  `facebook_url` varchar(255) DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `twitter_url` varchar(255) DEFAULT NULL,
  `instagram_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employer_social_links`
--

INSERT INTO `employer_social_links` (`social_id`, `employer_id`, `website_url`, `facebook_url`, `linkedin_url`, `twitter_url`, `instagram_url`, `created_at`, `updated_at`) VALUES
(1, 1, 'https://example.com', '', '', NULL, NULL, '2025-06-09 20:48:44', '2025-06-09 20:48:44'),
(2, 6, 'https://example.com', '', '', NULL, NULL, '2025-06-13 00:16:14', '2025-06-13 00:16:14'),
(3, 7, 'https://example.com', '', '', NULL, NULL, '2025-07-08 13:48:11', '2025-07-08 13:48:11');

-- --------------------------------------------------------

--
-- Table structure for table `experience`
--

CREATE TABLE `experience` (
  `experience_id` int(11) NOT NULL,
  `seeker_id` int(11) NOT NULL,
  `job_title` varchar(255) NOT NULL,
  `company` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `is_current` tinyint(1) DEFAULT 0,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `experience`
--

INSERT INTO `experience` (`experience_id`, `seeker_id`, `job_title`, `company`, `location`, `start_date`, `end_date`, `is_current`, `description`, `created_at`) VALUES
(1, 4, 'Web Dev', 'Ex Company', 'Philippines', '2023-05-01', '2025-05-01', 0, 'Senior Programmer', '2025-07-04 07:26:24');

-- --------------------------------------------------------

--
-- Table structure for table `feedback_templates`
--

CREATE TABLE `feedback_templates` (
  `template_id` int(11) NOT NULL,
  `employer_id` int(11) NOT NULL,
  `template_title` varchar(100) NOT NULL,
  `template_content` text NOT NULL,
  `template_type` enum('rejection','interview_request','general') DEFAULT 'rejection',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `industries`
--

CREATE TABLE `industries` (
  `industry_id` int(11) NOT NULL,
  `industry_name` varchar(100) NOT NULL,
  `industry_icon` varchar(50) DEFAULT 'fa-building',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `industries`
--

INSERT INTO `industries` (`industry_id`, `industry_name`, `industry_icon`, `created_at`) VALUES
(1, 'Technology & IT', 'fa-laptop-code', '2025-05-28 18:57:28'),
(2, 'Business Process Outsourcing (BPO)', 'fa-headset', '2025-05-28 18:57:28'),
(3, 'Manufacturing', 'fa-industry', '2025-05-28 18:57:28'),
(4, 'Healthcare & Medical', 'fa-heart', '2025-05-28 18:57:28'),
(5, 'Education & Training', 'fa-graduation-cap', '2025-05-28 18:57:28'),
(6, 'Financial Services', 'fa-chart-line', '2025-05-28 18:57:28'),
(7, 'Retail & Sales', 'fa-store', '2025-05-28 18:57:28'),
(8, 'Food & Beverage', 'fa-utensils', '2025-05-28 18:57:28'),
(9, 'Transportation & Logistics', 'fa-truck', '2025-05-28 18:57:28'),
(10, 'Government & Public Service', 'fa-landmark', '2025-05-28 18:57:28'),
(11, 'Non-Profit Organizations', 'fa-hands-helping', '2025-05-28 18:57:28'),
(12, 'Creative & Media', 'fa-palette', '2025-05-28 18:57:28');

-- --------------------------------------------------------

--
-- Table structure for table `interviews`
--

CREATE TABLE `interviews` (
  `interview_id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `interview_type` enum('online','in_person','phone') NOT NULL,
  `scheduled_date` date NOT NULL,
  `scheduled_time` time NOT NULL,
  `duration_minutes` int(11) DEFAULT 60,
  `interview_platform` varchar(100) DEFAULT NULL,
  `meeting_link` varchar(500) DEFAULT NULL,
  `meeting_id` varchar(100) DEFAULT NULL,
  `location_address` text DEFAULT NULL,
  `interviewer_notes` text DEFAULT NULL,
  `candidate_notes` text DEFAULT NULL,
  `interview_status` enum('scheduled','confirmed','completed','cancelled','rescheduled','no_show') DEFAULT 'scheduled',
  `accommodations_needed` text DEFAULT NULL,
  `sign_language_interpreter` tinyint(1) DEFAULT 0,
  `wheelchair_accessible_venue` tinyint(1) DEFAULT 0,
  `screen_reader_materials` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by_employer_id` int(11) DEFAULT NULL,
  `reminder_sent` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `interviews`
--

INSERT INTO `interviews` (`interview_id`, `application_id`, `interview_type`, `scheduled_date`, `scheduled_time`, `duration_minutes`, `interview_platform`, `meeting_link`, `meeting_id`, `location_address`, `interviewer_notes`, `candidate_notes`, `interview_status`, `accommodations_needed`, `sign_language_interpreter`, `wheelchair_accessible_venue`, `screen_reader_materials`, `created_at`, `updated_at`, `created_by_employer_id`, `reminder_sent`) VALUES
(2, 6, 'online', '2025-07-05', '10:00:00', 60, 'zoom', '', NULL, '', '', NULL, 'scheduled', '', 1, 0, 0, '2025-07-04 01:16:41', '2025-07-04 01:16:41', 6, 0),
(3, 4, 'online', '2025-07-09', '10:00:00', 60, 'zoom', '', NULL, '', '', NULL, 'scheduled', '', 0, 0, 0, '2025-07-08 19:37:03', '2025-07-08 19:37:03', 6, 0),
(4, 9, 'online', '2025-07-09', '10:00:00', 60, 'zoom', '', NULL, '', '', NULL, 'scheduled', '', 0, 0, 0, '2025-07-08 20:19:05', '2025-07-08 20:19:05', 6, 0);

-- --------------------------------------------------------

--
-- Table structure for table `interview_feedback`
--

CREATE TABLE `interview_feedback` (
  `feedback_id` int(11) NOT NULL,
  `interview_id` int(11) NOT NULL,
  `technical_score` int(11) DEFAULT NULL,
  `communication_score` int(11) DEFAULT NULL,
  `cultural_fit_score` int(11) DEFAULT NULL,
  `overall_rating` int(11) DEFAULT NULL,
  `strengths` text DEFAULT NULL,
  `areas_for_improvement` text DEFAULT NULL,
  `recommendation` enum('strongly_recommend','recommend','maybe','not_recommend','strongly_not_recommend') DEFAULT NULL,
  `detailed_feedback` text DEFAULT NULL,
  `accessibility_needs_discussed` tinyint(1) DEFAULT 0,
  `accommodation_feasibility` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_accommodations`
--

CREATE TABLE `job_accommodations` (
  `accommodation_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `wheelchair_accessible` tinyint(1) DEFAULT 0,
  `flexible_schedule` tinyint(1) DEFAULT 0,
  `assistive_technology` tinyint(1) DEFAULT 0,
  `remote_work_option` tinyint(1) DEFAULT 0,
  `screen_reader_compatible` tinyint(1) DEFAULT 0,
  `sign_language_interpreter` tinyint(1) DEFAULT 0,
  `modified_workspace` tinyint(1) DEFAULT 0,
  `transportation_support` tinyint(1) DEFAULT 0,
  `additional_accommodations` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_accommodations`
--

INSERT INTO `job_accommodations` (`accommodation_id`, `job_id`, `wheelchair_accessible`, `flexible_schedule`, `assistive_technology`, `remote_work_option`, `screen_reader_compatible`, `sign_language_interpreter`, `modified_workspace`, `transportation_support`, `additional_accommodations`, `created_at`, `updated_at`) VALUES
(1, 1, 0, 0, 0, 0, 0, 0, 0, 0, '', '2025-06-09 20:49:57', '2025-06-09 20:49:57'),
(2, 2, 1, 0, 1, 1, 1, 0, 1, 1, NULL, '2025-07-02 00:39:26', '2025-07-02 00:39:26'),
(4, 3, 0, 0, 0, 0, 0, 0, 0, 0, '', '2025-07-02 01:44:32', '2025-07-02 01:45:53'),
(5, 4, 0, 0, 0, 0, 0, 0, 0, 0, '', '2025-07-02 01:47:54', '2025-07-02 01:47:54'),
(6, 5, 1, 0, 0, 0, 0, 0, 0, 0, '', '2025-07-04 15:06:27', '2025-07-04 15:06:27'),
(7, 6, 0, 0, 0, 0, 0, 0, 0, 0, '', '2025-07-04 15:13:06', '2025-07-04 15:13:06'),
(8, 7, 0, 0, 0, 0, 0, 0, 0, 0, '', '2025-07-04 15:40:23', '2025-07-04 15:40:23'),
(9, 8, 0, 0, 0, 0, 0, 0, 0, 0, '', '2025-07-05 07:03:07', '2025-07-05 07:03:07'),
(10, 9, 0, 0, 0, 0, 0, 0, 0, 0, '', '2025-07-05 07:06:45', '2025-07-05 07:06:45'),
(11, 10, 0, 0, 0, 0, 0, 0, 0, 0, '', '2025-07-07 14:55:30', '2025-07-07 14:55:30'),
(12, 11, 0, 0, 0, 0, 0, 0, 0, 0, '', '2025-07-08 14:00:02', '2025-07-08 14:00:02'),
(13, 12, 0, 0, 0, 0, 0, 0, 0, 0, '', '2025-07-10 03:00:22', '2025-07-10 03:00:22'),
(14, 13, 0, 0, 0, 0, 0, 0, 0, 0, '', '2025-07-11 01:10:31', '2025-07-11 01:10:31');

-- --------------------------------------------------------

--
-- Table structure for table `job_alert_settings`
--

CREATE TABLE `job_alert_settings` (
  `alert_id` int(11) NOT NULL,
  `seeker_id` int(11) NOT NULL,
  `alert_frequency` enum('daily','weekly','off') DEFAULT 'daily',
  `email_alerts` tinyint(1) DEFAULT 1,
  `sms_alerts` tinyint(1) DEFAULT 0,
  `app_alerts` tinyint(1) DEFAULT 1,
  `job_categories` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`job_categories`)),
  `job_keywords` text DEFAULT NULL,
  `job_location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_analytics`
--

CREATE TABLE `job_analytics` (
  `analytics_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `date_recorded` date NOT NULL,
  `views_count` int(11) DEFAULT 0,
  `applications_count` int(11) DEFAULT 0,
  `saves_count` int(11) DEFAULT 0,
  `clicks_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_applications`
--

CREATE TABLE `job_applications` (
  `application_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `seeker_id` int(11) NOT NULL,
  `resume_id` int(11) DEFAULT NULL,
  `cover_letter` text DEFAULT NULL,
  `application_status` enum('submitted','under_review','shortlisted','interview_scheduled','interviewed','hired','rejected','withdrawn') DEFAULT 'submitted',
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status_updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `employer_notes` text DEFAULT NULL,
  `candidate_notes` text DEFAULT NULL,
  `interview_score` int(11) DEFAULT NULL,
  `match_score` decimal(5,2) DEFAULT 0.00,
  `skills_matched` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`skills_matched`)),
  `skills_missing` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`skills_missing`)),
  `accommodation_compatibility` decimal(5,2) DEFAULT 0.00,
  `rejection_reason` text DEFAULT NULL,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_applications`
--

INSERT INTO `job_applications` (`application_id`, `job_id`, `seeker_id`, `resume_id`, `cover_letter`, `application_status`, `applied_at`, `status_updated_at`, `employer_notes`, `candidate_notes`, `interview_score`, `match_score`, `skills_matched`, `skills_missing`, `accommodation_compatibility`, `rejection_reason`, `last_activity`) VALUES
(3, 2, 6, 6, '{\"include_cover_letter\":0,\"include_portfolio\":0,\"include_references\":0}', 'submitted', '2025-06-29 22:39:46', '2025-07-10 01:41:24', NULL, '', NULL, 27.50, '[\"Social Media Management\"]', '[\"Digital Literacy\",\"Data Entry\",\"Microsoft Office\",\"Data Analysis\",\"Basic Coding\",\"Basic AI Understanding\",\"Problem Resolution\",\"Complaint Management\",\"Virtual Assistant Skills\",\"Document Management\",\"Filing and Record Keeping\",\"Office Equipment Operation\",\"Basic Bookkeeping\"]', 0.00, NULL, '2025-07-10 01:41:24'),
(4, 2, 4, NULL, '{\"include_cover_letter\":0,\"include_portfolio\":0,\"include_references\":0}', 'interview_scheduled', '2025-07-01 01:46:47', '2025-07-19 04:34:51', NULL, '', NULL, 52.50, '[\"Basic Bookkeeping\"]', '[\"Digital Literacy\",\"Data Entry\",\"Microsoft Office\",\"Data Analysis\",\"Basic Coding\",\"Social Media Management\",\"Basic AI Understanding\",\"Problem Resolution\",\"Complaint Management\",\"Virtual Assistant Skills\",\"Document Management\",\"Filing and Record Keeping\",\"Office Equipment Operation\"]', 100.00, NULL, '2025-07-19 04:34:51'),
(5, 2, 5, 5, '{\"include_cover_letter\":0,\"include_portfolio\":0,\"include_references\":0}', 'submitted', '2025-07-01 01:47:49', '2025-07-10 01:41:24', NULL, '', NULL, 52.50, '[\"Digital Literacy\"]', '[\"Data Entry\",\"Microsoft Office\",\"Data Analysis\",\"Basic Coding\",\"Social Media Management\",\"Basic AI Understanding\",\"Problem Resolution\",\"Complaint Management\",\"Virtual Assistant Skills\",\"Document Management\",\"Filing and Record Keeping\",\"Office Equipment Operation\",\"Basic Bookkeeping\"]', 100.00, NULL, '2025-07-10 01:41:24'),
(6, 3, 4, NULL, '{\"include_cover_letter\":0,\"include_portfolio\":0,\"include_references\":0}', 'rejected', '2025-07-02 01:44:46', '2025-07-19 04:34:50', NULL, '', NULL, 85.00, '[]', '[]', 100.00, NULL, '2025-07-19 04:34:50'),
(7, 4, 4, NULL, '{\"include_cover_letter\":0,\"include_portfolio\":0,\"include_references\":0}', 'submitted', '2025-07-02 01:49:05', '2025-07-19 04:34:50', NULL, '', NULL, 50.00, '[]', '[\"Basic Coding\",\"Digital Literacy\",\"Microsoft Office\"]', 100.00, NULL, '2025-07-19 04:34:50'),
(8, 5, 4, NULL, '{\"include_cover_letter\":0,\"include_portfolio\":0,\"include_references\":0}', 'rejected', '2025-07-04 15:06:47', '2025-08-04 06:56:03', NULL, '', NULL, 50.00, '[]', '[\"Complaint Management\",\"Product Knowledge\"]', 100.00, NULL, '2025-08-04 06:56:03'),
(9, 6, 4, NULL, '{\"include_cover_letter\":0,\"include_portfolio\":0,\"include_references\":0}', 'withdrawn', '2025-07-04 15:13:14', '2025-09-07 19:37:57', NULL, '--- Withdrawal ---\nReason: Test\nWithdrawn on: 2025-09-08 03:37:57', NULL, 85.00, '[]', '[]', 100.00, NULL, '2025-09-07 19:37:57'),
(10, 12, 4, NULL, '{\"include_cover_letter\":0,\"include_portfolio\":0,\"include_references\":0}', 'submitted', '2025-07-10 03:00:34', '2025-07-19 04:34:50', NULL, '', NULL, 58.75, '[\"Basic Bookkeeping\"]', '[\"Call Handling\",\"Basic AI Understanding\",\"Basic Coding\"]', 100.00, NULL, '2025-07-19 04:34:50');

-- --------------------------------------------------------

--
-- Table structure for table `job_posts`
--

CREATE TABLE `job_posts` (
  `job_id` int(11) NOT NULL,
  `employer_id` int(11) NOT NULL,
  `job_title` varchar(255) NOT NULL,
  `job_description` text NOT NULL,
  `job_requirements` text NOT NULL,
  `department` varchar(100) NOT NULL,
  `location` varchar(255) NOT NULL,
  `employment_type` enum('Full-time','Part-time','Contract','Internship','Freelance') NOT NULL,
  `salary_range` varchar(100) DEFAULT NULL,
  `application_deadline` date DEFAULT NULL,
  `job_status` enum('draft','active','paused','closed','expired') DEFAULT 'draft',
  `remote_work_available` tinyint(1) DEFAULT 0,
  `flexible_schedule` tinyint(1) DEFAULT 0,
  `requires_degree` tinyint(1) DEFAULT 0,
  `degree_field` varchar(100) DEFAULT NULL,
  `requires_certification` tinyint(1) DEFAULT 0,
  `certification_type` varchar(100) DEFAULT NULL,
  `requires_license` tinyint(1) DEFAULT 0,
  `license_type` varchar(100) DEFAULT NULL,
  `min_experience_years` int(11) DEFAULT 0,
  `specific_industry_exp` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `posted_at` timestamp NULL DEFAULT NULL,
  `views_count` int(11) DEFAULT 0,
  `applications_count` int(11) DEFAULT 0,
  `requires_credentials` tinyint(1) DEFAULT 0,
  `credential_types` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`credential_types`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_posts`
--

INSERT INTO `job_posts` (`job_id`, `employer_id`, `job_title`, `job_description`, `job_requirements`, `department`, `location`, `employment_type`, `salary_range`, `application_deadline`, `job_status`, `remote_work_available`, `flexible_schedule`, `requires_degree`, `degree_field`, `requires_certification`, `certification_type`, `requires_license`, `license_type`, `min_experience_years`, `specific_industry_exp`, `created_at`, `updated_at`, `posted_at`, `views_count`, `applications_count`, `requires_credentials`, `credential_types`) VALUES
(1, 1, 'Test Job', 'Test Role', 'Looking for candidates with Digital Literacy, Data Entry, Microsoft Office, and Customer Service skills. Must have experience with Call Handling, Client Communication, and Problem Resolution. Knowledge of Web Development and Basic Coding preferred.', 'Engineering', 'Gen. Trias, Cavite', 'Part-time', '10000', '2025-06-28', 'active', 0, 1, 0, NULL, 0, NULL, 0, NULL, 0, 0, '2025-06-09 20:49:57', '2025-07-02 00:39:26', '2025-06-09 14:49:57', 16, 1, 0, NULL),
(2, 6, 'Test', 'Test', 'Seeking professionals with Basic Coding skills, Web Development experience, Digital Literacy, and Database Management capabilities. Strong Customer Service, Microsoft Office, and Data Entry skills required. Experience with Problem Resolution and technical troubleshooting essential.', 'Design', 'Gen. Trias, Cavite', 'Full-time', '', '0000-00-00', 'active', 0, 0, 0, NULL, 0, NULL, 0, NULL, 0, 0, '2025-06-13 01:09:59', '2025-07-02 00:39:26', '2025-06-13 01:09:59', 0, 4, 0, NULL),
(3, 6, 'Test Job 2', 'Testing', 'Graduate', 'Engineering', 'Cavite', 'Part-time', '25000', NULL, 'active', 1, 0, 0, NULL, 0, NULL, 0, NULL, 0, 0, '2025-07-02 01:44:32', '2025-07-02 01:45:53', '2025-07-01 19:44:32', 0, 1, 0, NULL),
(4, 6, 'Test Web Developer', 'Test job for matching system', '', 'Engineering', 'Manila, Philippines', 'Full-time', NULL, '2025-08-23', 'active', 0, 0, 0, NULL, 0, NULL, 0, NULL, 0, 0, '2025-07-02 01:47:54', '2025-07-02 01:49:05', '2025-07-01 19:47:54', 0, 1, 0, NULL),
(5, 6, 'Cashier', 'Bilangin mo pera pero tagpipiso', 'Experience: 2+ years experience', 'Customer Service', 'Cavite', 'Full-time', '25000', '2025-08-01', 'active', 0, 0, 0, NULL, 0, NULL, 0, NULL, 0, 0, '2025-07-04 15:06:27', '2025-07-04 15:06:47', '2025-07-04 09:06:27', 0, 1, 0, NULL),
(6, 6, 'Waiter', 'Basta', 'Experience: 2+ years experience', 'Design', 'Cavite', 'Part-time', '25,000', '2025-08-01', 'active', 0, 0, 0, NULL, 0, NULL, 0, NULL, 0, 0, '2025-07-04 15:13:06', '2025-07-04 15:13:14', '2025-07-04 09:13:06', 0, 1, 0, NULL),
(7, 6, 'Do not apply', 'Do not apply', 'Experience: 2+ years experience', 'Design', 'Cavite', 'Full-time', '25000', '2025-08-01', 'active', 0, 0, 0, NULL, 0, NULL, 0, NULL, 0, 0, '2025-07-04 15:40:23', '2025-07-04 15:40:23', '2025-07-04 09:40:23', 0, 0, 0, NULL),
(8, 6, 'Test Job 3', 'Test', 'Requirements will be discussed during the interview process.', 'Marketing', 'Cavite', 'Full-time', '25000', '2025-07-26', 'active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-05 07:03:07', '2025-07-05 07:03:07', '2025-07-05 01:03:07', 0, 0, 0, NULL),
(9, 6, 'Test Job 3', 'Test', 'Requirements will be discussed during the interview process.', 'Design', 'Cavite', 'Full-time', '25000', '2025-08-09', 'active', 0, 0, 0, NULL, 1, NULL, 0, NULL, 4, 0, '2025-07-05 07:06:45', '2025-07-05 07:06:45', '2025-07-05 01:06:45', 0, 0, 0, NULL),
(10, 6, 'Test Job 4', 'Test', 'Requirements will be discussed during the interview process.', 'Sales', 'Cavite', 'Full-time', '20000', '2025-08-14', 'active', 0, 0, 0, NULL, 1, NULL, 0, NULL, 3, 0, '2025-07-07 14:55:30', '2025-07-07 14:55:30', '2025-07-07 08:55:30', 0, 0, 0, NULL),
(11, 7, 'Salesman', 'Test', 'Requirements will be discussed during the interview process.', 'Sales', 'Cavite', 'Full-time', NULL, '2025-08-09', 'active', 0, 0, 0, NULL, 0, NULL, 0, NULL, 3, 0, '2025-07-08 14:00:02', '2025-07-08 14:00:02', '2025-07-08 08:00:02', 0, 0, 0, NULL),
(12, 6, 'Test Job 5', 'Test', 'Requirements will be discussed during the interview process.', 'Engineering', 'Cavite', 'Full-time', '25000', '2025-08-01', 'active', 0, 0, 1, NULL, 0, NULL, 0, NULL, 2, 0, '2025-07-10 03:00:22', '2025-07-10 03:00:34', '2025-07-09 21:00:22', 0, 1, 0, NULL),
(13, 6, 'Teacher', 'Test', 'Requirements will be discussed during the interview process.', 'Engineering', 'Cavite', 'Full-time', '25000', '2025-08-01', 'active', 0, 0, 1, NULL, 0, NULL, 0, NULL, 2, 0, '2025-07-11 01:10:31', '2025-07-11 01:16:35', '2025-07-10 19:10:31', 0, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `job_requirements`
--

CREATE TABLE `job_requirements` (
  `requirement_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `is_required` tinyint(1) DEFAULT 1,
  `experience_level` enum('beginner','intermediate','advanced','expert') DEFAULT 'intermediate',
  `priority` enum('critical','important','preferred') DEFAULT 'important',
  `weight` decimal(3,2) DEFAULT 1.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_requirements`
--

INSERT INTO `job_requirements` (`requirement_id`, `job_id`, `skill_id`, `is_required`, `experience_level`, `priority`, `weight`) VALUES
(8, 4, 5, 1, 'intermediate', 'important', 1.00),
(9, 4, 1, 1, 'intermediate', 'important', 1.00),
(10, 4, 3, 1, 'intermediate', 'important', 1.00),
(11, 5, 13, 1, 'intermediate', 'important', 1.00),
(12, 5, 12, 1, 'intermediate', 'important', 1.00),
(13, 11, 9, 1, 'intermediate', 'important', 1.00),
(14, 11, 12, 1, 'intermediate', 'important', 1.00),
(15, 12, 22, 1, 'intermediate', 'important', 1.00),
(16, 12, 9, 1, 'intermediate', 'important', 1.00),
(17, 12, 8, 1, 'intermediate', 'important', 1.00),
(18, 12, 5, 1, 'intermediate', 'important', 1.00),
(19, 13, 22, 1, 'intermediate', 'important', 1.00),
(20, 13, 21, 1, 'intermediate', 'important', 1.00);

-- --------------------------------------------------------

--
-- Table structure for table `job_seekers`
--

CREATE TABLE `job_seekers` (
  `seeker_id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) NOT NULL,
  `suffix` varchar(50) DEFAULT NULL,
  `disability_id` int(11) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `setup_complete` tinyint(1) DEFAULT 0,
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_seekers`
--

INSERT INTO `job_seekers` (`seeker_id`, `first_name`, `middle_name`, `last_name`, `suffix`, `disability_id`, `contact_number`, `created_at`, `setup_complete`, `city`, `province`) VALUES
(4, 'Shan Michael', 'Martillana', 'Baccay', '', 4, '09664873735', '2025-06-22 21:00:34', 1, 'Gen3', '80vac'),
(5, 'Applicant', '', 'One', '', 1, '09123456789', '2025-06-29 22:14:38', 1, NULL, NULL),
(6, 'Applicant', '', 'Two', '', 2, '09123456789', '2025-06-29 22:17:21', 1, NULL, NULL),
(7, 'Jewel', '', 'Paira', '', 1, '09123456789', '2025-09-13 01:13:36', 0, NULL, NULL),
(8, 'Erin', '', 'Baguhin', '', 1, '09123456789', '2025-09-25 13:19:19', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `job_views`
--

CREATE TABLE `job_views` (
  `view_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `seeker_id` int(11) DEFAULT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_views`
--

INSERT INTO `job_views` (`view_id`, `job_id`, `seeker_id`, `viewed_at`, `ip_address`, `user_agent`) VALUES
(1, 1, NULL, '2025-06-09 20:50:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(2, 1, NULL, '2025-06-09 20:50:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(3, 1, NULL, '2025-06-10 01:57:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(4, 1, NULL, '2025-06-10 01:58:29', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(5, 1, NULL, '2025-06-10 02:42:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(6, 1, NULL, '2025-06-10 02:42:11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(7, 1, NULL, '2025-06-10 02:42:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(8, 1, NULL, '2025-06-10 02:42:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(9, 1, NULL, '2025-06-10 02:42:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(10, 1, NULL, '2025-06-10 02:42:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(11, 1, NULL, '2025-06-10 04:50:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(12, 1, NULL, '2025-06-10 04:50:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(13, 1, NULL, '2025-06-10 04:54:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(14, 1, NULL, '2025-06-10 04:54:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(15, 1, NULL, '2025-06-12 23:27:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(16, 1, NULL, '2025-06-12 23:29:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `recipient_type` enum('employer','candidate') NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `related_job_id` int(11) DEFAULT NULL,
  `related_application_id` int(11) DEFAULT NULL,
  `related_interview_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `email_sent` tinyint(1) DEFAULT 0,
  `email_sent_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `recipient_type`, `recipient_id`, `type_id`, `title`, `message`, `related_job_id`, `related_application_id`, `related_interview_id`, `is_read`, `read_at`, `created_at`, `email_sent`, `email_sent_at`) VALUES
(1, 'candidate', 1, 5, 'Welcome to ThisAble!', 'Thank you for joining our platform. Complete your profile to get started finding your next opportunity.', NULL, NULL, NULL, 0, NULL, '2025-06-09 20:44:51', 0, NULL),
(2, 'employer', 1, 4, 'Job Posted Successfully', 'Your job posting for \"Test Job\" has been published and is now live.', 1, NULL, NULL, 0, NULL, '2025-06-09 20:49:57', 0, NULL),
(3, 'candidate', 2, 5, 'Welcome to ThisAble!', 'Thank you for joining our platform. Complete your profile to get started finding your next opportunity.', NULL, NULL, NULL, 0, NULL, '2025-06-10 04:44:29', 0, NULL),
(4, 'employer', 6, 4, 'Job Posted Successfully', 'Your job posting \"Test\" is now live and accepting applications.', 2, NULL, NULL, 0, NULL, '2025-06-13 01:09:59', 0, NULL),
(8, 'candidate', 4, 5, 'Welcome to ThisAble!', 'Thank you for joining our platform. Complete your profile to get started finding your next opportunity.', NULL, NULL, NULL, 0, NULL, '2025-06-29 19:38:16', 0, NULL),
(9, 'candidate', 5, 5, 'Welcome to ThisAble!', 'Thank you for joining our platform. Complete your profile to get started finding your next opportunity.', NULL, NULL, NULL, 0, NULL, '2025-06-29 22:15:39', 0, NULL),
(10, 'candidate', 6, 5, 'Welcome to ThisAble!', 'Thank you for joining our platform. Complete your profile to get started finding your next opportunity.', NULL, NULL, NULL, 0, NULL, '2025-06-29 22:18:19', 0, NULL),
(11, 'candidate', 4, 3, 'Application Under Review', 'Your application for Test Job 2 at Test Company is now under review.', 3, 6, NULL, 0, NULL, '2025-07-02 05:47:45', 0, NULL),
(12, 'candidate', 4, 3, 'Interview Scheduled', 'An interview has been scheduled for your application to Test Job 2 at Test Company.', 3, 6, NULL, 0, NULL, '2025-07-04 01:14:35', 0, NULL),
(13, 'candidate', 4, 3, 'Application Under Review', 'Your application for Test Job 2 at Test Company is now under review.', 3, 6, NULL, 0, NULL, '2025-07-04 01:15:04', 0, NULL),
(14, 'candidate', 4, 3, 'Interview Scheduled', 'An interview has been scheduled for your application to Test Job 2 at Test Company.', 3, 6, NULL, 0, NULL, '2025-07-04 01:15:20', 0, NULL),
(15, 'candidate', 4, 3, 'Congratulations - You\'re Hired!', 'Congratulations! You\'ve been selected for Test Job 2 at Test Company!', 3, 6, NULL, 0, NULL, '2025-07-04 01:15:26', 0, NULL),
(16, 'candidate', 4, 3, 'Application Under Review', 'Your application for Test Job 2 at Test Company is now under review.', 3, 6, NULL, 1, '2025-07-04 01:24:16', '2025-07-04 01:15:42', 0, NULL),
(17, 'employer', 6, 2, 'Interview Scheduled', 'Interview scheduled with Shan Michael Baccay for Test Job 2 on July 5, 2025 at 10:00 AM.', 3, 6, 2, 0, NULL, '2025-07-04 01:16:41', 0, NULL),
(18, 'candidate', 4, 2, 'Interview Scheduled', 'You have an interview scheduled for Test Job 2 on Jul 5, 2025 at 10:00 AM. Accommodations: Sign language interpreter will be provided', NULL, 6, 2, 0, NULL, '2025-07-04 01:16:41', 0, NULL),
(19, 'candidate', 4, 3, 'Application Under Review', 'Your application for Test Job 2 at Test Company is now under review.', 3, 6, NULL, 0, NULL, '2025-07-04 01:17:06', 0, NULL),
(20, 'candidate', 4, 3, 'Application Under Review', 'Your application for Cashier at Test Company is now under review.', 5, 8, NULL, 0, NULL, '2025-07-04 15:07:24', 0, NULL),
(21, 'candidate', 4, 3, 'Interview Scheduled', 'An interview has been scheduled for your application to Test Job 2 at Test Company.', 3, 6, NULL, 0, NULL, '2025-07-06 23:31:18', 0, NULL),
(22, 'candidate', 4, 3, 'Application Update', 'Thank you for your interest in Test at Test Company. Unfortunately, we won\'t be moving forward at this time.', 2, 4, NULL, 0, NULL, '2025-07-06 23:56:16', 0, NULL),
(23, 'candidate', 4, 3, 'Application Under Review', 'Your application for Test Job 2 at Test Company is now under review.', 3, 6, NULL, 0, NULL, '2025-07-08 02:11:55', 0, NULL),
(24, 'candidate', 4, 3, 'Interview Scheduled', 'An interview has been scheduled for your application to Test Job 2 at Test Company.', 3, 6, NULL, 0, NULL, '2025-07-08 02:37:43', 0, NULL),
(25, 'candidate', 4, 3, 'Congratulations - You\'re Hired!', 'Congratulations! You\'ve been selected for Test Job 2 at Test Company!', 3, 6, NULL, 0, NULL, '2025-07-08 03:14:12', 0, NULL),
(26, 'candidate', 4, 3, 'Application Under Review', 'Your application for Test Job 2 at Test Company is now under review.', 3, 6, NULL, 0, NULL, '2025-07-08 03:14:14', 0, NULL),
(27, 'candidate', 4, 3, 'Interview Scheduled', 'An interview has been scheduled for your application to Test Job 2 at Test Company.', 3, 6, NULL, 0, NULL, '2025-07-08 03:24:12', 0, NULL),
(28, 'employer', 6, 2, 'Interview Scheduled', 'Interview scheduled with Shan Michael Baccay for Test on July 9, 2025 at 10:00 AM.', 2, 4, 3, 0, NULL, '2025-07-08 19:37:03', 0, NULL),
(29, 'candidate', 4, 2, 'Interview Scheduled', 'You have an interview scheduled for Test on Jul 9, 2025 at 10:00 AM.', NULL, 4, 3, 0, NULL, '2025-07-08 19:37:03', 0, NULL),
(30, 'employer', 6, 2, 'Interview Scheduled', 'Interview scheduled with Shan Michael Baccay for Waiter on July 9, 2025 at 10:00 AM.', 6, 9, 4, 0, NULL, '2025-07-08 20:19:05', 0, NULL),
(31, 'candidate', 4, 2, 'Interview Scheduled', 'You have an interview scheduled for Waiter on Jul 9, 2025 at 10:00 AM.', NULL, 9, 4, 0, NULL, '2025-07-08 20:19:05', 0, NULL),
(32, 'candidate', 4, 3, 'Application Under Review', 'Your application for Waiter at Test Company is now under review.', 6, 9, NULL, 0, NULL, '2025-07-08 20:19:17', 0, NULL),
(33, 'candidate', 4, 3, 'Application Update', 'Thank you for your interest in Test Job 2 at Test Company. Unfortunately, we won\'t be moving forward at this time.', 3, 6, NULL, 0, NULL, '2025-07-10 23:52:09', 0, NULL),
(34, 'employer', 6, 5, 'Job Closed', 'Your job posting for \"Teacher\" has been closed to new applications.', NULL, NULL, NULL, 0, NULL, '2025-07-11 01:16:32', 0, NULL),
(35, 'candidate', 4, 3, 'Application Update', 'Thank you for your interest in Cashier at Test Company. Unfortunately, we won\'t be moving forward at this time.', 5, 8, NULL, 0, NULL, '2025-08-04 06:56:03', 0, NULL),
(36, 'employer', 6, 3, 'Application Withdrawn', 'A candidate has withdrawn their application for \"Waiter\"', NULL, 9, NULL, 0, NULL, '2025-09-07 19:37:57', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notification_settings`
--

CREATE TABLE `notification_settings` (
  `notification_id` int(11) NOT NULL,
  `seeker_id` int(11) NOT NULL,
  `email_notifications` tinyint(1) DEFAULT 1,
  `sms_notifications` tinyint(1) DEFAULT 0,
  `push_notifications` tinyint(1) DEFAULT 1,
  `job_alerts` tinyint(1) DEFAULT 1,
  `application_updates` tinyint(1) DEFAULT 1,
  `message_notifications` tinyint(1) DEFAULT 1,
  `marketing_notifications` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_types`
--

CREATE TABLE `notification_types` (
  `type_id` int(11) NOT NULL,
  `type_name` varchar(50) NOT NULL,
  `type_description` varchar(255) DEFAULT NULL,
  `icon_class` varchar(50) DEFAULT 'fas fa-bell',
  `color_class` varchar(50) DEFAULT 'blue'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification_types`
--

INSERT INTO `notification_types` (`type_id`, `type_name`, `type_description`, `icon_class`, `color_class`) VALUES
(1, 'new_application', 'New job application received', 'fas fa-user-plus', 'green'),
(2, 'interview_scheduled', 'Interview scheduled or updated', 'fas fa-calendar-alt', 'blue'),
(3, 'application_status', 'Application status changed', 'fas fa-clipboard-check', 'orange'),
(4, 'job_posted', 'Job successfully posted', 'fas fa-briefcase', 'purple'),
(5, 'system_update', 'System notifications', 'fas fa-cog', 'gray'),
(6, 'deadline_reminder', 'Application deadline reminder', 'fas fa-clock', 'red'),
(7, 'interview_reminder', 'Upcoming interview reminder', 'fas fa-bell', 'yellow'),
(8, 'interview_feedback', 'Interview feedback required', 'fas fa-clipboard-check', 'orange'),
(9, 'job_expiring', 'Job posting expiring soon', 'fas fa-clock', 'red'),
(10, 'job_performance', 'Job performance update', 'fas fa-chart-line', 'green'),
(11, 'subscription_renewal', 'Subscription renewal reminder', 'fas fa-credit-card', 'blue'),
(12, 'profile_completion', 'Profile completion reminder', 'fas fa-user-edit', 'orange');

-- --------------------------------------------------------

--
-- Table structure for table `privacy_settings`
--

CREATE TABLE `privacy_settings` (
  `privacy_id` int(11) NOT NULL,
  `seeker_id` int(11) NOT NULL,
  `profile_visibility` enum('all','verified','none') DEFAULT 'all',
  `peer_visibility` tinyint(1) DEFAULT 1,
  `search_listing` tinyint(1) DEFAULT 1,
  `data_collection` tinyint(1) DEFAULT 1,
  `third_party_sharing` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `profile_details`
--

CREATE TABLE `profile_details` (
  `profile_id` int(11) NOT NULL,
  `seeker_id` int(11) NOT NULL,
  `bio` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `profile_photo_path` varchar(255) DEFAULT NULL,
  `cover_photo_path` varchar(255) DEFAULT NULL,
  `headline` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `profile_details`
--

INSERT INTO `profile_details` (`profile_id`, `seeker_id`, `bio`, `location`, `profile_photo_path`, `cover_photo_path`, `headline`, `created_at`, `updated_at`) VALUES
(1, 4, 'Sample Bio', '', NULL, NULL, NULL, '2025-07-03 10:14:30', '2025-08-26 05:19:40');

-- --------------------------------------------------------

--
-- Table structure for table `pwd_ids`
--

CREATE TABLE `pwd_ids` (
  `pwd_id` int(11) NOT NULL,
  `seeker_id` int(11) NOT NULL,
  `pwd_id_number` varchar(50) NOT NULL,
  `issued_at` date DEFAULT curdate(),
  `is_verified` tinyint(1) DEFAULT 0,
  `verification_date` datetime DEFAULT NULL,
  `verification_attempts` int(11) DEFAULT 0,
  `id_image_path` varchar(255) DEFAULT NULL,
  `verification_status` enum('pending','verified','rejected') DEFAULT 'pending',
  `verification_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pwd_ids`
--

INSERT INTO `pwd_ids` (`pwd_id`, `seeker_id`, `pwd_id_number`, `issued_at`, `is_verified`, `verification_date`, `verification_attempts`, `id_image_path`, `verification_status`, `verification_notes`) VALUES
(4, 4, '12344', '2024-06-22', 0, NULL, 0, NULL, 'pending', NULL),
(5, 5, '001', '2024-12-30', 0, NULL, 0, '../../uploads/pwd_ids/5_6861bace7f33f.jpg', 'pending', NULL),
(6, 6, '002', '2021-12-02', 0, NULL, 0, '../../uploads/pwd_ids/6_6861bb7149100.jpg', 'pending', NULL),
(7, 7, '123123123', '2003-03-10', 0, NULL, 0, NULL, 'pending', NULL),
(8, 8, '12345679', '2024-09-04', 0, NULL, 0, NULL, 'pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `rejection_feedback`
--

CREATE TABLE `rejection_feedback` (
  `feedback_id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `employer_id` int(11) NOT NULL,
  `rejection_reasons` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`rejection_reasons`)),
  `missing_requirements` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`missing_requirements`)),
  `employer_comments` text DEFAULT NULL,
  `suggestions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`suggestions`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resumes`
--

CREATE TABLE `resumes` (
  `resume_id` int(11) NOT NULL,
  `seeker_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_current` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resumes`
--

INSERT INTO `resumes` (`resume_id`, `seeker_id`, `file_name`, `file_path`, `file_size`, `file_type`, `upload_date`, `is_current`) VALUES
(4, 5, 'Resume-Maria Santos.pdf', 'uploads/resumes/5_6861bafda85ad.pdf', 32781, 'application/pdf', '2025-06-29 22:15:25', 1),
(5, 5, 'Resume-Maria Santos.pdf', 'uploads/resumes/5_6861bb0bc4387.pdf', 32781, 'application/pdf', '2025-06-29 22:15:39', 1),
(6, 6, 'Resume-Maria Santos.pdf', 'uploads/resumes/6_6861bb9fb2c7c.pdf', 32781, 'application/pdf', '2025-06-29 22:18:07', 1),
(7, 4, 'Resume.pdf', 'uploads/resumes/4_687051ae2af26.pdf', 513524, 'application/pdf', '2025-07-10 23:50:06', 0),
(8, 4, 'temp.pdf', 'uploads/resumes/4_687069220912e.pdf', 241553, 'application/octet-stream', '2025-07-11 01:30:10', 0),
(9, 4, 'temp2.pdf', 'uploads/resumes/4_687069b7b536b.pdf', 304190, 'application/octet-stream', '2025-07-11 01:32:39', 0),
(10, 4, 'temp2.pdf', 'uploads/resumes/4_68706ba5899f9.pdf', 304190, 'application/octet-stream', '2025-07-11 01:40:53', 1);

-- --------------------------------------------------------

--
-- Table structure for table `saved_jobs`
--

CREATE TABLE `saved_jobs` (
  `saved_id` int(11) NOT NULL,
  `seeker_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `saved_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `seeker_skills`
--

CREATE TABLE `seeker_skills` (
  `seeker_skill_id` int(11) NOT NULL,
  `seeker_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seeker_skills`
--

INSERT INTO `seeker_skills` (`seeker_skill_id`, `seeker_id`, `skill_id`) VALUES
(70, 4, 22),
(51, 5, 1),
(53, 6, 7);

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE `skills` (
  `skill_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `skill_name` varchar(255) NOT NULL,
  `skill_icon` varchar(50) NOT NULL,
  `skill_tooltip` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`skill_id`, `category_id`, `skill_name`, `skill_icon`, `skill_tooltip`) VALUES
(1, 1, 'Digital Literacy', 'fa-desktop', 'Basic to advanced knowledge of computer use and digital tools'),
(2, 1, 'Data Entry', 'fa-keyboard', 'Fast and accurate input of data into computer systems'),
(3, 1, 'Microsoft Office', 'fa-file-word', 'Proficiency with Word, Excel, PowerPoint and other Office applications'),
(4, 1, 'Data Analysis', 'fa-chart-bar', 'Ability to interpret data and generate insights'),
(5, 1, 'Basic Coding', 'fa-code', 'Fundamental programming knowledge in any language'),
(6, 1, 'Cybersecurity Awareness', 'fa-shield-alt', 'Understanding of digital security practices'),
(7, 1, 'Social Media Management', 'fa-hashtag', 'Skills in managing social media accounts and content'),
(8, 1, 'Basic AI Understanding', 'fa-robot', 'Familiarity with AI concepts and tools'),
(9, 2, 'Call Handling', 'fa-phone-alt', 'Managing customer calls efficiently and professionally'),
(10, 2, 'Client Communication', 'fa-comments', 'Clear and effective communication with clients'),
(11, 2, 'Problem Resolution', 'fa-tools', 'Ability to solve customer issues effectively'),
(12, 2, 'Product Knowledge', 'fa-box-open', 'Understanding of products or services offered'),
(13, 2, 'Complaint Management', 'fa-hand-peace', 'Handling customer complaints professionally'),
(14, 2, 'Email Communication', 'fa-envelope', 'Professional email correspondence skills'),
(15, 2, 'Virtual Assistant Skills', 'fa-user-tie', 'Providing remote administrative support'),
(16, 3, 'Document Management', 'fa-file-alt', 'Organizing and maintaining document systems'),
(17, 3, 'Filing and Record Keeping', 'fa-folder', 'Maintaining organized records and files'),
(18, 3, 'Scheduling', 'fa-calendar', 'Managing appointments and calendars'),
(19, 3, 'Transcription', 'fa-headphones', 'Converting audio to written text accurately'),
(20, 3, 'Proofreading', 'fa-spell-check', 'Checking documents for errors and clarity'),
(21, 3, 'Office Equipment Operation', 'fa-print', 'Using office machines like printers and scanners'),
(22, 3, 'Basic Bookkeeping', 'fa-book', 'Fundamental record-keeping of financial transactions');

-- --------------------------------------------------------

--
-- Table structure for table `skill_categories`
--

CREATE TABLE `skill_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `category_icon` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `skill_categories`
--

INSERT INTO `skill_categories` (`category_id`, `category_name`, `category_icon`) VALUES
(1, 'Digital and Technical Skills', 'fa-laptop-code'),
(2, 'Customer Service Skills', 'fa-headset'),
(3, 'Administrative and Clerical Skills', 'fa-tasks'),
(4, 'Accounting and Financial Skills', 'fa-calculator'),
(5, 'BPO-Specific Skills', 'fa-building'),
(6, 'Manufacturing Skills', 'fa-industry'),
(7, 'Disability-Specific Strengths', 'fa-star'),
(8, 'Soft Skills and Work Attributes', 'fa-users');

-- --------------------------------------------------------

--
-- Table structure for table `user_accounts`
--

CREATE TABLE `user_accounts` (
  `account_id` int(11) NOT NULL,
  `seeker_id` int(11) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `google_account` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_accounts`
--

INSERT INTO `user_accounts` (`account_id`, `seeker_id`, `email`, `password_hash`, `google_account`) VALUES
(4, 4, 'baccayshan@gmail.com', '$2y$10$jDA4X6MgFP1qS9VAerJpoOhQDCiyHVnkl8Znclb0GncInW03nFoBS', 1),
(5, 5, 'applicant1@email.com', '$2y$10$JKZ.IEyqzAcFqqiZLBGgGeNaa21Zx1L0DmPlnvNeN0fWkS2nUHUD2', 0),
(6, 6, 'applicant2@email.com', '$2y$10$8fPKvXBwgNwhrS62b0cEO.fQd2lvXJ6QjlPdsGZ/ttQuziURR8WUG', 0),
(7, 7, 'jpaira@email.com', '$2y$10$ZE.FfuCn2HpeqFVNZR0wlOZw9PjFuSHZdXxw8CwABxhXB9Jf8gE5i', 0),
(8, 8, 'ebaguhin@email.com', '$2y$10$kwirzXb5V7EjKPi7fQiCE.6tx2x3VfH061esjhCjmi/h76TM8OQ7u', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

CREATE TABLE `user_preferences` (
  `preference_id` int(11) NOT NULL,
  `seeker_id` int(11) NOT NULL,
  `work_style` enum('remote','hybrid','onsite') DEFAULT NULL,
  `job_type` enum('freelance','parttime','fulltime') DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `salary_range` varchar(50) DEFAULT NULL,
  `availability` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_preferences`
--

INSERT INTO `user_preferences` (`preference_id`, `seeker_id`, `work_style`, `job_type`, `updated_at`, `salary_range`, `availability`) VALUES
(4, 4, 'remote', 'freelance', '2025-07-19 07:14:40', 'Below ₱20,000', 'Immediate'),
(5, 5, 'remote', 'fulltime', '2025-06-29 22:15:25', NULL, NULL),
(6, 6, 'remote', 'fulltime', '2025-06-29 22:18:07', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_settings`
--

CREATE TABLE `user_settings` (
  `setting_id` int(11) NOT NULL,
  `seeker_id` int(11) NOT NULL,
  `theme` enum('light','dark','system') DEFAULT 'light',
  `font_size` enum('small','medium','large') DEFAULT 'medium',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `workplace_accommodations`
--

CREATE TABLE `workplace_accommodations` (
  `accommodation_id` int(11) NOT NULL,
  `seeker_id` int(11) NOT NULL,
  `disability_type` enum('apparent','non-apparent') NOT NULL,
  `accommodation_list` text NOT NULL,
  `no_accommodations_needed` tinyint(1) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `workplace_accommodations`
--

INSERT INTO `workplace_accommodations` (`accommodation_id`, `seeker_id`, `disability_type`, `accommodation_list`, `no_accommodations_needed`, `updated_at`) VALUES
(4, 4, 'non-apparent', '[]', 1, '2025-07-11 01:40:44'),
(5, 5, 'apparent', '[]', 1, '2025-06-29 22:15:25'),
(6, 6, 'apparent', '[\"Lighting Adjustments\"]', 0, '2025-06-29 22:18:07');

-- --------------------------------------------------------

--
-- Structure for view `applicant_overview`
--
DROP TABLE IF EXISTS `applicant_overview`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `applicant_overview`  AS SELECT `ja`.`application_id` AS `application_id`, `ja`.`job_id` AS `job_id`, `ja`.`seeker_id` AS `seeker_id`, `ja`.`application_status` AS `application_status`, `ja`.`applied_at` AS `applied_at`, `ja`.`cover_letter` AS `cover_letter`, `ja`.`employer_notes` AS `employer_notes`, `ja`.`last_activity` AS `last_activity`, `ja`.`resume_id` AS `resume_id`, `jp`.`job_title` AS `job_title`, `jp`.`employer_id` AS `employer_id`, `jp`.`employment_type` AS `employment_type`, `jp`.`location` AS `job_location`, `jp`.`salary_range` AS `salary_range`, `js`.`first_name` AS `first_name`, `js`.`last_name` AS `last_name`, `js`.`contact_number` AS `contact_number`, `js`.`city` AS `city`, `js`.`province` AS `province`, `js`.`disability_id` AS `disability_id`, `dt`.`disability_name` AS `disability_name`, `dc`.`category_name` AS `disability_category`, `pd`.`headline` AS `headline`, `pd`.`bio` AS `bio`, `pd`.`profile_photo_path` AS `profile_photo_path`, `pd`.`location` AS `preferred_location`, `r`.`file_name` AS `resume_filename`, `r`.`file_path` AS `resume_path`, `r`.`file_type` AS `resume_type`, `r`.`upload_date` AS `resume_upload_date`, `ua`.`email` AS `email` FROM (((((((`job_applications` `ja` join `job_posts` `jp` on(`ja`.`job_id` = `jp`.`job_id`)) join `job_seekers` `js` on(`ja`.`seeker_id` = `js`.`seeker_id`)) join `disability_types` `dt` on(`js`.`disability_id` = `dt`.`disability_id`)) join `disability_categories` `dc` on(`dt`.`category_id` = `dc`.`category_id`)) left join `profile_details` `pd` on(`js`.`seeker_id` = `pd`.`seeker_id`)) left join `resumes` `r` on(`ja`.`resume_id` = `r`.`resume_id` and `r`.`is_current` = 1)) left join `user_accounts` `ua` on(`js`.`seeker_id` = `ua`.`seeker_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `employer_applicant_stats`
--
DROP TABLE IF EXISTS `employer_applicant_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `employer_applicant_stats`  AS SELECT `jp`.`employer_id` AS `employer_id`, count(`ja`.`application_id`) AS `total_applications`, sum(case when `ja`.`application_status` = 'submitted' then 1 else 0 end) AS `new_applications`, sum(case when `ja`.`application_status` = 'under_review' then 1 else 0 end) AS `under_review`, sum(case when `ja`.`application_status` = 'shortlisted' then 1 else 0 end) AS `shortlisted`, sum(case when `ja`.`application_status` = 'interview_scheduled' then 1 else 0 end) AS `interviews_scheduled`, sum(case when `ja`.`application_status` = 'interviewed' then 1 else 0 end) AS `interviewed`, sum(case when `ja`.`application_status` = 'hired' then 1 else 0 end) AS `hired`, sum(case when `ja`.`application_status` = 'rejected' then 1 else 0 end) AS `rejected`, count(distinct `ja`.`job_id`) AS `jobs_with_applications`, max(`ja`.`applied_at`) AS `latest_application` FROM (`job_posts` `jp` left join `job_applications` `ja` on(`jp`.`job_id` = `ja`.`job_id`)) GROUP BY `jp`.`employer_id` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accessibility_settings`
--
ALTER TABLE `accessibility_settings`
  ADD PRIMARY KEY (`accessibility_id`),
  ADD UNIQUE KEY `seeker_id` (`seeker_id`);

--
-- Indexes for table `api_tokens`
--
ALTER TABLE `api_tokens`
  ADD PRIMARY KEY (`token_id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `expires_at` (`expires_at`);

--
-- Indexes for table `application_settings`
--
ALTER TABLE `application_settings`
  ADD PRIMARY KEY (`app_setting_id`),
  ADD UNIQUE KEY `seeker_id` (`seeker_id`);

--
-- Indexes for table `application_status_history`
--
ALTER TABLE `application_status_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `idx_application_id` (`application_id`),
  ADD KEY `idx_changed_at` (`changed_at`);

--
-- Indexes for table `candidate_documents`
--
ALTER TABLE `candidate_documents`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `idx_seeker_documents` (`seeker_id`),
  ADD KEY `idx_document_type` (`document_type`);

--
-- Indexes for table `company_values`
--
ALTER TABLE `company_values`
  ADD PRIMARY KEY (`value_id`),
  ADD KEY `employer_id` (`employer_id`),
  ADD KEY `display_order` (`display_order`);

--
-- Indexes for table `connection_test`
--
ALTER TABLE `connection_test`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `disability_categories`
--
ALTER TABLE `disability_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `disability_types`
--
ALTER TABLE `disability_types`
  ADD PRIMARY KEY (`disability_id`),
  ADD UNIQUE KEY `disability_name` (`disability_name`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `document_categories`
--
ALTER TABLE `document_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD KEY `idx_category_type` (`category_type`);

--
-- Indexes for table `education`
--
ALTER TABLE `education`
  ADD PRIMARY KEY (`education_id`),
  ADD KEY `idx_education_seeker_id` (`seeker_id`);

--
-- Indexes for table `employers`
--
ALTER TABLE `employers`
  ADD PRIMARY KEY (`employer_id`),
  ADD KEY `idx_company_name` (`company_name`),
  ADD KEY `idx_industry` (`industry`),
  ADD KEY `idx_verification_status` (`verification_status`),
  ADD KEY `industry_id` (`industry_id`);

--
-- Indexes for table `employer_accounts`
--
ALTER TABLE `employer_accounts`
  ADD PRIMARY KEY (`account_id`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD UNIQUE KEY `unique_employer_contact` (`employer_id`,`contact_id`),
  ADD KEY `contact_id` (`contact_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_verification_token` (`email_verification_token`),
  ADD KEY `idx_reset_token` (`reset_token`);

--
-- Indexes for table `employer_contacts`
--
ALTER TABLE `employer_contacts`
  ADD PRIMARY KEY (`contact_id`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_employer_id` (`employer_id`);

--
-- Indexes for table `employer_display_settings`
--
ALTER TABLE `employer_display_settings`
  ADD PRIMARY KEY (`display_id`),
  ADD UNIQUE KEY `unique_employer` (`employer_id`);

--
-- Indexes for table `employer_hiring_preferences`
--
ALTER TABLE `employer_hiring_preferences`
  ADD PRIMARY KEY (`preference_id`),
  ADD UNIQUE KEY `unique_employer` (`employer_id`);

--
-- Indexes for table `employer_notification_settings`
--
ALTER TABLE `employer_notification_settings`
  ADD PRIMARY KEY (`notification_id`),
  ADD UNIQUE KEY `unique_employer` (`employer_id`);

--
-- Indexes for table `employer_privacy_settings`
--
ALTER TABLE `employer_privacy_settings`
  ADD PRIMARY KEY (`privacy_id`),
  ADD UNIQUE KEY `unique_employer` (`employer_id`);

--
-- Indexes for table `employer_setup_progress`
--
ALTER TABLE `employer_setup_progress`
  ADD PRIMARY KEY (`progress_id`),
  ADD UNIQUE KEY `unique_employer` (`employer_id`);

--
-- Indexes for table `employer_social_links`
--
ALTER TABLE `employer_social_links`
  ADD PRIMARY KEY (`social_id`),
  ADD UNIQUE KEY `unique_employer` (`employer_id`);

--
-- Indexes for table `experience`
--
ALTER TABLE `experience`
  ADD PRIMARY KEY (`experience_id`),
  ADD KEY `idx_experience_seeker_id` (`seeker_id`);

--
-- Indexes for table `feedback_templates`
--
ALTER TABLE `feedback_templates`
  ADD PRIMARY KEY (`template_id`),
  ADD KEY `employer_id` (`employer_id`),
  ADD KEY `template_type` (`template_type`);

--
-- Indexes for table `industries`
--
ALTER TABLE `industries`
  ADD PRIMARY KEY (`industry_id`);

--
-- Indexes for table `interviews`
--
ALTER TABLE `interviews`
  ADD PRIMARY KEY (`interview_id`),
  ADD KEY `idx_application_id` (`application_id`),
  ADD KEY `idx_scheduled_date` (`scheduled_date`),
  ADD KEY `idx_interview_status` (`interview_status`),
  ADD KEY `idx_interviews_date_status` (`scheduled_date`,`interview_status`),
  ADD KEY `idx_interviews_application_id` (`application_id`),
  ADD KEY `idx_interviews_scheduled_date` (`scheduled_date`),
  ADD KEY `idx_interviews_status` (`interview_status`);

--
-- Indexes for table `interview_feedback`
--
ALTER TABLE `interview_feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD UNIQUE KEY `unique_interview_feedback` (`interview_id`);

--
-- Indexes for table `job_accommodations`
--
ALTER TABLE `job_accommodations`
  ADD PRIMARY KEY (`accommodation_id`),
  ADD UNIQUE KEY `unique_job_accommodation` (`job_id`);

--
-- Indexes for table `job_alert_settings`
--
ALTER TABLE `job_alert_settings`
  ADD PRIMARY KEY (`alert_id`),
  ADD UNIQUE KEY `seeker_id` (`seeker_id`);

--
-- Indexes for table `job_analytics`
--
ALTER TABLE `job_analytics`
  ADD PRIMARY KEY (`analytics_id`),
  ADD UNIQUE KEY `unique_job_date` (`job_id`,`date_recorded`),
  ADD KEY `idx_date_recorded` (`date_recorded`);

--
-- Indexes for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD PRIMARY KEY (`application_id`),
  ADD UNIQUE KEY `unique_job_seeker_application` (`job_id`,`seeker_id`),
  ADD KEY `idx_job_id` (`job_id`),
  ADD KEY `idx_seeker_id` (`seeker_id`),
  ADD KEY `idx_application_status` (`application_status`),
  ADD KEY `idx_applied_at` (`applied_at`),
  ADD KEY `resume_id` (`resume_id`),
  ADD KEY `idx_applications_job_status` (`job_id`,`application_status`),
  ADD KEY `idx_applications_employer_date` (`applied_at`),
  ADD KEY `idx_applications_status_date` (`application_status`,`applied_at`),
  ADD KEY `idx_job_applications_job_id` (`job_id`),
  ADD KEY `idx_job_applications_seeker_id` (`seeker_id`),
  ADD KEY `idx_match_score` (`match_score`);

--
-- Indexes for table `job_posts`
--
ALTER TABLE `job_posts`
  ADD PRIMARY KEY (`job_id`),
  ADD KEY `idx_employer_id` (`employer_id`),
  ADD KEY `idx_job_status` (`job_status`),
  ADD KEY `idx_employment_type` (`employment_type`),
  ADD KEY `idx_posted_at` (`posted_at`),
  ADD KEY `idx_job_posts_employer_status` (`employer_id`,`job_status`),
  ADD KEY `idx_job_requirements` (`requires_degree`,`requires_certification`,`requires_license`),
  ADD KEY `idx_job_experience` (`min_experience_years`);

--
-- Indexes for table `job_requirements`
--
ALTER TABLE `job_requirements`
  ADD PRIMARY KEY (`requirement_id`),
  ADD UNIQUE KEY `unique_job_skill` (`job_id`,`skill_id`),
  ADD KEY `idx_skill_id` (`skill_id`),
  ADD KEY `idx_job_skills` (`job_id`,`skill_id`);

--
-- Indexes for table `job_seekers`
--
ALTER TABLE `job_seekers`
  ADD PRIMARY KEY (`seeker_id`),
  ADD KEY `disability_id` (`disability_id`);

--
-- Indexes for table `job_views`
--
ALTER TABLE `job_views`
  ADD PRIMARY KEY (`view_id`),
  ADD KEY `idx_job_id` (`job_id`),
  ADD KEY `idx_seeker_id` (`seeker_id`),
  ADD KEY `idx_viewed_at` (`viewed_at`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_recipient` (`recipient_type`,`recipient_id`),
  ADD KEY `idx_type_id` (`type_id`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_related_job` (`related_job_id`),
  ADD KEY `idx_related_application` (`related_application_id`),
  ADD KEY `idx_related_interview` (`related_interview_id`),
  ADD KEY `idx_notifications_recipient_read` (`recipient_type`,`recipient_id`,`is_read`);

--
-- Indexes for table `notification_settings`
--
ALTER TABLE `notification_settings`
  ADD PRIMARY KEY (`notification_id`),
  ADD UNIQUE KEY `seeker_id` (`seeker_id`);

--
-- Indexes for table `notification_types`
--
ALTER TABLE `notification_types`
  ADD PRIMARY KEY (`type_id`),
  ADD UNIQUE KEY `unique_type_name` (`type_name`);

--
-- Indexes for table `privacy_settings`
--
ALTER TABLE `privacy_settings`
  ADD PRIMARY KEY (`privacy_id`),
  ADD UNIQUE KEY `seeker_id` (`seeker_id`);

--
-- Indexes for table `profile_details`
--
ALTER TABLE `profile_details`
  ADD PRIMARY KEY (`profile_id`),
  ADD UNIQUE KEY `seeker_id` (`seeker_id`),
  ADD KEY `idx_profile_details_seeker_id` (`seeker_id`);

--
-- Indexes for table `pwd_ids`
--
ALTER TABLE `pwd_ids`
  ADD PRIMARY KEY (`pwd_id`),
  ADD UNIQUE KEY `seeker_id` (`seeker_id`),
  ADD UNIQUE KEY `pwd_id_number` (`pwd_id_number`);

--
-- Indexes for table `rejection_feedback`
--
ALTER TABLE `rejection_feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `idx_application_feedback` (`application_id`),
  ADD KEY `idx_employer_feedback` (`employer_id`);

--
-- Indexes for table `resumes`
--
ALTER TABLE `resumes`
  ADD PRIMARY KEY (`resume_id`),
  ADD KEY `idx_resumes_seeker_id` (`seeker_id`);

--
-- Indexes for table `saved_jobs`
--
ALTER TABLE `saved_jobs`
  ADD PRIMARY KEY (`saved_id`),
  ADD UNIQUE KEY `unique_seeker_job_save` (`seeker_id`,`job_id`),
  ADD KEY `idx_seeker_id` (`seeker_id`),
  ADD KEY `idx_job_id` (`job_id`);

--
-- Indexes for table `seeker_skills`
--
ALTER TABLE `seeker_skills`
  ADD PRIMARY KEY (`seeker_skill_id`),
  ADD UNIQUE KEY `seeker_id` (`seeker_id`,`skill_id`),
  ADD KEY `skill_id` (`skill_id`),
  ADD KEY `idx_seeker_skills_seeker_id` (`seeker_id`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`skill_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `skill_categories`
--
ALTER TABLE `skill_categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `user_accounts`
--
ALTER TABLE `user_accounts`
  ADD PRIMARY KEY (`account_id`),
  ADD UNIQUE KEY `seeker_id` (`seeker_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD PRIMARY KEY (`preference_id`),
  ADD UNIQUE KEY `seeker_id` (`seeker_id`);

--
-- Indexes for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `seeker_id` (`seeker_id`);

--
-- Indexes for table `workplace_accommodations`
--
ALTER TABLE `workplace_accommodations`
  ADD PRIMARY KEY (`accommodation_id`),
  ADD UNIQUE KEY `seeker_id` (`seeker_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accessibility_settings`
--
ALTER TABLE `accessibility_settings`
  MODIFY `accessibility_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `api_tokens`
--
ALTER TABLE `api_tokens`
  MODIFY `token_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=206;

--
-- AUTO_INCREMENT for table `application_settings`
--
ALTER TABLE `application_settings`
  MODIFY `app_setting_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `application_status_history`
--
ALTER TABLE `application_status_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `candidate_documents`
--
ALTER TABLE `candidate_documents`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `company_values`
--
ALTER TABLE `company_values`
  MODIFY `value_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `connection_test`
--
ALTER TABLE `connection_test`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `disability_categories`
--
ALTER TABLE `disability_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `disability_types`
--
ALTER TABLE `disability_types`
  MODIFY `disability_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `document_categories`
--
ALTER TABLE `document_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `education`
--
ALTER TABLE `education`
  MODIFY `education_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `employers`
--
ALTER TABLE `employers`
  MODIFY `employer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `employer_accounts`
--
ALTER TABLE `employer_accounts`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `employer_contacts`
--
ALTER TABLE `employer_contacts`
  MODIFY `contact_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `employer_display_settings`
--
ALTER TABLE `employer_display_settings`
  MODIFY `display_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employer_hiring_preferences`
--
ALTER TABLE `employer_hiring_preferences`
  MODIFY `preference_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `employer_notification_settings`
--
ALTER TABLE `employer_notification_settings`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employer_privacy_settings`
--
ALTER TABLE `employer_privacy_settings`
  MODIFY `privacy_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employer_setup_progress`
--
ALTER TABLE `employer_setup_progress`
  MODIFY `progress_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `employer_social_links`
--
ALTER TABLE `employer_social_links`
  MODIFY `social_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `experience`
--
ALTER TABLE `experience`
  MODIFY `experience_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `feedback_templates`
--
ALTER TABLE `feedback_templates`
  MODIFY `template_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `industries`
--
ALTER TABLE `industries`
  MODIFY `industry_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `interviews`
--
ALTER TABLE `interviews`
  MODIFY `interview_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `interview_feedback`
--
ALTER TABLE `interview_feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_accommodations`
--
ALTER TABLE `job_accommodations`
  MODIFY `accommodation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `job_alert_settings`
--
ALTER TABLE `job_alert_settings`
  MODIFY `alert_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_analytics`
--
ALTER TABLE `job_analytics`
  MODIFY `analytics_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_applications`
--
ALTER TABLE `job_applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `job_posts`
--
ALTER TABLE `job_posts`
  MODIFY `job_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `job_requirements`
--
ALTER TABLE `job_requirements`
  MODIFY `requirement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `job_seekers`
--
ALTER TABLE `job_seekers`
  MODIFY `seeker_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `job_views`
--
ALTER TABLE `job_views`
  MODIFY `view_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `notification_settings`
--
ALTER TABLE `notification_settings`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_types`
--
ALTER TABLE `notification_types`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `privacy_settings`
--
ALTER TABLE `privacy_settings`
  MODIFY `privacy_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `profile_details`
--
ALTER TABLE `profile_details`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pwd_ids`
--
ALTER TABLE `pwd_ids`
  MODIFY `pwd_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `rejection_feedback`
--
ALTER TABLE `rejection_feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resumes`
--
ALTER TABLE `resumes`
  MODIFY `resume_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `saved_jobs`
--
ALTER TABLE `saved_jobs`
  MODIFY `saved_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `seeker_skills`
--
ALTER TABLE `seeker_skills`
  MODIFY `seeker_skill_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `skill_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `skill_categories`
--
ALTER TABLE `skill_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_accounts`
--
ALTER TABLE `user_accounts`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_preferences`
--
ALTER TABLE `user_preferences`
  MODIFY `preference_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_settings`
--
ALTER TABLE `user_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `workplace_accommodations`
--
ALTER TABLE `workplace_accommodations`
  MODIFY `accommodation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accessibility_settings`
--
ALTER TABLE `accessibility_settings`
  ADD CONSTRAINT `accessibility_settings_ibfk_1` FOREIGN KEY (`seeker_id`) REFERENCES `job_seekers` (`seeker_id`) ON DELETE CASCADE;

--
-- Constraints for table `application_settings`
--
ALTER TABLE `application_settings`
  ADD CONSTRAINT `application_settings_ibfk_1` FOREIGN KEY (`seeker_id`) REFERENCES `job_seekers` (`seeker_id`) ON DELETE CASCADE;

--
-- Constraints for table `application_status_history`
--
ALTER TABLE `application_status_history`
  ADD CONSTRAINT `application_status_history_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `job_applications` (`application_id`) ON DELETE CASCADE;

--
-- Constraints for table `candidate_documents`
--
ALTER TABLE `candidate_documents`
  ADD CONSTRAINT `fk_documents_seeker` FOREIGN KEY (`seeker_id`) REFERENCES `job_seekers` (`seeker_id`) ON DELETE CASCADE;

--
-- Constraints for table `company_values`
--
ALTER TABLE `company_values`
  ADD CONSTRAINT `company_values_ibfk_1` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`employer_id`) ON DELETE CASCADE;

--
-- Constraints for table `disability_types`
--
ALTER TABLE `disability_types`
  ADD CONSTRAINT `disability_types_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `disability_categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `education`
--
ALTER TABLE `education`
  ADD CONSTRAINT `education_ibfk_1` FOREIGN KEY (`seeker_id`) REFERENCES `job_seekers` (`seeker_id`) ON DELETE CASCADE;

--
-- Constraints for table `employers`
--
ALTER TABLE `employers`
  ADD CONSTRAINT `employers_ibfk_1` FOREIGN KEY (`industry_id`) REFERENCES `industries` (`industry_id`);

--
-- Constraints for table `employer_accounts`
--
ALTER TABLE `employer_accounts`
  ADD CONSTRAINT `employer_accounts_ibfk_1` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`employer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employer_accounts_ibfk_2` FOREIGN KEY (`contact_id`) REFERENCES `employer_contacts` (`contact_id`) ON DELETE CASCADE;

--
-- Constraints for table `employer_contacts`
--
ALTER TABLE `employer_contacts`
  ADD CONSTRAINT `employer_contacts_ibfk_1` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`employer_id`) ON DELETE CASCADE;

--
-- Constraints for table `employer_display_settings`
--
ALTER TABLE `employer_display_settings`
  ADD CONSTRAINT `employer_display_settings_ibfk_1` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`employer_id`) ON DELETE CASCADE;

--
-- Constraints for table `employer_hiring_preferences`
--
ALTER TABLE `employer_hiring_preferences`
  ADD CONSTRAINT `employer_hiring_preferences_ibfk_1` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`employer_id`) ON DELETE CASCADE;

--
-- Constraints for table `employer_notification_settings`
--
ALTER TABLE `employer_notification_settings`
  ADD CONSTRAINT `employer_notification_settings_ibfk_1` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`employer_id`) ON DELETE CASCADE;

--
-- Constraints for table `employer_privacy_settings`
--
ALTER TABLE `employer_privacy_settings`
  ADD CONSTRAINT `employer_privacy_settings_ibfk_1` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`employer_id`) ON DELETE CASCADE;

--
-- Constraints for table `employer_setup_progress`
--
ALTER TABLE `employer_setup_progress`
  ADD CONSTRAINT `employer_setup_progress_ibfk_1` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`employer_id`) ON DELETE CASCADE;

--
-- Constraints for table `employer_social_links`
--
ALTER TABLE `employer_social_links`
  ADD CONSTRAINT `employer_social_links_ibfk_1` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`employer_id`) ON DELETE CASCADE;

--
-- Constraints for table `experience`
--
ALTER TABLE `experience`
  ADD CONSTRAINT `experience_ibfk_1` FOREIGN KEY (`seeker_id`) REFERENCES `job_seekers` (`seeker_id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback_templates`
--
ALTER TABLE `feedback_templates`
  ADD CONSTRAINT `feedback_templates_ibfk_1` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`employer_id`) ON DELETE CASCADE;

--
-- Constraints for table `interviews`
--
ALTER TABLE `interviews`
  ADD CONSTRAINT `interviews_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `job_applications` (`application_id`) ON DELETE CASCADE;

--
-- Constraints for table `interview_feedback`
--
ALTER TABLE `interview_feedback`
  ADD CONSTRAINT `interview_feedback_ibfk_1` FOREIGN KEY (`interview_id`) REFERENCES `interviews` (`interview_id`) ON DELETE CASCADE;

--
-- Constraints for table `job_accommodations`
--
ALTER TABLE `job_accommodations`
  ADD CONSTRAINT `job_accommodations_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `job_posts` (`job_id`) ON DELETE CASCADE;

--
-- Constraints for table `job_alert_settings`
--
ALTER TABLE `job_alert_settings`
  ADD CONSTRAINT `job_alert_settings_ibfk_1` FOREIGN KEY (`seeker_id`) REFERENCES `job_seekers` (`seeker_id`) ON DELETE CASCADE;

--
-- Constraints for table `job_analytics`
--
ALTER TABLE `job_analytics`
  ADD CONSTRAINT `job_analytics_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `job_posts` (`job_id`) ON DELETE CASCADE;

--
-- Constraints for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD CONSTRAINT `job_applications_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `job_posts` (`job_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_applications_ibfk_2` FOREIGN KEY (`seeker_id`) REFERENCES `job_seekers` (`seeker_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_applications_ibfk_3` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`resume_id`) ON DELETE SET NULL;

--
-- Constraints for table `job_posts`
--
ALTER TABLE `job_posts`
  ADD CONSTRAINT `job_posts_ibfk_1` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`employer_id`) ON DELETE CASCADE;

--
-- Constraints for table `job_requirements`
--
ALTER TABLE `job_requirements`
  ADD CONSTRAINT `job_requirements_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `job_posts` (`job_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_requirements_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`skill_id`) ON DELETE CASCADE;

--
-- Constraints for table `job_seekers`
--
ALTER TABLE `job_seekers`
  ADD CONSTRAINT `job_seekers_ibfk_1` FOREIGN KEY (`disability_id`) REFERENCES `disability_types` (`disability_id`) ON DELETE CASCADE;

--
-- Constraints for table `job_views`
--
ALTER TABLE `job_views`
  ADD CONSTRAINT `job_views_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `job_posts` (`job_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_views_ibfk_2` FOREIGN KEY (`seeker_id`) REFERENCES `job_seekers` (`seeker_id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `notification_types` (`type_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`related_job_id`) REFERENCES `job_posts` (`job_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_3` FOREIGN KEY (`related_application_id`) REFERENCES `job_applications` (`application_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_4` FOREIGN KEY (`related_interview_id`) REFERENCES `interviews` (`interview_id`) ON DELETE CASCADE;

--
-- Constraints for table `notification_settings`
--
ALTER TABLE `notification_settings`
  ADD CONSTRAINT `notification_settings_ibfk_1` FOREIGN KEY (`seeker_id`) REFERENCES `job_seekers` (`seeker_id`) ON DELETE CASCADE;

--
-- Constraints for table `privacy_settings`
--
ALTER TABLE `privacy_settings`
  ADD CONSTRAINT `privacy_settings_ibfk_1` FOREIGN KEY (`seeker_id`) REFERENCES `job_seekers` (`seeker_id`) ON DELETE CASCADE;

--
-- Constraints for table `profile_details`
--
ALTER TABLE `profile_details`
  ADD CONSTRAINT `profile_details_ibfk_1` FOREIGN KEY (`seeker_id`) REFERENCES `job_seekers` (`seeker_id`) ON DELETE CASCADE;

--
-- Constraints for table `pwd_ids`
--
ALTER TABLE `pwd_ids`
  ADD CONSTRAINT `pwd_ids_ibfk_1` FOREIGN KEY (`seeker_id`) REFERENCES `job_seekers` (`seeker_id`) ON DELETE CASCADE;

--
-- Constraints for table `rejection_feedback`
--
ALTER TABLE `rejection_feedback`
  ADD CONSTRAINT `fk_feedback_application` FOREIGN KEY (`application_id`) REFERENCES `job_applications` (`application_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_feedback_employer` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`employer_id`) ON DELETE CASCADE;

--
-- Constraints for table `resumes`
--
ALTER TABLE `resumes`
  ADD CONSTRAINT `resumes_ibfk_1` FOREIGN KEY (`seeker_id`) REFERENCES `job_seekers` (`seeker_id`) ON DELETE CASCADE;

--
-- Constraints for table `saved_jobs`
--
ALTER TABLE `saved_jobs`
  ADD CONSTRAINT `saved_jobs_ibfk_1` FOREIGN KEY (`seeker_id`) REFERENCES `job_seekers` (`seeker_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `saved_jobs_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `job_posts` (`job_id`) ON DELETE CASCADE;

--
-- Constraints for table `seeker_skills`
--
ALTER TABLE `seeker_skills`
  ADD CONSTRAINT `seeker_skills_ibfk_1` FOREIGN KEY (`seeker_id`) REFERENCES `job_seekers` (`seeker_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `seeker_skills_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`skill_id`) ON DELETE CASCADE;

--
-- Constraints for table `skills`
--
ALTER TABLE `skills`
  ADD CONSTRAINT `skills_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `skill_categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_accounts`
--
ALTER TABLE `user_accounts`
  ADD CONSTRAINT `user_accounts_ibfk_1` FOREIGN KEY (`seeker_id`) REFERENCES `job_seekers` (`seeker_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD CONSTRAINT `user_preferences_ibfk_1` FOREIGN KEY (`seeker_id`) REFERENCES `job_seekers` (`seeker_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD CONSTRAINT `user_settings_ibfk_1` FOREIGN KEY (`seeker_id`) REFERENCES `job_seekers` (`seeker_id`) ON DELETE CASCADE;

--
-- Constraints for table `workplace_accommodations`
--
ALTER TABLE `workplace_accommodations`
  ADD CONSTRAINT `workplace_accommodations_ibfk_1` FOREIGN KEY (`seeker_id`) REFERENCES `job_seekers` (`seeker_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
