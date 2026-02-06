CREATE TABLE `api_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT 'ID del usuario ERP, NULL si es token del sistema',
  `provider` varchar(50) NOT NULL DEFAULT 'facture_app',
  `access_token` text NOT NULL,
  `refresh_token` text NOT NULL,
  `expires_in` int(11) NOT NULL,
  `scope` varchar(255) DEFAULT NULL,
  `token_type` varchar(50) DEFAULT 'Bearer',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
