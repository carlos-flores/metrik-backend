<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacturasController extends Controller
{
    /*******************************************
     *              P R U E B A S
     *******************************************/
    public function prueba(Request $request)
    {
        $jwtAth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $checkToken = $jwtAth->checkToken($token);
        if ($checkToken) {
            $usuarios = DB::select('SELECT login from res_users');
            $salida = '';
            foreach ($usuarios as $us) {
                $salida .= $us->login . '<br>';
            }
            return $salida;
        } else {
            return "usuario NO autenticado";
        }

    }

/*******************************************
 *              G E N E R A L E S
 *******************************************/

    public function ultimas(Request $request, $size)
    {
        $jwtAth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $checkToken = $jwtAth->checkToken($token);
        if ($checkToken) {
            $date = date('Y-m-d H:i:s');
            //Restando 2 dias
            //dd($date);
            $mod_date = strtotime($date . "- 360 days");
            $fecha = date("Y-m-d H:i:s", $mod_date);
            //dd($fecha);

            $facturas = DB::select("SELECT ai.number as factura_id, rp.id as id_cliente, rp.name as cliente, ai.city as ciudad_cliente, rcs.name as estado_cliente, ai.amount_total monto,ai.create_date fecha
                                    FROM account_invoice ai
                                    INNER JOIN res_partner rp
                                    ON ai.partner_id = rp.id
                                    INNER JOIN res_country_state rcs
                                    ON rp.state_id = rcs.id
                                    INNER JOIN account_invoice_line ail
                                    ON ail.invoice_id = ai.id
                                    INNER JOIN product_product pp
                                    ON ail.product_id = pp.id
                                    where 
                                    ai.create_date > :fecha
                                    and ai.type = 'out_invoice'
                                    and ai.number is not null
                                    group by ai.number, rp.id,rp.name,ai.city,rcs.name,ai.amount_total,ai.create_date
                                    ORDER BY ai.create_date DESC
                                    limit ".$size, ['fecha' => date("Y-m-d H:i:s", $mod_date)]);
            return $facturas;
        } else {
            return "usuario NO autenticado";
        }

    }

    public function detalleFactura(Request $request, $id)
    {
        $jwtAth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $checkToken = $jwtAth->checkToken($token);
        if ($checkToken) {
            $facturas = DB::select("SELECT ai.number as invoice, rp.name customer, pp.id as id_producto, pp.default_code code, pp.name_template product_name, ail.price_unit, ail.quantity, ail.price_subtotal
                                    FROM account_invoice ai
                                    INNER JOIN res_partner rp
                                    ON ai.partner_id = rp.id
                                    INNER JOIN res_country_state rcs
                                    ON rp.state_id = rcs.id
                                    INNER JOIN account_invoice_line ail
                                    ON ail.invoice_id = ai.id
                                    INNER JOIN product_product pp
                                    ON ail.product_id = pp.id
                                    where ai.number = :id
                                    ORDER BY pp.default_code DESC;", ['id' => $id]);
            
            return $facturas;
        } else {
            return "usuario NO autenticado";
        }
    }

    public function clientes(Request $request, String $fechaIni, String $fechaFin)
    {
        $jwtAth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $checkToken = $jwtAth->checkToken($token);
        if ($checkToken) {
            $facturas = DB::select("SELECT rp.id, rp.name customer
                                    FROM account_invoice ai
                                    INNER JOIN res_partner rp
                                    ON ai.partner_id = rp.id
                                    where ai.create_date >= :fechaIni
                                    and ai.create_date <= :fechaFin
                                    and ai.number is not null
                                    and ai.type = 'out_invoice'
                                    group by rp.id,rp.name
                                    ORDER BY rp.name ASC", ['fechaIni' => date("Y-m-d", strtotime($fechaIni)),'fechaFin' => date("Y-m-d", strtotime($fechaFin))]);
            return $facturas;
        } else {
            return "usuario NO autenticado";
        }
    }

    public function productos(Request $request, String $fechaIni, String $fechaFin)
    {
        $jwtAth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $checkToken = $jwtAth->checkToken($token);
        if ($checkToken) {
            $productos = DB::select("SELECT pp.id as id, pp.default_code as code, pp.name_template as name
                                    FROM account_invoice ai
                                    INNER JOIN account_invoice_line ail
                                    ON ail.invoice_id = ai.id
                                    INNER JOIN product_product pp
                                    ON ail.product_id = pp.id
                                    where 
                                    ai.create_date >= :fechaIni
                                    and ai.create_date <= :fechaFin
                                    and ai.number is not null
                                    and ai.type = 'out_invoice'
                                    group by pp.id, pp.default_code, pp.name_template
                                    ORDER BY pp.name_template ASC, pp.default_code ASC", ['fechaIni' => date("Y-m-d", strtotime($fechaIni)),'fechaFin' => date("Y-m-d", strtotime($fechaFin))]);
            return $productos;
        } else {
            return "usuario NO autenticado";
        }
    }


    public function productosPorPeriodo(Request $request, String $fechaIni, String $fechaFin, $clientes, $productos, $estados, $montoIni, $montoFin)
    {
        $jwtAth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $checkToken = $jwtAth->checkToken($token);
        if ($checkToken) {

            $exploded = explode(",", $clientes);
            $arrayNumber = array();
            foreach ($exploded as $ex) {
                array_push($arrayNumber, intval($ex));
            }

            $explodedProductos = explode(",", $productos);
            $arrayNumberProductos = array();
            foreach ($explodedProductos as $exProducto) {
                array_push($arrayNumberProductos, intval($exProducto));
            }

            $explodedEstados = explode(",", $estados);
            $arrayNumberEstados = array();
            foreach ($explodedEstados as $ex) {
                array_push($arrayNumberEstados, intval($ex));
            }




            $productos = DB::select("SELECT pp.id as id, pp.default_code as code, pp.name_template as name,count(pp.id) as total1, sum(ail.quantity) as total2
                                    FROM account_invoice ai
                                    INNER JOIN account_invoice_line ail
                                    ON ail.invoice_id = ai.id
                                    INNER JOIN res_partner rp
                                    ON ai.partner_id = rp.id
                                    INNER JOIN res_country_state rcs 
                                    ON rp.state_id = rcs.id
                                    INNER JOIN product_product pp
                                    ON ail.product_id = pp.id
                                    where 
                                    ai.create_date >= :fechaIni
                                    and ai.create_date <= :fechaFin
                                    and ai.number is not null
                                    and ai.type = 'out_invoice'
                                    ".($clientes==='TODOS'?'':'and rp.id in('.implode(',',$arrayNumber).') ')." 
                                    ".($productos==='TODOS'?'':'and pp.id in('.implode(',',$arrayNumberProductos).') ')." 
                                    ".($estados==='TODOS'?'':'and rcs.id in('.implode(',',$arrayNumberEstados).') ')." 
                                    ".($montoIni== -1 ?'':'and ai.amount_total >= '.$montoIni.' and ai.amount_total <= '.$montoFin)." 
                                    group by pp.id, pp.default_code, pp.name_template
                                    ORDER BY pp.name_template ASC, pp.default_code ASC", ['fechaIni' => date("Y-m-d", strtotime($fechaIni)),'fechaFin' => date("Y-m-d", strtotime($fechaFin))]);
            return $productos;
        } else {
            return "usuario NO autenticado";
        }
    }



/*******************************************
 *           P O R   P E R I O D O
 *******************************************/

    public function porPeriodo(Request $request, String $fechaIni, $fechaFin, $estados)
    {
        $jwtAth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $checkToken = $jwtAth->checkToken($token);
        if ($checkToken) {

            $explodedEstados = explode(",", $estados);
            $arrayNumberEstados = array();
            foreach ($explodedEstados as $ex) {
                array_push($arrayNumberEstados, intval($ex));
            }

            $facturas = DB::select("select id_cliente,cliente,ciudad_cliente,estado_cliente,calle,codigo_postal,count(id_cliente) as total_facturas from 
            (SELECT ai.number as factura_id, rp.id as id_cliente, rp.name as cliente, ai.city as ciudad_cliente, rcs.name as estado_cliente,rp.street as calle, rp.zip as codigo_postal 
            FROM account_invoice ai 
            INNER JOIN res_partner rp 
            ON ai.partner_id = rp.id 
            INNER JOIN res_country_state rcs 
            ON rp.state_id = rcs.id 
            INNER JOIN account_invoice_line ail 
            ON ail.invoice_id = ai.id 
            INNER JOIN product_product pp 
            ON ail.product_id = pp.id 
            where ai.create_date >= :fechaIni 
            and ai.create_date <= :fechaFin 
            and ai.type = 'out_invoice' 
            and ai.number is not null 
            ".($estados==='TODOS'?'':'and rcs.id in('.implode(',',$arrayNumberEstados).') ')." 
            group by ai.number, rp.id,rp.name,ai.city,rcs.name,rp.street,rp.zip) as facturas 
            group by id_cliente, cliente,ciudad_cliente,estado_cliente,calle,codigo_postal 
            ORDER BY cliente ASC", ['fechaIni' => date("Y-m-d", strtotime($fechaIni)),'fechaFin' => date("Y-m-d", strtotime($fechaFin))]);
            
            return $facturas;
        } else {
            return "usuario NO autenticado";
        }
    }

    public function porPeriodoCliente(Request $request, String $fechaIni, String $fechaFin, $clientes, $estados)
    {
        $jwtAth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $checkToken = $jwtAth->checkToken($token);
        if ($checkToken) {
            
            $exploded = explode(",", $clientes);
            $arrayNumber = array();
            foreach ($exploded as $ex) {
                array_push($arrayNumber, intval($ex));
            }

            $explodedEstados = explode(",", $estados);
            $arrayNumberEstados = array();
            foreach ($explodedEstados as $ex) {
                array_push($arrayNumberEstados, intval($ex));
            }

            $facturas = DB::select("select id_cliente,cliente,ciudad_cliente,estado_cliente,calle,codigo_postal, count(id_cliente) as total_facturas from
                                    (SELECT ai.number as factura_id, rp.id as id_cliente, rp.name as cliente, ai.city as ciudad_cliente, rcs.name as estado_cliente,rp.street as calle, rp.zip as codigo_postal
                                    FROM account_invoice ai
                                    INNER JOIN res_partner rp
                                    ON ai.partner_id = rp.id
                                    INNER JOIN res_country_state rcs
                                    ON rp.state_id = rcs.id
                                    INNER JOIN account_invoice_line ail
                                    ON ail.invoice_id = ai.id
                                    INNER JOIN product_product pp
                                    ON ail.product_id = pp.id
                                    where ai.create_date >= :fechaIni
                                    and ai.create_date <= :fechaFin
                                    and ai.type = 'out_invoice'
                                    and ai.number is not null
                                    and rp.id in (".implode(',',$arrayNumber).")
                                    ".($estados==='TODOS'?'':'and rcs.id in('.implode(',',$arrayNumberEstados).') ')." 
                                    group by ai.number, rp.id,rp.name,ai.city,rcs.name,rp.street,rp.zip) as facturas
                                    group by id_cliente, cliente,ciudad_cliente,estado_cliente,calle,codigo_postal
                                    ORDER BY cliente ASC", ['fechaIni' => date("Y-m-d", strtotime($fechaIni)),'fechaFin' => date("Y-m-d", strtotime($fechaFin))]);
            
            return $facturas;
        } else {
            return "usuario NO autenticado";
        }
    }

    public function porPeriodoMonto(Request $request, String $fechaIni, String $fechaFin, $montoIni, $montoFin, $estados)
    {
        $jwtAth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $checkToken = $jwtAth->checkToken($token);
        if ($checkToken) {
            
$explodedEstados = explode(",", $estados);
$arrayNumberEstados = array();
foreach ($explodedEstados as $ex) {
    array_push($arrayNumberEstados, intval($ex));
}

            $facturas = DB::select("select id_cliente,cliente,ciudad_cliente,estado_cliente,calle,codigo_postal, count(id_cliente) as total_facturas from
                                    (SELECT ai.number as factura_id, rp.id as id_cliente, rp.name as cliente, ai.city as ciudad_cliente, rcs.name as estado_cliente,rp.street as calle, rp.zip as codigo_postal
                                    FROM account_invoice ai
                                    INNER JOIN res_partner rp
                                    ON ai.partner_id = rp.id
                                    INNER JOIN res_country_state rcs
                                    ON rp.state_id = rcs.id
                                    INNER JOIN account_invoice_line ail
                                    ON ail.invoice_id = ai.id
                                    INNER JOIN product_product pp
                                    ON ail.product_id = pp.id
                                    where ai.create_date >= :fechaIni
                                    and ai.create_date <= :fechaFin
                                    and ai.type = 'out_invoice'
                                    and ai.number is not null
                                    and ai.amount_total >= :montoIni
                                    and ai.amount_total <= :montoFin
                                    ".($estados==='TODOS'?'':'and rcs.id in('.implode(',',$arrayNumberEstados).') ')." 
                                    group by ai.number, rp.id,rp.name,ai.city,rcs.name,rp.street,rp.zip) as facturas
                                    group by id_cliente, cliente,ciudad_cliente,estado_cliente,calle,codigo_postal
                                    ORDER BY cliente ASC", ['fechaIni' => date("Y-m-d", strtotime($fechaIni)),'fechaFin' => date("Y-m-d", strtotime($fechaFin)), 'montoIni'=> $montoIni, 'montoFin' => $montoFin]);
            
            return $facturas;
        } else {
            return "usuario NO autenticado";
        }
    }

    public function porPeriodoProductos(Request $request, String $fechaIni, String $fechaFin, $productos, $estados)
    {
        $jwtAth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $checkToken = $jwtAth->checkToken($token);
        if ($checkToken) {
            
            $exploded = explode(",", $productos);
            $arrayNumber = array();
            foreach ($exploded as $ex) {
                array_push($arrayNumber, intval($ex));
            }

            $explodedEstados = explode(",", $estados);
$arrayNumberEstados = array();
foreach ($explodedEstados as $ex) {
    array_push($arrayNumberEstados, intval($ex));
}

            $facturas = DB::select("select id_cliente,cliente,ciudad_cliente,estado_cliente,calle,codigo_postal, count(id_cliente) as total_facturas from
                                    (SELECT ai.number as factura_id, rp.id as id_cliente, rp.name as cliente, ai.city as ciudad_cliente, rcs.name as estado_cliente,rp.street as calle, rp.zip as codigo_postal
                                    FROM account_invoice ai
                                    INNER JOIN res_partner rp
                                    ON ai.partner_id = rp.id
                                    INNER JOIN res_country_state rcs
                                    ON rp.state_id = rcs.id
                                    INNER JOIN account_invoice_line ail
                                    ON ail.invoice_id = ai.id
                                    INNER JOIN product_product pp
                                    ON ail.product_id = pp.id
                                    where ai.create_date >= :fechaIni
                                    and ai.create_date <= :fechaFin
                                    and ai.type = 'out_invoice'
                                    and ai.number is not null
                                    and pp.id in (".implode(',',$arrayNumber).")
                                    ".($estados==='TODOS'?'':'and rcs.id in('.implode(',',$arrayNumberEstados).') ')." 
                                    group by ai.number, rp.id,rp.name,ai.city,rcs.name,rp.street,rp.zip) as facturas
                                    group by id_cliente, cliente,ciudad_cliente,estado_cliente,calle,codigo_postal
                                    ORDER BY cliente ASC", ['fechaIni' => date("Y-m-d", strtotime($fechaIni)),'fechaFin' => date("Y-m-d", strtotime($fechaFin))]);
            
            return $facturas;
        } else {
            return "usuario NO autenticado";
        }
    }


/*******************************************
 *    PERIODO COMBINADOS 2
 *******************************************/

    public function porPeriodoClientesMonto(Request $request, String $fechaIni, String $fechaFin, $clientes, $montoIni, $montoFin, $estados)
    {
        $jwtAth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $checkToken = $jwtAth->checkToken($token);
        if ($checkToken) {
            
            $exploded = explode(",", $clientes);
            $arrayNumber = array();
            foreach ($exploded as $ex) {
                array_push($arrayNumber, intval($ex));
            }

$explodedEstados = explode(",", $estados);
$arrayNumberEstados = array();
foreach ($explodedEstados as $ex) {
    array_push($arrayNumberEstados, intval($ex));
}

            $facturas = DB::select("select id_cliente,cliente,ciudad_cliente,estado_cliente,calle,codigo_postal, count(id_cliente) as total_facturas from
                                    (SELECT ai.number as factura_id, rp.id as id_cliente, rp.name as cliente, ai.city as ciudad_cliente, rcs.name as estado_cliente,rp.street as calle, rp.zip as codigo_postal
                                    FROM account_invoice ai
                                    INNER JOIN res_partner rp
                                    ON ai.partner_id = rp.id
                                    INNER JOIN res_country_state rcs
                                    ON rp.state_id = rcs.id
                                    INNER JOIN account_invoice_line ail
                                    ON ail.invoice_id = ai.id
                                    INNER JOIN product_product pp
                                    ON ail.product_id = pp.id
                                    where ai.create_date >= :fechaIni
                                    and ai.create_date <= :fechaFin
                                    and ai.type = 'out_invoice'
                                    and ai.number is not null
                                    and rp.id in (".implode(',',$arrayNumber).")
                                    and ai.amount_total >= :montoIni
                                    and ai.amount_total <= :montoFin
                                    ".($estados==='TODOS'?'':'and rcs.id in('.implode(',',$arrayNumberEstados).') ')." 
                                    group by ai.number, rp.id,rp.name,ai.city,rcs.name,rp.street,rp.zip) as facturas
                                    group by id_cliente, cliente,ciudad_cliente,estado_cliente,calle,codigo_postal
                                    ORDER BY cliente ASC", ['fechaIni' => date("Y-m-d", strtotime($fechaIni)),'fechaFin' => date("Y-m-d", strtotime($fechaFin)), 'montoIni'=> $montoIni, 'montoFin' => $montoFin]);
            
            return $facturas;
        } else {
            return "usuario NO autenticado";
        }
    }

    public function porPeriodoClientesProductos(Request $request, String $fechaIni, String $fechaFin, $clientes, $productos, $estados)
    {
        $jwtAth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $checkToken = $jwtAth->checkToken($token);
        if ($checkToken) {
            
            $exploded = explode(",", $clientes);
            $arrayNumber = array();
            foreach ($exploded as $ex) {
                array_push($arrayNumber, intval($ex));
            }

            $explodedProductos = explode(",", $productos);
            $arrayNumberProductos = array();
            foreach ($explodedProductos as $exProducto) {
                array_push($arrayNumberProductos, intval($exProducto));
            }

$explodedEstados = explode(",", $estados);
$arrayNumberEstados = array();
foreach ($explodedEstados as $ex) {
    array_push($arrayNumberEstados, intval($ex));
}

            $facturas = DB::select("select id_cliente,cliente,ciudad_cliente,estado_cliente,calle,codigo_postal, count(id_cliente) as total_facturas from
                                    (SELECT ai.number as factura_id, rp.id as id_cliente, rp.name as cliente, ai.city as ciudad_cliente, rcs.name as estado_cliente,rp.street as calle, rp.zip as codigo_postal
                                    FROM account_invoice ai
                                    INNER JOIN res_partner rp
                                    ON ai.partner_id = rp.id
                                    INNER JOIN res_country_state rcs
                                    ON rp.state_id = rcs.id
                                    INNER JOIN account_invoice_line ail
                                    ON ail.invoice_id = ai.id
                                    INNER JOIN product_product pp
                                    ON ail.product_id = pp.id
                                    where ai.create_date >= :fechaIni
                                    and ai.create_date <= :fechaFin
                                    and ai.type = 'out_invoice'
                                    and ai.number is not null
                                    and rp.id in (".implode(',',$arrayNumber).")
                                    and pp.id in (".implode(',',$arrayNumberProductos).")
                                    ".($estados==='TODOS'?'':'and rcs.id in('.implode(',',$arrayNumberEstados).') ')." 
                                    group by ai.number, rp.id,rp.name,ai.city,rcs.name,rp.street,rp.zip) as facturas
                                    group by id_cliente, cliente,ciudad_cliente,estado_cliente,calle,codigo_postal
                                    ORDER BY cliente ASC", ['fechaIni' => date("Y-m-d", strtotime($fechaIni)),'fechaFin' => date("Y-m-d", strtotime($fechaFin))]);
            
            return $facturas;
        } else {
            return "usuario NO autenticado";
        }
    }

    public function porPeriodoProductosMonto(Request $request, String $fechaIni, String $fechaFin, $productos, $montoIni, $montoFin, $estados)
    {
        $jwtAth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $checkToken = $jwtAth->checkToken($token);
        if ($checkToken) {
            
            $explodedProductos = explode(",", $productos);
            $arrayNumberProductos = array();
            foreach ($explodedProductos as $exProducto) {
                array_push($arrayNumberProductos, intval($exProducto));
            }

$explodedEstados = explode(",", $estados);
$arrayNumberEstados = array();
foreach ($explodedEstados as $ex) {
    array_push($arrayNumberEstados, intval($ex));
}

            $facturas = DB::select("select id_cliente,cliente,ciudad_cliente,estado_cliente,calle,codigo_postal, count(id_cliente) as total_facturas from
                                    (SELECT ai.number as factura_id, rp.id as id_cliente, rp.name as cliente, ai.city as ciudad_cliente, rcs.name as estado_cliente,rp.street as calle, rp.zip as codigo_postal
                                    FROM account_invoice ai
                                    INNER JOIN res_partner rp
                                    ON ai.partner_id = rp.id
                                    INNER JOIN res_country_state rcs
                                    ON rp.state_id = rcs.id
                                    INNER JOIN account_invoice_line ail
                                    ON ail.invoice_id = ai.id
                                    INNER JOIN product_product pp
                                    ON ail.product_id = pp.id
                                    where ai.create_date >= :fechaIni
                                    and ai.create_date <= :fechaFin
                                    and ai.type = 'out_invoice'
                                    and ai.number is not null
                                    and pp.id in (".implode(',',$arrayNumberProductos).")
                                    and ai.amount_total >= :montoIni
                                    and ai.amount_total <= :montoFin
                                    ".($estados==='TODOS'?'':'and rcs.id in('.implode(',',$arrayNumberEstados).') ')." 
                                    group by ai.number, rp.id,rp.name,ai.city,rcs.name,rp.street,rp.zip) as facturas
                                    group by id_cliente, cliente,ciudad_cliente,estado_cliente,calle,codigo_postal
                                    ORDER BY cliente ASC", ['fechaIni' => date("Y-m-d", strtotime($fechaIni)),'fechaFin' => date("Y-m-d", strtotime($fechaFin)), 'montoIni'=> $montoIni, 'montoFin' => $montoFin]);
            return $facturas;
        } else {
            return "usuario NO autenticado";
        }
    }

/*******************************************
 *           P O R   P E R I O D O   3
 *******************************************/

    public function porPeriodoClientesProductosMonto(Request $request, String $fechaIni, String $fechaFin, $clientes, $productos, $montoIni, $montoFin, $estados)
    {
        $jwtAth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $checkToken = $jwtAth->checkToken($token);
        if ($checkToken) {
            
            $exploded = explode(",", $clientes);
            $arrayNumber = array();
            foreach ($exploded as $ex) {
                array_push($arrayNumber, intval($ex));
            }

            $explodedProductos = explode(",", $productos);
            $arrayNumberProductos = array();
            foreach ($explodedProductos as $exProducto) {
                array_push($arrayNumberProductos, intval($exProducto));
            }

            $explodedEstados = explode(",", $estados);
$arrayNumberEstados = array();
foreach ($explodedEstados as $ex) {
    array_push($arrayNumberEstados, intval($ex));
}

            $facturas = DB::select("select id_cliente,cliente,ciudad_cliente,estado_cliente,calle,codigo_postal, count(id_cliente) as total_facturas from
                                    (SELECT ai.number as factura_id, rp.id as id_cliente, rp.name as cliente, ai.city as ciudad_cliente, rcs.name as estado_cliente,rp.street as calle, rp.zip as codigo_postal
                                    FROM account_invoice ai
                                    INNER JOIN res_partner rp
                                    ON ai.partner_id = rp.id
                                    INNER JOIN res_country_state rcs
                                    ON rp.state_id = rcs.id
                                    INNER JOIN account_invoice_line ail
                                    ON ail.invoice_id = ai.id
                                    INNER JOIN product_product pp
                                    ON ail.product_id = pp.id
                                    where ai.create_date >= :fechaIni
                                    and ai.create_date <= :fechaFin
                                    and ai.type = 'out_invoice'
                                    and ai.number is not null
                                    and rp.id in (".implode(',',$arrayNumber).")
                                    and pp.id in (".implode(',',$arrayNumberProductos).")
                                    and ai.amount_total >= :montoIni
                                    and ai.amount_total <= :montoFin
                                    ".($estados==='TODOS'?'':'and rcs.id in('.implode(',',$arrayNumberEstados).') ')." 
                                    group by ai.number, rp.id,rp.name,ai.city,rcs.name,rp.street,rp.zip) as facturas
                                    group by id_cliente, cliente,ciudad_cliente,estado_cliente,calle,codigo_postal
                                    ORDER BY cliente ASC", ['fechaIni' => date("Y-m-d", strtotime($fechaIni)),'fechaFin' => date("Y-m-d", strtotime($fechaFin)), 'montoIni'=> $montoIni, 'montoFin' => $montoFin]);
            
            return $facturas;
        } else {
            return "usuario NO autenticado";
        }
    }


/*******************************************
 *           P O R    C L I E N T E
 *******************************************/

    public function porClientePeriodo(Request $request, $cliente, String $fechaIni, String $fechaFin, $estados)
    {
        $jwtAth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $checkToken = $jwtAth->checkToken($token);
        if ($checkToken) {

$explodedEstados = explode(",", $estados);
$arrayNumberEstados = array();
foreach ($explodedEstados as $ex) {
    array_push($arrayNumberEstados, intval($ex));
}

            $facturas = DB::select("SELECT ai.number as factura_id, rp.id as id_cliente, rp.name as cliente, ai.city as ciudad_cliente, rcs.name as estado_cliente, ai.amount_total monto,ai.create_date fecha
                                    FROM account_invoice ai
                                    INNER JOIN res_partner rp
                                    ON ai.partner_id = rp.id
                                    INNER JOIN res_country_state rcs
                                    ON rp.state_id = rcs.id
                                    INNER JOIN account_invoice_line ail
                                    ON ail.invoice_id = ai.id
                                    INNER JOIN product_product pp
                                    ON ail.product_id = pp.id
                                    where ai.create_date >= :fechaIni
                                    and ai.create_date <= :fechaFin
                                    and ai.type = 'out_invoice'
                                    and ai.number is not null
                                    and rp.id = :cliente 
                                    ".($estados==='TODOS'?'':'and rcs.id in('.implode(',',$arrayNumberEstados).') ')." 
                                    group by ai.number, rp.id,rp.name,ai.city,rcs.name,ai.amount_total,ai.create_date
                                    ORDER BY cliente ASC", ['fechaIni' => date("Y-m-d", strtotime($fechaIni)),'fechaFin' => date("Y-m-d", strtotime($fechaFin)),'cliente' => $cliente]);
            
            return $facturas;
        } else {
            return "usuario NO autenticado";
        }
    }

        public function porClientePeriodoMonto(Request $request, $cliente, String $fechaIni, String $fechaFin, $montoIni, $montoFin, $estados)
    {
        $jwtAth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $checkToken = $jwtAth->checkToken($token);
        if ($checkToken) {

            $explodedEstados = explode(",", $estados);
$arrayNumberEstados = array();
foreach ($explodedEstados as $ex) {
    array_push($arrayNumberEstados, intval($ex));
}

$facturas = DB::select("SELECT ai.number as factura_id, rp.id as id_cliente, rp.name as cliente, ai.city as ciudad_cliente, rcs.name as estado_cliente, ai.amount_total monto,ai.create_date fecha
                                    FROM account_invoice ai
                                    INNER JOIN res_partner rp
                                    ON ai.partner_id = rp.id
                                    INNER JOIN res_country_state rcs
                                    ON rp.state_id = rcs.id
                                    INNER JOIN account_invoice_line ail
                                    ON ail.invoice_id = ai.id
                                    INNER JOIN product_product pp
                                    ON ail.product_id = pp.id
                                    where ai.create_date >= :fechaIni
                                    and ai.create_date <= :fechaFin
                                    and ai.type = 'out_invoice'
                                    and ai.number is not null
                                    and rp.id = :cliente
                                    and ai.amount_total >= :montoIni
                                    and ai.amount_total <= :montoFin
                                    ".($estados==='TODOS'?'':'and rcs.id in('.implode(',',$arrayNumberEstados).')')." 
                                    group by ai.number, rp.id,rp.name,ai.city,rcs.name,ai.amount_total,ai.create_date
                                    ORDER BY cliente ASC", ['fechaIni' => date("Y-m-d", strtotime($fechaIni)),'fechaFin' => date("Y-m-d", strtotime($fechaFin)), 'montoIni'=> $montoIni, 'montoFin' => $montoFin, "cliente" => $cliente]);
            return $facturas;
        } else {
            return "usuario NO autenticado";
        }
    }

        public function porClientePeriodoProductos(Request $request, $cliente, String $fechaIni, String $fechaFin, $productos, $estados)
    {
        $jwtAth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $checkToken = $jwtAth->checkToken($token);
        if ($checkToken) {

            $explodedProductos = explode(",", $productos);
            $arrayNumberProductos = array();
            foreach ($explodedProductos as $exProducto) {
                array_push($arrayNumberProductos, intval($exProducto));
            }

            $explodedEstados = explode(",", $estados);
$arrayNumberEstados = array();
foreach ($explodedEstados as $ex) {
    array_push($arrayNumberEstados, intval($ex));
}

            $facturas = DB::select("SELECT ai.number as factura_id, rp.id as id_cliente, rp.name as cliente, ai.city as ciudad_cliente, rcs.name as estado_cliente, ai.amount_total monto,ai.create_date fecha
                                    FROM account_invoice ai
                                    INNER JOIN res_partner rp
                                    ON ai.partner_id = rp.id
                                    INNER JOIN res_country_state rcs
                                    ON rp.state_id = rcs.id
                                    INNER JOIN account_invoice_line ail
                                    ON ail.invoice_id = ai.id
                                    INNER JOIN product_product pp
                                    ON ail.product_id = pp.id
                                    where ai.create_date >= :fechaIni
                                    and ai.create_date <= :fechaFin
                                    and ai.type = 'out_invoice'
                                    and ai.number is not null
                                    and rp.id = :cliente
                                    and pp.id in (".implode(',',$arrayNumberProductos).")
                                    ".($estados==='TODOS'?'':'and rcs.id in('.implode(',',$arrayNumberEstados).') ')." 
                                    group by ai.number, rp.id,rp.name,ai.city,rcs.name,ai.amount_total,ai.create_date
                                    ORDER BY cliente ASC", ['fechaIni' => date("Y-m-d", strtotime($fechaIni)),'fechaFin' => date("Y-m-d", strtotime($fechaFin)), "cliente" => $cliente]);
            return $facturas;
        } else {
            return "usuario NO autenticado";
        }
    }

    public function porClientePeriodoMontoProductos(Request $request, $cliente, String $fechaIni, String $fechaFin, $montoIni, $montoFin, $productos, $estados)
    {
        $jwtAth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $checkToken = $jwtAth->checkToken($token);
        if ($checkToken) {
            $explodedProductos = explode(",", $productos);
            $arrayNumberProductos = array();
            foreach ($explodedProductos as $exProducto) {
                array_push($arrayNumberProductos, intval($exProducto));
            }

$explodedEstados = explode(",", $estados);
$arrayNumberEstados = array();
foreach ($explodedEstados as $ex) {
    array_push($arrayNumberEstados, intval($ex));
}

$facturas = DB::select("SELECT ai.number as factura_id, rp.id as id_cliente, rp.name as cliente, ai.city as ciudad_cliente, rcs.name as estado_cliente, ai.amount_total monto,ai.create_date fecha
                                    FROM account_invoice ai
                                    INNER JOIN res_partner rp
                                    ON ai.partner_id = rp.id
                                    INNER JOIN res_country_state rcs
                                    ON rp.state_id = rcs.id
                                    INNER JOIN account_invoice_line ail
                                    ON ail.invoice_id = ai.id
                                    INNER JOIN product_product pp
                                    ON ail.product_id = pp.id
                                    where ai.create_date >= :fechaIni
                                    and ai.create_date <= :fechaFin
                                    and ai.type = 'out_invoice'
                                    and ai.number is not null
                                    and rp.id = :cliente
                                    and ai.amount_total >= :montoIni
                                    and ai.amount_total <= :montoFin
                                    and pp.id in (".implode(',',$arrayNumberProductos).")
                                    ".($estados==='TODOS'?'':'and rcs.id in('.implode(',',$arrayNumberEstados).') ')." 
                                    group by ai.number, rp.id,rp.name,ai.city,rcs.name,ai.amount_total,ai.create_date
                                    ORDER BY cliente ASC", ['fechaIni' => date("Y-m-d", strtotime($fechaIni)),'fechaFin' => date("Y-m-d", strtotime($fechaFin)), 'montoIni'=> $montoIni, 'montoFin' => $montoFin, "cliente" => $cliente]);
            return $facturas;
        } else {
            return "usuario NO autenticado";
        }
    }



}
