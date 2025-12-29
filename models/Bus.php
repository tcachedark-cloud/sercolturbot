<?php
class Bus{ function listar($pdo){ return $pdo->query("SELECT * FROM buses")->fetchAll(PDO::FETCH_ASSOC); } }
?>