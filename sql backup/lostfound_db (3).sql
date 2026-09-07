-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 08, 2026 at 02:42 PM
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
-- Database: `lostfound_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `message`, `is_active`, `created_at`) VALUES
(2, 'WEBSITE TERHEBAT ABAD INI', 0, '2026-06-07 00:15:18'),
(3, 'WAKLIF RAMLEE', 0, '2026-06-07 15:37:28'),
(4, 'JANGAN GARU SAMPAI LUKA', 1, '2026-06-07 22:44:40');

-- --------------------------------------------------------

--
-- Table structure for table `claims`
--

CREATE TABLE `claims` (
  `id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `claimant_id` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','collected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reward_paid` tinyint(1) DEFAULT 0,
  `reward_method` varchar(50) DEFAULT NULL,
  `preferred_reward_method` enum('cash','online') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `claims`
--

INSERT INTO `claims` (`id`, `listing_id`, `claimant_id`, `message`, `status`, `created_at`, `reward_paid`, `reward_method`, `preferred_reward_method`) VALUES
(6, 111, 54, 'ni kipas aku oii awat hang rembat', 'collected', '2026-06-07 14:53:38', 0, NULL, NULL),
(7, 112, 54, 'aku ada jumpa la dok jam kau', 'collected', '2026-06-07 17:59:47', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `listings`
--

CREATE TABLE `listings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `listing_type` enum('lost','found') NOT NULL,
  `is_boosted` tinyint(1) DEFAULT 0,
  `status` enum('active','removed') DEFAULT 'active',
  `resolved_status` enum('active','resolved','expired') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `boost_requested` tinyint(1) DEFAULT 0,
  `date_lost` date DEFAULT NULL,
  `reward` varchar(50) DEFAULT NULL,
  `lat` decimal(10,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  `is_flagged` tinyint(1) DEFAULT 0,
  `flag_reason` varchar(255) DEFAULT NULL,
  `boost_expires_at` datetime DEFAULT NULL,
  `is_archived` tinyint(1) DEFAULT 0,
  `archived_at` datetime DEFAULT NULL,
  `archived_reason` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `listings`
--

INSERT INTO `listings` (`id`, `user_id`, `title`, `description`, `category`, `location`, `image`, `listing_type`, `is_boosted`, `status`, `resolved_status`, `created_at`, `boost_requested`, `date_lost`, `reward`, `lat`, `lng`, `is_flagged`, `flag_reason`, `boost_expires_at`, `is_archived`, `archived_at`, `archived_reason`) VALUES
(11, 6, 'Lost Gold Bracelet with Allah Pendant', 'Gold bracelet with a small Allah calligraphy pendant. Sentimental value, gift from late grandmother. Lost near the prayer hall.', 'item', 'Masjid Putra, Putrajaya', 'uploads/bracelet1.webp', 'lost', 0, 'active', 'active', '2026-04-11 05:00:00', 0, '2026-04-11', '300', 2.93680000, 101.68850000, 0, NULL, NULL, 0, NULL, NULL),
(12, 3, 'Lost Black Leather Wallet', 'Black bifold leather wallet containing IC, two bank cards and RM80 cash. Has a small Arsenal FC sticker on the back.', 'item', 'Mid Valley Megamall, Kuala Lumpur', 'uploads/wallet1.jpg', 'lost', 0, 'active', 'active', '2026-04-10 06:00:00', 0, '2026-04-10', '50', 3.11780000, 101.67660000, 0, NULL, NULL, 0, NULL, NULL),
(13, 3, 'Found Orange Tabby Cat Near Mosque', 'Pet Type: Cat\nPet Name: Unknown\nBreed: Domestic Shorthair\nColor: Orange tabby\n\nFound a friendly orange tabby cat sitting near the mosque entrance. No collar. Well fed and calm. Currently being kept safe.', 'pet', 'Masjid Wilayah, Kuala Lumpur', 'uploads/cat1.jpg', 'found', 0, 'active', 'active', '2026-04-20 01:00:00', 0, '2026-04-20', '', 3.17510000, 101.68690000, 0, NULL, NULL, 0, NULL, NULL),
(14, 4, 'Lost Rose Gold iPhone 14', 'Rose gold iPhone 14 with a transparent floral case. Screen has a small crack on the top right. Lost after taking photos at the waterfront.', 'item', 'Kuching Waterfront, Sarawak', 'uploads/iphone1.jpg', 'lost', 0, 'active', 'active', '2026-04-08 08:30:00', 0, '2026-04-08', '150', 1.55780000, 110.34630000, 0, NULL, NULL, 1, '2026-06-08 02:12:04', '60_days_no_resolution'),
(15, 4, 'Missing: Young Girl Age 8 Named Hana', 'Name: Hana\nAge: 8\nLast seen wearing: Yellow dress, white sandals, pink backpack\nDistinguishing features: Short hair with yellow hairclip, small birthmark on left cheek\n\nWent missing while shopping with family. Please contact immediately if seen.', 'person', 'Parkson Kuching, Sarawak', 'uploads/child1.jpg', 'lost', 0, 'active', 'active', '2026-04-23 05:30:00', 0, '2026-04-23', '200', 1.55730000, 110.34540000, 0, NULL, NULL, 0, NULL, NULL),
(16, 5, 'Lost Polygon Mountain Bike', 'Vehicle Type: Bicycle\nPlate Number: N/A\nVehicle Color: Blue and black\n\nBlue and black Polygon mountain bike 21-speed. Has custom blue handlebar grips. Lock was cut at the bike rack outside the gym.', 'vehicle', 'USJ, Subang Jaya', 'uploads/bicycle1.jpg', 'lost', 0, 'active', 'active', '2026-04-15 13:00:00', 0, '2026-04-15', '200', 3.04560000, 101.57880000, 0, NULL, NULL, 0, NULL, NULL),
(17, 5, 'Lost Black Labrador Named Rex', 'Pet Type: Dog\nPet Name: Rex\nBreed: Black Labrador\nColor: Black\n\nLarge friendly black Labrador. Responds to Rex. Wearing a blue GPS collar — battery may be dead. Escaped through the gate.', 'pet', 'Damansara Perdana, Petaling Jaya', 'uploads/dog1.jpg', 'lost', 0, 'active', 'active', '2026-04-17 11:00:00', 0, '2026-04-17', '300', 3.15600000, 101.62300000, 0, NULL, NULL, 0, NULL, NULL),
(18, 6, 'Found: Unconscious Man Near Roadside', 'Name: Unknown\nAge: 40\nWearing: Office shirt, dark trousers\nDistinguishing features: Has a name tag that says Encik Raza\n\nFound a man unconscious near the roadside. Ambulance has been called. Posting here in case family is searching.', 'person', 'Jalan Ipoh, Kuala Lumpur', 'uploads/person1.jpg', 'found', 0, 'active', 'active', '2026-04-27 14:00:00', 0, '2026-04-27', '', 3.18780000, 101.68230000, 0, NULL, NULL, 0, NULL, NULL),
(19, 7, 'Lost Siberian Husky Named Niko', 'Pet Type: Dog\nPet Name: Niko\nBreed: Siberian Husky\nColor: Grey and white with blue eyes\n\nVery energetic and friendly. Blue eyes. Wearing black collar with name tag. Ran out when delivery person opened the gate.', 'pet', 'Kota Damansara, Selangor', 'uploads/dog2.jpg', 'lost', 0, 'active', 'active', '2026-04-14 09:00:00', 0, '2026-04-14', '400', 3.15950000, 101.58120000, 0, NULL, NULL, 0, NULL, NULL),
(20, 7, 'Found Red Kapchai Parked for Days', 'Vehicle Type: Motorcycle\nPlate Number: Partially visible — B 23**\nVehicle Color: Red\n\nRed kapchai parked outside the mamak for 5 days. No lock. Gathering dust. May have been abandoned.', 'vehicle', 'Subang Jaya, Selangor', 'uploads/motorcycle1.jpg', 'found', 0, 'active', 'active', '2026-04-21 03:00:00', 0, '2026-04-21', '', 3.04560000, 101.57880000, 0, NULL, NULL, 0, NULL, NULL),
(21, 8, 'Found Pair of AirPods Pro', 'Found AirPods Pro in white case near the food court. No scratches. 20% battery remaining. Kept safely at customer service counter.', 'item', 'IOI City Mall, Putrajaya', 'uploads/airpods1.jpg', 'found', 0, 'active', 'active', '2026-04-18 05:00:00', 0, '2026-04-18', '', 2.96970000, 101.71770000, 0, NULL, NULL, 0, NULL, NULL),
(22, 8, 'Missing: Autistic Teen Named Iqbal', 'Name: Muhammad Iqbal\nAge: 15\nLast seen wearing: Green polo shirt, grey pants, white cap\nDistinguishing features: Non-verbal, carries blue fidget spinner, responds to loud clapping\n\nMissed the school bus and has not returned home.', 'person', 'Wangsa Maju, Kuala Lumpur', 'uploads/person2.jpg', 'lost', 0, 'active', 'active', '2026-04-21 06:30:00', 0, '2026-04-21', '200', 3.20650000, 101.73420000, 0, NULL, NULL, 0, NULL, NULL),
(23, 9, 'Lost DJI Mini 3 Drone', 'DJI Mini 3 in original grey case. Has a small Malaysian flag sticker on the body. Lost at the event after flyaway incident. SD card has important footage.', 'item', 'Putrajaya Botanical Garden', 'uploads/drone1.jpg', 'lost', 0, 'active', 'active', '2026-04-14 07:00:00', 0, '2026-04-14', '200', 2.92320000, 101.69760000, 0, NULL, NULL, 0, NULL, NULL),
(24, 9, 'Lost Red Honda CBR150', 'Vehicle Type: Motorcycle\nPlate Number: WB 3312 D\nVehicle Color: Red and black\n\nRed and black Honda CBR150 with racing-style custom exhaust. Has a Transformers logo sticker on the fuel tank.', 'vehicle', 'Pandan Indah, Kuala Lumpur', 'uploads/motorcycle2.jpg', 'lost', 0, 'active', 'active', '2026-04-18 15:30:00', 0, '2026-04-18', '300', 3.11000000, 101.75000000, 0, NULL, NULL, 0, NULL, NULL),
(25, 10, 'Lost Shih Tzu Named Mimi', 'Pet Type: Dog\nPet Name: Mimi\nBreed: Shih Tzu\nColor: White and golden\n\nSmall white and golden Shih Tzu recently groomed. Wearing pink ribbon collar. Very friendly towards strangers which is worrying.', 'pet', 'Klang, Selangor', 'uploads/dog3.jpg', 'lost', 0, 'active', 'active', '2026-04-17 10:00:00', 0, '2026-04-17', '150', 3.04490000, 101.44500000, 0, NULL, NULL, 0, NULL, NULL),
(26, 10, 'Found Pink Wallet Near Cashier', 'Found a pink floral wallet near the cashier. Contains some cash and a Touch n Go card but no IC. Submitted to customer service but also posting here.', 'item', 'AEON Bukit Tinggi, Klang', 'uploads/wallet2.jpg', 'found', 0, 'active', 'active', '2026-04-20 08:30:00', 0, '2026-04-20', '', 3.05120000, 101.46780000, 0, NULL, NULL, 0, NULL, NULL),
(27, 11, 'Lost Canon EOS R50 Camera', 'Black Canon EOS R50 with 18-45mm kit lens. Has a red patterned camera strap. SD card contains irreplaceable holiday photos.', 'item', 'Batu Ferringhi Beach, Penang', 'uploads/camera1.jpg', 'lost', 0, 'active', 'active', '2026-04-16 06:00:00', 0, '2026-04-16', '300', 5.46780000, 100.24780000, 0, NULL, NULL, 0, NULL, NULL),
(28, 11, 'Lost White Perodua Axia', 'Vehicle Type: Car\nPlate Number: PEL 7743\nVehicle Color: White\n\nWhite Perodua Axia with USM parking sticker on the windscreen and a small scratch on the rear bumper.', 'vehicle', 'USM, Penang', 'uploads/car1.jpg', 'lost', 0, 'active', 'active', '2026-04-19 12:00:00', 0, '2026-04-19', '300', 5.35730000, 100.30230000, 0, NULL, NULL, 0, NULL, NULL),
(29, 12, 'Lost British Shorthair Cat Named Ash', 'Pet Type: Cat\nPet Name: Ash\nBreed: British Shorthair\nColor: Blue grey\n\nBlue grey British Shorthair, round face and plush coat. Neutered male. Wearing breakaway collar with address tag. Indoor cat, very scared outside.', 'pet', 'Cyberjaya, Selangor', 'uploads/cat2.jpg', 'lost', 0, 'active', 'active', '2026-04-19 14:00:00', 0, '2026-04-19', '200', 2.92130000, 101.65590000, 0, NULL, NULL, 0, NULL, NULL),
(30, 12, 'Found: Crying Girl at Airport', 'Name: Unknown\nAge: 7\nWearing: Pink butterfly dress, white shoes\nDistinguishing features: Carrying a Minnie Mouse luggage trolley\n\nFound young girl crying alone at departure hall. Says she cannot find her parents. Currently with airport security.', 'person', 'KLIA, Sepang', 'uploads/child2.jpg', 'found', 0, 'active', 'active', '2026-04-27 06:00:00', 0, '2026-04-27', '', 2.74560000, 101.70950000, 0, NULL, NULL, 0, NULL, NULL),
(31, 13, 'Lost Passport Near Check-In Counter', 'Malaysian passport (blue cover) with a boarding pass to London and an international driving license inside. Lost near the check-in counter. Extremely urgent.', 'item', 'Dubai International Airport, UAE', 'uploads/passport1.jpg', 'lost', 0, 'active', 'active', '2026-04-18 01:00:00', 0, '2026-04-18', '500', 25.25320000, 55.36570000, 0, NULL, NULL, 0, NULL, NULL),
(32, 13, 'Lost White Tesla Model 3', 'Vehicle Type: Car\nPlate Number: Dubai plate, partially visible D 4**21\nVehicle Color: White\n\nWhite Tesla Model 3 Long Range. Has a dashcam and a Malaysian flag sticker on the rear. Went missing from hotel valet.', 'vehicle', 'Downtown Dubai, UAE', 'uploads/car2.jpg', 'lost', 0, 'active', 'active', '2026-04-19 15:00:00', 0, '2026-04-19', '2000', 25.19720000, 55.27960000, 0, NULL, NULL, 0, NULL, NULL),
(33, 14, 'Lost British Bulldog Named Winston', 'Pet Type: Dog\nPet Name: Winston\nBreed: British Bulldog\nColor: White and brown patches\n\nBritish Bulldog, 4 years old. Green collar with Union Jack tag. Went missing from the garden. Very slow runner so should be nearby.', 'pet', 'Richmond, London, UK', 'uploads/dog4.jpg', 'lost', 0, 'active', 'active', '2026-04-19 07:00:00', 0, '2026-04-19', '500', 51.46130000, -0.30370000, 0, NULL, NULL, 0, NULL, NULL),
(34, 14, 'Found Scarf and Gloves at Park Bench', 'Found a grey wool scarf with matching gloves on the park bench. Appear expensive and handmade. Left at the park information desk.', 'item', 'Hyde Park, London, UK', 'uploads/scarf1.jpg', 'found', 0, 'active', 'active', '2026-04-22 03:00:00', 0, '2026-04-22', '', 51.50730000, -0.16570000, 0, NULL, NULL, 0, NULL, NULL),
(35, 15, 'Lost Black Honda EX5 Motorcycle', 'Vehicle Type: Motorcycle\nPlate Number: WQR 4521\nVehicle Color: Black\n\nBlack Honda EX5 with a small scratch on the left panel. Has yellow sticker on front. Went missing overnight from roadside.', 'vehicle', 'Chow Kit, Kuala Lumpur', 'uploads/motorcycle3.jpg', 'lost', 0, 'active', 'active', '2026-04-10 23:00:00', 0, '2026-04-11', '300', 3.16870000, 101.69850000, 0, NULL, NULL, 0, NULL, NULL),
(36, 15, 'Missing: Elderly Man Named Pak Hamid', 'Name: Pak Hamid\nAge: 72\nLast seen wearing: Grey baju melayu, black songkok\nDistinguishing features: Walks with wooden cane, hearing impaired\n\nMissing since morning prayer. Family is very worried. Please contact immediately.', 'person', 'Cheras, Kuala Lumpur', 'uploads/person3.jpg', 'lost', 0, 'active', 'active', '2026-04-25 00:00:00', 0, '2026-04-25', '100', 3.08000000, 101.74000000, 0, NULL, NULL, 0, NULL, NULL),
(37, 16, 'Found MacBook Air M2 in Library', 'Silver MacBook Air M2 13-inch. Has a cactus sticker on the cover. Currently kept at the library front desk. Please bring proof of ownership.', 'item', 'Universiti Malaya Library, PJ', 'uploads/laptop1.jpg', 'found', 0, 'active', 'active', '2026-04-19 08:00:00', 0, '2026-04-19', '', 3.12090000, 101.65590000, 0, NULL, NULL, 0, NULL, NULL),
(38, 16, 'Lost Ragdoll Cat Named Luna', 'Pet Type: Cat\nPet Name: Luna\nBreed: Ragdoll\nColor: Cream with light brown markings\n\nFemale Ragdoll, very calm and gentle. Blue eyes. Silver collar with bell. Slipped out through a gap in the fence.', 'pet', 'Taman Melawati, Kuala Lumpur', 'uploads/cat3.jpg', 'lost', 0, 'active', 'active', '2026-04-15 13:00:00', 0, '2026-04-15', '150', 3.21760000, 101.75430000, 0, NULL, NULL, 0, NULL, NULL),
(39, 17, 'Found Abandoned White Myvi on Roadside', 'Vehicle Type: Car\nPlate Number: Partially visible — JTB **34\nVehicle Color: White\n\nWhite Perodua Myvi parked on the roadside with hazard lights on for over 2 days. Keys left inside. Also reported to police.', 'vehicle', 'Jalan Ipoh, Kuala Lumpur', 'uploads/car3.jpg', 'found', 0, 'active', 'active', '2026-04-17 02:00:00', 0, '2026-04-17', '', 3.18780000, 101.68230000, 0, NULL, NULL, 0, NULL, NULL),
(40, 17, 'Lost Sony WH-1000XM5 Headphones', 'Black Sony WH-1000XM5 in a black case. Has a small red sticker on the right ear cup. Lost on the LRT during peak hour commute.', 'item', 'KL Sentral, Kuala Lumpur', 'uploads/headphones1.jpg', 'lost', 0, 'active', 'active', '2026-04-09 00:30:00', 0, '2026-04-09', '100', 3.13430000, 101.68680000, 0, NULL, NULL, 0, NULL, NULL),
(41, 18, 'Found Stray Puppy Near School Gate', 'Pet Type: Dog\nPet Name: Unknown\nBreed: Mixed breed\nColor: Brown with white patches\n\nFound a small puppy near the school gate, around 3 months old. Very skinny but friendly. Looking for owner or suitable adopter.', 'pet', 'Ampang, Selangor', 'uploads/puppy1.jpg', 'found', 0, 'active', 'active', '2026-04-19 23:30:00', 0, '2026-04-20', '', 3.15480000, 101.76230000, 0, NULL, NULL, 0, NULL, NULL),
(42, 18, 'Missing: Teenage Boy Age 16 Named Azri', 'Name: Azri\nAge: 16\nLast seen wearing: Black hoodie, blue jeans, white Nike shoes\nDistinguishing features: Tall, slim, wears glasses\n\nLeft home for school but never arrived. Phone is switched off.', 'person', 'Wangsa Maju, Kuala Lumpur', 'uploads/person4.jpg', 'lost', 0, 'active', 'active', '2026-04-20 07:00:00', 0, '2026-04-20', '100', 3.20650000, 101.73420000, 0, NULL, NULL, 0, NULL, NULL),
(43, 19, 'Lost Ray-Ban Wayfarer Sunglasses', 'Black Ray-Ban Wayfarer sunglasses in a brown leather case. Prescription lenses, minus power. Lost near the volleyball court area on the beach.', 'item', 'Batu Ferringhi Beach, Penang', 'uploads/sunglasses1.jpg', 'lost', 0, 'active', 'active', '2026-04-16 05:20:00', 0, '2026-04-16', '150', 5.46780000, 100.24780000, 0, NULL, NULL, 0, NULL, NULL),
(44, 19, 'Found Luxury Road Bicycle at MRT Station', 'Vehicle Type: Bicycle\nPlate Number: N/A\nVehicle Color: Gold and black\n\nHigh-end gold and black road bicycle locked to the railing at the MRT station. Has been there for 4 days uncollected.', 'vehicle', 'Bukit Bintang MRT, KL', 'uploads/bicycle2.jpg', 'found', 0, 'active', 'active', '2026-04-24 03:00:00', 0, '2026-04-24', '', 3.14450000, 101.71180000, 0, NULL, NULL, 0, NULL, NULL),
(45, 20, 'Lost Pomeranian Named Kiki', 'Pet Type: Dog\nPet Name: Kiki\nBreed: Pomeranian\nColor: Cream\n\nCream fluffy Pomeranian, very small. Wearing green bow collar. Very attached to owner. Went missing near the beach promenade.', 'pet', 'Gurney Drive, Penang', 'uploads/dog5.jpg', 'lost', 0, 'active', 'active', '2026-04-18 09:00:00', 0, '2026-04-18', '200', 5.43670000, 100.31330000, 0, NULL, NULL, 0, NULL, NULL),
(46, 20, 'Found GoPro Hero 11 Washed Ashore', 'Found a GoPro Hero 11 in a waterproof case on the beach shore. Still functional. Has footage on the SD card which may identify the owner.', 'item', 'Redang Island, Terengganu', 'uploads/gopro1.jpg', 'found', 0, 'active', 'active', '2026-04-21 00:00:00', 0, '2026-04-21', '', 5.78970000, 102.99960000, 0, NULL, NULL, 0, NULL, NULL),
(47, 21, 'Lost Matte Black Proton Saga', 'Vehicle Type: Car\nPlate Number: WXY 5566\nVehicle Color: Matte black\n\nMatte black Proton Saga with custom body kit and red brake calipers. Has a dragon sticker on the rear windscreen.', 'vehicle', 'Shah Alam, Selangor', 'uploads/car4.jpg', 'lost', 0, 'active', 'active', '2026-04-16 15:00:00', 0, '2026-04-16', '300', 3.07330000, 101.51850000, 0, NULL, NULL, 0, NULL, NULL),
(48, 21, 'Found: Elderly Auntie at Pasar Malam', 'Name: Unknown\nAge: 70\nWearing: Pink floral blouse, black slacks\nDistinguishing features: Carrying large plastic bag with vegetables, appears confused\n\nFound an elderly woman at the pasar malam who cannot remember where she lives.', 'person', 'Pasar Malam Shah Alam', 'uploads/person5.jpg', 'found', 0, 'active', 'active', '2026-04-25 12:00:00', 0, '2026-04-25', '', 3.07330000, 101.51850000, 0, NULL, NULL, 0, NULL, NULL),
(49, 22, 'Lost Longchamp Navy Tote Bag', 'Navy blue Longchamp Le Pliage tote bag. Contains a planner, earphones and a medicine pouch. Lost near the departure gates.', 'item', 'KLIA2, Sepang', 'uploads/bag1.jpg', 'lost', 0, 'active', 'active', '2026-04-16 23:30:00', 0, '2026-04-17', '150', 2.74560000, 101.70950000, 0, NULL, NULL, 0, NULL, NULL),
(50, 22, 'Found Stray Rabbit in Garden', 'Pet Type: Other\nPet Name: Unknown\nBreed: Dutch Rabbit\nColor: White and brown\n\nFound a tame white and brown Dutch rabbit eating grass in the garden. No tag or collar. Clearly a pet. Currently being cared for.', 'pet', 'Puchong, Selangor', 'uploads/rabbit1.jpg', 'found', 0, 'active', 'active', '2026-04-18 23:30:00', 0, '2026-04-19', '', 3.02770000, 101.62000000, 0, NULL, NULL, 0, NULL, NULL),
(51, 23, 'Lost Blue Proton X50', 'Vehicle Type: Car\nPlate Number: VCH 8821\nVehicle Color: Electric blue\n\nElectric blue Proton X50 2022 with dashcam visible through windscreen and sport rims. Went missing from the apartment parking overnight.', 'vehicle', 'Gombak, Selangor', 'uploads/car5.jpg', 'lost', 0, 'active', 'active', '2026-04-16 22:00:00', 0, '2026-04-17', '500', 3.24560000, 101.69820000, 0, NULL, NULL, 0, NULL, NULL),
(52, 23, 'Found Silver Watch Near Fountain', 'Found a silver Casio analog watch near the main fountain. In good condition. Kept safely at the information counter.', 'item', 'Dataran Merdeka, KL', 'uploads/watch1.jpg', 'found', 0, 'active', 'active', '2026-04-11 03:00:00', 0, '2026-04-11', '', 3.14780000, 101.69530000, 0, NULL, NULL, 0, NULL, NULL),
(53, 24, 'Lost Cockatiel Named Coco', 'Pet Type: Bird\nPet Name: Coco\nBreed: Cockatiel\nColor: Yellow and grey\n\nYellow and grey cockatiel. Can whistle and say hello. Flew out through the balcony door during windy weather.', 'pet', 'Sri Petaling, Kuala Lumpur', 'uploads/bird1.jpg', 'lost', 0, 'active', 'active', '2026-04-13 08:00:00', 0, '2026-04-13', '150', 3.06960000, 101.69480000, 0, NULL, NULL, 0, NULL, NULL),
(54, 24, 'Found Electric Scooter Near Mamak', 'Vehicle Type: Other\nPlate Number: N/A\nVehicle Color: Black\n\nBlack electric scooter parked outside the mamak for 4 days. No lock. Posting here in case owner is searching.', 'vehicle', 'Bangsar, Kuala Lumpur', 'uploads/scooter1.jpg', 'found', 0, 'active', 'active', '2026-04-22 12:00:00', 0, '2026-04-22', '', 3.12970000, 101.68000000, 0, NULL, NULL, 0, NULL, NULL),
(55, 25, 'Lost Mechanical Keyboard Keychron K2', 'Black Keychron K2 mechanical keyboard with RGB. Has a custom blue spacebar keycap. Left at the cybercafe by accident.', 'item', 'Imbi, Kuala Lumpur', 'uploads/keyboard1.jpg', 'lost', 0, 'active', 'active', '2026-04-12 15:00:00', 0, '2026-04-12', '80', 3.14430000, 101.71230000, 0, NULL, NULL, 0, NULL, NULL),
(56, 25, 'Missing: University Student Named Yasmin', 'Name: Yasmin Binti Razali\nAge: 20\nLast seen wearing: Purple hijab, grey university hoodie, jeans\nDistinguishing features: Petite, round glasses, always carries a tote bag\n\nDid not return to hostel. Last seen at the library.', 'person', 'UPM, Serdang', 'uploads/person6.jpg', 'lost', 0, 'active', 'active', '2026-04-24 15:00:00', 0, '2026-04-24', '100', 2.99280000, 101.71610000, 0, NULL, NULL, 0, NULL, NULL),
(57, 26, 'Found Student ID Card at Cafeteria', 'Found a UiTM student ID card on the cafeteria floor. Name is visible on the card. Please contact to collect.', 'item', 'UiTM Samarahan, Sarawak', 'uploads/id1.jpg', 'found', 0, 'active', 'active', '2026-04-11 04:00:00', 0, '2026-04-11', '', 1.47490000, 110.45470000, 0, NULL, NULL, 0, NULL, NULL),
(58, 26, 'Lost White Persian Cat Named Snowball', 'Pet Type: Cat\nPet Name: Snowball\nBreed: Persian\nColor: Pure white\n\nMissing indoor white Persian cat. Very shy. Pink collar with small bell. Last seen near the kitchen window sill.', 'pet', 'Tabuan Jaya, Kuching', 'uploads/cat4.jpg', 'lost', 0, 'active', 'active', '2026-04-14 12:00:00', 0, '2026-04-14', '100', 1.51330000, 110.36330000, 0, NULL, NULL, 0, NULL, NULL),
(59, 27, 'Lost Silver Honda City', 'Vehicle Type: Car\nPlate Number: WTR 2234\nVehicle Color: Silver\n\nSilver Honda City 2019 with a UPM parking sticker on the windscreen. Missing from roadside since morning.', 'vehicle', 'Serdang, Selangor', 'uploads/car6.jpg', 'lost', 0, 'active', 'active', '2026-04-15 22:30:00', 0, '2026-04-16', '500', 2.99280000, 101.71610000, 0, NULL, NULL, 0, NULL, NULL),
(60, 27, 'Lost Michael Kors Handbag', 'Brown Michael Kors satchel bag with gold hardware. Contains makeup pouch, house keys and a notebook. Lost while shopping at the mall.', 'item', 'Pavilion KL, Bukit Bintang', 'uploads/handbag1.jpg', 'lost', 0, 'active', 'active', '2026-04-15 07:00:00', 0, '2026-04-15', '200', 3.14880000, 101.71310000, 0, NULL, NULL, 0, NULL, NULL),
(61, 28, 'Missing: Mother Named Kak Ros', 'Name: Rohani Binti Karim\nAge: 42\nLast seen wearing: Maroon hijab, black baju kurung\nDistinguishing features: Slight limp on left leg, carries brown handbag\n\nLeft home for the market and has not returned. Phone goes to voicemail.', 'person', 'Pasar Besar Selayang, Selangor', 'uploads/person7.jpg', 'lost', 0, 'active', 'active', '2026-04-25 02:00:00', 0, '2026-04-25', '100', 3.24330000, 101.64980000, 0, NULL, NULL, 0, NULL, NULL),
(62, 28, 'Found Injured Pigeon with Ring on Leg', 'Pet Type: Bird\nPet Name: Unknown\nBreed: Racing Pigeon\nColor: Grey and white\n\nFound an injured pigeon on the rooftop. Has a metal ring on its leg indicating it may be a racing pigeon. Currently being cared for.', 'pet', 'Jalan Tun Razak, KL', 'uploads/bird2.jpg', 'found', 0, 'active', 'active', '2026-04-19 06:00:00', 0, '2026-04-19', '', 3.16360000, 101.71830000, 0, NULL, NULL, 0, NULL, NULL),
(63, 29, 'Lost Tiffany Blue Hydro Flask', 'Tiffany blue Hydro Flask 32oz with BTS and sunflower stickers. Has a dent near the bottom. Lost at the gym locker area.', 'item', 'Fitness First, The Gardens Mall, KL', 'uploads/flask1.jpg', 'lost', 0, 'active', 'active', '2026-04-13 11:00:00', 0, '2026-04-13', '30', 3.11860000, 101.67570000, 0, NULL, NULL, 0, NULL, NULL),
(64, 29, 'Lost Yellow Honda Wave', 'Vehicle Type: Motorcycle\nPlate Number: BG 7821 A\nVehicle Color: Yellow and black\n\nYellow and black Honda Wave 125. Has a small dent on the right footrest area. Went missing from the roadside near the hawker stall.', 'vehicle', 'Kota Kinabalu, Sabah', 'uploads/motorcycle4.jpg', 'lost', 0, 'active', 'active', '2026-04-20 14:00:00', 0, '2026-04-20', '200', 5.98040000, 116.07350000, 0, NULL, NULL, 0, NULL, NULL),
(65, 30, 'Lost Golden Retriever Named Buddy', 'Pet Type: Dog\nPet Name: Buddy\nBreed: Golden Retriever\nColor: Golden\n\nFriendly golden retriever responds to Buddy. Wearing red collar with tag. Last seen near the park jogging trail early morning.', 'pet', 'Taman Tasik Permaisuri, Cheras', 'uploads/dog6.jpg', 'lost', 0, 'active', 'active', '2026-04-12 10:00:00', 0, '2026-04-12', '200', 3.08780000, 101.72340000, 0, NULL, NULL, 0, NULL, NULL),
(66, 30, 'Found: Confused Foreign Tourist at Gold Souk', 'Name: Unknown\nAge: 70\nWearing: Traditional Japanese kimono\nDistinguishing features: Japanese female, cannot speak English or Arabic, has tour group badge\n\nFound elderly Japanese tourist separated from her group.', 'person', 'Gold Souk, Deira, Dubai, UAE', 'uploads/person8.jpg', 'found', 0, 'active', 'active', '2026-04-26 06:00:00', 0, '2026-04-26', '', 25.26970000, 55.30950000, 0, NULL, NULL, 0, NULL, NULL),
(67, 31, 'Found Purple Spectacles Near Lake Bench', 'Found a pair of purple-framed spectacles near the bench by the lake. Prescription lenses with minus power. Kept safely at the reception desk.', 'item', 'Putrajaya Lake, Putrajaya', 'uploads/glasses1.jpg', 'found', 0, 'active', 'active', '2026-04-17 01:00:00', 0, '2026-04-17', '', 2.93530000, 101.69420000, 0, NULL, NULL, 0, NULL, NULL),
(68, 31, 'Lost Grey Toyota Vios', 'Vehicle Type: Car\nPlate Number: WA 1223 E\nVehicle Color: Grey\n\nGrey Toyota Vios with a small scratch on the left door. Has a Grab driver sticker on rear windscreen. Went missing from roadside near mamak.', 'vehicle', 'Klang, Selangor', 'uploads/car7.jpg', 'lost', 0, 'active', 'active', '2026-04-18 14:00:00', 0, '2026-04-18', '400', 3.04490000, 101.44500000, 0, NULL, NULL, 0, NULL, NULL),
(69, 32, 'Found Small Turtle Near Drain', 'Pet Type: Other\nPet Name: Unknown\nBreed: Red-eared Slider\nColor: Green with red markings\n\nFound a small red-eared slider turtle near the drain. Very tame, appears to be a pet. Keeping it in a container with water.', 'pet', 'Ampang, Selangor', 'uploads/turtle1.jpg', 'found', 0, 'active', 'active', '2026-04-21 02:00:00', 0, '2026-04-21', '', 3.15480000, 101.76230000, 0, NULL, NULL, 0, NULL, NULL),
(70, 32, 'Lost Louis Vuitton Handbag', 'Brown LV monogram handbag, medium size. Contains a passport, cosmetics and a Singaporean phone. Lost in the terminal area after check-in. Very urgent.', 'item', 'Changi Airport, Singapore', 'uploads/handbag2.jpg', 'lost', 0, 'active', 'active', '2026-04-12 00:00:00', 0, '2026-04-12', '1000', 1.36440000, 103.99150000, 0, NULL, NULL, 0, NULL, NULL),
(71, 33, 'Lost Black Yamaha Y15ZR', 'Vehicle Type: Motorcycle\nPlate Number: VBB 4433\nVehicle Color: Black and yellow\n\nBlack and yellow Yamaha Y15ZR with custom exhaust and race stickers on the fairing. Went missing from apartment parking at 2AM.', 'vehicle', 'Cyberjaya, Selangor', 'uploads/motorcycle5.jpg', 'lost', 0, 'active', 'active', '2026-04-19 18:00:00', 0, '2026-04-20', '400', 2.92130000, 101.65590000, 0, NULL, NULL, 0, NULL, NULL),
(72, 33, 'Missing: Father Named Ah Keng', 'Name: Keng Wei Lin\nAge: 48\nLast seen wearing: White polo shirt, dark jeans\nDistinguishing features: Stocky build, dragon tattoo on left forearm\n\nDid not return home after leaving for work. Phone is unreachable.', 'person', 'Cheras, Kuala Lumpur', 'uploads/person9.jpg', 'lost', 0, 'active', 'active', '2026-04-23 14:00:00', 0, '2026-04-23', '100', 3.08500000, 101.75170000, 0, NULL, NULL, 0, NULL, NULL),
(73, 34, 'Lost Pastel Green Nintendo Switch', 'Pastel green Nintendo Switch OLED with a custom case covered in Animal Crossing stickers. Contains 3 game cartridges inside the case. Lost on the train.', 'item', 'Bangsar LRT Station, KL', 'uploads/switch1.jpg', 'lost', 0, 'active', 'active', '2026-04-19 12:00:00', 0, '2026-04-19', '150', 3.12900000, 101.67360000, 0, NULL, NULL, 0, NULL, NULL),
(74, 34, 'Found Kitten in Apartment Corridor', 'Pet Type: Cat\nPet Name: Unknown\nBreed: Mixed breed\nColor: Black and white\n\nFound a tiny black and white kitten meowing loudly in the apartment corridor. Around 2 months old. Very hungry. Currently being nursed.', 'pet', 'Ara Damansara, Petaling Jaya', 'uploads/kitten1.jpg', 'found', 0, 'active', 'active', '2026-04-20 00:00:00', 0, '2026-04-20', '', 3.12280000, 101.57680000, 0, NULL, NULL, 0, NULL, NULL),
(75, 35, 'Found Bicycle with Broken Chain', 'Vehicle Type: Bicycle\nPlate Number: N/A\nVehicle Color: Orange\n\nFound an orange bicycle with a broken chain leaning against the wall near the taman entrance. Appears abandoned for several days.', 'vehicle', 'Taman Melawati, Kuala Lumpur', 'uploads/bicycle3.jpg', 'found', 0, 'active', 'active', '2026-04-23 02:00:00', 0, '2026-04-23', '', 3.21760000, 101.75430000, 0, NULL, NULL, 0, NULL, NULL),
(76, 35, 'Lost Vintage Film Camera Olympus OM-1', 'Black Olympus OM-1 film camera with a 50mm f1.4 lens. Has a small dent on the top plate. Very sentimental, belonged to my late father. Lost at the flea market.', 'item', 'Amcorp Mall Flea Market, PJ', 'uploads/camera2.jpg', 'lost', 0, 'active', 'active', '2026-04-21 06:00:00', 0, '2026-04-21', '200', 3.10460000, 101.63780000, 0, NULL, NULL, 0, NULL, NULL),
(77, 36, 'Lost Beagle Named Cookie', 'Pet Type: Dog\nPet Name: Cookie\nBreed: Beagle\nColor: Tricolor brown, black and white\n\nBeagle with long floppy ears. Loves to follow scents. Wearing red collar with name tag. Escaped through the front door.', 'pet', 'Pandan Jaya, Kuala Lumpur', 'uploads/dog7.jpg', 'lost', 0, 'active', 'active', '2026-04-16 02:00:00', 0, '2026-04-16', '200', 3.12450000, 101.74340000, 0, NULL, NULL, 0, NULL, NULL),
(78, 36, 'Found Green Thermos Flask at Gym', 'Found a dark green Thermos flask with a dented bottom near the gym lockers. Has initials MR scratched on the bottom. Currently at the gym front desk.', 'item', 'Sunway Pyramid, Selangor', 'uploads/flask2.jpg', 'found', 0, 'active', 'active', '2026-04-13 09:00:00', 0, '2026-04-13', '', 3.07340000, 101.60790000, 0, NULL, NULL, 0, NULL, NULL),
(79, 37, 'Found: Schoolgirl Alone at Bus Stop at Night', 'Name: Unknown\nAge: 12\nWearing: Blue school pinafore, white shoes\nDistinguishing features: Pigtails, Hello Kitty backpack\n\nFound a schoolgirl alone at the bus stop at 9PM. Says she missed her bus. Currently at the nearby sundry shop.', 'person', 'Kepong Baru, Kuala Lumpur', 'uploads/child3.jpg', 'found', 0, 'active', 'active', '2026-04-24 13:00:00', 0, '2026-04-24', '', 3.20520000, 101.63450000, 0, NULL, NULL, 0, NULL, NULL),
(80, 37, 'Lost Red and Black Honda CBR250', 'Vehicle Type: Motorcycle\nPlate Number: WM 9923 C\nVehicle Color: Red and black\n\nRed and black Honda CBR250 with a custom windshield. Has a sticker of a phoenix on the fuel tank. Went missing from the roadside.', 'vehicle', 'Kepong, Kuala Lumpur', 'uploads/motorcycle6.jpg', 'lost', 0, 'active', 'active', '2026-04-22 15:00:00', 0, '2026-04-22', '400', 3.21260000, 101.63700000, 0, NULL, NULL, 0, NULL, NULL),
(81, 38, 'Lost Samsonite Luggage — Red 28 Inch', 'Red Samsonite Spinner 28-inch luggage with a TSA lock. Has a yellow ribbon tied to the handle for identification. Contains 2 weeks of clothes and important documents. Lost at the airport carousel.', 'item', 'KLIA Terminal 1, Sepang', 'uploads/luggage1.jpg', 'lost', 0, 'active', 'active', '2026-04-18 03:00:00', 0, '2026-04-18', '300', 2.74560000, 101.70950000, 0, NULL, NULL, 0, NULL, NULL),
(82, 38, 'Found: Sleeping Man at Bus Terminal', 'Name: Unknown\nAge: 30\nWearing: Brown shirt, torn jeans, no shoes\nDistinguishing features: Small backpack, appears disoriented, cannot remember where he is from\n\nFound at the bus terminal for 2 days. Currently being assisted by volunteers.', 'person', 'Puduraya, Kuala Lumpur', 'uploads/person10.jpg', 'found', 0, 'active', 'active', '2026-04-26 00:00:00', 0, '2026-04-26', '', 3.14340000, 101.70130000, 0, NULL, NULL, 0, NULL, NULL),
(83, 39, 'Lost Corgi Named Waffles', 'Pet Type: Dog\nPet Name: Waffles\nBreed: Pembroke Welsh Corgi\nColor: Golden and white\n\nCorgi with short legs and a very fluffy butt. Wearing a blue collar. Very playful and will approach strangers. Escaped from the yard.', 'pet', 'Taman Desa, Kuala Lumpur', 'uploads/dog8.jpg', 'lost', 0, 'active', 'active', '2026-04-17 08:00:00', 0, '2026-04-17', '300', 3.10280000, 101.68620000, 0, NULL, NULL, 0, NULL, NULL),
(84, 39, 'Found Car Keys with Toyota Logo Near Lift', 'Vehicle Type: Car\nPlate Number: Unknown\nVehicle Color: Unknown\n\nFound Toyota car keys with a purple rabbit keychain near the lift lobby on level B2. Handed to security guard.', 'vehicle', 'AEON Mall Kuching', 'uploads/keys1.jpg', 'found', 0, 'active', 'active', '2026-04-21 07:00:00', 0, '2026-04-21', '', 1.49270000, 110.37390000, 0, NULL, NULL, 0, NULL, NULL),
(85, 40, 'Lost Kindle Paperwhite', 'Black Kindle Paperwhite 11th gen in a dark green leather case. Has over 200 books downloaded. Lost at the airport lounge while waiting for boarding.', 'item', 'Penang International Airport', 'uploads/kindle1.jpg', 'lost', 0, 'active', 'active', '2026-04-20 05:00:00', 0, '2026-04-20', '80', 5.29770000, 100.27620000, 0, NULL, NULL, 0, NULL, NULL),
(86, 40, 'Found Hamster Abandoned in Shoebox', 'Pet Type: Other\nPet Name: Unknown\nBreed: Syrian Hamster\nColor: Golden brown\n\nFound a golden hamster inside a shoebox near the rubbish bin. Someone may have abandoned it. Currently being kept alive with food and water.', 'pet', 'Kepong, Kuala Lumpur', 'uploads/hamster1.jpg', 'found', 0, 'active', 'active', '2026-04-21 04:00:00', 0, '2026-04-21', '', 3.21260000, 101.63700000, 0, NULL, NULL, 0, NULL, NULL),
(87, 41, 'Lost Blue Ducati Monster', 'Vehicle Type: Motorcycle\nPlate Number: VGA 1122\nVehicle Color: Blue\n\nBlue Ducati Monster 797. Has a custom seat with red stitching and a Ducati branded tank bag. Went missing from the hotel parking.', 'vehicle', 'KLCC, Kuala Lumpur', 'uploads/motorcycle7.jpg', 'lost', 0, 'active', 'active', '2026-04-19 15:30:00', 0, '2026-04-19', '2000', 3.15790000, 101.71160000, 0, NULL, NULL, 0, NULL, NULL),
(88, 41, 'Missing: Malaysian Student Abroad', 'Name: Ahmad Syazwan\nAge: 23\nLast seen wearing: White kandura, carrying laptop bag\nDistinguishing features: Short, dark complexion, small beard\n\nMalaysian student on exchange program. Did not attend classes for 3 days.', 'person', 'Dubai International Academic City, UAE', 'uploads/person11.jpg', 'lost', 0, 'active', 'active', '2026-04-22 02:00:00', 0, '2026-04-22', '300', 25.06570000, 55.36720000, 0, NULL, NULL, 0, NULL, NULL),
(89, 42, 'Found Purple Water Bottle at Gym', 'Found a purple Hydro Flask with stickers on it near the gym entrance. Has a dent on the bottom. Left at the gym reception.', 'item', 'Sunway Pyramid, Subang Jaya', 'uploads/bottle1.jpg', 'found', 0, 'active', 'active', '2026-04-13 08:30:00', 0, '2026-04-13', '', 3.07340000, 101.60790000, 0, NULL, NULL, 0, NULL, NULL),
(90, 42, 'Lost African Grey Parrot Named Koko', 'Pet Type: Bird\nPet Name: Koko\nBreed: African Grey Parrot\nColor: Grey with red tail\n\nLost African Grey Parrot that can say a few words including his own name. Lost during a power outage when the cage was accidentally left open.', 'pet', 'Ampang, Selangor', 'uploads/parrot1.jpg', 'lost', 0, 'active', 'active', '2026-04-15 13:00:00', 0, '2026-04-15', '500', 3.15480000, 101.76230000, 0, NULL, NULL, 0, NULL, NULL),
(91, 43, 'Found Motorcycle Helmet at Petrol Station', 'Vehicle Type: Motorcycle\nPlate Number: N/A\nVehicle Color: N/A\n\nFound a black and yellow full-face helmet sitting on top of a petrol pump. Appears to have been left behind by accident.', 'vehicle', 'Jalan Sultan Ahmad Shah, Penang', 'uploads/helmet1.jpg', 'found', 0, 'active', 'active', '2026-04-24 08:00:00', 0, '2026-04-24', '', 5.42340000, 100.32890000, 0, NULL, NULL, 0, NULL, NULL),
(92, 43, 'Lost Fossil Gen 6 Smartwatch', 'Rose gold Fossil Gen 6 smartwatch with a brown leather strap. Has my initials MTL engraved on the back. Lost at the restaurant table.', 'item', 'Gurney Plaza, Penang', 'uploads/smartwatch1.jpg', 'lost', 0, 'active', 'active', '2026-04-21 12:00:00', 0, '2026-04-21', '200', 5.43670000, 100.31330000, 0, NULL, NULL, 0, NULL, NULL),
(93, 44, 'Found Stray Mixed Breed Dog Near Highway', 'Pet Type: Dog\nPet Name: Unknown\nBreed: Mixed breed\nColor: Brown and white\n\nFound a brown and white mixed breed dog wandering near the highway fence. Appears friendly and domesticated. No collar.', 'pet', 'Pasar Satok, Kuching', 'uploads/dog9.jpg', 'found', 0, 'active', 'active', '2026-04-16 01:30:00', 0, '2026-04-16', '', 1.54320000, 110.34780000, 0, NULL, NULL, 0, NULL, NULL),
(94, 44, 'Missing: Old Uncle with Dementia', 'Name: Encik Lim Ah Kow\nAge: 78\nLast seen wearing: White singlet, dark blue shorts, slippers\nDistinguishing features: Very thin, walks slowly, hearing aid in left ear\n\nWandered away from home during the night. Suffers from early dementia.', 'person', 'Kepong, Kuala Lumpur', 'uploads/person12.jpg', 'lost', 0, 'active', 'active', '2026-04-21 22:00:00', 0, '2026-04-22', '100', 3.21260000, 101.63700000, 0, NULL, NULL, 0, NULL, NULL),
(95, 45, 'Lost Pastel Pink Laptop Bag', 'Pastel pink laptop bag brand Tomtoc. Contains a 13-inch MacBook Pro, charger and a small pouch with cables. Lost on the bus.', 'item', 'Rapid KL Bus, Petaling Jaya', 'uploads/laptopbag1.jpg', 'lost', 0, 'active', 'active', '2026-04-22 09:30:00', 0, '2026-04-22', '150', 3.10730000, 101.60680000, 0, NULL, NULL, 0, NULL, NULL),
(96, 45, 'Found Pink Scooter Helmet Near Minimart', 'Vehicle Type: Motorcycle\nPlate Number: N/A\nVehicle Color: N/A\n\nFound a pink full-face helmet with flower stickers leaning against the wall outside the minimart. Has been there for 3 days.', 'vehicle', 'Taman Bahagia, Petaling Jaya', 'uploads/helmet2.jpg', 'found', 0, 'active', 'active', '2026-04-24 01:00:00', 0, '2026-04-24', '', 3.10600000, 101.63200000, 0, NULL, NULL, 0, NULL, NULL),
(97, 46, 'Missing: Backpacker Tourist Named James', 'Name: James Miller\nAge: 26\nLast seen wearing: Orange hiking shirt, khaki pants, large backpack\nDistinguishing features: Caucasian, red beard, very tall\n\nAmerican tourist who was supposed to check out but never appeared. Belongings still in room.', 'person', 'George Town, Penang', 'uploads/person13.jpg', 'lost', 0, 'active', 'active', '2026-04-20 04:00:00', 0, '2026-04-20', '100', 5.41410000, 100.32880000, 0, NULL, NULL, 0, NULL, NULL),
(98, 46, 'Found Monitor Lizard in Garden', 'Pet Type: Other\nPet Name: Unknown\nBreed: Monitor Lizard\nColor: Dark grey\n\nFound a large monitor lizard in the garden. Not injured. Unsure if it is a pet or wild. Reporting here just in case someone is missing theirs.', 'pet', 'Balik Pulau, Penang', 'uploads/lizard1.jpg', 'found', 0, 'active', 'active', '2026-04-23 03:00:00', 0, '2026-04-23', '', 5.33560000, 100.23450000, 0, NULL, NULL, 0, NULL, NULL),
(99, 47, 'Lost Prayer Mat and Tasbih Bag', 'A cream coloured prayer mat with blue floral pattern inside a cream drawstring bag. Also contains a gold tasbih. Very sentimental. Lost at the airport surau.', 'item', 'KLIA2 Surau, Sepang', 'uploads/prayermat1.jpg', 'lost', 0, 'active', 'active', '2026-04-19 06:00:00', 0, '2026-04-19', '50', 2.74560000, 101.70950000, 0, NULL, NULL, 0, NULL, NULL),
(100, 47, 'Lost White Perodua Bezza', 'Vehicle Type: Car\nPlate Number: PHN 3321\nVehicle Color: White\n\nWhite Perodua Bezza with a small prayer sticker on the dashboard visible from outside. Has a Baby on Board sign. Went missing from apartment parking.', 'vehicle', 'Bangi, Selangor', 'uploads/car8.jpg', 'lost', 0, 'active', 'active', '2026-04-20 17:00:00', 0, '2026-04-21', '300', 2.99560000, 101.77950000, 0, NULL, NULL, 0, NULL, NULL),
(101, 48, 'Lost Maine Coon Cat Named Titan', 'Pet Type: Cat\nPet Name: Titan\nBreed: Maine Coon\nColor: Brown tabby\n\nLarge fluffy Maine Coon, very friendly. Wearing a brown leather collar. Escaped when contractor left the door open. Responds to his name.', 'pet', 'Mont Kiara, Kuala Lumpur', 'uploads/cat5.jpg', 'lost', 0, 'active', 'active', '2026-04-18 07:00:00', 0, '2026-04-18', '250', 3.17260000, 101.65020000, 0, NULL, NULL, 0, NULL, NULL),
(102, 48, 'Found Rayban Sunglasses in Changing Room', 'Found a pair of Ray-Ban Aviator sunglasses in the gym changing room. Gold frame with green lenses. Kept at the gym reception.', 'item', 'Celebrity Fitness, Mont Kiara', 'uploads/sunglasses2.jpg', 'found', 0, 'active', 'active', '2026-04-23 04:00:00', 0, '2026-04-23', '', 3.17260000, 101.65020000, 0, NULL, NULL, 0, NULL, NULL),
(103, 49, 'Lost Black Yamaha NMAX', 'Vehicle Type: Motorcycle\nPlate Number: WUY 6634\nVehicle Color: Black\n\nBlack Yamaha NMAX 155. Has a small scratch on the left mirror. Custom black matte helmet stored in the top box. Went missing overnight.', 'vehicle', 'Setapak, Kuala Lumpur', 'uploads/motorcycle8.jpg', 'lost', 0, 'active', 'active', '2026-04-20 15:00:00', 0, '2026-04-20', '400', 3.20120000, 101.71870000, 0, NULL, NULL, 0, NULL, NULL),
(104, 49, 'Found: Injured Foreign Worker Near Construction Site', 'Name: Unknown\nAge: 25\nWearing: Blue safety vest, hard hat, work boots\nDistinguishing features: Appears to be from Bangladesh, laceration on forehead\n\nFound injured worker near construction site. Ambulance called. Posting here to contact employer.', 'person', 'Bayan Lepas, Penang', 'uploads/person14.jpg', 'found', 0, 'active', 'active', '2026-04-25 07:00:00', 0, '2026-04-25', '', 5.29780000, 100.26890000, 0, NULL, NULL, 0, NULL, NULL),
(105, 50, 'Lost Brown Leather Satchel', 'Brown leather satchel with brass buckles. Contains a journal, pencil case and a vintage film camera. Lost on the London Underground.', 'item', 'London Underground, UK', 'uploads/bag2.jpg', 'lost', 0, 'active', 'active', '2026-04-17 10:30:00', 0, '2026-04-17', '100', 51.50740000, -0.12780000, 0, NULL, NULL, 0, NULL, NULL),
(106, 50, 'Lost Maltese Dog Named Cotton', 'Pet Type: Dog\nPet Name: Cotton\nBreed: Maltese\nColor: Pure white\n\nPure white toy Maltese, 1 year old. Wearing a diamond-studded collar. Very timid. Went missing from the hotel lobby area.', 'pet', 'Palm Jumeirah, Dubai, UAE', 'uploads/dog10.jpg', 'lost', 0, 'active', 'active', '2026-04-20 10:00:00', 0, '2026-04-20', '1000', 25.11240000, 55.13900000, 0, NULL, NULL, 0, NULL, NULL),
(107, 51, 'Found Abandoned Bicycle Near Lake', 'Vehicle Type: Bicycle\nPlate Number: N/A\nVehicle Color: Red and silver\n\nFound a red and silver mountain bicycle leaning against a tree near the lake. No lock. Has been there for 3 days with no owner in sight.', 'vehicle', 'Taman Tasik Titiwangsa, KL', 'uploads/bicycle4.jpg', 'found', 0, 'active', 'active', '2026-04-22 00:00:00', 0, '2026-04-22', '', 3.17260000, 101.70660000, 0, NULL, NULL, 0, NULL, NULL),
(108, 51, 'Lost Bose QuietComfort 45 Headphones', 'Black Bose QC45 in a black zip case. Has a small white sticker on the right ear cup with my name. Lost at the airport boarding gate.', 'item', 'Hamad International Airport, Qatar', 'uploads/headphones2.jpg', 'lost', 0, 'active', 'active', '2026-04-19 02:00:00', 0, '2026-04-19', '200', 25.27310000, 51.60830000, 0, NULL, NULL, 0, NULL, NULL),
(109, 52, 'Found Stray Cat with Pink Collar', 'Pet Type: Cat\nPet Name: Unknown\nBreed: Mixed breed\nColor: Grey and white\n\nFound a grey and white cat with a pink collar near the housing area. Has a tag but the name is faded. Currently being kept safely indoors.', 'pet', 'Bandar Sri Damansara, KL', 'uploads/cat6.jpg', 'found', 0, 'active', 'active', '2026-04-23 11:00:00', 0, '2026-04-23', '', 3.18500000, 101.62300000, 0, NULL, NULL, 0, NULL, NULL),
(110, 52, 'Missing: Grandmother Named Mak Cik Rohaya', 'Name: Rohaya Binti Ismail\nAge: 68\nLast seen wearing: Green baju kurung, white tudung bawal\nDistinguishing features: Short, slightly hunched, carries prayer beads\n\nWent to the market alone and has not returned. Not answering calls.', 'person', 'Pasar Klang, Selangor', 'uploads/person15.jpg', 'lost', 0, 'active', 'active', '2026-04-22 03:00:00', 0, '2026-04-22', '100', 3.04570000, 101.44780000, 0, NULL, NULL, 0, NULL, NULL),
(111, 53, 'Lost Portable Fan', '', 'item', 'M17, Sultan Abdul Aziz Shah Airport (SZB), Skypark Subang Terminal, 47200 Subang, Selangor, Malaysia', 'uploads/1780843946_kipas.webp', 'lost', 0, 'removed', 'active', '2026-06-07 14:52:26', 0, '2026-06-06', '10', 3.12815950, 101.55231550, 0, NULL, NULL, 1, '2026-06-08 02:12:04', 'collected'),
(112, 53, 'Lost Casio Watch', '', 'item', 'Miami Beach, FL, USA', 'uploads/1780855167_casio.webp', 'lost', 0, 'removed', 'active', '2026-06-07 17:59:27', 0, '2026-06-07', '10', 25.79065400, -80.13004550, 0, NULL, NULL, 1, '2026-06-08 02:12:04', 'collected');

-- --------------------------------------------------------

--
-- Table structure for table `listing_images`
--

CREATE TABLE `listing_images` (
  `id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `claim_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `claim_id`, `sender_id`, `message`, `created_at`) VALUES
(1, 2, 3, 'yo sup bro, found yo shoe', '2026-06-02 16:29:11'),
(2, 3, 3, 'yo this my item', '2026-06-02 16:32:56'),
(3, 3, 3, 'okeh', '2026-06-02 16:36:03'),
(4, 3, 3, 'orait dawg', '2026-06-02 16:38:05'),
(5, 4, 3, 'aku punya ni', '2026-06-02 16:55:45'),
(6, 4, 4, 'yowww serious ahh', '2026-06-02 17:03:38'),
(7, 4, 3, 'ye leee, meh location aku nak mai', '2026-06-03 08:28:35'),
(8, 4, 4, 'nah : *****************************', '2026-06-03 08:29:06'),
(9, 5, 3, 'serious la', '2026-06-04 07:13:22'),
(10, 6, 53, 'biaq betoi hang punya', '2026-06-07 14:53:58'),
(11, 6, 54, 'yowwww seres aa kau takcaye', '2026-06-07 14:54:21'),
(12, 6, 53, 'ada nampak aku men men ka', '2026-06-07 14:54:36'),
(13, 6, 54, 'adela sket', '2026-06-07 14:54:48'),
(14, 6, 53, 'mana nak jumpa ni? malam free akk?', '2026-06-07 14:55:01'),
(15, 6, 54, 'free je, kat mana? suraya on??', '2026-06-07 14:55:18'),
(16, 6, 53, 'onnnn, nak minum tak?', '2026-06-07 14:55:34'),
(17, 6, 54, 'boleh je tapi kau belanja a', '2026-06-07 14:55:46'),
(18, 6, 53, 'okay set masa kul berapa nak jumpa', '2026-06-07 14:56:01'),
(19, 6, 54, 'pas maghrib a', '2026-06-07 14:56:11'),
(20, 6, 53, 'okay onzzzzz', '2026-06-07 14:56:23'),
(21, 7, 53, 'seres a ada', '2026-06-07 18:00:02'),
(22, 7, 54, 'ye a do aku jumpa haritu, meh location aku free skrg', '2026-06-07 18:00:34'),
(23, 7, 53, 'thankyou do, aku kat sini skang : *****', '2026-06-07 18:00:51'),
(24, 7, 54, 'orait\" otw', '2026-06-07 18:00:59');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL COMMENT 'boost or reward',
  `amount` decimal(10,2) NOT NULL,
  `method` varchar(50) DEFAULT 'online' COMMENT 'online or cash',
  `status` varchar(50) DEFAULT 'pending' COMMENT 'pending, paid, failed',
  `reference` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `user_id`, `listing_id`, `type`, `amount`, `method`, `status`, `reference`, `created_at`) VALUES
(1, 5, 19, 'boost', 5.00, 'online', 'paid', 'SIM-6A24523856606', '2026-06-07 01:00:40');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` varchar(20) DEFAULT 'user',
  `profile_pic` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`, `role`, `profile_pic`) VALUES
(1, 'Administrator', 'admin@lostfound.com', '$2y$10$YqFgNNe2EDE02PtMt4Oozezgn5BfQazmrWDRxIdIuArnfKPMgwapq', '2026-06-06 17:41:12', 'admin', NULL),
(2, 'Ipan', 'ipan@gmail.com', '$2y$10$/1kgZgBoCSrG1LUpjlghW.vqK4Ioy.XECxAe/89tOlmPOIZ5UEQYa', '2026-06-06 17:41:12', 'user', 'uploads/profile_2_1780817795.jpeg'),
(3, 'Syafiq Hakim', 'user3@gmail.com', '$2y$10$0KFUMqI8hcBwfOE5m3aZZe54T3VtJOmJK1e.BsV2hUFBGwz61q4Vy', '2026-06-06 17:41:12', 'user', NULL),
(4, 'Aina Sofea', 'user4@gmail.com', '$2y$10$jwX7n1eYURIyCxtHZ0II7ubw/XazoNzPFcdEzlYAgYmq9VvZdEXgu', '2026-06-06 17:41:12', 'user', NULL),
(5, 'Muhammad Danial', 'user5@gmail.com', '$2y$10$LQ8TGyReQ16OBCgAzTQLbe4S76nA9wIw9VG.dE17pa9J6KA7mO7FC', '2026-06-06 17:41:12', 'user', NULL),
(6, 'Nurul Hidayah', 'user6@gmail.com', '$2y$10$AxsyOKKD92CHEGea6MZSFeQ1WkkqwhrkaQr5mGDfMzKCWozTEW7fu', '2026-06-06 17:41:12', 'user', NULL),
(7, 'Haziq Amsyar', 'user7@gmail.com', '$2y$10$EziL4CjfcgxExZR2tbnVxer9akbEFl.xBrZtfNEiNH9u6CWI99oFC', '2026-06-06 17:41:12', 'user', NULL),
(8, 'Siti Aisyah', 'user8@gmail.com', '$2y$10$YNLYmPhFYuvWyIyEpVbVu.sLR0TYR4FDKvFtKTKT.fEI6I5y3ty4m', '2026-06-06 17:41:12', 'user', NULL),
(9, 'Daniel Lee', 'user9@gmail.com', '$2y$10$zDKicElgm9/E0ij3jwsdROg8GUM66FvEadKhkJnBiweI1ub1xv0/i', '2026-06-06 17:41:12', 'user', NULL),
(10, 'Farah Nadia', 'user10@gmail.com', '$2y$10$K4LbfGkdCiOrUfHQunA9ve..RZlOkm5TUinrIIQkaRcq4yyO0JvJa', '2026-06-06 17:41:12', 'user', NULL),
(11, 'Amirul Hakim', 'user11@gmail.com', '$2y$10$R5WlBBaJB7KyWT28ptSQhuFlE1M5IsMpfxLHKXh9gxQ3bRCT94eWq', '2026-06-06 17:41:12', 'user', NULL),
(12, 'Nadia Iman', 'user12@gmail.com', '$2y$10$8CmUdN.qg2W6Tfsc06.RFeBaOXjiH8KbUDyZVGppbXpHqojyIIPWy', '2026-06-06 17:41:12', 'user', NULL),
(13, 'Syazwan Rahman', 'user13@gmail.com', '$2y$10$7OXKCnFOsqCWIUQVYTyHIO9d2Qb9kemaChow/aeGTzez2y27wkS0i', '2026-06-06 17:41:12', 'user', NULL),
(14, 'Puteri Balqis', 'user14@gmail.com', '$2y$10$5F0WFXMajXG02bKhDb4XkuxPorHW5ZLlCoyMNuABSESCHg1WCAlwO', '2026-06-06 17:41:12', 'user', NULL),
(15, 'Zulkifli Ahmad', 'user15@gmail.com', '$2y$10$SiCgrFNvDJ3qBZnejI5TI.Uxuzp50CLURj80mmTJTrUZCuQ5.5Mnm', '2026-06-06 17:41:12', 'user', NULL),
(16, 'Nur Amirah', 'user16@gmail.com', '$2y$10$QnKHgBZDS72MiOupWHthBefXeg7Mxtj8kxn0LwDvEsefIIIzCPB8W', '2026-06-06 17:41:12', 'user', NULL),
(17, 'Irfan Ali', 'user17@gmail.com', '$2y$10$KMk4ysJydqi6Ca5NC92LkeJ8MiITm5wz.rry/080aiOhmTxumGx/K', '2026-06-06 17:41:12', 'user', NULL),
(18, 'Hannah Tan', 'user18@gmail.com', '$2y$10$f6EGndg/v6X4720BK1nuIeaI16YsdOB9vICgNZ/0qZcvgVh0Ul9mi', '2026-06-06 17:41:12', 'user', NULL),
(19, 'Jason Ng', 'user19@gmail.com', '$2y$10$d1a4bUH1NLxwmVONA7QJQOnt6xV01ztczbPXPPeaK.A1v5SfAKRqW', '2026-06-06 17:41:12', 'user', NULL),
(20, 'Mei Ling Wong', 'user20@gmail.com', '$2y$10$hD.I2IQYlPDsdwwZDiJkYuWDkFi5N4lY9YLXjbKi5COhJT1BXXIim', '2026-06-06 17:41:12', 'user', NULL),
(21, 'Adam Nor', 'user21@gmail.com', '$2y$10$kTvOW1VRg2fOu9QG3.uHI.JRvlyP7A6iny0ye/8Fp5k7yc2ZsgJu2', '2026-06-06 17:41:12', 'user', NULL),
(22, 'Bella Wong', 'user22@gmail.com', '$2y$10$gQfDhPTew4H/Ig1Jz7p9kONcyqQDkio2VTE6pE6Cw4g.aQykceyhe', '2026-06-06 17:41:12', 'user', NULL),
(23, 'Imran Shah', 'user23@gmail.com', '$2y$10$GXQNZG1JJaDFtkrcQeeYGeEBoJiqpMBWqT5SNJT9.k7gK0f.HC42y', '2026-06-06 17:41:12', 'user', NULL),
(24, 'Sophia Lee', 'user24@gmail.com', '$2y$10$0W2pXZU3IUmdG2lmrScbUuurD4UL4VuhsbyH2SqThOJHiiCYyvDW2', '2026-06-06 17:41:12', 'user', NULL),
(25, 'Arif Hamdan', 'user25@gmail.com', '$2y$10$OKisynNIE/rHVVqdxqabAuvh6YniHluipLoggxLlCzujyEXTMBgxG', '2026-06-06 17:41:12', 'user', NULL),
(26, 'Chloe Lim', 'user26@gmail.com', '$2y$10$54FWTOSOFIyxJWvm2o8gP.QtMfys2v6AE2zVkfXpmvkQ94t39a1/W', '2026-06-06 17:41:12', 'user', NULL),
(27, 'Farhan Aziz', 'user27@gmail.com', '$2y$10$zYqglblGaNIppIH0400agOZEZqSGeyHuwL2d1wxhbpq08IuA8ZCo2', '2026-06-06 17:41:12', 'user', NULL),
(28, 'Nur Syuhada', 'user28@gmail.com', '$2y$10$aqnJuhuuDi59GUXyzoVML..DtfIY.eQVeHEzO0ebBywGtd3JIKlri', '2026-06-06 17:41:12', 'user', NULL),
(29, 'Ryan Khoo', 'user29@gmail.com', '$2y$10$wt53izQnlTMnjx34H15pEOYV/pe1GD454x86QmyJKoOveUASXxysy', '2026-06-06 17:41:12', 'user', NULL),
(30, 'Zara Malik', 'user30@gmail.com', '$2y$10$gJO8fij/wCUNzvSxJfLHpenWcJG4oZiE7EUkbBQqq3kHjRS3Ydqj2', '2026-06-06 17:41:12', 'user', NULL),
(31, 'Elina Joseph', 'user31@gmail.com', '$2y$10$Up76yk23SPCIc1Fvmb62nOa93oNj.teQJiJPWNDkb1GJxbuC2cH4m', '2026-06-06 17:41:12', 'user', NULL),
(32, 'Marcus Tan', 'user32@gmail.com', '$2y$10$Q5ZFFaRmIGsda9hd7dR.BOSDgtOMUhXG/X7PWbAxRxr9nh8VI6aMO', '2026-06-06 17:41:12', 'user', NULL),
(33, 'Naufal Hakim', 'user33@gmail.com', '$2y$10$eDtfiXbxH49sZTBReg/Jjut38/vjyFIKbLddwdjlr/8d/Q1AqG3Cq', '2026-06-06 17:41:12', 'user', NULL),
(34, 'Alicia Tan', 'user34@gmail.com', '$2y$10$hXB300sOT5FdeXcLaXBJKOgBGKM/Hb2zUhPaQcnkuHL.GOXb5XyMm', '2026-06-06 17:41:12', 'user', NULL),
(35, 'Jonathan Lee', 'user35@gmail.com', '$2y$10$shKKzxmKOX1paQVtzG0YU.pgxvtwYUKFkvdD09S711Fm2Oldkqlqi', '2026-06-06 17:41:12', 'user', NULL),
(36, 'Haziq Asyraf', 'user36@gmail.com', '$2y$10$1AyeZRBIXYxpTE.WrXI8PemH8xoG.SNq3yw4yt8d.pfyAZNJCkuA6', '2026-06-06 17:41:12', 'user', NULL),
(37, 'Sabrina Wong', 'user37@gmail.com', '$2y$10$GGbV.aZUwnqHmE5W7csmk.NfEWknY3gG.UXMgBNIpy1NcsqKRfnLq', '2026-06-06 17:41:12', 'user', NULL),
(38, 'Faizal Rahman', 'user38@gmail.com', '$2y$10$yQLLLMsY.WZF1LGaTYAOQOfub/ygXsSuE0VbBirgvC3WUabiHrzYa', '2026-06-06 17:41:12', 'user', NULL),
(39, 'Melvin Ong', 'user39@gmail.com', '$2y$10$.TXKfYTnwhS/QiHTy2QuZevdXd0rZHyL9NRGY8IcC79M1xszwTV9e', '2026-06-06 17:41:12', 'user', NULL),
(40, 'Aqilah Rosli', 'user40@gmail.com', '$2y$10$qCkhzbjUJxyQV0k7Omxd6eAhW1V5yUFIWjrEVGSpvxT7osN0QrFxW', '2026-06-06 17:41:12', 'user', NULL),
(41, 'Daniel Ong', 'user41@gmail.com', '$2y$10$db0qlDZfX9u0BET27Oo6f.km7FGOVpGwQ9zuVjrP0GshTZBRjvPYe', '2026-06-06 17:41:12', 'user', NULL),
(42, 'Rania Zainal', 'user42@gmail.com', '$2y$10$.LHfillx0LARn.BcomH6iexoghWva9YCBg6c/90wt9QchW.HKPuCu', '2026-06-06 17:41:12', 'user', NULL),
(43, 'Mei Ling Tan', 'user43@gmail.com', '$2y$10$C9PNDZykMnjFoajbbRqQXOwWsWKkcb.uVX99zlfaJ7LtUSzLLM4yq', '2026-06-06 17:41:12', 'user', NULL),
(44, 'Brandon Lim', 'user44@gmail.com', '$2y$10$Fkd4KrXxil.vwJbs7p1TMOmxSWSiBFC5sRMtPsduB30zZGelJNBIe', '2026-06-06 17:41:12', 'user', NULL),
(45, 'Amelia Wong', 'user45@gmail.com', '$2y$10$fJT1lq8qaXXfkRW8SdU/n.Tkau9kkzQWk1Rf7xEm/to1Os9vPtP9K', '2026-06-06 17:41:12', 'user', NULL),
(46, 'Hassan Malik', 'user46@gmail.com', '$2y$10$SvmZwJYy.RhHK9/RHAbtlucGKSyz65dIoNToQl93G3NvS5lt6xczq', '2026-06-06 17:41:12', 'user', NULL),
(47, 'Nur Hafizah', 'user47@gmail.com', '$2y$10$5gTIvFk2slAsW/PVTQV0d.er2WjSZSHA.F5e.pwkJTHx39h/R6SmC', '2026-06-06 17:41:12', 'user', NULL),
(48, 'Kevin Wong', 'user48@gmail.com', '$2y$10$ONCJRexeOZdghtoP41Qy3eKCcq0ec4EchC/cmGOz/3CQgafAbQNXi', '2026-06-06 17:41:12', 'user', NULL),
(49, 'Liyana Rahman', 'user49@gmail.com', '$2y$10$/RWzumgv6NQOQNPj76bPheRcZhF4/OY5JXeSUcANg1jxGZ5Fu98CC', '2026-06-06 17:41:12', 'user', NULL),
(50, 'Jason Tan', 'user50@gmail.com', '$2y$10$Ru5NL2J1UAZL/VtHx1fuT.COWITvk7Xg7E1oQbiEhU9UAJKgh4sjS', '2026-06-06 17:41:12', 'user', NULL),
(51, 'Syed Arman', 'user51@gmail.com', '$2y$10$tz1B4cmNyAnq.Rh9M9ywI.A9QNXbnsERS/jrWWhaasm9H7e64J7Pq', '2026-06-06 17:41:12', 'user', NULL),
(52, 'Nur Aina', 'user52@gmail.com', '$2y$10$lL.Ts9U0b18v6B/PlMl6c.8DmaFAM0p1k32O2IMVSM9ESTLg2FoJK', '2026-06-06 17:41:12', 'user', NULL),
(53, 'AmirKacakXX', 'amir@gmail.com', '$2y$10$.5LLOWI96Yktf7OlRU7bQu/dbigCD6/jmovFMKV8pCU4Dxktz4Tpi', '2026-06-07 07:41:09', 'user', 'uploads/profile_53_1780818103.jpg'),
(54, 'Walif Kee Ni', 'walif67@gmail.com', '$2y$10$UQcHCo4c6SpVb55P2iho8OiLgu.UjXliCdh7jvA8p1t92i3h4fj9e', '2026-06-07 14:32:13', 'user', 'uploads/profile_54_1780843064.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `claims`
--
ALTER TABLE `claims`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_claims_listing` (`listing_id`),
  ADD KEY `fk_claims_user` (`claimant_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `listings`
--
ALTER TABLE `listings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_listings_user` (`user_id`);

--
-- Indexes for table `listing_images`
--
ALTER TABLE `listing_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `listing_id` (`listing_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `claims`
--
ALTER TABLE `claims`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `listings`
--
ALTER TABLE `listings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT for table `listing_images`
--
ALTER TABLE `listing_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `claims`
--
ALTER TABLE `claims`
  ADD CONSTRAINT `fk_claims_listing` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_claims_user` FOREIGN KEY (`claimant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `listings`
--
ALTER TABLE `listings`
  ADD CONSTRAINT `fk_listings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
