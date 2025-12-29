<?php
class Guia{ function listar($pdo){ return $pdo->query("SELECT * FROM guias")->fetchAll(PDO::FETCH_ASSOC); } }
?>