<?php
require 'DbHandler.php';

class DebitoRecurrente
{
    public function debito()
    {
        $db = new DbHandler();
        $resultado = $db->debitarMembresiasDiarias();
        return $resultado;
    }
}

$debitos = new DebitoRecurrente();
$debitos->debito();