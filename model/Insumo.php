<?php
class Insumo
{
    public $id;
    public $fecha;
    public $nombre;
    public $precio;
    public $local;
    public $estado;
    public $servicio;
    public $id_orden;

    function __construct(string $nombre, int $precio, string $local, int $estado, string $servicio, int $id_orden)
    {
        $this->id = null;
        $this->nombre = $nombre;
        $this->fecha = date("Y-m-d");
        $this->precio = $precio;
        $this->local = $local;
        $this->estado = $estado;
        $this->servicio = $servicio;
        $this->id_orden = $id_orden;
    }
}