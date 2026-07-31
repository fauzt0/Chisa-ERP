-- Fix: Agregar 'produccion' al ENUM de origen_tipo en preordenes
-- Requerido para que el módulo de Producción pueda generar pre-órdenes
-- Ejecutar: mysql st32477_chisa < database/fix_preordenes_origen_tipo.sql

ALTER TABLE `preordenes` 
  MODIFY COLUMN `origen_tipo` ENUM('venta','obra','interno','produccion') 
  NOT NULL DEFAULT 'interno'
  COMMENT 'Qué generó la necesidad';
