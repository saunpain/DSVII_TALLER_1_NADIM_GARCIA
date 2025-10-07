<?php
interface Inventariable {
    public function obtenerInformacionInventario(): string;
}

class Producto implements Inventariable {
    public $id;
    public $nombre;
    public $precio;
    public $estado;
    public $categoria;
    public $fechaIngreso;

    public function __construct($data) {
        $this->id = $data['id'] ?? null;
        $this->nombre = $data['nombre'] ?? '';
        $this->precio = $data['precio'] ?? 0;
        $this->estado = $data['estado'] ?? 'disponible';
        $this->categoria = $data['categoria'] ?? '';
        $this->fechaIngreso = $data['fechaIngreso'] ?? date('Y-m-d');
    }

    public function obtenerInformacionInventario(): string {
        return "Producto general sin información específica.";
    }

    public function toArray() {
        return get_object_vars($this);
    }
}

class ProductoElectronico extends Producto {
    public $garantiaMeses;

    public function __construct($data) {
        parent::__construct($data);
        $this->garantiaMeses = $data['garantiaMeses'] ?? 0;
    }

    public function obtenerInformacionInventario(): string {
        return "Producto electrónico con garantía de: {$this->garantiaMeses} meses";
    }
}

class ProductoAlimento extends Producto {
    public $fechaVencimiento;

    public function __construct($data) {
        parent::__construct($data);
        $this->fechaVencimiento = $data['fechaVencimiento'] ?? '';
    }

    public function obtenerInformacionInventario(): string {
        return "Producto alimentairo que vence el: {$this->fechaVencimiento}";
    }
}

class ProductoRopa extends Producto {
    public $talla;

    public function __construct($data) {
        parent::__construct($data);
        $this->talla = $data['talla'] ?? '';
    }

    public function obtenerInformacionInventario(): string {
        return "Producto textil de talla: {$this->talla}";
    }
}

class GestorInventario {
    private $archivo;
    public $items;

    public function __construct($archivo) {
        $this->archivo = $archivo;
        $this->cargarDesdeArchivo();
    }

    public function cargarDesdeArchivo() {
        if (!file_exists($this->archivo)) {
            $this->items = [];
            return;
        }

        $json = file_get_contents($this->archivo);
        $datos = json_decode($json, true) ?? [];

        $this->items = [];
        foreach ($datos as $item) {
            switch ($item['categoria']) {
                case 'electronico':
                    $this->items[] = new ProductoElectronico($item);
                    break;
                case 'alimento':
                    $this->items[] = new ProductoAlimento($item);
                    break;
                case 'ropa':
                    $this->items[] = new ProductoRopa($item);
                    break;
                default:
                    $this->items[] = new Producto($item);
                    break;
            }
        }
    }

    public function persistirEnArchivo() {
        $array = array_map(fn($prod) => $prod->toArray(), $this->items);
        file_put_contents($this->archivo, json_encode($array, JSON_PRETTY_PRINT));
    }

    public function obtenerTodos() {
        return $this->items;
    }

    public function obtenerMaximoId() {
        $max = 0;
        foreach ($this->items as $p) {
            if ($p->id > $max) $max = $p->id;
        }
        return $max;
    }

    public function agregar($nuevoProducto) {
        $nuevoProducto->id = $this->obtenerMaximoId() + 1;
        $this->items[] = $nuevoProducto;
        $this->persistirEnArchivo();
    }

    public function eliminar($idProducto) {
        foreach ($this->items as $i => $p) {
            if ($p->id == $idProducto) {
                unset($this->items[$i]);
                $this->items = array_values($this->items);
                $this->persistirEnArchivo();
                return true;
            }
        }
        return false;
    }

    public function actualizar($productoActualizado) {
        foreach ($this->items as $i => $p) {
            if ($p->id == $productoActualizado->id) {
                $this->items[$i] = $productoActualizado;
                $this->persistirEnArchivo();
                return true;
            }
        }
        return false;
    }

    public function cambiarEstado($idProducto, $estadoNuevo) {
        foreach ($this->items as $i => $p) {
            if ($p->id == $idProducto) {
                $this->items[$i]->estado = $estadoNuevo;
                $this->persistirEnArchivo();
                return true;
            }
        }
        return false;
    }

    public function filtrarPorEstado($estadoBuscado) {
        if (empty($estadoBuscado)) {
            return $this->items;
        }
        return array_filter($this->items, fn($p) => $p->estado == $estadoBuscado);
    }

    public function obtenerPorId($idBuscado) {
        foreach ($this->items as $p) {
            if ($p->id == $idBuscado) return $p;
        }
        return null;
    }
}
?>
