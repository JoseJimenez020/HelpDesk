<?php
session_start();

class Conectar
{
    protected $dbh;

    function Conexion()
    {
        try {
            //Local
            $conectar = $this->dbh = new PDO("mysql:local=127.0.0.1;dbname=helpdesk", "root", "");
            //Produccion
            //$conectar = $this->dbh = new PDO("mysql:host=localhost;dbname=andercode_helpdesk1","andercode","contraseña");
            return $conectar;
        } catch (Exception $e) {
            print "¡Error BD!: " . $e->getMessage() . "<br/>";
            die();
        }
    }

    public function set_names()
    {
        return $this->dbh->query("SET NAMES 'utf8'");
    }

    public static function ruta()
    {
        //Local
        return "http://helpdesk.local.com/";
        //Produccion
        //return "http://helpdesk.anderson-bastidas.com/";
    }

}
?>