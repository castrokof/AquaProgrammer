<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Factura Moderna</title>
<style>
  body { font-family: Arial, sans-serif; margin: 30px; color: #333; }
  header { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #007BFF; padding-bottom: 10px; }
  header img { max-height: 70px; }
  h1 { color: #007BFF; }
  .datos, .cliente { margin-top: 20px; }
  table { width: 100%; border-collapse: collapse; margin-top: 20px; }
  th, td { border: 1px solid #ddd; padding: 12px; }
  th { background-color: #007BFF; color: #fff; }
  tfoot tr td { font-weight: bold; }
  .totales td { background-color: #f0f0f0; }
</style>
</head>
<body>

<header>
  <div>
    <img src="logo.png" alt="Logo Empresa" />
    <p>Empresa XYZ S.A.S.</p>
  </div>
  <div>
    <h1>Factura</h1>
    <p><strong>N°:</strong> 000123</p>
    <p><strong>Fecha:</strong> 28/05/2025</p>
  </div>
</header>

<section class="cliente">
  <h2>Datos del Cliente</h2>
  <p><strong>Nombre:</strong> Juan Pérez</p>
  <p><strong>Documento:</strong> CC 123456789</p>
  <p><strong>Dirección:</strong> Calle Falsa 123, Bogotá</p>
</section>

<table>
  <thead>
    <tr>
      <th>Concepto</th>
      <th>Cantidad</th>
      <th>Precio Unitario</th>
      <th>Total</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Servicio Agua Potable</td>
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
      <td colspan="3">IVA 19%</td>
      <td>$13,300</td>
    </tr>
    <tr class="totales">
      <td colspan="3">Total</td>
      <td>$83,300</td>
    </tr>
  </tfoot>
</table>

<footer style="text-align:center; margin-top: 30px; font-style: italic; color: #666;">
  <p>Gracias por preferirnos.</p>
</footer>

</body>
</html>
