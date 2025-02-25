-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 24, 2025 at 04:35 PM
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
(1, 'Pancakes with Maple Syrup', 'Starters', 140, 'Fluffy pancakes drizzled with sweet maple syrup.', '', '67ab6b133dced.jpeg'),
(2, 'Scrambled Eggs with Toast', 'Starters', 100, 'Creamy scrambled eggs served with crispy toast.', '', '67ab8193554ba.jpeg'),
(3, 'Fresh Fruit Platter with Yogurt', 'Starters', 110, 'A refreshing medley of fruits with creamy yogurt.', '', '67ab81dba238b.jpeg'),
(4, 'Bagels with Cream Cheese', 'Starters', 110, 'Toasted bagels topped with smooth cream cheese.', '', '67ab821cdb342.jpg'),
(5, 'Omelet with Multigrain Bread', 'Starters', 130, 'Wholesome omelet with nutritious multigrain bread.', '', '67ab82a0e4a40.jpeg'),
(6, 'Cereal with Milk', 'Starters', 100, 'Toasting the cornflakes before steeping them deepens the flavor of the milk.', '', '67ab8348ee017.jpeg'),
(7, 'Grilled Chicken with Herb Rice', 'Main Course', 230, 'Tender chicken served with fragrant rice.', '', '67ab838f6d35a.jpeg'),
(8, 'Paneer Butter with Fried Rice', 'Main Course', 220, 'Creamy paneer curry with cumin-flavored rice.', '', '67ab83ce38eca.jpeg'),
(9, 'Spicy Chicken Lasagna', 'Main Course', 230, 'Layers of cheesy goodness with spicy grilled chicken.', '', '67ab8420d18f2.jpeg'),
(10, 'Pasta Alfredo with Garlic Bread', 'Main Course', 220, 'Creamy pasta with buttery garlic bread.', '', '67ab8464ec382.jpeg'),
(11, 'Arabic Shawarma Wrap', 'Main Course', 180, 'Juicy shawarma with pickles and fries in a soft wrap.', '', '67ab8498980ac.jpeg'),
(12, 'BBQ Chicken Skewers', 'Main Course', 200, 'Smoky, tender and crispy chicken skewers.', '', '67ab84bfdc3bc.jpeg'),
(13, 'Mediterranean Falafel Platter', 'Main Course', 115, 'A delightful spread of crispy falafel and side dips.', '', '67ab8566a1307.jpeg'),
(14, 'Ham and Cheese Sliders', 'Main Course', 200, 'Ham and cheese rolls, drizzled with mustard sauce.', '', '67ab859026604.jpeg'),
(15, 'Brownies with Ice Cream', 'Desserts', 100, 'Warm brownies topped with cold ice cream.', '', '67ab8676bf96c.jpeg'),
(16, 'Fruit Custard with Jelly', 'Desserts', 120, 'A fruity dessert with a touch of jelly.', '', '67ab869d7e7bc.jpeg'),
(17, 'Chocolate Dream Cake', 'Desserts', 150, 'Decadent and velvety chocolate treat.', '', '67ab86c8d4385.jpeg'),
(18, 'Blueberry Cheesecake', 'Desserts', 140, 'Smooth Cheese Cake with buttery base and blueberry pulp.', '', '67ab874693972.jpeg'),
(19, 'Tiramisu', 'Desserts', 120, 'Classic Italian dessert with layers of coffee and cream.', '', '67ab87aeea5b2.jpeg'),
(20, 'Gulab Jamun with Icecream', 'Desserts', 80, 'Sweet gulab jamuns with creamy vanilla ice cream.', '', '67ab88386cf40.jpeg'),
(21, 'Rice Kheer', 'Desserts', 90, 'A fragrant rice pudding made with a base of basmati rice, whole milk, and sugar.', '', '67ab88662afe3.jpeg'),
(22, 'Hot Chocolate with Marshmallows', 'Desserts', 115, 'Soft and fluffy Marshmelllows with warm Chocolate drink.', '', '67ab889683e81.jpeg'),
(23, 'Choconut Smoothie', 'Beverages', 125, 'Blend of Chocolate, nuts and pure joy.', '', '67ab88d280cb2.jpeg'),
(24, 'Mocha Coffee', 'Beverages', 120, 'A beautiful blend of Chocolate and Coffee.', '', '67ab8901af06b.jpeg'),
(25, 'Lemon Soda', 'Beverages', 95, 'Classic Lemon Fizzy.', '', '67ab892e58e80.jpeg'),
(27, 'Mixed Berry Smoothie', 'Beverages', 130, 'Fresh and healthy blend of berries.', '', '67ab897eb5d44.jpeg'),
(28, 'Mango Lassi', 'Beverages', 90, 'Creamy mango-flavored delight.', '', '67ab89ab3f760.jpeg'),
(29, 'Cold Coffee', 'Beverages', 90, 'Chilled coffee for a caffeine kick..', '', '67ab89d3d48df.jpeg'),
(37, 'Masala Chai', 'Beverages', 65, 'A blend of Indian Spices with traditional chai!', '', '67af331d0b3df.jpeg');

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
(1, 1, 'Fluffy and golden pancakes, made with a soft batter, cooked to perfection, and drizzled with rich, amber-colored maple syrup, offering a balance of sweetness and warmth in every bite.', '2025-02-13 07:54:53'),
(7, 29, 'A smooth and refreshing chilled coffee, blended with milk and sugar, sometimes topped with whipped cream for a creamy and energizing beverage.', '2025-02-13 19:00:45'),
(8, 28, 'A creamy and refreshing Indian yogurt-based drink blended with ripe mangoes, sugar, and cardamom, offering a delightful mix of sweetness and tanginess.', '2025-02-13 19:00:59'),
(9, 27, 'A vibrant and nutritious smoothie made with a blend of berries like strawberries, blueberries, and raspberries, combined with yogurt or milk for a refreshing drink.', '2025-02-13 19:01:19'),
(10, 25, 'A refreshing fizzy drink made with freshly squeezed lemon juice, soda water, and a hint of sugar and salt, offering a tangy and invigorating taste.', '2025-02-13 19:01:33'),
(11, 24, 'A bold coffee blend infused with rich chocolate syrup, topped with steamed milk and a frothy layer of foam, delivering a perfect balance of coffee and cocoa flavors.', '2025-02-13 19:01:57'),
(12, 23, 'A deliciously thick and creamy smoothie made with chocolate, coconut milk, and nuts, blended to perfection for a rich and nutty flavor.', '2025-02-13 19:02:17'),
(13, 22, 'A comforting cup of creamy, velvety hot chocolate topped with soft, pillowy marshmallows that melt into the drink for an extra indulgent touch.', '2025-02-13 19:02:36'),
(15, 21, 'A traditional Indian rice pudding made with slow-cooked rice, milk, sugar, and cardamom, garnished with nuts and saffron for a rich and aromatic dessert.', '2025-02-13 19:05:41'),
(16, 20, 'Soft, syrup-soaked Indian dumplings made from khoya, served warm with a scoop of creamy vanilla ice cream for a delightful contrast of textures and temperatures.', '2025-02-13 19:06:12'),
(17, 19, 'A classic Italian dessert made with espresso-soaked ladyfingers layered with velvety mascarpone cream and dusted with cocoa powder for a rich, coffee-infused indulgence.', '2025-02-13 19:06:26'),
(18, 18, 'A creamy, smooth cheesecake with a buttery graham cracker crust, topped with a luscious blueberry compote for the perfect balance of tangy and sweet flavors.', '2025-02-13 19:06:46'),
(19, 17, 'A decadent chocolate cake with moist layers, rich chocolate ganache, and a velvety chocolate mousse, delivering a melt-in-the-mouth experience.', '2025-02-13 19:07:02'),
(20, 16, 'A delightful combination of silky, chilled custard mixed with diced fresh fruits, layered with wobbly, colorful jelly for a refreshing and light dessert.', '2025-02-13 19:08:34'),
(21, 15, 'Fudgy, rich chocolate brownies served warm with a scoop of creamy vanilla ice cream, melting into the gooey goodness for an irresistible dessert.', '2025-02-13 19:08:55'),
(22, 14, 'Mini soft buns stuffed with smoky ham and melted cheese, brushed with butter, and baked until golden, making for a savory and delightful bite-sized snack.', '2025-02-13 19:09:12'),
(23, 13, 'Crispy, golden-brown chickpea fritters served with pita bread, hummus, fresh salad, and a tangy tahini dressing, delivering an authentic Mediterranean experience.', '2025-02-13 19:09:33'),
(24, 12, 'Succulent chunks of chicken, marinated in smoky barbecue sauce, skewered, and grilled to perfection, creating a mouthwatering, juicy treat with a charred exterior.', '2025-02-13 19:09:51'),
(25, 11, 'A Middle Eastern favorite featuring thinly sliced grilled chicken, wrapped in soft pita bread with crunchy vegetables, crispy potato fries and creamy garlic sauce, bursting with bold flavors.', '2025-02-13 19:11:16'),
(26, 10, 'Silky smooth Alfredo sauce made with butter, garlic, cream, and parmesan, coating tender pasta, served alongside crispy, buttery garlic bread for a heavenly meal.', '2025-02-13 19:11:52'),
(27, 9, 'Layers of al dente pasta, fiery spiced chicken, tangy tomato sauce, and gooey melted cheese, baked to perfection for a comforting and indulgent Italian delight.', '2025-02-13 19:12:09'),
(28, 8, 'Soft cubes of paneer simmered in a rich, creamy butter-based tomato gravy, paired with lightly spiced fried rice with ghee for a delightful fusion of flavors.', '2025-02-13 19:12:45'),
(29, 7, 'Tender, juicy grilled chicken marinated with aromatic herbs and spices, served over a bed of fragrant, herb-infused rice, creating a flavorful and well-balanced meal.', '2025-02-13 19:13:04'),
(30, 6, 'A classic breakfast option featuring crunchy cereal soaked in cold, creamy milk, with the option to add fruits or nuts for extra flavor and nutrition.', '2025-02-13 19:13:22'),
(31, 5, 'A fluffy omelet made with fresh eggs, loaded with veggies, cheese, or meats, served with hearty, fiber-rich multigrain bread for a nutritious and filling meal.', '2025-02-13 19:13:38'),
(32, 4, 'Classic New York-style bagels, slightly crisp on the outside and chewy inside, generously spread with velvety cream cheese, offering a delicious combination of textures and flavors.', '2025-02-13 19:13:58'),
(33, 3, 'A vibrant mix of seasonal fruits like apples, bananas, berries, and grapes, elegantly arranged and paired with smooth, creamy yogurt for a refreshing and healthy start to the day.', '2025-02-13 19:14:16'),
(34, 2, 'Soft, creamy scrambled eggs made with a hint of butter and seasoning, served alongside crispy, golden-brown toast for a wholesome and satisfying breakfast.', '2025-02-13 19:14:34'),
(36, 37, 'A spiced Indian tea made by boiling black tea with milk, aromatic spices like cardamom, cinnamon, and ginger, creating a warm and comforting drink full of rich flavors.', '2025-02-18 17:57:38');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_orders`
--

CREATE TABLE `tbl_orders` (
  `order_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `mobile_number` varchar(15) NOT NULL,
  `table_number` int(11) NOT NULL,
  `payment_mode` varchar(50) NOT NULL,
  `order_status` enum('Pending','In Progress','Completed','Cancelled') DEFAULT 'Pending',
  `total_amount` decimal(10,2) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_orders`
--

INSERT INTO `tbl_orders` (`order_id`, `customer_name`, `mobile_number`, `table_number`, `payment_mode`, `order_status`, `total_amount`, `order_date`) VALUES
(54, 'neeva', '7412589632', 1, 'upi', 'Pending', 240.00, '2025-02-20 05:29:34'),
(55, 'annmaria', '8564239774', 2, 'upi', 'Pending', 250.00, '2025-02-20 08:05:02'),
(56, 'neeva', '7412589632', 2, 'cash', 'Pending', 100.00, '2025-02-20 09:52:09'),
(57, 'neeva', '7412589632', 2, 'cash', 'Pending', 240.00, '2025-02-20 09:56:27'),
(58, 'sunish', '7896541230', 8, 'cash', 'Pending', 210.00, '2025-02-20 09:57:38'),
(59, 'nn', '8111962758', 3, 'upi', 'Pending', 210.00, '2025-02-20 10:09:55'),
(60, 'neeva', '7896541230', 3, 'cash', 'Pending', 130.00, '2025-02-20 10:30:32'),
(61, 'nn', '6541230789', 1, 'card', 'Pending', 110.00, '2025-02-20 10:33:42'),
(62, 'neeva', '8564239774', 2, 'upi', 'Pending', 200.00, '2025-02-20 10:49:10'),
(63, 'neeva', '6541230789', 2, 'card', 'Pending', 120.00, '2025-02-20 10:50:10'),
(64, 'neeva', '8111962758', 3, 'card', 'Pending', 290.00, '2025-02-22 18:39:49'),
(65, 'annmaria', '8111962758', 2, 'upi', 'Pending', 110.00, '2025-02-22 18:40:18');

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
(53, 54, 'Pancakes with Maple Syrup', 1, 140.00),
(54, 54, 'Scrambled Eggs with Toast', 1, 100.00),
(55, 55, 'Pancakes with Maple Syrup', 1, 140.00),
(56, 55, 'Bagels with Cream Cheese', 1, 110.00),
(57, 56, 'Cereal with Milk', 1, 100.00),
(58, 57, 'Omelet with Multigrain Bread', 1, 130.00),
(59, 57, 'Bagels with Cream Cheese', 1, 110.00),
(60, 58, 'Fresh Fruit Platter with Yogurt', 1, 110.00),
(61, 58, 'Cereal with Milk', 1, 100.00),
(62, 59, 'Bagels with Cream Cheese', 1, 110.00),
(63, 59, 'Scrambled Eggs with Toast', 1, 100.00),
(64, 60, 'Omelet with Multigrain Bread', 1, 130.00),
(65, 61, 'Fresh Fruit Platter with Yogurt', 1, 110.00),
(66, 62, 'Scrambled Eggs with Toast', 1, 100.00),
(67, 62, 'Brownies with Ice Cream', 1, 100.00),
(68, 63, 'Fruit Custard with Jelly', 1, 120.00),
(69, 64, 'Bagels with Cream Cheese', 1, 110.00),
(70, 64, 'Arabic Shawarma Wrap', 1, 180.00),
(71, 65, 'Bagels with Cream Cheese', 1, 110.00);

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
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_admin`
--
ALTER TABLE `tbl_admin`
  ADD PRIMARY KEY (`adminid`),
  ADD UNIQUE KEY `adminuname` (`adminuname`);

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
-- AUTO_INCREMENT for table `tbl_fooditem`
--
ALTER TABLE `tbl_fooditem`
  MODIFY `itemid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `tbl_fooditemdetailed`
--
ALTER TABLE `tbl_fooditemdetailed`
  MODIFY `ditemid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `tbl_orders`
--
ALTER TABLE `tbl_orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `tbl_order_items`
--
ALTER TABLE `tbl_order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `tbl_shoppingcart`
--
ALTER TABLE `tbl_shoppingcart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_fooditemdetailed`
--
ALTER TABLE `tbl_fooditemdetailed`
  ADD CONSTRAINT `tbl_fooditemdetailed_ibfk_1` FOREIGN KEY (`itemid`) REFERENCES `tbl_fooditem` (`itemid`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_fooditemdetailed_ibfk_2` FOREIGN KEY (`itemid`) REFERENCES `tbl_fooditem` (`itemid`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_order_items`
--
ALTER TABLE `tbl_order_items`
  ADD CONSTRAINT `tbl_order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `tbl_orders` (`order_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
