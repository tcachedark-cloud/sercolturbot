<?php
require_once '../models/Cliente.php';
require_once '../models/Reserva.php';
require_once '../models/Guia.php';
require_once '../models/Bus.php';
class DashboardController{
 function datos($pdo){
  return [
   'clientes'=>(new Cliente)->listar($pdo),
   'reservas'=>(new Reserva)->listar($pdo),
   'guias'=>(new Guia)->listar($pdo),
   'buses'=>(new Bus)->listar($pdo)
  ];
 }
}
?>