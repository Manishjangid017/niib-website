-- =====================================================
-- NIIB Institute - Complete Database Schema
-- =====================================================
-- DB Name: niibinstitute
-- Host: localhost  User: root  Pass: mysql
-- =====================================================

CREATE DATABASE IF NOT EXISTS `niibinstitute` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `niibinstitute`;

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

-- ------------------------------------------------------
-- 1) admin_users
-- ------------------------------------------------------
DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE `admin_users` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('super_admin','editor') NOT NULL DEFAULT 'editor',
  `avatar` VARCHAR(255) DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `last_login` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin password = admin123 (bcrypt)
INSERT INTO `admin_users` (`name`,`email`,`password`,`role`,`status`) VALUES
('Super Admin','admin@admin.com','$2y$10$BXR4Y7yKxnP6JdJxqGzKxOQ3sCgYbSQF.f0XR/UB1G7zRzL8KvJ/G','super_admin',1);

-- ------------------------------------------------------
-- 2) site_settings
-- ------------------------------------------------------
DROP TABLE IF EXISTS `site_settings`;
CREATE TABLE `site_settings` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` LONGTEXT,
  `setting_group` VARCHAR(60) DEFAULT 'general',
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `site_settings` (`setting_key`,`setting_value`,`setting_group`) VALUES
('site_name','NIIB Institute','general'),
('site_tagline','Empowering Careers Through Quality Education','general'),
('site_email','info@niibinstitute.com','general'),
('site_phone','+91 9876543210','general'),
('site_whatsapp','+919876543210','general'),
('site_address','123 Education Lane, New Delhi, India - 110001','general'),
('site_logo','assets/images/logo.svg','general'),
('site_favicon','assets/images/favicon.svg','general'),
('footer_text','NIIB Institute is committed to delivering world-class education and skill development.','footer'),
('copyright_text','© 2026 NIIB Institute. All rights reserved.','footer'),
('working_hours','Mon - Sat: 9:00 AM - 7:00 PM','general'),
('google_maps','<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3501.5!2d77.2!3d28.6\" width=\"100%\" height=\"300\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\"></iframe>','general'),
('maintenance_mode','0','general');

-- ------------------------------------------------------
-- 3) pages
-- ------------------------------------------------------
DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `banner_image` VARCHAR(255) DEFAULT NULL,
  `content` LONGTEXT,
  `meta_title` VARCHAR(255) DEFAULT NULL,
  `meta_description` TEXT,
  `meta_keywords` TEXT,
  `canonical_url` VARCHAR(255) DEFAULT NULL,
  `og_image` VARCHAR(255) DEFAULT NULL,
  `schema_code` LONGTEXT,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `pages` (`title`,`slug`,`content`,`meta_title`,`meta_description`,`status`) VALUES
('About Us','about','<h2>Welcome to NIIB Institute</h2><p>NIIB Institute is a premier educational institution dedicated to delivering industry-relevant courses and training programs. With over a decade of experience, we have empowered thousands of students to build successful careers.</p><h3>Our Mission</h3><p>To provide quality education that bridges the gap between academic learning and industry requirements.</p><h3>Our Vision</h3><p>To be the most trusted name in skill-based education across India.</p>','About NIIB Institute - Leading Skill Development Institute','Learn about NIIB Institute, our mission, vision, and commitment to delivering quality education and skill-based training programs.',1),
('Contact Us','contact','<p>Get in touch with NIIB Institute. We are here to help you with any questions about our courses, admissions, or anything else.</p>','Contact NIIB Institute','Contact NIIB Institute for course inquiries, admissions, and general questions.',1),
('Privacy Policy','privacy-policy','<h2>Privacy Policy</h2><p>This Privacy Policy describes how NIIB Institute collects, uses, and protects your information.</p>','Privacy Policy - NIIB Institute','Our privacy policy explains how we handle your personal information.',1),
('Terms & Conditions','terms-conditions','<h2>Terms & Conditions</h2><p>By accessing this website, you agree to be bound by these terms and conditions.</p>','Terms & Conditions - NIIB Institute','Terms and conditions for using NIIB Institute website and services.',1);

-- ------------------------------------------------------
-- 4) menu_items
-- ------------------------------------------------------
DROP TABLE IF EXISTS `menu_items`;
CREATE TABLE `menu_items` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(150) NOT NULL,
  `url` VARCHAR(255) DEFAULT NULL,
  `page_id` INT(11) UNSIGNED DEFAULT NULL,
  `parent_id` INT(11) UNSIGNED DEFAULT 0,
  `menu_location` ENUM('header','footer','both') NOT NULL DEFAULT 'header',
  `target` ENUM('_self','_blank') NOT NULL DEFAULT '_self',
  `icon` VARCHAR(80) DEFAULT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `menu_location` (`menu_location`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `menu_items` (`title`,`url`,`parent_id`,`menu_location`,`sort_order`,`status`) VALUES
('Home','index.php',0,'header',1,1),
('About','about',0,'header',2,1),
('Courses','courses.php',0,'header',3,1),
('Blog','blog.php',0,'header',4,1),
('Contact','contact.php',0,'header',5,1),
('Privacy Policy','page/privacy-policy',0,'footer',1,1),
('Terms & Conditions','page/terms-conditions',0,'footer',2,1),
('About Us','about',0,'footer',3,1),
('Contact','contact.php',0,'footer',4,1);

-- ------------------------------------------------------
-- 5) sliders
-- ------------------------------------------------------
DROP TABLE IF EXISTS `sliders`;
CREATE TABLE `sliders` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) DEFAULT NULL,
  `subtitle` VARCHAR(255) DEFAULT NULL,
  `description` TEXT,
  `image` VARCHAR(255) DEFAULT NULL,
  `button_text` VARCHAR(80) DEFAULT NULL,
  `button_url` VARCHAR(255) DEFAULT NULL,
  `button_text_2` VARCHAR(80) DEFAULT NULL,
  `button_url_2` VARCHAR(255) DEFAULT NULL,
  `text_align` ENUM('left','center','right') NOT NULL DEFAULT 'left',
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `sliders` (`title`,`subtitle`,`description`,`image`,`button_text`,`button_url`,`sort_order`,`status`) VALUES
('Build Your Career With NIIB','India\'s Leading Skill Institute','Industry-recognized courses, expert faculty, and 100% placement assistance.','assets/images/slider-1.svg','Explore Courses','courses.php',1,1),
('Learn From The Best','Expert-Led Programs','Master in-demand skills with hands-on training and real-world projects.','assets/images/slider-2.svg','Get Started','contact.php',2,1);

-- ------------------------------------------------------
-- 6) banners
-- ------------------------------------------------------
DROP TABLE IF EXISTS `banners`;
CREATE TABLE `banners` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `location` VARCHAR(100) DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `link_url` VARCHAR(255) DEFAULT NULL,
  `alt_text` VARCHAR(255) DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------
-- 7) course_categories
-- ------------------------------------------------------
DROP TABLE IF EXISTS `course_categories`;
CREATE TABLE `course_categories` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL,
  `description` TEXT,
  `icon` VARCHAR(80) DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `course_categories` (`name`,`slug`,`description`,`icon`,`status`) VALUES
('Information Technology','information-technology','Computer & software programs','bi-laptop',1),
('Business & Management','business-management','MBA, BBA & management programs','bi-briefcase',1),
('Design & Creative','design-creative','Graphic, UI/UX & creative arts','bi-palette',1),
('Digital Marketing','digital-marketing','SEO, SMM & online marketing','bi-megaphone',1);

-- ------------------------------------------------------
-- 8) courses
-- ------------------------------------------------------
DROP TABLE IF EXISTS `courses`;
CREATE TABLE `courses` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT(11) UNSIGNED DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `short_description` TEXT,
  `full_description` LONGTEXT,
  `duration` VARCHAR(100) DEFAULT NULL,
  `fees` VARCHAR(100) DEFAULT NULL,
  `original_fees` VARCHAR(100) DEFAULT NULL,
  `eligibility` TEXT,
  `image` VARCHAR(255) DEFAULT NULL,
  `brochure` VARCHAR(255) DEFAULT NULL,
  `video_url` VARCHAR(255) DEFAULT NULL,
  `level` VARCHAR(50) DEFAULT 'Beginner',
  `language` VARCHAR(50) DEFAULT 'English',
  `students_count` INT(11) DEFAULT 0,
  `rating` DECIMAL(2,1) DEFAULT 5.0,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `meta_title` VARCHAR(255) DEFAULT NULL,
  `meta_description` TEXT,
  `meta_keywords` TEXT,
  `canonical_url` VARCHAR(255) DEFAULT NULL,
  `og_image` VARCHAR(255) DEFAULT NULL,
  `schema_code` LONGTEXT,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  KEY `status` (`status`),
  KEY `is_featured` (`is_featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `courses` (`category_id`,`title`,`slug`,`short_description`,`full_description`,`duration`,`fees`,`original_fees`,`eligibility`,`level`,`is_featured`,`status`) VALUES
(1,'Full Stack Web Development','full-stack-web-development','Master front-end and back-end development with hands-on projects.','<p>Become a complete web developer in 6 months. Learn HTML, CSS, JavaScript, React, Node.js, MongoDB, and deployment.</p><h3>What You\'ll Learn</h3><ul><li>HTML5, CSS3, JavaScript ES6+</li><li>React.js & Redux</li><li>Node.js & Express</li><li>MongoDB & SQL Databases</li><li>REST APIs & Authentication</li><li>Git & GitHub</li></ul>','6 Months','₹45,000','₹60,000','12th pass or equivalent','Intermediate',1,1),
(1,'Python Programming','python-programming','Learn Python from beginner to advanced level with real projects.','<p>Comprehensive Python course covering core programming, data structures, web frameworks, and automation.</p>','4 Months','₹25,000','₹35,000','Basic computer knowledge','Beginner',1,1),
(2,'Digital Marketing Master Course','digital-marketing-master-course','Complete digital marketing certification with practical training.','<p>Master SEO, SEM, social media marketing, content marketing, email marketing, and analytics.</p>','3 Months','₹20,000','₹28,000','12th pass','Beginner',1,1),
(3,'Graphic Design Pro','graphic-design-pro','Become a professional graphic designer with Adobe tools.','<p>Master Photoshop, Illustrator, InDesign, and design principles. Build a strong portfolio.</p>','4 Months','₹30,000','₹40,000','Basic computer knowledge','Beginner',1,1),
(2,'MBA in Marketing','mba-marketing','Postgraduate program in Marketing Management.','<p>Specialized MBA program focused on modern marketing strategies and management.</p>','2 Years','₹1,80,000',NULL,'Graduation in any discipline','Advanced',0,1),
(3,'UI/UX Design','ui-ux-design','Design beautiful and user-friendly digital products.','<p>Learn user research, wireframing, prototyping, Figma, and design systems.</p>','5 Months','₹35,000','₹45,000','Basic computer knowledge','Intermediate',1,1);

-- ------------------------------------------------------
-- 9) blog_categories
-- ------------------------------------------------------
DROP TABLE IF EXISTS `blog_categories`;
CREATE TABLE `blog_categories` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL,
  `description` TEXT,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `blog_categories` (`name`,`slug`,`description`,`status`) VALUES
('Education','education','Articles on education and learning',1),
('Career Tips','career-tips','Career advice and tips',1),
('Technology','technology','Latest technology articles',1),
('Industry News','industry-news','News from various industries',1);

-- ------------------------------------------------------
-- 10) blog_posts
-- ------------------------------------------------------
DROP TABLE IF EXISTS `blog_posts`;
CREATE TABLE `blog_posts` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT(11) UNSIGNED DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `short_description` TEXT,
  `content` LONGTEXT,
  `featured_image` VARCHAR(255) DEFAULT NULL,
  `author` VARCHAR(150) DEFAULT 'Admin',
  `tags` VARCHAR(500) DEFAULT NULL,
  `publish_date` DATE DEFAULT NULL,
  `views` INT(11) DEFAULT 0,
  `meta_title` VARCHAR(255) DEFAULT NULL,
  `meta_description` TEXT,
  `meta_keywords` TEXT,
  `canonical_url` VARCHAR(255) DEFAULT NULL,
  `og_image` VARCHAR(255) DEFAULT NULL,
  `schema_code` LONGTEXT,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `blog_posts` (`category_id`,`title`,`slug`,`short_description`,`content`,`author`,`publish_date`,`status`) VALUES
(2,'Top 10 In-Demand IT Skills in 2026','top-it-skills-2026','Discover the most in-demand IT skills for 2026 and how to learn them.','<p>The IT industry is evolving rapidly. Here are the top skills employers are looking for in 2026...</p><h3>1. Artificial Intelligence & Machine Learning</h3><p>AI is transforming every industry. Learning ML frameworks like TensorFlow, PyTorch is essential.</p><h3>2. Cloud Computing</h3><p>AWS, Azure, and Google Cloud certifications open doors to high-paying roles.</p>','Rajesh Kumar','2026-05-01',1),
(1,'How To Choose The Right Career After 12th','choose-career-after-12th','Confused about career options after 12th? Read this complete guide.','<p>Choosing the right career path after 12th is crucial. Consider your interests, market demand, and earning potential.</p>','Priya Sharma','2026-04-20',1),
(3,'Why Full Stack Development Is The Future','full-stack-development-future','Full stack developers are in massive demand. Find out why.','<p>Companies prefer engineers who can build complete applications end-to-end. Full stack development gives you that edge.</p>','Amit Singh','2026-04-15',1);

-- ------------------------------------------------------
-- 11) testimonials
-- ------------------------------------------------------
DROP TABLE IF EXISTS `testimonials`;
CREATE TABLE `testimonials` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `designation` VARCHAR(150) DEFAULT NULL,
  `company` VARCHAR(150) DEFAULT NULL,
  `review` TEXT,
  `rating` TINYINT(1) NOT NULL DEFAULT 5,
  `image` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `testimonials` (`name`,`designation`,`company`,`review`,`rating`,`status`) VALUES
('Rahul Verma','Software Engineer','TCS','NIIB Institute changed my career. The trainers are excellent, and the placement support is outstanding.',5,1),
('Sneha Gupta','Digital Marketer','Wipro','The digital marketing course was practical and hands-on. I landed a great job right after completion.',5,1),
('Arjun Mehta','UI Designer','Infosys','Loved the UI/UX program. The mentors gave personal attention and helped me build a strong portfolio.',5,1),
('Pooja Nair','Web Developer','Accenture','Best decision of my life. The full-stack course opened so many doors for me.',5,1);

-- ------------------------------------------------------
-- 12) gallery
-- ------------------------------------------------------
DROP TABLE IF EXISTS `gallery`;
CREATE TABLE `gallery` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) DEFAULT NULL,
  `category` VARCHAR(100) DEFAULT NULL,
  `image` VARCHAR(255) NOT NULL,
  `alt_text` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------
-- 13) faq
-- ------------------------------------------------------
DROP TABLE IF EXISTS `faq`;
CREATE TABLE `faq` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `question` VARCHAR(500) NOT NULL,
  `answer` TEXT NOT NULL,
  `category` VARCHAR(100) DEFAULT 'general',
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `faq` (`question`,`answer`,`category`,`status`) VALUES
('What courses does NIIB Institute offer?','We offer a wide range of courses in IT, business management, digital marketing, design, and more.','general',1),
('Is online learning available?','Yes, most of our courses are available in both online and classroom formats.','general',1),
('Do you provide placement assistance?','Yes, we provide 100% placement assistance and have a dedicated placement cell.','admission',1),
('What are the fees?','Course fees vary based on the program. Please check individual course pages for details.','fees',1),
('How can I apply for admission?','You can fill the contact form, call us, or visit our campus to apply.','admission',1);

-- ------------------------------------------------------
-- 14) enquiry_forms (course enquiries)
-- ------------------------------------------------------
DROP TABLE IF EXISTS `enquiry_forms`;
CREATE TABLE `enquiry_forms` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `course_id` INT(11) UNSIGNED DEFAULT NULL,
  `course_name` VARCHAR(255) DEFAULT NULL,
  `message` TEXT,
  `source` VARCHAR(50) DEFAULT 'website',
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `is_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------
-- 15) contact_messages
-- ------------------------------------------------------
DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE `contact_messages` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `subject` VARCHAR(255) DEFAULT NULL,
  `message` TEXT NOT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `is_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- newsletter subscribers
DROP TABLE IF EXISTS `newsletter_subscribers`;
CREATE TABLE `newsletter_subscribers` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(190) NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------
-- 16) seo_settings
-- ------------------------------------------------------
DROP TABLE IF EXISTS `seo_settings`;
CREATE TABLE `seo_settings` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` LONGTEXT,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `seo_settings` (`setting_key`,`setting_value`) VALUES
('global_meta_title','NIIB Institute - Best Skill Development Institute in India'),
('global_meta_description','Join NIIB Institute for industry-recognized courses in IT, digital marketing, design, and management. 100% placement assistance.'),
('global_meta_keywords','niib institute, skill development, IT courses, digital marketing course, web development course, best institute'),
('og_title','NIIB Institute - Premier Skill Development'),
('og_description','Industry-leading courses with expert faculty and 100% placement assistance.'),
('og_image','assets/images/og-image.svg'),
('og_type','website'),
('twitter_card','summary_large_image'),
('twitter_site','@niibinstitute'),
('robots_meta','index, follow'),
('organization_schema','{"@context":"https://schema.org","@type":"EducationalOrganization","name":"NIIB Institute","url":"https://niibinstitute.com","logo":"assets/images/logo.svg"}'),
('robots_txt','User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /includes/\nDisallow: /config/\n\nSitemap: /sitemap.xml');

-- ------------------------------------------------------
-- 17) header_codes
-- 18) footer_codes
-- 19) custom_css
-- 20) custom_js
-- ------------------------------------------------------
DROP TABLE IF EXISTS `header_codes`;
CREATE TABLE `header_codes` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `code` LONGTEXT,
  `position` ENUM('head','body_start') NOT NULL DEFAULT 'head',
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `footer_codes`;
CREATE TABLE `footer_codes` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `code` LONGTEXT,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `custom_css`;
CREATE TABLE `custom_css` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL DEFAULT 'Global CSS',
  `code` LONGTEXT,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `custom_css` (`name`,`code`,`status`) VALUES
('Global CSS','/* Add your custom CSS here */',1);

DROP TABLE IF EXISTS `custom_js`;
CREATE TABLE `custom_js` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL DEFAULT 'Global JS',
  `code` LONGTEXT,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `custom_js` (`name`,`code`,`status`) VALUES
('Global JS','// Add your custom JavaScript here',1);

-- ------------------------------------------------------
-- 21) redirects
-- ------------------------------------------------------
DROP TABLE IF EXISTS `redirects`;
CREATE TABLE `redirects` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `old_url` VARCHAR(255) NOT NULL,
  `new_url` VARCHAR(255) NOT NULL,
  `type` ENUM('301','302') NOT NULL DEFAULT '301',
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `hits` INT(11) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `old_url` (`old_url`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------
-- 22) social_links
-- ------------------------------------------------------
DROP TABLE IF EXISTS `social_links`;
CREATE TABLE `social_links` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `platform` VARCHAR(80) NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  `icon` VARCHAR(80) DEFAULT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `social_links` (`platform`,`url`,`icon`,`sort_order`,`status`) VALUES
('Facebook','https://facebook.com','bi-facebook',1,1),
('Twitter','https://twitter.com','bi-twitter-x',2,1),
('Instagram','https://instagram.com','bi-instagram',3,1),
('YouTube','https://youtube.com','bi-youtube',4,1),
('LinkedIn','https://linkedin.com','bi-linkedin',5,1);

-- ------------------------------------------------------
-- 23) popup_settings
-- ------------------------------------------------------
DROP TABLE IF EXISTS `popup_settings`;
CREATE TABLE `popup_settings` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) DEFAULT NULL,
  `description` TEXT,
  `image` VARCHAR(255) DEFAULT NULL,
  `button_text` VARCHAR(80) DEFAULT NULL,
  `button_url` VARCHAR(255) DEFAULT NULL,
  `delay_seconds` INT(11) NOT NULL DEFAULT 3,
  `show_once` TINYINT(1) NOT NULL DEFAULT 1,
  `popup_type` ENUM('image','form','message') NOT NULL DEFAULT 'message',
  `status` TINYINT(1) NOT NULL DEFAULT 0,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `popup_settings` (`title`,`description`,`button_text`,`button_url`,`delay_seconds`,`show_once`,`popup_type`,`status`) VALUES
('Special Offer!','Get up to 25% off on all courses. Limited time offer.','Explore Courses','courses.php',5,1,'message',0);

-- ------------------------------------------------------
-- 24) team_members
-- ------------------------------------------------------
DROP TABLE IF EXISTS `team_members`;
CREATE TABLE `team_members` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `designation` VARCHAR(150) DEFAULT NULL,
  `bio` TEXT,
  `image` VARCHAR(255) DEFAULT NULL,
  `facebook` VARCHAR(255) DEFAULT NULL,
  `twitter` VARCHAR(255) DEFAULT NULL,
  `linkedin` VARCHAR(255) DEFAULT NULL,
  `instagram` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `team_members` (`name`,`designation`,`bio`,`sort_order`,`status`) VALUES
('Dr. Anil Kapoor','Founder & Director','20+ years experience in education sector.',1,1),
('Ms. Sunita Rao','Academic Head','PhD in Education, dedicated mentor.',2,1),
('Mr. Vikram Singh','Placement Director','Expert in industry connections and placements.',3,1);

-- ------------------------------------------------------
-- 25) counters
-- ------------------------------------------------------
DROP TABLE IF EXISTS `counters`;
CREATE TABLE `counters` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` VARCHAR(150) NOT NULL,
  `value` VARCHAR(50) NOT NULL,
  `suffix` VARCHAR(10) DEFAULT '+',
  `icon` VARCHAR(80) DEFAULT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `counters` (`label`,`value`,`suffix`,`icon`,`sort_order`,`status`) VALUES
('Students Trained','12500','+','bi-people-fill',1,1),
('Expert Trainers','85','+','bi-person-badge',2,1),
('Courses Offered','40','+','bi-book',3,1),
('Years Experience','15','+','bi-award',4,1);

-- ------------------------------------------------------
-- 26) why_choose_us
-- ------------------------------------------------------
DROP TABLE IF EXISTS `why_choose_us`;
CREATE TABLE `why_choose_us` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `icon` VARCHAR(80) DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `why_choose_us` (`title`,`description`,`icon`,`sort_order`,`status`) VALUES
('Expert Faculty','Learn from industry-leading professionals with decades of experience.','bi-person-workspace',1,1),
('Modern Curriculum','Industry-aligned syllabus updated regularly to match market demand.','bi-journal-code',2,1),
('100% Placement Support','Dedicated placement cell with strong industry connections.','bi-briefcase-fill',3,1),
('Hands-On Training','Real-world projects and practical learning environment.','bi-tools',4,1),
('Flexible Learning','Online and offline class options, weekend batches available.','bi-clock-history',5,1),
('Affordable Fees','Competitive pricing with installment options and scholarships.','bi-cash-coin',6,1);

-- ------------------------------------------------------
-- 27) homepage_sections
-- ------------------------------------------------------
DROP TABLE IF EXISTS `homepage_sections`;
CREATE TABLE `homepage_sections` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `section_key` VARCHAR(80) NOT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `subtitle` VARCHAR(255) DEFAULT NULL,
  `content` LONGTEXT,
  `image` VARCHAR(255) DEFAULT NULL,
  `button_text` VARCHAR(80) DEFAULT NULL,
  `button_url` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `section_key` (`section_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `homepage_sections` (`section_key`,`title`,`subtitle`,`content`,`button_text`,`button_url`,`sort_order`,`status`) VALUES
('about','About NIIB Institute','Empowering Future Leaders','<p>NIIB Institute is a premier educational destination committed to delivering industry-relevant courses and skill-based training. With expert faculty, modern infrastructure, and 100% placement assistance, we empower students to achieve their dreams.</p>','Learn More','about',1,1),
('courses','Popular Courses','Choose Your Path',NULL,'View All Courses','courses.php',2,1),
('why_choose','Why Choose Us','Our Unique Advantages',NULL,NULL,NULL,3,1),
('counters','Our Achievements','Numbers That Speak',NULL,NULL,NULL,4,1),
('testimonials','Student Testimonials','What Our Students Say',NULL,NULL,NULL,5,1),
('cta','Ready To Transform Your Career?','Join thousands of successful students.','<p>Take the first step towards your dream career. Connect with us today.</p>','Enquire Now','contact.php',6,1),
('blog','Latest From Our Blog','Industry Insights & Tips',NULL,'View All Posts','blog.php',7,1),
('faq','Frequently Asked Questions','We have answers to your questions',NULL,NULL,NULL,8,1),
('contact','Get In Touch','Have questions? Contact us',NULL,NULL,NULL,9,1);

-- ------------------------------------------------------
-- 28) media_library
-- ------------------------------------------------------
DROP TABLE IF EXISTS `media_library`;
CREATE TABLE `media_library` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `file_name` VARCHAR(255) NOT NULL,
  `original_name` VARCHAR(255) DEFAULT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `file_type` VARCHAR(50) DEFAULT NULL,
  `file_size` INT(11) DEFAULT NULL,
  `alt_text` VARCHAR(255) DEFAULT NULL,
  `uploaded_by` INT(11) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------
-- 29) admin_activity_logs
-- ------------------------------------------------------
DROP TABLE IF EXISTS `admin_activity_logs`;
CREATE TABLE `admin_activity_logs` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` INT(11) UNSIGNED DEFAULT NULL,
  `action` VARCHAR(150) NOT NULL,
  `module` VARCHAR(100) DEFAULT NULL,
  `description` TEXT,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------
-- 30) html_blocks (Custom HTML blocks for pages)
-- ------------------------------------------------------
DROP TABLE IF EXISTS `html_blocks`;
CREATE TABLE `html_blocks` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `block_key` VARCHAR(80) NOT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `html_content` LONGTEXT,
  `css_class` VARCHAR(150) DEFAULT NULL,
  `page_slug` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `block_key` (`block_key`),
  KEY `page_slug` (`page_slug`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
