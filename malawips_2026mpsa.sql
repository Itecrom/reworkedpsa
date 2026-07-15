-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 15, 2026 at 08:54 PM
-- Server version: 8.0.46-cll-lve
-- PHP Version: 8.4.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `malawips_2026mpsa`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `token_expire` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `role` varchar(50) DEFAULT 'admin'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `email`, `reset_token`, `token_expire`, `created_at`, `role`) VALUES
(10, '$admin', '$2y$10$Mc0NgH5ZSWo0a.4I9S7Y0OWpTMF9adSl5SNWbtRlPP5GuvvqLzWNu', 'leonardjjmhone@gmail.com', 'f1017a490401a9d8f4e36c9b56d2f55f80b4ea84aa198be5702d87f998db054d', '2026-07-08 19:20:40', '2026-04-17 06:05:23', 'admin'),
(11, 'PSA Admin', '$2y$10$2vM2jQHzvUlsTTyYD.M93OLAe8dm6O17L0CAHly2ijNhmwNCIInre', 'raphaelfeiva@gmail.com', NULL, NULL, '2026-04-20 18:23:34', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `summary` text NOT NULL,
  `published_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int NOT NULL,
  `news_id` int DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `comment` text,
  `approved` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `parent_id` int DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `venue` varchar(255) DEFAULT NULL,
  `organizer` varchar(255) DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `featured` tinyint(1) DEFAULT '0',
  `registration_link` varchar(255) DEFAULT NULL,
  `google_map` text,
  `brochure` varchar(255) DEFAULT NULL,
  `event_type` enum('physical','online','hybrid') DEFAULT 'physical',
  `online_link` varchar(255) DEFAULT NULL,
  `status` enum('upcoming','past') DEFAULT 'upcoming',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `slug`, `description`, `image`, `venue`, `organizer`, `start_date`, `end_date`, `featured`, `registration_link`, `google_map`, `brochure`, `event_type`, `online_link`, `status`, `created_at`) VALUES
(2, 'PSA WEBINAR SERIES NUMBER 7:  The Politicisation of public appointments and Promotions in Malawi', 'psa-webinar-series-number-7-the-politicisation-of-public-appointments-and-promotions-in-malawi', '<p>The Political Science Association of Malawi (PSA) invites you to the seventh installment of its Webinar Series, focusing on a critical governance challenge: the increasing politicization of public appointments and promotions in Malawi.&nbsp;As concerns over merit, transparency, and institutional integrity continue to grow, this webinar brings together scholars and practitioners to discuss the causes, consequences, and potential reforms related to political interference in the public sector.</p>\r\n\r\n<p><strong>Date:</strong> Monday, 11th May 2026<br />\r\n<strong>Time:</strong> 6:00 PM &ndash; 7:30 PM</p>\r\n\r\n<p><strong>Featured Panelists:</strong></p>\r\n\r\n<p>Dr. Susan Mtuwa &ndash; Keynote Address<br />\r\nBenedicto Kondowe, Chairperson, National Advocacy Platform (NAP) &ndash; Discussant<br />\r\nPeter Chisi, Director of Civil and Political Rights, Malawi Human Rights Commission &ndash; Moderator</p>\r\n\r\n<p>All are welcome to attend. For more information, visit www.malawipsa.org or email admin@malawipsa.org.<br />\r\nFollow us on <strong><a href=\"https://facebook.com/psamalawi\" onclick=\"window.open(this.href, \'PSAFACEBOOKPAGE\', \'resizable=yes,status=no,location=no,toolbar=no,menubar=no,fullscreen=no,scrollbars=no,dependent=no\'); return false;\">FACEBOOK</a></strong>`1&nbsp;for live updates.</p>\r\n', 'uploads/events/1778834838_6351.jpeg', 'Online', 'Political Science Association', '2026-05-11 18:00:00', '2026-05-11 19:30:00', 0, 'https://teams.live.com/meet/9358183398967?p=Eay0zt9ieBZtMXrR4K', '', 'uploads/events/brochures/1778834838_9614.pdf', 'online', 'https://teams.live.com/meet/9358183398967?p=Eay0zt9ieBZtMXrR4K', 'past', '2026-05-15 08:47:18'),
(3, 'The 4th PSA Biennial Conference — 26th–27th September 2026', 'the-4th-psa-biennial-conference-26th-27th-september-2026', '<p><strong>The Political Science Association of Malawi (PSA) is pleased to announce the 4th PSA Biennial Conference</strong></p>\r\n\r\n<p><br />\r\n<strong>Dates:</strong>&nbsp;26th&ndash;27th September 2026</p>\r\n\r\n<p><strong>Venue:</strong>&nbsp;Sunbird Livingstonia Beach and Waterfront, Salima.</p>\r\n\r\n<p><strong>2026 Theme:</strong> <em>&quot;Malawi&#39;s democracy at the crossroads? Maturing institutions or a tailspin into uncertainty?&quot;</em></p>\r\n\r\n<p><strong>Who Should Attend:&nbsp;</strong>The conference is open to both PSA Members and Non-PSA Members, with options for physical and virtual participation.</p>\r\n\r\n<p><strong>Registration Fees:</strong></p>\r\n\r\n<p><strong><em>Early Bird Fees (Deadline: 1st August 2026)</em></strong></p>\r\n\r\n<ul>\r\n	<li>PSA Members: MK240,000.00 (Physical Participation) | MK140,000.00 (Virtual Participation)</li>\r\n	<li>Non-PSA Members: MK380,000.00 (Physical Participation) | MK280,000.00 (Virtual Participation)</li>\r\n</ul>\r\n\r\n<p><strong><em>Late Fees (Deadline: 18th September 2026)</em></strong></p>\r\n\r\n<ul>\r\n	<li>PSA Members: MK380,000.00 (Physical Participation) | MK280,000.00 (Virtual Participation)</li>\r\n	<li>Non-PSA Members: MK500,000.00 (Physical Participation) | MK400,000.00 (Virtual Participation)</li>\r\n</ul>\r\n\r\n<p><strong>Please note:</strong> registration fees do not include accommodation and transport.</p>\r\n\r\n<p><strong>To register:</strong>&nbsp;contact <a href=\"mailto:admin@malawipsa.org\">admin@malawipsa.org</a>.</p>\r\n\r\n<p>Follow us on Instagram, LinkedIn, X (Twitter), and YouTube: <strong>@malawipsa</strong></p>\r\n', 'uploads/events/1784136183_1246.jpg', 'Sunbird Livingstonia Beach and Waterfront, Salima', 'Political Science Association', '2026-09-26 08:00:00', '2026-09-27 12:00:00', 1, '', '', '', 'physical', '', 'upcoming', '2026-07-15 17:23:03');

-- --------------------------------------------------------

--
-- Table structure for table `event_gallery`
--

CREATE TABLE `event_gallery` (
  `id` int NOT NULL,
  `event_id` int NOT NULL,
  `image` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `event_gallery`
--

INSERT INTO `event_gallery` (`id`, `event_id`, `image`, `created_at`) VALUES
(2, 3, 'uploads/events/gallery/1784136183_6420.jpg', '2026-07-15 17:23:03'),
(3, 3, 'uploads/events/gallery/1784136183_8321.jpg', '2026-07-15 17:23:03');

-- --------------------------------------------------------

--
-- Table structure for table `event_rsvps`
--

CREATE TABLE `event_rsvps` (
  `id` int NOT NULL,
  `event_id` int NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `attendees` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `journals`
--

CREATE TABLE `journals` (
  `id` int NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `slug` varchar(199) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `downloads` int DEFAULT '0',
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `summary` text,
  `tags` varchar(255) DEFAULT NULL,
  `pdf` varchar(255) DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `description` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `journal_comments`
--

CREATE TABLE `journal_comments` (
  `id` int NOT NULL,
  `journal_id` int NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `comment` text,
  `approved` tinyint DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `journal_topics`
--

CREATE TABLE `journal_topics` (
  `id` int NOT NULL,
  `journal_id` int NOT NULL,
  `topic` varchar(255) NOT NULL,
  `file` varchar(255) NOT NULL,
  `downloads` int DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `slug` varchar(199) DEFAULT NULL,
  `content` text,
  `summary` text,
  `image` varchar(255) DEFAULT NULL,
  `author` varchar(100) DEFAULT NULL,
  `date_posted` date DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'published',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `featured` tinyint(1) DEFAULT '0',
  `pdf` varchar(255) DEFAULT NULL,
  `downloads` int DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `title`, `slug`, `content`, `summary`, `image`, `author`, `date_posted`, `category`, `tags`, `status`, `created_at`, `featured`, `pdf`, `downloads`) VALUES
(13, '2 years of Tonse Alliance in government Retrospect and Prospects', '2-years-of-tonse-alliance-in-government-retrospect-and-prospects', '<h3>Overview and Aim</h3>\r\n\r\n<p>PSA Newsletter is primarily owned and published by the PSA &ndash; Malawi. The aim of the Newsletter is to publish and disseminate high quality articles (non-academic but grounded on relevant analytical frameworks) on politics and public affairs and in the process promote informed discussions as well as debates. Ultimately it is expected that the newsletter will further assist in advancement of policy advocacy in Malawi and beyond. Thus, the Newsletter is essential as a valuable resource for political science and public affairs professionals in the private and public sector institutions. With the exception of Special editions, the Newsletter will be published biannually in June and December every year.</p>\r\n\r\n<h3>Call for Special Issue</h3>\r\n\r\n<p>The Editors of PSA Newsletter are inviting Articles for a special issue of the Newsletter to be published by early July 2022. This special issue is entitled: &ldquo;A reflection of the State of democracy, governance, and politics in Malawi on the occasion of 2 years of Tonse Alliance in government &ndash; Retrospect and Prospects&rdquo;. Malawi held presidential elections on 23rd June 2020 following the annulment of the May 2019 presidential elections results in which the-then incumbent Peter Mutharika of the Democratic Progressive Party was declared the winner. Opposition leader Lazarus Chakwera of the Malawi Congress Party (but heading the electoral Tonse Alliance composed of 9 parties including the MCP, UTM, AFORD, PPM, Umodzi Party, MAFUNDE, Peoples Party, PETRA, and Freedom Party) emerged victorious in this Presential lections re-run when he got 59% of the votes. Chakwera was eventually sworn-in as President of Malawi on 28th June 2022. This presidential re-run was therefore very unique for many reasons which include: The court verdict that led to these elections set a milestone across African politics, for it made Malawi just the second country south of the Sahara to have presidential poll results annulled by the courts (after Kenya in 2017). But unlike the Kenyan case, the presidential re-run was won by an opposition leader. The said court ruling also had a significant impact on the Malawi presidential electoral system. Specifically, through the court&rsquo;s interpretation of the Malawi constitution, June 2020 presidential elections witnessed the 50%+1 rule being applied for the first time. Another unique element was persistent and well-coordinated protests, against the May 2019 presidential elections results, with the army playing a key role to protect the protesters when the police failed to manage the situation. Equally important was the fact that the ushering of the new Tonse Alliance government provided a new wave of hope that the new regime would translate the campaign rhetoric of championing a transformational leadership in order to &ldquo;clear the rubble&rdquo; and &ldquo;broken system&rdquo;. On 28th June 2022, the Tonse Alliance government will have clocked exactly 2 years in office.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<h3>Purpose</h3>\r\n\r\n<p>To critically reflect on the state of democracy, governance, and politics in Malawi within 2 years of Tonse Alliance at the helm. What are the gains, losses, lessons and implications on politics, governance, and public affairs? What are the retrospects and prospects? Ultimately it is expected that the articles submitted will provide further insights into the implications of this state of democracy, governance, and politics for the past 2 years not only in Malawi but also in Africa and beyond. This Special edition will also highlight the recent achievements of individual members of the Association. The Newsletter will be published on the PSA website.On ly members of PSA will be eligible to submit articles to be considered for publishment under this Issue. Interested members are advised to contribute to any of the following sub-themes:</p>\r\n\r\n<ul>\r\n	<li>Civil Society, civic space, NGO Accountability and human rights</li>\r\n	<li>Coalition politics and governance &amp; Intraparty politics and democracy.</li>\r\n	<li>Covid-19, politics and democracy and Political leadership, governance and democracy</li>\r\n</ul>\r\n\r\n<ul>\r\n	<li>National politics and chieftaincy: Relationship between the state, the ruling party on one hand and chiefs</li>\r\n	<li>The Opposition and democracy &amp; Parliament and democracy &amp; Public sector reforms, democracy and governance</li>\r\n	<li>Peace and security OR Corruption, OR Campaign promises Versus Action OR Gender, governance and politics. &amp; Elections, politics and governance.</li>\r\n	<li>Establish and foster closer relationship between political scientists and government officials, academic institutions, Civil Society, and the Media.</li>\r\n	<li>Promote the study, research and publications related to but not limited to Malawi politics.</li>\r\n	<li>Promote activities of associations of Political Science students in education institutions in Malawi.Members of Parliament &amp; Candidates</li>\r\n</ul>\r\n\r\n<h3>Specifications</h3>\r\n\r\n<p>The articles should be within the word range of 700 to 1000 words. The articles must be analytical and must set out a brief analytical framework and proceed to deal with the issue. The deadline for submission of Articles is 30th June 2022, 12:00 midnight. All the articles will be published on this site and should be submitted to the following</p>\r\n\r\n<p><br />\r\n&nbsp;snkowani@gmail.com&nbsp; | raphaelfeiva@gmail.com.</p>\r\n', NULL, 'uploads/news/2026/04/1776407855_56de354d.jpg', 'Len', '2022-06-27', 'Politics', 'Politics, Elections, Tonse Alliance', 'published', '2026-04-17 06:37:36', 0, NULL, 0),
(11, 'Call for abstracts book title: The Malawi 2025 General Elections: New Laws and Systems, better outcomes?', 'call-for-abstracts-book-title-the-malawi-2025-general-elections-new-laws-and-systems-better-outcomes', '<h3>Introduction</h3>\r\n\r\n<p>The editors of the book, Associate Professor Nandini Patel, Professor Happy Kayuni and Dr. Henry Chingaipe, invite scholars, researchers, and practitioners to submit abstracts for consideration for inclusion in the forthcoming publication titled: &ldquo;The Malawi 2025 General Elections: New Laws and Systems, Better Outcomes?&rdquo; by the National Initiative for Civic Education (NICE), the Institute for Policy Interaction (IPI), and the Political Science Association (PSA).</p>\r\n\r\n<h3>Scope</h3>\r\n\r\n<p>The Political Science Association of Malawi will hold its 3rd Biennial Conference from 3-4 May, 2025-2026 at Sunbird Nkopola Lodge in Mangochi under the theme: &#39;3oth Anniversary of Multiparty Democracy: Reflections on Malawi&#39;s Governance, Politics and Development&#39;. This is a Premium gathering of Local and International Political Scientists. For Details on how to be part of this gathering, email PSA Admin:&nbsp;<strong>admin@psamalawi.org</strong>.</p>\r\n\r\n<h3>Objectives</h3>\r\n\r\n<p>The proposed book shall adopt a wide-ranging and systematic approach. Embracing an electoral cycle framework approach and utilising the &quot;electoral governance&quot; lens, the volume is intended to go beyond superficial documentation of experiences to present candid analyses of the performance and viability of institutional arrangements, structural dynamics, and human agency to shed light on how, singularly or jointly, they impact the quality of democracy generally, and electoral democracy in particular. Thus, by covering the complete electoral cycle&mdash;from pre-election preparations to post-election dispute resolution&mdash;the proposed book envisages a holistic investigation that emphasizes achievements, vulnerabilities and resilience factors of Malawi&rsquo;s electoral democracy.</p>\r\n\r\n<ul>\r\n	<li>To evaluate the implementation of the 2020 electoral reforms and their impact on the 2025 general elections.</li>\r\n	<li>To assess the implementation of the Political Parties Act, particularly the regulation of campaign hand-outs, in promoting fair competition and transparency on expenditures through party disclosures;</li>\r\n	<li>To assess whether and how the media, both conventional and modern, impact the political participation of various segments of the electorate and competition among candidates and political parties;</li>\r\n</ul>\r\n\r\n<ul>\r\n	<li>To assess the status of inclusivity of the electoral process, especially for women, youth and differently-abled persons;</li>\r\n	<li>To examine the role of technology in elections, including the politics of adoption and uptake, contributions to efficiency and effectiveness in - The management of elections and the associated risks and challenges of technology;</li>\r\n	<li>To identify and analyse the drivers of electoral violence and assess the effectiveness of the institutional framework for resolving electoral disputes and preventing and penalising electoral violence;</li>\r\n	<li>To evaluate the delivery and impact of Civic and Voter Education (CVE);</li>\r\n	<li>To compile information and reflect on election observations; and To compare Malawi&rsquo;s electoral challenges and successes with regional and international trends to draw broader insights.</li>\r\n</ul>\r\n\r\n<h3>Themes</h3>\r\n\r\n<ul>\r\n	<li>The Legal Framework, the Electoral Reforms and Implementation</li>\r\n	<li>Boundary Re-demarcation of Constituencies and Wards</li>\r\n	<li>Technology in Elections</li>\r\n	<li>Inclusivity and Participation: Progress and Challenges for Women and Youth and Differently-abled Persons</li>\r\n	<li>The Management of the 2025 Elections</li>\r\n	<li>Political Parties &ndash; Conventions, Primaries, Campaign</li>\r\n	<li>Media &ndash; Mainstream and Digital</li>\r\n	<li>Civil Society &ndash; Civic education, Voter Mobilisation and Election Observation</li>\r\n	<li>Analysing the Vote</li>\r\n	<li>Electoral Disputes, Violence, Resolution and Election Observation</li>\r\n</ul>\r\n\r\n<p><strong>Guidelines</strong></p>\r\n\r\n<ul>\r\n	<li>Word limit: 300 words</li>\r\n</ul>\r\n\r\n<p><strong>Submission deadline</strong></p>\r\n\r\n<ul>\r\n	<li>6th March 2025. Late submissions will not be considered.</li>\r\n	<li>Submissions to: malawi.ipi@outlook.com and copied to admin@malawipsa.org.</li>\r\n</ul>\r\n\r\n<div style=\"background:#eeeeee; border:1px solid #cccccc; padding:5px 10px\"><strong>We eagerly look forward to your submissions.</strong></div>\r\n', NULL, 'uploads/news/2026/04/1776406355_5176576f.jpg', 'Len', '2025-02-20', 'Abstracts', 'General Elections, Laws, 2025', 'published', '2026-04-17 06:12:35', 1, 'uploads/pdfs/1776410135_8a439373.pdf', 0),
(12, '3rd Biennial PSA Conference and Call for Papers THEME: 30 Years of Multiparty Democracy', '3rd-biennial-psa-conference-and-call-for-papers-theme-30-years-of-multiparty-democracy', '<h3>Background and Purpose of the Conference/ Special Issue Journal</h3>\r\n\r\n<p>The Political Science Association (PSA) is a registered entity with a diverse membership of academics and professionals in the fields of political science, political philosophy, political and economic governance, public administration, international relations, public law, public policy and political economy analysis and other related disciplines relevant to the study, analysis and understanding of politics. The purpose of PSA is to contribute to political and socio-economic development of Malawi through promoting the study and analysis of politics through research, advocacy and policy dialogue.<br />\r\n<br />\r\nThe Political Science Association (Malawi), in collaboration with the editors of Malawi Journal of Politics &amp; Public Affairs and the University of Malawi&rsquo;s Department of Politics and Government, are pleased to announce its 3rd Biennial Conference, scheduled to take place from the 25th to the 27th of April 2025-2026. The theme of the conference is &ldquo;30 Years of Multiparty Democracy: Reflections on Malawi&rsquo;s Governance, Politics, and Development&rdquo;. This conference holds special significance as it marks 30 years since Malawi transitioned from a one-party system to a multiparty democracy in 1994. In light of this important political milestone, the conference aims to serve as a platform for stakeholders to undertake a comparative analysis and a critical examination of the first 30 years of one-party rule (1964-1994) and the subsequent multi-party era (1994-2025-2026). Specifically, the2 overall objective is to gain insights into the political, social, and economic transformations that Malawi has experienced, hence drawing lessons for the future.<br />\r\n<br />\r\nThe 3rd Biennial conference will therefore provide a platform for academics, researchers, policymakers, and practitioners to engage in deep discussions, share perspectives, and present empirical findings related to Malawi&#39;s political landscape over the two distinctive periods. In conjunction with the conference, the Political Science Association is calling for the submission of papers for presentation at the conference, which will be considered for publication in a special issue of the Malawi Journal of Politics and Public Affairs.</p>\r\n\r\n<h3>Specific Thematic Areas</h3>\r\n\r\n<p>The Political Science Association of Malawi will hold its 3rd Biennial Conference from 3-4 May, 2025-2026 at Sunbird Nkopola Lodge in Mangochi under the theme: &#39;3oth Anniversary of Multiparty Democracy: Reflections on Malawi&#39;s Governance, Politics and Development&#39;. This is a Premium gathering of Local and International Political Scientists. For Details on how to be part of this gathering, email PSA Admin:&nbsp;<strong>admin@psamalawi.org</strong>.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<h3>Purpose</h3>\r\n\r\n<p>Papers are invited on the following thematic areas, with a focus on the comparative analysis between the first 30 years of one-party rule and the subsequent multi-party era:</p>\r\n\r\n<ul>\r\n	<li>Governance Structures and Political Evolution- Comparing the governance structures and political dynamics during the one-party era and the multi-party democracy.</li>\r\n	<li>Party Politics and Institutional Framework- Analyzing the evolution of political parties, their roles, and the impact of the institutional framework on political stability.</li>\r\n	<li>Socioeconomic Development- Assessing the impact of political systems on socioeconomic development during both periods, including issues of poverty reduction, education, healthcare, and infrastructure.</li>\r\n</ul>\r\n\r\n<ul>\r\n	<li>Citizen Participation and Political Culture- Investigating changes in political culture and the extent of citizen participation in the democratic process across the two eras.</li>\r\n	<li>Foreign Policy and Diplomacy- Comparing the foreign policy approaches, regional integration relationships, and international standing during the one-party rule and multiparty democracy.</li>\r\n	<li>Media&#39;s Role in Politics - exploring the role of media in shaping political processes during the two distinctive periods.</li>\r\n</ul>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<ul>\r\n	<li>Civil Society Dynamics- Examining the evolution and impact of civil society in promoting political accountability, inclusivity, and social justice.</li>\r\n	<li>Anti-Corruption Strategies and Public Sector Reforms- Assessing the effectiveness of anticorruption strategies and public sector reforms in enhancing transparency, accountability, and efficiency in governance.</li>\r\n</ul>\r\n\r\n<h3>Timelines</h3>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<ul>\r\n	<li>Abstract Submission Deadline: 18th March 2025-2026</li>\r\n	<li>Notification of Acceptance: 1 April 2025-2026</li>\r\n	<li>Presentation of Selected Papers: 3rd Biennial PSA Conference, 3rd- 4th May 2025-2026</li>\r\n	<li>Full Paper Submission Deadline: 17th May 2025-2026</li>\r\n	<li>Publication: July - August 2025-2026</li>\r\n</ul>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>We invite interested scholars and researchers to submit abstracts addressing the conference theme, with a specific focus on the comparative analysis of the two distinctive periods in Malawi&#39;s political history. Abstracts should be sent to&nbsp;&nbsp;hkayuni@unima.ac.mw&nbsp;&nbsp;bdulani@unima.ac.mw by the submission deadline. Selected papers presented during the conference will be considered for publication in the July 2025-2026 issue of the Malawi Journal of Politics &amp; Public Affairs.<br />\r\n<br />\r\n<strong>For further details please download the concept note</strong></p>\r\n', NULL, 'uploads/news/2026/04/1776407354_0c487a84.jpg', 'Len', '2025-02-15', 'Conference', ' Politics, Economics, Foreign Policy', 'published', '2026-04-17 06:29:14', 1, NULL, 0),
(14, 'Call for Papers- the 4th Biennial Conference for Malawi Political Science Association (PSA)', 'call-for-papers--the-4th-biennial-conference-for-malawi-political-science-association-psa', '<p><strong>Political Science Association Announces 4th Biennial Conference Call for Papers</strong></p>\r\n\r\n<p>The Political Science Association (PSA) of Malawi has officially announced a Call for Papers for its upcoming <strong>4th Biennial Conference</strong>, scheduled to take place from <strong>8th to 9th August 2026</strong> at the <strong>Sunbird Livingstonia Beach and Waterfront in Salima</strong>.</p>\r\n\r\n<p>The conference will be held under the theme:<br />\r\n<em>&ldquo;Malawi&rsquo;s democracy at the crossroads? Maturing Institutions or a Tailspin into Uncertainty?&rdquo;</em>&mdash;a timely and thought-provoking topic that seeks to examine the current state, resilience, and future trajectory of Malawi&rsquo;s democratic governance.</p>\r\n\r\n<p>Established under the Trustees Incorporation Act, the PSA is a professional body bringing together academics and practitioners from fields such as political science, governance, public administration, international relations, public policy, and political economy. The association plays a vital role in promoting research, policy dialogue, and critical analysis to support Malawi&rsquo;s political and socio-economic development.</p>\r\n\r\n<p>This year&rsquo;s conference holds special significance as it marks <strong>15 years since the establishment of PSA</strong>, and comes more than three decades after Malawi&rsquo;s transition to multiparty democracy in 1993. It also follows the <strong>2025 general elections</strong>, which ushered in a new administration and sparked renewed debate on the strength, performance, and independence of democratic institutions.</p>\r\n\r\n<p>The conference will provide a platform for <strong>academics, researchers, policymakers, practitioners, and citizens</strong> to engage in meaningful discussions and share insights on Malawi&rsquo;s evolving political landscape. Key thematic areas include elections and democratic consolidation, governance and institutions, political parties, citizen participation, media and civil society, as well as emerging challenges such as digital influence, climate governance, and security.</p>\r\n\r\n<p>The PSA is inviting <strong>abstract submissions (maximum 300 words)</strong> that clearly outline the research question, methodology, and key arguments. Accepted papers will be presented during the conference and may be considered for publication in the <strong>Malawi Journal of Politics and Public Affairs (MJPPA)</strong> following a rigorous peer-review process.</p>\r\n\r\n<h3><strong>Key Dates</strong></h3>\r\n\r\n<ul>\r\n	<li>\r\n	<p><strong>Abstract Submission Deadline:</strong> 30 April 2026</p>\r\n	</li>\r\n	<li>\r\n	<p><strong>Notification of Acceptance:</strong> 20 May 2026</p>\r\n	</li>\r\n	<li>\r\n	<p><strong>Full Paper Submission:</strong> 20 July 2026</p>\r\n	</li>\r\n	<li>\r\n	<p><strong>Conference Dates:</strong> 8&ndash;9 August 2026</p>\r\n	</li>\r\n</ul>\r\n\r\n<h3><strong>Submission &amp; Enquiries</strong></h3>\r\n\r\n<p>All abstracts should be sent via email to:<br />\r\n📧 <strong><a href=\"mailto:malawipsa@gmail.com\">malawipsa@gmail.com</a></strong><br />\r\n📧 <strong><a href=\"mailto:hkayuni@unima.ac.mw\">hkayuni@unima.ac.mw</a></strong><br />\r\n📧 <strong><a href=\"mailto:bdulani@unima.ac.mw\">bdulani@unima.ac.mw</a></strong></p>\r\n\r\n<p>Researchers and interested participants are encouraged to submit their work early and be part of this important academic and policy dialogue shaping Malawi&rsquo;s democratic future.</p>\r\n\r\n<hr />\r\n<h3>📄 <strong>Full Conference Guidelines</strong></h3>\r\n\r\n<p>For detailed submission requirements, formatting guidelines, and full conference information, <strong>please download the full Call for Papers document using the button below</strong>.</p>\r\n', NULL, 'uploads/news/2026/04/1776433169_8388ad8f.jpg', 'Admin', '2026-04-17', 'Conference', 'Malawi, Political Science,  Africa, Academic Conference, PSA Malawi, Malaw', 'published', '2026-04-17 13:39:29', 1, 'uploads/pdfs/1776433169_a1740dc8.pdf', 0),
(25, 'Webinar 7 | The Politicization of Public Appointments and Promotions in Malawi (Insights from PhD Thesis)', 'webinar-7-the-politicization-of-public-appointments-and-promotions-in-malawi-insights-from-phd-thesis', '<p>The Political Science Association of Malawi (PSA) invites you to the seventh installment of its Webinar Series, focusing on a critical governance challenge: the increasing politicization of public appointments and promotions in Malawi.&nbsp;As concerns over merit, transparency, and institutional integrity continue to grow, this webinar brings together scholars and practitioners to discuss the causes, consequences, and potential reforms related to political interference in the public sector.</p>\r\n\r\n<p>Date: Monday, 11th May 2026<br />\r\nTime: 6:00 PM &ndash; 7:30 PM<br />\r\nPlatform: Microsoft Teams (scan the QR code to join) | Live on Facebook at fb.com/psamw<br />\r\nFeatured Panelists:</p>\r\n\r\n<p>Dr. Susan Mtuwa &ndash; Keynote Address<br />\r\nBenedicto Kondowe, Chairperson, National Advocacy Platform (NAP) &ndash; Discussant<br />\r\nPeter Chisi, Director of Civil and Political Rights, Malawi Human Rights Commission &ndash; Moderator</p>\r\n\r\n<p>All are welcome to attend. For more information, visit www.malawipsa.org or email admin@malawipsa.org.<br />\r\nFollow us on FACEBOOK`1&nbsp;for live updates.</p>\r\n', NULL, 'uploads/news/2026/05/1778387910_5bda36e5.jpg', '$Admin', '2026-05-10', 'Webinar Series', 'Politicisation, PubliAppointments, Promotions', 'published', '2026-05-10 04:38:30', 1, 'uploads/pdfs/1778387910_dc305a16.pdf', 0);

-- --------------------------------------------------------

--
-- Table structure for table `psa_members`
--

CREATE TABLE `psa_members` (
  `id` int NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `qualification` text,
  `employer` text,
  `expertise` text,
  `contacts` text,
  `image` varchar(255) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscribers`
--

CREATE TABLE `subscribers` (
  `id` int NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `status` enum('pending','approved') DEFAULT 'pending',
  `subscribed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `approved` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `team`
--

CREATE TABLE `team` (
  `id` int NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `team_members`
--

CREATE TABLE `team_members` (
  `id` int NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `category` varchar(100) NOT NULL,
  `image` varchar(255) NOT NULL,
  `facebook` varchar(255) NOT NULL,
  `twitter` varchar(255) NOT NULL,
  `linkedin` varchar(255) NOT NULL,
  `google` varchar(255) NOT NULL,
  `bio` text,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('active','inactive') DEFAULT 'active',
  `ordering` int DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `team_members`
--

INSERT INTO `team_members` (`id`, `name`, `position`, `category`, `image`, `facebook`, `twitter`, `linkedin`, `google`, `bio`, `photo`, `created_at`, `status`, `ordering`) VALUES
(8, 'Dr. Blessings Chinsinga', 'Patron', 'Patron', '1776533480_bless.jpg', 'https://facebook.com/', 'https://x.com/', 'https://linkedin.com/', '', NULL, NULL, '2026-04-18 17:31:20', 'active', 0),
(9, 'Dr. Henry Chingaipe', 'President', 'Executive', '1776534170_team-page-8.jpg', 'https://facebook.com/', 'https://x.com/', 'https://linkedin.com/', '', NULL, NULL, '2026-04-18 17:33:49', 'active', 0),
(11, 'Dr. Henry Chingaipe', 'President', 'Members of the Executive', '1776534252_team-page-8.jpg', 'https://facebook.com/', 'https://x.com/', 'https://linkedin.com/', '', NULL, NULL, '2026-04-18 17:44:12', 'active', 0),
(12, 'Ms. Sellina Nyankowani Kainja', 'Vice President', 'Members of the Executive', '1776534316_team-page-3.jpg', 'https://facebook.com/', 'https://x.com/', 'https://linkedin.com/', '', NULL, NULL, '2026-04-18 17:45:16', 'active', 0),
(13, 'Makhumbo Munthali', 'Secretary General', 'Members of the Executive', '1776538047_team-page-2.jpg', 'https://facebook.com/', 'https://x.com/', 'https://linkedin.com/', '', NULL, NULL, '2026-04-18 18:47:27', 'active', 0),
(14, 'Mr. George A. Mhango', 'Tresurer General', 'Members of the Executive', '1776541775_team-page-4.jpg', 'https://facebook.com/', 'https://x.com/', 'https://linkedin.com/', '', NULL, NULL, '2026-04-18 19:49:35', 'active', 0),
(15, 'Dr. Michael Jana', 'Councilor', 'Members of the Executive', '1776667332_team-page-5.jpg', 'https://facebook.com/', 'https://x.com/', 'https://linkedin.com/', '', NULL, NULL, '2026-04-20 06:42:12', 'active', 0),
(16, 'Raphael Nedi', 'PSA Admin', 'Secretariate', '1778360476_Screenshot 2026-05-09 230039.png', '', '', '', '', NULL, NULL, '2026-04-21 10:09:29', 'active', 0),
(17, 'Maureen Kapanga', 'Councilor', 'Members of the Executive', '1778356866_Maureen.jpeg', '', '', '', '', NULL, NULL, '2026-05-09 20:01:06', 'active', 0),
(18, 'Mavuto Bamusi', 'Publicity Secretary', 'Members of the Executive', '1778358701_Mavuto.jpeg', '', '', '', '', NULL, NULL, '2026-05-09 20:31:41', 'active', 0),
(19, 'Prof. Happy M. Kayuni', 'Co-Editor in Chief', 'Editorial Team', '1778360204_Screenshot 2026-05-09 225506.png', '', '', '', '', NULL, NULL, '2026-05-09 20:36:52', 'active', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `news_id` (`news_id`),
  ADD KEY `idx_comments_parent` (`parent_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `event_gallery`
--
ALTER TABLE `event_gallery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_gallery_ibfk_1` (`event_id`);

--
-- Indexes for table `event_rsvps`
--
ALTER TABLE `event_rsvps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_rsvps_ibfk_1` (`event_id`);

--
-- Indexes for table `journals`
--
ALTER TABLE `journals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `journal_comments`
--
ALTER TABLE `journal_comments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `journal_topics`
--
ALTER TABLE `journal_topics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `journal_id` (`journal_id`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `psa_members`
--
ALTER TABLE `psa_members`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscribers`
--
ALTER TABLE `subscribers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `team`
--
ALTER TABLE `team`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `event_gallery`
--
ALTER TABLE `event_gallery`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `event_rsvps`
--
ALTER TABLE `event_rsvps`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `journals`
--
ALTER TABLE `journals`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `journal_comments`
--
ALTER TABLE `journal_comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `journal_topics`
--
ALTER TABLE `journal_topics`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `psa_members`
--
ALTER TABLE `psa_members`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `subscribers`
--
ALTER TABLE `subscribers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `team`
--
ALTER TABLE `team`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `event_gallery`
--
ALTER TABLE `event_gallery`
  ADD CONSTRAINT `event_gallery_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_rsvps`
--
ALTER TABLE `event_rsvps`
  ADD CONSTRAINT `event_rsvps_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
