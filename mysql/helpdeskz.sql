-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: db:3306
-- Tiempo de generación: 02-07-2025 a las 21:10:28
-- Versión del servidor: 8.0.42
-- Versión de PHP: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `helpdesk`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hdzfv_api`
--

CREATE TABLE `hdzfv_api` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `date` int NOT NULL DEFAULT '0',
  `last_update` int NOT NULL,
  `permissions` text,
  `ip_address` mediumtext,
  `active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hdzfv_articles`
--

CREATE TABLE `hdzfv_articles` (
  `id` int NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text,
  `category` int DEFAULT '0',
  `staff_id` int NOT NULL DEFAULT '0',
  `date` int NOT NULL,
  `last_update` int NOT NULL DEFAULT '0',
  `views` int NOT NULL DEFAULT '0',
  `public` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hdzfv_attachments`
--

CREATE TABLE `hdzfv_attachments` (
  `id` int NOT NULL,
  `name` varchar(200) NOT NULL,
  `enc` varchar(200) NOT NULL,
  `filetype` varchar(200) DEFAULT NULL,
  `article_id` int NOT NULL DEFAULT '0',
  `ticket_id` int NOT NULL DEFAULT '0',
  `msg_id` int NOT NULL DEFAULT '0',
  `filesize` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hdzfv_canned_response`
--

CREATE TABLE `hdzfv_canned_response` (
  `id` int NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` mediumtext,
  `position` int NOT NULL DEFAULT '1',
  `date` int NOT NULL DEFAULT '0',
  `last_update` int NOT NULL DEFAULT '0',
  `staff_id` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hdzfv_config`
--

CREATE TABLE `hdzfv_config` (
  `id` int NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `site_name` varchar(255) DEFAULT NULL,
  `windows_title` varchar(255) DEFAULT NULL,
  `page_size` int NOT NULL DEFAULT '0',
  `date_format` varchar(100) DEFAULT NULL,
  `timezone` varchar(100) DEFAULT NULL,
  `maintenance` tinyint(1) NOT NULL DEFAULT '0',
  `maintenance_message` text,
  `recaptcha` tinyint(1) NOT NULL DEFAULT '0',
  `recaptcha_sitekey` varchar(255) DEFAULT NULL,
  `recaptcha_privatekey` varchar(255) DEFAULT NULL,
  `login_attempt` int NOT NULL DEFAULT '0',
  `login_attempt_minutes` int NOT NULL DEFAULT '1',
  `reply_order` enum('asc','desc') NOT NULL DEFAULT 'asc',
  `tickets_page` int NOT NULL DEFAULT '1',
  `tickets_replies` int NOT NULL DEFAULT '1',
  `overdue_time` int NOT NULL DEFAULT '48',
  `ticket_autoclose` int NOT NULL DEFAULT '96',
  `ticket_attachment` tinyint(1) NOT NULL DEFAULT '0',
  `ticket_attachment_number` int NOT NULL DEFAULT '1',
  `ticket_file_size` double NOT NULL DEFAULT '2',
  `ticket_file_type` mediumtext,
  `kb_articles` int NOT NULL DEFAULT '4',
  `kb_maxchar` int NOT NULL DEFAULT '200',
  `kb_popular` int NOT NULL DEFAULT '4',
  `kb_latest` int NOT NULL DEFAULT '4'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `hdzfv_config`
--

INSERT INTO `hdzfv_config` (`id`, `logo`, `site_name`, `windows_title`, `page_size`, `date_format`, `timezone`, `maintenance`, `maintenance_message`, `recaptcha`, `recaptcha_sitekey`, `recaptcha_privatekey`, `login_attempt`, `login_attempt_minutes`, `reply_order`, `tickets_page`, `tickets_replies`, `overdue_time`, `ticket_autoclose`, `ticket_attachment`, `ticket_attachment_number`, `ticket_file_size`, `ticket_file_type`, `kb_articles`, `kb_maxchar`, `kb_popular`, `kb_latest`) VALUES
(1, '', 'HelpDesk', 'HelpDesk', 25, 'd F Y h:i a', 'America/Mexico_City', 0, NULL, 0, '', '', 3, 5, 'desc', 15, 15, 48, 96, 1, 1, 2.5, 'a:3:{i:0;s:3:\"jpg\";i:1;s:3:\"png\";i:2;s:3:\"gif\";}', 2, 200, 3, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hdzfv_contact`
--

CREATE TABLE `hdzfv_contact` (
  `id` int NOT NULL,
  `contactname` varchar(50) NOT NULL,
  `registration` int NOT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hdzfv_custom_fields`
--

CREATE TABLE `hdzfv_custom_fields` (
  `id` int NOT NULL,
  `type` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `value` text,
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `departments` mediumtext,
  `display` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hdzfv_departments`
--

CREATE TABLE `hdzfv_departments` (
  `id` int NOT NULL,
  `dep_order` int NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `private` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hdzfv_emails`
--

CREATE TABLE `hdzfv_emails` (
  `id` int NOT NULL,
  `default` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(200) DEFAULT NULL,
  `email` varchar(200) NOT NULL,
  `department_id` int NOT NULL DEFAULT '0',
  `created` int NOT NULL DEFAULT '0',
  `last_update` int NOT NULL DEFAULT '0',
  `outgoing_type` enum('php','smtp') NOT NULL,
  `smtp_host` varchar(200) DEFAULT NULL,
  `smtp_port` varchar(10) DEFAULT NULL,
  `smtp_encryption` varchar(10) DEFAULT NULL,
  `smtp_username` varchar(200) DEFAULT NULL,
  `smtp_password` varchar(200) DEFAULT NULL,
  `incoming_type` varchar(10) DEFAULT NULL,
  `imap_host` varchar(200) DEFAULT NULL,
  `imap_port` varchar(10) DEFAULT NULL,
  `imap_username` varchar(200) DEFAULT NULL,
  `imap_password` varchar(200) DEFAULT NULL,
  `imap_minutes` double NOT NULL DEFAULT '5'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `hdzfv_emails`
--

INSERT INTO `hdzfv_emails` (`id`, `default`, `name`, `email`, `department_id`, `created`, `last_update`, `outgoing_type`, `smtp_host`, `smtp_port`, `smtp_encryption`, `smtp_username`, `smtp_password`, `incoming_type`, `imap_host`, `imap_port`, `imap_username`, `imap_password`, `imap_minutes`) VALUES
(1, 1, 'HelpDeskZ', 'system@ticket.com.mx', 1, 1660778385, 0, 'php', 'mail.gmail.com', '587', 'tls', 'username@gmail.com', '', '', '', '', '', '', 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hdzfv_emails_tpl`
--

CREATE TABLE `hdzfv_emails_tpl` (
  `id` varchar(255) NOT NULL,
  `position` smallint NOT NULL,
  `name` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` mediumtext NOT NULL,
  `last_update` int NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `hdzfv_emails_tpl`
--

INSERT INTO `hdzfv_emails_tpl` (`id`, `position`, `name`, `subject`, `message`, `last_update`, `status`) VALUES
('autoresponse', 4, 'New Message Autoresponse', '[#%ticket_id%] %ticket_subject%', '<p>Dear %client_name%,</p>\r\n<p>Your reply to support request #%ticket_id% has been noted.</p>\r\n<p>Ticket Details <br />--------------------<br />Ticket ID: %ticket_id% <br />Department: %ticket_department% <br />Status: %ticket_status% <br />Priority: %ticket_priority% <br />Helpdesk: %support_url%</p>', 0, 0),
('lost_password', 2, 'Lost password confirmation', 'Recuperación de Password - %company_name%', '<p>Hemos recibido una solicitud para restablecer la contrase&ntilde;a de su cuenta para el %company_name% helpdesk (%helpdesk_url%).</p>\r\n<p>El nuevo passsword es: %client_password%</p>\r\n<p>Gracias, <br />%company_name% <br />Helpdesk: %support_url%</p>', 1717908303, 2),
('new_ticket', 3, 'New ticket creation', '[#%ticket_id%] %ticket_subject%', '<p>Querido %client_name%,</p>\r\n<p>Gracias por contactarnos. Esto es una respuesta automatica de confirmaci&oacute;n de recepci&oacute;n de su ticket. One of our agents will get back to you as soon as possible.</p>\r\n<p>For your records, the details of the ticket are listed below. When replying, please make sure that the ticket ID is kept in the subject line to ensure that your replies are tracked appropriately.</p>\r\n<p>Ticket ID: %ticket_id% <br />Subject: %ticket_subject% <br />Department: %ticket_department% <br />Status: %ticket_status% <br />Priority: %ticket_priority%</p>\r\n<p>You can check the status of or reply to this ticket online at: %support_url%</p>\r\n<p>Regards, <br />%company_name%</p>', 1717908529, 1),
('new_user', 1, 'Welcome email registration', 'Bienvenido a %company_name% - HelpDesk', '<p>Hola,</p>\r\n<p>Este correo electr&oacute;nico es la confirmaci&oacute;n de que ya est&aacute; registrado en nuestro servicio de asistencia.</p>\r\n<p><strong>Registered email:</strong> %client_email% <br /><strong>Password:</strong> %client_password%</p>\r\n<p>Puede visitar el servicio de asistencia para consultar art&iacute;culos y ponerse en contacto con nosotros en cualquier momento:</p>\r\n<p>%support_url%</p>\r\n<p>Gracias por registrarse!</p>\r\n<p>%company_name%<br />Helpdesk: %support_url%</p>', 1717908211, 1),
('staff_reply', 5, 'Staff Reply', 'Re: [#%ticket_id%] %ticket_subject%', '<p>%message% </p>\r\n<p>-------------------------------------------------------------<br />Ticket Details<br />-------------------------------------------------------------<br /><strong>Ticket ID:</strong> %ticket_id% <br /><strong>Department:</strong> %ticket_department% <br /><strong>Status:</strong> %ticket_status% <br /><strong>Priority:</strong> %ticket_priority% <br /><strong>Helpdesk:</strong> %support_url%</p>', 0, 2),
('staff_ticketnotification', 6, 'New ticket notification to staff', 'New ticket notification', '<p>Dear %staff_name%,</p>\r\n<p>A new ticket has been created in department assigned for you, please login to staff panel to answer it.</p>\r\n<p>Ticket Details<br />-------------------<br />Ticket ID: %ticket_id% <br />Department: %ticket_department% <br />Status: %ticket_status% <br />Priority: %ticket_priority% <br />Helpdesk: %support_url%</p>', 0, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hdzfv_kb_category`
--

CREATE TABLE `hdzfv_kb_category` (
  `id` int NOT NULL,
  `name` varchar(200) NOT NULL,
  `position` int NOT NULL,
  `parent` int NOT NULL DEFAULT '0',
  `public` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hdzfv_login_attempt`
--

CREATE TABLE `hdzfv_login_attempt` (
  `ip` varchar(200) NOT NULL,
  `attempts` int NOT NULL DEFAULT '0',
  `date` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hdzfv_login_log`
--

CREATE TABLE `hdzfv_login_log` (
  `id` int NOT NULL,
  `date` int NOT NULL,
  `staff_id` int NOT NULL DEFAULT '0',
  `ip` varchar(255) NOT NULL,
  `agent` varchar(255) NOT NULL,
  `success` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hdzfv_priority`
--

CREATE TABLE `hdzfv_priority` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `color` varchar(10) NOT NULL DEFAULT '#000000'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `hdzfv_priority`
--

INSERT INTO `hdzfv_priority` (`id`, `name`, `color`) VALUES
(1, 'Baja', '#8A8A8A'),
(2, 'Medía', '#000000'),
(3, 'Alta', '#F07D18'),
(4, 'Urgente', '#E826C6'),
(5, 'Emergencia', '#E06161'),
(6, 'Critico', '#FF0000');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hdzfv_staff`
--

CREATE TABLE `hdzfv_staff` (
  `id` int NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `registration` int NOT NULL DEFAULT '0',
  `login` int NOT NULL DEFAULT '0',
  `last_login` int NOT NULL DEFAULT '0',
  `department` mediumtext,
  `timezone` varchar(255) DEFAULT NULL,
  `signature` longtext,
  `avatar` varchar(200) DEFAULT NULL,
  `two_factor` varchar(255) DEFAULT NULL,
  `admin` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `hdzfv_staff`
--

INSERT INTO `hdzfv_staff` (`id`, `username`, `password`, `fullname`, `email`, `token`, `registration`, `login`, `last_login`, `department`, `timezone`, `signature`, `avatar`, `two_factor`, `admin`, `active`) VALUES
(1, 'admin', '$2y$10$RQfD17YP5fe9CvHofU90fuEs95hj7Mwe8jQb/.nBRISXUqlChb8UW', 'System', 'admin@ticket.com.mx', '7ac4109001b6e07174728fa8ad2031bde676bef2', 1660778385, 1722980195, 1721503547, NULL, 'America/Mexico_City', '', NULL, NULL, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hdzfv_tickets`
--

CREATE TABLE `hdzfv_tickets` (
  `id` int NOT NULL,
  `department_id` int NOT NULL DEFAULT '0',
  `priority_id` int NOT NULL DEFAULT '0',
  `user_id` int NOT NULL DEFAULT '0',
  `subject` varchar(255) NOT NULL,
  `date` int NOT NULL DEFAULT '0',
  `last_update` int NOT NULL DEFAULT '0',
  `status` smallint NOT NULL DEFAULT '1',
  `replies` int NOT NULL DEFAULT '0',
  `last_replier` tinyint(1) DEFAULT '0',
  `custom_vars` mediumtext
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hdzfv_tickets_messages`
--

CREATE TABLE `hdzfv_tickets_messages` (
  `id` int NOT NULL,
  `ticket_id` int NOT NULL DEFAULT '0',
  `date` int NOT NULL DEFAULT '0',
  `customer` int NOT NULL DEFAULT '1',
  `staff_id` int NOT NULL DEFAULT '0',
  `message` text,
  `ip` varchar(255) DEFAULT NULL,
  `email` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hdzfv_ticket_notes`
--

CREATE TABLE `hdzfv_ticket_notes` (
  `id` int NOT NULL,
  `ticket_id` int NOT NULL,
  `staff_id` int NOT NULL,
  `date` int NOT NULL,
  `message` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hdzfv_users`
--

CREATE TABLE `hdzfv_users` (
  `id` int NOT NULL,
  `fullname` varchar(250) NOT NULL DEFAULT 'Guest',
  `email` varchar(250) NOT NULL,
  `password` varchar(150) NOT NULL,
  `registration` int NOT NULL DEFAULT '0',
  `last_login` int NOT NULL DEFAULT '0',
  `token` varchar(255) DEFAULT NULL,
  `timezone` varchar(200) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `status` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `hdzfv_api`
--
ALTER TABLE `hdzfv_api`
  ADD PRIMARY KEY (`id`),
  ADD KEY `token` (`token`);

--
-- Indices de la tabla `hdzfv_articles`
--
ALTER TABLE `hdzfv_articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category` (`category`);

--
-- Indices de la tabla `hdzfv_attachments`
--
ALTER TABLE `hdzfv_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `article_id` (`article_id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `msg_id` (`msg_id`);

--
-- Indices de la tabla `hdzfv_canned_response`
--
ALTER TABLE `hdzfv_canned_response`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `hdzfv_config`
--
ALTER TABLE `hdzfv_config`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `hdzfv_custom_fields`
--
ALTER TABLE `hdzfv_custom_fields`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `hdzfv_departments`
--
ALTER TABLE `hdzfv_departments`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `hdzfv_emails`
--
ALTER TABLE `hdzfv_emails`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `hdzfv_emails_tpl`
--
ALTER TABLE `hdzfv_emails_tpl`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `hdzfv_kb_category`
--
ALTER TABLE `hdzfv_kb_category`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `hdzfv_login_attempt`
--
ALTER TABLE `hdzfv_login_attempt`
  ADD UNIQUE KEY `ip` (`ip`);

--
-- Indices de la tabla `hdzfv_login_log`
--
ALTER TABLE `hdzfv_login_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indices de la tabla `hdzfv_priority`
--
ALTER TABLE `hdzfv_priority`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `hdzfv_staff`
--
ALTER TABLE `hdzfv_staff`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `hdzfv_tickets`
--
ALTER TABLE `hdzfv_tickets`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `hdzfv_tickets_messages`
--
ALTER TABLE `hdzfv_tickets_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`);

--
-- Indices de la tabla `hdzfv_ticket_notes`
--
ALTER TABLE `hdzfv_ticket_notes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `hdzfv_users`
--
ALTER TABLE `hdzfv_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `hdzfv_api`
--
ALTER TABLE `hdzfv_api`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `hdzfv_articles`
--
ALTER TABLE `hdzfv_articles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `hdzfv_attachments`
--
ALTER TABLE `hdzfv_attachments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `hdzfv_canned_response`
--
ALTER TABLE `hdzfv_canned_response`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `hdzfv_config`
--
ALTER TABLE `hdzfv_config`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `hdzfv_custom_fields`
--
ALTER TABLE `hdzfv_custom_fields`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `hdzfv_departments`
--
ALTER TABLE `hdzfv_departments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `hdzfv_emails`
--
ALTER TABLE `hdzfv_emails`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `hdzfv_kb_category`
--
ALTER TABLE `hdzfv_kb_category`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `hdzfv_login_log`
--
ALTER TABLE `hdzfv_login_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `hdzfv_priority`
--
ALTER TABLE `hdzfv_priority`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `hdzfv_staff`
--
ALTER TABLE `hdzfv_staff`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `hdzfv_tickets`
--
ALTER TABLE `hdzfv_tickets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `hdzfv_tickets_messages`
--
ALTER TABLE `hdzfv_tickets_messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `hdzfv_ticket_notes`
--
ALTER TABLE `hdzfv_ticket_notes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `hdzfv_users`
--
ALTER TABLE `hdzfv_users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
