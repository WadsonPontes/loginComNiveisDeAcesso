-- phpMyAdmin SQL Dump
-- version 4.9.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 14-Out-2019 às 05:23
-- Versão do servidor: 10.4.8-MariaDB
-- versão do PHP: 7.3.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `teste`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(60) NOT NULL,
  `email` varchar(60) NOT NULL,
  `tel` varchar(60) NOT NULL,
  `nivel` varchar(60) NOT NULL,
  `senha` varchar(60) NOT NULL,
  `token` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `tel`, `nivel`, `senha`, `token`) VALUES
(46, 'Teste', 'teste@teste.com', '0123456789', 'Cliente', '$2y$10$qNXJI8xXuWEgUqedU.YJlO4m9oDcR789O5mQbFNZFc53CMCtYFtWu', 'wQoJsmikfyili/Sa5JvYUSQaic1/uU1R2zzgts4MDqSleDeTux4g0WSt2Sxmt51yAzOB++9NxF18ZzA8mHZoBt9TXE3lBRuRHzeMrSqIYuM='),
(47, 'Admin Master', 'admin@empresa.com', '8488888888', 'Admin', '$2y$10$Y.vGQln0E3fnnHIR0oAozubSNHW2BkpraSr77v18QXrCG6U1JETRO', 'lHPk0u48+ta0+l2T4wqXOPmAr8Ck+aWcJsTshb83eRqT9mLSeIpKkwEXI+IDnWdOBYTTdM3vyDP50kkbt/XHszLCld23z8CwjdRo8Kr/CDU='),
(48, 'novo novo', 'novo@novo.com', '558488008800', 'Usuario', '$2y$10$FedV/vYnc4MJehj3i464eur/rppH8FJJ86Aqd.jU2xor0n1bPBufW', 'vVhPrHbOUyUeQQ6IgYdeyTJF5elPixaiKwc12gswjVFX4LY908v9VNGyT/MnRkKVrq85NMLLJFr3XiM1pSOb8UcmfHtaHp7mgT7UTenqP4c=');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
