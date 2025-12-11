<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura Acueducto</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; }
        .container { width: 800px; margin: auto; }
        .header, .section, .totales, .footer { border: 1px solid #000; padding: 10px; margin-bottom: 10px; }
        .header img { width: 60px; float: left; margin-right: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        .right { text-align: right; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
    </style>
</head>
<body>
<div class="container">

    <!-- ENCABEZADO -->
    <div class="header">
        <h2>ASOCIACIÓN ADMINISTRADORA DEL ACUEDUCTO<br>ALTO LOS MANGOS</h2>
        <p>NIT: 805.016.027-9</p>
        <p>Teléfonos: Fijo 602 513 05 21 – Celular 300 381 36 09<br>
           Email: asociacionaltolosmangos@gmail.com</p>
        <p class="right bold">Factura No: 57681<br>Contrato No: 29.114.439</p>
    </div>

    <!-- DATOS DEL SUSCRIPTOR -->
    <div class="section">
        <table>
            <tr>
                <td><b>NUID:</b> 577</td>
                <td><b>Suscriptor:</b> Faustina Giraldo Hernandez</td>
                <td><b>Sector:</b> La Reforma</td>
                <td><b>Estrato:</b> 3</td>
            </tr>
            <tr>
                <td><b>Medidor:</b> M3 18131003</td>
                <td><b>Clase de Uso:</b> Residencial</td>
                <td colspan="2"></td>
            </tr>
        </table>
        <table>
            <tr>
                <td><b>Mes Cuenta:</b> Abril 2025</td>
                <td><b>Periodo:</b> 1 Abril 2025 - 30 Abril 2025</td>
                <td><b>Días Fact:</b> 30</td>
                <td><b>Fecha Expedición:</b> 30 Abril 2025</td>
                <td><b>F. Mora:</b> 20 Mayo 2025</td>
                <td><b>Suspensión:</b> 5/21/2025</td>
            </tr>
        </table>
    </div>

    <!-- ACUEDUCTO -->
    <div class="section">
        <h3>ACUEDUCTO</h3>
        <table>
            <thead>
                <tr><th>Concepto</th><th>Cant.</th><th>Vr. Unit.</th><th>Valor</th></tr>
            </thead>
            <tbody>
                <tr><td>Cargo Fijo</td><td></td><td></td><td>$8,700</td></tr>
                <tr><td>Cons. Básico</td><td>6</td><td>$1,150</td><td>$6,900</td></tr>
                <tr><td>Cons. Complementario</td><td>8</td><td>$1,400</td><td>$11,200</td></tr>
                <tr><td>Cons. Suntuario</td><td></td><td></td><td>$0</td></tr>
                <tr><td>Intereses de Mora</td><td></td><td></td><td>$0</td></tr>
            </tbody>
            <tfoot>
                <tr><td colspan="3" class="right bold">Subtotal:</td><td>$38,300</td></tr>
            </tfoot>
        </table>
        <p><b>Lectura Actual:</b> 529 &nbsp; <b>Lectura Anterior:</b> 505 &nbsp; <b>Consumo:</b> 24</p>
        <p><b>Promedio:</b> 12 M3</p>
    </div>

    <!-- ALCANTARILLADO -->
    <div class="section">
        <h3>ALCANTARILLADO</h3>
        <table>
            <thead>
                <tr><th>Concepto</th><th>Vr. Unit.</th><th>Valor</th></tr>
            </thead>
            <tbody>
                <tr><td>Vertimiento Básico</td><td>$500</td><td>$0</td></tr>
                <tr><td>Vertimiento Complementario</td><td>$650</td><td>$0</td></tr>
                <tr><td>Vertimiento Suntuario</td><td>$1,000</td><td>$0</td></tr>
                <tr><td>Intereses de Mora</td><td></td><td>$0</td></tr>
            </tbody>
            <tfoot>
                <tr><td colspan="2" class="right bold">Subtotal:</td><td>$0</td></tr>
            </tfoot>
        </table>
    </div>

    <!-- TOTALES -->
    <div class="totales">
        <table>
            <tr><td><b>Acueducto:</b></td><td class="right">$38,300</td></tr>
            <tr><td><b>Alcantarillado:</b></td><td class="right">$0</td></tr>
            <tr><td><b>Otros:</b></td><td class="right">$0</td></tr>
            <tr><td><b>Saldo Anterior:</b></td><td class="right">$76,300</td></tr>
            <tr><td><b>Pagos Realizados:</b></td><td class="right">-$76,300</td></tr>
            <tr><td class="bold">TOTAL A PAGAR:</td><td class="right bold">$38,300</td></tr>
        </table>
    </div>

    <!-- PIE DE PAGINA -->
    <div class="footer center">
        <p><b>RECUERDE:</b> Después de dos facturas vencidas su servicio será suspendido.<br>
        Si está al día, haga caso omiso a la fecha de suspensión.</p>
    </div>

</div>
</body>
</html>
