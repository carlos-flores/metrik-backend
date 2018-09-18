<?php
header("access-control-allow-origin: *");
header('Access-Control-Allow-Headers: X-CSRF-Token, Access-Control-Request-Headers, Access-Control-Request-Method, Accept, X-Requested-With, Content-Type, X-Auth-Token, Origin, Authorization');
header('Access-Control-Allow-Methods: PATCH, GET, POST, PUT, DELETE, OPTIONS');

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Usuarios
Route::post('/api/register','UserController@register');
Route::post('/api/login','UserController@login');

// Pruebas
Route::get('/api/prueba', 'PruebaController@prueba');
Route::get('/api/facturas/prueba', 'FacturasController@prueba');

// Generales
Route::get('/api/facturas/ultimas/{size}', 'FacturasController@ultimas');
Route::get('/api/facturas/detalle/{id}', 'FacturasController@detalleFactura');
Route::get('/api/facturas/clientes/periodo/{fechaIni}/{fechaFin}', 'FacturasController@clientes');
Route::get('/api/facturas/productos/periodo/{fechaIni}/{fechaFin}', 'FacturasController@productos');
//Route::get('/api/facturas/clientes', 'FacturasController@clientes');


// Facturas por Periodo
Route::get('/api/facturas/porPeriodo/{fechaIni}/{fechaFin}', 'FacturasController@porPeriodo');
Route::get('/api/facturas/porPeriodo/{fechaIni}/{fechaFin}/clientes/{clientes}', 'FacturasController@porPeriodoCliente');
Route::get('/api/facturas/porPeriodo/{fechaIni}/{fechaFin}/monto/{montoIni}/{montoFin}', 'FacturasController@porPeriodoMonto');
Route::get('/api/facturas/porPeriodo/{fechaIni}/{fechaFin}/productos/{productos}', 'FacturasController@porPeriodoProductos');

// Combinación 2
Route::get('/api/facturas/porPeriodo/{fechaIni}/{fechaFin}/clientes/{clientes}/monto/{montoIni}/{montoFin}', 'FacturasController@porPeriodoClientesMonto');
Route::get('/api/facturas/porPeriodo/{fechaIni}/{fechaFin}/clientes/{clientes}/productos/{productos}', 'FacturasController@porPeriodoClientesProductos');
Route::get('/api/facturas/porPeriodo/{fechaIni}/{fechaFin}/productos/{productos}/monto/{montoIni}/{montoFin}', 'FacturasController@porPeriodoProductosMonto');

// Combinación 3
Route::get('/api/facturas/porPeriodo/{fechaIni}/{fechaFin}/clientes/{clientes}/productos/{productos}/monto/{montoIni}/{montoFin}', 'FacturasController@porPeriodoClientesProductosMonto');

// Facturas por cliente
Route::get('/api/facturas/porCliente/{cliente}/periodo/{fechaIni}/{fechaFin}', 'FacturasController@porClientePeriodo');
Route::get('/api/facturas/porCliente/{cliente}/periodo/{fechaIni}/{fechaFin}/monto/{montoIni}/{montoFin}', 'FacturasController@porClientePeriodoMonto');
Route::get('/api/facturas/porCliente/{cliente}/periodo/{fechaIni}/{fechaFin}/productos/{productos}', 'FacturasController@porClientePeriodoProductos');
Route::get('/api/facturas/porCliente/{cliente}/periodo/{fechaIni}/{fechaFin}/monto/{montoIni}/{montoFin}/productos/{productos}', 'FacturasController@porClientePeriodoMontoProductos');

// Detalle de factura


//Route::resource('/api/cars','CarController');
