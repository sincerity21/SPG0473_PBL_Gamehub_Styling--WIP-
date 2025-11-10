-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 07, 2025 at 09:41 AM
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
-- Database: `gamehub`
--

-- --------------------------------------------------------

--
-- Table structure for table `favourites`
--

CREATE TABLE `favourites` (
  `favourite_id` int(11) NOT NULL,
  `favourite_game` tinyint(1) NOT NULL DEFAULT 0,
  `game_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favourites`
--

INSERT INTO `favourites` (`favourite_id`, `favourite_game`, `game_id`, `user_id`) VALUES
(1, 1, 7, 3),
(4, 1, 38, 3),
(5, 1, 14, 3),
(6, 1, 29, 3);

-- --------------------------------------------------------

--
-- Table structure for table `feedback_game`
--

CREATE TABLE `feedback_game` (
  `feedback_game_id` int(11) NOT NULL,
  `feedback_game_frequency` varchar(255) DEFAULT NULL,
  `feedback_game_open` text NOT NULL,
  `game_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback_game`
--

INSERT INTO `feedback_game` (`feedback_game_id`, `feedback_game_frequency`, `feedback_game_open`, `game_id`, `user_id`) VALUES
(4, 'frequency_0', 'Incredibly fun. A breath of fresh air (ironically) compared to 2042. The game, in a way, feels similar to older Battlefield titles like BF4 and BF3, in a good way. It has some QoL here and there, and unfortunately some bugs, but I believe they\'ll be fixed soon. \r\n\r\nThe best part about this game, is that I can feel the passion the devs put into this title. After 2042\'s...disastrous launch, this game had an incredibly smooth launch, and is possibly the most optimized triple-A title of 2025, which is impressive, even more so for EA.\r\n\r\n\r\nNonetheless, if you like Battlefield, you need to try this.', 7, 3),
(6, 'frequency_0', 'Quite a bad launch for the game, and yet, as years gone by, the game kept receiving updates, that optimized it a bit more, fixed more bugs, and people realized under all the mess, is such a beautiful, highly-detailed world, with a great RPG story and captivating characters. \r\n\r\nI honestly prefer this over GTAV. It just feels...more immersive. Play this game, you need to.', 14, 3),
(7, 'frequency_0', 'I\'ve never thought driving trucks can be fun, yet here we are. A surprisingly fun game about driving, well, trucks, in a miniaturized, scaled-down Europe, that still feels massive on its own, excluding various map mods one can download for the game. \r\n\r\nLive out your trucker dreams.', 29, 3);

-- --------------------------------------------------------

--
-- Table structure for table `feedback_site`
--

CREATE TABLE `feedback_site` (
  `feedback_site_id` int(11) NOT NULL,
  `feedback_site_satisfaction` varchar(255) DEFAULT NULL,
  `feedback_site_open` text NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback_site`
--

INSERT INTO `feedback_site` (`feedback_site_id`, `feedback_site_satisfaction`, `feedback_site_open`, `user_id`) VALUES
(1, 'satisfaction_3', 'Maybe decorate it a bit more? Functionality-wise, it\'s impressive, but the background is quite bland. ', 3);

-- --------------------------------------------------------

--
-- Table structure for table `games`
--

CREATE TABLE `games` (
  `game_id` int(11) NOT NULL,
  `game_category` varchar(50) NOT NULL,
  `game_name` varchar(255) NOT NULL,
  `game_desc` varchar(10000) DEFAULT NULL,
  `game_img` varchar(1024) DEFAULT NULL,
  `game_trailerLink` varchar(512) DEFAULT NULL,
  `game_Link` varchar(512) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `games`
--

INSERT INTO `games` (`game_id`, `game_category`, `game_name`, `game_desc`, `game_img`, `game_trailerLink`, `game_Link`) VALUES
(7, 'fps', 'Battlefield 6', 'A return to form. After EA\'s worst Battlefield title in BF2042 released a few years ago during COVID, they\'ve made the latest Battlefield to be one of the safest Battlefields, barely any innovation in-terms of gameplay. And players love it.', 'uploads/images/game_img_690a47f9bb7277.63178711.jpg', 'https://www.youtube.com/watch?v=wFGEMfyAQtI', 'https://store.steampowered.com/app/2807960/Battlefield_6/'),
(8, 'fps', 'Counter-Strike 2', 'The legendary Counter-Strike: Global Offensive, now upgraded into a new engine. With better visuals that not only makes for a more detailed game, but also changed some gameplay fundamentals for the better. The biggest e-Sports in the world.', 'uploads/images/game_img_6903cc52d0c2d8.82770197.jpg', 'https://www.youtube.com/watch?v=c80dVYcL69E', 'https://store.steampowered.com/app/730/CounterStrike_2/'),
(9, 'fps', 'Valorant', 'Riot\'s FPS answer for Valve\'s CS2. Combining gunplay elements from Counter-Strike, hero / character elements present in hero shooters, and map knowledge, allowing for a more strategic gunplay. A surprise hit for the people, and it\'s still going strong.', 'uploads/images/game_img_6903cd278a3aa6.99306922.jpg', 'https://www.youtube.com/watch?v=lWr6dhTcu-E', 'https://playvalorant.com/en-us/'),
(12, 'rpg', 'The Elder Scrolls V: Skyrim', 'Skyrim, the fifth installment in the legendary Elder Scrolls series. The biggest game Bethesda has ever made, they re-released it two to three times. Also features an incredibly active modding community, possibly only second to Minecraft\'s modding community.\r\nIf you can only choose one RPG to play, it\'s this one.', 'uploads/images/game_img_6903d2077e8661.77472310.png', 'https://www.youtube.com/watch?v=JSRtYpNRoN0', 'https://store.steampowered.com/app/489830/The_Elder_Scrolls_V_Skyrim_Special_Edition/'),
(13, 'rpg', 'ELDEN RING', 'Another entry in the \"Souls\" genre, by FromSoftware themselves. The most beginner-friendly \"Souls\" game, though that doesn\'t mean the game is any easier than other \"Souls\" titles. Features world-building written by George R. R. Martin. If you\'re new into the \"Souls\" genre, this is the game for you.', 'uploads/images/game_img_6903d2c47a1a77.34827150.jpg', 'https://www.youtube.com/watch?v=E3Huy2cdih0', 'https://store.steampowered.com/app/1245620/ELDEN_RING/'),
(14, 'rpg', 'Cyberpunk 2077', 'The most anticipated games of the 2020s, only for it to release in a buggy state that renders the game unplayable. Fast-forward to now, through constant updates and fixes, the game has more than redeemed itself. With an incredibly detailed world and charming characters, one of which played by a certain Keanu Reeves. Welcome To Night City.', 'uploads/images/game_img_6903d36a481187.22550180.jpg', 'https://www.youtube.com/watch?v=lJiCOFwoyMA', 'https://store.steampowered.com/app/1091500/Cyberpunk_2077/'),
(17, 'moba', 'Dota 2', 'The biggest MOBA game in the world, the second biggest e-Sports behind Counter-Strike 2. Dota 2 is complex, having a steep learning curve that may turn off new players. And yet, it\'s one of the most rewarding game there is. ', 'uploads/images/game_img_69046f7a41e7b5.00230625.jpg', 'https://www.youtube.com/watch?v=-cSFPIwMEq4', 'https://store.steampowered.com/app/570/Dota_2/'),
(18, 'moba', 'Mobile Legends: Bang Bang', 'A fast-paced MOBA, developed specifically for mobile devices. It follows the general MOBA blueprint; teams of five, push down the three lanes, destroy the enemy\'s opposing turrets and eliminate their base. Being a mobile game, it\'s incredibly accessible, making it the biggest mobile e-Sports game.', 'uploads/images/game_img_690470564fc6f3.71791790.jpg', 'https://www.youtube.com/watch?v=cftqT7au9gk', 'https://www.mobilelegends.com/'),
(19, 'moba', 'Brawl Stars', 'Another fast-paced MOBA developed for mobile, with a more vibrant creative direction compared to Mobile Legends. It features a quick 3v3 battle and a solo or duo Battle Royale mode. It emphasizes rapid-fire action and strategic team coordination, making it easy to pick up, but hard to master.', 'uploads/images/game_img_6904714ba71fb1.30964272.webp', 'https://www.youtube.com/watch?v=Fik4Rp6S1Bs', 'https://supercell.com/en/games/brawlstars/'),
(22, 'puzzle', 'Portal', 'You play as Chell, a test subject in the Aperture Science Lab, where you\'re tasked with solving puzzles, involving escaping rooms, using a Portal gun. A legendary game that redefined the concept of portals in general.', 'uploads/images/game_img_69047c07c08de9.76248477.jpg', 'https://www.youtube.com/watch?v=TluRVBhmf8w', 'https://store.steampowered.com/app/400/Portal/'),
(23, 'puzzle', 'The Talos Principle', 'You play as an unnamed robot as you traverse a strange, yet beautiful world filled with ancient ruins and advanced technology. Guided by a voice known as Elohim, you are tasked with solving complex environmental puzzles.\r\n', 'uploads/images/game_img_69050ffbaaaa07.56713655.jpg', 'https://www.youtube.com/watch?v=Vu9QFBWb7WQ', 'https://store.steampowered.com/app/257510/The_Talos_Principle/'),
(24, 'puzzle', 'The Witness', 'An open-world puzzle game, set in a dazzling, bright, densely packed island. Unlike other puzzle games, the game does not provide instructions, instead relying on environmental cues and keen observation by the player to solve the ever-evolving puzzles.', 'uploads/images/game_img_690510be9da306.30675164.jpg', 'https://www.youtube.com/watch?v=ul7kNFD6noU', 'https://store.steampowered.com/app/210970/The_Witness/'),
(25, 'sport', 'eFootball', 'A free-to-play football title, a rebrand of Konami\'s long-standing Pro Evolution Soccer (PES) series. It features two primary modes: Authentic Teams, where you compete against other players using real teams, and Dream Team, where players can build their Dream Team and compete against other players\' Dream Teams.', 'uploads/images/game_img_690511a5cb8657.38880090.jpg', 'https://www.youtube.com/watch?v=BdyXsZMPjWo', 'https://store.steampowered.com/app/1665460/eFootball/'),
(26, 'sport', 'EA Sports FC', 'A rebrand of EA Sports\' long-standing FiFA series. A football game, similar in concept to older titles, each year bringing visual improvements. ', 'uploads/images/game_img_69051220ac8ed6.71695339.jpg', 'https://www.youtube.com/watch?v=TSi0iJYSQ24&vl=en', 'https://store.steampowered.com/app/3405690/EA_SPORTS_FC_26/'),
(27, 'sport', 'Football Manager 26', 'Another entry in the long-standing Football Manager series, you play as the coach as you manage your football team. Developed in Unity, the first in the series, the title features brand new user interface overhaul and improved details. It also introduces Women\'s Football, the first in its series.', 'uploads/images/game_img_69051338f00fc0.17805927.jpg', 'https://www.youtube.com/watch?v=_cDQi5kwuHQ', 'https://store.steampowered.com/app/3551340/Football_Manager_26/'),
(28, 'sim', 'Cities: Skylines', 'Critically-acclaimed title in the city-building genre, where you can build your own city, let it grow, develop it however you like, go through challenges and hurdles that plague a city, or blow it all up, because you can.', 'uploads/images/game_img_69051522c7c120.15159830.jpg', 'https://www.youtube.com/watch?v=0gI2N10QyRA', 'https://store.steampowered.com/app/255710/Cities_Skylines/'),
(29, 'sim', 'Euro Truck Simulator 2', 'Have you ever wanted to drive trucks? Yes? Then this is for you! Drive highly-detailed and fully-simulated trucks in a scaled-down Europe, featuring various European countries, trucks from various truck manufacturers, and take in the sights of what Europe has to offer.', 'uploads/images/game_img_69051776b566c0.50248242.jpg', 'https://www.youtube.com/watch?v=d3GuiADdiEg', 'https://store.steampowered.com/app/227300/Euro_Truck_Simulator_2/'),
(30, 'sim', 'The Sims 4', 'The fourth full entry in the Sims series, where you can make, customize, control your own sim in the world. Meet people, form relationships, get a job, get a hobby, redecorate a house, get married, let your sim drown in an electrocuted pool, the possibilities are endless!', 'uploads/images/game_img_69051884dd9b11.09379960.png', 'https://www.youtube.com/watch?v=GJENRAB4ykA', 'https://store.steampowered.com/app/1222670/The_Sims_4/'),
(31, 'survival', 'Sons of The Forest', 'Serving as a sequel to 2018\'s The Forest, you play as a private military contractor, dispatched into an island to find a billionaire and his family. You must utilize various survival, crafting, gathering skills and defend yourself from mutants and cannibal tribes.', 'uploads/images/game_img_6905199f35be09.96889943.jpg', 'https://www.youtube.com/watch?v=A_E4eCwUEqg', 'https://store.steampowered.com/app/1326470/Sons_Of_The_Forest/'),
(32, 'survival', 'Project Zomboid', 'A zombie survival RPG, stylized like a pixel art game, played from the top-down. You play as a human, trying to survive in a zombified world. Build bases, gather resources, craft weapons, find other humans to make allies, survive however long you can.', 'uploads/images/game_img_69051ac56e4133.45176083.jpg', 'https://www.youtube.com/watch?v=YhSd39QqQUg', 'https://store.steampowered.com/app/108600/Project_Zomboid/'),
(33, 'survival', 'The Long Dark', 'A first-person, exploration survival game, you are challenged to survive in a freezing, hostile Canadian wilderness in the aftermath of a mysterious global geomagnetic disaster that has knocked out all modern technology. Your threats? Cold, hunger, thirst, and Mother Nature. Good luck. ', 'uploads/images/game_img_69051b9106f5f9.89006106.jpg', 'https://www.youtube.com/watch?v=V5ytwWofaqY', 'https://store.steampowered.com/app/305620/The_Long_Dark/'),
(34, 'fight', 'TEKKEN 8', 'The 8th full title in the long-standing TEKKEN series, the game continues the story surrounding the TEKKEN world, from recurring characters such as Kazuya Mishima and his family, Jun Kazama and Jin Kazama, but also new additions to the series such as Reina and Victor Chevalier. Now updated with brand new animations and visuals.', 'uploads/images/game_img_69051e41c03877.17729290.jpg', 'https://www.youtube.com/watch?v=_MM4clV2qjE', 'https://store.steampowered.com/app/1778820/TEKKEN_8/'),
(35, 'fight', 'Mortal Kombat 1', 'Serving as the reboot in the long-standing Mortal Kombat series, the game is violent, with stunning visuals, but still maintained the fast-paced fighting gameplay everyone expects, and of course, fatalities.', 'uploads/images/game_img_69051f3c2da550.36042022.jpeg', 'https://www.youtube.com/watch?v=PL6ZdOXlj6g', 'https://store.steampowered.com/app/1971870/Mortal_Kombat_1/'),
(36, 'fight', 'Street Fighter 6', 'The latest entry in the long-standing Street Fighter series, the game features new fighting mechanics that revolutionize the gameplay, alongside brand new modes to appeal to newcomers.\r\n', 'uploads/images/game_img_69051fec556ed3.48763014.jpg', 'https://www.youtube.com/watch?v=4EnsDg6DCTE', 'https://store.steampowered.com/app/1364780/Street_Fighter_6/'),
(37, 'fps', 'iRacing', 'iRacing, the most realistic racing simulator there is. Compete with other racers in highly-detailed race-tracks around the world, in fully-simulated racing machines from all kinds of motorsports, under proper regulations employed by real motorsports. It can\'t get any more realistic than this.', 'uploads/images/game_img_690522237b2068.57186791.png', 'https://www.youtube.com/watch?v=ecfJGNauAwY', 'https://store.steampowered.com/app/266410/iRacing/'),
(38, 'fps', 'Assetto Corsa', 'A racing simulator, with heavy emphasis on the driving physics. Features a single-player mode, multiplayer, and various other race modes, with the main appeal being the modding scene. Mod the game to race Japanese Drift Machines in Tokyo\'s busy highways, and many more!', 'uploads/images/game_img_690522d95e1c63.09347479.png', 'https://www.youtube.com/watch?v=TDFN-E30jhU', 'https://store.steampowered.com/app/244210/Assetto_Corsa/'),
(39, 'fps', 'Gran Turismo 7', 'A PlayStation-exclusive, and the latest entry in the long-standing Gran Turismo series. A celebration of car culture, where you can drive, race, modify, and simply appreciate highly detailed cars from all spectrums, from the fastest machines ever to the historical machines that paved the way for the industry. This is Gran Turismo, the Real Driving Simulator.', 'uploads/images/game_img_690523a41c71b3.07100900.avif', 'https://www.youtube.com/watch?v=oz-O74SmTSQ', 'https://www.gran-turismo.com/us/gt7/top/');

-- --------------------------------------------------------

--
-- Table structure for table `game_cover`
--

CREATE TABLE `game_cover` (
  `game_cover_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `cover_path` varchar(1024) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `game_cover`
--

INSERT INTO `game_cover` (`game_cover_id`, `game_id`, `cover_path`) VALUES
(2, 7, 'uploads/covers/cover_7_690a0ff7754bf.jpg'),
(3, 8, 'uploads/covers/cover_8_690a2006e0117.jpg'),
(4, 12, 'uploads/covers/cover_12_690a204f3eacc.jpg'),
(5, 13, 'uploads/covers/cover_13_690a2081171ae.jpg'),
(6, 14, 'uploads/covers/cover_14_690a20ec59669.jpg'),
(7, 17, 'uploads/covers/cover_17_690a234240e7f.jpg'),
(8, 22, 'uploads/covers/cover_22_690a239abc7f8.jpg'),
(9, 23, 'uploads/covers/cover_23_690a23b3c316d.jpg'),
(10, 24, 'uploads/covers/cover_24_690a23cb37962.jpg'),
(11, 25, 'uploads/covers/cover_25_690a23e945fc5.jpg'),
(12, 26, 'uploads/covers/cover_26_690a240ba4928.jpg'),
(13, 27, 'uploads/covers/cover_27_690a2469688b2.jpg'),
(14, 28, 'uploads/covers/cover_28_690a2482e5653.jpg'),
(15, 29, 'uploads/covers/cover_29_690a2504403aa.jpg'),
(16, 30, 'uploads/covers/cover_30_690a2523720d3.jpg'),
(17, 31, 'uploads/covers/cover_31_690a25d6d4a5b.jpg'),
(18, 32, 'uploads/covers/cover_32_690a25fade09b.jpg'),
(19, 33, 'uploads/covers/cover_33_690a2616a4d96.jpg'),
(20, 34, 'uploads/covers/cover_34_690a2631d5566.jpg'),
(21, 35, 'uploads/covers/cover_35_690a264b08399.jpg'),
(22, 36, 'uploads/covers/cover_36_690a266eec4b1.jpg'),
(23, 37, 'uploads/covers/cover_37_690a268a07dc1.jpg'),
(24, 38, 'uploads/covers/cover_38_690a26b1d8ed0.jpg'),
(25, 9, 'uploads/covers/cover_9_690a2cf3570e9.jpg'),
(27, 39, 'uploads/covers/cover_39_690a332508cd6.jpg'),
(28, 18, 'uploads/covers/cover_18_690a51011355e.webp'),
(29, 19, 'uploads/covers/cover_19_690a51505532b.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `game_images`
--

CREATE TABLE `game_images` (
  `game_img_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `img_path` varchar(1024) NOT NULL,
  `img_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `game_images`
--

INSERT INTO `game_images` (`game_img_id`, `game_id`, `img_path`, `img_order`) VALUES
(11, 8, 'uploads/gallery/game_8_6905e1ff56f5c.jpg', 0),
(12, 8, 'uploads/gallery/game_8_6905e1ff5792f.jpg', 4),
(13, 8, 'uploads/gallery/game_8_6905e1ff5812d.jpg', 1),
(14, 8, 'uploads/gallery/game_8_6905e1ff58b67.jpg', 3),
(15, 8, 'uploads/gallery/game_8_6905e1ff597e8.jpg', 2),
(16, 8, 'uploads/gallery/game_8_6905e40108c34.jpg', 5),
(17, 7, 'uploads/gallery/game_7_6905e56124a13.jpg', 3),
(18, 7, 'uploads/gallery/game_7_6905e5612526e.jpg', 2),
(19, 7, 'uploads/gallery/game_7_6905e56125ec1.jpg', 1),
(20, 7, 'uploads/gallery/game_7_6905e561267af.jpg', 4),
(21, 7, 'uploads/gallery/game_7_6905e56126daf.jpg', 5),
(22, 7, 'uploads/gallery/game_7_6905e5612741c.jpg', 0),
(23, 12, 'uploads/gallery/game_12_6905e89220280.jpg', 5),
(24, 12, 'uploads/gallery/game_12_6905e892211e8.jpg', 0),
(25, 12, 'uploads/gallery/game_12_6905e89221e6a.jpg', 1),
(26, 12, 'uploads/gallery/game_12_6905e892226aa.jpg', 2),
(27, 12, 'uploads/gallery/game_12_6905e89222da0.jpg', 3),
(28, 12, 'uploads/gallery/game_12_6905e892233e6.jpg', 4),
(29, 13, 'uploads/gallery/game_13_6905e95459c87.jpg', 0),
(30, 13, 'uploads/gallery/game_13_6905e9545a5b5.jpg', 3),
(31, 13, 'uploads/gallery/game_13_6905e9545afd5.jpg', 1),
(32, 13, 'uploads/gallery/game_13_6905e9545bb79.jpg', 2),
(33, 13, 'uploads/gallery/game_13_6905e9545c3cc.jpg', 4),
(34, 13, 'uploads/gallery/game_13_6905e9545ca7e.jpg', 5),
(35, 14, 'uploads/gallery/game_14_6905ea201f6cd.jpg', 3),
(36, 14, 'uploads/gallery/game_14_6905ea20205c6.jpg', 4),
(37, 14, 'uploads/gallery/game_14_6905ea20210c4.jpg', 5),
(38, 14, 'uploads/gallery/game_14_6905ea2021843.jpg', 1),
(39, 14, 'uploads/gallery/game_14_6905ea2021e8d.jpg', 2),
(40, 14, 'uploads/gallery/game_14_6905ea20224f2.jpg', 0),
(41, 17, 'uploads/gallery/game_17_6905eb7e1ef24.jpg', 3),
(42, 17, 'uploads/gallery/game_17_6905eb7e1f937.jpg', 0),
(43, 17, 'uploads/gallery/game_17_6905eb7e20632.jpg', 4),
(44, 17, 'uploads/gallery/game_17_6905eb7e21291.jpg', 5),
(45, 17, 'uploads/gallery/game_17_6905eb7e21cc2.jpg', 2),
(46, 17, 'uploads/gallery/game_17_6905eb7e22543.jpg', 1),
(47, 22, 'uploads/gallery/game_22_6905ec1318338.jpg', 0),
(48, 22, 'uploads/gallery/game_22_6905ec131928d.jpg', 1),
(49, 22, 'uploads/gallery/game_22_6905ec1319c97.jpg', 2),
(50, 22, 'uploads/gallery/game_22_6905ec131a67d.jpg', 3),
(51, 22, 'uploads/gallery/game_22_6905ec131af7e.jpg', 4),
(52, 22, 'uploads/gallery/game_22_6905ec131b6af.jpg', 5),
(53, 23, 'uploads/gallery/game_23_6905ec7ed4c7d.jpg', 1),
(54, 23, 'uploads/gallery/game_23_6905ec7ed57f5.jpg', 0),
(55, 23, 'uploads/gallery/game_23_6905ec7ed6199.jpg', 3),
(56, 23, 'uploads/gallery/game_23_6905ec7ed6a94.jpg', 2),
(57, 23, 'uploads/gallery/game_23_6905ec7ed7090.jpg', 5),
(58, 23, 'uploads/gallery/game_23_6905ec7ed77b1.jpg', 4),
(59, 24, 'uploads/gallery/game_24_6905ed0463b63.jpg', 5),
(60, 24, 'uploads/gallery/game_24_6905ed04648a0.jpg', 4),
(61, 24, 'uploads/gallery/game_24_6905ed046548b.jpg', 1),
(62, 24, 'uploads/gallery/game_24_6905ed0465fb4.jpg', 3),
(63, 24, 'uploads/gallery/game_24_6905ed046679b.jpg', 2),
(64, 24, 'uploads/gallery/game_24_6905ed0466e05.jpg', 0),
(65, 25, 'uploads/gallery/game_25_6905ed9108b8b.jpg', 2),
(66, 25, 'uploads/gallery/game_25_6905ed91094bf.jpg', 3),
(67, 25, 'uploads/gallery/game_25_6905ed9109ee5.jpg', 4),
(68, 25, 'uploads/gallery/game_25_6905ed910a8ce.jpg', 5),
(69, 25, 'uploads/gallery/game_25_6905ed910af18.jpg', 1),
(70, 25, 'uploads/gallery/game_25_6905ed910b4af.jpg', 0),
(71, 26, 'uploads/gallery/game_26_6905eefc99a40.jpg', 0),
(72, 26, 'uploads/gallery/game_26_6905eefc9a69b.jpg', 1),
(73, 26, 'uploads/gallery/game_26_6905eefc9afec.jpg', 2),
(74, 26, 'uploads/gallery/game_26_6905eefc9b660.jpg', 3),
(75, 26, 'uploads/gallery/game_26_6905eefc9baf6.jpg', 4),
(76, 26, 'uploads/gallery/game_26_6905eefc9bee9.jpg', 5),
(77, 27, 'uploads/gallery/game_27_6905ef93b2c98.jpg', 5),
(78, 27, 'uploads/gallery/game_27_6905ef93b3a25.jpg', 4),
(79, 27, 'uploads/gallery/game_27_6905ef93b4525.jpg', 3),
(80, 27, 'uploads/gallery/game_27_6905ef93b4da9.jpg', 0),
(81, 27, 'uploads/gallery/game_27_6905ef93b55cc.jpg', 1),
(82, 27, 'uploads/gallery/game_27_6905ef93b5bea.jpg', 2),
(83, 28, 'uploads/gallery/game_28_6905f0340a282.jpg', 5),
(84, 28, 'uploads/gallery/game_28_6905f0340b230.jpg', 3),
(85, 28, 'uploads/gallery/game_28_6905f0340bbc1.jpg', 2),
(86, 28, 'uploads/gallery/game_28_6905f0340c3f5.jpg', 4),
(87, 28, 'uploads/gallery/game_28_6905f0340cae7.jpg', 1),
(88, 28, 'uploads/gallery/game_28_6905f0340d549.jpg', 0),
(89, 29, 'uploads/gallery/game_29_6905f0ab629ba.jpg', 3),
(90, 29, 'uploads/gallery/game_29_6905f0ab631b0.jpg', 2),
(91, 29, 'uploads/gallery/game_29_6905f0ab63a0f.jpg', 5),
(92, 29, 'uploads/gallery/game_29_6905f0ab63f72.jpg', 4),
(93, 29, 'uploads/gallery/game_29_6905f0ab645b6.jpg', 1),
(94, 29, 'uploads/gallery/game_29_6905f0ab64caa.jpg', 0),
(95, 30, 'uploads/gallery/game_30_6905f11ea9da6.jpg', 3),
(96, 30, 'uploads/gallery/game_30_6905f11eaa395.jpg', 1),
(97, 30, 'uploads/gallery/game_30_6905f11eaacd9.jpg', 2),
(98, 30, 'uploads/gallery/game_30_6905f11eab733.jpg', 0),
(99, 31, 'uploads/gallery/game_31_6905f182ae727.jpg', 2),
(100, 31, 'uploads/gallery/game_31_6905f182af192.jpg', 3),
(101, 31, 'uploads/gallery/game_31_6905f182afeff.jpg', 0),
(102, 31, 'uploads/gallery/game_31_6905f182b0a4e.jpg', 1),
(103, 31, 'uploads/gallery/game_31_6905f182b1031.jpg', 4),
(104, 31, 'uploads/gallery/game_31_6905f182b1583.jpg', 6),
(105, 32, 'uploads/gallery/game_32_6905f2a1693ed.jpg', 1),
(106, 32, 'uploads/gallery/game_32_6905f2a169dde.jpg', 3),
(107, 32, 'uploads/gallery/game_32_6905f2a16ab01.jpg', 2),
(108, 32, 'uploads/gallery/game_32_6905f2a16b3eb.jpg', 4),
(109, 32, 'uploads/gallery/game_32_6905f2a16b904.jpg', 5),
(110, 32, 'uploads/gallery/game_32_6905f2a16bfc2.jpg', 0),
(111, 33, 'uploads/gallery/game_33_6905f3255d83c.jpg', 0),
(112, 33, 'uploads/gallery/game_33_6905f3255de4d.jpg', 2),
(113, 33, 'uploads/gallery/game_33_6905f3255e90f.jpg', 3),
(114, 33, 'uploads/gallery/game_33_6905f3255f2ce.jpg', 1),
(115, 33, 'uploads/gallery/game_33_6905f3255fb85.jpg', 5),
(116, 33, 'uploads/gallery/game_33_6905f32560280.jpg', 4),
(117, 34, 'uploads/gallery/game_34_6905f3c74ced9.jpg', 2),
(118, 34, 'uploads/gallery/game_34_6905f3c74d667.jpg', 5),
(119, 34, 'uploads/gallery/game_34_6905f3c74de99.jpg', 3),
(120, 34, 'uploads/gallery/game_34_6905f3c74e358.jpg', 4),
(121, 34, 'uploads/gallery/game_34_6905f3c74e7ad.jpg', 1),
(122, 34, 'uploads/gallery/game_34_6905f3c74ef77.jpg', 0),
(123, 35, 'uploads/gallery/game_35_6906146355dac.jpg', 4),
(124, 35, 'uploads/gallery/game_35_6906146356c27.jpg', 5),
(125, 35, 'uploads/gallery/game_35_6906146357773.jpg', 3),
(126, 35, 'uploads/gallery/game_35_6906146357dad.jpg', 0),
(127, 35, 'uploads/gallery/game_35_69061463583ba.jpg', 1),
(128, 35, 'uploads/gallery/game_35_69061463589c5.jpg', 2),
(129, 36, 'uploads/gallery/game_36_690615130a4cc.jpg', 0),
(130, 36, 'uploads/gallery/game_36_690615130aee9.jpg', 0),
(131, 36, 'uploads/gallery/game_36_690615130b90f.jpg', 0),
(132, 36, 'uploads/gallery/game_36_690615130be8e.jpg', 0),
(133, 36, 'uploads/gallery/game_36_690615130c405.jpg', 0),
(134, 36, 'uploads/gallery/game_36_690615130ccf6.jpg', 0),
(135, 37, 'uploads/gallery/game_37_690615b695b1b.jpg', 1),
(136, 37, 'uploads/gallery/game_37_690615b6960e7.jpg', 5),
(137, 37, 'uploads/gallery/game_37_690615b6967a7.jpg', 2),
(138, 37, 'uploads/gallery/game_37_690615b697281.jpg', 3),
(139, 37, 'uploads/gallery/game_37_690615b697cf4.jpg', 4),
(140, 37, 'uploads/gallery/game_37_690615b6983b8.jpg', 0),
(141, 38, 'uploads/gallery/game_38_690616246c3cd.jpg', 1),
(142, 38, 'uploads/gallery/game_38_690616246d3b6.jpg', 2),
(143, 38, 'uploads/gallery/game_38_690616246db8c.jpg', 3),
(144, 38, 'uploads/gallery/game_38_690616246e24a.jpg', 4),
(145, 38, 'uploads/gallery/game_38_690616246e7fc.jpg', 0),
(146, 38, 'uploads/gallery/game_38_690616246ed68.jpg', 5),
(149, 9, 'uploads/gallery/game_9_690a2de5d111c.jpg', 2),
(150, 9, 'uploads/gallery/game_9_690a2dee11e15.jpg', 1),
(151, 9, 'uploads/gallery/game_9_690a2e4d425a5.jpg', 4),
(152, 9, 'uploads/gallery/game_9_690a2e4d43295.jpg', 3),
(153, 9, 'uploads/gallery/game_9_690a2e4d4407f.webp', 5),
(154, 9, 'uploads/gallery/game_9_690a2e4d44ab7.jpg', 0),
(161, 39, 'uploads/gallery/game_39_690a35b60f907.jpeg', 0),
(162, 39, 'uploads/gallery/game_39_690a35b6101b2.webp', 1),
(163, 39, 'uploads/gallery/game_39_690a35b610bf6.jpg', 4),
(164, 39, 'uploads/gallery/game_39_690a35b611293.png', 2),
(165, 39, 'uploads/gallery/game_39_690a35b61192a.jpg', 5),
(166, 39, 'uploads/gallery/game_39_690a35b611ec8.jpg', 3),
(167, 18, 'uploads/gallery/game_18_690a50aaa73a5.webp', 0),
(168, 18, 'uploads/gallery/game_18_690a50aaa82d8.jpg', 1),
(169, 18, 'uploads/gallery/game_18_690a50aaa8ca7.jpg', 2),
(170, 19, 'uploads/gallery/game_19_690a51982ae95.webp', 0),
(171, 19, 'uploads/gallery/game_19_690a51982bb21.webp', 1),
(172, 19, 'uploads/gallery/game_19_690a51982c597.jpg', 2);

-- --------------------------------------------------------

--
-- Table structure for table `rating`
--

CREATE TABLE `rating` (
  `rating_id` int(11) NOT NULL,
  `rating_game` tinyint(1) NOT NULL,
  `game_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rating`
--

INSERT INTO `rating` (`rating_id`, `rating_game`, `game_id`, `user_id`) VALUES
(1, 4, 7, 3),
(5, 5, 38, 3),
(8, 5, 14, 3),
(9, 1, 18, 3),
(10, 4, 29, 3);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `user_username` varchar(50) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `user_server` varchar(100) DEFAULT NULL,
  `sec_prompt` varchar(255) DEFAULT NULL,
  `sec_answer` varchar(255) DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `user_username`, `user_email`, `user_password`, `user_server`, `sec_prompt`, `sec_answer`, `is_admin`) VALUES
(1, 'admin', 'sincerity2103@gmail.com', '$2y$10$PagVgbv92r5BqIe/3hm6GuAlP0/9.iKhCKx3nnjh6BzibtN0kM2Fy', 'seas', 'prompt_1', '$2y$10$rgN6yG/mXBVk/zFdo6BCW.NvHBrcwlaueYZ5SMVKfgfhn3OtvaV/K', 1),
(3, 'max_beingstepen', 'anwar@gmail.com', '$2y$10$eXolrW74Tgl1JXBhbgjuje/RTaoF.DCYoA4WhJVqQJPSQMArT2hPC', 'east', 'prompt_2', '$2y$10$HLXht7TkyDVQiVbcvUBwFOhs3os9cpgJbbto9cuD6ckpxV.lNabHq', 0),
(6, 'irelandboi69', 'irelandboi@gmail.com', '$2y$10$n8Og4Z.8JJLohNqlpFdsS.vPUY0SmELlS7QQompVxRebv0uF4PK1a', 'east', 'prompt_4', '$2y$10$gWjf8y5rOBu.NCnWvF0mJOT1.nl3kXQw/vS8TQZvBg2lqpbxzQ3QS', 0),
(23, 'admin21', 'admin21@gmail.com', '$2y$10$ydz26uAkJ1JWqdTLARcpie0tDL8WRiGWN/VZqptMH0tFuDaCHkt2O', 'seas', 'prompt_1', '$2y$10$RV8.L6xomgj.RV/rMGrQEOR6OV3NtvodISpb7ZgGasd2eRXjmdK9S', 1),
(25, 'testing12345', 'testing12345@gmail.com', '$2y$10$qexK0JMfN6UDR.VmhoCHquw/s360X1YNi0Am6JmYhkdimRpE6s7I2', 'seas', 'prompt_5', '$2y$10$pWEVjFgaT6EBdWEYYX0oGuzy2OgbuUens5z6l7JyE7C.hztBEaDJe', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `favourites`
--
ALTER TABLE `favourites`
  ADD PRIMARY KEY (`favourite_id`),
  ADD UNIQUE KEY `user_game_favourite` (`user_id`,`game_id`),
  ADD KEY `game_id` (`game_id`);

--
-- Indexes for table `feedback_game`
--
ALTER TABLE `feedback_game`
  ADD PRIMARY KEY (`feedback_game_id`),
  ADD UNIQUE KEY `user_game_feedback` (`user_id`,`game_id`),
  ADD KEY `game_id` (`game_id`);

--
-- Indexes for table `feedback_site`
--
ALTER TABLE `feedback_site`
  ADD PRIMARY KEY (`feedback_site_id`),
  ADD UNIQUE KEY `user_feedback` (`user_id`);

--
-- Indexes for table `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`game_id`),
  ADD UNIQUE KEY `game_name` (`game_name`);

--
-- Indexes for table `game_cover`
--
ALTER TABLE `game_cover`
  ADD PRIMARY KEY (`game_cover_id`),
  ADD KEY `fk_game_cover_to_game` (`game_id`);

--
-- Indexes for table `game_images`
--
ALTER TABLE `game_images`
  ADD PRIMARY KEY (`game_img_id`),
  ADD KEY `game_id` (`game_id`);

--
-- Indexes for table `rating`
--
ALTER TABLE `rating`
  ADD PRIMARY KEY (`rating_id`),
  ADD UNIQUE KEY `user_game_rating` (`user_id`,`game_id`),
  ADD KEY `game_id` (`game_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_username` (`user_username`),
  ADD UNIQUE KEY `user_email` (`user_email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `favourites`
--
ALTER TABLE `favourites`
  MODIFY `favourite_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `feedback_game`
--
ALTER TABLE `feedback_game`
  MODIFY `feedback_game_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `feedback_site`
--
ALTER TABLE `feedback_site`
  MODIFY `feedback_site_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `games`
--
ALTER TABLE `games`
  MODIFY `game_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `game_cover`
--
ALTER TABLE `game_cover`
  MODIFY `game_cover_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `game_images`
--
ALTER TABLE `game_images`
  MODIFY `game_img_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=173;

--
-- AUTO_INCREMENT for table `rating`
--
ALTER TABLE `rating`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `favourites`
--
ALTER TABLE `favourites`
  ADD CONSTRAINT `favourites_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`game_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favourites_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback_game`
--
ALTER TABLE `feedback_game`
  ADD CONSTRAINT `feedback_game_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`game_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_game_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_game_ibfk_3` FOREIGN KEY (`game_id`) REFERENCES `games` (`game_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_game_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback_site`
--
ALTER TABLE `feedback_site`
  ADD CONSTRAINT `feedback_site_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `game_cover`
--
ALTER TABLE `game_cover`
  ADD CONSTRAINT `fk_game_cover_to_game` FOREIGN KEY (`game_id`) REFERENCES `games` (`game_id`) ON DELETE CASCADE;

--
-- Constraints for table `game_images`
--
ALTER TABLE `game_images`
  ADD CONSTRAINT `game_images_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`game_id`);

--
-- Constraints for table `rating`
--
ALTER TABLE `rating`
  ADD CONSTRAINT `rating_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`game_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rating_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
