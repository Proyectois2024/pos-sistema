<?php
$id = isset($_GET["idDocto"]) ? (int)$_GET["idDocto"] : 0;

$cotizacion = ControladorCotizaciones::ctrMostrarCotizacion("id", $id);
$detalle = ControladorCotizaciones::ctrMostrarDetalleCotizacion($id);
$cliente = array();

if (is_array($cotizacion) && isset($cotizacion["id_cliente"])) {
    $cliente = ControladorClientes::ctrMostrarClientes("id", $cotizacion["id_cliente"]);
}

if (!$cotizacion || !is_array($cotizacion)) {
    die("Cotización no encontrada");
}

$nombreCliente = isset($cliente["nombre"]) ? $cliente["nombre"] : "";
$nitCliente = isset($cliente["documento"]) ? $cliente["documento"] : "";
$direccionCliente = isset($cliente["direccion"]) ? $cliente["direccion"] : "";
$fecha = !empty($cotizacion["fecha"]) ? date("d/m/Y", strtotime($cotizacion["fecha"])) : "";
$total = isset($cotizacion["total"]) ? (float)$cotizacion["total"] : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Cotización <?php echo htmlspecialchars($cotizacion["codigo_docto"]); ?></title>
<style>
@page {
    size: letter;
    margin: 14mm;
}
body{
    font-family: Arial, Helvetica, sans-serif;
    color:#111;
    margin:0;
    background:#fff;
}
.page{
    width: 100%;
    max-width: 900px;
    margin: 0 auto;
    padding: 10px 20px 30px 20px;
    box-sizing: border-box;
}
.top-line{
    border-top: 4px solid #5ea52f;
    margin: 6px 0 24px 0;
}
.header{
text-align:center;
margin-bottom:20px;
}
.header-left,
.header-center,
.header-right{
    display: table-cell;
    vertical-align: middle;
}
.header-left{
    width: 19%;
}
.header-center{
    width: 51%;
    text-align: center;
}
.header-right{
    width: 30%;
    vertical-align: top;
}
.logo-center{
margin-bottom:10px;
}

.logo-grande{
width:500px;
}
.company-title{
    font-size: 28px;
    font-weight: 800;
    line-height: 1.05;
    margin: 0 0 6px 0;
}
.company-text{
    font-size: 14px;
    line-height: 1.3;
    margin: 0;
}
.doc-box{
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}
.doc-box td{
    border: 1px solid #7da942;
    padding: 3px 6px;
}
.doc-box .doc-head{
    text-align:center;
    font-weight:700;
    background:#fff;
}
.doc-box .doc-title{
    text-align:center;
    font-weight:800;
    color:#fff;
    background:#6ea834;
    font-size: 17px;
}
.info-table{
    width:100%;
    border-collapse: collapse;
    margin: 10px 0 16px 0;
    font-size: 14px;
}
.info-table td{
    border:1px solid #cfcfcf;
    padding: 4px 8px;
}
.info-label{
    width: 34%;
    font-weight: 700;
    background:#f7f7f4;
}
.items{
    width:100%;
    border-collapse: collapse;
    table-layout: fixed;
    font-size: 14px;
}
.items th{
    background:#6ea834;
    color:#fff;
    border:1px solid #6ea834;
    padding:6px 8px;
    text-align:center;
    font-weight:700;
}
.items td{
    border:1px solid #d0d0d0;
    padding:6px 8px;
}
.items td:nth-child(1),
.items td:nth-child(3),
.items td:nth-child(4),
.items td:nth-child(5){
    text-align:center;
}
.total-table{
    width:100%;
    border-collapse: collapse;
    margin-top: 18px;
    font-size: 16px;
    font-weight: 700;
}
.total-table td{
    border:2px solid #6ea834;
    padding:8px 10px;
}
.total-label{
    width: 50%;
    text-align:center;
}
.total-value{
    text-align:center;
}
.obs{
    margin-top: 14px;
    font-size: 15px;
}
.obs b{
    font-weight: 700;
}
.signatures{
    margin-top: 70px;
    width:100%;
    display: table;
}
.sign-col{
    display: table-cell;
    width:50%;
    text-align:center;
    vertical-align: bottom;
}
.sign-line{
    width: 65%;
    margin: 0 auto 4px auto;
    border-top:1px solid #333;
    height: 1px;
}
.sign-name{
    font-weight:700;
}
.note{
    margin-top: 26px;
    text-align:center;
    font-style: italic;
    color:#444;
    font-size: 14px;
}
@media print{
    .no-print{ display:none; }
    body{ -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>
</head>
<body>
<div class="page">

    <div class="top-line"></div>

   <div class="header">

    <div class="logo-center">
        <img src="vistas/img/logo_chonay.png" class="logo-grande">
    </div>

</div>

        <div class="header-right">
            <table class="doc-box">
                <tr><td class="doc-head">DOCUMENTO</td></tr>
                <tr><td class="doc-title">COTIZACION</td></tr>
                <tr><td><b>No.</b> <?php echo htmlspecialchars($cotizacion["codigo_docto"]); ?></td></tr>
                <tr><td><b>Fecha:</b> <?php echo $fecha; ?></td></tr>
                <tr><td><b>NIT:</b> <?php echo htmlspecialchars((string)$nitCliente); ?></td></tr>
            </table>
        </div>
    </div>

    <table class="info-table">
        <tr>
            <td class="info-label">Nombre:</td>
            <td><?php echo htmlspecialchars($nombreCliente); ?></td>
        </tr>
        <tr>
            <td class="info-label">NIT / DPI:</td>
            <td><?php echo htmlspecialchars((string)$nitCliente); ?></td>
        </tr>
        <tr>
            <td class="info-label">Direccion / referencia:</td>
            <td><?php echo htmlspecialchars($direccionCliente); ?></td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width:12%;">CANTIDAD</th>
                <th>DESCRIPCION</th>
                <th style="width:18%;">PRECIO UNIT.</th>
                <th style="width:18%;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            <?php if(is_array($detalle) && count($detalle) > 0): ?>
                <?php foreach($detalle as $item): ?>
                    <tr>
                        <td><?php echo number_format((float)$item["cantidad"], 0); ?></td>
                        <td><?php echo htmlspecialchars($item["descripcion_item"]); ?></td>
                        <td>Q <?php echo number_format((float)$item["precio_unitario"], 2); ?></td>
                        <td>Q <?php echo number_format((float)$item["subtotal"], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td>&nbsp;</td><td></td><td></td><td></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <table class="total-table">
        <tr>
            <td class="total-label">TOTAL</td>
            <td class="total-value">Q <?php echo number_format($total, 2); ?></td>
        </tr>
    </table>

    <div class="obs">
        <b>Observaciones:</b>
    </div>

    <div class="signatures">
        <div class="sign-col">
            <div class="sign-line"></div>
            <div class="sign-name">AGROPECUARIA CHONAY</div>
        </div>
        <div class="sign-col">
            <div class="sign-line"></div>
            <div class="sign-name">RECIBIDO POR CLIENTE</div>
        </div>
    </div>

    <div class="note">
        AGROPECUARIA CHONAY, SOMOS ASESORIA TECNICA RESPONSABLE...
    </div>

    <div class="no-print" style="margin-top:30px; text-align:center;">
        <button onclick="window.print()">Imprimir</button>
    </div>

</div>
</body>
</html>