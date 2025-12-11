<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Factura Clásica</title>
<style>
  body { font-family: "Times New Roman", serif; margin: 30px; color: #000; }
  .header { text-align: center; margin-bottom: 20px; }
  .header img { max-height: 80px; }
  .datos-empresa, .datos-cliente { margin-bottom: 20px; }
  table { width: 100%; border-collapse: collapse; }
  th, td { border: 1px solid #444; padding: 10px; }
  th { background-color: #ddd; }
  .totales { font-weight: bold; }
</style>
</head>
<body>

<div class="header">
  <img src="logo.png" alt="Logo Empresa" />
  <h1>Factura de Servicios Públicos</h1>
  <p>Empresa XYZ S.A.S.</p>
  <p>Dirección: Calle 123 #45-67 | Tel: (1) 234 5678</p>
</div>

<div class="datos-empresa">
  <strong>Factura N°:</strong> 000123<br />
  <strong>Fecha emisión:</strong> 28/05/2025
</div>

<div class="datos-cliente">
  <strong>Cliente:</strong> Juan Pérez<br />
  <strong>Documento:</strong> CC 123456789<br />
  <strong>Dirección:</strong> Calle Falsa 123, Bogotá
</div>

<table>
  <thead>
    <tr>
      <th>Descripción</th>
      <th>Cantidad</th>
      <th>Valor unitario</th>
      <th>Total</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Servicio de Agua Potable</td>
      <td>1</td>
      <td>$50,000</td>
      <td>$50,000</td>
    </tr>
    <tr>
      <td>Servicio Alcantarillado</td>
      <td>1</td>
      <td>$20,000</td>
      <td>$20,000</td>
    </tr>
  </tbody>
  <tfoot>
    <tr class="totales">
      <td colspan="3">Subtotal</td>
      <td>$70,000</td>
    </tr>
    <tr class="totales">
      <td colspan="3">IVA (19%)</td>
      <td>$13,300</td>
    </tr>
    <tr class="totales">
      <td colspan="3">Total a pagar</td>
      <td>$83,300</td>
    </tr>
  </tfoot>
</table>

<p>¡Gracias por confiar en nosotros!</p>

</body>
</html>
