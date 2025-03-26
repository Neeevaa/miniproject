-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 19, 2025 at 05:53 PM
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
-- Database: `aromiq`
--

-- --------------------------------------------------------

--
-- Table structure for table `order_sequence`
--

CREATE TABLE `order_sequence` (
  `sequence_date` date NOT NULL,
  `last_seq` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_sequence`
--

INSERT INTO `order_sequence` (`sequence_date`, `last_seq`) VALUES
('2025-03-18', 3);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_admin`
--

CREATE TABLE `tbl_admin` (
  `adminid` int(3) NOT NULL,
  `adminuname` varchar(25) NOT NULL,
  `adminpswd` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_admin`
--

INSERT INTO `tbl_admin` (`adminid`, `adminuname`, `adminpswd`) VALUES
(101, 'admin', '1234'),
(102, 'kitchenadmin', '1234'),
(103, 'chef', '1234');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_bookings`
--

CREATE TABLE `tbl_bookings` (
  `booking_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `table_number` int(11) NOT NULL,
  `datetime` datetime NOT NULL,
  `people_count` int(11) NOT NULL,
  `special_request` text DEFAULT NULL,
  `special_option` varchar(100) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_bookings`
--

INSERT INTO `tbl_bookings` (`booking_id`, `name`, `table_number`, `datetime`, `people_count`, `special_request`, `special_option`, `status`, `created_at`) VALUES
(1, 'Andrea Byju', 3, '2025-08-31 01:30:00', 2, 'Romantic Background Music', 'Add ring in wine glass', 'Confirmed', '2025-03-17 23:19:17'),
(2, 'Elizabeth Mary Abraham', 3, '2025-05-20 08:13:00', 1, 'Add a chair for my baby to sit on', 'Customized cake options', 'Confirmed', '2025-03-19 02:44:56');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_feedback`
--

CREATE TABLE `tbl_feedback` (
  `feedbackId` int(4) NOT NULL,
  `orderid` int(6) NOT NULL,
  `rating` int(5) NOT NULL,
  `comments` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_fooditem`
--

CREATE TABLE `tbl_fooditem` (
  `itemid` int(11) NOT NULL,
  `itemname` varchar(80) NOT NULL,
  `category` varchar(50) NOT NULL,
  `price` float NOT NULL,
  `itemdescription` text NOT NULL,
  `itemdetailed` text NOT NULL,
  `itemimage` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_fooditem`
--

INSERT INTO `tbl_fooditem` (`itemid`, `itemname`, `category`, `price`, `itemdescription`, `itemdetailed`, `itemimage`) VALUES
(1, 'Pancakes with Maple Syrup', 'Starters', 100, 'Soft, fluffy pancakes drizzled with rich maple syrup for a sweet, comforting start.', '', '1742207669.jpeg'),
(2, 'Scrambled Eggs with Toast', 'Starters', 80, 'Light and creamy scrambled eggs served with crispy golden toast.', '', '1742209434.jpeg'),
(3, 'Fresh Fruit Platter with Yogurt', 'Starters', 120, 'A colorful assortment of fresh fruits paired with creamy yogurt.', '', '1742253228.jpeg'),
(4, 'Masala Chai', 'Beverages', 50, 'A spiced Indian tea with bold flavors of ginger and cardamom.', '', '1742254772.jpeg'),
(6, 'Choconut Smoothie', 'Desserts', 100, 'A thick and creamy chocolate and coconut blended smoothie.', '', '1742274316_67d8ff0c79db0.jpeg'),
(7, 'Mocha Coffee', 'Beverages', 80, 'A rich and frothy mix of coffee and chocolate flavors', '', '1742277647_67d90c0f65b4f.jpeg'),
(8, 'Lemon Soda', 'Beverages', 60, 'A fizzy and refreshing lemon-infused soda drink.', '', '1742277699_67d90c437ac4a.jpeg'),
(9, 'Mixed Berry Smoothie', 'Beverages', 80, 'A fruity smoothie blended with mixed fresh berries.', '', '1742277758_67d90c7e06e5c.jpeg'),
(11, 'Bagels with Cream Cheese', 'Starters', 130, 'Chewy bagels generously spread with smooth and tangy cream cheese.', '', '1742292123_67d9449bc9738.jpg'),
(12, 'Omelet with Multigrain Bread', 'Starters', 80, 'A fluffy, protein-packed omelet served with hearty multigrain bread.', '', '1742292169_67d944c9879a3.jpeg'),
(13, 'Cereal with Milk', 'Starters', 90, 'Crunchy cereal soaked in chilled milk for a quick and easy breakfast.', '', '1742292315_67d9455b2431b.jpeg'),
(16, 'Grilled Chicken with Herb Rice', 'Main Course', 210, 'Tender grilled chicken paired with fragrant, herb-infused rice.', '', '1742292788_67d9473495904.jpeg'),
(17, 'Brownies with Ice Cream ', 'Desserts', 90, 'Rich, fudgy brownies topped with creamy vanilla ice cream.', '', '1742396599_67dadcb75a3b2.jpeg'),
(18, 'Fruit Custard with Jelly', 'Desserts', 100, 'Silky custard mixed with fresh fruits and colorful jelly.', '', '1742396676_67dadd045c525.jpeg'),
(19, 'Chocolate Mousse', 'Desserts', 80, 'Moist chocolate cake with layers of rich chocolate ganache.', '', '1742396768_67dadd60c0f00.jpeg'),
(20, 'Blueberry Cheesecake ', 'Desserts', 90, 'Creamy cheesecake topped with tangy blueberry compote.', '', '1742396818_67dadd92edf61.jpeg'),
(21, 'Tiramisu', 'Desserts', 120, 'A coffee-flavored Italian dessert with layers of mascarpone cream.', '', '1742396910_67daddeeecd27.jpeg'),
(22, 'Gulab Jamun with Ice Cream', 'Desserts', 120, 'Soft, syrup-soaked dumplings paired with cold ice cream.', '', '1742396974_67dade2ec4cf5.jpeg'),
(23, 'Rice Kheer', 'Desserts', 80, 'A creamy Indian rice pudding infused with cardamom and nuts.', '', '1742397031_67dade675e88b.jpeg'),
(25, 'Mango Lassi ', 'Beverages', 80, 'A creamy, sweet yogurt drink infused with ripe mangoes.', '', '1742397151_67dadedfa5c3a.jpeg'),
(26, 'Cold Coffee', 'Beverages', 80, 'A chilled, refreshing coffee blended with milk and sugar.', '', '1742397197_67dadf0dd85f3.jpeg'),
(27, 'Ham and Cheese Sliders', 'Main Course', 220, 'Mini sandwiches filled with smoky ham and melted cheese.', '', '1742397252_67dadf444a0a4.jpeg'),
(28, 'Mediterranean Falafel Platter', 'Main Course', 230, 'Crispy falafels served with hummus, pita, and fresh salad.', '', '1742397301_67dadf75eee40.jpeg'),
(29, 'BBQ Chicken Skewers ', 'Main Course', 220, 'Smoky, grilled chicken skewers coated in tangy BBQ sauce.', '', '1742397358_67dadfaec86dd.jpeg'),
(30, 'Arabic Shawarma Wrap', 'Main Course', 120, 'Juicy, marinated meat wrapped in soft pita with garlic sauce.', '', '1742397405_67dadfdd32493.jpeg'),
(31, 'Hot Chocolate with Marshmallows', 'Beverages', 140, 'Creamy hot chocolate topped with fluffy marshmallows.', '', '67dae0616875d.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_fooditemdetailed`
--

CREATE TABLE `tbl_fooditemdetailed` (
  `ditemid` int(11) NOT NULL,
  `itemid` int(11) DEFAULT NULL,
  `itemdetailed` text DEFAULT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_fooditemdetailed`
--

INSERT INTO `tbl_fooditemdetailed` (`ditemid`, `itemid`, `itemdetailed`, `added_at`) VALUES
(1, 1, 'Fluffy and golden pancakes, made with a soft batter, cooked to perfection, and drizzled with rich, amber-colored maple syrup, offering a balance of sweetness and warmth in every bite.', '2025-03-17 10:34:29'),
(2, 2, 'Soft, creamy scrambled eggs made with a hint of butter and seasoning, served alongside crispy, golden-brown toast for a wholesome and satisfying breakfast.', '2025-03-17 11:03:54'),
(3, 3, 'A vibrant mix of seasonal fruits like apples, bananas, berries, and grapes, elegantly arranged and paired with smooth, creamy yogurt for a refreshing and healthy start to the day.', '2025-03-17 23:13:48'),
(4, 4, 'A spiced Indian tea made by boiling black tea with milk, aromatic spices like cardamom, cinnamon, and ginger, creating a warm and comforting drink full of rich flavors.', '2025-03-17 23:39:32'),
(6, 6, 'A deliciously thick and creamy smoothie made with chocolate, coconut milk, and nuts, blended to perfection for a rich and nutty flavor.', '2025-03-18 05:05:16'),
(7, 7, 'A bold coffee blend infused with rich chocolate syrup, topped with steamed milk and a frothy layer of foam, delivering a perfect balance of coffee and cocoa flavors.', '2025-03-18 06:00:47'),
(8, 8, 'A refreshing fizzy drink made with freshly squeezed lemon juice, soda water, and a hint of sugar and salt, offering a tangy and invigorating taste.', '2025-03-18 06:01:39'),
(9, 9, 'A vibrant and nutritious smoothie made with a blend of berries like strawberries, blueberries, and raspberries, combined with yogurt or milk for a refreshing drink.', '2025-03-18 06:02:38'),
(12, 11, 'Classic New York-style bagels, slightly crisp on the outside and chewy inside, generously spread with velvety cream cheese, offering a delicious combination of textures and flavors.', '2025-03-18 10:02:03'),
(13, 12, 'A fluffy omelet made with fresh eggs, loaded with veggies, cheese, or meats, served with hearty, fiber-rich multigrain bread for a nutritious and filling meal.', '2025-03-18 10:02:49'),
(16, 13, 'A classic breakfast option featuring crunchy cereal soaked in cold, creamy milk, with the option to add fruits or nuts for extra flavor and nutrition.', '2025-03-18 10:05:15'),
(19, 16, 'Tender, juicy grilled chicken marinated with aromatic herbs and spices, served over a bed of fragrant, herb-infused rice, creating a flavorful and well-balanced meal.', '2025-03-18 10:13:08'),
(20, 17, 'Fudgy, rich chocolate brownies served warm with a scoop of creamy vanilla ice cream, melting into the gooey goodness for an irresistible dessert.', '2025-03-19 15:03:19'),
(21, 18, 'A delightful combination of silky, chilled custard mixed with diced fresh fruits, layered with wobbly, colorful jelly for a refreshing and light dessert.', '2025-03-19 15:04:36'),
(22, 19, 'A decadent chocolate cake with moist layers, rich chocolate ganache, and a velvety chocolate mousse, delivering a melt-in-the-mouth experience.', '2025-03-19 15:06:08'),
(23, 20, 'A creamy, smooth cheesecake with a buttery graham cracker crust, topped with a luscious blueberry compote for the perfect balance of tangy and sweet flavors.', '2025-03-19 15:06:58'),
(24, 21, 'A classic Italian dessert made with espresso-soaked ladyfingers layered with velvety mascarpone cream and dusted with cocoa powder for a rich, coffee-infused indulgence.', '2025-03-19 15:08:30'),
(25, 22, 'Soft, syrup-soaked Indian dumplings made from khoya, served warm with a scoop of creamy vanilla ice cream for a delightful contrast of textures and temperatures.', '2025-03-19 15:09:34'),
(26, 23, 'A traditional Indian rice pudding made with slow-cooked rice, milk, sugar, and cardamom, garnished with nuts and saffron for a rich and aromatic dessert.', '2025-03-19 15:10:31'),
(27, 25, 'A creamy and refreshing Indian yogurt-based drink blended with ripe mangoes, sugar, and cardamom, offering a delightful mix of sweetness and tanginess.', '2025-03-19 15:12:31'),
(28, 26, 'A smooth and refreshing chilled coffee, blended with milk and sugar, sometimes topped with whipped cream for a creamy and energizing beverage.', '2025-03-19 15:13:17'),
(29, 27, 'Mini soft buns stuffed with smoky ham and melted cheese, brushed with butter, and baked until golden, making for a savory and delightful bite-sized snack.', '2025-03-19 15:14:12'),
(30, 28, 'Crispy, golden-brown chickpea fritters served with pita bread, hummus, fresh salad, and a tangy tahini dressing, delivering an authentic Mediterranean experience.', '2025-03-19 15:15:01'),
(31, 29, 'Succulent chunks of chicken, marinated in smoky barbecue sauce, skewered, and grilled to perfection, creating a mouthwatering, juicy treat with a charred exterior.', '2025-03-19 15:15:58'),
(32, 30, 'A Middle Eastern favorite featuring thinly sliced, marinated meat wrapped in soft pita bread with crunchy vegetables and creamy garlic sauce, bursting with bold flavors.', '2025-03-19 15:16:45'),
(33, 31, 'A comforting cup of creamy, velvety hot chocolate topped with soft, pillowy marshmallows that melt into the drink for an extra indulgent touch.', '2025-03-19 15:19:08');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_orders`
--

CREATE TABLE `tbl_orders` (
  `order_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mobile_number` varchar(15) NOT NULL,
  `table_number` int(11) NOT NULL,
  `payment_mode` varchar(50) NOT NULL,
  `order_status` enum('Pending','In Progress','Completed','Cancelled') DEFAULT 'Pending',
  `total_amount` decimal(10,2) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_status` enum('Paid','Pending') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_orders`
--

INSERT INTO `tbl_orders` (`order_id`, `customer_name`, `email`, `mobile_number`, `table_number`, `payment_mode`, `order_status`, `total_amount`, `order_date`, `payment_status`) VALUES
(30, 'Sunish Mathew', NULL, '8111962758', 3, 'card', '', 130.00, '2025-03-18 10:59:36', 'Pending'),
(31, 'Neeva Sunish Mathew', NULL, '3698521470', 2, 'upi', '', 190.00, '2025-03-18 12:03:14', 'Pending'),
(32, 'Milu Jiji', NULL, '8564239774', 2, 'upi', '', 80.00, '2025-03-18 12:09:06', 'Pending'),
(33, 'Mariya George', NULL, '7412589632', 3, 'upi', '', 220.00, '2025-03-19 04:45:50', 'Pending'),
(34, 'Veeva Sunish Mathew', NULL, '7412589632', 12, 'cash', 'Pending', 300.00, '2025-03-19 09:21:28', 'Pending'),
(35, 'Aleena Mathew', NULL, '3698521470', 9, 'upi', '', 100.00, '2025-03-19 09:23:16', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_order_items`
--

CREATE TABLE `tbl_order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `food_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_order_items`
--

INSERT INTO `tbl_order_items` (`id`, `order_id`, `food_name`, `quantity`, `price`) VALUES
(20, 30, 'Bagels with Cream Cheese', 1, 130.00),
(21, 31, 'Hot Chocolate with Marshmallows', 1, 110.00),
(22, 31, 'Mocha Coffee', 1, 80.00),
(23, 32, 'Mixed Berry Smoothie', 1, 80.00),
(24, 33, 'Choconut Smoothie', 1, 100.00),
(25, 33, 'Fresh Fruit Platter with Yogurt', 1, 120.00),
(26, 34, 'Grilled Chicken with Herb Rice', 1, 210.00),
(27, 34, 'Cereal with Milk', 1, 90.00),
(28, 35, 'Choconut Smoothie', 1, 100.00);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_payment`
--

CREATE TABLE `tbl_payment` (
  `paymentid` int(4) NOT NULL,
  `orderid` int(6) NOT NULL,
  `sessionid` int(11) NOT NULL,
  `amount` int(8) NOT NULL,
  `method` enum('Card','UPI','Cash') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_shoppingcart`
--

CREATE TABLE `tbl_shoppingcart` (
  `id` int(11) NOT NULL,
  `usersessionid` varchar(255) NOT NULL,
  `food_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_shoppingcart`
--

INSERT INTO `tbl_shoppingcart` (`id`, `usersessionid`, `food_name`, `price`, `quantity`, `image_path`) VALUES
(20, 'm4ltj6501mtgpg748nnn7fnhue', 'Chocolate Mousse', 80.00, 1, '1742396768_67dadd60c0f00.jpeg'),
(21, 'h887anf4pc3d5f9vtsa2c0at7q', 'Arabic Shawarma Wrap', 120.00, 1, '1742397405_67dadfdd32493.jpeg'),
(22, 'h887anf4pc3d5f9vtsa2c0at7q', 'Cold Coffee', 80.00, 1, '1742397197_67dadf0dd85f3.jpeg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `order_sequence`
--
ALTER TABLE `order_sequence`
  ADD PRIMARY KEY (`sequence_date`);

--
-- Indexes for table `tbl_admin`
--
ALTER TABLE `tbl_admin`
  ADD PRIMARY KEY (`adminid`),
  ADD UNIQUE KEY `adminuname` (`adminuname`);

--
-- Indexes for table `tbl_bookings`
--
ALTER TABLE `tbl_bookings`
  ADD PRIMARY KEY (`booking_id`);

--
-- Indexes for table `tbl_feedback`
--
ALTER TABLE `tbl_feedback`
  ADD PRIMARY KEY (`feedbackId`);

--
-- Indexes for table `tbl_fooditem`
--
ALTER TABLE `tbl_fooditem`
  ADD PRIMARY KEY (`itemid`),
  ADD UNIQUE KEY `itemname` (`itemname`);

--
-- Indexes for table `tbl_fooditemdetailed`
--
ALTER TABLE `tbl_fooditemdetailed`
  ADD PRIMARY KEY (`ditemid`),
  ADD UNIQUE KEY `unique_itemid` (`itemid`);

--
-- Indexes for table `tbl_orders`
--
ALTER TABLE `tbl_orders`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `tbl_order_items`
--
ALTER TABLE `tbl_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `tbl_payment`
--
ALTER TABLE `tbl_payment`
  ADD PRIMARY KEY (`paymentid`),
  ADD UNIQUE KEY `orderid` (`orderid`),
  ADD UNIQUE KEY `sessionid` (`sessionid`);

--
-- Indexes for table `tbl_shoppingcart`
--
ALTER TABLE `tbl_shoppingcart`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_admin`
--
ALTER TABLE `tbl_admin`
  MODIFY `adminid` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `tbl_bookings`
--
ALTER TABLE `tbl_bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_feedback`
--
ALTER TABLE `tbl_feedback`
  MODIFY `feedbackId` int(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_fooditem`
--
ALTER TABLE `tbl_fooditem`
  MODIFY `itemid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `tbl_fooditemdetailed`
--
ALTER TABLE `tbl_fooditemdetailed`
  MODIFY `ditemid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `tbl_orders`
--
ALTER TABLE `tbl_orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12352;

--
-- AUTO_INCREMENT for table `tbl_order_items`
--
ALTER TABLE `tbl_order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `tbl_payment`
--
ALTER TABLE `tbl_payment`
  MODIFY `paymentid` int(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_shoppingcart`
--
ALTER TABLE `tbl_shoppingcart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_fooditemdetailed`
--
ALTER TABLE `tbl_fooditemdetailed`
  ADD CONSTRAINT `tbl_fooditemdetailed_ibfk_1` FOREIGN KEY (`itemid`) REFERENCES `tbl_fooditem` (`itemid`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_order_items`
--
ALTER TABLE `tbl_order_items`
  ADD CONSTRAINT `tbl_order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `tbl_orders` (`order_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
